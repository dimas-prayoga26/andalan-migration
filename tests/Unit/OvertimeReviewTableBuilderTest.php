<?php

namespace Tests\Unit;

use App\Support\Attendance\OvertimeReviewTableBuilder;
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

    /**
     * @return array<string, array{0:string,1:array{0:string,1:string,2:string,3:string}}>
     */
    public static function contextStatusRules(): array
    {
        return [
            'admin' => ['admin', ['task_hours_verification', 'pending', 'director_approval', 'approved']],
            'pic' => ['pic', ['task_hours_verification', 'pending', 'task_hours_verification', 'verified']],
            'director' => ['director', ['director_approval', 'pending', 'director_approval', 'approved']],
        ];
    }
}
