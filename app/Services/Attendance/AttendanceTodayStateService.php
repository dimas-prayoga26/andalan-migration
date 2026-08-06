<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceLog;
use App\Models\User;

class AttendanceTodayStateService
{
    public function __construct(private AttendanceContextService $attendanceContextService) {}

    /**
     * @return array{
     *   employeeId:?string,
     *   todayJakartaDate:string,
     *   todayAttendance:?Attendance,
     *   todayAttendanceId:?string,
     *   todayAttendanceDistanceKm:?float,
     *   todayAttendanceDistanceOutKm:?float,
     *   hasAttendanceExceptionToday:bool,
     *   hasEarlyDepartureExceptionToday:bool,
     *   hasCheckedInToday:bool,
     *   hasCheckedOutToday:bool
     * }
     */
    public function getTodayStateForUser(?User $authenticatedUser, ?array $officeContext = null): array
    {
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee');
        }

        $employeeId = is_string($authenticatedUser?->employee?->id)
            ? trim($authenticatedUser->employee->id)
            : '';
        $todayJakartaDate = $this->attendanceContextService->attendanceDateFor(now('Asia/Jakarta'), $officeContext);
        $todayAttendance = null;
        $todayAttendanceDistanceKm = null;
        $todayAttendanceDistanceOutKm = null;
        $hasAttendanceExceptionToday = false;
        $hasEarlyDepartureExceptionToday = false;

        if ($employeeId !== '') {
            $todayAttendance = Attendance::query()
                ->where('date', $todayJakartaDate)
                ->where('employee_id', $employeeId)
                ->first();
        }

        $todayAttendanceId = is_string($todayAttendance?->id) ? trim($todayAttendance->id) : null;
        if (is_string($todayAttendanceId) && $todayAttendanceId !== '') {
            $latestDistanceIn = AttendanceLog::query()
                ->where('attendance_id', $todayAttendanceId)
                ->where('type', true)
                ->whereNotNull('distance')
                ->orderByDesc('created_at')
                ->value('distance');
            $latestDistanceOut = AttendanceLog::query()
                ->where('attendance_id', $todayAttendanceId)
                ->where('type', false)
                ->whereNotNull('distance')
                ->orderByDesc('created_at')
                ->value('distance');

            if (is_numeric($latestDistanceIn)) {
                $todayAttendanceDistanceKm = round(((float) $latestDistanceIn) / 1000, 2);
            }

            if (is_numeric($latestDistanceOut)) {
                $todayAttendanceDistanceOutKm = round(((float) $latestDistanceOut) / 1000, 2);
            }
        }

        if ($employeeId !== '') {
            $todayAttendanceException = AttendanceException::query()
                ->where('employee_id', $employeeId)
                ->whereDate('exception_date', $todayJakartaDate)
                ->latest('created_at')
                ->first(['type']);

            if ($todayAttendanceException instanceof AttendanceException) {
                $hasAttendanceExceptionToday = true;
                $hasEarlyDepartureExceptionToday = $todayAttendanceException->type === 'early_departure';
            }
        }

        $normalizedEmployeeId = $employeeId !== '' ? $employeeId : null;
        $hasCheckedInToday = ! empty($todayAttendance?->clock_in);
        $hasCheckedOutToday = ! empty($todayAttendance?->clock_out);

        return [
            'employeeId' => $normalizedEmployeeId,
            'todayJakartaDate' => $todayJakartaDate,
            'todayAttendance' => $todayAttendance,
            'todayAttendanceId' => $todayAttendanceId,
            'todayAttendanceDistanceKm' => $todayAttendanceDistanceKm,
            'todayAttendanceDistanceOutKm' => $todayAttendanceDistanceOutKm,
            'hasAttendanceExceptionToday' => $hasAttendanceExceptionToday,
            'hasEarlyDepartureExceptionToday' => $hasEarlyDepartureExceptionToday,
            'hasCheckedInToday' => $hasCheckedInToday,
            'hasCheckedOutToday' => $hasCheckedOutToday,
        ];
    }
}
