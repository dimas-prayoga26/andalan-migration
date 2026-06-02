<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceHoliday;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use App\View\Composers\AttendanceProfileComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceProfileComposerStaffStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_stats_are_computed_from_attendance_leave_and_holiday_tables(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-29 09:00:00', 'Asia/Jakarta'));

        try {
            $role = Role::query()->firstOrCreate([
                'name' => 'staff',
                'guard_name' => 'web',
            ]);

            $user = User::query()->create([
                'username' => 'staff_stats_tester',
                'email' => 'staff_stats_tester@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $user->assignRole($role);

            $employee = Employee::query()->create([
                'user_id' => $user->id,
                'status' => 'Active',
            ]);

            AttendanceHoliday::query()->create([
                'date' => '2026-05-28',
                'name' => 'Cuti Bersama',
                'type' => 2,
            ]);

            Attendance::query()->create([
                'employee_id' => $employee->id,
                'date' => '2026-05-26',
                'clock_in' => '08:00:00',
                'clock_out' => '17:00:00',
                'late_minutes' => 0,
                'work_hours' => 9,
                'status' => 'Masuk',
            ]);
            Attendance::query()->create([
                'employee_id' => $employee->id,
                'date' => '2026-05-27',
                'clock_in' => '08:10:00',
                'clock_out' => null,
                'late_minutes' => 10,
                'work_hours' => null,
                'status' => 'Terlambat',
            ]);
            Attendance::query()->create([
                'employee_id' => $employee->id,
                'date' => '2026-05-29',
                'clock_in' => '08:00:00',
                'clock_out' => null,
                'late_minutes' => 0,
                'work_hours' => null,
                'status' => 'Masuk',
            ]);

            LeaveRequest::query()->create([
                'employee_id' => $employee->id,
                'leave_type_id' => null,
                'start_date' => '2026-05-27',
                'end_date' => '2026-05-27',
                'total_days' => 1,
                'reason' => 'Sick leave approved',
                'status' => 'approved',
                'is_active' => true,
            ]);
            LeaveRequest::query()->create([
                'employee_id' => $employee->id,
                'leave_type_id' => null,
                'start_date' => '2026-05-28',
                'end_date' => '2026-05-28',
                'total_days' => 1,
                'reason' => 'Pending leave',
                'status' => 'pending',
                'is_active' => true,
            ]);

            $this->actingAs($user);

            $view = view('attendance.layouts.profile-header');
            app(AttendanceProfileComposer::class)->compose($view);
            $data = $view->getData();

            $this->assertSame('staff', $data['profileStatsMode']);
            $this->assertSame(1, $data['profileLateInCount']);
            $this->assertSame(1, $data['profileLeavesAndSickCount']);
            $this->assertSame(75, $data['profileWeeklyAttendancePercent']);
            $this->assertSame(67, $data['profileWeeklyOnTimePercent']);
            $this->assertIsArray($data['profileMonthlyAttendanceLabels']);
            $this->assertIsArray($data['profileMonthlyAttendanceSeries']);
            $this->assertCount(12, $data['profileMonthlyAttendanceLabels']);
            $this->assertCount(12, $data['profileMonthlyAttendanceSeries']);
            $this->assertSame(15.0, (float) $data['profileMonthlyAttendanceDelta']);
        } finally {
            Carbon::setTestNow();
        }
    }
}
