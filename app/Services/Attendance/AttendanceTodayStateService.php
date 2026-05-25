<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceLog;
use App\Models\User;

class AttendanceTodayStateService
{
    /**
     * @return array{
     *   employeeId:?string,
     *   todayJakartaDate:string,
     *   absensiHariIni:?Attendance,
     *   todayAttendanceId:?string,
     *   todayAttendanceDistanceKm:?float,
     *   todayAttendanceDistanceOutKm:?float,
     *   hasEarlyDepartureExceptionToday:bool,
     *   hasCheckedInToday:bool,
     *   hasCheckedOutToday:bool
     * }
     */
    public function getTodayStateForUser(?User $authenticatedUser): array
    {
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee');
        }

        $employeeId = is_string($authenticatedUser?->employee?->id)
            ? trim($authenticatedUser->employee->id)
            : '';
        $todayJakartaDate = now('Asia/Jakarta')->toDateString();
        $absensiHariIni = null;
        $todayAttendanceDistanceKm = null;
        $todayAttendanceDistanceOutKm = null;
        $hasEarlyDepartureExceptionToday = false;

        if ($employeeId !== '') {
            $absensiHariIni = Attendance::query()
                ->where('date', $todayJakartaDate)
                ->where('employee_id', $employeeId)
                ->first();
        }

        $todayAttendanceId = is_string($absensiHariIni?->id) ? trim($absensiHariIni->id) : null;
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
                $hasEarlyDepartureExceptionToday = $todayAttendanceException->type === 'early_departure';
            }
        }

        $normalizedEmployeeId = $employeeId !== '' ? $employeeId : null;
        $hasCheckedInToday = ! empty($absensiHariIni?->clock_in);
        $hasCheckedOutToday = ! empty($absensiHariIni?->clock_out);

        return [
            'employeeId' => $normalizedEmployeeId,
            'todayJakartaDate' => $todayJakartaDate,
            'absensiHariIni' => $absensiHariIni,
            'todayAttendanceId' => $todayAttendanceId,
            'todayAttendanceDistanceKm' => $todayAttendanceDistanceKm,
            'todayAttendanceDistanceOutKm' => $todayAttendanceDistanceOutKm,
            'hasEarlyDepartureExceptionToday' => $hasEarlyDepartureExceptionToday,
            'hasCheckedInToday' => $hasCheckedInToday,
            'hasCheckedOutToday' => $hasCheckedOutToday,
        ];
    }
}
