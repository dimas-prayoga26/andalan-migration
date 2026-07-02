<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAttendance\AttendanceLeaveController;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminAttendanceLeaveOverviewStatsTest extends TestCase
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

    public function test_leave_overview_stats_are_scoped_to_current_user_company(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 09:00:00', 'Asia/Jakarta'));

        $currentCompany = Company::query()->create(['name' => 'RNB']);
        $otherCompany = Company::query()->create(['name' => 'Other Company']);
        $adminUser = $this->createEmployeeUser($currentCompany);
        $activeEmployee = $this->createEmployeeUser($currentCompany)->employee;
        $inactiveEmployee = $this->createEmployeeUser($currentCompany, 'Inactive')->employee;
        $otherCompanyEmployee = $this->createEmployeeUser($otherCompany)->employee;

        $annualLeave = $this->createLeaveType('ANNUAL', 'Cuti Tahunan', 'monthly', 1);
        $sickLeave = $this->createLeaveType('SICK', 'Sakit');
        $specialLeave = $this->createLeaveType('SPECIAL', 'Cuti Khusus');
        $unpaidLeave = $this->createLeaveType('UNPAID', 'Unpaid Leave', 'none');

        $pendingLeaveRequest = $this->createLeaveRequest($activeEmployee, $annualLeave, 'pending', '2026-06-24', '2026-06-24');
        $this->createLeaveRequest($activeEmployee, $sickLeave, 'approved', '2026-06-30', '2026-06-30');
        $this->createLeaveRequest($activeEmployee, $specialLeave, 'rejected', '2026-06-10', '2026-06-10');
        $this->createLeaveRequest($activeEmployee, $unpaidLeave, 'refused', '2026-06-11', '2026-06-11');
        $this->createLeaveRequest($inactiveEmployee, $annualLeave, 'pending', '2026-06-25', '2026-06-25');
        $this->createLeaveRequest($otherCompanyEmployee, $annualLeave, 'pending', '2026-06-24', '2026-06-24');
        $this->createSupervisorReviewHistory($pendingLeaveRequest, 'approved');

        $request = Request::create('/admin-attendance/leave');
        $request->setUserResolver(static fn (): User => $adminUser);

        $view = app(AttendanceLeaveController::class)->index($request);
        $stats = $view->getData()['leaveOverviewStats'];

        $this->assertSame('admin_attendance.leave.index', $view->name());
        $this->assertSame(1, $stats['pending']);
        $this->assertSame(2, $stats['rejected']);
        $this->assertSame(1, $stats['approved']);
        $this->assertSame(1, $stats['this_week']);
        $this->assertSame(1, $stats['next_week']);
        $this->assertSame(1, $stats['annual']);
        $this->assertSame(1, $stats['sick']);
        $this->assertSame(1, $stats['special']);
        $this->assertSame(1, $stats['unpaid']);
    }

    public function test_pending_datatable_only_lists_supervisor_approved_pending_leave_requests(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 09:00:00', 'Asia/Jakarta'));

        $company = Company::query()->create(['name' => 'RNB']);
        $adminUser = $this->createEmployeeUser($company);
        $employee = $this->createEmployeeUser($company)->employee;
        $annualLeave = $this->createLeaveType('ANNUAL', 'Cuti Tahunan', 'monthly', 1);

        $supervisorApprovedPendingRequest = $this->createLeaveRequest($employee, $annualLeave, 'pending', '2026-06-24', '2026-06-24');
        $pendingWithoutSupervisorApproval = $this->createLeaveRequest($employee, $annualLeave, 'pending', '2026-06-25', '2026-06-25');
        $pendingWithPendingSupervisorReview = $this->createLeaveRequest($employee, $annualLeave, 'pending', '2026-06-26', '2026-06-26');
        $this->createSupervisorReviewHistory($supervisorApprovedPendingRequest, 'approved');
        $this->createSupervisorReviewHistory($pendingWithPendingSupervisorReview, 'pending');

        $request = Request::create('/admin-attendance/leave/pending-datatable', 'GET', [
            'month' => 6,
            'year' => 2026,
        ]);
        $request->setUserResolver(static fn (): User => $adminUser);

        $response = app(AttendanceLeaveController::class)->pendingDatatable($request);
        $data = $response->getData(true)['data'];

        $this->assertCount(1, $data);
        $this->assertSame('24 Jun 2026 (1 day)', $data[0]['date']);
        $this->assertSame('Annual', $data[0]['type']);
    }

    private function createEmployeeUser(Company $company, string $employeeStatus = 'Active'): User
    {
        $user = User::query()->create([
            'username' => 'user_'.uniqid(),
            'email' => uniqid('user_', true).'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => $employeeStatus,
        ]);
        $deployment = EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => $company->id,
            'join_date' => '2026-01-01',
            'status' => $employeeStatus,
        ]);

        $employee->setRelation('deployment', $deployment);
        $user->setRelation('employee', $employee);

        return $user;
    }

    private function createLeaveType(
        string $code,
        string $name,
        string $accrualMethod = 'yearly',
        int $monthlyAccrualRate = 0
    ): LeaveType {
        return LeaveType::query()->create([
            'code' => $code,
            'name' => $name,
            'accrual_method' => $accrualMethod,
            'monthly_accrual_rate' => $monthlyAccrualRate,
            'is_encashable' => false,
            'is_active' => true,
        ]);
    }

    private function createLeaveRequest(
        Employee $employee,
        LeaveType $leaveType,
        string $status,
        string $startDate,
        string $endDate
    ): LeaveRequest {
        return LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => 1,
            'reason' => 'Testing leave overview stats.',
            'is_active' => true,
            'status' => $status,
        ]);
    }

    private function createSupervisorReviewHistory(LeaveRequest $leaveRequest, string $toStatus): LeaveRequestHistory
    {
        return LeaveRequestHistory::query()->create([
            'leave_request_id' => $leaveRequest->id,
            'event_type' => 'supervisor_review',
            'title' => 'Supervisor Review',
            'from_status' => 'pending',
            'to_status' => $toStatus,
            'happened_at' => now('Asia/Jakarta'),
        ]);
    }
}
