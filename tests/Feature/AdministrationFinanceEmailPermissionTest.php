<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationFinanceEmailPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_gives_the_new_permission_to_positions_that_had_email_management(): void
    {
        $emailManagementPermission = Permission::query()->firstOrCreate(['name' => 'view-email-management', 'guard_name' => 'web']);
        $administrator = Position::query()->create(['name' => 'Office Administrator']);
        $administrator->permissions()->sync([$emailManagementPermission->uuid]);
        $staff = Position::query()->create(['name' => 'Office Staff']);

        $migration = require database_path('migrations/2026_10_22_112039_add_view_administration_finance_email_permission.php');
        $migration->up();
        $migration->up();

        $administrationFinanceEmailPermission = Permission::query()->where('name', 'view-administration-finance-email')->sole();

        $this->assertDatabaseHas('position_has_permissions', [
            'position_id' => $administrator->id,
            'permission_id' => $administrationFinanceEmailPermission->uuid,
        ]);
        $this->assertDatabaseMissing('position_has_permissions', [
            'position_id' => $staff->id,
            'permission_id' => $administrationFinanceEmailPermission->uuid,
        ]);
        $this->assertSame(1, $administrator->permissions()->where('name', 'view-administration-finance-email')->count());
    }

    public function test_assign_permission_page_lists_the_administration_finance_email_permission(): void
    {
        $user = User::factory()->superAdministrator()->create();

        $this->actingAs($user)
            ->get(route('authorization.access-menus'))
            ->assertOk()
            ->assertSee('Administration &amp; Finance - Email', false)
            ->assertSee('Admin Management - Email Management')
            ->assertSee('view-administration-finance-email');
    }

    public function test_email_management_permission_alone_no_longer_opens_administration_finance_email(): void
    {
        $emailManager = User::factory()->activeEmployee(['view-email-management'])->create();
        $financeStaff = User::factory()->activeEmployee(['view-administration-finance-email'])->create();

        $this->actingAs($emailManager)
            ->get(route('administration-finance.email.inbox'))
            ->assertForbidden();

        $this->actingAs($financeStaff)
            ->get(route('administration-finance.email.inbox'))
            ->assertOk();

        $this->actingAs($financeStaff)
            ->get(route('email-management.index'))
            ->assertForbidden();
    }
}
