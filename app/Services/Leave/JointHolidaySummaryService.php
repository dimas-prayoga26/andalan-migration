<?php

namespace App\Services\Leave;

use App\Models\AttendanceHoliday;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JointHolidaySummaryService
{
    /**
     * @return array{label:string, items:list<string>}
     */
    public function forYear(int $year, Carbon $today): array
    {
        $jointHolidays = AttendanceHoliday::query()
            ->whereYear('date', $year)
            ->where('type', 2)
            ->orderBy('date')
            ->get(['date', 'name']);

        return $this->summarize($jointHolidays, $today);
    }

    /**
     * @param  Collection<int, AttendanceHoliday>  $jointHolidays
     * @return array{label:string, items:list<string>}
     */
    public function summarize(Collection $jointHolidays, Carbon $today): array
    {
        $currentDate = $today->copy()->setTimezone('Asia/Jakarta')->startOfDay();
        $totalDays = $jointHolidays->count();
        $passedDays = $jointHolidays
            ->filter(function (AttendanceHoliday $attendanceHoliday) use ($currentDate): bool {
                return $this->holidayDate($attendanceHoliday)->lessThanOrEqualTo($currentDate);
            })
            ->count();

        return [
            'label' => $passedDays.' / '.$totalDays.' '.Str::plural('Day', $totalDays),
            'items' => $this->formatItems($jointHolidays),
        ];
    }

    private function holidayDate(AttendanceHoliday $attendanceHoliday): Carbon
    {
        if ($attendanceHoliday->date instanceof \DateTimeInterface) {
            return Carbon::instance($attendanceHoliday->date)->setTimezone('Asia/Jakarta')->startOfDay();
        }

        return Carbon::parse((string) $attendanceHoliday->date, 'Asia/Jakarta')->startOfDay();
    }

    /**
     * @param  Collection<int, AttendanceHoliday>  $jointHolidays
     * @return list<string>
     */
    private function formatItems(Collection $jointHolidays): array
    {
        $items = $jointHolidays
            ->groupBy(static fn (AttendanceHoliday $attendanceHoliday): string => trim((string) $attendanceHoliday->name) !== '' ? trim((string) $attendanceHoliday->name) : 'Joint Holiday')
            ->map(function (Collection $items, string $holidayName): string {
                $dateLabels = $items
                    ->map(fn (AttendanceHoliday $attendanceHoliday): string => $this->holidayDate($attendanceHoliday)->format('d M'))
                    ->implode(', ');

                return $holidayName.' ('.$dateLabels.')';
            })
            ->values()
            ->all();

        return $items !== [] ? $items : ['No joint holiday scheduled.'];
    }
}
