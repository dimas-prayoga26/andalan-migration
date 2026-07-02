<?php

namespace App\Http\Controllers\DirectorAttendance;

use App\Http\Controllers\PicAttendance\PicAttendanceController;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DirectorAttendanceController extends PicAttendanceController
{
    protected function attendanceIndexView(): string
    {
        return 'director_attendance.attendance.index';
    }

    protected function attendanceDetailView(): string
    {
        return 'director_attendance.attendance.detail-employees';
    }

    protected function activeEmployeeIdsFor(Carbon $date, ?string $companyId): Collection
    {
        if (! is_string($companyId) || trim($companyId) === '') {
            return collect();
        }

        $todayDate = $date->toDateString();

        return Employee::query()
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
            ->whereHas('user', function ($query): void {
                $query->where('is_active', true);
            })
            ->whereHas('deployment', function ($query) use ($todayDate, $companyId): void {
                $query
                    ->whereNull('deleted_at')
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->where('current_company_id', $companyId)
                    ->where(function ($query) use ($todayDate): void {
                        $query
                            ->whereNull('join_date')
                            ->orWhereDate('join_date', '<=', $todayDate);
                    })
                    ->where(function ($query) use ($todayDate): void {
                        $query
                            ->whereNull('resignation_date')
                            ->orWhereDate('resignation_date', '>=', $todayDate);
                    });
            })
            ->pluck('id')
            ->filter(static fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->values();
    }
}
