<?php

namespace App\Http\Controllers\AdminAttendance;

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
        $legacyMonth = $request->query('month');
        $legacyYear = $request->query('year');
        $cardMonth = $this->normalizeMonth($request->query('card_month', $legacyMonth));
        $cardYear = $this->normalizeYear($request->query('card_year', $legacyYear));
        $pendingTableData = $tableBuilder->buildForContext(
            'admin',
            null,
            null,
            $request->query('pending_month', $legacyMonth),
            $request->query('pending_year', $legacyYear)
        );
        $completeTableData = $tableBuilder->buildForContext(
            'admin',
            null,
            null,
            $request->query('complete_month', $legacyMonth),
            $request->query('complete_year', $legacyYear)
        );

        return view('admin_attendance.overtime.index', [
            'overtimeSummary' => $metricBuilder->summarizeForPeriod(null, null, $cardMonth, $cardYear),
            'overtimeCards' => $this->adminOvertimeCardsFor(null, $cardMonth, $cardYear),
            'monthOptions' => $pendingTableData['monthOptions'],
            'yearOptions' => $this->yearOptionsForFilters(
                $cardYear,
                (int) $pendingTableData['selectedYear'],
                (int) $completeTableData['selectedYear'],
                $pendingTableData['yearOptions'],
                $completeTableData['yearOptions']
            ),
            'selectedMonth' => $pendingTableData['selectedMonth'],
            'selectedYear' => $pendingTableData['selectedYear'],
            'selectedCardMonth' => $cardMonth,
            'selectedCardYear' => $cardYear,
            'selectedPendingMonth' => $pendingTableData['selectedMonth'],
            'selectedPendingYear' => $pendingTableData['selectedYear'],
            'selectedCompleteMonth' => $completeTableData['selectedMonth'],
            'selectedCompleteYear' => $completeTableData['selectedYear'],
            'pendingRows' => $pendingTableData['pendingRows'],
            'approvedRows' => $completeTableData['approvedRows'],
        ]);
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
     * @return array<int, int>
     */
    private function yearOptionsForFilters(mixed ...$selectedYearsAndOptions): array
    {
        return collect($selectedYearsAndOptions)
            ->flatten()
            ->filter(fn (mixed $year): bool => is_numeric($year) && (int) $year >= 2020)
            ->map(fn (mixed $year): int => (int) $year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    public function detail(Request $request, string $uid): View
    {
        $authenticatedUser = $request->user();
        abort_unless($authenticatedUser instanceof User, 403);

        $overtime = AttendanceOvertime::query()
            ->select([
                'id',
                'employee_id',
                'assigned_by',
                'record_number',
                'overtime_date',
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
                'lifecycleLogs:id,overtime_id,event_key,title,status,actor_id,happened_at',
                'lifecycleLogs.actor:id,username,email',
                'lifecycleLogs.actor.employee:id,user_id',
                'lifecycleLogs.actor.employee.profile:id,employee_id,name',
                'projectTasks:id,overtime_id,employee_id,project_id,assigned_by,title,description,status,priority,start_date,due_date,blockers,attachment_path,completed_at,created_at',
                'projectTasks.project:id,name',
                'projectTasks.assignedBy:id,username',
            ])
            ->whereKey($uid)
            ->whereHas('employee', function (Builder $query): void {
                $query
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereHas('deployment', function (Builder $query): void {
                        $query->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active']);
                    });
            })
            ->firstOrFail();

        return view('admin_attendance.overtime.detail', [
            'overtime' => $overtime,
            'overtimeDetail' => $this->adminOvertimeDetailSummary($overtime),
            'overtimeTaskItems' => $this->buildOvertimeTaskItems($overtime),
        ]);
    }

    public function updateApproval(Request $request, string $uid): RedirectResponse
    {
        $validated = $request->validateWithBag('adminOvertimeApproval', [
            'payroll_processing_status' => ['required', 'in:approved,rejected'],
        ]);

        $authenticatedUser = $request->user();
        abort_unless($authenticatedUser instanceof User, 403);

        $overtime = AttendanceOvertime::query()
            ->select(['id'])
            ->with([
                'lifecycleLogs:id,overtime_id,event_key,status,actor_id,happened_at',
            ])
            ->whereKey($uid)
            ->whereHas('employee', function (Builder $query): void {
                $query
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereHas('deployment', function (Builder $query): void {
                        $query->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active']);
                    });
            })
            ->firstOrFail();

        $payrollProcessingLog = $this->overtimeLifecycleLog($overtime, 'payroll_processing');
        abort_unless($payrollProcessingLog instanceof OvertimeLifecycleLog, 404);

        if (strtolower(trim((string) $payrollProcessingLog->status)) !== 'pending') {
            return back()->withErrors([
                'payroll_processing_status' => 'HR / Payroll Processing hanya bisa diubah ketika status masih pending.',
            ], 'adminOvertimeApproval');
        }

        DB::transaction(function () use ($authenticatedUser, $overtime, $payrollProcessingLog, $validated): void {
            $approvedAt = Carbon::now('Asia/Jakarta');
            $status = strtolower((string) $validated['payroll_processing_status']);
            $payrollStatus = $status === 'approved' ? 'calculated_locked' : 'rejected';

            $payrollProcessingLog->forceFill([
                'status' => $payrollStatus,
                'actor_id' => $authenticatedUser->id,
                'happened_at' => $approvedAt,
            ])->save();

            if ($status === 'approved') {
                $this->updateOvertimeLifecycleLog(
                    $overtime,
                    'director_approval',
                    'pending',
                    null,
                    null,
                    true
                );
            }
        });

        return redirect()
            ->route('admin-attendance.overtime.detail', ['uid' => $overtime->id])
            ->with('success', 'HR / Payroll Processing overtime berhasil diperbarui.');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function adminOvertimeCardsFor(?string $companyId, int $month, int $year): Collection
    {
        $periodStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        return AttendanceOvertime::query()
            ->select([
                'id',
                'employee_id',
                'assigned_by',
                'record_number',
                'overtime_date',
                'actual_start_time',
                'actual_end_time',
                'approved_start_time',
                'approved_end_time',
                'instruction',
                'status',
            ])
            ->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled'])
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
                'employee.profile:id,employee_id,name',
                'employee.user:id,username,email',
                'assignedBy:id,username,email',
                'assignedBy.employee:id,user_id',
                'assignedBy.employee.profile:id,employee_id,name',
                'projectTasks:id,overtime_id,start_date,due_date',
                'lifecycleLogs:id,overtime_id,event_key,title,status,step_order',
            ])
            ->orderByDesc('overtime_date')
            ->orderByDesc('created_at')
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
        $showActualTime = $this->hasApprovedOvertimeTimes($overtime) || $this->hasActualOvertimeTimes($overtime);

        return [
            'record_number' => '#'.(is_string($overtime->record_number) && trim($overtime->record_number) !== '' ? trim($overtime->record_number) : '-'),
            'employee_name' => $this->employeeDisplayName($overtime),
            'instruction' => is_string($overtime->instruction) && trim($overtime->instruction) !== '' ? trim($overtime->instruction) : '-',
            'date_label' => $this->dateRangeLabel($task?->start_date ?: $overtime->overtime_date, $task?->due_date ?: $overtime->overtime_date),
            'time_lines' => $this->timeLinesFor($overtime, $showActualTime),
            'supervisor_name' => $this->supervisorDisplayName($overtime),
            'detail_url' => route('admin-attendance.overtime.detail', ['uid' => $overtime->id]),
            'current_log' => $this->currentAdminLifecycleLog($lifecycleLogsByEvent),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function adminOvertimeDetailSummary(AttendanceOvertime $overtime): array
    {
        $actualStartTime = $this->formatTime($overtime->actual_start_time);
        $actualEndTime = $this->formatTime($overtime->actual_end_time);
        $approvedStartTime = $this->formatTime($overtime->approved_start_time);
        $approvedEndTime = $this->formatTime($overtime->approved_end_time);
        $hasActualTime = $this->hasActualOvertimeTimes($overtime);
        $hasApprovedTime = $this->hasApprovedOvertimeTimes($overtime);
        $actualTimeRange = $hasActualTime ? $actualStartTime.' - '.$actualEndTime : '-';
        $actualDuration = $hasActualTime ? $this->durationLabel($overtime->actual_start_time, $overtime->actual_end_time) : '-';
        $approvedDuration = $hasApprovedTime ? $this->durationLabel($overtime->approved_start_time, $overtime->approved_end_time) : '-';
        $approvedTimeRange = $hasApprovedTime ? $approvedStartTime.' - '.$approvedEndTime : '-';
        $taskDeliverablesSubmitted = $this->isTaskDeliverablesSubmitted($overtime);
        $taskHoursVerified = $this->isTaskHoursVerified($overtime);
        $verificationLog = $this->overtimeLifecycleLog($overtime, 'task_hours_verification');
        $actualEndDateTime = $this->formatActualEndDateTime($overtime);
        $payrollProcessingLog = $this->overtimeLifecycleLog($overtime, 'payroll_processing');
        $payrollStatus = strtolower(trim((string) ($payrollProcessingLog?->status ?? 'pending')));
        $directorApprovalLog = $this->overtimeLifecycleLog($overtime, 'director_approval');
        $directorApprover = $directorApprovalLog?->actor instanceof User
            ? $directorApprovalLog->actor
            : $this->resolveOvertimeDirectorApprover($overtime);
        $directorStatus = strtolower(trim((string) ($directorApprovalLog?->status ?? 'pending')));

        return [
            'staff_name' => $this->employeeDisplayName($overtime),
            'supervisor_name' => $this->supervisorDisplayName($overtime),
            'log_status' => $this->overtimeStatusLabel((string) ($overtime->status ?? 'assigned')),
            'record_number' => '#'.(is_string($overtime->record_number) && trim($overtime->record_number) !== '' ? trim($overtime->record_number) : '-'),
            'overtime_date' => Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->format('d M Y'),
            'planned_start_time' => '-',
            'planned_end_time' => '-',
            'actual_start_time' => $actualStartTime,
            'actual_end_time' => $actualEndTime,
            'approved_start_time' => $approvedStartTime,
            'approved_end_time' => $approvedEndTime,
            'staff_submitted_time_range' => $actualTimeRange,
            'staff_submitted_duration' => $actualDuration,
            'planned_time_range' => '-',
            'actual_time_range' => $actualTimeRange,
            'approved_time_range' => $approvedTimeRange,
            'time_changed' => false,
            'planned_duration' => '-',
            'actual_duration' => $actualDuration,
            'approved_duration' => $approvedDuration,
            'duration_changed' => false,
            'verification_ready' => $taskDeliverablesSubmitted,
            'verification_start_time' => $taskDeliverablesSubmitted ? ($approvedStartTime !== '-' ? $approvedStartTime : $actualStartTime) : '-',
            'verification_end_time' => $taskDeliverablesSubmitted ? ($approvedEndTime !== '-' ? $approvedEndTime : $actualEndTime) : '-',
            'verification_duration' => $taskDeliverablesSubmitted ? ($approvedDuration !== '-' ? $approvedDuration : $actualDuration) : '-',
            'is_task_hours_verified' => $taskHoursVerified,
            'instruction' => is_string($overtime->instruction) && trim($overtime->instruction) !== '' ? trim($overtime->instruction) : '-',
            'payout_period' => 'Included in '.Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->format('F Y').' Payroll',
            'verified_note' => $hasActualTime ? 'Actual overtime hours recorded' : 'Waiting for clock-out',
            'verified_datetime' => $actualEndDateTime !== '-' ? $actualEndDateTime : $this->formatLifecycleDateTime($verificationLog),
            'supervisor_approver' => $this->supervisorDisplayName($overtime),
            'supervisor_datetime' => $this->formatLifecycleDateTime($verificationLog),
            'director_approver' => $directorApprover instanceof User ? $this->userDisplayName($directorApprover) : '-',
            'director_datetime' => $this->formatLifecycleDateTime($directorApprovalLog),
            'director_approval_status' => in_array($directorStatus, ['pending', 'approved', 'rejected'], true) ? $directorStatus : 'pending',
            'can_update_director_approval' => $directorStatus === 'pending',
            'payroll_processing_status' => in_array($payrollStatus, ['pending', 'calculated_locked', 'rejected'], true) ? $payrollStatus : 'pending',
            'payroll_processing_approval_status' => match ($payrollStatus) {
                'calculated_locked' => 'approved',
                'rejected' => 'rejected',
                default => 'pending',
            },
            'can_update_payroll_processing' => $payrollStatus === 'pending',
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

    private function resolveOvertimeDirectorApprover(AttendanceOvertime $overtime): ?User
    {
        $preferredDirector = User::query()
            ->select(['id', 'username', 'email'])
            ->with('employee.profile')
            ->whereRaw('LOWER(email) = ?', ['lukman@rnbmanagement.com'])
            ->first();

        if ($preferredDirector instanceof User) {
            return $preferredDirector;
        }

        $directorNames = [
            'Board of Directors',
            'board of directors',
            'board_of_directors',
            'board_of_director',
            'board_of_rector',
            'board of directur',
            'board_of_directur',
            'Director',
            'director',
        ];

        return User::query()
            ->select(['id', 'username', 'email'])
            ->with('employee.profile')
            ->where(function (Builder $query) use ($directorNames): void {
                $query
                    ->whereHas('roles', function (Builder $query) use ($directorNames): void {
                        $query->whereIn('name', $directorNames);
                    })
                    ->orWhereHas('employee.deployment.position', function (Builder $query) use ($directorNames): void {
                        $query->whereIn('name', $directorNames);
                    })
                    ->orWhereHas('employee.deployment.positions', function (Builder $query) use ($directorNames): void {
                        $query->whereIn('positions.name', $directorNames);
                    });
            })
            ->orderBy('username')
            ->first();
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

    private function isTaskHoursVerified(AttendanceOvertime $overtime): bool
    {
        $status = strtolower(trim((string) ($this->overtimeLifecycleLog($overtime, 'task_hours_verification')?->status ?? '')));

        return $status === 'verified';
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

    private function overtimeStatusLabel(string $status): string
    {
        return Str::of($status)
            ->replace(['_', '-'], ' ')
            ->title()
            ->toString();
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
        $plannedTimeLabel = '-';

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
