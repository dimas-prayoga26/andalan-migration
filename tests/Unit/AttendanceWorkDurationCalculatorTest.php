<?php

namespace Tests\Unit;

use App\Support\Attendance\AttendanceWorkDurationCalculator;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class AttendanceWorkDurationCalculatorTest extends TestCase
{
    public function test_standard_office_hours_are_reduced_by_rest_time(): void
    {
        $calculator = new AttendanceWorkDurationCalculator;

        $this->assertSame(8.0, $calculator->netHoursBetween(
            Carbon::parse('2026-07-07 08:00:00', 'Asia/Jakarta'),
            Carbon::parse('2026-07-07 17:00:00', 'Asia/Jakarta')
        ));
        $this->assertSame(480, $calculator->netMinutesBetweenTimeLabels('08:00', '17:00'));
    }

    public function test_short_work_duration_is_not_reduced_by_rest_time(): void
    {
        $calculator = new AttendanceWorkDurationCalculator;

        $this->assertSame(4.0, $calculator->netHoursBetween(
            Carbon::parse('2026-07-07 08:00:00', 'Asia/Jakarta'),
            Carbon::parse('2026-07-07 12:00:00', 'Asia/Jakarta')
        ));
        $this->assertSame(240, $calculator->netMinutesBetweenTimeLabels('08:00', '12:00'));
    }

    public function test_rest_time_can_be_skipped_for_driver_position(): void
    {
        $calculator = new AttendanceWorkDurationCalculator;

        $this->assertSame(9.0, $calculator->netHoursBetween(
            Carbon::parse('2026-07-07 08:00:00', 'Asia/Jakarta'),
            Carbon::parse('2026-07-07 17:00:00', 'Asia/Jakarta'),
            false
        ));
        $this->assertSame(540, $calculator->netMinutesBetweenTimeLabels('08:00', '17:00', false));
    }

    public function test_negative_duration_returns_zero(): void
    {
        $calculator = new AttendanceWorkDurationCalculator;

        $this->assertSame(0.0, $calculator->netHoursBetween(
            Carbon::parse('2026-07-07 17:00:00', 'Asia/Jakarta'),
            Carbon::parse('2026-07-07 08:00:00', 'Asia/Jakarta')
        ));
    }
}
