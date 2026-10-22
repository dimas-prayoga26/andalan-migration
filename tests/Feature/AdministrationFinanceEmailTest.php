<?php

namespace Tests\Feature;

use App\Http\Controllers\MailController;
use App\Models\MailAccessAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdministrationFinanceEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_administration_finance_email_routes_reuse_the_mail_controller(): void
    {
        foreach ([
            'index' => ['administration-finance/email', 'index'],
            'select' => ['administration-finance/email/select', 'selectAccount'],
            'check' => ['administration-finance/email/check', 'checkEmail'],
            'login' => ['administration-finance/email/login', 'authenticate'],
            'inbox' => ['administration-finance/email/inbox', 'inbox'],
            'sent' => ['administration-finance/email/sent', 'sent'],
            'destroy' => ['administration-finance/email/delete', 'destroy'],
            'mark-all-read' => ['administration-finance/email/mark-all-read', 'markAllAsRead'],
            'compose' => ['administration-finance/email/compose', 'compose'],
            'send' => ['administration-finance/email/send', 'send'],
            'attachment' => ['administration-finance/email/read/{uid}/attachments/{attachment}', 'attachment'],
            'reply' => ['administration-finance/email/read/{uid}/reply', 'reply'],
            'read' => ['administration-finance/email/read/{uid?}', 'read'],
            'logout' => ['administration-finance/email/logout', 'logout'],
        ] as $routeSuffix => [$uri, $action]) {
            $route = Route::getRoutes()->getByName('administration-finance.email.'.$routeSuffix);

            $this->assertNotNull($route, $routeSuffix);
            $this->assertSame($uri, $route->uri());
            $this->assertSame(MailController::class.'@'.$action, $route->getActionName());
            $this->assertContains('position.permission:view-email-management', $route->gatherMiddleware());
        }
    }

    public function test_sidebar_shows_email_only_under_administration_finance(): void
    {
        $sidebar = File::get(resource_path('views/layouts/sidebar.blade.php'));

        $emailManagerSidebar = Blade::render($sidebar, [
            'canViewSidebarMenu' => static fn (string $permissionName): bool => in_array($permissionName, ['view-email-management', 'view-talent-acquisition'], true),
        ]);
        $staffSidebar = Blade::render($sidebar, [
            'canViewSidebarMenu' => static fn (string $permissionName): bool => $permissionName === 'view-talent-acquisition',
        ]);

        $this->assertStringContainsString('<strong>Administration & Finance</strong>', $emailManagerSidebar);
        $this->assertLessThan(
            strpos($emailManagerSidebar, 'href="'.route('administration-finance.email.index').'"'),
            strpos($emailManagerSidebar, '<strong>Administration & Finance</strong>'),
        );
        $this->assertStringContainsString('Job Vacancies</a>', $emailManagerSidebar);
        $this->assertSame(1, substr_count($emailManagerSidebar, 'href="'.route('administration-finance.email.index').'"'));
        $this->assertStringNotContainsString('Administration & Finance', $staffSidebar);
        $this->assertStringNotContainsString(route('administration-finance.email.index'), $staffSidebar);
    }

    public function test_administration_finance_email_page_keeps_its_own_links(): void
    {
        MailAccessAccount::query()->create([
            'email' => 'finance@rnb.co.id',
            'type' => MailAccessAccount::TYPE_DEPARTMENT,
            'pin' => Hash::make('1234'),
            'is_active' => true,
        ]);
        $user = User::factory()->superAdministrator()->create();
        $user->assignRole(Role::query()->firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']));

        $this->actingAs($user)
            ->get(route('administration-finance.email.index'))
            ->assertRedirect(route('administration-finance.email.inbox'));

        $response = $this->actingAs($user)->get(route('administration-finance.email.inbox'));

        $response->assertOk();
        $response->assertSee('Pilih email account terlebih dahulu.');
        $response->assertSee(route('administration-finance.email.select'), false);
    }

    public function test_old_talent_acquisition_email_link_redirects_to_administration_finance(): void
    {
        $user = User::factory()->superAdministrator()->create();

        $this->actingAs($user)
            ->get('/applicant/email')
            ->assertRedirect('/administration-finance/email');
    }
}
