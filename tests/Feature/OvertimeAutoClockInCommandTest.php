<?php

namespace Tests\Feature;

use Tests\TestCase;

class OvertimeAutoClockInCommandTest extends TestCase
{
    public function test_command_contains_auto_clock_in_guardrails(): void
    {
        $command = file_get_contents(app_path('Console/Commands/AutoClockInOvertimes.php'));

        $this->assertIsString($command);
        $this->assertStringContainsString("Signature('overtimes:auto-clock-in", $command);
        $this->assertStringContainsString("whereNull('actual_start_time')", $command);
        $this->assertStringContainsString('hasAttendanceCheckIn', $command);
        $this->assertStringContainsString("'actual_start_time' => \$actualStartTime", $command);
        $this->assertStringContainsString("'status' => self::STATUS_IN_PROGRESS", $command);
        $this->assertStringContainsString("'session_started'", $command);
        $this->assertStringContainsString("'task_deliverables_submitted'", $command);
        $this->assertStringContainsString("'status' => 'pending'", $command);
    }

    public function test_command_is_scheduled_every_minute(): void
    {
        $schedule = file_get_contents(base_path('routes/console.php'));

        $this->assertIsString($schedule);
        $this->assertStringContainsString("Schedule::command('overtimes:auto-clock-in')", $schedule);
        $this->assertStringContainsString('->everyMinute()', $schedule);
        $this->assertStringContainsString("->timezone('Asia/Jakarta')", $schedule);
        $this->assertStringContainsString('->withoutOverlapping()', $schedule);
    }
}
