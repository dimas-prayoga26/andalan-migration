<?php

namespace Tests\Unit;

use App\Http\Controllers\MailController;
use App\Models\MailAccessAccount;
use Illuminate\Support\Facades\Hash;
use ReflectionMethod;
use Tests\TestCase;

class MailAccessAccountTest extends TestCase
{
    public function test_pin_matches_plain_text_pin_for_manual_database_setup(): void
    {
        $account = new MailAccessAccount([
            'pin' => '123456',
        ]);

        $this->assertTrue($account->pinMatches('123456'));
        $this->assertFalse($account->pinMatches('654321'));
    }

    public function test_pin_matches_hashed_pin(): void
    {
        $account = new MailAccessAccount([
            'pin' => Hash::make('123456'),
        ]);

        $this->assertTrue($account->pinMatches('123456'));
        $this->assertFalse($account->pinMatches('654321'));
    }

    public function test_type_defaults_to_personal_and_exposes_supported_options(): void
    {
        $account = new MailAccessAccount;

        $this->assertSame(MailAccessAccount::TYPE_PERSONAL, $account->type);
        $this->assertSame([
            MailAccessAccount::TYPE_PERSONAL => 'Personal',
            MailAccessAccount::TYPE_DEPARTMENT => 'Department',
            MailAccessAccount::TYPE_APPLICANT_NOTIFICATION => 'Applicant Notification',
        ], MailAccessAccount::typeOptions());
    }

    public function test_mail_controller_uses_exact_hr_mailer_before_domain_catchall_fallback(): void
    {
        config([
            'mail_inboxes.accounts.rnb.username' => 'catchall-temp@rnb.co.id',
            'mail_inboxes.accounts.rnb_hr.username' => 'hr@rnb.co.id',
            'mail.mailers.rnb' => ['transport' => 'smtp'],
            'mail.mailers.rnb_hr' => ['transport' => 'smtp'],
        ]);

        $method = new ReflectionMethod(MailController::class, 'mailerForAccount');

        $this->assertSame('rnb_hr', $method->invoke(
            new MailController,
            new MailAccessAccount(['email' => 'hr@rnb.co.id']),
        ));

        $this->assertSame('rnb', $method->invoke(
            new MailController,
            new MailAccessAccount(['email' => 'recruitment@rnb.co.id']),
        ));
    }
}
