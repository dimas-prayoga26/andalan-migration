<?php

namespace Tests\Unit;

use App\Models\MailAccessAccount;
use Illuminate\Support\Facades\Hash;
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
        $this->assertFalse($account->is_applicant_mail_sender);
        $this->assertSame([
            MailAccessAccount::TYPE_PERSONAL => 'Personal',
            MailAccessAccount::TYPE_DEPARTMENT => 'Department',
        ], MailAccessAccount::typeOptions());
    }
}
