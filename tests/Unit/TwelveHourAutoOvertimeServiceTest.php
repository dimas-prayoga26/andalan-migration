<?php

namespace Tests\Unit;

use App\Services\Attendance\TwelveHourAutoOvertimeService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class TwelveHourAutoOvertimeServiceTest extends TestCase
{
    public function test_overtime_window_starts_after_twelve_working_hours(): void
    {
        $service = new TwelveHourAutoOvertimeService;

        $window = $service->overtimeWindow(
            Carbon::parse('2026-08-14 08:00:00', 'Asia/Jakarta'),
            Carbon::parse('2026-08-14 22:00:00', 'Asia/Jakarta')
        );

        $this->assertIsArray($window);
        $this->assertSame('2026-08-14 20:00:00', $window['actual_start_at']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-14 22:00:00', $window['actual_end_at']->format('Y-m-d H:i:s'));
        $this->assertSame(120, $window['overtime_minutes']);
    }

    public function test_exactly_twelve_working_hours_does_not_create_empty_overtime_window(): void
    {
        $service = new TwelveHourAutoOvertimeService;

        $window = $service->overtimeWindow(
            Carbon::parse('2026-08-14 08:00:00', 'Asia/Jakarta'),
            Carbon::parse('2026-08-14 20:00:00', 'Asia/Jakarta')
        );

        $this->assertNull($window);
    }

    public function test_overtime_window_supports_overnight_attendance(): void
    {
        $service = new TwelveHourAutoOvertimeService;

        $window = $service->overtimeWindow(
            Carbon::parse('2026-08-14 20:00:00', 'Asia/Jakarta'),
            Carbon::parse('2026-08-15 10:00:00', 'Asia/Jakarta')
        );

        $this->assertIsArray($window);
        $this->assertSame('2026-08-15 08:00:00', $window['actual_start_at']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-15 10:00:00', $window['actual_end_at']->format('Y-m-d H:i:s'));
        $this->assertSame(120, $window['overtime_minutes']);
    }
}
