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

    public function test_leave_overview_stats_follow_selected_period(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 09:00:00', 'Asia/Jakarta'));

        $company = Company::query()->create(['name' => 'RNB']);
        $adminUser = $this->createEmployeeUser($company);
        $employee = $this->createEmployeeUser($company)->employee;
        $sickLeave = $this->createLeaveType('SICK', 'Sakit');

        foreach ([
            ['pending', '2026-10-21'],
            ['approved', '2026-10-15'],
            ['approved', '2026-09-10'],
            ['pending', '2026-09-11'],
            ['rejected', '2026-09-12'],
        ] as [$status, $leaveDate]) {
            $this->createSupervisorReviewHistory(
                $this->createLeaveRequest($employee, $sickLeave, $status, $leaveDate, $leaveDate),
                'approved'
            );
        }

        $statsFor = function (array $query) use ($adminUser): array {
            $request = Request::create('/admin-attendance/leave', 'GET', $query);
            $request->setUserResolver(static fn (): User => $adminUser);

            return app(AttendanceLeaveController::class)->index($request)->getData()['leaveOverviewStats'];
        };

        $octoberStats = $statsFor(['month' => 10, 'year' => 2026]);

        $this->assertSame(1, $octoberStats['pending']);
        $this->assertSame(1, $octoberStats['approved']);
        $this->assertSame(0, $octoberStats['rejected']);
        $this->assertSame(2, $octoberStats['sick']);

        $septemberStats = $statsFor(['month' => 9, 'year' => 2026]);

        $this->assertSame(1, $septemberStats['pending']);
        $this->assertSame(1, $septemberStats['approved']);
        $this->assertSame(1, $septemberStats['rejected']);
        $this->assertSame(3, $septemberStats['sick']);
    }

    public function test_admin_leave_cards_show_steps_two_to_four_and_lock_cards_before_hr_verification(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 09:00:00', 'Asia/Jakarta'));

        $company = Company::query()->create(['name' => 'RNB']);
        $adminUser = $this->createEmployeeUser($company);
        $employee = $this->createEmployeeUser($company)->employee;
        $sickLeave = $this->createLeaveType('SICK', 'Sakit');

        foreach ([
            ['pending', 'pending', '2026-10-01'],
            ['rejected', 'rejected', '2026-10-02'],
            ['pending', 'approved', '2026-10-03'],
            ['approved', 'approved', '2026-10-04'],
            ['rejected', 'approved', '2026-10-05'],
            ['pending', 'approved', '2026-09-30'],
        ] as [$status, $supervisorReviewStatus, $leaveDate]) {
            $this->createSupervisorReviewHistory(
                $this->createLeaveRequest($employee, $sickLeave, $status, $leaveDate, $leaveDate),
                $supervisorReviewStatus
            );
        }
        $this->createLeaveRequest($employee, $sickLeave, 'pending', '2026-10-06', '2026-10-06');

        $request = Request::create('/admin-attendance/leave', 'GET', ['month' => 10, 'year' => 2026]);
        $request->setUserResolver(static fn (): User => $adminUser);
        $leaveCards = app(AttendanceLeaveController::class)->index($request)->getData()['leaveCards'];

        $this->assertSame(
            ['05 Oct 2026 (1 day)', '04 Oct 2026 (1 day)', '03 Oct 2026 (1 day)', '02 Oct 2026 (1 day)', '01 Oct 2026 (1 day)'],
            $leaveCards->pluck('date_label')->all(),
        );
        $this->assertSame(
            [
                ['title' => 'Final Decision', 'status' => 'Rejected', 'color' => 'danger'],
                ['title' => 'Final Decision', 'status' => 'Approved', 'color' => 'success'],
                ['title' => 'HR Verification', 'status' => 'Pending', 'color' => 'warning'],
                ['title' => 'Supervisor Review', 'status' => 'Rejected', 'color' => 'danger'],
                ['title' => 'Supervisor Review', 'status' => 'Pending', 'color' => 'warning'],
            ],
            $leaveCards->pluck('current_step')->all(),
        );
        $this->assertSame(
            [null, null, null, 'Ditolak PIC', 'Belum Disetujui PIC'],
            $leaveCards->map(fn (array $leaveCard): ?string => $leaveCard['locked_notice']['title'] ?? null)->all(),
        );
        $this->assertSame(
            'Pengajuan cuti ini belum disetujui oleh PIC / Supervisor, sehingga belum bisa diproses.',
            $leaveCards->last()['locked_notice']['message'],
        );
        $this->assertSame(
            'Pengajuan cuti ini ditolak oleh PIC / Supervisor, sehingga tidak diteruskan.',
            $leaveCards->get(3)['locked_notice']['message'],
        );
    }

    public function test_admin_leave_view_uses_single_period_filter_above_summary_badges(): void
    {
        $view = (string) file_get_contents(resource_path('views/admin_attendance/leave/index.blade.php'));

        $this->assertSame(1, substr_count($view, '<form'));
        $this->assertSame(1, substr_count($view, 'name="month"'));
        $this->assertSame(1, substr_count($view, 'name="year"'));
        $this->assertStringNotContainsString('adminLeavePendingMonth', $view);
        $this->assertStringNotContainsString('adminLeaveApprovedMonth', $view);
        $this->assertStringContainsString("requestData.month = $('#adminLeavePeriodMonth').val();", $view);
        $this->assertStringContainsString("requestData.year = $('#adminLeavePeriodYear').val();", $view);
        $this->assertLessThan(
            strpos($view, '{{ $leaveOverviewStats[\'pending\'] }}'),
            strpos($view, 'admin-leave-period-filter'),
        );
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
