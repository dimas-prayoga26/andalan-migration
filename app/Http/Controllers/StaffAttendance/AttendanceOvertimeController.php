<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\OvertimeLifecycleLog;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceOvertimeController extends Controller
{
    private const OVERTIME_STATUS_ASSIGNED = 'assigned';

    private const OVERTIME_STATUS_IN_PROGRESS = 'in_progress';

    private const OVERTIME_STATUS_COMPLETED = 'completed';

    private const OVERTIME_STATUS_CANCELLED = 'cancelled';

    private const OVERTIME_CLOCK_TOLERANCE_MINUTES = 30;

    private const OVERTIME_LIFECYCLE_PHASES = [
        'assignment_request' => [
            'title' => 'Phase 1: Assignment & Request',
            'sort_order' => 1,
        ],
        'execution_time_tracking' => [
            'title' => 'Phase 2: Execution (Time Tracking)',
            'sort_order' => 2,
        ],
        'review_approval' => [
            'title' => 'Phase 3: Review & Approval',
            'sort_order' => 3,
        ],
        'payroll_payment' => [
            'title' => 'Phase 4: Payroll & Payment',
            'sort_order' => 4,
        ],
    ];

    private const OVERTIME_LIFECYCLE_STEPS = [
        [
            'phase' => 'assignment_request',
            'event_key' => 'assignment_submitted',
            'step_order' => 1,
            'title' => 'Overtime Assignment Submitted',
            'status' => 'complete',
        ],
        [
            'phase' => 'execution_time_tracking',
            'event_key' => 'session_started',
            'step_order' => 2,
            'title' => 'Overtime Session Started',
            'status' => 'waiting',
        ],
        [
            'phase' => 'execution_time_tracking',
            'event_key' => 'task_deliverables_submitted',
            'step_order' => 3,
            'title' => 'Task & Deliverables Submitted',
            'status' => 'waiting',
        ],
        [
            'phase' => 'execution_time_tracking',
            'event_key' => 'session_ended',
            'step_order' => 4,
            'title' => 'Overtime Session Ended',
            'status' => 'waiting',
        ],
        [
            'phase' => 'review_approval',
            'event_key' => 'task_hours_verification',
            'step_order' => 5,
            'title' => 'Task & Hours Verification',
            'status' => 'waiting',
        ],
        [
            'phase' => 'payroll_payment',
            'event_key' => 'payroll_processing',
            'step_order' => 6,
            'title' => 'HR / Payroll Processing',
            'status' => 'waiting',
        ],
        [
            'phase' => 'payroll_payment',
            'event_key' => 'director_approval',
            'step_order' => 7,
            'title' => 'Director Approval',
            'status' => 'waiting',
        ],
        [
            'phase' => 'payroll_payment',
            'event_key' => 'payment_disbursement',
            'step_order' => 8,
            'title' => 'Payment Disbursement',
            'status' => 'waiting',
        ],
    ];

    public function index(Request $request): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.profile', 'employee.deployment');
        }

        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $authenticatedEmployeeId = $authenticatedUser?->employee?->id;
        $hasStaffOvertimeAssignment = $this->hasOvertimeAssignmentForEmployee($authenticatedEmployeeId);
        $staffEditableOvertimeId = $isStaffUser
            ? $this->resolveStaffEditableOvertimeId($authenticatedEmployeeId)
            : null;
        $canManageOvertimeActions = $this->isSuperuserUser($authenticatedUser) || $this->isBoardOfDirectur($authenticatedUser);

        $staffOptions = $this->buildStaffOptions($authenticatedUser);
        $picOptions = $this->buildPicOptions($authenticatedUser);
        $assignedOvertimeEmployeeIds = AttendanceOvertime::query()
            ->select('employee_id')
            ->distinct()
            ->pluck('employee_id')
            ->filter(static fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->values();
        $defaultStaffEmployeeId = is_string($authenticatedEmployeeId) ? trim($authenticatedEmployeeId) : '';
        $staffOptionIds = $staffOptions
            ->pluck('id')
            ->filter(static fn (mixed $staffId): bool => is_string($staffId) && trim($staffId) !== '')
            ->values()
            ->all();
        if ($defaultStaffEmployeeId === '' || ! in_array($defaultStaffEmployeeId, $staffOptionIds, true)) {
            $defaultStaffEmployeeId = (string) ($staffOptionIds[0] ?? '');
        }

        $defaultPicUserId = is_string($authenticatedUser?->id) ? trim($authenticatedUser->id) : '';
        $picOptionIds = $picOptions
            ->pluck('id')
            ->filter(static fn (mixed $picId): bool => is_string($picId) && trim($picId) !== '')
            ->values()
            ->all();
        if ($defaultPicUserId === '' || ! in_array($defaultPicUserId, $picOptionIds, true)) {
            $defaultPicUserId = (string) ($picOptionIds[0] ?? '');
        }

        $overtimeStatusFilter = $this->normalizeOvertimeIndexStatusFilter($request->query('status'));
        $overtimeTimeframeFilter = $this->normalizeOvertimeIndexTimeframeFilter($request->query('timeframe'));

        return view('staff_attendance.overtimes.index', [
            'canSubmitOvertime' => $isStaffUser || $canManageOvertimeActions,
            'isStaffOvertimeUser' => $isStaffUser,
            'hasStaffOvertimeAssignment' => $hasStaffOvertimeAssignment,
            'canManageOvertimeActions' => $canManageOvertimeActions,
            'staffOptions' => $staffOptions,
            'picOptions' => $picOptions,
            'assignedOvertimeEmployeeIds' => $assignedOvertimeEmployeeIds,
            'defaultStaffEmployeeId' => $defaultStaffEmployeeId,
            'defaultPicUserId' => $defaultPicUserId,
            'staffEditableOvertimeId' => $staffEditableOvertimeId,
            'overtimeList' => $this->buildOvertimeIndexList(
                $authenticatedUser instanceof User ? $authenticatedUser : null,
                $overtimeStatusFilter,
                $overtimeTimeframeFilter
            ),
            'overtimeSummary' => $this->buildOvertimeIndexSummary($authenticatedUser instanceof User ? $authenticatedUser : null),
            'overtimeStatusFilter' => $overtimeStatusFilter,
            'overtimeTimeframeFilter' => $overtimeTimeframeFilter,
        ]);
    }

    public function detail(?AttendanceOvertime $attendanceOvertime = null): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $overtimeQuery = AttendanceOvertime::query()
            ->with([
                'employee.user:id,username',
                'employee.profile:id,employee_id,name',
                'assignedBy:id,username',
                'assignedBy.employee:id,user_id',
                'assignedBy.employee.profile:id,employee_id,name',
                'lifecycleLogs.actor',
                'lifecycleLogs.actor.employee.profile',
                'projectTasks' => function ($query): void {
                    $query
                        ->orderBy('due_date')
                        ->orderBy('created_at');
                },
            ]);

        if ($attendanceOvertime instanceof AttendanceOvertime) {
            $overtime = $overtimeQuery->whereKey($attendanceOvertime->id)->firstOrFail();
            abort_unless($this->canAccessOvertime($authenticatedUser instanceof User ? $authenticatedUser : null, $overtime), 403);
        } else {
            $overtime = $this->applyOvertimeAccessFilter($overtimeQuery, $authenticatedUser instanceof User ? $authenticatedUser : null)
                ->latest('overtime_date')
                ->latest('created_at')
                ->first();
        }

        return view('staff_attendance.overtimes.detail', [
            'overtime' => $overtime,
            'overtimeReference' => $overtime instanceof AttendanceOvertime ? $this->formatOvertimeReference($overtime) : '#OVT',
            'overtimeDetail' => $overtime instanceof AttendanceOvertime ? $this->buildOvertimeDetailSummary($overtime) : [],
            'overtimeLifecycleTracker' => $overtime instanceof AttendanceOvertime ? $this->buildOvertimeLifecycleTracker($overtime) : collect(),
            'overtimeTaskItems' => $overtime instanceof AttendanceOvertime ? $this->buildOvertimeTaskItems($overtime) : [
                'pending' => collect(),
                'finished' => collect(),
            ],
            'taskProjectOptions' => $overtime instanceof AttendanceOvertime ? $this->buildTaskProjectOptions($overtime) : collect(),
            'taskStoreUrl' => $overtime instanceof AttendanceOvertime && $this->canCreateOvertimeTask($overtime)
                ? route('attendance.overtimes.tasks.store', $overtime)
                : null,
            'taskDefaultDate' => $overtime instanceof AttendanceOvertime ? $this->formatDateInputValue($overtime->overtime_date) : Carbon::now('Asia/Jakarta')->toDateString(),
        ]);
    }

    public function datatable(): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isAdminUser = $this->isAdminUser($authenticatedUser);
        $userCompanyId = $authenticatedUser?->employee?->deployment?->current_company_id;
        $authenticatedEmployeeId = $authenticatedUser?->employee?->id;

        $overtimesQuery = AttendanceOvertime::query()
            ->with([
                'employee.user:id,username',
                'employee.profile:id,employee_id,name',
                'assignedBy:id,username',
                'assignedBy.employee:id,user_id',
                'assignedBy.employee.profile:id,employee_id,name',
            ])
            ->latest('id')
            ->select([
                'id',
                'record_number',
                'employee_id',
                'assigned_by',
                'overtime_date',
                'planned_start_time',
                'planned_end_time',
                'instruction',
                'actual_start_time',
                'actual_end_time',
                'calculated_hours',
                'status',
            ]);

        if ($isBoardOfDirectur && $userCompanyId) {
            $overtimesQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
                $query->where('current_company_id', $userCompanyId);
            });
        } elseif (! $isAdminUser) {
            if (! is_string($authenticatedEmployeeId) || trim($authenticatedEmployeeId) === '') {
                return response()->json(['data' => []]);
            }
            $overtimesQuery
                ->where('employee_id', $authenticatedEmployeeId)
                ->where('status', self::OVERTIME_STATUS_COMPLETED)
                ->whereNotNull('actual_start_time')
                ->whereNotNull('actual_end_time');
        }

        $overtimes = $overtimesQuery->get();

        $tableRows = $overtimes->map(function (AttendanceOvertime $overtime): array {
            $date = $overtime->overtime_date instanceof Carbon
                ? $overtime->overtime_date
                : Carbon::parse($overtime->overtime_date);
            $actualStartTime = $this->normalizeTimeString($overtime->actual_start_time);
            $actualEndTime = $this->normalizeTimeString($overtime->actual_end_time);
            $hasActualTime = $actualStartTime !== '-' && $actualEndTime !== '-';
            $calculatedHoursDisplay = '-';

            return [
                'id' => $overtime->id,
                'record_number' => (string) ($overtime->record_number ?? ''),
                'overtime_date' => $date->format('d M Y'),
                'staff_name' => $this->resolveEmployeeDisplayName($overtime->employee),
                'pic_name' => $this->resolveUserDisplayName($overtime->assignedBy),
                'planned_start_time' => $this->normalizeTimeString($overtime->planned_start_time),
                'planned_end_time' => $this->normalizeTimeString($overtime->planned_end_time),
                'actual_start_time' => $actualStartTime,
                'actual_end_time' => $actualEndTime,
                'has_actual_time' => $hasActualTime,
                'calculated_hours' => $calculatedHoursDisplay,
                'calculated_hours_display' => $calculatedHoursDisplay,
                'duration' => $this->calculateDurationLabel($overtime->actual_start_time, $overtime->actual_end_time),
                'status' => (string) ($overtime->status ?? self::OVERTIME_STATUS_ASSIGNED),
                'instruction' => $overtime->instruction ?: '-',
            ];
        })->values();

        return response()->json([
            'data' => $tableRows,
        ]);
    }

    /**
     * @return Collection<int, array{
     *     id: string,
     *     reference: string,
     *     detail_url: string,
     *     overtime_date: string,
     *     time_range: string,
     *     duration: string,
     *     instruction: string,
     *     due_label: string,
     *     pic_name: string,
     *     status: string,
     *     progress_label: string,
     *     footer_status_label: string,
     *     footer_status_badge_class: string,
     *     progress_percent: int
     * }>
     */
    private function buildOvertimeIndexList(?User $authenticatedUser, ?string $statusFilter, string $timeframeFilter): Collection
    {
        $overtimesQuery = AttendanceOvertime::query()
            ->with([
                'employee.user:id,username',
                'employee.profile:id,employee_id,name',
                'employee.deployment:id,employee_id,current_company_id',
                'assignedBy:id,username',
                'assignedBy.employee:id,user_id',
                'assignedBy.employee.profile:id,employee_id,name',
                'lifecycleLogs:id,overtime_id,event_key,status',
                'projectTasks' => function ($query): void {
                    $query
                        ->select([
                            'id',
                            'project_id',
                            'employee_id',
                            'overtime_id',
                            'title',
                            'description',
                            'status',
                            'priority',
                            'due_date',
                        ])
                        ->orderBy('due_date')
                        ->orderBy('id');
                },
            ])
            ->select([
                'id',
                'record_number',
                'employee_id',
                'assigned_by',
                'overtime_date',
                'planned_start_time',
                'planned_end_time',
                'instruction',
                'actual_start_time',
                'actual_end_time',
                'calculated_hours',
                'status',
                'created_at',
            ]);

        $overtimesQuery = $this->applyOvertimeAccessFilter($overtimesQuery, $authenticatedUser);

        if ($statusFilter === self::OVERTIME_STATUS_COMPLETED) {
            $overtimesQuery->whereHas('lifecycleLogs', function (Builder $query): void {
                $query
                    ->where('event_key', 'task_hours_verification')
                    ->whereRaw('LOWER(status) = ?', ['verified']);
            });
        } elseif ($statusFilter !== null) {
            $overtimesQuery->where('status', $statusFilter);
        }

        $this->applyOvertimeIndexTimeframeFilter($overtimesQuery, $timeframeFilter);

        return $overtimesQuery
            ->orderByDesc('overtime_date')
            ->orderByDesc('created_at')
            ->limit(24)
            ->get()
            ->map(fn (AttendanceOvertime $overtime): array => $this->buildOvertimeIndexCard($overtime));
    }

    /**
     * @return array{
     *     id: string,
     *     reference: string,
     *     detail_url: string,
     *     overtime_date: string,
     *     time_range: string,
     *     duration: string,
     *     instruction: string,
     *     due_label: string,
     *     pic_name: string,
     *     status: string,
     *     progress_label: string,
     *     footer_status_label: string,
     *     footer_status_badge_class: string,
     *     progress_percent: int
     * }
     */
    private function buildOvertimeIndexCard(AttendanceOvertime $overtime): array
    {
        $overtimeDate = $overtime->overtime_date instanceof Carbon
            ? $overtime->overtime_date
            : Carbon::parse($overtime->overtime_date);
        $plannedStartTime = $this->normalizeTimeString($overtime->planned_start_time);
        $plannedEndTime = $this->normalizeTimeString($overtime->planned_end_time);
        $projectTask = $overtime->projectTasks->first();
        $dueDate = $projectTask?->due_date;
        $dueLabel = $dueDate === null
            ? '-'
            : Carbon::parse($dueDate)->format('d M Y').($plannedEndTime !== '-' ? ', '.$plannedEndTime : '');
        $status = (string) ($overtime->status ?? self::OVERTIME_STATUS_ASSIGNED);

        return [
            'id' => (string) $overtime->id,
            'reference' => $this->formatOvertimeReference($overtime),
            'detail_url' => route('attendance.overtimes.detail', $overtime),
            'overtime_date' => $overtimeDate->format('d M Y'),
            'time_range' => $plannedStartTime.' - '.$plannedEndTime,
            'duration' => $this->calculateDurationLabel($overtime->planned_start_time, $overtime->planned_end_time),
            'instruction' => $overtime->instruction ?: '-',
            'due_label' => $dueLabel,
            'pic_name' => $this->resolveUserDisplayName($overtime->assignedBy),
            'status' => $status,
            'progress_label' => 'Complete',
            'footer_status_label' => $this->overtimeFooterStatusLabel($status),
            'footer_status_badge_class' => $this->overtimeFooterStatusBadgeClass($status),
            'progress_percent' => $this->overtimeLifecycleProgressPercent($overtime),
        ];
    }

    /**
     * @return array{
     *     current_month_label:string,
     *     total_logged_hours_label:string,
     *     overtime_cap_label:string,
     *     overtime_cap_progress:int,
     *     average_extra_hours_label:string,
     *     tasks_finalized_label:string,
     *     pending_spv_approval_hours_label:string,
     *     pending_spv_approval_hours_progress:int,
     *     completed_locked_hours_label:string,
     *     completed_locked_progress:int,
     *     estimated_extra_earnings_label:string,
     *     disputed_hours_label:string
     * }
     */
    private function buildOvertimeIndexSummary(?User $authenticatedUser): array
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $monthStart = $today->copy()->startOfMonth()->toDateString();
        $monthEnd = $today->copy()->endOfMonth()->toDateString();
        $monthlyOvertimes = $this->applyOvertimeAccessFilter(
            AttendanceOvertime::query()
                ->with(['projectTasks:id,overtime_id,status,completed_at'])
                ->select([
                    'id',
                    'employee_id',
                    'overtime_date',
                    'planned_start_time',
                    'planned_end_time',
                    'actual_start_time',
                    'actual_end_time',
                    'approved_start_time',
                    'approved_end_time',
                    'status',
                ])
                ->with(['lifecycleLogs:id,overtime_id,event_key,status'])
                ->whereBetween('overtime_date', [$monthStart, $monthEnd]),
            $authenticatedUser
        )->get();

        $activeOvertimes = $monthlyOvertimes
            ->reject(fn (AttendanceOvertime $overtime): bool => strtolower(trim((string) $overtime->status)) === self::OVERTIME_STATUS_CANCELLED);
        $loggedMinutes = $activeOvertimes
            ->sum(fn (AttendanceOvertime $overtime): int => $this->durationMinutesFromTimeValues($overtime->actual_start_time, $overtime->actual_end_time));
        $isPendingSupervisorOvertimeApproval = function (AttendanceOvertime $overtime): bool {
            if ($this->durationMinutesFromTimeValues($overtime->actual_start_time, $overtime->actual_end_time) === 0) {
                return false;
            }

            $verificationLog = $overtime->lifecycleLogs
                ->first(fn (OvertimeLifecycleLog $lifecycleLog): bool => (string) $lifecycleLog->event_key === 'task_hours_verification');

            return ! $verificationLog instanceof OvertimeLifecycleLog
                || $this->normalizeOvertimeLifecycleState((string) $verificationLog->status) !== 'completed';
        };
        $isVerifiedSupervisorOvertime = function (AttendanceOvertime $overtime): bool {
            $verificationLog = $overtime->lifecycleLogs
                ->first(fn (OvertimeLifecycleLog $lifecycleLog): bool => (string) $lifecycleLog->event_key === 'task_hours_verification');

            return $verificationLog instanceof OvertimeLifecycleLog
                && strtolower(trim((string) $verificationLog->status)) === 'verified';
        };
        $pendingSupervisorApprovalMinutes = $activeOvertimes
            ->filter($isPendingSupervisorOvertimeApproval)
            ->sum(fn (AttendanceOvertime $overtime): int => $this->durationMinutesFromTimeValues($overtime->actual_start_time, $overtime->actual_end_time));
        $completedMinutes = $activeOvertimes
            ->filter($isVerifiedSupervisorOvertime)
            ->sum(fn (AttendanceOvertime $overtime): int => $this->approvedOrActualDurationMinutes($overtime));
        $tasksFinalized = $activeOvertimes
            ->flatMap(fn (AttendanceOvertime $overtime): Collection => $overtime->projectTasks)
            ->filter(fn (ProjectTask $projectTask): bool => strtolower(trim((string) $projectTask->status)) === 'completed' || $projectTask->completed_at !== null)
            ->count();
        $overtimeCapHours = 40;
        $loggedHours = round($loggedMinutes / 60, 2);
        $pendingSupervisorApprovalHours = round($pendingSupervisorApprovalMinutes / 60, 2);
        $completedHours = round($completedMinutes / 60, 2);
        $weeksElapsedThisMonth = max(1, (int) ceil((float) $today->day / 7));
        $averageExtraHours = $loggedHours / $weeksElapsedThisMonth;

        return [
            'current_month_label' => $today->format('M'),
            'total_logged_hours_label' => $this->formatOvertimeSummaryHours($loggedHours),
            'overtime_cap_label' => $this->formatOvertimeSummaryHours($loggedHours, 'H').' ('.min(100, (int) round(($loggedHours / $overtimeCapHours) * 100)).'%)',
            'overtime_cap_progress' => min(100, (int) round(($loggedHours / $overtimeCapHours) * 100)),
            'average_extra_hours_label' => $this->formatOvertimeSummaryHours($averageExtraHours, 'H').' / Week',
            'tasks_finalized_label' => $tasksFinalized.' '.($tasksFinalized === 1 ? 'Task' : 'Tasks'),
            'pending_spv_approval_hours_label' => $this->formatOvertimeSummaryHours($pendingSupervisorApprovalHours),
            'pending_spv_approval_hours_progress' => min(100, (int) round(($pendingSupervisorApprovalHours / $overtimeCapHours) * 100)),
            'completed_locked_hours_label' => $this->formatOvertimeSummaryHours($completedHours),
            'completed_locked_progress' => min(100, (int) round(($completedHours / $overtimeCapHours) * 100)),
            'estimated_extra_earnings_label' => 'Rp 0',
            'disputed_hours_label' => '0 Hours',
        ];
    }

    private function normalizeOvertimeIndexStatusFilter(mixed $status): ?string
    {
        $normalizedStatus = is_string($status) ? strtolower(trim($status)) : '';
        if ($normalizedStatus === '' || $normalizedStatus === 'all') {
            return null;
        }

        return in_array($normalizedStatus, [
            self::OVERTIME_STATUS_ASSIGNED,
            self::OVERTIME_STATUS_IN_PROGRESS,
            self::OVERTIME_STATUS_COMPLETED,
            self::OVERTIME_STATUS_CANCELLED,
        ], true) ? $normalizedStatus : null;
    }

    private function normalizeOvertimeIndexTimeframeFilter(mixed $timeframe): string
    {
        $normalizedTimeframe = is_string($timeframe) ? strtolower(trim($timeframe)) : '';

        return in_array($normalizedTimeframe, ['all', 'this_month', 'last_month', 'year_to_date'], true)
            ? $normalizedTimeframe
            : 'year_to_date';
    }

    private function applyOvertimeIndexTimeframeFilter(Builder $overtimesQuery, string $timeframeFilter): void
    {
        if ($timeframeFilter === 'all') {
            return;
        }

        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        if ($timeframeFilter === 'this_month') {
            $overtimesQuery->whereBetween('overtime_date', [
                $today->copy()->startOfMonth()->toDateString(),
                $today->copy()->endOfMonth()->toDateString(),
            ]);

            return;
        }

        if ($timeframeFilter === 'last_month') {
            $lastMonth = $today->copy()->subMonthNoOverflow();
            $overtimesQuery->whereBetween('overtime_date', [
                $lastMonth->copy()->startOfMonth()->toDateString(),
                $lastMonth->copy()->endOfMonth()->toDateString(),
            ]);

            return;
        }

        $overtimesQuery->whereBetween('overtime_date', [
            $today->copy()->startOfYear()->toDateString(),
            $today->copy()->endOfYear()->toDateString(),
        ]);
    }

    private function overtimeFooterStatusLabel(string $status): string
    {
        return match (strtolower(trim($status))) {
            self::OVERTIME_STATUS_COMPLETED => 'Completed',
            self::OVERTIME_STATUS_CANCELLED => 'Cancelled',
            default => 'Pending',
        };
    }

    private function overtimeFooterStatusBadgeClass(string $status): string
    {
        return match (strtolower(trim($status))) {
            self::OVERTIME_STATUS_COMPLETED => 'badge-success light',
            self::OVERTIME_STATUS_CANCELLED => 'badge-danger light',
            default => 'badge-warning light',
        };
    }

    private function overtimeLifecycleProgressPercent(AttendanceOvertime $overtime): int
    {
        $lifecycleLogs = $overtime->lifecycleLogs;
        $totalLifecycleLogs = $lifecycleLogs->count();
        if ($totalLifecycleLogs === 0) {
            return 0;
        }

        $completedLifecycleLogs = $lifecycleLogs
            ->filter(fn (OvertimeLifecycleLog $lifecycleLog): bool => $this->normalizeOvertimeLifecycleState((string) $lifecycleLog->status) === 'completed')
            ->count();

        return (int) round(($completedLifecycleLogs / $totalLifecycleLogs) * 100);
    }

    /**
     * @return array{
     *     staff_name:string,
     *     supervisor_name:string,
     *     log_status:string,
     *     overtime_date:string,
     *     planned_time_range:string,
     *     actual_time_range:string,
     *     approved_time_range:string,
     *     log_time_range:string,
     *     has_actual_time:bool,
     *     has_approved_time:bool,
     *     is_pic_verified:bool,
     *     time_changed:bool,
     *     planned_duration:string,
     *     actual_duration:string,
     *     approved_duration:string,
     *     log_duration:string,
     *     duration_changed:bool,
     *     overtime_card_duration:string,
     *     overtime_card_start:string,
     *     overtime_card_ended:string,
     *     overtime_card_current_time:string,
     *     clock_in_allowed:bool,
     *     clock_in_unavailable_title:string,
     *     clock_in_unavailable_message:string,
     *     clock_in_window_start_label:string,
     *     clock_in_window_end_label:string,
     *     clock_out_allowed:bool,
     *     clock_out_unavailable_title:string,
     *     clock_out_unavailable_message:string,
     *     modal_date_label:string,
     *     scheduled_start_label:string,
     *     scheduled_end_label:string,
     *     actual_start_label:string,
     *     actual_end_label:string,
     *     target_duration_label:string,
     *     actual_duration_label:string,
     *     update_url:string,
     *     employee_id:string,
     *     pic_user_id:string,
     *     overtime_date_input:string,
     *     planned_start_time_value:string,
     *     planned_end_time_value:string,
     *     actual_start_time_value:string|null,
     *     actual_end_time_value:string|null,
     *     instruction:string,
     *     calculated_hours:string,
     *     estimated_earnings:string,
     *     payout_period:string,
     *     verified_note:string,
     *     verified_datetime:string,
     *     supervisor_approver:string,
     *     supervisor_datetime:string,
     *     director_approver:string,
     *     director_datetime:string
     * }
     */
    private function buildOvertimeDetailSummary(AttendanceOvertime $overtime): array
    {
        $overtime->loadMissing('employee.profile', 'employee.user', 'assignedBy.employee.profile', 'lifecycleLogs.actor.employee.profile');

        $plannedStartTime = $this->normalizeTimeString($overtime->planned_start_time);
        $plannedEndTime = $this->normalizeTimeString($overtime->planned_end_time);
        $actualStartTime = $this->normalizeTimeString($overtime->actual_start_time);
        $actualEndTime = $this->normalizeTimeString($overtime->actual_end_time);
        $approvedStartTime = $this->normalizeTimeString($overtime->approved_start_time);
        $approvedEndTime = $this->normalizeTimeString($overtime->approved_end_time);
        $overtimeDate = Carbon::parse($overtime->overtime_date)->timezone('Asia/Jakarta');
        $hasActualTime = $actualStartTime !== '-' && $actualEndTime !== '-';
        $hasApprovedTime = $approvedStartTime !== '-' && $approvedEndTime !== '-';
        $plannedTimeRange = $plannedStartTime.' - '.$plannedEndTime;
        $actualTimeRange = $hasActualTime ? $actualStartTime.' - '.$actualEndTime : '-';
        $approvedTimeRange = $hasApprovedTime ? $approvedStartTime.' - '.$approvedEndTime : '-';
        $plannedDuration = $this->calculateDurationLabel($overtime->planned_start_time, $overtime->planned_end_time);
        $actualDuration = $hasActualTime
            ? $this->calculateDurationLabel($overtime->actual_start_time, $overtime->actual_end_time)
            : '-';
        $approvedDuration = $hasApprovedTime
            ? $this->calculateDurationLabel($overtime->approved_start_time, $overtime->approved_end_time)
            : '-';
        $verificationLog = $this->overtimeLifecycleLog($overtime, 'task_hours_verification');
        $isPicVerified = $verificationLog instanceof OvertimeLifecycleLog
            && strtolower(trim((string) $verificationLog->status)) === 'verified';
        $logTimeRange = $isPicVerified
            ? ($hasApprovedTime ? $approvedTimeRange : ($hasActualTime ? $actualTimeRange : $plannedTimeRange))
            : ($hasActualTime ? $actualTimeRange : $plannedTimeRange);
        $logDuration = $isPicVerified
            ? ($hasApprovedTime ? $approvedDuration : ($hasActualTime ? $actualDuration : $plannedDuration))
            : ($hasActualTime ? $actualDuration : $plannedDuration);
        $actualEndDateTime = $this->formatActualEndDateTime($overtime);
        $directorApprovalLog = $this->overtimeLifecycleLog($overtime, 'director_approval');
        $directorApprover = $directorApprovalLog?->actor instanceof User
            ? $directorApprovalLog->actor
            : $this->resolveOvertimeDirectorApprover($overtime);
        $clockInWindow = $this->resolveOvertimeClockInWindow($overtime);
        $hasActualStartTime = $actualStartTime !== '-';
        $hasAttendanceCheckInToday = $this->hasAttendanceCheckInToday((string) $overtime->employee_id);
        $clockInUnavailableTitle = '';
        $clockInUnavailableMessage = '';
        if ($hasActualStartTime) {
            $clockInUnavailableTitle = 'Absen Lembur Sudah Dilakukan';
            $clockInUnavailableMessage = 'Sesi lembur sudah berjalan. Silakan selesaikan task lembur, lalu lakukan clock out setelah task sudah dikerjakan.';
        } elseif (! $hasAttendanceCheckInToday) {
            $clockInUnavailableTitle = 'Absen Kehadiran Belum Dilakukan';
            $clockInUnavailableMessage = 'Anda belum melakukan absen kehadiran hari ini. Silakan absen masuk terlebih dahulu sebelum absen lembur.';
        } elseif (! $clockInWindow['is_allowed']) {
            $clockInUnavailableTitle = $clockInWindow['state'] === 'after_window'
                ? 'Batas Absen Lembur Sudah Lewat'
                : 'Absen Lembur Belum Tersedia';
            $clockInUnavailableMessage = $clockInWindow['message'];
        }
        $clockOutReadiness = $this->resolveOvertimeClockOutReadiness(
            $overtime,
            $this->normalizeStoreTimeValue($overtime->actual_start_time),
            $actualEndTime !== '-'
        );

        return [
            'staff_name' => $this->resolveEmployeeDisplayName($overtime->employee),
            'supervisor_name' => $this->resolveUserDisplayName($overtime->assignedBy),
            'log_status' => $isPicVerified ? 'Completed' : $this->overtimeDetailStatusLabel((string) ($overtime->status ?? self::OVERTIME_STATUS_ASSIGNED)),
            'overtime_date' => $overtimeDate->format('d M Y'),
            'planned_time_range' => $plannedTimeRange,
            'actual_time_range' => $actualTimeRange,
            'approved_time_range' => $approvedTimeRange,
            'log_time_range' => $logTimeRange,
            'has_actual_time' => $hasActualTime,
            'has_approved_time' => $hasApprovedTime,
            'is_pic_verified' => $isPicVerified,
            'time_changed' => ($isPicVerified ? $logTimeRange : $actualTimeRange) !== '-' && ($isPicVerified ? $logTimeRange : $actualTimeRange) !== $plannedTimeRange,
            'planned_duration' => $plannedDuration,
            'actual_duration' => $actualDuration,
            'approved_duration' => $approvedDuration,
            'log_duration' => $logDuration,
            'duration_changed' => ($isPicVerified ? $logDuration : $actualDuration) !== '-' && ($isPicVerified ? $logDuration : $actualDuration) !== $plannedDuration,
            'overtime_card_duration' => $hasActualTime
                ? $this->calculateDurationClockLabel($overtime->actual_start_time, $overtime->actual_end_time)
                : '--:--',
            'overtime_card_start' => $actualStartTime !== '-' ? $actualStartTime : '--:--',
            'overtime_card_ended' => $actualEndTime !== '-' ? $actualEndTime : '--:--',
            'overtime_card_current_time' => Carbon::now('Asia/Jakarta')->format('H:i:s'),
            'clock_in_allowed' => ! $hasActualStartTime && $hasAttendanceCheckInToday && $clockInWindow['is_allowed'],
            'clock_in_unavailable_title' => $clockInUnavailableTitle,
            'clock_in_unavailable_message' => $clockInUnavailableMessage,
            'clock_in_window_start_label' => $clockInWindow['start_label'],
            'clock_in_window_end_label' => $clockInWindow['end_label'],
            'can_create_task' => $this->canCreateOvertimeTask($overtime),
            'clock_out_allowed' => $clockOutReadiness['is_allowed'],
            'clock_out_unavailable_title' => $clockOutReadiness['title'],
            'clock_out_unavailable_message' => $clockOutReadiness['message'],
            'modal_date_label' => $overtimeDate->format('D, d M Y'),
            'scheduled_start_label' => $this->formatOvertimeModalDateTimeLabel($overtime, $overtime->planned_start_time),
            'scheduled_end_label' => $this->formatOvertimeModalDateTimeLabel($overtime, $overtime->planned_end_time),
            'actual_start_label' => $actualStartTime !== '-' ? $this->formatOvertimeModalDateTimeLabel($overtime, $overtime->actual_start_time) : '-',
            'actual_end_label' => $actualEndTime !== '-' ? $this->formatOvertimeModalDateTimeLabel($overtime, $overtime->actual_end_time) : '-',
            'target_duration_label' => $this->formatDurationSummaryLabel($overtime->planned_start_time, $overtime->planned_end_time),
            'actual_duration_label' => $hasActualTime ? $this->formatDurationSummaryLabel($overtime->actual_start_time, $overtime->actual_end_time) : '-',
            'update_url' => route('attendance.overtimes.update', $overtime),
            'employee_id' => (string) $overtime->employee_id,
            'pic_user_id' => (string) $overtime->assigned_by,
            'overtime_date_input' => $overtimeDate->format('d/m/Y'),
            'planned_start_time_value' => $this->normalizeStoreTimeValue($overtime->planned_start_time) ?? '',
            'planned_end_time_value' => $this->normalizeStoreTimeValue($overtime->planned_end_time) ?? '',
            'actual_start_time_value' => $this->normalizeStoreTimeValue($overtime->actual_start_time),
            'actual_end_time_value' => $this->normalizeStoreTimeValue($overtime->actual_end_time),
            'instruction' => trim((string) ($overtime->instruction ?? '')) !== '' ? (string) $overtime->instruction : '-',
            'calculated_hours' => '-',
            'estimated_earnings' => '-',
            'payout_period' => 'Included in '.Carbon::parse($overtime->overtime_date)->timezone('Asia/Jakarta')->format('F Y').' Payroll',
            'verified_note' => $hasActualTime ? 'Actual overtime hours recorded' : 'Waiting for clock-out',
            'verified_datetime' => $actualEndDateTime !== '-' ? $actualEndDateTime : $this->formatOvertimeDetailLogDateTime($verificationLog),
            'supervisor_approver' => $this->resolveUserDisplayName($verificationLog?->actor ?? $overtime->assignedBy),
            'supervisor_datetime' => $this->formatOvertimeDetailLogDateTime($verificationLog),
            'director_approver' => $this->resolveUserDisplayName($directorApprover),
            'director_datetime' => $this->formatOvertimeDetailLogDateTime($directorApprovalLog),
        ];
    }

    private function formatOvertimeModalDateTimeLabel(AttendanceOvertime $overtime, mixed $timeValue): string
    {
        $normalizedTime = $this->normalizeTimeString($timeValue);
        if ($normalizedTime === '-') {
            return '-';
        }

        return Carbon::parse($overtime->overtime_date)
            ->timezone('Asia/Jakarta')
            ->format('D, d M Y').' - '.$normalizedTime;
    }

    /**
     * @return array{is_allowed:bool,state:string,message:string,start_label:string,end_label:string}
     */
    private function resolveOvertimeClockInWindow(AttendanceOvertime $overtime, ?Carbon $now = null): array
    {
        $scheduledStartTime = $this->normalizeStoreTimeValue($overtime->planned_start_time);
        $scheduledStartAt = $this->overtimeDateTimeFromTime($overtime, $scheduledStartTime);
        if (! $scheduledStartAt instanceof Carbon) {
            return [
                'is_allowed' => false,
                'state' => 'invalid_schedule',
                'message' => 'Jadwal mulai lembur belum valid. Silakan hubungi PIC untuk mengubah jadwal lemburnya.',
                'start_label' => '-',
                'end_label' => '-',
            ];
        }

        $windowStartAt = $scheduledStartAt->copy()->subMinutes(self::OVERTIME_CLOCK_TOLERANCE_MINUTES);
        $windowEndAt = $scheduledStartAt->copy()->addMinutes(self::OVERTIME_CLOCK_TOLERANCE_MINUTES);
        $currentTime = ($now instanceof Carbon ? $now : Carbon::now('Asia/Jakarta'))->copy()->timezone('Asia/Jakarta');

        if ($currentTime->lt($windowStartAt)) {
            return [
                'is_allowed' => false,
                'state' => 'before_window',
                'message' => 'Clock in lembur tersedia mulai '.$this->formatOvertimeClockInWindowLabel($windowStartAt).' sampai '.$this->formatOvertimeClockInWindowLabel($windowEndAt).'.',
                'start_label' => $this->formatOvertimeClockInWindowLabel($windowStartAt),
                'end_label' => $this->formatOvertimeClockInWindowLabel($windowEndAt),
            ];
        }

        if ($currentTime->gt($windowEndAt)) {
            return [
                'is_allowed' => false,
                'state' => 'after_window',
                'message' => 'Waktu absen lembur sudah melewati batas yang ditetapkan PIC. Silakan hubungi PIC untuk mengubah jadwal lemburnya.',
                'start_label' => $this->formatOvertimeClockInWindowLabel($windowStartAt),
                'end_label' => $this->formatOvertimeClockInWindowLabel($windowEndAt),
            ];
        }

        return [
            'is_allowed' => true,
            'state' => 'allowed',
            'message' => '',
            'start_label' => $this->formatOvertimeClockInWindowLabel($windowStartAt),
            'end_label' => $this->formatOvertimeClockInWindowLabel($windowEndAt),
        ];
    }

    private function formatOvertimeClockInWindowLabel(Carbon $dateTime): string
    {
        return $dateTime->copy()->timezone('Asia/Jakarta')->format('D, d M Y H:i');
    }

    /**
     * @return array{is_allowed:bool,title:string,message:string,start_label:string,end_label:string}
     */
    private function resolveOvertimeClockOutWindow(AttendanceOvertime $overtime, ?Carbon $now = null): array
    {
        $scheduledStartTime = $this->normalizeStoreTimeValue($overtime->planned_start_time);
        $scheduledEndTime = $this->normalizeStoreTimeValue($overtime->planned_end_time);
        $scheduledStartAt = $this->overtimeDateTimeFromTime($overtime, $scheduledStartTime);
        $scheduledEndAt = $this->overtimeDateTimeFromTime($overtime, $scheduledEndTime);
        if (! $scheduledStartAt instanceof Carbon || ! $scheduledEndAt instanceof Carbon) {
            return [
                'is_allowed' => false,
                'title' => 'Jadwal Lembur Tidak Valid',
                'message' => 'Jadwal selesai lembur belum valid. Silakan hubungi PIC untuk mengubah jadwal lemburnya.',
                'start_label' => '-',
                'end_label' => '-',
            ];
        }

        if ($scheduledEndAt->lte($scheduledStartAt)) {
            $scheduledEndAt->addDay();
        }

        $windowEndAt = $scheduledEndAt->copy()->addMinutes(self::OVERTIME_CLOCK_TOLERANCE_MINUTES);
        $currentTime = ($now instanceof Carbon ? $now : Carbon::now('Asia/Jakarta'))->copy()->timezone('Asia/Jakarta');

        if ($currentTime->gt($windowEndAt)) {
            return [
                'is_allowed' => false,
                'title' => 'Batas Clock Out Lembur Sudah Lewat',
                'message' => 'Waktu clock out lembur sudah melewati batas toleransi 30 menit dari jadwal selesai. Silakan hubungi PIC untuk mengubah jadwal lemburnya.',
                'start_label' => $this->formatOvertimeClockInWindowLabel($scheduledStartAt),
                'end_label' => $this->formatOvertimeClockInWindowLabel($windowEndAt),
            ];
        }

        return [
            'is_allowed' => true,
            'title' => '',
            'message' => '',
            'start_label' => $this->formatOvertimeClockInWindowLabel($scheduledStartAt),
            'end_label' => $this->formatOvertimeClockInWindowLabel($windowEndAt),
        ];
    }

    /**
     * @return array{is_allowed:bool,title:string,message:string,task_count:int,incomplete_task_count:int,completed_after_clock_in_count:int}
     */
    private function resolveOvertimeClockOutReadiness(AttendanceOvertime $overtime, ?string $actualStartTime, bool $hasActualEndTime, ?Carbon $now = null): array
    {
        $actualStartAt = $this->overtimeDateTimeFromTime($overtime, $actualStartTime);
        if (! $actualStartAt instanceof Carbon) {
            return [
                'is_allowed' => false,
                'title' => 'Absen Lembur Belum Dimulai',
                'message' => 'Silakan absen lembur terlebih dahulu sebelum mengakhiri sesi lembur.',
                'task_count' => 0,
                'incomplete_task_count' => 0,
                'completed_after_clock_in_count' => 0,
            ];
        }

        if ($hasActualEndTime) {
            return [
                'is_allowed' => false,
                'title' => 'Sesi Lembur Sudah Selesai',
                'message' => 'Sesi lembur ini sudah memiliki waktu selesai.',
                'task_count' => 0,
                'incomplete_task_count' => 0,
                'completed_after_clock_in_count' => 0,
            ];
        }

        $clockOutWindow = $this->resolveOvertimeClockOutWindow($overtime, $now);
        if (! $clockOutWindow['is_allowed']) {
            return [
                'is_allowed' => false,
                'title' => $clockOutWindow['title'],
                'message' => $clockOutWindow['message'],
                'task_count' => 0,
                'incomplete_task_count' => 0,
                'completed_after_clock_in_count' => 0,
            ];
        }

        $taskQuery = $this->buildCurrentOvertimeTaskQuery($overtime);
        $taskCount = (clone $taskQuery)->count();
        $incompleteTaskCount = (clone $taskQuery)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('completed_at')
                    ->whereRaw('LOWER(status) != ?', ['completed']);
            })
            ->count();
        $completedAfterClockInCount = (clone $taskQuery)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $actualStartAt->toDateTimeString())
            ->whereRaw('LOWER(status) = ?', ['completed'])
            ->count();

        if ($taskCount === 0) {
            return [
                'is_allowed' => false,
                'title' => 'Task Lembur Belum Disubmit',
                'message' => 'Silakan submit minimal satu task yang dikerjakan selama lembur, lalu ubah status task tersebut menjadi Completed sebelum mengakhiri sesi.',
                'task_count' => $taskCount,
                'incomplete_task_count' => $incompleteTaskCount,
                'completed_after_clock_in_count' => $completedAfterClockInCount,
            ];
        }

        if ($incompleteTaskCount > 0 || $completedAfterClockInCount === 0) {
            return [
                'is_allowed' => false,
                'title' => 'Task Lembur Belum Completed',
                'message' => 'Harap submit task lembur yang dikerjakan setelah clock-in, lalu pastikan statusnya Completed sebelum mengakhiri sesi.',
                'task_count' => $taskCount,
                'incomplete_task_count' => $incompleteTaskCount,
                'completed_after_clock_in_count' => $completedAfterClockInCount,
            ];
        }

        return [
            'is_allowed' => true,
            'title' => '',
            'message' => '',
            'task_count' => $taskCount,
            'incomplete_task_count' => 0,
            'completed_after_clock_in_count' => $completedAfterClockInCount,
        ];
    }

    private function formatDurationSummaryLabel(mixed $startTimeValue, mixed $endTimeValue): string
    {
        $totalMinutes = $this->durationDisplayMinutesFromTimeValues($startTimeValue, $endTimeValue);

        if ($totalMinutes === null) {
            return '-';
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        if ($hours === 0) {
            return sprintf('%d Minutes', $minutes);
        }

        if ($minutes === 0) {
            return sprintf('%d Hours', $hours);
        }

        return sprintf('%d Hours, %d Minutes', $hours, $minutes);
    }

    private function resolveOvertimeDirectorApprover(AttendanceOvertime $overtime): ?User
    {
        $overtime->loadMissing('employee.deployment');
        $companyId = $overtime->employee?->deployment?->current_company_id;

        $directorQuery = User::query()
            ->select(['id', 'username'])
            ->with('employee.profile')
            ->whereHas('roles', function ($query): void {
                $query->whereIn('name', [
                    'Board of Directors',
                    'board of directors',
                    'board_of_directors',
                    'board_of_director',
                    'board_of_rector',
                    'board of directur',
                    'board_of_directur',
                ]);
            });

        if (is_string($companyId) && trim($companyId) !== '') {
            $directorQuery->whereHas('employee.deployment', function ($query) use ($companyId): void {
                $query->where('current_company_id', trim($companyId));
            });
        }

        return $directorQuery
            ->orderBy('username')
            ->first();
    }

    private function overtimeLifecycleLog(AttendanceOvertime $overtime, string $eventKey): ?OvertimeLifecycleLog
    {
        return $overtime->lifecycleLogs
            ->first(fn (OvertimeLifecycleLog $lifecycleLog): bool => (string) $lifecycleLog->event_key === $eventKey);
    }

    private function formatOvertimeDetailLogDateTime(?OvertimeLifecycleLog $lifecycleLog): string
    {
        if (! $lifecycleLog instanceof OvertimeLifecycleLog || $lifecycleLog->happened_at === null) {
            return '-';
        }

        return Carbon::parse($lifecycleLog->happened_at)->timezone('Asia/Jakarta')->format('d M Y, H:i');
    }

    private function formatActualEndDateTime(AttendanceOvertime $overtime): string
    {
        $actualStartTime = $this->normalizeStoreTimeValue($overtime->actual_start_time);
        $actualEndTime = $this->normalizeStoreTimeValue($overtime->actual_end_time);

        if ($actualStartTime === null || $actualEndTime === null) {
            return '-';
        }

        try {
            $overtimeDate = Carbon::parse($overtime->overtime_date)->timezone('Asia/Jakarta')->format('Y-m-d');
            $actualStartAt = Carbon::createFromFormat('Y-m-d H:i:s', $overtimeDate.' '.$actualStartTime, 'Asia/Jakarta');
            $actualEndAt = Carbon::createFromFormat('Y-m-d H:i:s', $overtimeDate.' '.$actualEndTime, 'Asia/Jakarta');

            if ($actualEndAt->lessThanOrEqualTo($actualStartAt)) {
                $actualEndAt->addDay();
            }

            return $actualEndAt->format('d M Y, H:i');
        } catch (\Throwable) {
            return '-';
        }
    }

    private function overtimeDetailStatusLabel(string $status): string
    {
        return match (strtolower(trim($status))) {
            self::OVERTIME_STATUS_IN_PROGRESS => 'In Progress',
            self::OVERTIME_STATUS_COMPLETED => 'Completed',
            self::OVERTIME_STATUS_CANCELLED => 'Cancelled',
            default => 'Assigned',
        };
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'pic_user_id' => ['required', 'exists:users,id'],
            'overtime_date' => ['required', 'date_format:d/m/Y'],
            'planned_start_time' => ['required'],
            'planned_end_time' => ['required'],
            'instruction' => ['required', 'string', 'max:5000'],
            'actual_start_time' => ['nullable'],
            'actual_end_time' => ['nullable'],
            'status' => ['nullable', 'in:assigned,in_progress,completed,cancelled'],
        ]);

        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee');
        }

        $employeeId = trim((string) $validated['employee_id']);
        $assignedByUserId = trim((string) $validated['pic_user_id']);
        $authenticatedEmployeeId = trim((string) ($authenticatedUser?->employee?->id ?? ''));
        $isStaffUser = $this->isStaffUser($authenticatedUser);

        if ($isStaffUser && $authenticatedEmployeeId !== $employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Staff hanya dapat submit lembur untuk dirinya sendiri.',
            ], 403);
        }

        if ($isStaffUser && ! $this->hasOvertimeAssignmentForEmployee($authenticatedEmployeeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum mendapatkan assignment lembur.',
            ], 403);
        }

        if ($isStaffUser && ! $this->hasAttendanceCheckInToday($authenticatedEmployeeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus absen masuk hari ini sebelum submit lembur.',
            ], 422);
        }

        if ($employeeId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data employee belum tersedia untuk user ini.',
            ], 422);
        }

        if ($assignedByUserId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data PIC belum tersedia untuk aksi ini.',
            ], 422);
        }

        $overtimeDate = Carbon::createFromFormat('d/m/Y', $validated['overtime_date'])->format('Y-m-d');
        $startTime = $this->normalizeStoreTimeValue($validated['planned_start_time']);
        $endTime = $this->normalizeStoreTimeValue($validated['planned_end_time']);
        $actualStartTime = $this->normalizeStoreTimeValue($validated['actual_start_time'] ?? null);
        $actualEndTime = $this->normalizeStoreTimeValue($validated['actual_end_time'] ?? null);

        if (! $startTime || ! $endTime) {
            return response()->json([
                'success' => false,
                'message' => 'Format jam lembur tidak valid.',
            ], 422);
        }

        if (($validated['actual_start_time'] ?? null) !== null && ! $actualStartTime) {
            return response()->json([
                'success' => false,
                'message' => 'Format aktual jam mulai tidak valid.',
            ], 422);
        }

        if (($validated['actual_end_time'] ?? null) !== null && ! $actualEndTime) {
            return response()->json([
                'success' => false,
                'message' => 'Format aktual jam selesai tidak valid.',
            ], 422);
        }

        $assignmentActor = User::query()->find($assignedByUserId);
        DB::transaction(function () use ($actualEndTime, $actualStartTime, $assignmentActor, $employeeId, $isStaffUser, $overtimeDate, $assignedByUserId, $startTime, $endTime, $validated): void {
            $overtime = AttendanceOvertime::create([
                'employee_id' => $employeeId,
                'assigned_by' => $assignedByUserId,
                'overtime_date' => $overtimeDate,
                'planned_start_time' => $startTime,
                'planned_end_time' => $endTime,
                'instruction' => $validated['instruction'],
                'actual_start_time' => $actualStartTime,
                'actual_end_time' => $actualEndTime,
                'calculated_hours' => null,
                'status' => $this->resolveOvertimeStatus(
                    $isStaffUser ? null : ($validated['status'] ?? null),
                    $actualStartTime,
                    $actualEndTime
                ),
            ]);

            $this->createInitialOvertimeLifecycleLogs($overtime, $assignmentActor);
            $this->syncOvertimeLifecycleLogs($overtime, Auth::user() instanceof User ? Auth::user() : $assignmentActor);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan lembur berhasil disimpan.',
        ]);
    }

    public function show(AttendanceOvertime $attendanceOvertime): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canAccessOvertime($authenticatedUser, $attendanceOvertime)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke data lembur ini.',
            ], 403);
        }

        $attendanceOvertime->loadMissing([
            'employee.user:id,username',
            'employee.profile:id,employee_id,name',
            'assignedBy:id,username',
        ]);
        $overtimeDate = $attendanceOvertime->overtime_date instanceof Carbon
            ? $attendanceOvertime->overtime_date
            : Carbon::parse($attendanceOvertime->overtime_date);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendanceOvertime->id,
                'record_number' => (string) ($attendanceOvertime->record_number ?? ''),
                'employee_id' => $attendanceOvertime->employee_id,
                'pic_user_id' => $attendanceOvertime->assigned_by,
                'staff_name' => $this->resolveEmployeeDisplayName($attendanceOvertime->employee),
                'pic_name' => $this->resolveUserDisplayName($attendanceOvertime->assignedBy),
                'overtime_date' => $overtimeDate->format('d M Y'),
                'overtime_date_input' => $overtimeDate->format('d/m/Y'),
                'planned_start_time' => $this->normalizeStoreTimeValue($attendanceOvertime->planned_start_time),
                'planned_end_time' => $this->normalizeStoreTimeValue($attendanceOvertime->planned_end_time),
                'actual_start_time' => $this->normalizeStoreTimeValue($attendanceOvertime->actual_start_time),
                'actual_end_time' => $this->normalizeStoreTimeValue($attendanceOvertime->actual_end_time),
                'duration' => $this->calculateDurationLabel($attendanceOvertime->planned_start_time, $attendanceOvertime->planned_end_time),
                'status' => (string) ($attendanceOvertime->status ?? self::OVERTIME_STATUS_ASSIGNED),
                'instruction' => $attendanceOvertime->instruction ?: '-',
            ],
        ]);
    }

    public function update(Request $request, AttendanceOvertime $attendanceOvertime): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canManageOvertimeAction($authenticatedUser, $attendanceOvertime)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah data lembur ini.',
            ], 403);
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'pic_user_id' => ['required', 'exists:users,id'],
            'overtime_date' => ['required', 'date_format:d/m/Y'],
            'planned_start_time' => ['required'],
            'planned_end_time' => ['required'],
            'instruction' => ['required', 'string', 'max:5000'],
            'actual_start_time' => ['nullable'],
            'actual_end_time' => ['nullable'],
            'status' => ['nullable', 'in:assigned,in_progress,completed,cancelled'],
        ]);

        $overtimeDate = Carbon::createFromFormat('d/m/Y', $validated['overtime_date'])->format('Y-m-d');
        $startTime = $this->normalizeStoreTimeValue($validated['planned_start_time']);
        $endTime = $this->normalizeStoreTimeValue($validated['planned_end_time']);
        $hasActualStartTimeInput = array_key_exists('actual_start_time', $validated);
        $hasActualEndTimeInput = array_key_exists('actual_end_time', $validated);
        $currentActualStartTime = $this->normalizeStoreTimeValue($attendanceOvertime->actual_start_time);
        $currentActualEndTime = $this->normalizeStoreTimeValue($attendanceOvertime->actual_end_time);
        $actualStartTime = $hasActualStartTimeInput
            ? $this->normalizeStoreTimeValue($validated['actual_start_time'])
            : $currentActualStartTime;
        $actualEndTime = $hasActualEndTimeInput
            ? $this->normalizeStoreTimeValue($validated['actual_end_time'])
            : $currentActualEndTime;

        if (! $startTime || ! $endTime) {
            return response()->json([
                'success' => false,
                'message' => 'Format jam lembur tidak valid.',
            ], 422);
        }

        if ($hasActualStartTimeInput && ($validated['actual_start_time'] ?? null) !== null && ! $actualStartTime) {
            return response()->json([
                'success' => false,
                'message' => 'Format aktual jam mulai tidak valid.',
            ], 422);
        }

        if ($hasActualEndTimeInput && ($validated['actual_end_time'] ?? null) !== null && ! $actualEndTime) {
            return response()->json([
                'success' => false,
                'message' => 'Format aktual jam selesai tidak valid.',
            ], 422);
        }

        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $authenticatedEmployeeId = is_string($authenticatedUser?->employee?->id) ? trim($authenticatedUser->employee->id) : '';
        if ($isStaffUser && ! $this->hasAttendanceCheckInToday($authenticatedEmployeeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan absen kehadiran hari ini. Silakan absen masuk terlebih dahulu sebelum absen lembur.',
            ], 422);
        }

        if ($isStaffUser && $hasActualStartTimeInput && $currentActualStartTime === null && $actualStartTime !== null) {
            $clockInWindow = $this->resolveOvertimeClockInWindow($attendanceOvertime);
            if (! $clockInWindow['is_allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $clockInWindow['message'],
                ], 422);
            }
        }

        if ($isStaffUser && $hasActualEndTimeInput && $currentActualEndTime === null && $actualEndTime !== null) {
            $clockOutReadiness = $this->resolveOvertimeClockOutReadiness($attendanceOvertime, $actualStartTime, false);
            if (! $clockOutReadiness['is_allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $clockOutReadiness['message'],
                ], 422);
            }
        }

        $status = $this->resolveOvertimeStatus(
            $isStaffUser ? null : ($validated['status'] ?? ($attendanceOvertime->status ?? self::OVERTIME_STATUS_ASSIGNED)),
            $actualStartTime,
            $actualEndTime
        );

        $updatePayload = [
            'employee_id' => trim((string) $validated['employee_id']),
            'assigned_by' => trim((string) $validated['pic_user_id']),
            'overtime_date' => $overtimeDate,
            'planned_start_time' => $startTime,
            'planned_end_time' => $endTime,
            'instruction' => $validated['instruction'],
            'actual_start_time' => $actualStartTime,
            'actual_end_time' => $actualEndTime,
            'status' => $status,
        ];

        if ($isStaffUser) {
            $updatePayload['employee_id'] = (string) $attendanceOvertime->employee_id;
            $updatePayload['assigned_by'] = (string) $attendanceOvertime->assigned_by;
            $updatePayload['overtime_date'] = $attendanceOvertime->overtime_date instanceof Carbon
                ? $attendanceOvertime->overtime_date->format('Y-m-d')
                : Carbon::parse($attendanceOvertime->overtime_date)->format('Y-m-d');
            $updatePayload['planned_start_time'] = (string) $attendanceOvertime->planned_start_time;
            $updatePayload['planned_end_time'] = (string) $attendanceOvertime->planned_end_time;
            $updatePayload['instruction'] = (string) $attendanceOvertime->instruction;
        }

        DB::transaction(function () use ($attendanceOvertime, $authenticatedUser, $updatePayload): void {
            $attendanceOvertime->update($updatePayload);
            $attendanceOvertime->refresh();
            $this->syncOvertimeLifecycleLogs($attendanceOvertime, $authenticatedUser instanceof User ? $authenticatedUser : null);
        });

        if ($isStaffUser && $status === self::OVERTIME_STATUS_COMPLETED && $actualStartTime && $actualEndTime) {
            $this->syncAttendanceOvertimeLink(
                (string) $attendanceOvertime->employee_id,
                $attendanceOvertime->overtime_date instanceof Carbon
                    ? $attendanceOvertime->overtime_date->format('Y-m-d')
                    : Carbon::parse($attendanceOvertime->overtime_date)->format('Y-m-d'),
                (string) $attendanceOvertime->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Data lembur berhasil diperbarui.',
        ]);
    }

    public function updateTask(Request $request, AttendanceOvertime $attendanceOvertime, ProjectTask $projectTask): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canUpdateOvertimeTask($authenticatedUser, $attendanceOvertime, $projectTask)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah task lembur ini.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'due_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'blockers' => ['nullable', 'string', 'max:5000'],
            'task_category' => ['nullable', 'in:daily,project'],
            'project_id' => ['nullable', 'uuid'],
            'status' => ['nullable', 'in:pending,in_progress,completed,cancelled'],
        ]);

        $status = is_string($validated['status'] ?? null)
            ? strtolower(trim($validated['status']))
            : strtolower(trim((string) ($projectTask->status ?? 'pending')));

        $projectId = $projectTask->project_id;
        if (array_key_exists('task_category', $validated)) {
            $projectId = null;
            if ($validated['task_category'] === 'project') {
                $projectId = is_string($validated['project_id'] ?? null) ? trim($validated['project_id']) : '';
                if ($projectId === '' || ! $this->employeeIsProjectMember((string) $attendanceOvertime->employee_id, $projectId)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Project tidak tersedia untuk staff ini.',
                    ], 422);
                }
            }
        }

        $updatePayload = [
            'overtime_id' => (string) $attendanceOvertime->id,
            'project_id' => $projectId,
        ];

        foreach (['title', 'description', 'priority'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updatePayload[$field] = $validated[$field];
            }
        }

        if (array_key_exists('blockers', $validated)) {
            $updatePayload['blockers'] = $this->nullableStringValue($validated['blockers'] ?? null);
        }

        if (array_key_exists('attachment_path', $validated)) {
            $updatePayload['attachment_path'] = $this->nullableStringValue($validated['attachment_path'] ?? null);
        }

        if (array_key_exists('status', $validated)) {
            $updatePayload['status'] = $status;
            $updatePayload['completed_at'] = $status === 'completed'
                ? Carbon::now('Asia/Jakarta')
                : null;
        }

        if (array_key_exists('start_date', $validated)) {
            $updatePayload['start_date'] = $validated['start_date'];
        }

        if (array_key_exists('due_date', $validated)) {
            $updatePayload['due_date'] = $validated['due_date'];
        }

        DB::transaction(function () use ($attendanceOvertime, $projectTask, $updatePayload, $authenticatedUser): void {
            $projectTask->update($updatePayload);
            $attendanceOvertime->refresh();
            $this->syncOvertimeLifecycleLogs($attendanceOvertime, $authenticatedUser instanceof User ? $authenticatedUser : null);
        });

        return response()->json([
            'success' => true,
            'message' => 'Task lembur berhasil diperbarui.',
        ]);
    }

    public function storeTask(Request $request, AttendanceOvertime $attendanceOvertime): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canManageOvertimeAction($authenticatedUser, $attendanceOvertime)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menambahkan task lembur ini.',
            ], 403);
        }

        if (! $this->canCreateOvertimeTask($attendanceOvertime)) {
            return response()->json([
                'success' => false,
                'message' => 'Task lembur hanya dapat ditambahkan setelah Overtime Clock In.',
            ], 422);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'priority' => ['required', 'in:low,medium,high'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'blockers' => ['nullable', 'string', 'max:5000'],
            'task_category' => ['required', 'in:daily,project'],
            'project_id' => ['nullable', 'uuid'],
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        $projectId = null;
        if ($validated['task_category'] === 'project') {
            $projectId = is_string($validated['project_id'] ?? null) ? trim($validated['project_id']) : '';
            if ($projectId === '' || ! $this->employeeIsProjectMember((string) $attendanceOvertime->employee_id, $projectId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project tidak tersedia untuk staff ini.',
                ], 422);
            }
        }

        $status = strtolower(trim((string) $validated['status']));

        DB::transaction(function () use ($attendanceOvertime, $projectId, $status, $validated, $authenticatedUser): void {
            ProjectTask::query()->create([
                'project_id' => $projectId,
                'employee_id' => (string) $attendanceOvertime->employee_id,
                'assigned_by' => $attendanceOvertime->assigned_by,
                'overtime_id' => (string) $attendanceOvertime->id,
                'title' => trim((string) $validated['title']),
                'description' => trim((string) $validated['description']),
                'blockers' => $this->nullableStringValue($validated['blockers'] ?? null),
                'attachment_path' => $this->nullableStringValue($validated['attachment_path'] ?? null),
                'status' => $status,
                'priority' => strtolower(trim((string) $validated['priority'])),
                'start_date' => $validated['start_date'],
                'due_date' => $validated['due_date'],
                'completed_at' => $status === 'completed' ? Carbon::now('Asia/Jakarta') : null,
            ]);

            $attendanceOvertime->refresh();
            $this->syncOvertimeLifecycleLogs($attendanceOvertime, $authenticatedUser instanceof User ? $authenticatedUser : null);
        });

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil ditambahkan.',
        ]);
    }

    public function destroyTask(AttendanceOvertime $attendanceOvertime, ProjectTask $projectTask): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canUpdateOvertimeTask($authenticatedUser, $attendanceOvertime, $projectTask)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus task lembur ini.',
            ], 403);
        }

        $projectTask->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil dihapus.',
        ]);
    }

    private function canUpdateOvertimeTask(?User $authenticatedUser, AttendanceOvertime $attendanceOvertime, ProjectTask $projectTask): bool
    {
        if (! $this->canManageOvertimeAction($authenticatedUser, $attendanceOvertime)) {
            return false;
        }

        if ((string) $projectTask->employee_id !== (string) $attendanceOvertime->employee_id) {
            return false;
        }

        $projectTaskOvertimeId = is_string($projectTask->overtime_id) ? trim($projectTask->overtime_id) : '';

        return $projectTaskOvertimeId === '' || $projectTaskOvertimeId === (string) $attendanceOvertime->id;
    }

    private function canCreateOvertimeTask(AttendanceOvertime $attendanceOvertime): bool
    {
        return in_array(strtolower(trim((string) $attendanceOvertime->status)), [
            self::OVERTIME_STATUS_IN_PROGRESS,
            self::OVERTIME_STATUS_COMPLETED,
            self::OVERTIME_STATUS_CANCELLED,
        ], true)
            && $this->normalizeStoreTimeValue($attendanceOvertime->actual_start_time) !== null;
    }

    private function employeeIsProjectMember(string $employeeId, string $projectId): bool
    {
        return ProjectMember::query()
            ->where('employee_id', trim($employeeId))
            ->where('project_id', trim($projectId))
            ->where('status', 'active')
            ->exists();
    }

    public function destroy(AttendanceOvertime $attendanceOvertime): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canManageOvertimeAction($authenticatedUser, $attendanceOvertime)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus data lembur ini.',
            ], 403);
        }

        $attendanceOvertime->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data lembur berhasil dihapus.',
        ]);
    }

    private function normalizeTimeString(mixed $timeValue): string
    {
        if (! is_string($timeValue) || trim($timeValue) === '') {
            return '-';
        }

        try {
            return Carbon::parse($timeValue)->format('H:i');
        } catch (\Throwable) {
            return substr($timeValue, 0, 5);
        }
    }

    private function resolveOvertimeStatus(mixed $requestedStatus, ?string $actualStartTime, ?string $actualEndTime): string
    {
        $normalizedStatus = is_string($requestedStatus)
            ? strtolower(trim($requestedStatus))
            : '';

        if ($normalizedStatus === self::OVERTIME_STATUS_CANCELLED) {
            return self::OVERTIME_STATUS_CANCELLED;
        }

        if ($actualStartTime && $actualEndTime) {
            return self::OVERTIME_STATUS_COMPLETED;
        }

        if ($actualStartTime) {
            return self::OVERTIME_STATUS_IN_PROGRESS;
        }

        if ($normalizedStatus === self::OVERTIME_STATUS_ASSIGNED) {
            return self::OVERTIME_STATUS_ASSIGNED;
        }

        return self::OVERTIME_STATUS_ASSIGNED;
    }

    private function calculateDurationLabel(mixed $startTimeValue, mixed $endTimeValue): string
    {
        $totalMinutes = $this->durationDisplayMinutesFromTimeValues($startTimeValue, $endTimeValue);

        if ($totalMinutes === null) {
            return '-';
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d Jam %02d Menit', $hours, $minutes);
    }

    private function durationMinutesFromTimeValues(mixed $startTimeValue, mixed $endTimeValue): int
    {
        if (! is_string($startTimeValue) || ! is_string($endTimeValue)) {
            return 0;
        }

        try {
            $startTime = Carbon::createFromFormat('H:i:s', $startTimeValue);
            $endTime = Carbon::createFromFormat('H:i:s', $endTimeValue);

            if ($endTime->lessThan($startTime)) {
                $endTime->addDay();
            }

            return $startTime->diffInMinutes($endTime);
        } catch (\Throwable) {
            return 0;
        }
    }

    private function approvedOrActualDurationMinutes(AttendanceOvertime $overtime): int
    {
        if (
            is_string($overtime->approved_start_time)
            && trim($overtime->approved_start_time) !== ''
            && is_string($overtime->approved_end_time)
            && trim($overtime->approved_end_time) !== ''
        ) {
            return $this->durationMinutesFromTimeValues($overtime->approved_start_time, $overtime->approved_end_time);
        }

        return $this->durationMinutesFromTimeValues($overtime->actual_start_time, $overtime->actual_end_time);
    }

    private function formatOvertimeSummaryHours(float $hours, string $suffix = 'Hours'): string
    {
        $roundedHours = round($hours, 1);
        $formattedHours = floor($roundedHours) === $roundedHours
            ? (string) (int) $roundedHours
            : number_format($roundedHours, 1, '.', '');

        return $formattedHours.' '.$suffix;
    }

    private function calculateDurationClockLabel(mixed $startTimeValue, mixed $endTimeValue): string
    {
        $totalMinutes = $this->durationDisplayMinutesFromTimeValues($startTimeValue, $endTimeValue);

        if ($totalMinutes === null) {
            return '--:--';
        }

        return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    }

    private function durationDisplayMinutesFromTimeValues(mixed $startTimeValue, mixed $endTimeValue): ?int
    {
        $startTime = $this->normalizeStoreTimeValue($startTimeValue);
        $endTime = $this->normalizeStoreTimeValue($endTimeValue);

        if ($startTime === null || $endTime === null) {
            return null;
        }

        try {
            $start = Carbon::createFromFormat('H:i:s', $startTime)->seconds(0);
            $end = Carbon::createFromFormat('H:i:s', $endTime)->seconds(0);

            if ($end->lessThan($start)) {
                $end->addDay();
            }

            return (int) $start->diffInMinutes($end);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeStoreTimeValue(mixed $timeValue): ?string
    {
        if (! is_string($timeValue) || trim($timeValue) === '') {
            return null;
        }

        try {
            if (strlen($timeValue) === 5) {
                return Carbon::createFromFormat('H:i', $timeValue)->format('H:i:s');
            }

            return Carbon::createFromFormat('H:i:s', $timeValue)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function isBoardOfDirectur(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('board of directur')
            || $normalizedRoleNames->contains('board of directors');
    }

    private function isAdminUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('superuser');
    }

    private function isStaffUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('staff');
    }

    private function isSuperuserUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('superuser');
    }

    private function canAccessOvertime(?User $authenticatedUser, AttendanceOvertime $attendanceOvertime): bool
    {
        if (! $authenticatedUser instanceof User) {
            return false;
        }

        if ($this->isAdminUser($authenticatedUser)) {
            return true;
        }

        $authenticatedUser->loadMissing('employee');
        $authenticatedEmployeeId = $authenticatedUser->employee?->id;
        if (is_string($authenticatedEmployeeId) && $authenticatedEmployeeId === (string) $attendanceOvertime->employee_id) {
            return true;
        }

        if (! $this->isBoardOfDirectur($authenticatedUser)) {
            return false;
        }

        $authenticatedUser->loadMissing('employee.deployment');
        $userCompanyId = $authenticatedUser->employee?->deployment?->current_company_id;
        if (! is_string($userCompanyId) || trim($userCompanyId) === '') {
            return false;
        }

        return $attendanceOvertime->employee()
            ->whereHas('deployment', function ($query) use ($userCompanyId): void {
                $query->where('current_company_id', $userCompanyId);
            })
            ->exists();
    }

    private function canManageOvertimeAction(?User $authenticatedUser, AttendanceOvertime $attendanceOvertime): bool
    {
        if (! $authenticatedUser instanceof User) {
            return false;
        }

        $authenticatedUser->loadMissing('employee');
        $authenticatedEmployeeId = is_string($authenticatedUser->employee?->id) ? trim($authenticatedUser->employee->id) : '';
        if ($this->isStaffUser($authenticatedUser) && $authenticatedEmployeeId !== '' && $authenticatedEmployeeId === (string) $attendanceOvertime->employee_id) {
            return true;
        }

        if ($this->isSuperuserUser($authenticatedUser)) {
            return true;
        }

        if (! $this->isBoardOfDirectur($authenticatedUser)) {
            return false;
        }

        $authenticatedUser->loadMissing('employee.deployment');
        $userCompanyId = $authenticatedUser->employee?->deployment?->current_company_id;
        if (! is_string($userCompanyId) || trim($userCompanyId) === '') {
            return false;
        }

        return $attendanceOvertime->employee()
            ->whereHas('deployment', function ($query) use ($userCompanyId): void {
                $query->where('current_company_id', $userCompanyId);
            })
            ->exists();
    }

    private function hasOvertimeAssignmentForEmployee(?string $employeeId): bool
    {
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return false;
        }

        return AttendanceOvertime::query()
            ->where('employee_id', trim($employeeId))
            ->whereIn('status', [
                self::OVERTIME_STATUS_ASSIGNED,
                self::OVERTIME_STATUS_IN_PROGRESS,
            ])
            ->exists();
    }

    private function resolveStaffEditableOvertimeId(?string $employeeId): ?string
    {
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return null;
        }

        $overtime = AttendanceOvertime::query()
            ->where('employee_id', trim($employeeId))
            ->where(function ($query): void {
                $query->whereNull('actual_start_time')
                    ->orWhereNull('actual_end_time')
                    ->orWhereIn('status', [
                        self::OVERTIME_STATUS_ASSIGNED,
                        self::OVERTIME_STATUS_IN_PROGRESS,
                    ]);
            })
            ->orderByDesc('overtime_date')
            ->orderByDesc('id')
            ->first(['id']);

        if (! $overtime || ! is_string($overtime->id) || trim($overtime->id) === '') {
            return null;
        }

        return trim($overtime->id);
    }

    private function hasAttendanceCheckInToday(?string $employeeId): bool
    {
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return false;
        }

        $todayJakarta = Carbon::now('Asia/Jakarta')->toDateString();

        return Attendance::query()
            ->where('employee_id', trim($employeeId))
            ->whereDate('date', $todayJakarta)
            ->whereNotNull('clock_in')
            ->exists();
    }

    private function syncAttendanceOvertimeLink(string $employeeId, string $attendanceDate, string $overtimeId): void
    {
        $trimmedEmployeeId = trim($employeeId);
        $trimmedOvertimeId = trim($overtimeId);
        if ($trimmedEmployeeId === '' || $trimmedOvertimeId === '' || trim($attendanceDate) === '') {
            return;
        }

        Attendance::query()
            ->where('employee_id', $trimmedEmployeeId)
            ->whereDate('date', $attendanceDate)
            ->update([
                'overtime_id' => $trimmedOvertimeId,
                'is_overtime' => true,
            ]);
    }

    /**
     * @return Collection<int, array{id:string,name:string,code:string}>
     */
    private function buildTaskProjectOptions(AttendanceOvertime $overtime): Collection
    {
        $employeeId = trim((string) $overtime->employee_id);
        if ($employeeId === '') {
            return collect();
        }

        return Project::query()
            ->select(['id', 'name', 'code'])
            ->where('status', 'active')
            ->whereHas('memberships', function ($query) use ($employeeId): void {
                $query
                    ->where('employee_id', $employeeId)
                    ->where('status', 'active');
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Project $project): array => [
                'id' => (string) $project->id,
                'name' => (string) $project->name,
                'code' => (string) $project->code,
            ])
            ->values();
    }

    private function formatDateInputValue(mixed $dateValue): string
    {
        if ($dateValue === null || $dateValue === '') {
            return Carbon::now('Asia/Jakarta')->toDateString();
        }

        try {
            return Carbon::parse($dateValue)->timezone('Asia/Jakarta')->toDateString();
        } catch (\Throwable) {
            return Carbon::now('Asia/Jakarta')->toDateString();
        }
    }

    private function nullableStringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmedValue = trim($value);

        return $trimmedValue !== '' ? $trimmedValue : null;
    }

    private function createInitialOvertimeLifecycleLogs(AttendanceOvertime $overtime, ?User $actor): void
    {
        $assignmentHappenedAt = $this->assignmentSubmittedLifecycleDateTime($overtime);

        foreach (self::OVERTIME_LIFECYCLE_STEPS as $lifecycleStep) {
            $isAssignmentStep = $lifecycleStep['event_key'] === 'assignment_submitted';

            OvertimeLifecycleLog::query()->updateOrCreate(
                [
                    'overtime_id' => $overtime->id,
                    'event_key' => $lifecycleStep['event_key'],
                ],
                [
                    'phase' => $lifecycleStep['phase'],
                    'step_order' => $lifecycleStep['step_order'],
                    'title' => $lifecycleStep['title'],
                    'status' => $isAssignmentStep ? 'complete' : $lifecycleStep['status'],
                    'actor_id' => $isAssignmentStep ? $actor?->id : null,
                    'happened_at' => $isAssignmentStep ? $assignmentHappenedAt : null,
                    'metadata' => [
                        'overtime_status' => $overtime->status,
                        'planned_start_time' => $this->normalizeTimeString($overtime->planned_start_time),
                        'planned_end_time' => $this->normalizeTimeString($overtime->planned_end_time),
                    ],
                ]
            );
        }
    }

    private function syncOvertimeLifecycleLogs(AttendanceOvertime $overtime, ?User $actor): void
    {
        $overtime->loadMissing('employee.user', 'assignedBy');
        $assignmentActor = $overtime->assignedBy instanceof User ? $overtime->assignedBy : $actor;

        $this->updateOvertimeLifecycleLog(
            $overtime,
            'assignment_submitted',
            'complete',
            $assignmentActor,
            $this->assignmentSubmittedLifecycleDateTime($overtime)
        );

        $actualStartTime = $this->normalizeStoreTimeValue($overtime->actual_start_time);
        $actualEndTime = $this->normalizeStoreTimeValue($overtime->actual_end_time);
        $actualStartAt = $this->overtimeDateTimeFromTime($overtime, $actualStartTime);
        $completedTaskSubmittedAt = $actualStartAt instanceof Carbon
            ? $this->completedOvertimeTaskSubmittedAt($overtime, $actualStartAt)
            : null;
        $staffActor = $overtime->employee?->user instanceof User ? $overtime->employee->user : $actor;

        if ($actualStartAt instanceof Carbon) {
            $this->updateOvertimeLifecycleLog(
                $overtime,
                'session_started',
                'clock_in',
                $actor,
                $actualStartAt
            );
            $this->updateOvertimeLifecycleLog(
                $overtime,
                'task_deliverables_submitted',
                $completedTaskSubmittedAt instanceof Carbon ? 'complete' : 'pending',
                $completedTaskSubmittedAt instanceof Carbon ? $staffActor : null,
                $completedTaskSubmittedAt
            );
            $this->updateOvertimeLifecycleLog(
                $overtime,
                'session_ended',
                $completedTaskSubmittedAt instanceof Carbon ? 'pending' : 'waiting',
                null,
                null
            );
        }

        if ($actualEndTime === null) {
            return;
        }

        $this->updateOvertimeLifecycleLog(
            $overtime,
            'session_ended',
            'clock_out',
            $actor,
            $this->overtimeDateTimeFromTime($overtime, $actualEndTime)
        );
        $this->updateOvertimeLifecycleLog(
            $overtime,
            'task_hours_verification',
            'pending',
            null,
            null
        );
    }

    private function updateOvertimeLifecycleLog(AttendanceOvertime $overtime, string $eventKey, string $status, ?User $actor, ?Carbon $happenedAt): void
    {
        $lifecycleStep = $this->overtimeLifecycleStep($eventKey);
        if ($lifecycleStep === null) {
            return;
        }

        OvertimeLifecycleLog::query()->updateOrCreate(
            [
                'overtime_id' => $overtime->id,
                'event_key' => $eventKey,
            ],
            [
                'phase' => $lifecycleStep['phase'],
                'step_order' => $lifecycleStep['step_order'],
                'title' => $lifecycleStep['title'],
                'status' => $status,
                'actor_id' => $actor?->id,
                'happened_at' => $happenedAt,
                'metadata' => [
                    'overtime_status' => $overtime->status,
                    'planned_start_time' => $this->normalizeTimeString($overtime->planned_start_time),
                    'planned_end_time' => $this->normalizeTimeString($overtime->planned_end_time),
                    'actual_start_time' => $this->normalizeTimeString($overtime->actual_start_time),
                    'actual_end_time' => $this->normalizeTimeString($overtime->actual_end_time),
                ],
            ]
        );
    }

    /**
     * @return array{phase:string,event_key:string,step_order:int,title:string,status:string}|null
     */
    private function overtimeLifecycleStep(string $eventKey): ?array
    {
        foreach (self::OVERTIME_LIFECYCLE_STEPS as $lifecycleStep) {
            if ($lifecycleStep['event_key'] === $eventKey) {
                return $lifecycleStep;
            }
        }

        return null;
    }

    private function overtimeDateTimeFromTime(AttendanceOvertime $overtime, ?string $timeValue): ?Carbon
    {
        if (! is_string($timeValue) || trim($timeValue) === '') {
            return null;
        }

        try {
            $overtimeDate = $overtime->overtime_date instanceof Carbon
                ? $overtime->overtime_date->format('Y-m-d')
                : Carbon::parse($overtime->overtime_date)->format('Y-m-d');

            return Carbon::createFromFormat('Y-m-d H:i:s', $overtimeDate.' '.$timeValue, 'Asia/Jakarta');
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildOvertimeLifecycleTracker(AttendanceOvertime $overtime): Collection
    {
        return $overtime->lifecycleLogs
            ->sortBy('step_order')
            ->groupBy('phase')
            ->map(function (Collection $lifecycleLogs, string $phase) use ($overtime): array {
                $items = $lifecycleLogs
                    ->map(fn (OvertimeLifecycleLog $lifecycleLog): array => $this->overtimeLifecycleValueFromLog($lifecycleLog, $overtime))
                    ->values();
                $phaseValue = $this->overtimeLifecyclePhaseValue($items->all());
                $phaseConfig = self::OVERTIME_LIFECYCLE_PHASES[$phase] ?? null;

                return [
                    'phase' => $phase,
                    'title' => $phaseConfig['title'] ?? ucwords(str_replace('_', ' ', $phase)),
                    'sort_order' => $phaseConfig['sort_order'] ?? 999,
                    'date_label' => $phaseValue['date_label'],
                    'marker_class' => $phaseValue['marker_class'],
                    'items' => $items,
                ];
            })
            ->sortBy('sort_order')
            ->values();
    }

    /**
     * @return array{pending: Collection<int, array<string, mixed>>, finished: Collection<int, array<string, mixed>>}
     */
    private function buildOvertimeTaskItems(AttendanceOvertime $overtime): array
    {
        $taskItems = $this->buildOvertimeTaskQuery($overtime)
            ->get()
            ->map(fn (ProjectTask $projectTask): array => $this->overtimeTaskItemValue($projectTask, $overtime))
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

    private function buildOvertimeTaskQuery(AttendanceOvertime $overtime): Builder
    {
        $employeeId = trim((string) $overtime->employee_id);
        if ($employeeId === '') {
            return ProjectTask::query()->whereRaw('1 = 0');
        }

        return ProjectTask::query()
            ->with(['project:id,name', 'assignedBy:id,username'])
            ->where('employee_id', $employeeId)
            ->where(function ($query) use ($overtime): void {
                $query
                    ->where('overtime_id', (string) $overtime->id)
                    ->orWhereNull('overtime_id');
            })
            ->orderBy('due_date')
            ->orderBy('created_at');
    }

    private function buildCurrentOvertimeTaskQuery(AttendanceOvertime $overtime): Builder
    {
        $employeeId = trim((string) $overtime->employee_id);
        $overtimeId = trim((string) $overtime->id);
        if ($employeeId === '' || $overtimeId === '') {
            return ProjectTask::query()->whereRaw('1 = 0');
        }

        return ProjectTask::query()
            ->where('employee_id', $employeeId)
            ->where('overtime_id', $overtimeId);
    }

    private function completedOvertimeTaskSubmittedAt(AttendanceOvertime $overtime, Carbon $actualStartAt): ?Carbon
    {
        $completedAt = $this->buildCurrentOvertimeTaskQuery($overtime)
            ->whereRaw('LOWER(status) = ?', ['completed'])
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $actualStartAt->toDateTimeString())
            ->orderBy('completed_at')
            ->value('completed_at');

        return $completedAt !== null
            ? Carbon::parse($completedAt)->timezone('Asia/Jakarta')
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function overtimeTaskItemValue(ProjectTask $projectTask, AttendanceOvertime $overtime): array
    {
        $status = strtolower(trim((string) ($projectTask->status ?? 'pending')));
        $isFinished = $status === 'completed' || $projectTask->completed_at !== null;
        $dateValue = $isFinished ? $projectTask->completed_at : $projectTask->due_date;
        $projectId = is_string($projectTask->project_id) ? trim($projectTask->project_id) : '';

        return [
            'id' => (string) $projectTask->id,
            'title' => trim((string) ($projectTask->title ?? '')) !== '' ? (string) $projectTask->title : 'Untitled Task',
            'description' => (string) ($projectTask->description ?? ''),
            'start_date' => $this->formatDateInputValue($projectTask->start_date),
            'due_date' => $this->formatDateInputValue($projectTask->due_date),
            'date_label' => $dateValue !== null ? Carbon::parse($dateValue)->timezone('Asia/Jakarta')->format('d M Y') : '-',
            'date_range_label' => $this->taskDateRangeLabel($projectTask->start_date, $projectTask->due_date),
            'status_value' => $status,
            'status' => $this->projectTaskStatusLabel($status),
            'status_label' => $this->projectTaskDetailStatusLabel($status, $isFinished),
            'status_class' => $isFinished ? 'text-success' : 'text-danger',
            'priority' => strtolower(trim((string) ($projectTask->priority ?? 'medium'))) ?: 'medium',
            'attachment_path' => (string) ($projectTask->attachment_path ?? ''),
            'blockers' => (string) ($projectTask->blockers ?? ''),
            'task_category' => $projectId !== '' ? 'project' : 'daily',
            'task_category_label' => $projectId !== '' ? 'Project Task' : 'Daily Task',
            'project_name' => $projectId !== '' ? trim((string) ($projectTask->project?->name ?? '')) : 'Daily Task',
            'assigned_by' => trim((string) ($projectTask->assignedBy?->username ?? 'self')) ?: 'self',
            'project_id' => $projectId,
            'checked' => $isFinished,
            'update_url' => route('attendance.overtimes.tasks.update', [
                'attendanceOvertime' => $overtime,
                'projectTask' => $projectTask,
            ]),
            'delete_url' => route('attendance.overtimes.tasks.destroy', [
                'attendanceOvertime' => $overtime,
                'projectTask' => $projectTask,
            ]),
        ];
    }

    private function projectTaskStatusLabel(string $status): string
    {
        return match (strtolower(trim($status))) {
            'completed' => 'Completed',
            'in_progress' => 'In Progress',
            'cancelled', 'canceled' => 'Cancelled',
            default => 'Pending',
        };
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

    private function overtimeLifecyclePhaseValue(array $items): array
    {
        $itemCollection = collect($items);
        $state = $itemCollection->contains(fn (array $item): bool => $item['state'] === 'rejected')
            ? 'rejected'
            : ($itemCollection->contains(fn (array $item): bool => $item['state'] === 'pending')
                ? 'pending'
                : ($itemCollection->every(fn (array $item): bool => $item['state'] === 'completed') ? 'completed' : 'waiting'));
        $latestDatedItem = $itemCollection
            ->filter(fn (array $item): bool => in_array($item['state'], ['completed', 'pending', 'rejected'], true) && ! in_array($item['date_label'], ['-', 'Now', 'Next'], true))
            ->last();

        return [
            'date_label' => $latestDatedItem['date_label'] ?? ($state === 'pending' ? 'Now' : 'Next'),
            'marker_class' => $this->overtimeLifecycleMarkerClass($state),
        ];
    }

    private function overtimeLifecycleValueFromLog(OvertimeLifecycleLog $lifecycleLog, ?AttendanceOvertime $overtime = null): array
    {
        $status = trim(strtolower((string) ($lifecycleLog->status ?? ''))) ?: 'waiting';
        $state = $this->normalizeOvertimeLifecycleState($status);
        $date = $this->overtimeLifecycleDisplayDateTime($lifecycleLog, $overtime);

        return [
            'event_key' => (string) $lifecycleLog->event_key,
            'step_order' => (int) $lifecycleLog->step_order,
            'title' => (string) $lifecycleLog->title,
            'state' => $state,
            'date_label' => $this->formatOvertimeLifecycleDateLabel($date, $state),
            'datetime_label' => $this->formatOvertimeLifecycleDateTimeLabel($date, $state),
            'actor_label' => $this->resolveUserDisplayName($lifecycleLog->actor),
            'status_label' => $this->overtimeLifecycleStatusLabel($status),
            'marker_class' => $this->overtimeLifecycleMarkerClass($state),
            'badge_class' => $this->overtimeLifecycleBadgeClass($state),
        ];
    }

    private function overtimeLifecycleDisplayDateTime(OvertimeLifecycleLog $lifecycleLog, ?AttendanceOvertime $overtime): mixed
    {
        if ((string) $lifecycleLog->event_key !== 'assignment_submitted' || ! $overtime instanceof AttendanceOvertime) {
            return $lifecycleLog->happened_at;
        }

        return $this->assignmentSubmittedLifecycleDateTime($overtime) ?? $lifecycleLog->happened_at;
    }

    private function assignmentSubmittedLifecycleDateTime(AttendanceOvertime $overtime): ?Carbon
    {
        return $this->overtimeDateTimeFromTime(
            $overtime,
            $this->normalizeStoreTimeValue($overtime->planned_start_time)
        );
    }

    private function normalizeOvertimeLifecycleState(string $state): string
    {
        return match (strtolower(trim($state))) {
            'approved', 'calculated_locked', 'clock_in', 'clock_out', 'complete', 'completed', 'done', 'success', 'verified' => 'completed',
            'cancelled', 'canceled', 'failed', 'rejected' => 'rejected',
            'in_progress', 'pending', 'progress', 'review', 'upcoming' => 'pending',
            default => 'waiting',
        };
    }

    private function formatOvertimeLifecycleDateLabel(mixed $date, string $state): string
    {
        if ($date === null) {
            return $state === 'pending' ? 'Now' : 'Next';
        }

        return Carbon::parse($date)->timezone('Asia/Jakarta')->format('d M');
    }

    private function formatOvertimeLifecycleDateTimeLabel(mixed $date, string $state): string
    {
        if ($date === null) {
            return $state === 'pending' ? 'Pending' : 'Waiting';
        }

        return Carbon::parse($date)->timezone('Asia/Jakarta')->format('d F Y, H:i');
    }

    private function overtimeLifecycleStatusLabel(string $status): string
    {
        return match (strtolower(trim($status))) {
            'calculated_locked' => 'Calculated & Locked',
            'clock_in' => 'Clock In',
            'clock_out' => 'Clock Out',
            'complete' => 'Complete',
            'completed' => 'Completed',
            'verified' => 'Verified',
            'approved' => 'Approved',
            'cancelled', 'canceled' => 'Cancelled',
            'in_progress' => 'In Progress',
            'upcoming' => 'Upcoming',
            'pending' => 'Pending',
            default => 'Waiting',
        };
    }

    private function overtimeLifecycleMarkerClass(string $state): string
    {
        return match ($state) {
            'completed' => 'border-success',
            'pending' => 'border-warning',
            'rejected' => 'border-danger',
            default => 'border-secondary',
        };
    }

    private function overtimeLifecycleBadgeClass(string $state): string
    {
        return match ($state) {
            'completed' => 'badge-success light',
            'pending' => 'badge-warning light',
            'rejected' => 'badge-danger light',
            default => 'badge-secondary light',
        };
    }

    private function formatOvertimeReference(AttendanceOvertime $overtime): string
    {
        $recordNumber = trim((string) ($overtime->record_number ?? ''));
        if ($recordNumber !== '') {
            return '#'.$recordNumber;
        }

        $overtimeId = trim((string) $overtime->id);

        return $overtimeId !== '' ? '#'.$overtimeId : '#OVT';
    }

    private function applyOvertimeAccessFilter(Builder $overtimeQuery, ?User $authenticatedUser): Builder
    {
        if (! $authenticatedUser instanceof User) {
            return $overtimeQuery->whereRaw('1 = 0');
        }

        if ($this->isAdminUser($authenticatedUser)) {
            return $overtimeQuery;
        }

        $authenticatedUser->loadMissing('employee.deployment');
        $authenticatedEmployeeId = $authenticatedUser->employee?->id;
        if ($this->isStaffUser($authenticatedUser) && is_string($authenticatedEmployeeId) && trim($authenticatedEmployeeId) !== '') {
            return $overtimeQuery->where('employee_id', trim($authenticatedEmployeeId));
        }

        if (! $this->isBoardOfDirectur($authenticatedUser)) {
            return $overtimeQuery->whereRaw('1 = 0');
        }

        $userCompanyId = $authenticatedUser->employee?->deployment?->current_company_id;
        if (! is_string($userCompanyId) || trim($userCompanyId) === '') {
            return $overtimeQuery->whereRaw('1 = 0');
        }

        return $overtimeQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
            $query->where('current_company_id', $userCompanyId);
        });
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    private function buildStaffOptions(?User $authenticatedUser): Collection
    {
        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isAdminUser = $this->isAdminUser($authenticatedUser);
        $userCompanyId = $authenticatedUser?->employee?->deployment?->current_company_id;

        $staffUsersQuery = User::query()
            ->role('staff')
            ->select(['id', 'username'])
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.deployment:id,employee_id,current_company_id',
            ])
            ->whereHas('employee');

        if ($isBoardOfDirectur && $userCompanyId) {
            $staffUsersQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
                $query->where('current_company_id', $userCompanyId);
            });
        } elseif (! $isAdminUser && $this->isStaffUser($authenticatedUser)) {
            $staffUsersQuery->whereKey($authenticatedUser?->id);
        }

        return $staffUsersQuery
            ->get()
            ->map(function (User $staffUser): ?array {
                $employeeId = trim((string) ($staffUser->employee?->id ?? ''));
                if ($employeeId === '') {
                    return null;
                }

                return [
                    'id' => $employeeId,
                    'name' => $this->resolveEmployeeDisplayName($staffUser->employee),
                ];
            })
            ->filter(static fn (mixed $option): bool => is_array($option))
            ->values();
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    private function buildPicOptions(?User $authenticatedUser): Collection
    {
        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $userCompanyId = $authenticatedUser?->employee?->deployment?->current_company_id;

        $picUsersQuery = User::query()
            ->select(['id', 'username'])
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.deployment:id,employee_id,current_company_id',
            ])
            ->whereHas('employee');

        if ($isBoardOfDirectur && $userCompanyId) {
            $picUsersQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
                $query->where('current_company_id', $userCompanyId);
            });
        }

        return $picUsersQuery
            ->get()
            ->map(function (User $picUser): ?array {
                $picUserId = trim((string) ($picUser->id ?? ''));
                if ($picUserId === '') {
                    return null;
                }

                return [
                    'id' => $picUserId,
                    'name' => $this->resolveEmployeeDisplayName($picUser->employee),
                ];
            })
            ->filter(static fn (mixed $option): bool => is_array($option))
            ->values();
    }

    private function resolveEmployeeDisplayName(?Employee $employee): string
    {
        if (! $employee) {
            return '-';
        }

        $profileName = is_string($employee->profile?->name) ? trim($employee->profile->name) : '';
        if ($profileName !== '') {
            return $profileName;
        }

        $username = is_string($employee->user?->username) ? trim($employee->user->username) : '';
        if ($username !== '') {
            return $username;
        }

        return '-';
    }

    private function resolveUserDisplayName(?User $user): string
    {
        if (! $user) {
            return '-';
        }

        $user->loadMissing('employee.profile');

        $profileName = is_string($user->employee?->profile?->name) ? trim($user->employee->profile->name) : '';
        if ($profileName !== '') {
            return $profileName;
        }

        $username = is_string($user->username) ? trim($user->username) : '';
        if ($username !== '') {
            return $username;
        }

        return '-';
    }
}
