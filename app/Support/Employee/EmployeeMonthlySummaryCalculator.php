<?php

namespace App\Support\Employee;

use App\Models\Attendance;
use App\Models\AttendanceHoliday;
use App\Models\Employee;
use App\Models\ProjectTask;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the current-month summary on the Employee Details profile card.
 * Attendance follows the admin recap: attended work days so far against all work days (weekdays minus holidays) in the month.
 */
class EmployeeMonthlySummaryCalculator
{
    /**
     * @return array{month_label: string, attended_days: int, working_days: int, completed_tasks: int}
     */
    public function forEmployee(Employee $employee, ?CarbonInterface $referenceDate = null): array
    {
        $today = Carbon::instance($referenceDate ?? now('Asia/Jakarta'))->timezone('Asia/Jakarta')->startOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth()->startOfDay();
        $monthWorkDayKeys = $this->workDayKeysBetween($monthStart, $monthEnd);

        $attendedDays = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart->toDateString(), $today->toDateString()])
            ->whereNotNull('clock_in')
            ->whereNull('deleted_at')
            ->pluck('date')
            ->map(fn (mixed $date): string => Carbon::parse($date)->toDateString())
            ->unique()
            ->intersect($monthWorkDayKeys)
            ->count();

        $completedTasks = ProjectTask::query()
            ->where('employee_id', $employee->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$monthStart, $monthEnd->copy()->endOfDay()])
            ->count();

        return [
            'month_label' => $monthStart->format('F'),
            'attended_days' => $attendedDays,
            'working_days' => $monthWorkDayKeys->count(),
            'completed_tasks' => $completedTasks,
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function workDayKeysBetween(Carbon $startDate, Carbon $endDate): Collection
    {
        $holidayKeys = AttendanceHoliday::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('date')
            ->map(fn (mixed $date): string => Carbon::parse($date)->toDateString())
            ->flip();
        $workDayKeys = collect();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if (! $date->isWeekend() && ! $holidayKeys->has($date->toDateString())) {
                $workDayKeys->push($date->toDateString());
            }
        }

        return $workDayKeys;
    }
}
