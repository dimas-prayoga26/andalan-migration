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

    public function test_director_overtime_cards_lock_cards_before_director_approval(): void
    {
        $employee = $this->createActiveEmployee();
        $this->createOvertime($employee, '2026-09-30', [
            'task_hours_verification' => 'pending',
        ]);
        $this->createOvertime($employee, '2026-10-01', [
            'task_hours_verification' => 'pending',
            'payroll_processing' => 'waiting',
            'director_approval' => 'waiting',
        ]);
        $this->createOvertime($employee, '2026-10-02', []);
        $this->createOvertime($employee, '2026-10-03', [
            'task_hours_verification' => 'verified',
            'payroll_processing' => 'pending',
            'director_approval' => 'waiting',
        ]);
        $this->createOvertime($employee, '2026-10-04', [
            'task_hours_verification' => 'verified',
            'payroll_processing' => 'rejected',
            'director_approval' => 'waiting',
        ]);
        $this->createOvertimeWithDirectorStatus($employee, '2026-10-05', 'pending');
        $this->createOvertimeWithDirectorStatus($employee, '2026-10-06', 'approved');
        $this->createOvertimeWithDirectorStatus($employee, '2026-10-07', 'rejected');
        $this->createOvertimeWithDirectorStatus($employee, '2026-10-08', 'pending')->update(['status' => 'cancelled']);

        $viewData = $this->directorOvertimeIndex(['month' => 10, 'year' => 2026])->getData();
        $overtimeCards = $viewData['overtimeCards'];

        $this->assertCount(1, $viewData['pendingRows']);
        $this->assertSame(
            ['07 Oct 2026', '06 Oct 2026', '05 Oct 2026', '04 Oct 2026', '03 Oct 2026', '02 Oct 2026', '01 Oct 2026'],
            $overtimeCards->pluck('date_label')->all(),
        );
        $this->assertSame(
            [
                'Director Approval',
                'Director Approval',
                'Director Approval',
                'HR / Payroll Processing',
                'HR / Payroll Processing',
                'Task & Hours Verification',
                'Task & Hours Verification',
            ],
            $overtimeCards->pluck('current_log.title')->all(),
        );
        $this->assertSame(
            ['Rejected', 'Approved', 'Pending', 'Rejected', 'Pending', 'Waiting', 'Pending'],
            $overtimeCards->pluck('current_log.status')->all(),
        );
        $this->assertSame(
            [null, null, null, 'Ditolak HR', 'Belum Diproses HR', 'Belum Diverifikasi PIC', 'Belum Diverifikasi PIC'],
            $overtimeCards->map(fn (array $overtimeCard): ?string => $overtimeCard['locked_notice']['title'] ?? null)->all(),
        );
        $this->assertSame(
            'Lembur ini belum diverifikasi oleh PIC / Supervisor, sehingga belum bisa diproses.',
            $overtimeCards->last()['locked_notice']['message'],
        );
        $this->assertSame(
            'Lembur ini belum diproses oleh HR / Payroll, sehingga belum bisa di-approve Director.',
            $overtimeCards->get(4)['locked_notice']['message'],
        );
        $this->assertSame(
            'Lembur ini ditolak oleh HR / Payroll, sehingga tidak diteruskan ke Director.',
            $overtimeCards->get(3)['locked_notice']['message'],
        );
    }

    public function test_director_overtime_view_locks_cards_with_sweetalert_notice(): void
    {
        $view = (string) file_get_contents(resource_path('views/director_attendance/overtime/index.blade.php'));

        $this->assertStringContainsString('js-director-overtime-card-locked', $view);
        $this->assertStringContainsString('data-locked-message="{{ $overtimeCardLockedNotice[\'message\'] }}"', $view);
        $this->assertStringContainsString("window.Swal.fire({ icon: 'info', title: title, text: message });", $view);
        $this->assertStringContainsString('assets/vendor/sweetalert2/sweetalert2.min.js', $view);
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
        return $this->createOvertime($employee, $overtimeDate, [
            'task_hours_verification' => 'verified',
            'payroll_processing' => 'calculated_locked',
            'director_approval' => $directorStatus,
        ]);
    }

    /**
     * @param  array<string, string>  $lifecycleStatuses
     */
    private function createOvertime(Employee $employee, string $overtimeDate, array $lifecycleStatuses): AttendanceOvertime
    {
        $overtime = AttendanceOvertime::query()->create([
            'employee_id' => $employee->id,
            'overtime_date' => $overtimeDate,
            'actual_start_time' => '18:00:00',
            'actual_end_time' => '20:00:00',
            'instruction' => 'Director period filter test',
            'status' => 'completed',
        ]);

        $stepOrders = [
            'task_hours_verification' => 5,
            'payroll_processing' => 6,
            'director_approval' => 7,
        ];

        foreach ($lifecycleStatuses as $eventKey => $status) {
            OvertimeLifecycleLog::query()->create([
                'overtime_id' => $overtime->id,
                'phase' => 'payroll_payment',
                'event_key' => $eventKey,
                'step_order' => $stepOrders[$eventKey],
                'title' => $eventKey,
                'status' => $status,
                'happened_at' => Carbon::parse($overtimeDate.' 20:00:00', 'Asia/Jakarta'),
            ]);
        }

        return $overtime;
    }
}
