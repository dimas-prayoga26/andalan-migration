<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceHoliday;
use App\Models\Employee;
use App\Models\ProjectTask;
use App\Models\User;
use App\Support\Employee\EmployeeMonthlySummaryCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EmployeeMonthlySummaryCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_counts_attended_work_days_and_completed_tasks_for_the_current_month(): void
    {
        $employee = $this->createEmployee('summary.staff');
        $otherEmployee = $this->createEmployee('other.staff');
        AttendanceHoliday::query()->create(['date' => '2026-10-12', 'name' => 'Holiday', 'type' => 1]);

        foreach ([
            ['2026-10-01', '08:00:00', null],
            ['2026-10-02', '08:00:00', null],
            ['2026-10-03', '08:00:00', null],
            ['2026-10-05', null, null],
            ['2026-10-06', '08:00:00', now()],
            ['2026-10-12', '08:00:00', null],
            ['2026-10-22', '08:00:00', null],
            ['2026-09-30', '08:00:00', null],
        ] as [$date, $clockIn, $deletedAt]) {
            Attendance::query()->create([
                'employee_id' => $employee->id,
                'date' => $date,
                'clock_in' => $clockIn,
                'status' => 'Masuk',
                'deleted_at' => $deletedAt,
            ]);
        }

        foreach ([
            [$employee, 'completed', '2026-10-05 10:00:00'],
            [$employee, 'completed', '2026-10-20 16:00:00'],
            [$employee, 'completed', '2026-09-30 16:00:00'],
            [$employee, 'in_progress', null],
            [$otherEmployee, 'completed', '2026-10-07 09:00:00'],
        ] as [$taskEmployee, $status, $completedAt]) {
            ProjectTask::query()->create([
                'employee_id' => $taskEmployee->id,
                'title' => 'Task '.$status,
                'status' => $status,
                'completed_at' => $completedAt,
            ]);
        }
        ProjectTask::query()->create([
            'employee_id' => $employee->id,
            'title' => 'Deleted task',
            'status' => 'completed',
            'completed_at' => '2026-10-08 09:00:00',
        ])->delete();

        $summary = app(EmployeeMonthlySummaryCalculator::class)->forEmployee($employee, Carbon::parse('2026-10-21 10:00', 'Asia/Jakarta'));

        $this->assertSame([
            'month_label' => 'October',
            'attended_days' => 2,
            'working_days' => 21,
            'completed_tasks' => 2,
        ], $summary);
    }

    public function test_employee_without_activity_has_zero_counts(): void
    {
        $employee = $this->createEmployee('quiet.staff');

        $summary = app(EmployeeMonthlySummaryCalculator::class)->forEmployee($employee, Carbon::parse('2026-02-10', 'Asia/Jakarta'));

        $this->assertSame([
            'month_label' => 'February',
            'attended_days' => 0,
            'working_days' => 20,
            'completed_tasks' => 0,
        ], $summary);
    }

    private function createEmployee(string $username): Employee
    {
        $user = User::query()->create([
            'email' => $username.'@example.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        return Employee::query()->create(['user_id' => $user->id, 'status' => 'Active']);
    }
}
