<?php

namespace Tests\Feature;

use App\Http\Controllers\DirectorAttendance\DirectorAttendanceOvertimeController;
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

class DirectorAttendanceOvertimePeriodFilterTest extends TestCase
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

    public function test_single_period_filter_drives_director_tables_and_cards(): void
    {
        $employee = $this->createActiveEmployee();
        $this->createOvertimeWithDirectorStatus($employee, '2026-09-15', 'pending');
        $this->createOvertimeWithDirectorStatus($employee, '2026-09-16', 'approved');
        $this->createOvertimeWithDirectorStatus($employee, '2026-10-10', 'pending');
        $this->createOvertimeWithDirectorStatus($employee, '2026-10-11', 'pending');
        $this->createOvertimeWithDirectorStatus($employee, '2026-10-12', 'approved');

        $septemberData = $this->directorOvertimeIndex(['month' => 9, 'year' => 2026])->getData();

        $this->assertSame(9, $septemberData['selectedMonth']);
        $this->assertSame(2026, $septemberData['selectedYear']);
        $this->assertCount(1, $septemberData['pendingRows']);
        $this->assertCount(1, $septemberData['approvedRows']);
        $this->assertCount(2, $septemberData['overtimeCards']);

        $octoberData = $this->directorOvertimeIndex([])->getData();

        $this->assertSame(10, $octoberData['selectedMonth']);
        $this->assertCount(2, $octoberData['pendingRows']);
        $this->assertCount(1, $octoberData['approvedRows']);
        $this->assertCount(3, $octoberData['overtimeCards']);
        $this->assertSame(
            ['12 Oct 2026', '11 Oct 2026', '10 Oct 2026'],
            $octoberData['overtimeCards']->pluck('date_label')->all(),
        );
    }

    public function test_legacy_per_table_filter_parameters_are_ignored(): void
    {
        $employee = $this->createActiveEmployee();
        $this->createOvertimeWithDirectorStatus($employee, '2026-09-15', 'pending');
        $this->createOvertimeWithDirectorStatus($employee, '2026-10-10', 'pending');

        $viewData = $this->directorOvertimeIndex([
            'card_month' => 9,
            'card_year' => 2026,
            'pending_month' => 9,
            'approved_month' => 9,
        ])->getData();

        $this->assertSame(10, $viewData['selectedMonth']);
        $this->assertCount(1, $viewData['pendingRows']);
        $this->assertSame('10 Oct 2026', $viewData['overtimeCards']->first()['date_label']);
        $this->assertArrayNotHasKey('selectedCardMonth', $viewData);
        $this->assertArrayNotHasKey('selectedPendingMonth', $viewData);
        $this->assertArrayNotHasKey('selectedApprovedMonth', $viewData);
        $this->assertContains(2026, $viewData['yearOptions']);
    }

    /**
     * @param  array<string, int>  $query
     */
    private function directorOvertimeIndex(array $query): View
    {
        $request = Request::create('/director-attendance/overtime', 'GET', $query);

        return app(DirectorAttendanceOvertimeController::class)->index(
            $request,
            app(OvertimeSummaryMetricBuilder::class),
            app(OvertimeReviewTableBuilder::class),
        );
    }

    private function createActiveEmployee(): Employee
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
            'current_company_id' => Company::query()->create(['name' => 'RNB'])->id,
            'join_date' => '2026-01-01',
            'status' => 'Active',
        ]);

        return $employee;
    }

    private function createOvertimeWithDirectorStatus(Employee $employee, string $overtimeDate, string $directorStatus): AttendanceOvertime
    {
        $overtime = AttendanceOvertime::query()->create([
            'employee_id' => $employee->id,
            'overtime_date' => $overtimeDate,
            'actual_start_time' => '18:00:00',
            'actual_end_time' => '20:00:00',
            'instruction' => 'Director period filter test',
            'status' => 'completed',
        ]);

        foreach ([
            ['task_hours_verification', 'verified', 5],
            ['payroll_processing', 'calculated_locked', 6],
            ['director_approval', $directorStatus, 7],
        ] as [$eventKey, $status, $stepOrder]) {
            OvertimeLifecycleLog::query()->create([
                'overtime_id' => $overtime->id,
                'phase' => 'payroll_payment',
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
