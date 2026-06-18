<?php

namespace App\Support\Attendance;

use App\Models\BusinessTrip;
use App\Models\BusinessTripLifecycleLog;
use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;

class AttendanceCalendarPresenter
{
    public function leaveTypeLabel(LeaveRequest $leaveRequest): string
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

    public function leaveModalId(string $leaveTypeLabel): string
    {
        return match ($leaveTypeLabel) {
            'Annual Leave' => 'annualLeave',
            'Special Leave' => 'specialLeave',
            'Unpaid Leave' => 'unpaidLeave',
            'Sick Leave' => 'sick',
            default => 'annualLeave',
        };
    }

    public function text(mixed $value): string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : '-';
    }

    public function durationLabel(?string $startDate, ?string $endDate, int $totalDays): string
    {
        if ($startDate === null) {
            return '-';
        }

        $startDateLabel = $this->dateLabel($startDate);
        $endDateLabel = $endDate !== null ? $this->dateLabel($endDate) : $startDateLabel;
        $resolvedTotalDays = $totalDays > 0 ? $totalDays : $this->durationDays($startDate, $endDate);
        $durationUnit = $resolvedTotalDays === 1 ? 'day' : 'days';
        $durationLabel = ' ('.$resolvedTotalDays.' '.$durationUnit.')';

        if ($endDate === null || $endDate === $startDate) {
            return $startDateLabel.$durationLabel;
        }

        return $startDateLabel.' - '.$endDateLabel.$durationLabel;
    }

    public function statusDateValue(mixed $status, mixed $approvedAt, mixed $createdAt): mixed
    {
        $normalizedStatus = is_string($status) ? strtolower(trim($status)) : '';

        return $normalizedStatus === 'approved' ? $approvedAt : $createdAt;
    }

    public function statusDateLabel(mixed $date): string
    {
        $dateLabel = $this->dateLabel($date);

        return $dateLabel !== '-' ? 'on '.$dateLabel : '';
    }

    public function statusLabel(mixed $status): string
    {
        $normalizedStatus = is_string($status) ? strtolower(trim($status)) : '';

        return match ($normalizedStatus) {
            'approved' => 'Approved',
            'pending' => 'Pending',
            'rejected', 'refused' => 'Rejected',
            default => $normalizedStatus !== '' ? str($normalizedStatus)->replace('_', ' ')->title()->toString() : '-',
        };
    }

    public function statusTextClass(mixed $status): string
    {
        $normalizedStatus = is_string($status) ? strtolower(trim($status)) : '';

        return match ($normalizedStatus) {
            'approved' => 'text-success',
            'pending' => 'text-warning',
            'rejected', 'refused' => 'text-danger',
            default => 'text-gray',
        };
    }

    public function leaveAttachmentUrl(LeaveRequest $leaveRequest): string
    {
        $attachmentPath = is_string($leaveRequest->attachment_path) ? trim($leaveRequest->attachment_path) : '';
        if ($attachmentPath === '') {
            return '';
        }

        return asset('storage/'.ltrim($attachmentPath, '/'));
    }

    public function leaveAttachmentIsImage(LeaveRequest $leaveRequest): bool
    {
        $attachmentPath = is_string($leaveRequest->attachment_path) ? strtolower(trim($leaveRequest->attachment_path)) : '';

        return $attachmentPath !== '' && in_array(pathinfo($attachmentPath, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
    }

    public function businessTripDestination(BusinessTrip $businessTrip): string
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

    public function businessTripLifecycleEventHasStatus(BusinessTrip $businessTrip, string $eventKey, string $expectedStatus): bool
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
    public function labelAppearance(string $title): array
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

    public function date(mixed $date): ?string
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

    public function attendanceTimeWithSchedule(string $timeLabel, string $officeScheduleLabel): string
    {
        if (trim($timeLabel) === '') {
            return '-';
        }

        return $timeLabel.' ('.$officeScheduleLabel.')';
    }

    public function attendanceStatusLabel(string $attendanceEventType, string $attendanceTitle, int $lateMinutes): string
    {
        if ($attendanceEventType === 'on_time') {
            return 'On-Time Arrival';
        }

        if ($attendanceEventType === 'late') {
            return $lateMinutes > 0 ? 'Late '.$lateMinutes.' Minutes' : 'Late Arrival';
        }

        return $attendanceTitle;
    }

    public function timeLabel(mixed $time, string $fallback): string
    {
        $normalizedTime = $this->normalizeTime($time);

        return $normalizedTime !== null ? substr($normalizedTime, 0, 5) : $fallback;
    }

    private function normalizeTime(mixed $time): ?string
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

    private function dateLabel(mixed $date): string
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

    private function durationDays(string $startDate, ?string $endDate): int
    {
        try {
            $startDateValue = Carbon::parse($startDate, 'Asia/Jakarta')->startOfDay();
            $endDateValue = Carbon::parse($endDate ?? $startDate, 'Asia/Jakarta')->startOfDay();
        } catch (\Throwable) {
            return 1;
        }

        return max((int) $startDateValue->diffInDays($endDateValue) + 1, 1);
    }
}
