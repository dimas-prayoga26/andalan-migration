<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceHoliday;
use App\Models\AttendanceOvertime;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\LeaveRequest;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\View\Composers\AttendanceProfileComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceProfileComposerStaffStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is not available.');
        }

        parent::setUp();
    }

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
            $lateAttendance = Attendance::query()->create([
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

            AttendanceException::query()->create([
                'attendance_id' => $lateAttendance->id,
                'employee_id' => $employee->id,
                'exception_date' => '2026-05-27',
                'type' => 'late_arrival',
                'note' => 'Izin datang terlambat',
                'from_time' => '08:00:00',
                'to_time' => '08:10:00',
                'status' => 'approved',
            ]);

            foreach ([
                ['18:00:00', '06:00:00'],
                ['18:00:00', '06:00:00'],
                ['18:00:00', '06:00:00'],
            ] as [$actualStartTime, $actualEndTime]) {
                AttendanceOvertime::query()->create([
                    'employee_id' => $employee->id,
                    'overtime_date' => '2026-05-29',
                    'planned_start_time' => '18:00:00',
                    'planned_end_time' => '06:00:00',
                    'actual_start_time' => $actualStartTime,
                    'actual_end_time' => $actualEndTime,
                    'instruction' => 'Monthly overtime rate test',
                    'status' => 'completed',
                ]);
            }

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

            $view = view('staff_attendance.layouts.profile-header');
            app(AttendanceProfileComposer::class)->compose($view);
            $data = $view->getData();

            $this->assertSame('staff', $data['profileStatsMode']);
            $this->assertSame(1, $data['profileLateInCount']);
            $this->assertSame(1, $data['profileLeavesAndSickCount']);
            $this->assertSame(15, $data['profileAttendanceRatePercent']);
            $this->assertSame(10, $data['profileOnTimeRatePercent']);
            $this->assertSame(5, $data['profileLatenessRatePercent']);
            $this->assertSame(50, $data['profileOvertimeRatePercent']);
            $this->assertSame(75, $data['profileWeeklyAttendancePercent']);
            $this->assertSame(67, $data['profileWeeklyOnTimePercent']);
            $this->assertSame([2, 1, 1, 1], $data['profileAttendanceOverviewSeries']);
            $this->assertSame(15, $data['profileAttendanceProgressPercent']);
            $this->assertSame(67, $data['profileProgressOnTimePercent']);
            $this->assertSame(33, $data['profileProgressLatePercent']);
            $this->assertSame(9.0, (float) $data['profileWeeklyRequiredHours']);
            $this->assertSame(23, $data['profileWeeklyRequiredHoursPercent']);
            $this->assertSame(36.0, (float) $data['profileWeeklyOvertimeHours']);
            $this->assertSame(100, $data['profileWeeklyOvertimeHoursPercent']);
            $this->assertIsArray($data['profileMonthlyAttendanceLabels']);
            $this->assertIsArray($data['profileMonthlyAttendanceSeries']);
            $this->assertCount(12, $data['profileMonthlyAttendanceLabels']);
            $this->assertCount(12, $data['profileMonthlyAttendanceSeries']);
            $this->assertSame(15.0, (float) $data['profileMonthlyAttendanceDelta']);
            $this->assertSame(2026, $data['profileYearChartYear']);
            $this->assertSame(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], $data['profileYearMonthLabels']);
            $this->assertCount(12, $data['profileYearAttendanceOnTimeSeries']);
            $this->assertCount(12, $data['profileYearAttendanceLateSeries']);
            $this->assertCount(12, $data['profileYearAttendanceLeaveSeries']);
            $this->assertCount(12, $data['profileYearLeaveSeries']);
            $this->assertCount(12, $data['profileYearSickSeries']);
            $this->assertCount(12, $data['profileYearBusinessTripSeries']);
            $this->assertCount(12, $data['profileYearOvertimeHoursSeries']);
            $this->assertSame(2, $data['profileYearAttendanceOnTimeSeries'][4]);
            $this->assertSame(1, $data['profileYearAttendanceLateSeries'][4]);
            $this->assertSame(1, $data['profileYearAttendanceLeaveSeries'][4]);
            $this->assertSame(1, $data['profileYearLeaveSeries'][4]);
            $this->assertSame(0, $data['profileYearSickSeries'][4]);
            $this->assertSame(0, $data['profileYearBusinessTripSeries'][4]);
            $this->assertSame(36.0, (float) $data['profileYearOvertimeHoursSeries'][4]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_chief_operating_officer_uses_staff_profile_cards_with_global_management_scope(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-02 09:00:00', 'Asia/Jakarta'));

        try {
            $rnbCompany = Company::query()->create(['name' => 'RNB']);
            $otherCompany = Company::query()->create(['name' => 'Other Company']);
            $cooPosition = Position::query()->create(['name' => 'Chief Operating Officer']);
            $staffPosition = Position::query()->create(['name' => 'Staff']);

            $cooUser = User::query()->create([
                'username' => 'lukman',
                'email' => 'lukman@example.test',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);

            $cooUser->assignRole(Role::query()->firstOrCreate([
                'name' => 'Board of Directors',
                'guard_name' => 'web',
            ]));

            $cooEmployee = Employee::query()->create([
                'user_id' => $cooUser->id,
                'status' => 'Active',
            ]);

            EmployeeDeployment::query()->create([
                'employee_id' => $cooEmployee->id,
                'current_company_id' => $rnbCompany->id,
                'current_position_id' => $cooPosition->id,
                'status' => 'Active',
            ]);

            $this->createEmployeeForAttendanceProfileScope($rnbCompany, $staffPosition);
            $this->createEmployeeForAttendanceProfileScope($otherCompany, $staffPosition);

            Attendance::query()->create([
                'employee_id' => $cooEmployee->id,
                'date' => '2026-07-02',
                'clock_in' => '08:00:00',
                'clock_out' => null,
                'late_minutes' => 0,
                'work_hours' => null,
                'status' => 'Masuk',
            ]);

            $this->actingAs($cooUser);

            $view = view('staff_attendance.layouts.profile-header');
            app(AttendanceProfileComposer::class)->compose($view);
            $data = $view->getData();

            $this->assertSame('staff', $data['profileStatsMode']);
            $this->assertSame(3, $data['managementTotalEmployeesCount']);
            $this->assertSame(1, $data['managementPresentTodayCount']);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createEmployeeForAttendanceProfileScope(Company $company, Position $position): Employee
    {
        $user = User::query()->create([
            'username' => 'scope_user_'.Str::random(8),
            'email' => Str::random(8).'@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);

        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => $company->id,
            'current_position_id' => $position->id,
            'status' => 'Active',
        ]);

        return $employee;
    }
}
