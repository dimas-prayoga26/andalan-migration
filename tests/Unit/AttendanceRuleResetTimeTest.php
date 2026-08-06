<?php

namespace Tests\Unit;

use App\Models\RulesOfAttendace;
use PHPUnit\Framework\TestCase;

class AttendanceRuleResetTimeTest extends TestCase
{
    public function test_attendance_rule_defaults_office_reset_time_to_midnight(): void
    {
        $attendanceRule = new RulesOfAttendace;

        $this->assertSame('00:00:00', $attendanceRule->office_reset_time);
    }
}
