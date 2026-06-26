<?php

namespace App\Http\Controllers\AdminAttendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceOvertime;
use App\Models\OvertimeLifecycleLog;
use App\Models\User;
use App\Support\Attendance\OvertimeReviewTableBuilder;
use App\Support\Attendance\OvertimeSummaryMetricBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceOvertimeController extends Controller
{
    /**
     * @var array<int, array{event_key:string,title:string,completed_statuses:array<int, string>}>
     */
    private const ADMIN_OVERTIME_CARD_STEPS = [
        [
            'event_key' => 'task_hours_verification',
            'title' => 'Task & Hours Verification',
            'completed_statuses' => ['verified'],
        ],
        [
            'event_key' => 'payroll_processing',
            'title' => 'HR / Payroll Processing',
            'completed_statuses' => ['calculated_locked'],
        ],
        [
            'event_key' => 'director_approval',
            'title' => 'Director Approval',
            'completed_statuses' => ['approved'],
        ],
        [
            'event_key' => 'payment_disbursement',
            'title' => 'Payment Distribution',
            'completed_statuses' => ['complete'],
        ],
    ];

    public function index(Request $request, OvertimeSummaryMetricBuilder $metricBuilder, OvertimeReviewTableBuilder $tableBuilder): View
    {
        $authenticatedUser = $request->user();
        $companyId = $authenticatedUser instanceof User ? $this->currentCompanyIdFor($authenticatedUser) : null;
        $tableData = $tableBuilder->buildForContext('admin', $companyId, null, $request->query('month'), $request->query('year'));

        return view('admin_attendance.overtime.index', [
            'overtimeSummary' => $metricBuilder->summarizeForCompany($companyId),
            'overtimeCards' => $this->adminOvertimeCardsFor($companyId, $tableData['selectedMonth'], $tableData['selectedYear']),
            ...$tableData,
        ]);
    }

    public function detail(): View
    {
        return view('admin_attendance.overtime.detail');
    }

    private function currentCompanyIdFor(User $user): ?string
    {
        $user->loadMissing('employee.deployment:id,employee_id,current_company_id');
        $companyId = $user->employee?->deployment?->current_company_id;

        return is_string($companyId) && trim($companyId) !== '' ? trim($companyId) : null;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function adminOvertimeCardsFor(?string $companyId, int $month, int $year): Collection
    {
        if (! is_string($companyId) || trim($companyId) === '') {
            return collect();
        }

        $periodStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        return AttendanceOvertime::query()
            ->select([
                'id',
                'employee_id',
                'assigned_by',
                'record_number',
                'overtime_date',
                'planned_start_time',
                'planned_end_time',
                'actual_start_time',
                'actual_end_time',
                'instruction',
                'status',
            ])
            ->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled'])
            ->whereBetween('overtime_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereHas('employee', function (Builder $query) use ($companyId): void {
                $query
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereHas('deployment', function (Builder $query) use ($companyId): void {
                        $query
                            ->where('current_company_id', trim($companyId))
                            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active']);
                    });
            })
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.user:id,username,email',
                'assignedBy:id,username,email',
                'assignedBy.employee:id,user_id',
                'assignedBy.employee.profile:id,employee_id,name',
                'projectTasks:id,overtime_id,start_date,due_date',
                'lifecycleLogs:id,overtime_id,event_key,title,status,step_order',
            ])
            ->orderByDesc('overtime_date')
            ->orderByDesc('planned_start_time')
            ->get()
            ->filter(fn (AttendanceOvertime $overtime): bool => $this->hasStartedAdminOvertimeCardRange($overtime))
            ->map(fn (AttendanceOvertime $overtime): array => $this->adminOvertimeCardFor($overtime))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function adminOvertimeCardFor(AttendanceOvertime $overtime): array
    {
        $task = $overtime->projectTasks->first();
        $lifecycleLogsByEvent = $overtime->lifecycleLogs->keyBy('event_key');
        $isPaymentComplete = $this->lifecycleStatus($lifecycleLogsByEvent, 'payment_disbursement') === 'complete';

        return [
            'record_number' => '#'.(is_string($overtime->record_number) && trim($overtime->record_number) !== '' ? trim($overtime->record_number) : '-'),
            'employee_name' => $this->employeeDisplayName($overtime),
            'instruction' => is_string($overtime->instruction) && trim($overtime->instruction) !== '' ? trim($overtime->instruction) : '-',
            'date_label' => $this->dateRangeLabel($task?->start_date ?: $overtime->overtime_date, $task?->due_date ?: $overtime->overtime_date),
            'time_lines' => $this->timeLinesFor($overtime, $isPaymentComplete),
            'supervisor_name' => $this->supervisorDisplayName($overtime),
            'detail_url' => route('admin-attendance.overtime.detail'),
            'current_log' => $this->currentAdminLifecycleLog($lifecycleLogsByEvent),
        ];
    }

    /**
     * @param  Collection<string, OvertimeLifecycleLog>  $lifecycleLogsByEvent
     * @return array{title:string,status:string,badge_class:string}
     */
    private function currentAdminLifecycleLog(Collection $lifecycleLogsByEvent): array
    {
        $adminLifecycleLogs = collect(self::ADMIN_OVERTIME_CARD_STEPS)
            ->map(function (array $step) use ($lifecycleLogsByEvent): array {
                $status = strtolower(trim((string) ($lifecycleLogsByEvent->get($step['event_key'])?->status ?? 'waiting')));

                return [
                    'title' => $step['title'],
                    'status' => $this->lifecycleStatusLabel($status),
                    'badge_class' => $this->lifecycleStatusBadgeClass($status),
                    'is_complete' => in_array($status, $step['completed_statuses'], true),
                ];
            })
            ->values();

        $currentLog = $adminLifecycleLogs->first(fn (array $log): bool => $log['is_complete'] === false);

        $selectedLog = $currentLog ?? $adminLifecycleLogs->last() ?? [
            'title' => 'Task & Hours Verification',
            'status' => 'Waiting',
            'badge_class' => 'badge-warning',
        ];

        unset($selectedLog['is_complete']);

        return $selectedLog;
    }

    private function hasStartedAdminOvertimeCardRange(AttendanceOvertime $overtime): bool
    {
        $lifecycleLogsByEvent = $overtime->lifecycleLogs->keyBy('event_key');

        foreach (self::ADMIN_OVERTIME_CARD_STEPS as $step) {
            $status = $this->lifecycleStatus($lifecycleLogsByEvent, $step['event_key']);

            if ($status !== '' && $status !== 'waiting') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Collection<string, OvertimeLifecycleLog>  $lifecycleLogsByEvent
     */
    private function lifecycleStatus(Collection $lifecycleLogsByEvent, string $eventKey): string
    {
        return strtolower(trim((string) ($lifecycleLogsByEvent->get($eventKey)?->status ?? '')));
    }

    private function employeeDisplayName(AttendanceOvertime $overtime): string
    {
        $name = $overtime->employee?->profile?->name ?: $overtime->employee?->user?->username;

        return is_string($name) && trim($name) !== '' ? trim($name) : '-';
    }

    private function supervisorDisplayName(AttendanceOvertime $overtime): string
    {
        $name = $overtime->assignedBy?->employee?->profile?->name ?: $overtime->assignedBy?->username;

        return is_string($name) && trim($name) !== '' ? trim($name) : '-';
    }

    private function dateRangeLabel(mixed $startDateValue, mixed $endDateValue): string
    {
        $startDate = Carbon::parse($startDateValue, 'Asia/Jakarta');
        $endDate = Carbon::parse($endDateValue ?: $startDateValue, 'Asia/Jakarta');

        if ($startDate->isSameDay($endDate)) {
            return $startDate->format('d M Y');
        }

        if ($startDate->isSameMonth($endDate) && $startDate->isSameYear($endDate)) {
            return $startDate->format('d').' - '.$endDate->format('d M Y');
        }

        return $startDate->format('d M Y').' - '.$endDate->format('d M Y');
    }

    /**
     * @return array<int, array{label:string,strike:bool}>
     */
    private function timeLinesFor(AttendanceOvertime $overtime, bool $showActualTime): array
    {
        $plannedTimeLabel = $this->timeRangeLabel($overtime->planned_start_time, $overtime->planned_end_time);

        if (! $showActualTime || ! $this->hasActualOvertimeTimes($overtime)) {
            return [
                ['label' => $plannedTimeLabel, 'strike' => false],
            ];
        }

        $actualTimeLabel = $this->timeRangeLabel($overtime->actual_start_time, $overtime->actual_end_time);

        if ($actualTimeLabel === $plannedTimeLabel) {
            return [
                ['label' => $actualTimeLabel, 'strike' => false],
            ];
        }

        return [
            ['label' => $plannedTimeLabel, 'strike' => true],
            ['label' => $actualTimeLabel, 'strike' => false],
        ];
    }

    private function timeRangeLabel(mixed $startTimeValue, mixed $endTimeValue): string
    {
        $startTime = $this->formatTime($startTimeValue);
        $endTime = $this->formatTime($endTimeValue);
        $duration = $this->durationLabel($startTimeValue, $endTimeValue);

        return "{$startTime} - {$endTime} ({$duration})";
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

    private function hasActualOvertimeTimes(AttendanceOvertime $overtime): bool
    {
        return is_string($overtime->actual_start_time)
            && trim($overtime->actual_start_time) !== ''
            && is_string($overtime->actual_end_time)
            && trim($overtime->actual_end_time) !== '';
    }

    private function lifecycleStatusLabel(string $status): string
    {
        $normalizedStatus = strtolower(trim($status));

        return match ($normalizedStatus) {
            'calculated_locked' => 'Calculated & Locked',
            'clock_in' => 'Clock_in',
            'clock_out' => 'Clock_out',
            default => $normalizedStatus !== '' ? ucfirst($normalizedStatus) : 'Waiting',
        };
    }

    private function lifecycleStatusBadgeClass(string $status): string
    {
        return match (strtolower(trim($status))) {
            'complete', 'verified', 'approved', 'calculated_locked' => 'badge-success',
            'clock_in', 'clock_out' => 'badge-info',
            'upcoming', 'waiting', 'pending' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}
