<?php

namespace Tests\Unit;

use App\Models\AttendanceOvertime;
use App\Models\OvertimeLifecycleLog;
use App\Support\Attendance\OvertimeReviewTableBuilder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Tests\TestCase;

class OvertimeReviewTableBuilderTest extends TestCase
{
    #[DataProvider('contextStatusRules')]
    public function test_it_uses_expected_pending_and_approved_lifecycle_rules(string $context, array $expectedRule): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('statusRuleFor');
        $method->setAccessible(true);

        $this->assertSame($expectedRule, $method->invoke(new OvertimeReviewTableBuilder, $context));
    }

    public function test_overtime_review_views_use_dynamic_rows_and_filter_inputs(): void
    {
        foreach ([
            resource_path('views/admin_attendance/overtime/index.blade.php'),
            resource_path('views/pic_attendance/overtime/index.blade.php'),
            resource_path('views/director_attendance/overtime/index.blade.php'),
        ] as $viewPath) {
            $view = (string) file_get_contents($viewPath);

            $this->assertStringContainsString('name="month"', $view);
            $this->assertStringContainsString('name="year"', $view);
            $this->assertStringContainsString('$monthOptions', $view);
            $this->assertStringContainsString('$yearOptions', $view);
            $this->assertStringContainsString('$pendingRows', $view);
            $this->assertStringContainsString('$approvedRows', $view);
            $this->assertStringNotContainsString('<td>Dimas</td>', $view);
            $this->assertStringNotContainsString('<td>Rexy</td>', $view);
        }
    }

    public function test_admin_view_uses_complete_table_and_dynamic_lifecycle_cards(): void
    {
        $view = (string) file_get_contents(resource_path('views/admin_attendance/overtime/index.blade.php'));

        $this->assertStringContainsString('Status : <span class="text-success">Complete</span>', $view);
        $this->assertStringContainsString('No complete overtime data available for this period.', $view);
        $this->assertStringContainsString('$overtimeCards', $view);
        $this->assertStringContainsString('$overtimeCard[\'current_log\'][\'title\']', $view);
        $this->assertStringNotContainsString('SPV : Approved', $view);
        $this->assertStringNotContainsString('#OVT-2605-0101', $view);
    }

    public function test_admin_pending_lifecycle_range_starts_at_task_hours_verification_and_excludes_payment_complete(): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('isAdminPendingLifecycleRange');
        $method->setAccessible(true);

        $builder = new OvertimeReviewTableBuilder;

        $this->assertFalse($method->invoke($builder, $this->overtimeWithLifecycle([
            'task_hours_verification' => 'waiting',
        ])));

        $this->assertTrue($method->invoke($builder, $this->overtimeWithLifecycle([
            'task_hours_verification' => 'pending',
            'payment_disbursement' => 'waiting',
        ])));

        $this->assertTrue($method->invoke($builder, $this->overtimeWithLifecycle([
            'task_hours_verification' => 'verified',
            'payroll_processing' => 'calculated_locked',
            'director_approval' => 'approved',
            'payment_disbursement' => 'upcoming',
        ])));

        $this->assertFalse($method->invoke($builder, $this->overtimeWithLifecycle([
            'task_hours_verification' => 'verified',
            'payroll_processing' => 'calculated_locked',
            'director_approval' => 'approved',
            'payment_disbursement' => 'complete',
        ])));
    }

    public function test_admin_pic_and_director_detail_urls_include_overtime_uid(): void
    {
        $reflection = new ReflectionClass(OvertimeReviewTableBuilder::class);
        $method = $reflection->getMethod('detailUrlFor');
        $method->setAccessible(true);

        $builder = new OvertimeReviewTableBuilder;
        $overtime = new AttendanceOvertime(['id' => 'OVT-DETAIL-UID']);

        $this->assertStringEndsWith(
            '/admin-attendance/overtime/detail/OVT-DETAIL-UID',
            $method->invoke($builder, 'admin', $overtime)
        );
        $this->assertStringEndsWith(
            '/pic-attendance/overtime/detail/OVT-DETAIL-UID',
            $method->invoke($builder, 'pic', $overtime)
        );
        $this->assertStringEndsWith(
            '/director-attendance/overtime/detail/OVT-DETAIL-UID',
            $method->invoke($builder, 'director', $overtime)
        );
    }

    /**
     * @return array<string, array{0:string,1:array{0:string,1:string,2:string,3:string}}>
     */
    public static function contextStatusRules(): array
    {
        return [
            'admin' => ['admin', ['task_hours_verification', 'in_progress', 'payment_disbursement', 'complete']],
            'pic' => ['pic', ['task_hours_verification', 'pending', 'task_hours_verification', 'verified']],
            'director' => ['director', ['director_approval', 'pending', 'director_approval', 'approved']],
        ];
    }

    /**
     * @param  array<string, string>  $statuses
     */
    private function overtimeWithLifecycle(array $statuses): AttendanceOvertime
    {
        $overtime = new AttendanceOvertime(['id' => 'overtime-test']);
        $overtime->setRelation('lifecycleLogs', $this->lifecycleLogs($statuses));

        return $overtime;
    }

    /**
     * @param  array<string, string>  $statuses
     * @return Collection<int, OvertimeLifecycleLog>
     */
    private function lifecycleLogs(array $statuses): Collection
    {
        return collect($statuses)
            ->map(fn (string $status, string $eventKey): OvertimeLifecycleLog => new OvertimeLifecycleLog([
                'overtime_id' => 'overtime-test',
                'event_key' => $eventKey,
                'status' => $status,
            ]))
            ->values();
    }
}
