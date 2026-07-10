<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceHoliday;
use App\Models\AttendanceLog;
use App\Models\BusinessTrip;
use App\Models\LeaveRequest;
use App\Support\Attendance\AttendanceCalendarPresenter;
use App\Support\Attendance\AttendanceDurationFormatter;
use App\Support\Attendance\AttendanceExceptionPresenter;
use App\Support\Attendance\AttendanceLocationFormatter;
use Illuminate\Support\Carbon;

class AttendanceCalendarEventService
{
    public function __construct(
        private AttendanceCalendarPresenter $presenter,
        private AttendanceDurationFormatter $durationFormatter,
        private AttendanceExceptionPresenter $exceptionPresenter,
        private AttendanceLocationFormatter $locationFormatter
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function buildHolidayEvents(): array
    {
        return AttendanceHoliday::query()
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
    }

    /**
     * @param  array<string, mixed>|null  $officeLocation
     * @return array{attendanceHistoryEvents:list<array<string,mixed>>,calendarLabelEvents:list<array<string,mixed>>}
     */
    public function buildEmployeeEvents(string $employeeId, ?array $officeLocation): array
    {
        return [
            'attendanceHistoryEvents' => $this->buildAttendanceHistoryEvents($employeeId, $officeLocation),
            'calendarLabelEvents' => array_merge(
                $this->buildLeaveCalendarEvents($employeeId),
                $this->buildBusinessTripCalendarEvents($employeeId)
            ),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $officeLocation
     * @return list<array<string, mixed>>
     */
    private function buildAttendanceHistoryEvents(string $employeeId, ?array $officeLocation): array
    {
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
        $officeStartTimeLabel = $this->presenter->timeLabel(
            is_array($officeLocation) ? ($officeLocation['office_start_time'] ?? null) : null,
            '08:00'
        );
        $officeEndTimeLabel = $this->presenter->timeLabel(
            is_array($officeLocation) ? ($officeLocation['office_end_time'] ?? null) : null,
            '17:00'
        );
        $officeScheduleLabel = $officeStartTimeLabel.' - '.$officeEndTimeLabel;

        return $attendanceHistoryRecords
            ->map(function (Attendance $attendanceItem) use ($attendanceLogsByAttendanceId, $officeScheduleLabel, $officeStartTimeLabel): ?array {
                $attendanceDateLabel = $attendanceItem->date?->format('Y-m-d');
                $clockInLabel = $attendanceItem->clock_in?->format('H:i');
                if (! is_string($attendanceDateLabel) || trim($attendanceDateLabel) === '' || ! is_string($clockInLabel) || trim($clockInLabel) === '') {
                    return null;
                }

                $clockOutLabel = $attendanceItem->clock_out?->format('H:i') ?? '-';
                $attendanceException = $attendanceItem->attendanceException;
                $exceptionType = is_string($attendanceException?->type)
                    ? strtolower(trim($attendanceException->type))
                    : '';
                $hasAttendanceExceptionColorOverride = in_array($exceptionType, ['early_departure', 'late_arrival'], true);
                $lateMinutes = $this->calculateClockInLateMinutes($attendanceDateLabel, $clockInLabel, $officeStartTimeLabel);
                $isLate = $lateMinutes > 0;
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
                        ? $this->exceptionPresenter->modalTitle($attendanceException)
                        : 'Attendance Exception';
                    $eventBackgroundColor = '#17a2b8';
                    $eventBorderColor = '#117a8b';
                } elseif ($isLate) {
                    $attendanceModalId = 'late';
                    $attendanceEventType = 'late';
                    $attendanceTitle = $this->durationFormatter->lateLabel($lateMinutes);
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
                        'clockInSchedule' => $this->presenter->attendanceTimeWithSchedule($clockInLabel, $officeScheduleLabel),
                        'clockOutSchedule' => $this->presenter->attendanceTimeWithSchedule($clockOutLabel, $officeScheduleLabel),
                        'attendanceStatusLabel' => $this->presenter->attendanceStatusLabel($attendanceEventType, $attendanceTitle, $lateMinutes),
                        'locationName' => $this->locationFormatter->name($attendanceLog),
                        'locationAddress' => $this->locationFormatter->address($attendanceLog),
                        'deviationIntroTitle' => $attendanceException instanceof AttendanceException ? $this->exceptionPresenter->introTitle($attendanceException) : '',
                        'deviationIntroPrimary' => $attendanceException instanceof AttendanceException ? $this->exceptionPresenter->introPrimary($attendanceException) : '',
                        'deviationIntroSecondary' => $attendanceException instanceof AttendanceException ? $this->exceptionPresenter->introSecondary($attendanceException) : '',
                        'requestTypeLabel' => $attendanceException instanceof AttendanceException ? $this->exceptionPresenter->requestTypeLabel($attendanceException) : '-',
                        'reason' => $attendanceException instanceof AttendanceException && is_string($attendanceException->note) && trim($attendanceException->note) !== ''
                            ? trim($attendanceException->note)
                            : '-',
                        'timeVarianceLabel' => $attendanceException instanceof AttendanceException ? $this->exceptionPresenter->timeVarianceLabel($attendanceException) : '-',
                        'exceptionStatusLabel' => $attendanceException instanceof AttendanceException ? $this->exceptionPresenter->statusLabel($attendanceException) : '-',
                        'exceptionStatusDateLabel' => $attendanceException instanceof AttendanceException ? $this->exceptionPresenter->statusDateLabel($attendanceException) : '-',
                    ],
                ];
            })
            ->filter(static fn (mixed $eventItem): bool => is_array($eventItem))
            ->values()
            ->all();
    }

    private function calculateClockInLateMinutes(string $attendanceDate, string $clockInLabel, string $officeStartTimeLabel): int
    {
        try {
            $clockInTime = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $attendanceDate.' '.$this->normalizeTimeLabel($clockInLabel),
                'Asia/Jakarta'
            );
            $officeStartTime = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $attendanceDate.' '.$this->normalizeTimeLabel($officeStartTimeLabel),
                'Asia/Jakarta'
            );
        } catch (\Throwable) {
            return 0;
        }

        if ($clockInTime->lessThanOrEqualTo($officeStartTime)) {
            return 0;
        }

        return max(0, (int) $officeStartTime->diffInMinutes($clockInTime, true));
    }

    private function normalizeTimeLabel(string $timeLabel): string
    {
        $normalizedTimeLabel = trim($timeLabel);

        if (preg_match('/^\d{2}:\d{2}$/', $normalizedTimeLabel) === 1) {
            return $normalizedTimeLabel.':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $normalizedTimeLabel) === 1) {
            return $normalizedTimeLabel;
        }

        throw new \InvalidArgumentException('Invalid time label.');
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
                $startDate = $this->presenter->date($leaveRequest->start_date);
                $endDate = $this->presenter->date($leaveRequest->end_date);

                if ($startDate === null) {
                    return null;
                }

                $title = $this->presenter->leaveTypeLabel($leaveRequest);
                $appearance = $this->presenter->labelAppearance($title);
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
                        'calendarModalId' => $this->presenter->leaveModalId($title),
                        'calendarModalTitle' => $title === 'Sick Leave' ? 'Attendance Sick' : $title,
                        'leaveTypeLabel' => $title,
                        'leaveReason' => $this->presenter->text($leaveRequest->reason),
                        'leaveDurationLabel' => $this->presenter->durationLabel($startDate, $endDate, (int) ($leaveRequest->total_days ?? 0)),
                        'leaveStatusLabel' => $this->presenter->statusLabel($leaveRequest->status),
                        'leaveStatusDateLabel' => $this->presenter->statusDateLabel(
                            $this->presenter->statusDateValue($leaveRequest->status, $leaveRequest->approved_at, $leaveRequest->created_at)
                        ),
                        'leaveStatusTextClass' => $this->presenter->statusTextClass($leaveRequest->status),
                        'medicalNotesUrl' => $this->presenter->leaveAttachmentUrl($leaveRequest),
                        'medicalNotesIsImage' => $this->presenter->leaveAttachmentIsImage($leaveRequest),
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

                $appearance = $this->presenter->labelAppearance('Business Trip');
                $canSubmitTask = $this->presenter->businessTripLifecycleEventHasStatus($businessTrip, 'supervisor_review', 'complete');
                $canRequestReimbursement = $this->presenter->businessTripLifecycleEventHasStatus($businessTrip, 'reimbursement_submitted', 'pending');
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
                        'tripPurpose' => $this->presenter->text($businessTrip->purpose),
                        'tripDestination' => $this->presenter->businessTripDestination($businessTrip),
                        'tripDurationLabel' => $this->presenter->durationLabel($startDate, $endDate, (int) ($businessTrip->total_days ?? 0)),
                        'tripStatusLabel' => $this->presenter->statusLabel($businessTrip->approval_status),
                        'tripStatusDateLabel' => $this->presenter->statusDateLabel($businessTrip->approved_at),
                        'tripStatusTextClass' => $this->presenter->statusTextClass($businessTrip->approval_status),
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
}
