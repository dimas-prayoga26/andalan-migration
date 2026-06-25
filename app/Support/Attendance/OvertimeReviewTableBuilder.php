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

        if (! is_string($companyId) || trim($companyId) === '') {
            return [
                'selectedMonth' => $selectedMonth,
                'selectedYear' => $selectedYear,
                'monthOptions' => $this->monthOptions(),
                'yearOptions' => $this->yearOptions($selectedYear),
                'pendingRows' => collect(),
                'approvedRows' => collect(),
            ];
        }

        $baseQuery = $this->baseQuery(trim($companyId), $selectedMonth, $selectedYear);

        if ($context === 'pic' && is_string($assignedByUserId) && trim($assignedByUserId) !== '') {
            $baseQuery->where('assigned_by', trim($assignedByUserId));
        }

        [$pendingEventKey, $pendingStatus, $approvedEventKey, $approvedStatus] = $this->statusRuleFor($context);

        return [
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'monthOptions' => $this->monthOptions(),
            'yearOptions' => $this->yearOptions($selectedYear),
            'pendingRows' => $this->rowsForLifecycleStatus(clone $baseQuery, $pendingEventKey, $pendingStatus, $context),
            'approvedRows' => $this->rowsForLifecycleStatus(clone $baseQuery, $approvedEventKey, $approvedStatus, $context),
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

    private function baseQuery(string $companyId, int $month, int $year): Builder
    {
        $periodStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        return AttendanceOvertime::query()
            ->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled'])
            ->whereBetween('overtime_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereHas('employee', function (Builder $query) use ($companyId): void {
                $query
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereHas('deployment', function (Builder $query) use ($companyId): void {
                        $query
                            ->where('current_company_id', $companyId)
                            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active']);
                    });
            })
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.user:id,username',
                'assignedBy:id,username',
                'assignedBy.employee:id,user_id',
                'assignedBy.employee.profile:id,employee_id,name',
                'projectTasks:id,overtime_id',
                'lifecycleLogs:id,overtime_id,event_key,status',
            ])
            ->orderBy('overtime_date')
            ->orderBy('planned_start_time');
    }

    /**
     * @return array{0:string,1:string,2:string,3:string}
     */
    private function statusRuleFor(string $context): array
    {
        return match ($context) {
            'pic' => [self::TASK_HOURS_VERIFICATION, 'pending', self::TASK_HOURS_VERIFICATION, 'verified'],
            'director' => [self::DIRECTOR_APPROVAL, 'pending', self::DIRECTOR_APPROVAL, 'approved'],
            default => [self::TASK_HOURS_VERIFICATION, 'pending', self::DIRECTOR_APPROVAL, 'approved'],
        };
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function rowsForLifecycleStatus(Builder $query, string $eventKey, string $status, string $context): Collection
    {
        /** @var Collection<int, AttendanceOvertime> $overtimes */
        $overtimes = $query->get([
            'id',
            'employee_id',
            'assigned_by',
            'record_number',
            'overtime_date',
            'planned_start_time',
            'planned_end_time',
            'actual_start_time',
            'actual_end_time',
            'status',
        ]);

        return $overtimes
            ->filter(fn (AttendanceOvertime $overtime): bool => $this->lifecycleStatus($overtime, $eventKey) === $status)
            ->values()
            ->map(fn (AttendanceOvertime $overtime): array => $this->rowFor($overtime, $context));
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
        return [
            'datetime' => $this->datetimeLabel($overtime),
            'name' => $this->employeeName($overtime),
            'supervisor' => $this->supervisorName($overtime),
            'task' => $overtime->projectTasks->count().' task',
            'payout' => '-',
            'detail_url' => $this->detailUrlFor($context, $overtime),
        ];
    }

    private function datetimeLabel(AttendanceOvertime $overtime): string
    {
        $dateLabel = Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->format('d M Y');
        $startTime = $this->formatTime($overtime->actual_start_time ?: $overtime->planned_start_time);
        $endTime = $this->formatTime($overtime->actual_end_time ?: $overtime->planned_end_time);
        $duration = $this->durationLabel($overtime->actual_start_time ?: $overtime->planned_start_time, $overtime->actual_end_time ?: $overtime->planned_end_time);

        return "{$dateLabel}, {$startTime} - {$endTime} ({$duration})";
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
        $name = $overtime->employee?->profile?->name ?: $overtime->employee?->user?->username;

        return is_string($name) && trim($name) !== '' ? trim($name) : '-';
    }

    private function supervisorName(AttendanceOvertime $overtime): string
    {
        $name = $overtime->assignedBy?->employee?->profile?->name ?: $overtime->assignedBy?->username;

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

        return $context === 'pic'
            ? route($routeName, ['uid' => $overtime->id])
            : route($routeName);
    }
}
