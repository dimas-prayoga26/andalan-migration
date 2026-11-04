<?php

namespace Tests\Feature;

use App\Models\MailAccessAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailAccessAccountEmailDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_account_is_created_from_name_and_selected_company_domain(): void
    {
        $admin = User::factory()->activeEmployee(['view-email-management'])->create();
        $owner = User::factory()->activeEmployee()->create();

        $this->actingAs($admin)
            ->post(route('email-management.accounts.store'), [
                'email_local_part' => ' Dimas.Prayoga ',
                'email_domain' => 'tims.co.id',
                'type' => MailAccessAccount::TYPE_PERSONAL,
                'employee_id' => $owner->employee->id,
            ])
            ->assertRedirect(route('email-management.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mail_business_accounts', [
            'email' => 'dimas.prayoga@tims.co.id',
            'employee_id' => $owner->employee->id,
        ]);
    }

    public function test_unknown_domain_and_invalid_name_are_rejected(): void
    {
        $admin = User::factory()->activeEmployee(['view-email-management'])->create();
        $owner = User::factory()->activeEmployee()->create();

        $this->actingAs($admin)
            ->post(route('email-management.accounts.store'), [
                'email_local_part' => 'dimas@tims.co.id',
                'email_domain' => 'gmail.com',
                'type' => MailAccessAccount::TYPE_PERSONAL,
                'employee_id' => $owner->employee->id,
            ])
            ->assertSessionHasErrors(['email_local_part', 'email_domain']);

        $this->assertDatabaseCount('mail_business_accounts', 0);
    }

    public function test_existing_account_keeps_its_current_domain_when_updated(): void
    {
        $admin = User::factory()->activeEmployee(['view-email-management'])->create();
        $owner = User::factory()->activeEmployee()->create();
        $account = MailAccessAccount::query()->create([
            'email' => 'legacy@andalanku.com',
            'type' => MailAccessAccount::TYPE_PERSONAL,
            'employee_id' => $owner->employee->id,
            'pin' => 'secret',
            'is_active' => true,
        ]);

        $this->assertContains('andalanku.com', MailAccessAccount::selectableMailDomains($account)->all());
        $this->assertNotContains('andalanku.com', MailAccessAccount::selectableMailDomains()->all());
        $this->assertContains('inticoal.co.id', MailAccessAccount::selectableMailDomains()->all());

        $this->actingAs($admin)
            ->patch(route('email-management.accounts.update', $account), [
                'email_local_part' => 'renamed',
                'email_domain' => 'andalanku.com',
                'type' => MailAccessAccount::TYPE_PERSONAL,
                'employee_id' => $owner->employee->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('renamed@andalanku.com', $account->fresh()->email);
    }

    public function test_account_form_splits_the_email_into_name_and_domain_select(): void
    {
        $owner = User::factory()->activeEmployee()->create();
        $account = MailAccessAccount::query()->create([
            'email' => 'yoga@tims.co.id',
            'type' => MailAccessAccount::TYPE_PERSONAL,
            'employee_id' => $owner->employee->id,
            'pin' => 'secret',
            'is_active' => true,
        ]);

        $html = view('email-management.partials.account-form', [
            'account' => $account,
            'companies' => collect(),
            'employeeOptions' => [],
            'mailTypeOptions' => [MailAccessAccount::TYPE_PERSONAL => 'Personal'],
        ])->render();

        $this->assertStringContainsString('name="email_local_part"', $html);
        $this->assertStringContainsString('placeholder="admin"', $html);
        $this->assertStringContainsString('value="yoga"', $html);
        $this->assertStringContainsString('<option value="tims.co.id" selected>@tims.co.id</option>', $html);
        $this->assertStringContainsString('<option value="inticoal.co.id" >@inticoal.co.id</option>', $html);
        $this->assertStringNotContainsString('name="email"', $html);
    }
}
