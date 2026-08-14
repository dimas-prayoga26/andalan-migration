<?php

namespace Tests\Feature;

use Tests\TestCase;

class OvertimePaymentDisbursementScheduleTest extends TestCase
{
    public function test_monthly_payment_disbursement_command_contains_required_guardrails(): void
    {
        $command = file_get_contents(app_path('Console/Commands/CompleteMonthlyOvertimePaymentDisbursements.php'));

        $this->assertIsString($command);
        $this->assertStringContainsString("Signature('overtimes:complete-monthly-payment-disbursements", $command);
        $this->assertStringContainsString("private const ACTOR_PROFILE_NAME = 'Leonie Putri Andhari';", $command);
        $this->assertStringContainsString("->where('event_key', self::DIRECTOR_APPROVAL)", $command);
        $this->assertStringContainsString("->whereRaw('LOWER(status) = ?', ['approved'])", $command);
        $this->assertStringContainsString("->where('event_key', self::PAYMENT_DISBURSEMENT)", $command);
        $this->assertStringContainsString("->whereRaw('LOWER(status) = ?', ['pending'])", $command);
        $this->assertStringContainsString("'status' => 'completed'", $command);
        $this->assertStringContainsString("'actor_id' => \$actor->id", $command);
    }

    public function test_monthly_payment_disbursement_command_is_scheduled_on_first_day_of_month(): void
    {
        $schedule = file_get_contents(base_path('routes/console.php'));

        $this->assertIsString($schedule);
        $this->assertStringContainsString("Schedule::command('overtimes:complete-monthly-payment-disbursements')", $schedule);
        $this->assertStringContainsString("->monthlyOn(1, '00:15')", $schedule);
        $this->assertStringContainsString("->timezone('Asia/Jakarta')", $schedule);
        $this->assertStringContainsString('->withoutOverlapping()', $schedule);
    }
}
