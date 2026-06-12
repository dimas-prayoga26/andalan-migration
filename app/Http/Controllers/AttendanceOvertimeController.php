<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\OvertimeLifecycleLog;
use App\Models\User;
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

    public function index(): View
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

        return view('attendance.overtimes.index', [
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
        ]);
    }

    public function detail(Request $request): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $requestedOvertimeId = is_string($request->query('id')) ? trim($request->query('id')) : '';
        $overtimeQuery = AttendanceOvertime::query()
            ->with([
                'employee.user:id,username',
                'employee.profile:id,employee_id,name',
                'assignedBy:id,username',
                'assignedBy.employee:id,user_id',
                'assignedBy.employee.profile:id,employee_id,name',
                'lifecycleLogs.actor',
                'lifecycleLogs.actor.employee.profile',
                'lifecycleLogs.actor.userProfile',
            ]);

        if ($requestedOvertimeId !== '') {
            $overtime = $overtimeQuery->whereKey($requestedOvertimeId)->firstOrFail();
            abort_unless($this->canAccessOvertime($authenticatedUser instanceof User ? $authenticatedUser : null, $overtime), 403);
        } else {
            $overtime = $this->applyOvertimeAccessFilter($overtimeQuery, $authenticatedUser instanceof User ? $authenticatedUser : null)
                ->latest('overtime_date')
                ->latest('created_at')
                ->first();
        }

        return view('attendance.overtimes.detail', [
            'overtime' => $overtime,
            'overtimeReference' => $overtime instanceof AttendanceOvertime ? $this->formatOvertimeReference($overtime) : '#OVT',
            'overtimeLifecycleTracker' => $overtime instanceof AttendanceOvertime ? $this->buildOvertimeLifecycleTracker($overtime) : collect(),
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
            $calculatedHoursDisplay = $hasActualTime
                ? number_format((float) ($overtime->calculated_hours ?? 0), 2, '.', '')
                : '-';

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
                'calculated_hours' => $this->calculateDurationHours($actualStartTime, $actualEndTime),
                'status' => $this->resolveOvertimeStatus(
                    $isStaffUser ? null : ($validated['status'] ?? null),
                    $actualStartTime,
                    $actualEndTime
                ),
            ]);

            $this->createInitialOvertimeLifecycleLogs($overtime, $assignmentActor, Carbon::now('Asia/Jakarta'));
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
                'message' => 'Anda harus absen masuk hari ini sebelum mengisi jam aktual lembur.',
            ], 422);
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
            'calculated_hours' => $this->calculateDurationHours($actualStartTime, $actualEndTime),
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
        if (! is_string($startTimeValue) || ! is_string($endTimeValue)) {
            return '-';
        }

        try {
            $startTime = Carbon::createFromFormat('H:i:s', $startTimeValue);
            $endTime = Carbon::createFromFormat('H:i:s', $endTimeValue);

            if ($endTime->lessThan($startTime)) {
                $endTime->addDay();
            }

            $totalMinutes = $startTime->diffInMinutes($endTime);
            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;

            return sprintf('%02d Jam %02d Menit', $hours, $minutes);
        } catch (\Throwable) {
            return '-';
        }
    }

    private function calculateDurationHours(?string $startTimeValue, ?string $endTimeValue): float
    {
        if (! is_string($startTimeValue) || trim($startTimeValue) === '' || ! is_string($endTimeValue) || trim($endTimeValue) === '') {
            return 0.0;
        }

        try {
            $startTime = Carbon::createFromFormat('H:i:s', $startTimeValue);
            $endTime = Carbon::createFromFormat('H:i:s', $endTimeValue);
            if ($endTime->lessThan($startTime)) {
                $endTime->addDay();
            }
            $totalMinutes = $startTime->diffInMinutes($endTime);

            return round($totalMinutes / 60, 2);
        } catch (\Throwable) {
            return 0.0;
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
            || $normalizedRoleNames->contains('board of directors')
            || $normalizedRoleNames->contains('supervisor');
    }

    private function isAdminUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('admin')
            || $normalizedRoleNames->contains('superuser');
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

    private function createInitialOvertimeLifecycleLogs(AttendanceOvertime $overtime, ?User $actor, Carbon $submittedAt): void
    {
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
                    'happened_at' => $isAssignmentStep ? $submittedAt : null,
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

        if ((string) $overtime->status === self::OVERTIME_STATUS_CANCELLED) {
            $this->updateOvertimeLifecycleLog(
                $overtime,
                'task_hours_verification',
                'cancelled',
                $actor,
                Carbon::now('Asia/Jakarta')
            );

            return;
        }

        $actualStartTime = $this->normalizeStoreTimeValue($overtime->actual_start_time);
        $actualEndTime = $this->normalizeStoreTimeValue($overtime->actual_end_time);

        if ($actualStartTime !== null) {
            $this->updateOvertimeLifecycleLog(
                $overtime,
                'session_started',
                'clock_in',
                $actor,
                $this->overtimeDateTimeFromTime($overtime, $actualStartTime)
            );
        }

        if ($actualEndTime === null) {
            return;
        }

        $staffActor = $overtime->employee?->user instanceof User ? $overtime->employee->user : $actor;

        $this->updateOvertimeLifecycleLog(
            $overtime,
            'task_deliverables_submitted',
            'completed',
            $staffActor,
            $this->overtimeDateTimeFromTime($overtime, $actualEndTime)
        );
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
            'verified',
            $overtime->assignedBy instanceof User ? $overtime->assignedBy : $actor,
            $this->overtimeDateTimeFromTime($overtime, $actualEndTime)
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
            ->map(function (Collection $lifecycleLogs, string $phase): array {
                $items = $lifecycleLogs
                    ->map(fn (OvertimeLifecycleLog $lifecycleLog): array => $this->overtimeLifecycleValueFromLog($lifecycleLog))
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

    private function overtimeLifecycleValueFromLog(OvertimeLifecycleLog $lifecycleLog): array
    {
        $status = trim(strtolower((string) ($lifecycleLog->status ?? ''))) ?: 'waiting';
        $state = $this->normalizeOvertimeLifecycleState($status);

        return [
            'event_key' => (string) $lifecycleLog->event_key,
            'step_order' => (int) $lifecycleLog->step_order,
            'title' => (string) $lifecycleLog->title,
            'state' => $state,
            'date_label' => $this->formatOvertimeLifecycleDateLabel($lifecycleLog->happened_at, $state),
            'datetime_label' => $this->formatOvertimeLifecycleDateTimeLabel($lifecycleLog->happened_at, $state),
            'actor_label' => $this->resolveUserDisplayName($lifecycleLog->actor),
            'status_label' => $this->overtimeLifecycleStatusLabel($status),
            'marker_class' => $this->overtimeLifecycleMarkerClass($state),
            'badge_class' => $this->overtimeLifecycleBadgeClass($state),
        ];
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
