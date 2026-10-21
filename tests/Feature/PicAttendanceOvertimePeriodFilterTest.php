<?php

namespace Tests\Feature;

use App\Http\Controllers\PicAttendance\PicAttendanceOvertimeController;
use App\Models\AttendanceOvertime;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\OvertimeLifecycleLog;
use App\Models\User;
use App\Support\Attendance\OvertimeReviewTableBuilder;
use App\Support\Attendance\OvertimeSummaryMetricBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PicAttendanceOvertimePeriodFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-21 09:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_single_period_filter_drives_pic_summary_tables_and_cards(): void
    {
        $company = Company::query()->create(['name' => 'RNB']);
        $picUser = $this->createEmployeeUser($company);
        $otherPicUser = $this->createEmployeeUser($company);
        $staffEmployee = $this->createEmployeeUser($company)->employee;

        $this->createWaitingVerificationOvertime($staffEmployee, $picUser, '2026-09-15');
        $this->createWaitingVerificationOvertime($staffEmployee, $picUser, '2026-10-10');
        $this->createWaitingVerificationOvertime($staffEmployee, $picUser, '2026-10-12');
        $this->createWaitingVerificationOvertime($staffEmployee, $otherPicUser, '2026-09-20');

        $septemberData = $this->picOvertimeIndex($picUser, ['month' => 9, 'year' => 2026])->getData();

        $this->assertSame(9, $septemberData['selectedMonth']);
        $this->assertSame(2026, $septemberData['selectedYear']);
        $this->assertSame('1 request', $septemberData['overtimeSummary']['pending_label']);
        $this->assertCount(1, $septemberData['pendingRows']);
        $this->assertCount(1, $septemberData['overtimeCards']);

        $octoberData = $this->picOvertimeIndex($picUser, [])->getData();

        $this->assertSame(10, $octoberData['selectedMonth']);
        $this->assertSame('2 request', $octoberData['overtimeSummary']['pending_label']);
        $this->assertCount(2, $octoberData['pendingRows']);
        $this->assertCount(2, $octoberData['overtimeCards']);
    }

    public function test_legacy_per_table_filter_parameters_are_ignored(): void
    {
        $company = Company::query()->create(['name' => 'RNB']);
        $picUser = $this->createEmployeeUser($company);
        $staffEmployee = $this->createEmployeeUser($company)->employee;

        $this->createWaitingVerificationOvertime($staffEmployee, $picUser, '2026-09-15');
        $this->createWaitingVerificationOvertime($staffEmployee, $picUser, '2026-10-10');

        $viewData = $this->picOvertimeIndex($picUser, [
            'card_month' => 9,
            'card_year' => 2026,
            'pending_month' => 9,
            'approved_month' => 9,
        ])->getData();

        $this->assertSame(10, $viewData['selectedMonth']);
        $this->assertCount(1, $viewData['pendingRows']);
        $this->assertCount(1, $viewData['overtimeCards']);
        $this->assertArrayNotHasKey('selectedCardMonth', $viewData);
        $this->assertArrayNotHasKey('selectedPendingMonth', $viewData);
        $this->assertArrayNotHasKey('selectedApprovedMonth', $viewData);
        $this->assertContains(2026, $viewData['yearOptions']);
    }

    /**
     * @param  array<string, int>  $query
     */
    private function picOvertimeIndex(User $picUser, array $query): View
    {
        $request = Request::create('/pic-attendance/overtime', 'GET', $query);
        $request->setUserResolver(static fn (): User => $picUser);

        return app(PicAttendanceOvertimeController::class)->index(
            $request,
            app(OvertimeSummaryMetricBuilder::class),
            app(OvertimeReviewTableBuilder::class),
        );
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

    private function createWaitingVerificationOvertime(Employee $staffEmployee, User $assignedBy, string $overtimeDate): AttendanceOvertime
    {
        $overtime = AttendanceOvertime::query()->create([
            'employee_id' => $staffEmployee->id,
            'assigned_by' => $assignedBy->id,
            'overtime_date' => $overtimeDate,
            'actual_start_time' => '18:00:00',
            'actual_end_time' => '20:00:00',
            'instruction' => 'PIC period filter test',
            'status' => 'completed',
        ]);

        foreach ([
            ['session_ended', 'clock_out', 4],
            ['task_hours_verification', 'pending', 5],
        ] as [$eventKey, $status, $stepOrder]) {
            OvertimeLifecycleLog::query()->create([
                'overtime_id' => $overtime->id,
                'phase' => 'pic',
                'event_key' => $eventKey,
                'step_order' => $stepOrder,
                'title' => $eventKey,
                'status' => $status,
                'happened_at' => Carbon::parse($overtimeDate.' 20:00:00', 'Asia/Jakarta'),
            ]);
        }

        return $overtime;
    }
}
