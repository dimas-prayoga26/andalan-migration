<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ActivityScheduleController extends Controller
{
    public function index(): View
    {
        return view('activity_schedule.index');
    }

    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'timeZone' => ['nullable', 'string'],
        ]);

        $defaultTimeZone = (string) config('activity_schedule.timezone', config('app.timezone', 'UTC'));
        $timeZone = $this->resolveTimeZone($validated['timeZone'] ?? null, $defaultTimeZone);

        $rangeStart = CarbonImmutable::parse($validated['start'], $timeZone);
        $rangeEnd = CarbonImmutable::parse($validated['end'], $timeZone);
        $calendarIds = config('activity_schedule.google_calendar_ids', []);
        $events = [];

        if (! is_array($calendarIds)) {
            return response()->json($events);
        }

        foreach ($calendarIds as $calendarId) {
            if (! is_string($calendarId) || trim($calendarId) === '') {
                continue;
            }

            $events = [
                ...$events,
                ...$this->fetchEventsFromCalendar(trim($calendarId), $rangeStart, $rangeEnd, $timeZone),
            ];
        }

        usort($events, static function (array $left, array $right): int {
            return strcmp((string) $left['start'], (string) $right['start']);
        });

        return response()->json($events);
    }

    private function fetchEventsFromCalendar(
        string $calendarId,
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd,
        string $targetTimeZone
    ): array {
        $cacheKey = sprintf('activity_schedule_ics:%s', md5($calendarId));
        $icsContent = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($calendarId): ?string {
            $encodedCalendarId = rawurlencode($calendarId);
            $feedUrl = sprintf('https://calendar.google.com/calendar/ical/%s/public/basic.ics', $encodedCalendarId);
            $response = Http::accept('text/calendar')
                ->connectTimeout(5)
                ->timeout(20)
                ->retry(2, 300, throw: false)
                ->get($feedUrl);

            if ($response->failed()) {
                return null;
            }

            return $response->body();
        });

        if (! is_string($icsContent) || $icsContent === '') {
            return [];
        }

        return $this->extractEventsFromIcs($icsContent, $rangeStart, $rangeEnd, $targetTimeZone, $calendarId);
    }

    private function extractEventsFromIcs(
        string $icsContent,
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd,
        string $targetTimeZone,
        string $calendarId
    ): array {
        $events = [];
        $lines = $this->unfoldIcsLines($icsContent);
        $eventData = [];
        $insideEvent = false;

        foreach ($lines as $line) {
            if ($line === 'BEGIN:VEVENT') {
                $insideEvent = true;
                $eventData = [];

                continue;
            }

            if ($line === 'END:VEVENT') {
                $insideEvent = false;
                $events = [...$events, ...$this->buildEventsFromIcsData($eventData, $rangeStart, $rangeEnd, $targetTimeZone, $calendarId)];
                $eventData = [];

                continue;
            }

            if (! $insideEvent) {
                continue;
            }

            [$name, $value] = $this->parseIcsContentLine($line);

            if ($name === null || $value === null) {
                continue;
            }

            if (! array_key_exists($name, $eventData)) {
                $eventData[$name] = [];
            }

            $eventData[$name][] = $value;
        }

        return $events;
    }

    private function buildEventsFromIcsData(
        array $eventData,
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd,
        string $targetTimeZone,
        string $calendarId
    ): array {
        $summary = $eventData['SUMMARY'][0] ?? null;
        $startEntry = $this->findPropertyEntry($eventData, 'DTSTART');

        if (! is_string($summary) || ! is_array($startEntry)) {
            return [];
        }

        $startDateTime = $this->parseIcsDateTime($startEntry['property'], $startEntry['value'], $targetTimeZone);

        if (! is_array($startDateTime)) {
            return [];
        }

        $endDateTime = null;
        $endEntry = $this->findPropertyEntry($eventData, 'DTEND');

        if (is_array($endEntry)) {
            $endDateTime = $this->parseIcsDateTime($endEntry['property'], $endEntry['value'], $targetTimeZone);
        }

        $start = $startDateTime['date'];
        $allDay = $startDateTime['all_day'];
        $end = is_array($endDateTime) ? $endDateTime['date'] : null;
        $rrule = $eventData['RRULE'][0] ?? null;
        $uid = $eventData['UID'][0] ?? md5($calendarId.$summary.$start->toIso8601String());

        if (is_string($rrule) && str_contains(strtoupper($rrule), 'FREQ=WEEKLY')) {
            return $this->buildRecurringEvents(
                uid: (string) $uid,
                title: $this->decodeIcsText($summary),
                start: $start,
                end: $end,
                allDay: $allDay,
                rrule: $rrule,
                rangeStart: $rangeStart,
                rangeEnd: $rangeEnd,
                calendarId: $calendarId
            );
        }

        if (! $this->eventIntersectsRange($start, $end, $rangeStart, $rangeEnd, $allDay)) {
            return [];
        }

        return [
            $this->buildEventPayload(
                id: (string) $uid,
                title: $this->decodeIcsText($summary),
                start: $start,
                end: $end,
                allDay: $allDay,
                calendarId: $calendarId
            ),
        ];
    }

    private function buildRecurringEvents(
        string $uid,
        string $title,
        CarbonImmutable $start,
        ?CarbonImmutable $end,
        bool $allDay,
        string $rrule,
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd,
        string $calendarId
    ): array {
        $ruleParts = $this->parseRrule($rrule);

        if (($ruleParts['FREQ'] ?? null) !== 'WEEKLY') {
            return [];
        }

        $interval = max(1, (int) ($ruleParts['INTERVAL'] ?? 1));
        $countLimit = isset($ruleParts['COUNT']) ? max(1, (int) $ruleParts['COUNT']) : null;
        $until = isset($ruleParts['UNTIL'])
            ? $this->parseUntilValue((string) $ruleParts['UNTIL'], $allDay, $start->getTimezone()->getName())
            : null;
        $byDays = $this->parseByDayValues($ruleParts['BYDAY'] ?? null);

        if ($byDays === []) {
            $byDays = [$start->dayOfWeekIso];
        }

        sort($byDays);
        $durationInSeconds = null;

        if ($end instanceof CarbonImmutable && $end->greaterThan($start)) {
            $durationInSeconds = (int) round($start->diffInSeconds($end, true));
        }

        $events = [];
        $createdInstances = 0;
        $weekCursor = $start->startOfWeek(CarbonImmutable::MONDAY);
        $maxIterations = 600;
        $iteration = 0;

        while ($weekCursor->lessThan($rangeEnd) && $iteration < $maxIterations) {
            foreach ($byDays as $day) {
                $occurrenceStart = $weekCursor
                    ->addDays($day - 1)
                    ->setTime($start->hour, $start->minute, $start->second, $start->microsecond);

                if ($occurrenceStart->lessThan($start)) {
                    continue;
                }

                if ($until instanceof CarbonImmutable && $occurrenceStart->greaterThan($until)) {
                    continue;
                }

                $createdInstances++;

                if ($countLimit !== null && $createdInstances > $countLimit) {
                    break 2;
                }

                $occurrenceEnd = $durationInSeconds !== null
                    ? $occurrenceStart->addSeconds($durationInSeconds)
                    : null;

                if (! $this->eventIntersectsRange($occurrenceStart, $occurrenceEnd, $rangeStart, $rangeEnd, $allDay)) {
                    continue;
                }

                $events[] = $this->buildEventPayload(
                    id: sprintf('%s-%s', $uid, $occurrenceStart->timestamp),
                    title: $title,
                    start: $occurrenceStart,
                    end: $occurrenceEnd,
                    allDay: $allDay,
                    calendarId: $calendarId
                );
            }

            $weekCursor = $weekCursor->addWeeks($interval);
            $iteration++;
        }

        return $events;
    }

    /**
     * @return array<int, string>
     */
    private function unfoldIcsLines(string $icsContent): array
    {
        $rawLines = preg_split('/\r\n|\r|\n/', $icsContent) ?: [];
        $lines = [];

        foreach ($rawLines as $line) {
            if ($line !== '' && ($line[0] === ' ' || $line[0] === "\t") && $lines !== []) {
                $lastIndex = array_key_last($lines);
                if ($lastIndex !== null) {
                    $lines[$lastIndex] .= ltrim($line);
                }

                continue;
            }

            $lines[] = trim($line);
        }

        return $lines;
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function parseIcsContentLine(string $line): array
    {
        if (! str_contains($line, ':')) {
            return [null, null];
        }

        [$propertyName, $value] = explode(':', $line, 2);

        return [trim($propertyName), trim($value)];
    }

    /**
     * @param  array<string, array<int, string>>  $eventData
     * @return array{property: string, value: string}|null
     */
    private function findPropertyEntry(array $eventData, string $propertyPrefix): ?array
    {
        foreach ($eventData as $property => $values) {
            if (! str_starts_with($property, $propertyPrefix) || $values === []) {
                continue;
            }

            return [
                'property' => $property,
                'value' => $values[0],
            ];
        }

        return null;
    }

    /**
     * @return array{date: CarbonImmutable, all_day: bool}|null
     */
    private function parseIcsDateTime(string $property, string $value, string $targetTimeZone): ?array
    {
        $segments = explode(';', $property);
        $params = [];

        foreach (array_slice($segments, 1) as $segment) {
            if (! str_contains($segment, '=')) {
                continue;
            }

            [$key, $paramValue] = explode('=', $segment, 2);
            $params[strtoupper(trim($key))] = trim($paramValue);
        }

        $valueType = strtoupper($params['VALUE'] ?? '');
        $eventTimeZone = $params['TZID'] ?? $targetTimeZone;

        try {
            if ($valueType === 'DATE' || preg_match('/^\d{8}$/', $value) === 1) {
                $date = CarbonImmutable::createFromFormat('Ymd', $value, $targetTimeZone);

                if (! $date instanceof CarbonImmutable) {
                    return null;
                }

                return [
                    'date' => $date->startOfDay(),
                    'all_day' => true,
                ];
            }

            if (str_ends_with($value, 'Z')) {
                $date = CarbonImmutable::createFromFormat('Ymd\\THis\\Z', $value, 'UTC');

                if (! $date instanceof CarbonImmutable) {
                    return null;
                }

                return [
                    'date' => $date->setTimezone($targetTimeZone),
                    'all_day' => false,
                ];
            }

            $date = CarbonImmutable::createFromFormat('Ymd\\THis', $value, $eventTimeZone);

            if (! $date instanceof CarbonImmutable) {
                return null;
            }

            return [
                'date' => $date->setTimezone($targetTimeZone),
                'all_day' => false,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, string>
     */
    private function parseRrule(string $rrule): array
    {
        $parts = explode(';', strtoupper(trim($rrule)));
        $mapped = [];

        foreach ($parts as $part) {
            if (! str_contains($part, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $part, 2);
            $mapped[trim($key)] = trim($value);
        }

        return $mapped;
    }

    /**
     * @return array<int, int>
     */
    private function parseByDayValues(mixed $byDay): array
    {
        if (! is_string($byDay) || trim($byDay) === '') {
            return [];
        }

        $weekDayMap = [
            'MO' => 1,
            'TU' => 2,
            'WE' => 3,
            'TH' => 4,
            'FR' => 5,
            'SA' => 6,
            'SU' => 7,
        ];

        $values = explode(',', strtoupper($byDay));
        $days = [];

        foreach ($values as $value) {
            $normalized = trim($value);

            if (isset($weekDayMap[$normalized])) {
                $days[] = $weekDayMap[$normalized];
            }
        }

        return array_values(array_unique($days));
    }

    private function parseUntilValue(string $until, bool $allDay, string $timeZone): ?CarbonImmutable
    {
        try {
            if ($allDay || preg_match('/^\d{8}$/', $until) === 1) {
                $date = CarbonImmutable::createFromFormat('Ymd', $until, $timeZone);

                return $date instanceof CarbonImmutable ? $date->endOfDay() : null;
            }

            if (str_ends_with($until, 'Z')) {
                $date = CarbonImmutable::createFromFormat('Ymd\\THis\\Z', $until, 'UTC');

                return $date instanceof CarbonImmutable ? $date->setTimezone($timeZone) : null;
            }

            $date = CarbonImmutable::createFromFormat('Ymd\\THis', $until, $timeZone);

            return $date instanceof CarbonImmutable ? $date : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function eventIntersectsRange(
        CarbonImmutable $eventStart,
        ?CarbonImmutable $eventEnd,
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd,
        bool $allDay
    ): bool {
        $effectiveEnd = $eventEnd;

        if (! $effectiveEnd instanceof CarbonImmutable) {
            $effectiveEnd = $allDay ? $eventStart->addDay() : $eventStart->addHour();
        }

        return $eventStart->lessThan($rangeEnd) && $effectiveEnd->greaterThan($rangeStart);
    }

    private function formatEventDate(CarbonImmutable $date, bool $allDay): string
    {
        if ($allDay) {
            return $date->format('Y-m-d');
        }

        return $date->toIso8601String();
    }

    private function decodeIcsText(string $value): string
    {
        return str_replace(
            ['\\n', '\\N', '\\,', '\\;', '\\\\'],
            ["\n", "\n", ',', ';', '\\'],
            $value
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEventPayload(
        string $id,
        string $title,
        CarbonImmutable $start,
        ?CarbonImmutable $end,
        bool $allDay,
        string $calendarId
    ): array {
        $appearance = $this->eventAppearanceForCalendar($calendarId);

        return [
            'id' => $id,
            'title' => $title,
            'start' => $this->formatEventDate($start, $allDay),
            'end' => $end ? $this->formatEventDate($end, $allDay) : null,
            'allDay' => $allDay,
            'display' => $appearance['display'],
            'backgroundColor' => $appearance['background_color'],
            'borderColor' => $appearance['border_color'],
            'textColor' => $appearance['text_color'],
            'classNames' => $appearance['class_names'],
            'extendedProps' => [
                'calendarId' => $calendarId,
                'isHoliday' => $appearance['is_holiday'],
            ],
        ];
    }

    /**
     * @return array{
     *   is_holiday: bool,
     *   display: string,
     *   background_color: string,
     *   border_color: string,
     *   text_color: string,
     *   class_names: array<int, string>
     * }
     */
    private function eventAppearanceForCalendar(string $calendarId): array
    {
        if ($this->isHolidayCalendar($calendarId)) {
            return [
                'is_holiday' => true,
                'display' => 'block',
                'background_color' => '#FDECEA',
                'border_color' => '#E24C4B',
                'text_color' => '#8B1F1E',
                'class_names' => ['holiday-event'],
            ];
        }

        return [
            'is_holiday' => false,
            'display' => 'auto',
            'background_color' => '#E8F1FF',
            'border_color' => '#5A8DEE',
            'text_color' => '#1E3A6E',
            'class_names' => ['default-calendar-event'],
        ];
    }

    private function isHolidayCalendar(string $calendarId): bool
    {
        return str_contains(strtolower($calendarId), '#holiday@group.v.calendar.google.com');
    }

    private function resolveTimeZone(?string $timeZone, string $defaultTimeZone): string
    {
        if (is_string($timeZone) && in_array($timeZone, timezone_identifiers_list(), true)) {
            return $timeZone;
        }

        return $defaultTimeZone;
    }
}
