<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAttendance\AttendanceLeaveController;
use App\Models\AttendanceHoliday;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\LeaveBalanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminAttendanceLeaveBalanceSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is not available.');
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_final_annual_leave_approval_syncs_the_leave_balance(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-24 09:00:00', 'Asia/Jakarta'));

        [$adminUser, $employee] = $this->createCompanyEmployees();
        $annualLeave = $this->createAnnualLeaveType();
        $leaveBalance = LeaveBalance::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $annualLeave->id,
            'period_year' => 2026,
            'earned_quota' => 5,
            'used_quota' => 0,
            'remaining_quota' => 5,
        ]);
        $leaveRequest = LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $annualLeave->id,
            'start_date' => '2026-06-26',
            'end_date' => '2026-06-26',
            'total_days' => 1,
            'reason' => 'Annual leave approval test.',
            'status' => 'pending',
            'is_active' => true,
        ]);
        LeaveRequestHistory::query()->create([
            'leave_request_id' => $leaveRequest->id,
            'actor_user_id' => $adminUser->id,
            'event_type' => 'supervisor_review',
            'title' => 'Supervisor Review',
            'from_status' => 'pending',
            'to_status' => 'approved',
            'happened_at' => now('Asia/Jakarta'),
        ]);

        $request = Request::create('/admin-attendance/leave/detail/'.$leaveRequest->id.'/approval', 'PUT', [
            'status' => 'approved',
        ]);
        $request->setUserResolver(static fn (): User => $adminUser);

        app(AttendanceLeaveController::class)->updateApproval($request, $leaveRequest->id);

        $this->assertSame('approved', $leaveRequest->refresh()->status);
        $this->assertSame('1.00', $leaveBalance->refresh()->used_quota);
        $this->assertSame('4.00', $leaveBalance->remaining_quota);
    }

    public function test_administrator_can_finalize_their_own_leave_request_after_supervisor_approval(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-24 09:00:00', 'Asia/Jakarta'));

        [$adminUser, , $adminEmployee] = $this->createCompanyEmployees();
        $annualLeave = $this->createAnnualLeaveType();
        $leaveRequest = LeaveRequest::query()->create([
            'employee_id' => $adminEmployee->id,
            'leave_type_id' => $annualLeave->id,
            'start_date' => '2026-06-26',
            'end_date' => '2026-06-26',
            'total_days' => 1,
            'reason' => 'Administrator self approval test.',
            'status' => 'pending',
            'is_active' => true,
        ]);
        LeaveRequestHistory::query()->create([
            'leave_request_id' => $leaveRequest->id,
            'actor_user_id' => $adminUser->id,
            'event_type' => 'supervisor_review',
            'title' => 'Supervisor Review',
            'from_status' => 'pending',
            'to_status' => 'approved',
            'happened_at' => now('Asia/Jakarta'),
        ]);

        $request = Request::create('/admin-attendance/leave/detail/'.$leaveRequest->id.'/approval', 'PUT', [
            'status' => 'approved',
        ]);
        $request->setUserResolver(static fn (): User => $adminUser);

        app(AttendanceLeaveController::class)->updateApproval($request, $leaveRequest->id);

        $this->assertSame('approved', $leaveRequest->refresh()->status);
        $this->assertDatabaseHas('leave_request_histories', [
            'leave_request_id' => $leaveRequest->id,
            'actor_user_id' => $adminUser->id,
            'event_type' => 'hr_verification',
            'to_status' => 'approved',
        ]);
    }

    public function test_leave_balance_seeder_syncs_approved_annual_leave_usage(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-24 09:00:00', 'Asia/Jakarta'));

        [, $employee] = $this->createCompanyEmployees();
        $annualLeave = $this->createAnnualLeaveType();
        foreach (range(1, 8) as $day) {
            AttendanceHoliday::query()->create([
                'date' => Carbon::create(2026, 1, $day)->toDateString(),
                'name' => 'Cuti Bersama '.$day,
                'type' => 2,
            ]);
        }
        LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $annualLeave->id,
            'start_date' => '2026-06-26',
            'end_date' => '2026-06-26',
            'total_days' => 1,
            'reason' => 'Approved annual leave seed test.',
            'status' => 'approved',
            'is_active' => true,
        ]);

        app(LeaveBalanceSeeder::class)->run();

        $leaveBalance = LeaveBalance::query()
            ->where('employee_id', $employee->id)
            ->where('leave_type_id', $annualLeave->id)
            ->where('period_year', 2026)
            ->firstOrFail();

        $this->assertSame('4.00', $leaveBalance->earned_quota);
        $this->assertSame('1.00', $leaveBalance->used_quota);
        $this->assertSame('3.00', $leaveBalance->remaining_quota);
    }

    /**
     * @return array{0: User, 1: Employee, 2: Employee}
     */
    private function createCompanyEmployees(): array
    {
        $company = Company::query()->create(['name' => 'RNB']);
        $adminUser = User::query()->create([
            'username' => 'admin_'.uniqid(),
            'email' => uniqid('admin_', true).'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $adminEmployee = Employee::query()->create([
            'user_id' => $adminUser->id,
            'status' => 'Active',
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $adminEmployee->id,
            'current_company_id' => $company->id,
            'join_date' => '2025-01-01',
            'status' => 'Active',
        ]);
        $employeeUser = User::query()->create([
            'username' => 'staff_'.uniqid(),
            'email' => uniqid('staff_', true).'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create([
            'user_id' => $employeeUser->id,
            'status' => 'Active',
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => $company->id,
            'join_date' => '2025-01-01',
            'status' => 'Active',
        ]);

        return [$adminUser, $employee, $adminEmployee];
    }

    private function createAnnualLeaveType(): LeaveType
    {
        return LeaveType::query()->create([
            'code' => 'ANNUAL',
            'name' => 'Cuti Tahunan',
            'accrual_method' => 'monthly',
            'monthly_accrual_rate' => 1,
            'is_encashable' => false,
            'is_active' => true,
        ]);
    }
}
