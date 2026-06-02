<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AttendanceOvertimeController extends Controller
{
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
                ->where('status', 'approved')
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
                'status' => (string) ($overtime->status ?? 'pending'),
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
            'status' => ['nullable', 'in:pending,approved'],
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

        AttendanceOvertime::create([
            'employee_id' => $employeeId,
            'assigned_by' => $assignedByUserId,
            'overtime_date' => $overtimeDate,
            'planned_start_time' => $startTime,
            'planned_end_time' => $endTime,
            'instruction' => $validated['instruction'],
            'actual_start_time' => $actualStartTime,
            'actual_end_time' => $actualEndTime,
            'calculated_hours' => $this->calculateDurationHours($actualStartTime, $actualEndTime),
            'status' => $isStaffUser
                ? 'pending'
                : strtolower(trim((string) ($validated['status'] ?? 'pending'))),
        ]);

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
                'status' => (string) ($attendanceOvertime->status ?? 'pending'),
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
            'status' => ['nullable', 'in:pending,approved'],
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

        $status = strtolower(trim((string) ($validated['status'] ?? ($attendanceOvertime->status ?? 'pending'))));
        if ($isStaffUser) {
            $status = ($actualStartTime && $actualEndTime) ? 'approved' : 'pending';
        }

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

        $attendanceOvertime->update($updatePayload);

        if ($isStaffUser && $status === 'approved' && $actualStartTime && $actualEndTime) {
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
                    ->orWhere('status', 'pending');
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
