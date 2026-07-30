<?php

namespace App\Http\Controllers\PicAttendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\OvertimeLifecycleLog;
use App\Models\ProjectTask;
use App\Models\User;
use App\Support\Attendance\OvertimeReviewTableBuilder;
use App\Support\Attendance\OvertimeSummaryMetricBuilder;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PicAttendanceOvertimeController extends Controller
{
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

    public function index(Request $request, OvertimeSummaryMetricBuilder $metricBuilder, OvertimeReviewTableBuilder $tableBuilder): View
    {
        $authenticatedUser = $request->user();
        $companyId = $authenticatedUser instanceof User ? $this->currentCompanyIdFor($authenticatedUser) : null;
        $assignedByUserId = $authenticatedUser instanceof User ? (string) $authenticatedUser->id : null;
        $legacyMonth = $request->query('month');
        $legacyYear = $request->query('year');
        $cardMonth = $this->normalizeMonth($request->query('card_month', $legacyMonth));
        $cardYear = $this->normalizeYear($request->query('card_year', $legacyYear));
        $pendingTableData = $tableBuilder->buildForContext(
            'pic',
            $companyId,
            $assignedByUserId,
            $request->query('pending_month', $legacyMonth),
            $request->query('pending_year', $legacyYear)
        );
        $approvedTableData = $tableBuilder->buildForContext(
            'pic',
            $companyId,
            $assignedByUserId,
            $request->query('approved_month', $legacyMonth),
            $request->query('approved_year', $legacyYear)
        );
        $overtimeSummary = is_string($assignedByUserId) && trim($assignedByUserId) !== ''
            ? $metricBuilder->summarizeForPeriod(
                $companyId,
                $assignedByUserId,
                $cardMonth,
                $cardYear
            )
            : $metricBuilder->summarizeForActiveEmployees($companyId, $assignedByUserId);

        return view('pic_attendance.overtime.index', [
            'overtimeSummary' => $overtimeSummary,
            'overtimeMetricCards' => $metricBuilder->metricCards($overtimeSummary),
            'assignableStaffOptions' => $authenticatedUser instanceof User
                ? $this->assignableStaffOptionsFor($authenticatedUser, $companyId)
                : collect(),
            'picOvertimeStaffGroups' => $authenticatedUser instanceof User
                ? $this->picOvertimeStaffGroupsFor(
                    $authenticatedUser,
                    $companyId,
                    $assignedByUserId,
                    $cardMonth,
                    $cardYear
                )
                : collect(),
            'overtimeCards' => $this->picOvertimeCardsFor(
                $companyId,
                $assignedByUserId,
                $cardMonth,
                $cardYear
            ),
            'monthOptions' => $pendingTableData['monthOptions'],
            'yearOptions' => $this->yearOptionsForFilters(
                $cardYear,
                (int) $pendingTableData['selectedYear'],
                (int) $approvedTableData['selectedYear'],
                $pendingTableData['yearOptions'],
                $approvedTableData['yearOptions']
            ),
            'selectedMonth' => $pendingTableData['selectedMonth'],
            'selectedYear' => $pendingTableData['selectedYear'],
            'selectedCardMonth' => $cardMonth,
            'selectedCardYear' => $cardYear,
            'selectedPendingMonth' => $pendingTableData['selectedMonth'],
            'selectedPendingYear' => $pendingTableData['selectedYear'],
            'selectedApprovedMonth' => $approvedTableData['selectedMonth'],
            'selectedApprovedYear' => $approvedTableData['selectedYear'],
            'pendingRows' => $pendingTableData['pendingRows'],
            'approvedRows' => $approvedTableData['approvedRows'],
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('picOvertimeStore', [
            'overtime_date' => ['required', 'date_format:Y-m-d'],
            'employee_id' => ['required', 'string'],
            'instruction' => ['required', 'string', 'max:5000'],
        ]);

        $authenticatedUser = $request->user();
        abort_unless($authenticatedUser instanceof User, 403);

        $overtimeDate = Carbon::createFromFormat('Y-m-d', $validated['overtime_date'], 'Asia/Jakarta')->startOfDay();
        $selectedEmployeeId = trim((string) $validated['employee_id']);
        $assignableStaffIds = $this->activeSupervisedEmployeeIdsFor(
            $authenticatedUser,
            $this->currentCompanyIdFor($authenticatedUser),
            $overtimeDate
        );

        if (! $assignableStaffIds->contains($selectedEmployeeId)) {
            $this->throwPicOvertimeValidationException('employee_id', 'Staff yang dipilih bukan bawahan supervisor ini.');
        }

        DB::transaction(function () use ($authenticatedUser, $validated, $selectedEmployeeId, $overtimeDate): void {
            $createdAt = Carbon::now('Asia/Jakarta');

            $overtime = AttendanceOvertime::query()->create([
                'employee_id' => $selectedEmployeeId,
                'assigned_by' => $authenticatedUser->id,
                'overtime_date' => $overtimeDate->toDateString(),
                'instruction' => $validated['instruction'],
                'actual_start_time' => null,
                'actual_end_time' => null,
                'approved_start_time' => null,
                'approved_end_time' => null,
                'calculated_hours' => null,
                'status' => 'assigned',
            ]);

            $this->createInitialOvertimeLifecycleLogs($overtime, $authenticatedUser, $createdAt);

            ProjectTask::query()->create($this->initialOvertimeProjectTaskPayload(
                $overtime,
                (string) $validated['instruction'],
                $overtimeDate,
                $authenticatedUser
            ));
        });

        return redirect()
            ->route('pic-attendance.overtime')
            ->with('success', 'Overtime berhasil ditambahkan.');
    }

    public function detail(string $uid): View
    {
        $authenticatedUser = request()->user();
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
            ->where('assigned_by', $authenticatedUser->id)
            ->firstOrFail();

        return view('pic_attendance.overtime.detail', [
            'overtime' => $overtime,
            'overtimeDetail' => $this->picOvertimeDetailSummary($overtime),
            'overtimeTaskItems' => $this->buildOvertimeTaskItems($overtime),
        ]);
    }

    public function updateTask(Request $request, AttendanceOvertime $attendanceOvertime, ProjectTask $projectTask): JsonResponse
    {
        $authenticatedUser = $request->user();
        if (! $this->canUpdatePicOvertimeTask($authenticatedUser, $attendanceOvertime, $projectTask)) {
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
            'status' => ['nullable', 'in:pending,in_progress,completed,cancelled'],
        ]);

        $updatePayload = [
            'overtime_id' => (string) $attendanceOvertime->id,
            'employee_id' => (string) $attendanceOvertime->employee_id,
        ];

        foreach (['title', 'description', 'priority'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updatePayload[$field] = $validated[$field];
            }
        }

        foreach (['blockers', 'attachment_path'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updatePayload[$field] = $this->nullableStringValue($validated[$field] ?? null);
            }
        }

        if (array_key_exists('status', $validated)) {
            $status = strtolower(trim((string) $validated['status']));
            $updatePayload['status'] = $status;
            $updatePayload['completed_at'] = $status === 'completed'
                ? Carbon::now('Asia/Jakarta')
                : null;
        }

        foreach (['start_date', 'due_date'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updatePayload[$field] = $validated[$field];
            }
        }

        $projectTask->update($updatePayload);

        return response()->json([
            'success' => true,
            'message' => 'Task lembur berhasil diperbarui.',
        ]);
    }

    public function verifySession(Request $request, string $uid): RedirectResponse
    {
        $validated = $request->validateWithBag('picOvertimeVerify', [
            'approved_start_time' => ['required', 'date_format:H:i'],
            'approved_end_time' => ['required', 'date_format:H:i'],
        ]);

        $authenticatedUser = $request->user();
        abort_unless($authenticatedUser instanceof User, 403);

        $approvedStartTime = $this->normalizeSubmittedTime($validated['approved_start_time']);
        $approvedEndTime = $this->normalizeSubmittedTime($validated['approved_end_time']);

        $overtime = AttendanceOvertime::query()
            ->select(['id', 'assigned_by', 'actual_start_time', 'actual_end_time', 'approved_start_time', 'approved_end_time', 'calculated_hours'])
            ->with('lifecycleLogs:id,overtime_id,event_key,phase,step_order,title,status,actor_id,happened_at,metadata')
            ->whereKey($uid)
            ->where('assigned_by', $authenticatedUser->id)
            ->firstOrFail();

        if (! $this->isTaskDeliverablesSubmitted($overtime)) {
            $this->throwPicOvertimeValidationException('approved_start_time', 'Task & Deliverables Submitted harus complete sebelum session diverifikasi.', 'picOvertimeVerify');
        }

        if (! $this->canUpdateOvertimeSessionReview($overtime)) {
            $this->throwPicOvertimeValidationException('approved_start_time', 'Review overtime session tidak bisa diubah karena HR / Payroll Processing sudah dikunci.', 'picOvertimeVerify');
        }

        DB::transaction(function () use ($authenticatedUser, $approvedStartTime, $approvedEndTime, $overtime): void {
            $approvedAt = Carbon::now('Asia/Jakarta');

            $overtime->forceFill([
                'approved_start_time' => $approvedStartTime,
                'approved_end_time' => $approvedEndTime,
                'calculated_hours' => round($this->durationMinutes($approvedStartTime, $approvedEndTime) / 60, 2),
            ])->save();

            $this->updateOvertimeLifecycleLog(
                $overtime,
                'task_hours_verification',
                'verified',
                $authenticatedUser,
                $approvedAt
            );

            $this->updateOvertimeLifecycleLog(
                $overtime,
                'payroll_processing',
                'pending',
                null,
                null,
                true
            );
        });

        return redirect()
            ->route('pic-attendance.overtime.detail', ['uid' => $overtime->id])
            ->with('success', 'Overtime session berhasil diverifikasi.');
    }

    /**
     * @return array{
     *     staff_name:string,
     *     supervisor_name:string,
     *     log_status:string,
     *     record_number:string,
     *     overtime_date:string,
     *     planned_start_time:string,
     *     planned_end_time:string,
     *     actual_start_time:string,
     *     actual_end_time:string,
     *     staff_submitted_time_range:string,
     *     staff_submitted_duration:string,
     *     approved_start_time:string,
     *     approved_end_time:string,
     *     planned_time_range:string,
     *     actual_time_range:string,
     *     approved_time_range:string,
     *     time_changed:bool,
     *     planned_duration:string,
     *     actual_duration:string,
     *     approved_duration:string,
     *     duration_changed:bool,
     *     verification_ready:bool,
     *     verification_start_time:string,
     *     verification_end_time:string,
     *     verification_duration:string,
     *     is_task_hours_verified:bool,
     *     can_update_overtime_session_review:bool,
     *     instruction:string,
     *     payout_period:string,
     *     verified_note:string,
     *     verified_datetime:string,
     *     supervisor_approver:string,
     *     supervisor_datetime:string,
     *     director_approver:string,
     *     director_datetime:string
     * }
     */
    private function picOvertimeDetailSummary(AttendanceOvertime $overtime): array
    {
        $actualStartTime = $this->formatTime($overtime->actual_start_time);
        $actualEndTime = $this->formatTime($overtime->actual_end_time);
        $approvedStartTime = $this->formatTime($overtime->approved_start_time);
        $approvedEndTime = $this->formatTime($overtime->approved_end_time);
        $hasActualTime = $this->hasActualOvertimeTimes($overtime);
        $hasApprovedTime = $this->hasApprovedOvertimeTimes($overtime);
        $actualTimeRange = $hasActualTime ? $actualStartTime.' - '.$actualEndTime : '-';
        $approvedTimeRange = $hasApprovedTime ? $approvedStartTime.' - '.$approvedEndTime : '-';
        $actualDuration = $hasActualTime ? $this->durationLabel($overtime->actual_start_time, $overtime->actual_end_time) : '-';
        $approvedDuration = $hasApprovedTime ? $this->durationLabel($overtime->approved_start_time, $overtime->approved_end_time) : '-';
        $taskDeliverablesSubmitted = $this->isTaskDeliverablesSubmitted($overtime);
        $taskHoursVerified = $this->isTaskHoursVerified($overtime);
        $canUpdateOvertimeSessionReview = $taskDeliverablesSubmitted && $this->canUpdateOvertimeSessionReview($overtime);
        $verificationLog = $this->overtimeLifecycleLog($overtime, 'task_hours_verification');
        $actualEndDateTime = $this->formatActualEndDateTime($overtime);
        $directorApprovalLog = $this->overtimeLifecycleLog($overtime, 'director_approval');
        $directorApprover = $directorApprovalLog?->actor instanceof User
            ? $directorApprovalLog->actor
            : $this->resolveOvertimeDirectorApprover($overtime);

        return [
            'staff_name' => $this->employeeDisplayName($overtime->employee),
            'supervisor_name' => $this->supervisorDisplayName($overtime),
            'log_status' => $this->overtimeStatusLabel((string) ($overtime->status ?? 'assigned')),
            'record_number' => '#'.(is_string($overtime->record_number) && trim($overtime->record_number) !== '' ? trim($overtime->record_number) : '-'),
            'overtime_date' => Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->format('d M Y'),
            'planned_start_time' => '-',
            'planned_end_time' => '-',
            'actual_start_time' => $actualStartTime,
            'actual_end_time' => $actualEndTime,
            'staff_submitted_time_range' => $actualTimeRange,
            'staff_submitted_duration' => $actualDuration,
            'approved_start_time' => $approvedStartTime,
            'approved_end_time' => $approvedEndTime,
            'planned_time_range' => '-',
            'actual_time_range' => $actualTimeRange,
            'approved_time_range' => $approvedTimeRange,
            'time_changed' => $hasApprovedTime && $actualTimeRange !== '-' && $approvedTimeRange !== $actualTimeRange,
            'planned_duration' => '-',
            'actual_duration' => $actualDuration,
            'approved_duration' => $approvedDuration,
            'duration_changed' => $hasApprovedTime && $actualDuration !== '-' && $approvedDuration !== $actualDuration,
            'verification_ready' => $taskDeliverablesSubmitted,
            'verification_start_time' => $taskDeliverablesSubmitted ? ($approvedStartTime !== '-' ? $approvedStartTime : $actualStartTime) : '-',
            'verification_end_time' => $taskDeliverablesSubmitted ? ($approvedEndTime !== '-' ? $approvedEndTime : $actualEndTime) : '-',
            'verification_duration' => $taskDeliverablesSubmitted ? ($approvedDuration !== '-' ? $approvedDuration : $actualDuration) : '-',
            'is_task_hours_verified' => $taskHoursVerified,
            'can_update_overtime_session_review' => $canUpdateOvertimeSessionReview,
            'instruction' => is_string($overtime->instruction) && trim($overtime->instruction) !== '' ? trim($overtime->instruction) : '-',
            'payout_period' => 'Included in '.Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->format('F Y').' Payroll',
            'verified_note' => $hasActualTime ? 'Actual overtime hours recorded' : 'Waiting for clock-out',
            'verified_datetime' => $actualEndDateTime !== '-' ? $actualEndDateTime : $this->formatLifecycleDateTime($verificationLog),
            'supervisor_approver' => $this->supervisorDisplayName($overtime),
            'supervisor_datetime' => $this->formatLifecycleDateTime($verificationLog),
            'director_approver' => $directorApprover instanceof User ? $this->userDisplayName($directorApprover) : '-',
            'director_datetime' => $this->formatLifecycleDateTime($directorApprovalLog),
        ];
    }

    private function canUpdatePicOvertimeTask(?User $authenticatedUser, AttendanceOvertime $attendanceOvertime, ProjectTask $projectTask): bool
    {
        if (! $authenticatedUser instanceof User) {
            return false;
        }

        return (string) $attendanceOvertime->assigned_by === (string) $authenticatedUser->id
            && (string) $projectTask->overtime_id === (string) $attendanceOvertime->id
            && (string) $projectTask->employee_id === (string) $attendanceOvertime->employee_id;
    }

    /**
     * @return array{
     *     project_id:null,
     *     employee_id:string,
     *     assigned_by:string,
     *     overtime_id:string,
     *     title:string,
     *     description:string,
     *     status:string,
     *     priority:string,
     *     start_date:string,
     *     due_date:string,
     *     completed_at:null
     * }
     */
    private function initialOvertimeProjectTaskPayload(AttendanceOvertime $overtime, string $instruction, Carbon $overtimeDate, User $assignedBy): array
    {
        $instructionText = trim($instruction);

        return [
            'project_id' => null,
            'employee_id' => (string) $overtime->employee_id,
            'assigned_by' => (string) $assignedBy->id,
            'overtime_id' => (string) $overtime->id,
            'title' => Str::limit($instructionText, 255, ''),
            'description' => $instructionText,
            'status' => 'pending',
            'priority' => 'high',
            'start_date' => $overtimeDate->toDateString(),
            'due_date' => $overtimeDate->toDateString(),
            'completed_at' => null,
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

    /**
     * @return array<string, mixed>
     */
    private function overtimeTaskItemValue(ProjectTask $projectTask, AttendanceOvertime $overtime): array
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
            'update_url' => route('pic-attendance.overtime.tasks.update', [
                'attendanceOvertime' => $overtime->id,
                'projectTask' => $projectTask->id,
            ]),
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

    private function canUpdateOvertimeSessionReview(AttendanceOvertime $overtime): bool
    {
        $status = strtolower(trim((string) ($this->overtimeLifecycleLog($overtime, 'payroll_processing')?->status ?? 'waiting')));

        return in_array($status, ['waiting', 'pending'], true);
    }

    private function updateOvertimeLifecycleLog(
        AttendanceOvertime $overtime,
        string $eventKey,
        string $status,
        ?User $actor,
        ?Carbon $happenedAt,
        bool $onlyWhenWaiting = false
    ): void {
        $lifecycleStep = collect(self::OVERTIME_LIFECYCLE_STEPS)
            ->first(fn (array $step): bool => (string) $step['event_key'] === $eventKey);

        if (! is_array($lifecycleStep)) {
            return;
        }

        $lifecycleLog = OvertimeLifecycleLog::query()
            ->where('overtime_id', $overtime->id)
            ->where('event_key', $eventKey)
            ->first();

        if (! $lifecycleLog instanceof OvertimeLifecycleLog) {
            $lifecycleLog = new OvertimeLifecycleLog([
                'overtime_id' => $overtime->id,
                'phase' => $lifecycleStep['phase'],
                'event_key' => $lifecycleStep['event_key'],
                'step_order' => $lifecycleStep['step_order'],
                'title' => $lifecycleStep['title'],
                'metadata' => [],
            ]);
        }

        if ($onlyWhenWaiting && strtolower(trim((string) $lifecycleLog->status)) !== 'waiting') {
            return;
        }

        $lifecycleLog->forceFill([
            'status' => $status,
            'actor_id' => $actor?->id,
            'happened_at' => $happenedAt,
        ])->save();
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
     * @return Collection<int, array{
     *     uid:string,
     *     record_number:string,
     *     employee_name:string,
     *     instruction:string,
     *     date_label:string,
     *     is_overnight:bool,
     *     time_lines:array<int, array{label:string,strike:bool}>,
     *     supervisor_name:string,
     *     detail_url:string,
     *     current_log:array{title:string,status:string,badge_class:string}
     * }>
     */
    private function picOvertimeCardsFor(?string $companyId, ?string $assignedByUserId, int $month, int $year): Collection
    {
        if (! is_string($assignedByUserId) || trim($assignedByUserId) === '') {
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
                'actual_start_time',
                'actual_end_time',
                'approved_start_time',
                'approved_end_time',
                'instruction',
                'status',
            ])
            ->where('assigned_by', trim($assignedByUserId))
            ->whereBetween('overtime_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereHas('employee', function (Builder $query): void {
                $query
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereHas('deployment', function (Builder $query): void {
                        $query->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active']);
                    });
            })
            ->with([
                'employee:id,user_id',
                'employee.user:id,username,email',
                'assignedBy:id,username,email',
                'assignedBy.employee:id,user_id',
                'assignedBy.employee.profile:id,employee_id,name',
                'lifecycleLogs:id,overtime_id,event_key,title,status,step_order',
            ])
            ->orderByDesc('overtime_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (AttendanceOvertime $overtime): array => $this->picOvertimeCardFor($overtime))
            ->values();
    }

    /**
     * @return array{
     *     uid:string,
     *     record_number:string,
     *     employee_name:string,
     *     instruction:string,
     *     date_label:string,
     *     is_overnight:bool,
     *     time_lines:array<int, array{label:string,strike:bool}>,
     *     supervisor_name:string,
     *     detail_url:string,
     *     current_log:array{title:string,status:string,badge_class:string}
     * }
     */
    private function picOvertimeCardFor(AttendanceOvertime $overtime): array
    {
        $lifecycleLogsByEvent = $overtime->lifecycleLogs->keyBy('event_key');
        $verificationStatus = strtolower(trim((string) ($lifecycleLogsByEvent->get('task_hours_verification')?->status ?? '')));
        $isVerified = $verificationStatus === 'verified';
        $isOvernight = false;

        return [
            'uid' => (string) $overtime->id,
            'record_number' => '#'.(is_string($overtime->record_number) && trim($overtime->record_number) !== '' ? trim($overtime->record_number) : '-'),
            'employee_name' => $this->employeeUsernameLabel($overtime->employee),
            'instruction' => is_string($overtime->instruction) && trim($overtime->instruction) !== '' ? trim($overtime->instruction) : '-',
            'date_label' => $this->overtimeDateLabel($overtime->overtime_date, $isOvernight),
            'is_overnight' => $isOvernight,
            'time_lines' => $this->timeLinesFor($overtime, $isVerified),
            'supervisor_name' => $this->supervisorDisplayName($overtime),
            'detail_url' => route('pic-attendance.overtime.detail', ['uid' => $overtime->id]),
            'current_log' => $this->currentPicLifecycleLog($lifecycleLogsByEvent),
        ];
    }

    /**
     * @param  Collection<string, OvertimeLifecycleLog>  $lifecycleLogsByEvent
     * @return array{title:string,status:string,badge_class:string}
     */
    private function currentPicLifecycleLog(Collection $lifecycleLogsByEvent): array
    {
        $picLifecycleLogs = collect(self::OVERTIME_LIFECYCLE_STEPS)
            ->take(5)
            ->map(function (array $lifecycleStep) use ($lifecycleLogsByEvent): array {
                $status = (string) ($lifecycleLogsByEvent->get($lifecycleStep['event_key'])?->status ?? $lifecycleStep['status']);
                $statusLabel = $this->lifecycleStatusLabel($status);

                return [
                    'title' => $lifecycleStep['title'],
                    'status' => $statusLabel,
                    'badge_class' => $this->lifecycleStatusBadgeClass($statusLabel),
                ];
            })
            ->values();

        $currentLog = $picLifecycleLogs->first(
            fn (array $lifecycleLog): bool => ! $this->isCompletedPicLifecycleStatus((string) $lifecycleLog['status'])
        );

        return $currentLog ?? $picLifecycleLogs->last() ?? [
            'title' => 'Overtime Assignment Submitted',
            'status' => 'pending',
            'badge_class' => 'badge-warning',
        ];
    }

    private function isCompletedPicLifecycleStatus(string $status): bool
    {
        return in_array(strtolower(trim($status)), ['complete', 'clock_in', 'clock_out', 'verified'], true);
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

    private function overtimeDateLabel(mixed $overtimeDateValue, bool $isOvernight): string
    {
        $startDate = Carbon::parse($overtimeDateValue, 'Asia/Jakarta');

        if (! $isOvernight) {
            return $startDate->format('d M Y');
        }

        return $startDate->format('d M Y').' → '.$startDate->copy()->addDay()->format('d M Y');
    }

    private function isOvernightTimeRange(mixed $startTimeValue, mixed $endTimeValue): bool
    {
        if (! is_string($startTimeValue) || ! is_string($endTimeValue)) {
            return false;
        }

        return trim($endTimeValue) <= trim($startTimeValue);
    }

    /**
     * @return array<int, array{label:string,strike:bool}>
     */
    private function timeLinesFor(AttendanceOvertime $overtime, bool $isVerified): array
    {
        $plannedTimeLabel = '-';

        if (! $isVerified || (! $this->hasApprovedOvertimeTimes($overtime) && ! $this->hasActualOvertimeTimes($overtime))) {
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

        return [
            ['label' => $this->timeRangeLabel($verifiedStartTime, $verifiedEndTime), 'strike' => false],
        ];
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

    private function timeRangeLabel(mixed $startTimeValue, mixed $endTimeValue): string
    {
        $startTime = $this->formatTime($startTimeValue);
        $endTime = $this->formatTime($endTimeValue);
        $durationLabel = $this->durationLabel($startTimeValue, $endTimeValue);

        return "{$startTime} - {$endTime} ({$durationLabel})";
    }

    private function formatTime(mixed $time): string
    {
        if (! is_string($time) || trim($time) === '') {
            return '-';
        }

        return Carbon::parse($time, 'Asia/Jakarta')->format('H:i');
    }

    private function formatDateInputValue(mixed $dateValue): string
    {
        if ($dateValue === null || trim((string) $dateValue) === '') {
            return '';
        }

        return Carbon::parse($dateValue, 'Asia/Jakarta')->format('Y-m-d');
    }

    private function nullableStringValue(mixed $value): ?string
    {
        $stringValue = is_string($value) ? trim($value) : '';

        return $stringValue !== '' ? $stringValue : null;
    }

    private function durationLabel(mixed $startTimeValue, mixed $endTimeValue): string
    {
        $minutes = $this->durationMinutes($startTimeValue, $endTimeValue);
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }

        return "{$hours}h {$remainingMinutes}m";
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

    private function supervisorDisplayName(AttendanceOvertime $overtime): string
    {
        $profileName = is_string($overtime->assignedBy?->employee?->profile?->name)
            ? trim($overtime->assignedBy->employee->profile->name)
            : '';

        if ($profileName !== '') {
            return $profileName;
        }

        $username = is_string($overtime->assignedBy?->username) ? trim($overtime->assignedBy->username) : '';
        if ($username !== '') {
            return $username;
        }

        $email = is_string($overtime->assignedBy?->email) ? trim($overtime->assignedBy->email) : '';

        return $email !== '' ? $email : '-';
    }

    private function lifecycleStatusLabel(string $status): string
    {
        $normalizedStatus = strtolower(trim($status));

        return $normalizedStatus === 'waiting' ? 'pending' : ($normalizedStatus !== '' ? $normalizedStatus : 'pending');
    }

    private function lifecycleStatusBadgeClass(string $status): string
    {
        return match (strtolower(trim($status))) {
            'complete', 'verified', 'approved' => 'badge-success',
            'clock_in', 'clock_out' => 'badge-info',
            'rejected', 'cancelled' => 'badge-danger',
            default => 'badge-warning',
        };
    }

    /**
     * @return Collection<int, array{
     *     group_label:string,
     *     staff:Collection<int, array{
     *         initial:string,
     *         name:string,
     *         position:string,
     *         this_week_label:string,
     *         this_month_label:string,
     *         this_year_label:string,
     *         paid_out_label:string,
     *         paid_amount_label:string,
     *         compensatory_time_label:string
     *     }>
     * }>
     */
    private function picOvertimeStaffGroupsFor(User $user, ?string $companyId, ?string $assignedByUserId, int $selectedMonth, int $selectedYear): Collection
    {
        if (! is_string($companyId) || trim($companyId) === '' || ! is_string($assignedByUserId) || trim($assignedByUserId) === '') {
            return collect();
        }

        $referenceDate = Carbon::now('Asia/Jakarta')->startOfDay();
        $employeeIds = $this->activeSupervisedEmployeeIdsFor($user, $companyId, $referenceDate);

        if ($employeeIds->isEmpty()) {
            return collect();
        }

        $employees = Employee::query()
            ->select(['id', 'user_id'])
            ->with([
                'profile:id,employee_id,name',
                'user:id,username,email',
                'deployment:id,employee_id,current_position_id,current_department_id,current_company_id',
                'deployment.department:id,name',
                'deployment.position:id,name',
            ])
            ->whereIn('id', $employeeIds)
            ->get()
            ->sortBy(fn (Employee $employee): string => $this->employeeDisplayName($employee))
            ->values();

        if ($employees->isEmpty()) {
            return collect();
        }

        $yearStart = Carbon::create($selectedYear, 1, 1, 0, 0, 0, 'Asia/Jakarta')->startOfYear();
        $yearEnd = $yearStart->copy()->endOfYear();
        $monthStart = Carbon::create($selectedYear, $selectedMonth, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $weekStart = $referenceDate->copy()->startOfWeek();
        $weekEnd = $referenceDate->copy()->endOfWeek();

        $overtimesByEmployee = AttendanceOvertime::query()
            ->select([
                'id',
                'employee_id',
                'assigned_by',
                'overtime_date',
                'actual_start_time',
                'actual_end_time',
                'approved_start_time',
                'approved_end_time',
                'status',
            ])
            ->with(['lifecycleLogs:id,overtime_id,event_key,status'])
            ->where('assigned_by', trim($assignedByUserId))
            ->whereIn('employee_id', $employeeIds)
            ->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled'])
            ->whereBetween('overtime_date', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->get()
            ->groupBy(fn (AttendanceOvertime $overtime): string => (string) $overtime->employee_id);

        return $employees
            ->groupBy(fn (Employee $employee): string => $this->staffGridGroupLabel($employee))
            ->map(function (Collection $groupedEmployees, string $groupLabel) use ($overtimesByEmployee, $weekStart, $weekEnd, $monthStart, $monthEnd, $yearStart, $yearEnd): array {
                return [
                    'group_label' => $groupLabel,
                    'staff' => $groupedEmployees
                        ->map(function (Employee $employee) use ($overtimesByEmployee, $weekStart, $weekEnd, $monthStart, $monthEnd, $yearStart, $yearEnd): array {
                            $employeeOvertimes = $overtimesByEmployee->get((string) $employee->id, collect());
                            $paidOutMinutes = $this->paidOutOvertimeMinutes($employeeOvertimes);

                            return [
                                'initial' => $this->employeeInitial($employee),
                                'name' => $this->employeeDisplayName($employee),
                                'position' => $this->staffGridPositionLabel($employee),
                                'this_week_label' => $this->durationMinutesLabel($this->overtimeMinutesBetween($employeeOvertimes, $weekStart, $weekEnd)),
                                'this_month_label' => $this->durationMinutesLabel($this->overtimeMinutesBetween($employeeOvertimes, $monthStart, $monthEnd)),
                                'this_year_label' => $this->durationMinutesLabel($this->overtimeMinutesBetween($employeeOvertimes, $yearStart, $yearEnd)),
                                'paid_out_label' => $this->durationMinutesLabel($paidOutMinutes),
                                'paid_amount_label' => $paidOutMinutes > 0 ? 'Rp. 300.000' : 'Rp. 0',
                                'compensatory_time_label' => '0 hours',
                            ];
                        })
                        ->values(),
                ];
            })
            ->sortKeys()
            ->values();
    }

    private function staffGridGroupLabel(Employee $employee): string
    {
        $departmentName = is_string($employee->deployment?->department?->name) ? trim($employee->deployment->department->name) : '';
        if ($departmentName !== '') {
            return $departmentName;
        }

        $positionName = is_string($employee->deployment?->position?->name) ? trim($employee->deployment->position->name) : '';

        return $positionName !== '' ? $positionName : 'Staff Under PIC';
    }

    private function staffGridPositionLabel(Employee $employee): string
    {
        $positionName = is_string($employee->deployment?->position?->name) ? trim($employee->deployment->position->name) : '';

        return $positionName !== '' ? $positionName : '-';
    }

    private function employeeInitial(Employee $employee): string
    {
        $name = $this->employeeDisplayName($employee);
        $initial = Str::substr($name, 0, 1);

        return $initial !== '' ? Str::upper($initial) : '-';
    }

    /**
     * @param  Collection<int, AttendanceOvertime>  $overtimes
     */
    private function overtimeMinutesBetween(Collection $overtimes, Carbon $periodStart, Carbon $periodEnd): int
    {
        return (int) $overtimes
            ->filter(function (AttendanceOvertime $overtime) use ($periodStart, $periodEnd): bool {
                if ($overtime->overtime_date === null || trim((string) $overtime->overtime_date) === '') {
                    return false;
                }

                $overtimeDate = Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->startOfDay();

                return $overtimeDate->betweenIncluded($periodStart, $periodEnd);
            })
            ->sum(fn (AttendanceOvertime $overtime): int => $this->overtimeDurationMinutes($overtime));
    }

    /**
     * @param  Collection<int, AttendanceOvertime>  $overtimes
     */
    private function paidOutOvertimeMinutes(Collection $overtimes): int
    {
        return (int) $overtimes
            ->filter(function (AttendanceOvertime $overtime): bool {
                return $overtime->lifecycleLogs->contains(function (OvertimeLifecycleLog $lifecycleLog): bool {
                    $eventKey = is_string($lifecycleLog->event_key) ? trim($lifecycleLog->event_key) : '';
                    $status = strtolower(trim((string) $lifecycleLog->status));

                    return $eventKey === 'payment_disbursement' && in_array($status, ['complete', 'completed', 'approved'], true);
                });
            })
            ->sum(fn (AttendanceOvertime $overtime): int => $this->overtimeDurationMinutes($overtime));
    }

    private function overtimeDurationMinutes(AttendanceOvertime $overtime): int
    {
        $startTime = $this->nullableStringValue($overtime->actual_start_time);
        $endTime = $this->nullableStringValue($overtime->actual_end_time);

        return $this->durationMinutes($startTime, $endTime);
    }

    private function durationMinutesLabel(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 hours';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }

        return "{$hours}h {$remainingMinutes}m";
    }

    private function currentCompanyIdFor(User $user): ?string
    {
        $user->loadMissing('employee.deployment:id,employee_id,current_company_id');
        $companyId = $user->employee?->deployment?->current_company_id;

        return is_string($companyId) && trim($companyId) !== '' ? trim($companyId) : null;
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    private function assignableStaffOptionsFor(User $user, ?string $companyId): Collection
    {
        $employeeIds = $this->activeSupervisedEmployeeIdsFor($user, $companyId, Carbon::now('Asia/Jakarta')->startOfDay());

        if ($employeeIds->isEmpty()) {
            return collect();
        }

        return Employee::query()
            ->select(['id', 'user_id'])
            ->with([
                'profile:id,employee_id,name',
                'user:id,username,email',
            ])
            ->whereIn('id', $employeeIds)
            ->get()
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'name' => $this->employeeDisplayName($employee),
            ])
            ->sortBy('name')
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function activeSupervisedEmployeeIdsFor(User $user, ?string $companyId, Carbon $referenceDate): Collection
    {
        $supervisorEmployeeId = $this->currentEmployeeIdFor($user);

        if ($supervisorEmployeeId === null) {
            return collect();
        }

        $assignedStaffIds = DB::table('employee_pic_assignments')
            ->where('supervisor_employee_id', $supervisorEmployeeId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->pluck('staff_employee_id')
            ->filter(static fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->map(static fn (string $employeeId): string => trim($employeeId))
            ->values();

        if ($assignedStaffIds->isEmpty()) {
            return collect();
        }

        return Employee::query()
            ->whereIn('id', $assignedStaffIds)
            ->whereRaw("LOWER(COALESCE(status, '')) = ?", ['active'])
            ->whereNull('deleted_at')
            ->whereHas('user', function ($query): void {
                $query
                    ->where('is_active', true)
                    ->whereNull('deleted_at');
            })
            ->whereHas('deployment', function ($query) use ($referenceDate): void {
                $query
                    ->whereRaw("LOWER(COALESCE(status, '')) = ?", ['active'])
                    ->whereNull('deleted_at')
                    ->where(function ($dateQuery) use ($referenceDate): void {
                        $dateQuery
                            ->whereNull('join_date')
                            ->orWhere('join_date', '<=', $referenceDate->toDateString());
                    })
                    ->where(function ($dateQuery) use ($referenceDate): void {
                        $dateQuery
                            ->whereNull('resignation_date')
                            ->orWhere('resignation_date', '>=', $referenceDate->toDateString());
                    });
            })
            ->pluck('id')
            ->map(static fn (string $employeeId): string => trim($employeeId))
            ->values();
    }

    private function currentEmployeeIdFor(User $user): ?string
    {
        $user->loadMissing('employee:id,user_id');
        $employeeId = $user->employee?->id;

        return is_string($employeeId) && trim($employeeId) !== '' ? trim($employeeId) : null;
    }

    private function employeeDisplayName(?Employee $employee): string
    {
        $profileNameValue = $employee?->profile?->name;
        $profileName = is_string($profileNameValue) ? trim($profileNameValue) : '';
        if ($profileName !== '') {
            return $profileName;
        }

        $usernameValue = $employee?->user?->username;
        $username = is_string($usernameValue) ? trim($usernameValue) : '';
        if ($username !== '') {
            return $username;
        }

        $emailValue = $employee?->user?->email;
        $email = is_string($emailValue) ? trim($emailValue) : '';

        return $email !== '' ? $email : '-';
    }

    private function employeeUsernameLabel(?Employee $employee): string
    {
        $username = is_string($employee?->user?->username) ? trim($employee->user->username) : '';
        if ($username !== '') {
            return $username;
        }

        $email = is_string($employee?->user?->email) ? trim($employee->user->email) : '';

        return $email !== '' ? $email : '-';
    }

    private function normalizeSubmittedTime(string $time): string
    {
        return Carbon::createFromFormat('H:i', $time, 'Asia/Jakarta')->format('H:i:s');
    }

    private function throwPicOvertimeValidationException(string $field, string $message, string $errorBag = 'picOvertimeStore'): never
    {
        $exception = ValidationException::withMessages([$field => $message]);
        $exception->errorBag = $errorBag;

        throw $exception;
    }

    private function createInitialOvertimeLifecycleLogs(AttendanceOvertime $overtime, User $actor, Carbon $createdAt): void
    {
        foreach (self::OVERTIME_LIFECYCLE_STEPS as $lifecycleStep) {
            OvertimeLifecycleLog::query()->create([
                'overtime_id' => $overtime->id,
                'phase' => $lifecycleStep['phase'],
                'event_key' => $lifecycleStep['event_key'],
                'step_order' => $lifecycleStep['step_order'],
                'title' => $lifecycleStep['title'],
                'status' => $lifecycleStep['status'],
                'actor_id' => $lifecycleStep['event_key'] === 'assignment_submitted' ? $actor->id : null,
                'happened_at' => $lifecycleStep['event_key'] === 'assignment_submitted' ? $createdAt : null,
                'metadata' => [
                    'created_from' => 'pic_overtime_add_modal',
                    'overtime_status' => $overtime->status,
                ],
            ]);
        }
    }
}
