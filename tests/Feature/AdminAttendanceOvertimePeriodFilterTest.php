<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAttendance\AttendanceOvertimeController;
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

class AdminAttendanceOvertimePeriodFilterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<string, string>
     */
    private const WAITING_PIC_VERIFICATION = [
        'session_ended' => 'clock_out',
        'task_hours_verification' => 'pending',
    ];

    /**
     * @var array<string, string>
     */
    private const WAITING_PAYROLL_PROCESSING = [
        'session_ended' => 'clock_out',
        'task_hours_verification' => 'verified',
        'payroll_processing' => 'pending',
    ];

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

    public function test_single_period_filter_drives_summary_badges_tables_and_overtime_cards(): void
    {
        $employee = $this->createActiveEmployee();
        $this->createOvertime($employee, '2026-09-15', self::WAITING_PIC_VERIFICATION);
        $this->createOvertime($employee, '2026-09-16', self::WAITING_PAYROLL_PROCESSING);
        $this->createOvertime($employee, '2026-10-10', self::WAITING_PIC_VERIFICATION);
        $this->createOvertime($employee, '2026-10-12', self::WAITING_PIC_VERIFICATION);
        $this->createOvertime($employee, '2026-10-13', self::WAITING_PAYROLL_PROCESSING);

        $viewData = $this->adminOvertimeIndex(['month' => 9, 'year' => 2026])->getData();

        $this->assertSame(9, $viewData['selectedMonth']);
        $this->assertSame(2026, $viewData['selectedYear']);
        $this->assertSame('1 request', $viewData['overtimeSummary']['pending_label']);
        $this->assertSame('1 request', $this->metricCardValue($viewData['overtimeMetricCards'], 'Pending'));
        $this->assertCount(1, $viewData['pendingRows']);
        $this->assertSame(['16 Sep 2026', '15 Sep 2026'], $viewData['overtimeCards']->pluck('date_label')->all());
    }

    public function test_period_filter_defaults_to_current_month_when_missing_or_invalid(): void
    {
        $employee = $this->createActiveEmployee();
        $this->createOvertime($employee, '2026-09-15', self::WAITING_PIC_VERIFICATION);
        $this->createOvertime($employee, '2026-10-10', self::WAITING_PIC_VERIFICATION);
        $this->createOvertime($employee, '2026-10-12', self::WAITING_PIC_VERIFICATION);

        foreach ([[], ['month' => 13, 'year' => 1999]] as $query) {
            $viewData = $this->adminOvertimeIndex($query)->getData();

            $this->assertSame(10, $viewData['selectedMonth']);
            $this->assertSame(2026, $viewData['selectedYear']);
            $this->assertSame('2 request', $viewData['overtimeSummary']['pending_label']);
        }
    }

    public function test_legacy_per_table_filter_parameters_are_ignored(): void
    {
        $employee = $this->createActiveEmployee();
        $this->createOvertime($employee, '2026-09-15', self::WAITING_PAYROLL_PROCESSING);
        $this->createOvertime($employee, '2026-10-10', self::WAITING_PAYROLL_PROCESSING);

        $viewData = $this->adminOvertimeIndex([
            'card_month' => 9,
            'card_year' => 2026,
            'pending_month' => 9,
            'complete_month' => 9,
        ])->getData();

        $this->assertSame(10, $viewData['selectedMonth']);
        $this->assertSame('10 Oct 2026', $viewData['overtimeCards']->first()['date_label']);
        $this->assertArrayNotHasKey('selectedCardMonth', $viewData);
        $this->assertArrayNotHasKey('selectedPendingMonth', $viewData);
        $this->assertArrayNotHasKey('selectedCompleteMonth', $viewData);
        $this->assertContains(2026, $viewData['yearOptions']);
    }

    public function test_admin_overtime_cards_lock_cards_before_pic_verification(): void
    {
        $employee = $this->createActiveEmployee();
        $this->createOvertime($employee, '2026-09-30', self::WAITING_PIC_VERIFICATION);
        $this->createOvertime($employee, '2026-10-01', self::WAITING_PIC_VERIFICATION);
        $this->createOvertime($employee, '2026-10-02', [
            'task_hours_verification' => 'verified',
            'payroll_processing' => 'waiting',
        ]);
        $this->createOvertime($employee, '2026-10-03', self::WAITING_PAYROLL_PROCESSING);
        $this->createOvertime($employee, '2026-10-04', [
            'task_hours_verification' => 'verified',
            'payroll_processing' => 'calculated_locked',
            'director_approval' => 'pending',
        ]);
        $this->createOvertime($employee, '2026-10-05', [
            'task_hours_verification' => 'verified',
            'payroll_processing' => 'calculated_locked',
            'director_approval' => 'approved',
            'payment_disbursement' => 'completed',
        ]);
        $this->createOvertime($employee, '2026-10-06', [
            'session_ended' => 'waiting',
            'task_hours_verification' => 'waiting',
        ]);
        $this->createOvertime($employee, '2026-10-07', []);
        $cancelledOvertime = $this->createOvertime($employee, '2026-10-08', self::WAITING_PIC_VERIFICATION);
        $cancelledOvertime->update(['status' => 'cancelled']);

        $overtimeCards = $this->adminOvertimeIndex(['month' => 10, 'year' => 2026])->getData()['overtimeCards'];

        $this->assertSame(
            ['07 Oct 2026', '06 Oct 2026', '05 Oct 2026', '04 Oct 2026', '03 Oct 2026', '02 Oct 2026', '01 Oct 2026'],
            $overtimeCards->pluck('date_label')->all(),
        );
        $this->assertSame(
            [
                'Task & Hours Verification',
                'Task & Hours Verification',
                'Payment Distribution',
                'Director Approval',
                'HR / Payroll Processing',
                'HR / Payroll Processing',
                'Task & Hours Verification',
            ],
            $overtimeCards->pluck('current_log.title')->all(),
        );
        $this->assertSame(
            ['Waiting', 'Waiting', 'Completed', 'Pending', 'Pending', 'Waiting', 'Pending'],
            $overtimeCards->pluck('current_log.status')->all(),
        );
        $this->assertSame(
            ['Belum Diverifikasi PIC', 'Belum Diverifikasi PIC', null, null, null, null, 'Belum Diverifikasi PIC'],
            $overtimeCards->map(fn (array $overtimeCard): ?string => $overtimeCard['locked_notice']['title'] ?? null)->all(),
        );
        $this->assertSame(
            'Lembur ini belum diverifikasi oleh PIC / Supervisor, sehingga belum bisa diproses.',
            $overtimeCards->last()['locked_notice']['message'],
        );
    }

    /**
     * @param  array<string, int>  $query
     */
    private function adminOvertimeIndex(array $query): View
    {
        $request = Request::create('/admin-attendance/overtime', 'GET', $query);

        return app(AttendanceOvertimeController::class)->index(
            $request,
            app(OvertimeSummaryMetricBuilder::class),
            app(OvertimeReviewTableBuilder::class),
        );
    }

    /**
     * @param  array<int, array{label: string, value: string}>  $metricCards
     */
    private function metricCardValue(array $metricCards, string $label): ?string
    {
        return collect($metricCards)->firstWhere('label', $label)['value'] ?? null;
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
            'instruction' => 'Period filter test',
            'status' => 'completed',
        ]);

        $stepOrders = [
            'session_ended' => 4,
            'task_hours_verification' => 5,
            'payroll_processing' => 6,
            'director_approval' => 7,
            'payment_disbursement' => 8,
        ];

        foreach ($lifecycleStatuses as $eventKey => $status) {
            OvertimeLifecycleLog::query()->create([
                'overtime_id' => $overtime->id,
                'phase' => 'pic',
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
