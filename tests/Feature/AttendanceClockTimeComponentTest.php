<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AttendanceClockTimeComponentTest extends TestCase
{
    /**
     * Daily attendance log views that show clock in/out times.
     *
     * @var list<string>
     */
    private const DAILY_LOG_VIEWS = [
        'admin_attendance/recap_attendance/index.blade.php',
        'director_attendance/attendance/index.blade.php',
        'pic_attendance/attendance/index.blade.php',
    ];

    public function test_badge_time_is_protected_from_browser_translation(): void
    {
        $html = Blade::render('<x-attendance-clock-time :value="$value" :badge="$badge" />', [
            'value' => '08:00',
            'badge' => 'success',
        ]);

        $this->assertSame('<span class="badge badge-sm badge-success light fw-bold" translate="no">08:00</span>', trim($html));
    }

    public function test_plain_time_without_badge_is_protected_from_browser_translation(): void
    {
        $html = Blade::render('<x-attendance-clock-time :value="$value" :badge="$badge" />', [
            'value' => '17:05',
            'badge' => '',
        ]);

        $this->assertSame('<span class="fw-bold" translate="no">17:05</span>', trim($html));
    }

    public function test_non_time_labels_stay_translatable(): void
    {
        $leaveHtml = Blade::render('<x-attendance-clock-time :value="$value" :badge="$badge" />', [
            'value' => 'Unpaid Leave',
            'badge' => 'secondary',
        ]);
        $emptyHtml = Blade::render('<x-attendance-clock-time :value="$value" />', ['value' => '-']);

        $this->assertSame('<span class="badge badge-sm badge-secondary light fw-bold">Unpaid Leave</span>', trim($leaveHtml));
        $this->assertSame('<span class="fw-bold">-</span>', trim($emptyHtml));
    }

    public function test_daily_log_views_render_clock_times_through_the_component(): void
    {
        foreach (self::DAILY_LOG_VIEWS as $viewPath) {
            $view = File::get(resource_path('views/'.$viewPath));

            $this->assertStringContainsString('<x-attendance-clock-time :value="$row[\'clock_in\']" :badge="$row[\'clock_in_badge\']" />', $view, $viewPath);
            $this->assertStringContainsString('<x-attendance-clock-time :value="$row[\'clock_out\']" :badge="$row[\'clock_out_badge\']" />', $view, $viewPath);
            $this->assertStringNotContainsString('light fw-bold">{{ $row[\'clock_in\'] }}</span>', $view, $viewPath);
            $this->assertStringContainsString('<span id="attendanceLogClockIn" class="fw-semibold" translate="no">-</span>', $view, $viewPath);
            $this->assertStringContainsString('<span id="attendanceLogClockOut" class="text-gray fw-semibold" translate="no">-</span>', $view, $viewPath);
        }
    }
}
