<?php

namespace Tests\Unit;

use App\Http\Controllers\MailController;
use App\Models\MailAccessAccount;
use App\Services\MailInboxService;
use App\Services\SimpleImapClient;
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

    public function test_configured_inbox_emails_include_backend_connection_accounts(): void
    {
        config([
            'mail_inboxes.accounts.rnb.username' => 'catchall-temp@rnb.co.id',
            'mail_inboxes.accounts.rnb_hr.username' => 'hr@rnb.co.id',
        ]);

        $this->assertContains('catchall-temp@rnb.co.id', MailAccessAccount::catchAllInboxEmails()->all());
        $this->assertNotContains('hr@rnb.co.id', MailAccessAccount::catchAllInboxEmails()->all());
        $this->assertContains('catchall-temp@rnb.co.id', MailAccessAccount::configuredInboxEmails()->all());
        $this->assertContains('hr@rnb.co.id', MailAccessAccount::configuredInboxEmails()->all());
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

    public function test_catchall_inbox_messages_are_matched_to_the_logged_in_recipient(): void
    {
        new MailInboxService;

        $client = new SimpleImapClient('localhost', 993, 'ssl', 'catchall-temp@tims.co.id', 'secret');
        $belongsToEmail = new ReflectionMethod($client, 'messageBelongsToEmail');
        $rawHeaders = implode("\n", [
            'From: BVCS <claim_mv@bcainsurance.co.id>',
            'To: Yoga <yoga@tims.co.id>',
            'Delivered-To: yoga@tims.co.id',
            'Subject: Claim',
        ]);

        $this->assertTrue($belongsToEmail->invoke($client, $rawHeaders, 'yoga@tims.co.id', SimpleImapClient::EMAIL_FILTER_RECIPIENT));
        $this->assertFalse($belongsToEmail->invoke($client, $rawHeaders, 'dimas@tims.co.id', SimpleImapClient::EMAIL_FILTER_RECIPIENT));
    }

    public function test_catchall_sent_messages_are_matched_to_the_logged_in_sender(): void
    {
        new MailInboxService;

        $client = new SimpleImapClient('localhost', 993, 'ssl', 'catchall-temp@tims.co.id', 'secret');
        $belongsToEmail = new ReflectionMethod($client, 'messageBelongsToEmail');
        $rawHeaders = implode("\n", [
            'From: Dimas <dimas@tims.co.id>',
            'To: Client <client@example.test>',
            'Subject: Follow up',
        ]);

        $this->assertTrue($belongsToEmail->invoke($client, $rawHeaders, 'dimas@tims.co.id', SimpleImapClient::EMAIL_FILTER_SENDER));
        $this->assertFalse($belongsToEmail->invoke($client, $rawHeaders, 'yoga@tims.co.id', SimpleImapClient::EMAIL_FILTER_SENDER));
    }
}
