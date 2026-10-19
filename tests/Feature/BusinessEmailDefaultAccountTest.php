<?php

namespace Tests\Feature;

use App\Models\MailAccessAccount;
use App\Models\Role;
use App\Models\User;
use App\Services\MailInboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BusinessEmailDefaultAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @var MailInboxService&object{fetchedEmails: list<string>} */
    private MailInboxService $mailInbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailInbox = new class extends MailInboxService
        {
            /** @var list<string> */
            public array $fetchedEmails = [];

            public function messagesFor(string $email, int $limit = 20, string $folder = self::FOLDER_INBOX): array
            {
                $this->fetchedEmails[] = $email;

                return [];
            }
        };

        $this->app->instance(MailInboxService::class, $this->mailInbox);
    }

    public function test_business_email_automatically_selects_account_with_first_domain(): void
    {
        $user = $this->createBusinessEmailUser();
        $this->createPersonalAccount($user, 'andi@tims.co.id');
        $this->createPersonalAccount($user, 'fuad@tims.co.id');
        $rneAccount = $this->createPersonalAccount($user, 'fuad.fahrudin@rne.co.id');

        $this->actingAs($user)
            ->followingRedirects()
            ->get(route('business-email.index'))
            ->assertOk()
            ->assertDontSee('Pilih email account terlebih dahulu.')
            ->assertSeeInOrder(['fuad.fahrudin@rne.co.id', 'andi@tims.co.id', 'fuad@tims.co.id']);

        $this->assertSame(['fuad.fahrudin@rne.co.id'], $this->mailInbox->fetchedEmails);
        $this->assertSame($rneAccount->id, session('business_mail_business_account_id'));
        $this->assertNotNull($rneAccount->refresh()->last_login_at);
    }

    public function test_accounts_on_the_same_domain_are_ordered_by_email(): void
    {
        $user = $this->createBusinessEmailUser();
        $this->createPersonalAccount($user, 'zaki@rne.co.id');
        $this->createPersonalAccount($user, 'budi@rne.co.id');

        $this->actingAs($user)
            ->get(route('business-email.inbox'))
            ->assertOk();

        $this->assertSame(['budi@rne.co.id'], $this->mailInbox->fetchedEmails);
    }

    public function test_manually_switched_account_is_kept_while_browsing(): void
    {
        $user = $this->createBusinessEmailUser();
        $this->createPersonalAccount($user, 'fuad.fahrudin@rne.co.id');
        $timsAccount = $this->createPersonalAccount($user, 'fuad@tims.co.id');

        $this->actingAs($user)
            ->post(route('business-email.select'), ['mail_business_account_id' => $timsAccount->id])
            ->assertRedirect(route('business-email.inbox'));

        $this->actingAs($user)
            ->get(route('business-email.inbox'))
            ->assertOk();

        $this->assertSame(['fuad@tims.co.id'], $this->mailInbox->fetchedEmails);
    }

    public function test_opening_business_email_again_returns_to_the_default_account(): void
    {
        $user = $this->createBusinessEmailUser();
        $this->createPersonalAccount($user, 'fuad.fahrudin@rne.co.id');
        $timsAccount = $this->createPersonalAccount($user, 'fuad@tims.co.id');

        $this->actingAs($user)
            ->withSession([
                'business_mail_business_account_id' => $timsAccount->id,
                'business_mail_business_account_selected' => true,
            ])
            ->followingRedirects()
            ->get(route('business-email.index'))
            ->assertOk();

        $this->assertSame(['fuad.fahrudin@rne.co.id'], $this->mailInbox->fetchedEmails);
    }

    public function test_default_selection_skips_inactive_and_other_employee_accounts(): void
    {
        $user = $this->createBusinessEmailUser();
        $otherUser = $this->createBusinessEmailUser();
        $this->createPersonalAccount($otherUser, 'other@aaa.co.id');
        $this->createPersonalAccount($user, 'inactive@abc.co.id', isActive: false);
        $this->createPersonalAccount($user, 'fuad@tims.co.id');

        $this->actingAs($user)
            ->get(route('business-email.inbox'))
            ->assertOk()
            ->assertDontSee('other@aaa.co.id')
            ->assertDontSee('inactive@abc.co.id');

        $this->assertSame(['fuad@tims.co.id'], $this->mailInbox->fetchedEmails);
    }

    public function test_user_without_accounts_sees_contact_administrator_message(): void
    {
        $this->actingAs($this->createBusinessEmailUser())
            ->get(route('business-email.inbox'))
            ->assertOk()
            ->assertSee('Hubungi administrator untuk mendapatkan akses email personal business');

        $this->assertSame([], $this->mailInbox->fetchedEmails);
        $this->assertNull(session('business_mail_business_account_id'));
    }

    public function test_email_management_still_requires_manual_account_selection(): void
    {
        MailAccessAccount::query()->create([
            'email' => 'recruitment@rne.co.id',
            'type' => MailAccessAccount::TYPE_DEPARTMENT,
            'pin' => Hash::make('1234'),
            'is_active' => true,
        ]);

        $user = User::factory()->superAdministrator()->create();
        $user->assignRole(Role::query()->firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']));

        $this->actingAs($user)
            ->get(route('applicant.email.inbox'))
            ->assertOk()
            ->assertSee('Pilih email account terlebih dahulu.');

        $this->assertSame([], $this->mailInbox->fetchedEmails);
    }

    private function createBusinessEmailUser(): User
    {
        return User::factory()->activeEmployee(['view-business-email'])->create();
    }

    private function createPersonalAccount(User $user, string $email, bool $isActive = true): MailAccessAccount
    {
        return MailAccessAccount::query()->create([
            'employee_id' => $user->employee->id,
            'email' => $email,
            'type' => MailAccessAccount::TYPE_PERSONAL,
            'pin' => Hash::make('1234'),
            'is_active' => $isActive,
        ]);
    }
}
