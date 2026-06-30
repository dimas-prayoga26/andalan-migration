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
        $tableData = $tableBuilder->buildForContext('pic', $companyId, $assignedByUserId, $request->query('month'), $request->query('year'));

        return view('pic_attendance.overtime.index', [
            'overtimeSummary' => $metricBuilder->summarizeForCompany(
                $companyId,
                $assignedByUserId
            ),
            'assignableStaffOptions' => $authenticatedUser instanceof User
                ? $this->assignableStaffOptionsFor($authenticatedUser, $companyId)
                : collect(),
            'picOvertimeStaffGroups' => $authenticatedUser instanceof User
                ? $this->picOvertimeStaffGroupsFor(
                    $authenticatedUser,
                    $companyId,
                    $assignedByUserId,
                    (int) $tableData['selectedMonth'],
                    (int) $tableData['selectedYear']
                )
                : collect(),
            'overtimeCards' => $this->picOvertimeCardsFor(
                $companyId,
                $assignedByUserId,
                (int) $tableData['selectedMonth'],
                (int) $tableData['selectedYear']
            ),
            ...$tableData,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('picOvertimeStore', [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'employee_id' => ['required', 'string'],
            'instruction' => ['required', 'string', 'max:5000'],
        ]);

        $authenticatedUser = $request->user();
        abort_unless($authenticatedUser instanceof User, 403);

        $startDate = Carbon::createFromFormat('Y-m-d', $validated['start_date'], 'Asia/Jakarta')->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $validated['end_date'], 'Asia/Jakarta')->startOfDay();
        $startTime = $this->normalizeSubmittedTime($validated['start_time']);
        $endTime = $this->normalizeSubmittedTime($validated['end_time']);
        $selectedEmployeeId = trim((string) $validated['employee_id']);
        $assignableStaffIds = $this->activeSupervisedEmployeeIdsFor(
            $authenticatedUser,
            $this->currentCompanyIdFor($authenticatedUser),
            $startDate
        );

        if (! $assignableStaffIds->contains($selectedEmployeeId)) {
            $this->throwPicOvertimeValidationException('employee_id', 'Staff yang dipilih bukan bawahan supervisor ini.');
        }

        $overtimeDates = $this->overtimeAssignmentDates($startDate, $endDate, $startTime, $endTime);

        DB::transaction(function () use ($authenticatedUser, $validated, $selectedEmployeeId, $overtimeDates, $startTime, $endTime): void {
            $createdAt = Carbon::now('Asia/Jakarta');

            foreach ($overtimeDates as $overtimeDate) {
                $overtime = AttendanceOvertime::query()->create([
                    'employee_id' => $selectedEmployeeId,
                    'assigned_by' => $authenticatedUser->id,
                    'overtime_date' => $overtimeDate->toDateString(),
                    'planned_start_time' => $startTime,
                    'planned_end_time' => $endTime,
                    'instruction' => $validated['instruction'],
                    'actual_start_time' => null,
                    'actual_end_time' => null,
                    'calculated_hours' => null,
                    'status' => 'assigned',
                ]);

                $this->createInitialOvertimeLifecycleLogs($overtime, $authenticatedUser, $createdAt);
            }
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
                'planned_start_time',
                'planned_end_time',
                'actual_start_time',
                'actual_end_time',
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
                'projectTasks:id,overtime_id,employee_id,project_id,title,description,status,priority,start_date,due_date,blockers,attachment_path,completed_at,created_at',
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
            ->select(['id', 'assigned_by', 'actual_start_time', 'actual_end_time'])
            ->with('lifecycleLogs:id,overtime_id,event_key,phase,step_order,title,status,actor_id,happened_at,metadata')
            ->whereKey($uid)
            ->where('assigned_by', $authenticatedUser->id)
            ->firstOrFail();

        if (! $this->isTaskDeliverablesSubmitted($overtime)) {
            $this->throwPicOvertimeValidationException('approved_start_time', 'Task & Deliverables Submitted harus complete sebelum session diverifikasi.', 'picOvertimeVerify');
        }

        DB::transaction(function () use ($authenticatedUser, $approvedStartTime, $approvedEndTime, $overtime): void {
            $approvedAt = Carbon::now('Asia/Jakarta');

            $overtime->forceFill([
                'actual_start_time' => $approvedStartTime,
                'actual_end_time' => $approvedEndTime,
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
     *     planned_time_range:string,
     *     actual_time_range:string,
     *     time_changed:bool,
     *     planned_duration:string,
     *     actual_duration:string,
     *     duration_changed:bool,
     *     verification_ready:bool,
     *     verification_start_time:string,
     *     verification_end_time:string,
     *     verification_duration:string,
     *     is_task_hours_verified:bool,
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
        $plannedStartTime = $this->formatTime($overtime->planned_start_time);
        $plannedEndTime = $this->formatTime($overtime->planned_end_time);
        $actualStartTime = $this->formatTime($overtime->actual_start_time);
        $actualEndTime = $this->formatTime($overtime->actual_end_time);
        $hasActualTime = $this->hasActualOvertimeTimes($overtime);
        $plannedTimeRange = $plannedStartTime.' - '.$plannedEndTime;
        $actualTimeRange = $hasActualTime ? $actualStartTime.' - '.$actualEndTime : '-';
        $plannedDuration = $this->durationLabel($overtime->planned_start_time, $overtime->planned_end_time);
        $actualDuration = $hasActualTime ? $this->durationLabel($overtime->actual_start_time, $overtime->actual_end_time) : '-';
        $taskDeliverablesSubmitted = $this->isTaskDeliverablesSubmitted($overtime);
        $taskHoursVerified = $this->isTaskHoursVerified($overtime);
        $verificationLog = $this->overtimeLifecycleLog($overtime, 'task_hours_verification');
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
            'planned_start_time' => $plannedStartTime,
            'planned_end_time' => $plannedEndTime,
            'actual_start_time' => $actualStartTime,
            'actual_end_time' => $actualEndTime,
            'planned_time_range' => $plannedTimeRange,
            'actual_time_range' => $actualTimeRange,
            'time_changed' => $hasActualTime && $actualTimeRange !== $plannedTimeRange,
            'planned_duration' => $plannedDuration,
            'actual_duration' => $actualDuration,
            'duration_changed' => $hasActualTime && $actualDuration !== $plannedDuration,
            'verification_ready' => $taskDeliverablesSubmitted,
            'verification_start_time' => $taskDeliverablesSubmitted ? ($actualStartTime !== '-' ? $actualStartTime : $plannedStartTime) : '-',
            'verification_end_time' => $taskDeliverablesSubmitted ? ($actualEndTime !== '-' ? $actualEndTime : $plannedEndTime) : '-',
            'verification_duration' => $taskDeliverablesSubmitted ? ($actualDuration !== '-' ? $actualDuration : $plannedDuration) : '-',
            'is_task_hours_verified' => $taskHoursVerified,
            'instruction' => is_string($overtime->instruction) && trim($overtime->instruction) !== '' ? trim($overtime->instruction) : '-',
            'payout_period' => 'Included in '.Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->format('F Y').' Payroll',
            'verified_note' => $hasActualTime ? 'Actual overtime hours recorded' : 'Waiting for clock-out',
            'verified_datetime' => $this->formatLifecycleDateTime($verificationLog),
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
     * @return Collection<int, Carbon>
     */
    private function overtimeAssignmentDates(Carbon $startDate, Carbon $endDate, string $startTime, string $endTime): Collection
    {
        if ($this->isSingleOvernightDateRange($startDate, $endDate, $startTime, $endTime)) {
            return collect([$startDate->copy()]);
        }

        $dates = collect();
        $overtimeDate = $startDate->copy();

        while ($overtimeDate->lte($endDate)) {
            $dates->push($overtimeDate->copy());
            $overtimeDate->addDay();
        }

        return $dates;
    }

    private function isSingleOvernightDateRange(Carbon $startDate, Carbon $endDate, string $startTime, string $endTime): bool
    {
        return $endTime <= $startTime
            && $startDate->copy()->addDay()->isSameDay($endDate);
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
            'status_value' => $status !== '' ? $status : 'pending',
            'priority' => strtolower(trim((string) ($projectTask->priority ?? 'medium'))) ?: 'medium',
            'attachment_path' => (string) ($projectTask->attachment_path ?? ''),
            'blockers' => (string) ($projectTask->blockers ?? ''),
            'checked' => $isFinished,
            'update_url' => route('pic-attendance.overtime.tasks.update', [
                'attendanceOvertime' => $overtime->id,
                'projectTask' => $projectTask->id,
            ]),
        ];
    }

    private function resolveOvertimeDirectorApprover(AttendanceOvertime $overtime): ?User
    {
        $overtime->loadMissing('employee.deployment');
        $companyId = $overtime->employee?->deployment?->current_company_id;

        $directorQuery = User::query()
            ->select(['id', 'username', 'email'])
            ->with('employee.profile')
            ->whereHas('roles', function (Builder $query): void {
                $query->whereIn('name', [
                    'Board of Directors',
                    'board of directors',
                    'board_of_directors',
                    'board_of_director',
                    'board_of_rector',
                    'board of directur',
                    'board_of_directur',
                    'Director',
                    'director',
                ]);
            });

        if (is_string($companyId) && trim($companyId) !== '') {
            $directorQuery->whereHas('employee.deployment', function (Builder $query) use ($companyId): void {
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
     *     time_lines:array<int, array{label:string,strike:bool}>,
     *     supervisor_name:string,
     *     detail_url:string,
     *     current_log:array{title:string,status:string,badge_class:string}
     * }>
     */
    private function picOvertimeCardsFor(?string $companyId, ?string $assignedByUserId, int $month, int $year): Collection
    {
        if (! is_string($companyId) || trim($companyId) === '' || ! is_string($assignedByUserId) || trim($assignedByUserId) === '') {
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
            ->where('assigned_by', trim($assignedByUserId))
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
     *     time_lines:array<int, array{label:string,strike:bool}>,
     *     supervisor_name:string,
     *     detail_url:string,
     *     current_log:array{title:string,status:string,badge_class:string}
     * }
     */
    private function picOvertimeCardFor(AttendanceOvertime $overtime): array
    {
        $task = $overtime->projectTasks->first();
        $lifecycleLogsByEvent = $overtime->lifecycleLogs->keyBy('event_key');
        $verificationStatus = strtolower(trim((string) ($lifecycleLogsByEvent->get('task_hours_verification')?->status ?? '')));
        $isVerified = $verificationStatus === 'verified';

        return [
            'uid' => (string) $overtime->id,
            'record_number' => '#'.(is_string($overtime->record_number) && trim($overtime->record_number) !== '' ? trim($overtime->record_number) : '-'),
            'employee_name' => $this->employeeDisplayName($overtime->employee),
            'instruction' => is_string($overtime->instruction) && trim($overtime->instruction) !== '' ? trim($overtime->instruction) : '-',
            'date_label' => $this->dateRangeLabel($task?->start_date ?: $overtime->overtime_date, $task?->due_date ?: $overtime->overtime_date),
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

    /**
     * @return array<int, array{label:string,strike:bool}>
     */
    private function timeLinesFor(AttendanceOvertime $overtime, bool $isVerified): array
    {
        $plannedTimeLabel = $this->timeRangeLabel($overtime->planned_start_time, $overtime->planned_end_time);

        if (! $isVerified || ! $this->hasActualOvertimeTimes($overtime)) {
            return [
                ['label' => $plannedTimeLabel, 'strike' => false],
            ];
        }

        return [
            ['label' => $plannedTimeLabel, 'strike' => true],
            ['label' => $this->timeRangeLabel($overtime->actual_start_time, $overtime->actual_end_time), 'strike' => false],
        ];
    }

    private function hasActualOvertimeTimes(AttendanceOvertime $overtime): bool
    {
        return is_string($overtime->actual_start_time)
            && trim($overtime->actual_start_time) !== ''
            && is_string($overtime->actual_end_time)
            && trim($overtime->actual_end_time) !== '';
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
                'planned_start_time',
                'planned_end_time',
                'actual_start_time',
                'actual_end_time',
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
        $startTime = $this->nullableStringValue($overtime->actual_start_time) ?? $this->nullableStringValue($overtime->planned_start_time);
        $endTime = $this->nullableStringValue($overtime->actual_end_time) ?? $this->nullableStringValue($overtime->planned_end_time);

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

        if ($supervisorEmployeeId === null || $companyId === null) {
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
            ->whereHas('deployment', function ($query) use ($companyId, $referenceDate): void {
                $query
                    ->where('current_company_id', $companyId)
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
