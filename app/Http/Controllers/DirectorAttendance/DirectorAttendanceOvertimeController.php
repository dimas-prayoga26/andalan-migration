<?php

namespace App\Http\Controllers\DirectorAttendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceOvertime;
use App\Models\OvertimeLifecycleLog;
use App\Models\ProjectTask;
use App\Models\User;
use App\Support\Attendance\OvertimeReviewTableBuilder;
use App\Support\Attendance\OvertimeSummaryMetricBuilder;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DirectorAttendanceOvertimeController extends Controller
{
    public function index(Request $request, OvertimeSummaryMetricBuilder $metricBuilder, OvertimeReviewTableBuilder $tableBuilder): View
    {
        $tableData = $tableBuilder->buildForContext('director', null, null, $request->query('month'), $request->query('year'));

        return view('director_attendance.overtime.index', [
            'overtimeSummary' => $metricBuilder->summarizeForActiveEmployees(),
            'overtimeCards' => $this->directorOvertimeCardsFor($tableData['selectedMonth'], $tableData['selectedYear']),
            ...$tableData,
        ]);
    }

    public function detail(Request $request, string $uid): View
    {
        $authenticatedUser = $request->user();
        abort_unless($authenticatedUser instanceof User, 403);

        $overtime = $this->directorOvertimeQuery()
            ->whereKey($uid)
            ->whereHas('lifecycleLogs', function (Builder $query): void {
                $query->where('event_key', 'director_approval');
            })
            ->firstOrFail();

        return view('director_attendance.overtime.detail', [
            'overtime' => $overtime,
            'overtimeDetail' => $this->directorOvertimeDetailSummary($overtime),
            'overtimeTaskItems' => $this->buildOvertimeTaskItems($overtime),
        ]);
    }

    public function updateApproval(Request $request, string $uid): RedirectResponse
    {
        $validated = $request->validateWithBag('directorOvertimeApproval', [
            'director_approval_status' => ['required', 'in:approved,rejected'],
        ]);

        $authenticatedUser = $request->user();
        abort_unless($authenticatedUser instanceof User, 403);

        $overtime = $this->directorOvertimeQuery()
            ->whereKey($uid)
            ->firstOrFail();

        $directorApprovalLog = $this->overtimeLifecycleLog($overtime, 'director_approval');
        abort_unless($directorApprovalLog instanceof OvertimeLifecycleLog, 404);

        if (strtolower(trim((string) $directorApprovalLog->status)) !== 'pending') {
            return back()->withErrors([
                'director_approval_status' => 'Director approval hanya bisa diubah ketika status masih pending.',
            ], 'directorOvertimeApproval');
        }

        DB::transaction(function () use ($authenticatedUser, $directorApprovalLog, $overtime, $validated): void {
            $approvedAt = Carbon::now('Asia/Jakarta');
            $status = strtolower((string) $validated['director_approval_status']);

            $directorApprovalLog->forceFill([
                'status' => $status,
                'actor_id' => $authenticatedUser->id,
                'happened_at' => $approvedAt,
            ])->save();

            if ($status === 'approved') {
                $this->updateOvertimeLifecycleLog(
                    $overtime,
                    'payment_disbursement',
                    'pending',
                    null,
                    null,
                    true
                );
            }
        });

        return redirect()
            ->route('director-attendance.overtime.detail', ['uid' => $overtime->id])
            ->with('success', 'Director approval overtime berhasil diperbarui.');
    }

    private function directorOvertimeQuery(): Builder
    {
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
                'approved_start_time',
                'approved_end_time',
                'instruction',
                'status',
            ])
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.user:id,username,email',
                'employee.deployment:id,employee_id,current_company_id',
                'assignedBy:id,username,email',
                'assignedBy.employee:id,user_id',
                'assignedBy.employee.profile:id,employee_id,name',
                'lifecycleLogs:id,overtime_id,event_key,phase,step_order,title,status,actor_id,happened_at,metadata',
                'lifecycleLogs.actor:id,username,email',
                'lifecycleLogs.actor.employee:id,user_id',
                'lifecycleLogs.actor.employee.profile:id,employee_id,name',
                'projectTasks:id,overtime_id,employee_id,project_id,assigned_by,title,description,status,priority,start_date,due_date,blockers,attachment_path,completed_at,created_at',
                'projectTasks.project:id,name',
                'projectTasks.assignedBy:id,username',
            ])
            ->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled'])
            ->whereHas('employee', function (Builder $query): void {
                $query
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereHas('deployment', function (Builder $query): void {
                        $query
                            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active']);
                    });
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function directorOvertimeCardsFor(int $month, int $year): Collection
    {
        $periodStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        return $this->directorOvertimeQuery()
            ->whereBetween('overtime_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereHas('lifecycleLogs', function (Builder $query): void {
                $query
                    ->where('event_key', 'director_approval')
                    ->whereIn(DB::raw('LOWER(COALESCE(status, ""))'), ['pending', 'approved']);
            })
            ->orderByDesc('overtime_date')
            ->orderByDesc('planned_start_time')
            ->get()
            ->map(fn (AttendanceOvertime $overtime): array => $this->directorOvertimeCardFor($overtime))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function directorOvertimeCardFor(AttendanceOvertime $overtime): array
    {
        $task = $overtime->projectTasks->first();
        $directorApprovalLog = $this->overtimeLifecycleLog($overtime, 'director_approval');
        $status = strtolower(trim((string) ($directorApprovalLog?->status ?? 'pending')));

        return [
            'record_number' => '#'.(is_string($overtime->record_number) && trim($overtime->record_number) !== '' ? trim($overtime->record_number) : '-'),
            'employee_name' => $this->employeeDisplayName($overtime),
            'instruction' => is_string($overtime->instruction) && trim($overtime->instruction) !== '' ? trim($overtime->instruction) : '-',
            'date_label' => $this->dateRangeLabel($task?->start_date ?: $overtime->overtime_date, $task?->due_date ?: $overtime->overtime_date),
            'time_lines' => $this->timeLinesFor($overtime, $this->hasApprovedOvertimeTimes($overtime) || $this->hasActualOvertimeTimes($overtime)),
            'supervisor_name' => $this->supervisorDisplayName($overtime),
            'detail_url' => route('director-attendance.overtime.detail', ['uid' => $overtime->id]),
            'current_log' => [
                'title' => 'Director Approval',
                'status' => $this->lifecycleStatusLabel($status),
                'badge_class' => $this->lifecycleStatusBadgeClass($status),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function directorOvertimeDetailSummary(AttendanceOvertime $overtime): array
    {
        $plannedStartTime = $this->formatTime($overtime->planned_start_time);
        $plannedEndTime = $this->formatTime($overtime->planned_end_time);
        $actualStartTime = $this->formatTime($overtime->actual_start_time);
        $actualEndTime = $this->formatTime($overtime->actual_end_time);
        $approvedStartTime = $this->formatTime($overtime->approved_start_time);
        $approvedEndTime = $this->formatTime($overtime->approved_end_time);
        $hasActualTime = $this->hasActualOvertimeTimes($overtime);
        $hasApprovedTime = $this->hasApprovedOvertimeTimes($overtime);
        $plannedTimeRange = $plannedStartTime.' - '.$plannedEndTime;
        $actualTimeRange = $hasActualTime ? $actualStartTime.' - '.$actualEndTime : '-';
        $plannedDuration = $this->durationLabel($overtime->planned_start_time, $overtime->planned_end_time);
        $actualDuration = $hasActualTime ? $this->durationLabel($overtime->actual_start_time, $overtime->actual_end_time) : '-';
        $approvedDuration = $hasApprovedTime ? $this->durationLabel($overtime->approved_start_time, $overtime->approved_end_time) : '-';
        $taskDeliverablesSubmitted = $this->isTaskDeliverablesSubmitted($overtime);
        $verificationLog = $this->overtimeLifecycleLog($overtime, 'task_hours_verification');
        $actualEndDateTime = $this->formatActualEndDateTime($overtime);
        $directorApprovalLog = $this->overtimeLifecycleLog($overtime, 'director_approval');
        $directorStatus = strtolower(trim((string) ($directorApprovalLog?->status ?? 'pending')));

        return [
            'staff_name' => $this->employeeDisplayName($overtime),
            'supervisor_name' => $this->supervisorDisplayName($overtime),
            'log_status' => $this->overtimeStatusLabel((string) ($overtime->status ?? 'assigned')),
            'record_number' => '#'.(is_string($overtime->record_number) && trim($overtime->record_number) !== '' ? trim($overtime->record_number) : '-'),
            'overtime_date' => Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->format('d M Y'),
            'planned_start_time' => $plannedStartTime,
            'planned_end_time' => $plannedEndTime,
            'actual_start_time' => $actualStartTime,
            'actual_end_time' => $actualEndTime,
            'approved_start_time' => $approvedStartTime,
            'approved_end_time' => $approvedEndTime,
            'planned_time_range' => $plannedTimeRange,
            'actual_time_range' => $actualTimeRange,
            'time_changed' => $hasActualTime && $actualTimeRange !== $plannedTimeRange,
            'planned_duration' => $plannedDuration,
            'actual_duration' => $actualDuration,
            'duration_changed' => $hasActualTime && $actualDuration !== $plannedDuration,
            'verification_start_time' => $taskDeliverablesSubmitted ? ($approvedStartTime !== '-' ? $approvedStartTime : ($actualStartTime !== '-' ? $actualStartTime : $plannedStartTime)) : '-',
            'verification_end_time' => $taskDeliverablesSubmitted ? ($approvedEndTime !== '-' ? $approvedEndTime : ($actualEndTime !== '-' ? $actualEndTime : $plannedEndTime)) : '-',
            'verification_duration' => $taskDeliverablesSubmitted ? ($approvedDuration !== '-' ? $approvedDuration : ($actualDuration !== '-' ? $actualDuration : $plannedDuration)) : '-',
            'instruction' => is_string($overtime->instruction) && trim($overtime->instruction) !== '' ? trim($overtime->instruction) : '-',
            'payout_period' => 'Included in '.Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->format('F Y').' Payroll',
            'verified_note' => $hasActualTime ? 'Actual overtime hours recorded' : 'Waiting for clock-out',
            'verified_datetime' => $actualEndDateTime !== '-' ? $actualEndDateTime : $this->formatLifecycleDateTime($verificationLog),
            'supervisor_approver' => $this->supervisorDisplayName($overtime),
            'supervisor_datetime' => $this->formatLifecycleDateTime($verificationLog),
            'director_approver' => $directorApprovalLog?->actor instanceof User ? $this->userDisplayName($directorApprovalLog->actor) : '-',
            'director_datetime' => $this->formatLifecycleDateTime($directorApprovalLog),
            'director_approval_status' => in_array($directorStatus, ['pending', 'approved', 'rejected'], true) ? $directorStatus : 'pending',
            'can_update_director_approval' => $directorStatus === 'pending',
        ];
    }

    /**
     * @return array{pending: Collection<int, array<string, mixed>>, finished: Collection<int, array<string, mixed>>}
     */
    private function buildOvertimeTaskItems(AttendanceOvertime $overtime): array
    {
        $taskItems = $overtime->projectTasks
            ->sortBy([
                ['due_date', 'asc'],
                ['created_at', 'asc'],
            ])
            ->map(fn (ProjectTask $projectTask): array => $this->overtimeTaskItemValue($projectTask))
            ->values();

        return [
            'pending' => $taskItems
                ->reject(fn (array $taskItem): bool => $taskItem['checked'] === true)
                ->values(),
            'finished' => $taskItems
                ->filter(fn (array $taskItem): bool => $taskItem['checked'] === true)
                ->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function overtimeTaskItemValue(ProjectTask $projectTask): array
    {
        $status = strtolower(trim((string) ($projectTask->status ?? 'pending')));
        $isFinished = $status === 'completed' || $projectTask->completed_at !== null;
        $dateValue = $isFinished ? $projectTask->completed_at : $projectTask->due_date;

        return [
            'id' => (string) $projectTask->id,
            'title' => trim((string) ($projectTask->title ?? '')) !== '' ? (string) $projectTask->title : 'Untitled Task',
            'description' => (string) ($projectTask->description ?? ''),
            'start_date' => $this->formatDateInputValue($projectTask->start_date),
            'due_date' => $this->formatDateInputValue($projectTask->due_date),
            'date_label' => $dateValue !== null ? Carbon::parse($dateValue)->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB' : '-',
            'date_range_label' => $this->taskDateRangeLabel($projectTask->start_date, $projectTask->due_date),
            'status_value' => $status !== '' ? $status : 'pending',
            'status_label' => $this->projectTaskDetailStatusLabel($status, $isFinished),
            'status_class' => $isFinished ? 'text-success' : 'text-danger',
            'priority' => strtolower(trim((string) ($projectTask->priority ?? 'medium'))) ?: 'medium',
            'attachment_path' => (string) ($projectTask->attachment_path ?? ''),
            'blockers' => (string) ($projectTask->blockers ?? ''),
            'task_category_label' => $projectTask->project_id !== null ? 'Project Task' : 'Daily Task',
            'project_name' => $projectTask->project_id !== null ? trim((string) ($projectTask->project?->name ?? '')) : 'Daily Task',
            'assigned_by' => trim((string) ($projectTask->assignedBy?->username ?? 'self')) ?: 'self',
            'checked' => $isFinished,
        ];
    }

    private function projectTaskDetailStatusLabel(string $status, bool $isFinished): string
    {
        if ($isFinished) {
            return 'Finished';
        }

        return match (strtolower(trim($status))) {
            'in_progress' => 'On Progress',
            'cancelled', 'canceled' => 'Cancelled',
            default => 'Unfinished',
        };
    }

    private function taskDateRangeLabel(?CarbonInterface $startDate, ?CarbonInterface $dueDate): string
    {
        if ($startDate === null && $dueDate === null) {
            return 'No date';
        }

        if ($startDate === null) {
            return $dueDate?->format('d M Y') ?? 'No date';
        }

        if ($dueDate === null || $startDate->isSameDay($dueDate)) {
            return $startDate->format('d M Y');
        }

        if ($startDate->format('M Y') === $dueDate->format('M Y')) {
            return $startDate->format('d').' - '.$dueDate->format('d M Y');
        }

        return $startDate->format('d M Y').' - '.$dueDate->format('d M Y');
    }

    private function overtimeLifecycleLog(AttendanceOvertime $overtime, string $eventKey): ?OvertimeLifecycleLog
    {
        return $overtime->lifecycleLogs
            ->first(fn (OvertimeLifecycleLog $lifecycleLog): bool => (string) $lifecycleLog->event_key === $eventKey);
    }

    private function updateOvertimeLifecycleLog(AttendanceOvertime $overtime, string $eventKey, string $status, ?User $actor, ?Carbon $happenedAt, bool $preserveActor = false): void
    {
        $lifecycleLog = $this->overtimeLifecycleLog($overtime, $eventKey);

        if (! $lifecycleLog instanceof OvertimeLifecycleLog) {
            return;
        }

        $payload = [
            'status' => $status,
            'happened_at' => $happenedAt,
        ];

        if (! $preserveActor) {
            $payload['actor_id'] = $actor?->id;
        }

        $lifecycleLog->forceFill($payload)->save();
    }

    private function isTaskDeliverablesSubmitted(AttendanceOvertime $overtime): bool
    {
        $status = strtolower(trim((string) ($this->overtimeLifecycleLog($overtime, 'task_deliverables_submitted')?->status ?? '')));

        return in_array($status, ['complete', 'completed'], true);
    }

    private function formatLifecycleDateTime(?OvertimeLifecycleLog $lifecycleLog): string
    {
        if (! $lifecycleLog instanceof OvertimeLifecycleLog || $lifecycleLog->happened_at === null) {
            return '-';
        }

        return Carbon::parse($lifecycleLog->happened_at, 'Asia/Jakarta')->format('d M Y, H:i');
    }

    private function formatActualEndDateTime(AttendanceOvertime $overtime): string
    {
        if (! $this->hasActualOvertimeTimes($overtime)) {
            return '-';
        }

        try {
            $overtimeDate = Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->format('Y-m-d');
            $actualStartAt = Carbon::parse($overtimeDate.' '.$overtime->actual_start_time, 'Asia/Jakarta');
            $actualEndAt = Carbon::parse($overtimeDate.' '.$overtime->actual_end_time, 'Asia/Jakarta');

            if ($actualEndAt->lessThanOrEqualTo($actualStartAt)) {
                $actualEndAt->addDay();
            }

            return $actualEndAt->format('d M Y, H:i');
        } catch (\Throwable) {
            return '-';
        }
    }

    private function formatDateInputValue(mixed $dateValue): string
    {
        if ($dateValue === null || $dateValue === '') {
            return '';
        }

        return Carbon::parse($dateValue, 'Asia/Jakarta')->format('Y-m-d');
    }

    private function userDisplayName(User $user): string
    {
        $profileName = is_string($user->employee?->profile?->name) ? trim($user->employee->profile->name) : '';
        if ($profileName !== '') {
            return $profileName;
        }

        $username = is_string($user->username) ? trim($user->username) : '';
        if ($username !== '') {
            return $username;
        }

        $email = is_string($user->email) ? trim($user->email) : '';

        return $email !== '' ? $email : '-';
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

        if (! $showActualTime || (! $this->hasApprovedOvertimeTimes($overtime) && ! $this->hasActualOvertimeTimes($overtime))) {
            return [
                ['label' => $plannedTimeLabel, 'strike' => false],
            ];
        }

        $verifiedStartTime = $this->hasApprovedOvertimeTimes($overtime)
            ? $overtime->approved_start_time
            : $overtime->actual_start_time;
        $verifiedEndTime = $this->hasApprovedOvertimeTimes($overtime)
            ? $overtime->approved_end_time
            : $overtime->actual_end_time;
        $actualTimeLabel = $this->timeRangeLabel($verifiedStartTime, $verifiedEndTime);

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

    private function hasApprovedOvertimeTimes(AttendanceOvertime $overtime): bool
    {
        return is_string($overtime->approved_start_time)
            && trim($overtime->approved_start_time) !== ''
            && is_string($overtime->approved_end_time)
            && trim($overtime->approved_end_time) !== '';
    }

    private function overtimeStatusLabel(string $status): string
    {
        return Str::of($status)
            ->replace(['_', '-'], ' ')
            ->title()
            ->toString();
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
            'rejected' => 'badge-danger',
            'clock_in', 'clock_out' => 'badge-info',
            'upcoming', 'waiting', 'pending' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}
