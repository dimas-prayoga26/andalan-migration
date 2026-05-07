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
        return view('absensi.lembur', [
            'canSubmitOvertime' => $this->isStaffUser(Auth::user()),
        ]);
    }

    public function datatable(): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('userEmployee');
        }

        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isAdminUser = $this->isAdminUser($authenticatedUser);
        $userCompanyId = $authenticatedUser?->userEmployee?->company_id;
        $overtimesQuery = AttendanceOvertime::query()
            ->with(['user:id,name'])
            ->latest('id')
            ->select([
                'id',
                'user_id',
                'overtime_date',
                'start_time',
                'end_time',
                'description',
                'notes',
                'approval_status',
            ]);

        if ($isBoardOfDirectur && $userCompanyId) {
            $overtimesQuery->whereHas('user.userEmployee', function ($query) use ($userCompanyId): void {
                $query->where('company_id', $userCompanyId);
            });
        } elseif (! $isAdminUser) {
            $overtimesQuery->where('user_id', Auth::id());
        }

        $overtimes = $overtimesQuery->get();

        $tableRows = $overtimes->map(function (AttendanceOvertime $overtime): array {
            $date = $overtime->overtime_date instanceof Carbon
                ? $overtime->overtime_date
                : Carbon::parse($overtime->overtime_date);
            $startTime = $this->normalizeTimeString($overtime->start_time);
            $endTime = $this->normalizeTimeString($overtime->end_time);

            return [
                'id' => $overtime->id,
                'overtime_date' => $date->format('d M Y'),
                'staff_name' => $overtime->user?->name ?? '-',
                'time_range' => $startTime.' - '.$endTime,
                'duration' => $this->calculateDurationLabel($overtime->start_time, $overtime->end_time),
                'status' => (string) ($overtime->approval_status ?? 'pending'),
                'description' => $overtime->description ?: ($overtime->notes ?: '-'),
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
            'user_id' => Auth::id(),
            'overtime_date' => $overtimeDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'description' => $validated['description'],
            'notes' => null,
            'approval_status' => 'pending',
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

        $attendanceOvertime->loadMissing('user:id,name');
        $overtimeDate = $attendanceOvertime->overtime_date instanceof Carbon
            ? $attendanceOvertime->overtime_date
            : Carbon::parse($attendanceOvertime->overtime_date);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendanceOvertime->id,
                'staff_name' => $attendanceOvertime->user?->name ?? '-',
                'overtime_date' => $overtimeDate->format('d M Y'),
                'overtime_date_input' => $overtimeDate->format('d/m/Y'),
                'start_time' => $this->normalizeStoreTimeValue($attendanceOvertime->start_time),
                'end_time' => $this->normalizeStoreTimeValue($attendanceOvertime->end_time),
                'duration' => $this->calculateDurationLabel($attendanceOvertime->start_time, $attendanceOvertime->end_time),
                'status' => (string) ($attendanceOvertime->approval_status ?? 'pending'),
                'description' => $attendanceOvertime->description ?: ($attendanceOvertime->notes ?: '-'),
            ],
        ]);
    }

    public function update(Request $request, AttendanceOvertime $attendanceOvertime): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canAccessOvertime($authenticatedUser, $attendanceOvertime)) {
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

        $attendanceOvertime->update([
            'overtime_date' => $overtimeDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'description' => $validated['description'],
            'approval_status' => $validated['approval_status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data lembur berhasil diperbarui.',
        ]);
    }

    public function destroy(AttendanceOvertime $attendanceOvertime): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $this->canAccessOvertime($authenticatedUser, $attendanceOvertime)) {
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

    private function canAccessOvertime(?User $authenticatedUser, AttendanceOvertime $attendanceOvertime): bool
    {
        if (! $authenticatedUser instanceof User) {
            return false;
        }

        if ($this->isAdminUser($authenticatedUser)) {
            return true;
        }

        if ((int) $authenticatedUser->id === (int) $attendanceOvertime->user_id) {
            return true;
        }

        if (! $this->isBoardOfDirectur($authenticatedUser)) {
            return false;
        }

        $authenticatedUser->loadMissing('userEmployee');
        $userCompanyId = (int) ($authenticatedUser->userEmployee?->company_id ?? 0);
        if ($userCompanyId <= 0) {
            return false;
        }

        return $attendanceOvertime->user()
            ->whereHas('userEmployee', function ($query) use ($userCompanyId): void {
                $query->where('company_id', $userCompanyId);
            })
            ->exists();
    }
}
