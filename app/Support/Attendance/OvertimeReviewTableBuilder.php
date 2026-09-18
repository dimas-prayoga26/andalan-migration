<?php

namespace App\Support\Attendance;

use App\Models\AttendanceOvertime;
use App\Models\OvertimeLifecycleLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class OvertimeReviewTableBuilder
{
    private const TASK_HOURS_VERIFICATION = 'task_hours_verification';

    private const DIRECTOR_APPROVAL = 'director_approval';

    private const PAYMENT_DISTRIBUTION = 'payment_disbursement';

    private const COMPLETE = 'complete';

    /**
     * @var array<int, string>
     */
    private const COMPLETED_PAYMENT_DISTRIBUTION_STATUSES = [
        self::COMPLETE,
        'completed',
        'approved',
    ];

    /**
     * @var array<int, string>
     */
    private const COMPLETED_LIFECYCLE_STATUSES = [
        'approved',
        'calculated_locked',
        'clock_in',
        'clock_out',
        self::COMPLETE,
        'completed',
        'verified',
    ];

    /**
     * @var array<int, string>
     */
    private const ADMIN_LIFECYCLE_RANGE = [
        'payroll_processing',
        self::DIRECTOR_APPROVAL,
        self::PAYMENT_DISTRIBUTION,
    ];

    /**
     * @return array{
     *     selectedMonth:int,
     *     selectedYear:int,
     *     monthOptions:array<int, array{value:int,label:string}>,
     *     yearOptions:array<int, int>,
     *     pendingRows:Collection<int, array<string, string>>,
     *     approvedRows:Collection<int, array<string, string>>
     * }
     */
    public function buildForContext(string $context, ?string $companyId, ?string $assignedByUserId, mixed $month, mixed $year): array
    {
        $selectedMonth = $this->normalizeMonth($month);
        $selectedYear = $this->normalizeYear($year);

        if (
            $context === 'pic'
            && (! is_string($companyId) || trim($companyId) === '')
            && (! is_string($assignedByUserId) || trim($assignedByUserId) === '')
        ) {
            return [
                'selectedMonth' => $selectedMonth,
                'selectedYear' => $selectedYear,
                'monthOptions' => $this->monthOptions(),
                'yearOptions' => $this->yearOptions($selectedYear),
                'pendingRows' => collect(),
                'approvedRows' => collect(),
            ];
        }

        $baseQuery = $this->baseQuery(
            is_string($companyId) && trim($companyId) !== '' ? trim($companyId) : null,
            $selectedMonth,
            $selectedYear,
            $context === 'pic'
        );

        if ($context === 'pic' && is_string($assignedByUserId) && trim($assignedByUserId) !== '') {
            $baseQuery->where('assigned_by', trim($assignedByUserId));
        }

        if ($context === 'admin') {
            return [
                'selectedMonth' => $selectedMonth,
                'selectedYear' => $selectedYear,
                'monthOptions' => $this->monthOptions(),
                'yearOptions' => $this->yearOptions($selectedYear),
                'pendingRows' => $this->rowsForAdminPendingLifecycleRange(clone $baseQuery, $context),
                'approvedRows' => $this->rowsForAdminCompletedPaymentDisbursement(clone $baseQuery, $context),
            ];
        }

        [$pendingEventKey, $pendingStatus, $approvedEventKey, $approvedStatus] = $this->statusRuleFor($context);

        return [
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'monthOptions' => $this->monthOptions(),
            'yearOptions' => $this->yearOptions($selectedYear),
            'pendingRows' => $this->rowsForLifecycleStatus(clone $baseQuery, $pendingEventKey, $pendingStatus, $context),
            'approvedRows' => $context === 'pic'
                ? $this->rowsForPicApprovedLifecycle(clone $baseQuery, $context)
                : $this->rowsForLifecycleStatus(clone $baseQuery, $approvedEventKey, $approvedStatus, $context),
        ];
    }

    private function normalizeMonth(mixed $month): int
    {
        $monthNumber = (int) $month;

        return $monthNumber >= 1 && $monthNumber <= 12
            ? $monthNumber
            : (int) Carbon::now('Asia/Jakarta')->format('n');
    }

    private function normalizeYear(mixed $year): int
    {
        $yearNumber = (int) $year;

        return $yearNumber >= 2020 && $yearNumber <= 2100
            ? $yearNumber
            : (int) Carbon::now('Asia/Jakarta')->format('Y');
    }

    /**
     * @return array<int, array{value:int,label:string}>
     */
    private function monthOptions(): array
    {
        return collect(range(1, 12))
            ->map(fn (int $month): array => [
                'value' => $month,
                'label' => Carbon::create(2026, $month, 1, 0, 0, 0, 'Asia/Jakarta')->format('F'),
            ])
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function yearOptions(int $selectedYear): array
    {
        return collect(range($selectedYear + 1, $selectedYear - 3))
            ->filter(fn (int $year): bool => $year >= 2020)
            ->values()
            ->all();
    }

    private function baseQuery(?string $companyId, int $month, int $year, bool $includeCancelled = false): Builder
    {
        $periodStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $query = AttendanceOvertime::query()
            ->whereBetween('overtime_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereHas('employee', function (Builder $query) use ($companyId): void {
                $query
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereHas('deployment', function (Builder $query) use ($companyId): void {
                        $query->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active']);

                        if (is_string($companyId) && trim($companyId) !== '') {
                            $query->where('current_company_id', trim($companyId));
                        }
                    });
            })
            ->with([
                'employee:id,user_id',
                'employee.user:id,username',
                'assignedBy:id,username',
                'projectTasks:id,overtime_id',
                'lifecycleLogs:id,overtime_id,event_key,title,status,step_order',
            ])
            ->orderBy('overtime_date')
            ->orderBy('created_at');

        if (! $includeCancelled) {
            $query->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled']);
        }

        return $query;
    }

    /**
     * @return array{0:string,1:string,2:string,3:string}
     */
    private function statusRuleFor(string $context): array
    {
        return match ($context) {
            'pic' => [self::TASK_HOURS_VERIFICATION, 'pending', self::TASK_HOURS_VERIFICATION, 'verified'],
            'director' => [self::DIRECTOR_APPROVAL, 'pending', self::DIRECTOR_APPROVAL, 'approved'],
            default => [self::TASK_HOURS_VERIFICATION, 'in_progress', self::PAYMENT_DISTRIBUTION, self::COMPLETE],
        };
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function rowsForLifecycleStatus(Builder $query, string $eventKey, string $status, string $context): Collection
    {
        return $this->rowsForLifecycleStatuses($query, $eventKey, [$status], $context);
    }

    /**
     * @param  array<int, string>  $statuses
     * @return Collection<int, array<string, string>>
     */
    private function rowsForLifecycleStatuses(Builder $query, string $eventKey, array $statuses, string $context): Collection
    {
        /** @var Collection<int, AttendanceOvertime> $overtimes */
        $overtimes = $query->get([
            'id',
            'employee_id',
            'assigned_by',
            'record_number',
            'overtime_date',
            'actual_start_time',
            'actual_end_time',
            'approved_start_time',
            'approved_end_time',
            'status',
        ]);

        $normalizedStatuses = collect($statuses)
            ->map(fn (string $status): string => strtolower(trim($status)))
            ->filter()
            ->values()
            ->all();

        return $overtimes
            ->filter(fn (AttendanceOvertime $overtime): bool => in_array($this->lifecycleStatus($overtime, $eventKey), $normalizedStatuses, true))
            ->values()
            ->map(fn (AttendanceOvertime $overtime): array => $this->rowFor($overtime, $context));
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function rowsForAdminPendingLifecycleRange(Builder $query, string $context): Collection
    {
        /** @var Collection<int, AttendanceOvertime> $overtimes */
        $overtimes = $query->get([
            'id',
            'employee_id',
            'assigned_by',
            'record_number',
            'overtime_date',
            'actual_start_time',
            'actual_end_time',
            'approved_start_time',
            'approved_end_time',
            'status',
        ]);

        return $overtimes
            ->filter(fn (AttendanceOvertime $overtime): bool => $this->isAdminPendingLifecycleRange($overtime))
            ->values()
            ->map(fn (AttendanceOvertime $overtime): array => $this->rowFor($overtime, $context));
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function rowsForAdminCompletedPaymentDisbursement(Builder $query, string $context): Collection
    {
        /** @var Collection<int, AttendanceOvertime> $overtimes */
        $overtimes = $query->get([
            'id',
            'employee_id',
            'assigned_by',
            'record_number',
            'overtime_date',
            'actual_start_time',
            'actual_end_time',
            'approved_start_time',
            'approved_end_time',
            'status',
        ]);

        return $overtimes
            ->filter(fn (AttendanceOvertime $overtime): bool => $this->isAdminCompletedPaymentDisbursement($overtime))
            ->values()
            ->map(fn (AttendanceOvertime $overtime): array => $this->rowFor($overtime, $context));
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function rowsForPicApprovedLifecycle(Builder $query, string $context): Collection
    {
        /** @var Collection<int, AttendanceOvertime> $overtimes */
        $overtimes = $query->get([
            'id',
            'employee_id',
            'assigned_by',
            'record_number',
            'overtime_date',
            'actual_start_time',
            'actual_end_time',
            'approved_start_time',
            'approved_end_time',
            'status',
        ]);

        return $overtimes
            ->filter(fn (AttendanceOvertime $overtime): bool => $this->isPicApprovedLifecycle($overtime))
            ->values()
            ->map(fn (AttendanceOvertime $overtime): array => $this->rowFor($overtime, $context));
    }

    private function isAdminPendingLifecycleRange(AttendanceOvertime $overtime): bool
    {
        return $this->hasStartedAdminLifecycleRange($overtime)
            && ! in_array($this->lifecycleStatus($overtime, self::PAYMENT_DISTRIBUTION), self::COMPLETED_PAYMENT_DISTRIBUTION_STATUSES, true);
    }

    private function isAdminCompletedPaymentDisbursement(AttendanceOvertime $overtime): bool
    {
        return in_array($this->lifecycleStatus($overtime, self::PAYMENT_DISTRIBUTION), self::COMPLETED_PAYMENT_DISTRIBUTION_STATUSES, true);
    }

    private function isPicApprovedLifecycle(AttendanceOvertime $overtime): bool
    {
        $verificationStatus = $this->lifecycleStatus($overtime, self::TASK_HOURS_VERIFICATION);

        if ($this->isCompletedLifecycleStatus($verificationStatus)) {
            return true;
        }

        if (in_array($verificationStatus, ['', 'pending', 'waiting', 'rejected', 'cancelled', 'canceled'], true)) {
            return false;
        }

        return $this->hasStartedLaterLifecycle($overtime);
    }

    private function hasStartedLaterLifecycle(AttendanceOvertime $overtime): bool
    {
        foreach (['payroll_processing', self::DIRECTOR_APPROVAL, self::PAYMENT_DISTRIBUTION] as $eventKey) {
            $status = $this->lifecycleStatus($overtime, $eventKey);

            if ($status !== '' && $status !== 'waiting') {
                return true;
            }
        }

        return false;
    }

    private function hasStartedAdminLifecycleRange(AttendanceOvertime $overtime): bool
    {
        foreach (self::ADMIN_LIFECYCLE_RANGE as $eventKey) {
            $status = $this->lifecycleStatus($overtime, $eventKey);

            if ($status !== '' && $status !== 'waiting') {
                return true;
            }
        }

        return false;
    }

    private function lifecycleStatus(AttendanceOvertime $overtime, string $eventKey): string
    {
        $lifecycleLog = $overtime->lifecycleLogs
            ->first(fn (OvertimeLifecycleLog $log): bool => (string) $log->event_key === $eventKey);

        return strtolower(trim((string) ($lifecycleLog?->status ?? '')));
    }

    /**
     * @return array<string, string>
     */
    private function rowFor(AttendanceOvertime $overtime, string $context): array
    {
        $currentLifecycle = $this->currentLifecycleValue($overtime);

        return [
            'datetime' => $this->datetimeLabel($overtime),
            'name' => $this->employeeName($overtime),
            'supervisor' => $this->supervisorName($overtime),
            'task' => $overtime->projectTasks->count().' task',
            'status' => $currentLifecycle['title'],
            'status_info' => $currentLifecycle['status'],
            'status_class' => $currentLifecycle['text_class'],
            'payout' => '-',
            'detail_url' => $this->detailUrlFor($context, $overtime),
        ];
    }

    private function currentLifecycleTitle(AttendanceOvertime $overtime): string
    {
        return $this->currentLifecycleValue($overtime)['title'];
    }

    /**
     * @return array{title:string,status:string,text_class:string}
     */
    private function currentLifecycleValue(AttendanceOvertime $overtime): array
    {
        $currentLifecycleLog = $overtime->lifecycleLogs
            ->sortBy('step_order')
            ->first(fn (OvertimeLifecycleLog $lifecycleLog): bool => ! $this->isCompletedLifecycleStatus((string) $lifecycleLog->status));

        $selectedLifecycleLog = $currentLifecycleLog
            ?: $overtime->lifecycleLogs->sortByDesc('step_order')->first();

        $title = $selectedLifecycleLog?->title;
        $status = $selectedLifecycleLog?->status;

        return [
            'title' => is_string($title) && trim($title) !== '' ? trim($title) : '-',
            'status' => is_string($status) && trim($status) !== '' ? trim($status) : '-',
            'text_class' => $this->lifecycleTextClass((string) ($selectedLifecycleLog?->status ?? '')),
        ];
    }

    private function lifecycleTextClass(string $status): string
    {
        return match (strtolower(trim($status))) {
            'approved',
            'calculated_locked',
            'clock_in',
            'clock_out',
            'complete',
            'completed',
            'verified' => 'text-success',
            'rejected',
            'cancelled',
            'canceled',
            'failed' => 'text-danger',
            'in_progress',
            'progress',
            'review' => 'text-info',
            'pending',
            'upcoming',
            'waiting' => 'text-warning',
            default => 'text-muted',
        };
    }

    private function isCompletedLifecycleStatus(string $status): bool
    {
        return in_array(strtolower(trim($status)), self::COMPLETED_LIFECYCLE_STATUSES, true);
    }

    private function datetimeLabel(AttendanceOvertime $overtime): string
    {
        $dateLabel = Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->format('d M Y');
        [$startTimeValue, $endTimeValue] = $this->reviewTimeValues($overtime);
        $startTime = $this->formatTime($startTimeValue);
        $endTime = $this->formatTime($endTimeValue);
        $duration = $this->durationLabel($startTimeValue, $endTimeValue);

        return "{$dateLabel}, {$startTime} - {$endTime} ({$duration})";
    }

    /**
     * @return array{0:mixed,1:mixed}
     */
    private function reviewTimeValues(AttendanceOvertime $overtime): array
    {
        if ($this->hasTimeRange($overtime->approved_start_time, $overtime->approved_end_time)) {
            return [$overtime->approved_start_time, $overtime->approved_end_time];
        }

        return [$overtime->actual_start_time, $overtime->actual_end_time];
    }

    private function hasTimeRange(mixed $startTimeValue, mixed $endTimeValue): bool
    {
        return is_string($startTimeValue)
            && trim($startTimeValue) !== ''
            && is_string($endTimeValue)
            && trim($endTimeValue) !== '';
    }

    private function formatTime(mixed $time): string
    {
        if (! is_string($time) || trim($time) === '') {
            return '-';
        }

        return Carbon::parse($time, 'Asia/Jakarta')->format('H:i');
    }

    private function durationLabel(mixed $startTimeValue, mixed $endTimeValue): string
    {
        $minutes = $this->durationMinutes($startTimeValue, $endTimeValue);
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }

        return $hours.'h '.$remainingMinutes.'m';
    }

    private function durationMinutes(mixed $startTimeValue, mixed $endTimeValue): int
    {
        if (! is_string($startTimeValue) || trim($startTimeValue) === '' || ! is_string($endTimeValue) || trim($endTimeValue) === '') {
            return 0;
        }

        $start = Carbon::parse('2026-01-01 '.$startTimeValue, 'Asia/Jakarta');
        $end = Carbon::parse('2026-01-01 '.$endTimeValue, 'Asia/Jakarta');

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return max(0, (int) $start->diffInMinutes($end));
    }

    private function employeeName(AttendanceOvertime $overtime): string
    {
        $name = $overtime->employee?->user?->username;

        return is_string($name) && trim($name) !== '' ? trim($name) : '-';
    }

    private function supervisorName(AttendanceOvertime $overtime): string
    {
        $name = $overtime->assignedBy?->username;

        return is_string($name) && trim($name) !== '' ? trim($name) : '-';
    }

    private function detailUrlFor(string $context, AttendanceOvertime $overtime): string
    {
        $routeName = match ($context) {
            'pic' => 'pic-attendance.overtime.detail',
            'director' => 'director-attendance.overtime.detail',
            default => 'admin-attendance.overtime.detail',
        };

        if (! Route::has($routeName)) {
            return '#';
        }

        return in_array($context, ['admin', 'pic', 'director'], true)
            ? route($routeName, ['uid' => $overtime->id])
            : route($routeName);
    }
}
