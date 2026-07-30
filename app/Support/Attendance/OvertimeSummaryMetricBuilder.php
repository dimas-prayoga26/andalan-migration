<?php

namespace App\Support\Attendance;

use App\Models\AttendanceOvertime;
use App\Models\OvertimeLifecycleLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OvertimeSummaryMetricBuilder
{
    private const SESSION_ENDED = 'session_ended';

    private const TASK_HOURS_VERIFICATION = 'task_hours_verification';

    private const PAYROLL_PROCESSING = 'payroll_processing';

    private const CLOCK_OUT = 'clock_out';

    private const PENDING = 'pending';

    private const VERIFIED = 'verified';

    private const CALCULATED_LOCKED = 'calculated_locked';

    /**
     * @return array<string, string>
     */
    public function summarizeForCompany(?string $companyId, ?string $assignedByUserId = null): array
    {
        if (! is_string($companyId) || trim($companyId) === '') {
            return $this->emptySummary();
        }

        return $this->summarizeForActiveEmployees(trim($companyId), $assignedByUserId);
    }

    /**
     * @return array<string, string>
     */
    public function summarizeForActiveEmployees(?string $companyId = null, ?string $assignedByUserId = null): array
    {
        return $this->summarize($this->activeOvertimeQuery($companyId, $assignedByUserId));
    }

    /**
     * @return array<string, string>
     */
    public function summarizeForPeriod(?string $companyId, ?string $assignedByUserId, int $month, int $year): array
    {
        $periodStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        return $this->summarize(
            $this->activeOvertimeQuery($companyId, $assignedByUserId)
                ->whereBetween('overtime_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
        );
    }

    private function activeOvertimeQuery(?string $companyId = null, ?string $assignedByUserId = null): Builder
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $query = AttendanceOvertime::query()
            ->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled'])
            ->whereHas('employee', function (Builder $query) use ($companyId, $today): void {
                $query
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereHas('deployment', function (Builder $query) use ($companyId, $today): void {
                        $query
                            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                            ->whereDate('join_date', '<=', $today)
                            ->where(function (Builder $query) use ($today): void {
                                $query
                                    ->whereNull('resignation_date')
                                    ->orWhereDate('resignation_date', '>=', $today);
                            });

                        if (is_string($companyId) && trim($companyId) !== '') {
                            $query->where('current_company_id', trim($companyId));
                        }
                    });
            });

        if (is_string($assignedByUserId) && trim($assignedByUserId) !== '') {
            $query->where('assigned_by', trim($assignedByUserId));
        }

        return $query;
    }

    /**
     * @return array<string, string>
     */
    public function summarize(Builder $query): array
    {
        /** @var Collection<int, AttendanceOvertime> $overtimes */
        $overtimes = (clone $query)
            ->with([
                'employee:id,user_id',
                'employee.user:id,username',
                'lifecycleLogs:id,overtime_id,event_key,status',
            ])
            ->get([
                'id',
                'employee_id',
                'assigned_by',
                'overtime_date',
                'approved_start_time',
                'approved_end_time',
                'status',
            ]);

        return $this->summarizeCollection($overtimes);
    }

    /**
     * @param  Collection<int, AttendanceOvertime>  $overtimes
     * @return array<string, string>
     */
    public function summarizeCollection(Collection $overtimes): array
    {
        if ($overtimes->isEmpty()) {
            return $this->emptySummary();
        }

        $pendingCount = $overtimes
            ->filter(fn (AttendanceOvertime $overtime): bool => $this->isPendingTaskHoursVerification($overtime))
            ->count();

        $verifiedOvertimes = $overtimes
            ->filter(fn (AttendanceOvertime $overtime): bool => $this->isTaskHoursVerified($overtime))
            ->values();

        $directorApprovalCount = $overtimes
            ->filter(fn (AttendanceOvertime $overtime): bool => $this->lifecycleStatus($overtime, self::PAYROLL_PROCESSING) === self::CALCULATED_LOCKED)
            ->count();

        $durationRows = $verifiedOvertimes
            ->map(fn (AttendanceOvertime $overtime): array => [
                'overtime' => $overtime,
                'minutes' => $this->durationMinutes($overtime),
            ])
            ->filter(fn (array $row): bool => (int) $row['minutes'] > 0)
            ->values();

        $durationMinutes = $durationRows->pluck('minutes')->map(fn (mixed $minutes): int => (int) $minutes)->values();
        $totalMinutes = (int) $durationMinutes->sum();
        $medianMinutes = $this->medianMinutes($durationMinutes);
        $averageMinutes = $durationMinutes->isEmpty() ? 0 : (int) round($durationMinutes->average());
        $weekendMinutes = (int) $durationRows
            ->filter(fn (array $row): bool => $this->isWeekend($row['overtime']))
            ->sum('minutes');
        $weekdayMinutes = max(0, $totalMinutes - $weekendMinutes);

        return [
            'pending_label' => $this->requestLabel($pendingCount),
            'supervisor_approved_label' => $this->requestLabel($verifiedOvertimes->count()),
            'director_approved_label' => $this->requestLabel($directorApprovalCount),
            'total_hours_label' => $this->hoursLabel($totalMinutes),
            'estimated_cost_label' => '',
            'median_hours_label' => $this->hoursLabel($medianMinutes),
            'average_hours_label' => $this->hoursLabel($averageMinutes),
            'top_overtime_label' => $this->topOvertimeName($durationRows),
            'weekend_weekday_label' => $this->compactHoursLabel($weekendMinutes).' | '.$this->compactHoursLabel($weekdayMinutes),
        ];
    }

    /**
     * @param  array<string, string>  $summary
     * @return array<int, array{label: string, value: string, background_class: string, text_class: string}>
     */
    public function metricCards(array $summary): array
    {
        $pendingLabel = $this->summaryValue($summary, 'pending_label', '0 request');
        $supervisorApprovedLabel = $this->summaryValue($summary, 'supervisor_approved_label', '0 request');
        $directorApprovedLabel = $this->summaryValue($summary, 'director_approved_label', '0 request');
        $totalHoursLabel = $this->summaryValue($summary, 'total_hours_label', '0 hours');
        $estimatedCostLabel = $this->summaryValue($summary, 'estimated_cost_label', 'Rp. 12 Jt');
        $medianHoursLabel = $this->summaryValue($summary, 'median_hours_label', '0 hours');
        $averageHoursLabel = $this->summaryValue($summary, 'average_hours_label', '0 hours');
        $topOvertimeLabel = $this->summaryValue($summary, 'top_overtime_label', '-');
        $weekendWeekdayLabel = $this->summaryValue($summary, 'weekend_weekday_label', '0h | 0h');

        return [
            [
                'label' => 'Pending',
                'value' => $pendingLabel,
                'background_class' => $this->hasPositiveCount($pendingLabel) ? 'bg-warning-subtle' : 'bg-dark-subtle',
                'text_class' => $this->hasPositiveCount($pendingLabel) ? 'text-warning' : 'text-black',
            ],
            [
                'label' => 'SPV ACC',
                'value' => $supervisorApprovedLabel,
                'background_class' => $this->hasPositiveCount($supervisorApprovedLabel) ? 'bg-success-subtle' : 'bg-dark-subtle',
                'text_class' => $this->hasPositiveCount($supervisorApprovedLabel) ? 'text-success' : 'text-black',
            ],
            [
                'label' => 'Director ACC',
                'value' => $directorApprovedLabel,
                'background_class' => $this->hasPositiveCount($directorApprovedLabel) ? 'bg-success-subtle' : 'bg-dark-subtle',
                'text_class' => $this->hasPositiveCount($directorApprovedLabel) ? 'text-success' : 'text-black',
            ],
            [
                'label' => 'Total Hours',
                'value' => $totalHoursLabel,
                'background_class' => $this->hasNonZeroDuration($totalHoursLabel) ? 'bg-danger-subtle' : 'bg-dark-subtle',
                'text_class' => $this->hasNonZeroDuration($totalHoursLabel) ? 'text-danger' : 'text-black',
            ],
            [
                'label' => 'Est. Cost',
                'value' => $estimatedCostLabel,
                'background_class' => $estimatedCostLabel === '-' ? 'bg-dark-subtle' : 'bg-info-subtle',
                'text_class' => $estimatedCostLabel === '-' ? 'text-black' : 'text-info',
            ],
            [
                'label' => 'Median Hours',
                'value' => $medianHoursLabel,
                'background_class' => $this->hasNonZeroDuration($medianHoursLabel) ? 'bg-primary-subtle' : 'bg-dark-subtle',
                'text_class' => $this->hasNonZeroDuration($medianHoursLabel) ? 'text-primary' : 'text-black',
            ],
            [
                'label' => 'Avg. Hours',
                'value' => $averageHoursLabel,
                'background_class' => $this->hasNonZeroDuration($averageHoursLabel) ? 'bg-warning-subtle' : 'bg-dark-subtle',
                'text_class' => $this->hasNonZeroDuration($averageHoursLabel) ? 'text-warning' : 'text-black',
            ],
            [
                'label' => 'Top Overtime',
                'value' => $topOvertimeLabel,
                'background_class' => $topOvertimeLabel === '-' ? 'bg-dark-subtle' : 'bg-secondary-subtle',
                'text_class' => $topOvertimeLabel === '-' ? 'text-black' : 'text-secondary',
            ],
            [
                'label' => 'W-end|W-day',
                'value' => $weekendWeekdayLabel,
                'background_class' => $this->hasNonZeroDuration($weekendWeekdayLabel) ? 'bg-light-subtle' : 'bg-dark-subtle',
                'text_class' => 'text-black',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function emptySummary(): array
    {
        return [
            'pending_label' => '0 request',
            'supervisor_approved_label' => '0 request',
            'director_approved_label' => '0 request',
            'total_hours_label' => '0 hours',
            'estimated_cost_label' => '',
            'median_hours_label' => '0 hours',
            'average_hours_label' => '0 hours',
            'top_overtime_label' => '-',
            'weekend_weekday_label' => '0h | 0h',
        ];
    }

    /**
     * @param  array<string, string>  $summary
     */
    private function summaryValue(array $summary, string $key, string $fallback): string
    {
        $value = trim((string) ($summary[$key] ?? ''));

        return $value !== '' ? $value : $fallback;
    }

    private function hasPositiveCount(string $label): bool
    {
        return preg_match('/^\s*([1-9]\d*)\b/', $label) === 1;
    }

    private function hasNonZeroDuration(string $label): bool
    {
        return preg_match('/\b[1-9]\d*\s*(?:h|hour|hours|m)\b/i', $label) === 1;
    }

    private function isPendingTaskHoursVerification(AttendanceOvertime $overtime): bool
    {
        return $this->lifecycleStatus($overtime, self::SESSION_ENDED) === self::CLOCK_OUT
            && $this->lifecycleStatus($overtime, self::TASK_HOURS_VERIFICATION) === self::PENDING;
    }

    private function isTaskHoursVerified(AttendanceOvertime $overtime): bool
    {
        return $this->lifecycleStatus($overtime, self::TASK_HOURS_VERIFICATION) === self::VERIFIED;
    }

    private function lifecycleStatus(AttendanceOvertime $overtime, string $eventKey): string
    {
        $lifecycleLog = $overtime->lifecycleLogs
            ->first(fn (OvertimeLifecycleLog $log): bool => (string) $log->event_key === $eventKey);

        return strtolower(trim((string) ($lifecycleLog?->status ?? '')));
    }

    private function durationMinutes(AttendanceOvertime $overtime): int
    {
        if (! $this->hasTimeRange($overtime->approved_start_time, $overtime->approved_end_time)) {
            return 0;
        }

        $date = Carbon::parse($overtime->overtime_date ?: now('Asia/Jakarta'), 'Asia/Jakarta')->toDateString();
        $start = Carbon::parse($date.' '.$overtime->approved_start_time, 'Asia/Jakarta');
        $end = Carbon::parse($date.' '.$overtime->approved_end_time, 'Asia/Jakarta');

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return max(0, (int) $start->diffInMinutes($end));
    }

    private function hasTimeRange(mixed $startTime, mixed $endTime): bool
    {
        return is_string($startTime)
            && trim($startTime) !== ''
            && is_string($endTime)
            && trim($endTime) !== '';
    }

    /**
     * @param  Collection<int, int>  $minutes
     */
    private function medianMinutes(Collection $minutes): int
    {
        if ($minutes->isEmpty()) {
            return 0;
        }

        $sorted = $minutes->sort()->values();
        $count = $sorted->count();
        $middleIndex = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (int) $sorted[$middleIndex];
        }

        return (int) round(((int) $sorted[$middleIndex - 1] + (int) $sorted[$middleIndex]) / 2);
    }

    private function isWeekend(AttendanceOvertime $overtime): bool
    {
        return Carbon::parse($overtime->overtime_date ?: now('Asia/Jakarta'), 'Asia/Jakarta')->isWeekend();
    }

    private function requestLabel(int $count): string
    {
        return $count.' request';
    }

    private function hoursLabel(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }

        return $hours.'h '.$remainingMinutes.'m';
    }

    private function compactHoursLabel(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours.'h';
        }

        return $hours.'h '.$remainingMinutes.'m';
    }

    /**
     * @param  Collection<int, array{overtime: AttendanceOvertime, minutes: int}>  $durationRows
     */
    private function topOvertimeName(Collection $durationRows): string
    {
        if ($durationRows->isEmpty()) {
            return '-';
        }

        $topEmployeeId = $durationRows
            ->groupBy(fn (array $row): string => (string) $row['overtime']->employee_id)
            ->map(fn (Collection $rows): int => (int) $rows->sum('minutes'))
            ->sortDesc()
            ->keys()
            ->first();

        $topOvertime = $durationRows
            ->first(fn (array $row): bool => (string) $row['overtime']->employee_id === (string) $topEmployeeId);

        $employee = $topOvertime['overtime']->employee ?? null;
        $name = $employee?->user?->username;

        return is_string($name) && trim($name) !== '' ? trim($name) : '-';
    }
}
