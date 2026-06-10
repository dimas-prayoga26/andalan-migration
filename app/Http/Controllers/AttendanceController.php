<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceHoliday;
use App\Models\AttendanceLog;
use App\Models\BusinessTrip;
use App\Models\BusinessTripLifecycleLog;
use App\Models\LeaveRequest;
use App\Models\TelegramUser;
use App\Models\User;
use App\Services\Attendance\AttendanceCardsViewDataService;
use App\Services\Attendance\AttendanceMutationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceCardsViewDataService $attendanceCardsViewDataService,
        private AttendanceMutationService $attendanceMutationService
    ) {}

    public function index(Request $request): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $attendanceCardsData = $this->attendanceCardsViewDataService->build(
            $authenticatedUser instanceof User ? $authenticatedUser : null,
            Auth::id(),
            $request
        );
        $employeeId = $attendanceCardsData['employeeId'];
        $attendanceHistoryEvents = [];
        $calendarLabelEvents = [];
        $holidayEvents = AttendanceHoliday::query()
            ->orderBy('date')
            ->get(['date', 'name', 'type'])
            ->map(static function (AttendanceHoliday $attendanceHoliday): ?array {
                $holidayDateLabel = $attendanceHoliday->date?->format('Y-m-d');
                $holidayNameLabel = is_string($attendanceHoliday->name) ? trim($attendanceHoliday->name) : '';
                if (! is_string($holidayDateLabel) || trim($holidayDateLabel) === '' || $holidayNameLabel === '') {
                    return null;
                }

                $holidayType = (int) ($attendanceHoliday->type ?? 1);
                $isNationalHoliday = $holidayType === 1;
                $eventTypeLabel = $isNationalHoliday ? 'Hari Libur Nasional' : 'Cuti Bersama';
                $eventBackgroundColor = $isNationalHoliday ? '#dc3545' : '#cd5c5c';

                return [
                    'title' => $holidayNameLabel,
                    'start' => $holidayDateLabel,
                    'allDay' => true,
                    'classNames' => [$isNationalHoliday ? 'fc-national-holiday-card' : 'fc-joint-leave-card'],
                    'backgroundColor' => $eventBackgroundColor,
                    'borderColor' => $eventBackgroundColor,
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'dayOffEventType' => $eventTypeLabel,
                        'dayOffHolidayName' => $holidayNameLabel,
                        'dayOffDate' => $holidayDateLabel,
                    ],
                ];
            })
            ->filter(static fn (mixed $eventItem): bool => is_array($eventItem))
            ->values()
            ->all();

        if (is_string($employeeId) && trim($employeeId) !== '') {
            $attendanceHistoryRecords = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereNotNull('clock_in')
                ->with([
                    'attendanceException:id,attendance_id,exception_date,type,note,from_time,to_time,status',
                ])
                ->orderBy('date')
                ->get(['id', 'date', 'clock_in', 'clock_out', 'late_minutes', 'status']);
            $attendanceLogsByAttendanceId = AttendanceLog::query()
                ->whereIn('attendance_id', $attendanceHistoryRecords->pluck('id')->filter()->all())
                ->where('type', true)
                ->orderByDesc('created_at')
                ->get([
                    'attendance_id',
                    'location',
                    'latitude',
                    'longitude',
                ])
                ->groupBy('attendance_id')
                ->map(static fn ($attendanceLogs) => $attendanceLogs->first());
            $officeLocation = $attendanceCardsData['officeLocation'] ?? null;
            $officeStartTimeLabel = $this->formatAttendanceTimeLabel(
                is_array($officeLocation) ? ($officeLocation['office_start_time'] ?? null) : null,
                '08:00'
            );
            $officeEndTimeLabel = $this->formatAttendanceTimeLabel(
                is_array($officeLocation) ? ($officeLocation['office_end_time'] ?? null) : null,
                '17:00'
            );
            $officeScheduleLabel = $officeStartTimeLabel.' - '.$officeEndTimeLabel;

            $attendanceHistoryEvents = $attendanceHistoryRecords
                ->map(function (Attendance $attendanceItem) use ($attendanceLogsByAttendanceId, $officeScheduleLabel): ?array {
                    $attendanceDateLabel = $attendanceItem->date?->format('Y-m-d');
                    $clockInLabel = $attendanceItem->clock_in?->format('H:i');
                    if (! is_string($attendanceDateLabel) || trim($attendanceDateLabel) === '' || ! is_string($clockInLabel) || trim($clockInLabel) === '') {
                        return null;
                    }

                    $clockOutLabel = $attendanceItem->clock_out?->format('H:i') ?? '-';
                    $normalizedStatus = is_string($attendanceItem->status)
                        ? strtolower(trim($attendanceItem->status))
                        : '';
                    $attendanceException = $attendanceItem->attendanceException;
                    $exceptionType = is_string($attendanceException?->type)
                        ? strtolower(trim($attendanceException->type))
                        : '';
                    $hasAttendanceExceptionColorOverride = in_array($exceptionType, ['early_departure', 'late_arrival'], true);
                    $lateMinutes = (int) ($attendanceItem->late_minutes ?? 0);
                    $isLate = $lateMinutes > 0 || $normalizedStatus === 'terlambat';
                    $attendanceModalId = 'onTime';
                    $attendanceEventType = 'on_time';
                    $attendanceTitle = 'On Time';
                    $eventBackgroundColor = '#20c997';
                    $eventBorderColor = '#1aa179';
                    $attendanceLog = $attendanceLogsByAttendanceId->get($attendanceItem->id);
                    $attendanceLog = $attendanceLog instanceof AttendanceLog ? $attendanceLog : null;

                    if ($hasAttendanceExceptionColorOverride) {
                        $attendanceModalId = 'deviation';
                        $attendanceEventType = $exceptionType;
                        $attendanceTitle = $attendanceException instanceof AttendanceException
                            ? $this->formatAttendanceExceptionModalTitle($attendanceException)
                            : 'Attendance Exception';
                        $eventBackgroundColor = '#17a2b8';
                        $eventBorderColor = '#117a8b';
                    } elseif ($isLate) {
                        $attendanceModalId = 'late';
                        $attendanceEventType = 'late';
                        $attendanceTitle = $lateMinutes > 0 ? 'Late '.$lateMinutes.' Minutes' : 'Late Arrival';
                        $eventBackgroundColor = '#fd7e14';
                        $eventBorderColor = '#d3680d';
                    }

                    return [
                        'title' => 'In : '.$clockInLabel.' - Out : '.$clockOutLabel,
                        'start' => $attendanceDateLabel,
                        'allDay' => true,
                        'classNames' => ['fc-attendance-log-card'],
                        'backgroundColor' => $eventBackgroundColor,
                        'borderColor' => $eventBorderColor,
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'attendanceModalId' => $attendanceModalId,
                            'attendanceEventType' => $attendanceEventType,
                            'modalTitle' => $attendanceTitle,
                            'attendanceDate' => $attendanceDateLabel,
                            'attendanceDateLabel' => $attendanceItem->date?->translatedFormat('d M Y') ?? $attendanceDateLabel,
                            'clockIn' => $clockInLabel,
                            'clockOut' => $clockOutLabel,
                            'clockInSchedule' => $this->formatAttendanceTimeWithSchedule($clockInLabel, $officeScheduleLabel),
                            'clockOutSchedule' => $this->formatAttendanceTimeWithSchedule($clockOutLabel, $officeScheduleLabel),
                            'attendanceStatusLabel' => $this->formatCalendarAttendanceStatusLabel($attendanceEventType, $attendanceTitle, $lateMinutes),
                            'locationName' => $this->formatAttendanceLocationName($attendanceLog),
                            'locationAddress' => $this->formatAttendanceLocationAddress($attendanceLog),
                            'deviationIntroTitle' => $attendanceException instanceof AttendanceException ? $this->formatAttendanceExceptionIntroTitle($attendanceException) : '',
                            'deviationIntroPrimary' => $attendanceException instanceof AttendanceException ? $this->formatAttendanceExceptionIntroPrimary($attendanceException) : '',
                            'deviationIntroSecondary' => $attendanceException instanceof AttendanceException ? $this->formatAttendanceExceptionIntroSecondary($attendanceException) : '',
                            'requestTypeLabel' => $attendanceException instanceof AttendanceException ? $this->formatAttendanceExceptionRequestTypeLabel($attendanceException) : '-',
                            'reason' => $attendanceException instanceof AttendanceException && is_string($attendanceException->note) && trim($attendanceException->note) !== ''
                                ? trim($attendanceException->note)
                                : '-',
                            'timeVarianceLabel' => $attendanceException instanceof AttendanceException ? $this->formatAttendanceExceptionTimeVarianceLabel($attendanceException) : '-',
                            'exceptionStatusLabel' => $attendanceException instanceof AttendanceException ? $this->formatAttendanceExceptionStatusLabel($attendanceException) : '-',
                            'exceptionStatusDateLabel' => $attendanceException instanceof AttendanceException ? $this->formatAttendanceExceptionStatusDateLabel($attendanceException) : '-',
                        ],
                    ];
                })
                ->filter(static fn (mixed $eventItem): bool => is_array($eventItem))
                ->values()
                ->all();
            $calendarLabelEvents = array_merge(
                $this->buildLeaveCalendarEvents($employeeId),
                $this->buildBusinessTripCalendarEvents($employeeId)
            );
        }

        return view('attendance.attendance.index', array_merge(
            $attendanceCardsData,
            [
                'attendanceHistoryEvents' => $attendanceHistoryEvents,
                'calendarLabelEvents' => $calendarLabelEvents,
                'holidayEvents' => $holidayEvents,
            ]
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildLeaveCalendarEvents(string $employeeId): array
    {
        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where('is_active', true)
            ->whereIn('status', ['pending', 'approved'])
            ->whereNull('deleted_at')
            ->with('leaveType:id,name,code')
            ->orderBy('start_date')
            ->get(['id', 'employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'reason', 'status', 'is_active', 'attachment_path', 'approved_at', 'created_at'])
            ->map(function (LeaveRequest $leaveRequest): ?array {
                $startDate = $this->formatCalendarDate($leaveRequest->start_date);
                $endDate = $this->formatCalendarDate($leaveRequest->end_date);

                if ($startDate === null) {
                    return null;
                }

                $title = $this->formatCalendarLeaveTypeLabel($leaveRequest);
                $appearance = $this->calendarLabelAppearance($title);
                $event = [
                    'title' => $title,
                    'start' => $startDate,
                    'allDay' => true,
                    'classNames' => ['fc-calendar-label-card'],
                    'backgroundColor' => $appearance['backgroundColor'],
                    'borderColor' => $appearance['borderColor'],
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'calendarLabelType' => 'leave',
                        'calendarLabelId' => $leaveRequest->id,
                        'calendarModalId' => $this->calendarLeaveModalId($title),
                        'calendarModalTitle' => $title === 'Sick Leave' ? 'Attendance Sick' : $title,
                        'leaveTypeLabel' => $title,
                        'leaveReason' => $this->formatCalendarText($leaveRequest->reason),
                        'leaveDurationLabel' => $this->formatCalendarDurationLabel($startDate, $endDate, (int) ($leaveRequest->total_days ?? 0)),
                        'leaveStatusLabel' => $this->formatCalendarStatusLabel($leaveRequest->status),
                        'leaveStatusDateLabel' => $this->formatCalendarStatusDateLabel(
                            $this->calendarStatusDateValue($leaveRequest->status, $leaveRequest->approved_at, $leaveRequest->created_at)
                        ),
                        'leaveStatusTextClass' => $this->calendarStatusTextClass($leaveRequest->status),
                        'medicalNotesUrl' => $this->formatLeaveAttachmentUrl($leaveRequest),
                        'medicalNotesIsImage' => $this->leaveAttachmentIsImage($leaveRequest),
                    ],
                ];

                if ($endDate !== null && $endDate !== $startDate) {
                    $event['end'] = Carbon::parse($endDate, 'Asia/Jakarta')->addDay()->toDateString();
                }

                return $event;
            })
            ->filter(static fn (mixed $eventItem): bool => is_array($eventItem))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildBusinessTripCalendarEvents(string $employeeId): array
    {
        return BusinessTrip::query()
            ->where('employee_id', $employeeId)
            ->where('approval_status', 'approved')
            ->with(['lifecycleLogs:id,business_trip_id,event_key,status'])
            ->orderBy('start_date')
            ->get(['id', 'employee_id', 'start_date', 'end_date', 'total_days', 'destination_zone', 'province_destination', 'city_destination', 'purpose', 'approval_status', 'approved_at'])
            ->map(function (BusinessTrip $businessTrip): ?array {
                $startDate = $businessTrip->start_date?->format('Y-m-d');
                $endDate = $businessTrip->end_date?->format('Y-m-d');

                if (! is_string($startDate) || trim($startDate) === '') {
                    return null;
                }

                $appearance = $this->calendarLabelAppearance('Business Trip');
                $canSubmitTask = $this->businessTripLifecycleEventHasStatus($businessTrip, 'supervisor_review', 'complete');
                $canRequestReimbursement = $this->businessTripLifecycleEventHasStatus($businessTrip, 'reimbursement_submitted', 'pending');
                $event = [
                    'title' => 'Business Trip',
                    'start' => $startDate,
                    'allDay' => true,
                    'classNames' => ['fc-calendar-label-card'],
                    'backgroundColor' => $appearance['backgroundColor'],
                    'borderColor' => $appearance['borderColor'],
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'calendarLabelType' => 'business_trip',
                        'calendarLabelId' => $businessTrip->id,
                        'calendarModalId' => 'trip',
                        'calendarModalTitle' => 'Business Trip',
                        'activityTypeLabel' => 'Business Trip',
                        'tripPurpose' => $this->formatCalendarText($businessTrip->purpose),
                        'tripDestination' => $this->formatBusinessTripDestination($businessTrip),
                        'tripDurationLabel' => $this->formatCalendarDurationLabel($startDate, $endDate, (int) ($businessTrip->total_days ?? 0)),
                        'tripStatusLabel' => $this->formatCalendarStatusLabel($businessTrip->approval_status),
                        'tripStatusDateLabel' => $this->formatCalendarStatusDateLabel($businessTrip->approved_at),
                        'tripStatusTextClass' => $this->calendarStatusTextClass($businessTrip->approval_status),
                        'tripSubmitTaskUrl' => route('attendance.business-trips.cash-advances.create', $businessTrip),
                        'tripReimbursementUrl' => route('attendance.business-trips.reimbursements.create', $businessTrip),
                        'tripCanSubmitTask' => $canSubmitTask,
                        'tripCanRequestReimbursement' => $canRequestReimbursement,
                        'tripSubmitTaskDisabledLabel' => 'Available after supervisor review is complete.',
                        'tripReimbursementDisabledLabel' => 'Available when reimbursement submission is pending.',
                    ],
                ];

                if (is_string($endDate) && trim($endDate) !== '' && $endDate !== $startDate) {
                    $event['end'] = Carbon::parse($endDate, 'Asia/Jakarta')->addDay()->toDateString();
                }

                return $event;
            })
            ->filter(static fn (mixed $eventItem): bool => is_array($eventItem))
            ->values()
            ->all();
    }

    private function formatCalendarLeaveTypeLabel(LeaveRequest $leaveRequest): string
    {
        $leaveType = $leaveRequest->leaveType;
        $leaveTypeName = is_string($leaveType?->name) ? trim($leaveType->name) : '';
        $leaveTypeCode = is_string($leaveType?->code) ? strtolower(trim($leaveType->code)) : '';
        $normalizedLeaveTypeName = strtolower($leaveTypeName);

        if (in_array($leaveTypeCode, ['annual', 'annual_leave'], true) || in_array($normalizedLeaveTypeName, ['annual leave', 'cuti tahunan'], true)) {
            return 'Annual Leave';
        }

        if (in_array($leaveTypeCode, ['sick', 'sick_leave'], true) || in_array($normalizedLeaveTypeName, ['sick leave', 'sakit'], true)) {
            return 'Sick Leave';
        }

        if (in_array($leaveTypeCode, ['special', 'special_leave'], true) || in_array($normalizedLeaveTypeName, ['special leave', 'cuti khusus'], true)) {
            return 'Special Leave';
        }

        if (in_array($leaveTypeCode, ['unpaid', 'unpaid_leave'], true) || in_array($normalizedLeaveTypeName, ['unpaid leave', 'cuti tidak dibayar'], true)) {
            return 'Unpaid Leave';
        }

        return $leaveTypeName !== '' ? $leaveTypeName : 'Leave';
    }

    private function calendarLeaveModalId(string $leaveTypeLabel): string
    {
        return match ($leaveTypeLabel) {
            'Annual Leave' => 'annualLeave',
            'Special Leave' => 'specialLeave',
            'Unpaid Leave' => 'unpaidLeave',
            'Sick Leave' => 'sick',
            default => 'annualLeave',
        };
    }

    private function formatCalendarText(mixed $value): string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : '-';
    }

    private function formatCalendarDurationLabel(?string $startDate, ?string $endDate, int $totalDays): string
    {
        if ($startDate === null) {
            return '-';
        }

        $startDateLabel = $this->formatCalendarDateLabel($startDate);
        $endDateLabel = $endDate !== null ? $this->formatCalendarDateLabel($endDate) : $startDateLabel;
        $resolvedTotalDays = $totalDays > 0 ? $totalDays : $this->calendarDurationDays($startDate, $endDate);
        $durationUnit = $resolvedTotalDays === 1 ? 'day' : 'days';
        $durationLabel = ' ('.$resolvedTotalDays.' '.$durationUnit.')';

        if ($endDate === null || $endDate === $startDate) {
            return $startDateLabel.$durationLabel;
        }

        return $startDateLabel.' - '.$endDateLabel.$durationLabel;
    }

    private function calendarDurationDays(string $startDate, ?string $endDate): int
    {
        try {
            $startDateValue = Carbon::parse($startDate, 'Asia/Jakarta')->startOfDay();
            $endDateValue = Carbon::parse($endDate ?? $startDate, 'Asia/Jakarta')->startOfDay();
        } catch (\Throwable) {
            return 1;
        }

        return max((int) $startDateValue->diffInDays($endDateValue) + 1, 1);
    }

    private function formatCalendarDateLabel(mixed $date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance($date)->timezone('Asia/Jakarta')->format('d M Y');
        }

        if (! is_string($date) || trim($date) === '') {
            return '-';
        }

        try {
            return Carbon::parse($date, 'Asia/Jakarta')->format('d M Y');
        } catch (\Throwable) {
            return trim($date);
        }
    }

    private function calendarStatusDateValue(mixed $status, mixed $approvedAt, mixed $createdAt): mixed
    {
        $normalizedStatus = is_string($status) ? strtolower(trim($status)) : '';

        return $normalizedStatus === 'approved' ? $approvedAt : $createdAt;
    }

    private function formatCalendarStatusDateLabel(mixed $date): string
    {
        $dateLabel = $this->formatCalendarDateLabel($date);

        return $dateLabel !== '-' ? 'on '.$dateLabel : '';
    }

    private function formatCalendarStatusLabel(mixed $status): string
    {
        $normalizedStatus = is_string($status) ? strtolower(trim($status)) : '';

        return match ($normalizedStatus) {
            'approved' => 'Approved',
            'pending' => 'Pending',
            'rejected', 'refused' => 'Rejected',
            default => $normalizedStatus !== '' ? str($normalizedStatus)->replace('_', ' ')->title()->toString() : '-',
        };
    }

    private function calendarStatusTextClass(mixed $status): string
    {
        $normalizedStatus = is_string($status) ? strtolower(trim($status)) : '';

        return match ($normalizedStatus) {
            'approved' => 'text-success',
            'pending' => 'text-warning',
            'rejected', 'refused' => 'text-danger',
            default => 'text-gray',
        };
    }

    private function formatLeaveAttachmentUrl(LeaveRequest $leaveRequest): string
    {
        $attachmentPath = is_string($leaveRequest->attachment_path) ? trim($leaveRequest->attachment_path) : '';

        return $attachmentPath !== '' ? Storage::disk('public')->url($attachmentPath) : '';
    }

    private function leaveAttachmentIsImage(LeaveRequest $leaveRequest): bool
    {
        $attachmentPath = is_string($leaveRequest->attachment_path) ? strtolower(trim($leaveRequest->attachment_path)) : '';

        return $attachmentPath !== '' && in_array(pathinfo($attachmentPath, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
    }

    private function formatBusinessTripDestination(BusinessTrip $businessTrip): string
    {
        $destinationParts = collect([
            $businessTrip->city_destination,
            $businessTrip->province_destination,
            $businessTrip->destination_zone,
        ])
            ->filter(static fn (mixed $destinationPart): bool => is_string($destinationPart) && trim($destinationPart) !== '')
            ->map(static fn (string $destinationPart): string => trim($destinationPart))
            ->unique()
            ->values();

        return $destinationParts->isNotEmpty() ? $destinationParts->implode(', ') : '-';
    }

    private function businessTripLifecycleEventHasStatus(BusinessTrip $businessTrip, string $eventKey, string $expectedStatus): bool
    {
        $expectedStatus = strtolower(trim($expectedStatus));

        return $businessTrip->lifecycleLogs
            ->contains(function (BusinessTripLifecycleLog $lifecycleLog) use ($eventKey, $expectedStatus): bool {
                return (string) $lifecycleLog->event_key === $eventKey
                    && strtolower(trim((string) $lifecycleLog->status)) === $expectedStatus;
            });
    }

    /**
     * @return array{backgroundColor: string, borderColor: string}
     */
    private function calendarLabelAppearance(string $title): array
    {
        return match ($title) {
            'Special Leave' => ['backgroundColor' => '#d63384', 'borderColor' => '#a82767'],
            'Sick Leave' => ['backgroundColor' => '#0d6efd', 'borderColor' => '#0a58ca'],
            'Unpaid Leave' => ['backgroundColor' => '#6c757d', 'borderColor' => '#5a6268'],
            'Annual Leave' => ['backgroundColor' => '#6f42c1', 'borderColor' => '#59339d'],
            'Business Trip' => ['backgroundColor' => '#0dcaf0', 'borderColor' => '#0aa2c0'],
            default => ['backgroundColor' => '#198754', 'borderColor' => '#146c43'],
        };
    }

    private function formatCalendarDate(mixed $date): ?string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        if (! is_string($date) || trim($date) === '') {
            return null;
        }

        try {
            return Carbon::parse($date, 'Asia/Jakarta')->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatAttendanceTimeWithSchedule(string $timeLabel, string $officeScheduleLabel): string
    {
        if (trim($timeLabel) === '') {
            return '-';
        }

        return $timeLabel.' ('.$officeScheduleLabel.')';
    }

    private function formatCalendarAttendanceStatusLabel(string $attendanceEventType, string $attendanceTitle, int $lateMinutes): string
    {
        if ($attendanceEventType === 'on_time') {
            return 'On-Time Arrival';
        }

        if ($attendanceEventType === 'late') {
            return $lateMinutes > 0 ? 'Late '.$lateMinutes.' Minutes' : 'Late Arrival';
        }

        return $attendanceTitle;
    }

    private function formatAttendanceLocationName(?AttendanceLog $attendanceLog): string
    {
        $locationParts = $this->formatAttendanceLocationParts($attendanceLog);
        if ($locationParts !== []) {
            return $locationParts[0];
        }

        if ($this->formatAttendanceLocationCoordinatesLabel($attendanceLog) !== null) {
            return 'Koordinat Absensi';
        }

        return 'Location not available';
    }

    private function formatAttendanceLocationAddress(?AttendanceLog $attendanceLog): string
    {
        $locationParts = $this->formatAttendanceLocationParts($attendanceLog);
        if ($locationParts !== []) {
            return implode(', ', $locationParts);
        }

        $coordinatesLabel = $this->formatAttendanceLocationCoordinatesLabel($attendanceLog);
        if ($coordinatesLabel !== null) {
            return $coordinatesLabel;
        }

        return $this->formatAttendanceLocationName($attendanceLog);
    }

    private function formatAttendanceLocationCoordinatesLabel(?AttendanceLog $attendanceLog): ?string
    {
        if (! isset($attendanceLog?->latitude, $attendanceLog?->longitude)) {
            return null;
        }

        $latitude = (float) $attendanceLog->latitude;
        $longitude = (float) $attendanceLog->longitude;

        if (! $this->isValidCoordinate($latitude, $longitude)) {
            return null;
        }

        return number_format($latitude, 7, '.', '').', '.number_format($longitude, 7, '.', '');
    }

    /**
     * @return list<string>
     */
    private function formatAttendanceLocationParts(?AttendanceLog $attendanceLog): array
    {
        $location = is_string($attendanceLog?->location) ? trim($attendanceLog->location) : '';
        if ($location === '' || str_starts_with(strtolower($location), 'https://www.google.com/maps')) {
            return [];
        }

        return collect(explode(',', $location))
            ->map(static function (string $locationPart): string {
                $cleanLocationPart = preg_replace('/\b\d{5}(?:-\d{4})?\b/', '', $locationPart) ?? $locationPart;
                $cleanLocationPart = preg_replace('/\bindonesia\b/i', '', $cleanLocationPart) ?? $cleanLocationPart;

                return trim($cleanLocationPart, " \t\n\r\0\x0B,.-");
            })
            ->filter(static fn (string $locationPart): bool => $locationPart !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function formatAttendanceExceptionModalTitle(AttendanceException $attendanceException): string
    {
        $exceptionType = is_string($attendanceException->type) ? strtolower(trim($attendanceException->type)) : '';

        return match ($exceptionType) {
            'late_arrival' => 'Permitted Late Arrival',
            'early_departure' => 'Early Departure',
            default => 'Attendance Exception',
        };
    }

    private function formatAttendanceExceptionIntroTitle(AttendanceException $attendanceException): string
    {
        $exceptionType = is_string($attendanceException->type) ? strtolower(trim($attendanceException->type)) : '';

        return match ($exceptionType) {
            'late_arrival' => 'Thanks for the heads-up!',
            'early_departure' => 'Wrapping up early today',
            default => '',
        };
    }

    private function formatAttendanceExceptionIntroPrimary(AttendanceException $attendanceException): string
    {
        $exceptionType = is_string($attendanceException->type) ? strtolower(trim($attendanceException->type)) : '';

        return match ($exceptionType) {
            'late_arrival' => 'We know things happen. Travel safely, and just jump right into your tasks whenever you get settled at your desk.',
            'early_departure' => 'Before you head out, just make sure any urgent tasks are handed over or completed. Have a great rest of your day!',
            default => '',
        };
    }

    private function formatAttendanceExceptionIntroSecondary(AttendanceException $attendanceException): string
    {
        return '';
    }

    private function formatAttendanceExceptionRequestTypeLabel(AttendanceException $attendanceException): string
    {
        $exceptionType = is_string($attendanceException->type) ? strtolower(trim($attendanceException->type)) : '';

        return match ($exceptionType) {
            'late_arrival' => 'Permitted Late Arrival',
            'early_departure' => 'Early Departure',
            default => '-',
        };
    }

    private function formatAttendanceExceptionTimeVarianceLabel(AttendanceException $attendanceException): string
    {
        $fromTime = $this->normalizeAttendanceExceptionTime($attendanceException->getRawOriginal('from_time'))
            ?? $this->normalizeAttendanceExceptionTime($attendanceException->from_time);
        $toTime = $this->normalizeAttendanceExceptionTime($attendanceException->getRawOriginal('to_time'))
            ?? $this->normalizeAttendanceExceptionTime($attendanceException->to_time);

        if ($fromTime === null || $toTime === null) {
            return '-';
        }

        $durationLabel = $this->formatAttendanceExceptionDurationLabel($attendanceException, $fromTime, $toTime);
        $fromTimeLabel = substr($fromTime, 0, 5);
        $toTimeLabel = substr($toTime, 0, 5);
        $exceptionType = is_string($attendanceException->type) ? strtolower(trim($attendanceException->type)) : '';

        if ($durationLabel === '') {
            return $fromTimeLabel.' - '.$toTimeLabel;
        }

        if ($exceptionType === 'late_arrival') {
            return trim($fromTimeLabel.' - '.$toTimeLabel.' (Terlambat '.$durationLabel.')');
        }

        if ($exceptionType === 'early_departure') {
            return trim($fromTimeLabel.' - '.$toTimeLabel.' (Pulang '.$durationLabel.' Lebih Awal)');
        }

        return $fromTimeLabel.' - '.$toTimeLabel;
    }

    private function formatAttendanceExceptionDurationLabel(AttendanceException $attendanceException, string $fromTime, string $toTime): string
    {
        $exceptionDate = $attendanceException->exception_date?->format('Y-m-d') ?? now('Asia/Jakarta')->toDateString();

        try {
            $fromDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $exceptionDate.' '.$fromTime, 'Asia/Jakarta');
            $toDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $exceptionDate.' '.$toTime, 'Asia/Jakarta');
        } catch (\Throwable) {
            return '';
        }

        $minutes = abs((int) $fromDateTime->diffInMinutes($toDateTime, false));
        if ($minutes <= 0) {
            return '';
        }

        $hoursPart = intdiv($minutes, 60);
        $minutesPart = $minutes % 60;
        $segments = [];

        if ($hoursPart > 0) {
            $segments[] = $hoursPart.' Jam';
        }

        if ($minutesPart > 0) {
            $segments[] = $minutesPart.' Menit';
        }

        return implode(' ', $segments);
    }

    private function formatAttendanceExceptionStatusLabel(AttendanceException $attendanceException): string
    {
        $status = is_string($attendanceException->status) ? trim($attendanceException->status) : '';

        return $status !== '' ? str($status)->replace('_', ' ')->title()->toString() : '-';
    }

    private function formatAttendanceExceptionStatusDateLabel(AttendanceException $attendanceException): string
    {
        return $attendanceException->exception_date?->format('d M Y') ?? '-';
    }

    private function formatAttendanceTimeLabel(mixed $time, string $fallback): string
    {
        $normalizedTime = $this->normalizeAttendanceExceptionTime($time);

        return $normalizedTime !== null ? substr($normalizedTime, 0, 5) : $fallback;
    }

    private function normalizeAttendanceExceptionTime(mixed $time): ?string
    {
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i:s');
        }

        if (! is_string($time) || trim($time) === '') {
            return null;
        }

        $normalizedTime = trim($time);
        if (preg_match('/^\d{2}:\d{2}$/', $normalizedTime) === 1) {
            return $normalizedTime.':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $normalizedTime) === 1) {
            return $normalizedTime;
        }

        return null;
    }

    private function isValidCoordinate(float $latitude, float $longitude): bool
    {
        return $latitude >= -90
            && $latitude <= 90
            && $longitude >= -180
            && $longitude <= 180
            && ! (abs($latitude) < 0.000001 && abs($longitude) < 0.000001);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $storeResult = $this->attendanceMutationService->store(
                $request,
                Auth::user(),
                Auth::id(),
            );

            return response()->json($storeResult['payload'], $storeResult['status']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absen masuk.',
            ], 500);
        }
    }

    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        try {
            $updateResult = $this->attendanceMutationService->update(
                $request,
                $attendance,
                Auth::user(),
                Auth::id(),
            );

            return response()->json($updateResult['payload'], $updateResult['status']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absen pulang.',
            ], 500);
        }
    }

    public function currentIp(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $publicIp = '-';
        $officeLocation = $this->attendanceMutationService->resolveOfficeContext($userId);
        $clientIpAddress = $this->attendanceMutationService->resolveClientIpAddress($request, $request->query('client_ip'));
        $ipdataData = $this->attendanceMutationService->fetchIpdata($clientIpAddress);

        if (! empty($ipdataData['ip'])) {
            $publicIp = (string) $ipdataData['ip'];
        }

        $allowedIpRange = is_array($officeLocation) ? ($officeLocation['ip_range'] ?? null) : null;
        $publicIpPrefix = $this->attendanceMutationService->extractIpTwoOctets($publicIp);
        $allowedIpPrefix = $this->attendanceMutationService->extractIpTwoOctets($allowedIpRange);
        $isIpPrefixMatch = $publicIpPrefix !== null
            && $allowedIpPrefix !== null
            && $publicIpPrefix === $allowedIpPrefix;

        return response()->json([
            'ip' => $publicIp,
            'public_ip_prefix' => $publicIpPrefix,
            'allowed_ip_prefix' => $allowedIpPrefix,
            'is_ip_prefix_match' => $isIpPrefixMatch,
        ]);
    }

    public function verifyTelegramUsername(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $authenticatedUser instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        $authenticatedUser->loadMissing('employee.telegramUser');
        $employeeId = $authenticatedUser?->employee?->id;
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee untuk user ini belum tersedia.',
            ], 422);
        }

        if ($authenticatedUser->is_telegram_verified) {
            if ($authenticatedUser->employee?->telegramUser instanceof TelegramUser) {
                return response()->json([
                    'success' => true,
                    'message' => 'Verifikasi Telegram berhasil.',
                ]);
            }

            $authenticatedUser->forceFill([
                'is_telegram_verified' => false,
            ])->save();

            return response()->json([
                'success' => false,
                'message' => 'Data Telegram user tidak ditemukan. Silakan verifikasi ulang.',
            ], 422);
        }

        if ($authenticatedUser->employee?->telegramUser instanceof TelegramUser) {
            $authenticatedUser->forceFill([
                'is_telegram_verified' => true,
            ])->save();

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi Telegram berhasil.',
            ]);
        }

        // Temporary disabled:
        // Skip Telegram API verification + persistence flow for now.
        // Remove this return to re-enable verification flow below.
        return response()->json([
            'success' => true,
            'message' => 'Verifikasi Telegram sementara dinonaktifkan. Lanjut verifikasi geofencing.',
        ]);

        /*
        // Temporary disabled:
        // Enforce app username requirement before matching Telegram account.
        // Uncomment this block when strict username binding is needed again.
        // $applicationUsername = is_string($authenticatedUser->username) ? trim($authenticatedUser->username) : '';
        // if ($applicationUsername === '') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Username akun aplikasi belum tersedia.',
        //     ], 422);
        // }

        $botToken = config('services.telegram.bot_token');
        if (! is_string($botToken) || trim($botToken) === '') {
            return response()->json([
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN belum diset.',
            ], 422);
        }

        try {
            $telegramResponse = Http::connectTimeout(8)
                ->timeout(20)
                ->retry(3, 500)
                ->withOptions([
                    'version' => 1.1,
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    ],
                ])
                ->acceptJson()
                ->get("https://api.telegram.org/bot{$botToken}/getUpdates");

            if (! $telegramResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data update Telegram.',
                ], 422);
            }

            $payload = $telegramResponse->json();
            if (! is_array($payload)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payload Telegram tidak valid.',
                ], 422);
            }

            $updates = isset($payload['result']) && is_array($payload['result']) ? $payload['result'] : [];
            $matchedFrom = null;
            $matchedChat = null;

            foreach ($updates as $update) {
                if (! is_array($update)) {
                    continue;
                }

                $message = $update['message'] ?? $update['edited_message'] ?? null;
                if (! is_array($message)) {
                    continue;
                }

                $from = $message['from'] ?? null;
                if (! is_array($from)) {
                    continue;
                }

                $telegramUsername = isset($from['username']) && is_string($from['username']) ? trim($from['username']) : '';
                if ($telegramUsername === '') {
                    continue;
                }

                // Temporary disabled:
                // Strict Telegram username matching with app username.
                // Uncomment this block when strict username binding is needed again.
                // if (mb_strtolower($telegramUsername) !== mb_strtolower($applicationUsername)) {
                //     continue;
                // }

                $matchedFrom = $from;
                $matchedChat = isset($message['chat']) && is_array($message['chat']) ? $message['chat'] : null;
            }

            if (! is_array($matchedFrom)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username Telegram belum ditemukan. Silakan kirim /start ke bot terlebih dahulu.',
                ], 422);
            }

            TelegramUser::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'chat_id' => is_array($matchedChat) && isset($matchedChat['id']) ? (string) $matchedChat['id'] : null,
                    'first_name' => isset($matchedFrom['first_name']) && is_string($matchedFrom['first_name']) ? $matchedFrom['first_name'] : null,
                    'last_name' => isset($matchedFrom['last_name']) && is_string($matchedFrom['last_name']) ? $matchedFrom['last_name'] : null,
                    'username' => isset($matchedFrom['username']) && is_string($matchedFrom['username']) ? $matchedFrom['username'] : null,
                    'language_code' => isset($matchedFrom['language_code']) && is_string($matchedFrom['language_code']) ? $matchedFrom['language_code'] : null,
                ]
            );

            $authenticatedUser->forceFill([
                'is_telegram_verified' => true,
            ])->save();

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi Telegram berhasil.',
            ]);
        } catch (\Throwable $throwable) {
            $errorMessage = 'Terjadi kesalahan saat verifikasi Telegram.';
            if (str_contains($throwable->getMessage(), 'cURL error 35')) {
                $errorMessage = 'Koneksi ke Telegram terputus. Silakan coba lagi.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 500);
        }
        */
    }

    public function storeException(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee');
        }

        $employeeId = $authenticatedUser?->employee?->id;
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data employee belum tersedia untuk user ini.',
            ], 422);
        }

        $validatedData = $request->validate([
            'type' => ['required', 'in:late_arrival,early_departure'],
            'note' => ['nullable', 'string', 'max:1000'],
            'from_time' => ['nullable', 'date_format:H:i'],
            'to_time' => ['nullable', 'date_format:H:i'],
            'exception_date' => ['nullable', 'date'],
        ]);

        try {
            return $this->attendanceMutationService->storeException(
                $validatedData,
                $employeeId,
                Auth::id(),
            );
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses attendance exception.',
            ], 500);
        }
    }
}
