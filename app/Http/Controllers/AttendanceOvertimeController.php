<?php

namespace App\Http\Controllers;

use App\Models\AttendanceOvertime;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceOvertimeController extends Controller
{
    public function index(): View
    {
        $authenticatedUser = Auth::user();

        return view('absensi.lembur', [
            'canSubmitOvertime' => $this->isStaffUser($authenticatedUser),
            'canManageOvertimeActions' => $this->isSuperuserUser($authenticatedUser) || $this->isBoardOfDirectur($authenticatedUser),
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
            ->with(['employee.user:id,name'])
            ->latest('id')
            ->select([
                'id',
                'employee_id',
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
            $overtimesQuery->where('employee_id', $authenticatedEmployeeId);
        }

        $overtimes = $overtimesQuery->get();

        $tableRows = $overtimes->map(function (AttendanceOvertime $overtime): array {
            $date = $overtime->overtime_date instanceof Carbon
                ? $overtime->overtime_date
                : Carbon::parse($overtime->overtime_date);
            $startTime = $this->normalizeTimeString($overtime->planned_start_time);
            $endTime = $this->normalizeTimeString($overtime->planned_end_time);

            return [
                'id' => $overtime->id,
                'overtime_date' => $date->format('d M Y'),
                'staff_name' => $overtime->employee?->user?->name ?? '-',
                'time_range' => $startTime.' - '.$endTime,
                'duration' => $this->calculateDurationLabel($overtime->planned_start_time, $overtime->planned_end_time),
                'status' => (string) ($overtime->status ?? 'pending'),
                'description' => $overtime->instruction ?: '-',
            ];
        })->values();

        return response()->json([
            'data' => $tableRows,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'overtime_date' => ['required', 'date_format:d/m/Y'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'description' => ['required', 'string', 'max:5000'],
            'approval_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee');
        }
        $employeeId = $authenticatedUser?->employee?->id;
        $assignedByUserId = $authenticatedUser?->id;
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data employee belum tersedia untuk user ini.',
            ], 422);
        }
        if (! is_string($assignedByUserId) || trim($assignedByUserId) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data user belum tersedia untuk aksi ini.',
            ], 422);
        }

        $overtimeDate = Carbon::createFromFormat('d/m/Y', $validated['overtime_date'])->format('Y-m-d');
        $startTime = $this->normalizeStoreTimeValue($validated['start_time']);
        $endTime = $this->normalizeStoreTimeValue($validated['end_time']);

        if (! $startTime || ! $endTime) {
            return response()->json([
                'success' => false,
                'message' => 'Format jam lembur tidak valid.',
            ], 422);
        }

        AttendanceOvertime::create([
            'employee_id' => $employeeId,
            'assigned_by' => $assignedByUserId,
            'overtime_date' => $overtimeDate,
            'planned_start_time' => $startTime,
            'planned_end_time' => $endTime,
            'instruction' => $validated['description'],
            'calculated_hours' => $this->calculateDurationHours($startTime, $endTime),
            'status' => 'pending',
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

        $attendanceOvertime->loadMissing('employee.user:id,name');
        $overtimeDate = $attendanceOvertime->overtime_date instanceof Carbon
            ? $attendanceOvertime->overtime_date
            : Carbon::parse($attendanceOvertime->overtime_date);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendanceOvertime->id,
                'staff_name' => $attendanceOvertime->employee?->user?->name ?? '-',
                'overtime_date' => $overtimeDate->format('d M Y'),
                'overtime_date_input' => $overtimeDate->format('d/m/Y'),
                'start_time' => $this->normalizeStoreTimeValue($attendanceOvertime->planned_start_time),
                'end_time' => $this->normalizeStoreTimeValue($attendanceOvertime->planned_end_time),
                'duration' => $this->calculateDurationLabel($attendanceOvertime->planned_start_time, $attendanceOvertime->planned_end_time),
                'status' => (string) ($attendanceOvertime->status ?? 'pending'),
                'description' => $attendanceOvertime->instruction ?: '-',
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
            'overtime_date' => ['required', 'date_format:d/m/Y'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'description' => ['required', 'string', 'max:5000'],
            'approval_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $overtimeDate = Carbon::createFromFormat('d/m/Y', $validated['overtime_date'])->format('Y-m-d');
        $startTime = $this->normalizeStoreTimeValue($validated['start_time']);
        $endTime = $this->normalizeStoreTimeValue($validated['end_time']);

        if (! $startTime || ! $endTime) {
            return response()->json([
                'success' => false,
                'message' => 'Format jam lembur tidak valid.',
            ], 422);
        }

        $status = strtolower(trim((string) $validated['approval_status']));
        if ($status === 'rejected') {
            $status = 'rejected';
        }

        $attendanceOvertime->update([
            'overtime_date' => $overtimeDate,
            'planned_start_time' => $startTime,
            'planned_end_time' => $endTime,
            'instruction' => $validated['description'],
            'calculated_hours' => $this->calculateDurationHours($startTime, $endTime),
            'status' => $status,
        ]);

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

    private function calculateDurationHours(string $startTimeValue, string $endTimeValue): float
    {
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
            || $normalizedRoleNames->contains('board of directors');
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
}
