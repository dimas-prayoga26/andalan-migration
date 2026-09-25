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
}
