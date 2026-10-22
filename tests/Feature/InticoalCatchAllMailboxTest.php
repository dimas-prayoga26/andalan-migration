<?php

namespace Tests\Feature;

use App\Models\MailAccessAccount;
use Tests\TestCase;

class InticoalCatchAllMailboxTest extends TestCase
{
    public function test_inticoal_catch_all_inbox_and_mailer_are_configured(): void
    {
        $this->assertSame('mail.inticoal.co.id', config('mail_inboxes.accounts.inticoal.host'));
        $this->assertSame('catchall-temp@inticoal.co.id', config('mail_inboxes.accounts.inticoal.username'));
        $this->assertContains('catchall-temp@inticoal.co.id', MailAccessAccount::catchAllInboxEmails()->all());

        $this->assertSame('smtp', config('mail.mailers.inticoal.transport'));
        $this->assertSame('mail.inticoal.co.id', config('mail.mailers.inticoal.host'));
        $this->assertSame('catchall-temp@inticoal.co.id', config('mail.mailers.inticoal.username'));
        $this->assertSame('inticoal.co.id', config('mail.mailers.inticoal.local_domain'));
    }
}
