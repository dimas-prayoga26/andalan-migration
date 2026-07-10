<?php

namespace Tests\Unit;

use App\Support\Attendance\AttendanceDurationFormatter;
use PHPUnit\Framework\TestCase;

class AttendanceDurationFormatterTest extends TestCase
{
    public function test_late_minutes_are_formatted_as_hours_and_minutes(): void
    {
        $formatter = new AttendanceDurationFormatter;

        $this->assertSame('Late 7 Hours 15 Minutes', $formatter->lateLabel(435));
        $this->assertSame('Late 7 Hours', $formatter->lateLabel(420));
        $this->assertSame('Late 15 Minutes', $formatter->lateLabel(15));
        $this->assertSame('Late 1 Hour 1 Minute', $formatter->lateLabel(61));
        $this->assertSame('Late Arrival', $formatter->lateLabel(0));
    }
}
