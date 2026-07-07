<?php

namespace App\Http\Controllers\DirectorAttendance;

use App\Http\Controllers\AdminAttendance\AttendanceRecapController;

class DirectorAttendanceController extends AttendanceRecapController
{
    protected function attendanceIndexView(): string
    {
        return 'director_attendance.attendance.index';
    }

    protected function attendanceDetailView(): string
    {
        return 'director_attendance.attendance.detail-employees';
    }
}
