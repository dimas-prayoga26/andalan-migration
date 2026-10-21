<?php

namespace Tests\Feature;

use App\Http\Controllers\PicAttendance\PicAttendanceLeaveController;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeePicAssignment;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class PicAttendanceLeavePeriodFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $picUser;

    private Employee $assignedStaff;

    private Employee $outsideStaff;

    private LeaveType $sickLeave;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-21 09:00:00', 'Asia/Jakarta'));

        $company = Company::query()->create(['name' => 'RNB']);
        $this->picUser = $this->createEmployeeUser($company);
        $this->assignedStaff = $this->createEmployeeUser($company)->employee;
        $this->outsideStaff = $this->createEmployeeUser($company)->employee;
        $this->sickLeave = LeaveType::query()->create([
            'code' => 'SICK',
            'name' => 'Sakit',
            'accrual_method' => 'yearly',
            'monthly_accrual_rate' => 0,
            'is_encashable' => false,
            'is_active' => true,
        ]);

        EmployeePicAssignment::query()->create([
            'id' => (string) Str::uuid(),
            'supervisor_employee_id' => $this->picUser->employee->id,
            'staff_employee_id' => $this->assignedStaff->id,
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_summary_badges_follow_selected_period_and_only_count_pic_staff(): void
    {
        $this->createLeaveRequest($this->assignedStaff, '2026-10-05', 'pending', 'pending');
        $this->createLeaveRequest($this->assignedStaff, '2026-10-15', 'approved', 'approved');
        $this->createLeaveRequest($this->assignedStaff, '2026-09-10', 'pending', 'approved');
        $this->createLeaveRequest($this->outsideStaff, '2026-10-16', 'approved', 'approved');
        $this->createLeaveRequest($this->outsideStaff, '2026-10-17', 'pending', 'pending');

        $octoberStats = $this->picLeaveIndexData(['month' => 10, 'year' => 2026])['leaveOverviewStats'];

        $this->assertSame(1, $octoberStats['pending']);
        $this->assertSame(1, $octoberStats['approved']);
        $this->assertSame(1, $octoberStats['sick']);
        $this->assertSame(0, $octoberStats['rejected']);

        $septemberStats = $this->picLeaveIndexData(['month' => 9, 'year' => 2026])['leaveOverviewStats'];

        $this->assertSame(0, $septemberStats['pending']);
        $this->assertSame(1, $septemberStats['approved']);
        $this->assertSame(1, $septemberStats['sick']);
    }

    public function test_pending_only_counts_leave_requests_sitting_at_supervisor_review_step(): void
    {
        $this->createLeaveRequest($this->assignedStaff, '2026-10-05', 'pending', 'pending');
        $this->createLeaveRequest($this->assignedStaff, '2026-10-06', 'pending', null);
        $this->createLeaveRequest($this->assignedStaff, '2026-10-07', 'rejected', 'rejected');

        $viewData = $this->picLeaveIndexData(['month' => 10, 'year' => 2026]);

        $this->assertSame(1, $viewData['leaveOverviewStats']['pending']);
        $this->assertSame(1, $viewData['leaveOverviewStats']['rejected']);
        $this->assertCount(2, $viewData['leaveCards']);
        $this->assertSame(1, $this->datatableRowCount('pendingDatatable', ['month' => 10, 'year' => 2026]));
    }

    public function test_leave_cards_show_pending_approved_and_rejected_requests_from_step_two_to_four(): void
    {
        $this->createLeaveRequest($this->assignedStaff, '2026-10-01', 'pending', 'pending');
        $this->createLeaveRequest($this->assignedStaff, '2026-10-02', 'pending', 'approved');
        $this->createLeaveRequest($this->assignedStaff, '2026-10-03', 'approved', 'approved');
        $this->createLeaveRequest($this->assignedStaff, '2026-10-04', 'rejected', 'rejected');
        $this->createLeaveRequest($this->assignedStaff, '2026-10-05', 'rejected', 'approved');
        $this->createLeaveRequest($this->outsideStaff, '2026-10-06', 'approved', 'approved');

        foreach (['2026-10-20', '2026-10-21', '2026-10-22', '2026-10-23'] as $leaveDate) {
            $this->createLeaveRequest($this->assignedStaff, $leaveDate, 'pending', 'pending');
        }

        $leaveCards = $this->picLeaveIndexData(['month' => 10, 'year' => 2026])['leaveCards'];

        $this->assertCount(9, $leaveCards);
        $this->assertSame(
            ['23 Oct 2026 (1 day)', '22 Oct 2026 (1 day)', '21 Oct 2026 (1 day)', '20 Oct 2026 (1 day)', '05 Oct 2026 (1 day)'],
            $leaveCards->take(5)->pluck('date_label')->all(),
        );
        $this->assertSame(
            [
                ['title' => 'Final Decision', 'status' => 'Rejected', 'color' => 'danger'],
                ['title' => 'Supervisor Review', 'status' => 'Rejected', 'color' => 'danger'],
                ['title' => 'Final Decision', 'status' => 'Approved', 'color' => 'success'],
                ['title' => 'HR Verification', 'status' => 'Pending', 'color' => 'warning'],
                ['title' => 'Supervisor Review', 'status' => 'Pending', 'color' => 'warning'],
            ],
            $leaveCards->slice(4)->pluck('current_step')->values()->all(),
        );
    }

    public function test_single_period_filter_drives_tables_and_cards(): void
    {
        $this->createLeaveRequest($this->assignedStaff, '2026-10-05', 'pending', 'pending');
        $this->createLeaveRequest($this->assignedStaff, '2026-10-15', 'approved', 'approved');
        $this->createLeaveRequest($this->assignedStaff, '2026-09-08', 'pending', 'pending');

        $septemberData = $this->picLeaveIndexData(['month' => 9, 'year' => 2026, 'card_month' => 10]);

        $this->assertSame(9, $septemberData['leaveSelectedMonth']);
        $this->assertCount(1, $septemberData['leaveCards']);
        $this->assertArrayNotHasKey('leaveCardSelectedMonth', $septemberData);
        $this->assertSame(1, $this->datatableRowCount('pendingDatatable', ['month' => 9, 'year' => 2026]));
        $this->assertSame(0, $this->datatableRowCount('approvedDatatable', ['month' => 9, 'year' => 2026]));
        $this->assertSame(1, $this->datatableRowCount('approvedDatatable', ['month' => 10, 'year' => 2026]));
    }

    public function test_empty_leave_card_state_uses_generic_message(): void
    {
        foreach ([
            resource_path('views/pic_attendance/leave/index.blade.php'),
            resource_path('views/admin_attendance/leave/index.blade.php'),
        ] as $viewPath) {
            $view = (string) file_get_contents($viewPath);

            $this->assertStringContainsString('No leave request data available.</div>', $view);
            $this->assertStringNotContainsString('No pending leave request data available.', $view);
        }
    }

    /**
     * @param  array<string, int>  $query
     * @return array<string, mixed>
     */
    private function picLeaveIndexData(array $query): array
    {
        return app(PicAttendanceLeaveController::class)
            ->index($this->picRequest('/pic-attendance/leave', $query))
            ->getData();
    }

    /**
     * @param  array<string, int>  $query
     */
    private function datatableRowCount(string $method, array $query): int
    {
        $response = app(PicAttendanceLeaveController::class)
            ->{$method}($this->picRequest('/pic-attendance/leave/datatable', $query));

        return count($response->getData(true)['data']);
    }

    /**
     * @param  array<string, int>  $query
     */
    private function picRequest(string $uri, array $query): Request
    {
        $request = Request::create($uri, 'GET', $query);
        $request->setUserResolver(fn (): User => $this->picUser);

        return $request;
    }

    private function createEmployeeUser(Company $company): User
    {
        $user = User::query()->create([
            'username' => 'user_'.uniqid(),
            'email' => uniqid('user_', true).'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => $company->id,
            'join_date' => '2026-01-01',
            'status' => 'Active',
        ]);

        return $user->load('employee');
    }

    private function createLeaveRequest(Employee $employee, string $leaveDate, string $status, ?string $supervisorReviewStatus): LeaveRequest
    {
        $leaveRequest = LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $this->sickLeave->id,
            'start_date' => $leaveDate,
            'end_date' => $leaveDate,
            'total_days' => 1,
            'reason' => 'PIC leave period filter test.',
            'is_active' => true,
            'status' => $status,
        ]);

        if ($supervisorReviewStatus !== null) {
            LeaveRequestHistory::query()->create([
                'leave_request_id' => $leaveRequest->id,
                'event_type' => 'supervisor_review',
                'title' => 'Supervisor Review',
                'from_status' => 'pending',
                'to_status' => $supervisorReviewStatus,
                'happened_at' => now('Asia/Jakarta'),
            ]);
        }

        return $leaveRequest;
    }
}
