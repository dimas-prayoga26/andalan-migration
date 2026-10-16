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

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_leave_overview_stats_include_active_employees_from_every_company(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 09:00:00', 'Asia/Jakarta'));

        $currentCompany = Company::query()->create(['name' => 'RNB']);
        $otherCompany = Company::query()->create(['name' => 'TMS']);
        $adminUser = $this->createEmployeeUser($currentCompany);
        $activeEmployee = $this->createEmployeeUser($currentCompany)->employee;
        $inactiveEmployee = $this->createEmployeeUser($currentCompany, 'Inactive')->employee;
        $otherCompanyEmployee = $this->createEmployeeUser($otherCompany)->employee;

        $annualLeave = $this->createLeaveType('ANNUAL', 'Cuti Tahunan', 'monthly', 1);
        $sickLeave = $this->createLeaveType('SICK', 'Sakit');
        $specialLeave = $this->createLeaveType('SPECIAL', 'Cuti Khusus');
        $unpaidLeave = $this->createLeaveType('UNPAID', 'Unpaid Leave', 'none');

        $leaveRequests = [
            $this->createLeaveRequest($activeEmployee, $annualLeave, 'pending', '2026-06-24', '2026-06-24'),
            $this->createLeaveRequest($activeEmployee, $sickLeave, 'approved', '2026-06-30', '2026-06-30'),
            $this->createLeaveRequest($activeEmployee, $specialLeave, 'rejected', '2026-06-10', '2026-06-10'),
            $this->createLeaveRequest($activeEmployee, $unpaidLeave, 'refused', '2026-06-11', '2026-06-11'),
            $this->createLeaveRequest($inactiveEmployee, $annualLeave, 'pending', '2026-06-25', '2026-06-25'),
            $this->createLeaveRequest($otherCompanyEmployee, $sickLeave, 'pending', '2026-06-24', '2026-06-24'),
        ];
        foreach ($leaveRequests as $leaveRequest) {
            $this->createSupervisorReviewHistory($leaveRequest, 'approved');
        }

        $request = Request::create('/admin-attendance/leave');
        $request->setUserResolver(static fn (): User => $adminUser);

        $view = app(AttendanceLeaveController::class)->index($request);
        $stats = $view->getData()['leaveOverviewStats'];

        $this->assertSame('admin_attendance.leave.index', $view->name());
        $this->assertSame(2, $stats['pending']);
        $this->assertSame(2, $stats['rejected']);
        $this->assertSame(1, $stats['approved']);
        $this->assertSame(2, $stats['this_week']);
        $this->assertSame(1, $stats['next_week']);
        $this->assertSame(1, $stats['annual']);
        $this->assertSame(2, $stats['sick']);
        $this->assertSame(1, $stats['special']);
        $this->assertSame(1, $stats['unpaid']);
    }

    public function test_pending_datatable_lists_leave_requests_from_other_companies(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-16 09:00:00', 'Asia/Jakarta'));

        $adminUser = $this->createEmployeeUser(Company::query()->create(['name' => 'Niskala']));
        $otherCompanyUser = $this->createEmployeeUser(Company::query()->create(['name' => 'TMS']));
        $sickLeave = $this->createLeaveType('SICK', 'Sakit');
        $leaveRequest = $this->createLeaveRequest($otherCompanyUser->employee, $sickLeave, 'pending', '2026-10-15', '2026-10-15');
        $this->createSupervisorReviewHistory($leaveRequest, 'approved');

        $request = Request::create('/admin-attendance/leave/pending-datatable', 'GET', [
            'month' => 10,
            'year' => 2026,
        ]);
        $request->setUserResolver(static fn (): User => $adminUser);

        $data = app(AttendanceLeaveController::class)->pendingDatatable($request)->getData(true)['data'];

        $this->assertCount(1, $data);
        $this->assertSame($otherCompanyUser->username, $data[0]['name']);
        $this->assertSame('Sick', $data[0]['type']);
        $this->assertSame(route('admin-attendance.leave.detail', ['uid' => $leaveRequest->id]), $data[0]['detail_url']);
    }

    public function test_admin_can_open_and_finalize_leave_request_from_another_company(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-16 09:00:00', 'Asia/Jakarta'));

        $adminUser = $this->createEmployeeUser(Company::query()->create(['name' => 'Niskala']));
        $otherCompanyUser = $this->createEmployeeUser(Company::query()->create(['name' => 'TMS']));
        $sickLeave = $this->createLeaveType('SICK', 'Sakit');
        $leaveRequest = $this->createLeaveRequest($otherCompanyUser->employee, $sickLeave, 'pending', '2026-10-15', '2026-10-15');
        $this->createSupervisorReviewHistory($leaveRequest, 'approved');

        $detailRequest = Request::create('/admin-attendance/leave/detail/'.$leaveRequest->id);
        $detailRequest->setUserResolver(static fn (): User => $adminUser);
        $detailView = app(AttendanceLeaveController::class)->detail($detailRequest, $leaveRequest->id);

        $this->assertTrue($detailView->getData()['leaveApproval']['can_finalize']);

        $approvalRequest = Request::create('/admin-attendance/leave/detail/'.$leaveRequest->id.'/approval', 'PUT', [
            'status' => 'approved',
        ]);
        $approvalRequest->setUserResolver(static fn (): User => $adminUser);
        app(AttendanceLeaveController::class)->updateApproval($approvalRequest, $leaveRequest->id);

        $leaveRequest->refresh();
        $this->assertSame('approved', $leaveRequest->status);
        $this->assertSame((string) $adminUser->id, (string) $leaveRequest->approved_by);
        $this->assertSame(1, $leaveRequest->histories()->where('event_type', 'hr_verification')->count());
        $this->assertDatabaseHas('leave_request_histories', [
            'leave_request_id' => $leaveRequest->id,
            'event_type' => 'hr_verification',
            'actor_user_id' => $adminUser->id,
            'to_status' => 'approved',
        ]);
    }

    public function test_hr_rejection_records_the_rejecting_admin_on_hr_verification(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-16 09:00:00', 'Asia/Jakarta'));

        $company = Company::query()->create(['name' => 'RNB']);
        $adminUser = $this->createEmployeeUser($company);
        $employee = $this->createEmployeeUser($company)->employee;
        $sickLeave = $this->createLeaveType('SICK', 'Sakit');
        $leaveRequest = $this->createLeaveRequest($employee, $sickLeave, 'pending', '2026-10-15', '2026-10-15');
        $this->createSupervisorReviewHistory($leaveRequest, 'approved');

        $this->assertDatabaseHas('leave_request_histories', [
            'leave_request_id' => $leaveRequest->id,
            'event_type' => 'hr_verification',
            'actor_user_id' => null,
            'to_status' => 'pending',
        ]);

        $rejectionRequest = Request::create('/admin-attendance/leave/detail/'.$leaveRequest->id.'/approval', 'PUT', [
            'status' => 'rejected',
        ]);
        $rejectionRequest->setUserResolver(static fn (): User => $adminUser);
        app(AttendanceLeaveController::class)->updateApproval($rejectionRequest, $leaveRequest->id);

        $leaveRequest->refresh();
        $this->assertSame('rejected', $leaveRequest->status);
        $this->assertNull($leaveRequest->approved_by);
        $this->assertDatabaseHas('leave_request_histories', [
            'leave_request_id' => $leaveRequest->id,
            'event_type' => 'hr_verification',
            'actor_user_id' => $adminUser->id,
            'to_status' => 'rejected',
        ]);
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
