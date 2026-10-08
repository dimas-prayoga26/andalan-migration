<?php

namespace Tests\Feature;

use App\Http\Controllers\EmailManagementController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class EmailManagementTest extends TestCase
{
    public function test_email_management_routes_are_registered_with_position_permission(): void
    {
        $indexRoute = Route::getRoutes()->getByName('email-management.index');
        $storeAccountRoute = Route::getRoutes()->getByName('email-management.accounts.store');
        $updateAccountRoute = Route::getRoutes()->getByName('email-management.accounts.update');
        $destroyAccountRoute = Route::getRoutes()->getByName('email-management.accounts.destroy');
        $storeTakeoverRoute = Route::getRoutes()->getByName('email-management.takeovers.store');
        $updateTakeoverRoute = Route::getRoutes()->getByName('email-management.takeovers.update');
        $revokeTakeoverRoute = Route::getRoutes()->getByName('email-management.takeovers.revoke');
        $destroyTakeoverRoute = Route::getRoutes()->getByName('email-management.takeovers.destroy');

        $this->assertNotNull($indexRoute);
        $this->assertSame('email-management', $indexRoute?->uri());
        $this->assertSame(EmailManagementController::class.'@index', $indexRoute?->getActionName());
        $this->assertContains('position.permission:view-email-management', $indexRoute?->gatherMiddleware() ?? []);
        $this->assertSame('email-management/accounts', $storeAccountRoute?->uri());
        $this->assertContains('POST', $storeAccountRoute?->methods() ?? []);
        $this->assertSame('email-management/accounts/{mailAccessAccount}', $updateAccountRoute?->uri());
        $this->assertContains('PATCH', $updateAccountRoute?->methods() ?? []);
        $this->assertSame('email-management/accounts/{mailAccessAccount}', $destroyAccountRoute?->uri());
        $this->assertContains('DELETE', $destroyAccountRoute?->methods() ?? []);
        $this->assertSame('email-management/takeovers', $storeTakeoverRoute?->uri());
        $this->assertContains('POST', $storeTakeoverRoute?->methods() ?? []);
        $this->assertSame('email-management/takeovers/{mailAccountTakeover}', $updateTakeoverRoute?->uri());
        $this->assertContains('PATCH', $updateTakeoverRoute?->methods() ?? []);
        $this->assertSame('email-management/takeovers/{mailAccountTakeover}/revoke', $revokeTakeoverRoute?->uri());
        $this->assertContains('PATCH', $revokeTakeoverRoute?->methods() ?? []);
        $this->assertSame('email-management/takeovers/{mailAccountTakeover}', $destroyTakeoverRoute?->uri());
        $this->assertContains('DELETE', $destroyTakeoverRoute?->methods() ?? []);
    }

    public function test_email_management_permission_is_registered_for_admin_menu(): void
    {
        $positionPermissionSeeder = File::get(database_path('seeders/PositionPermissionSeeder.php'));
        $emailManagementPermissionMigration = File::get(database_path('migrations/2026_10_07_101119_add_view_email_management_permission.php'));
        $authorizationController = File::get(app_path('Http/Controllers/AuthorizationController.php'));
        $sidebar = File::get(resource_path('views/layouts/sidebar.blade.php'));

        $this->assertStringContainsString("'permission' => 'view-email-management'", $positionPermissionSeeder);
        $this->assertStringContainsString('Position::KEY_ADMINISTRATOR => $allPermissionsWithoutPic', $positionPermissionSeeder);
        $this->assertStringContainsString('->whereSystemKey(Position::KEY_SUPER_ADMINISTRATOR)', $positionPermissionSeeder);
        $this->assertStringContainsString("private const KEY_ADMINISTRATOR = 'administrator';", $emailManagementPermissionMigration);
        $this->assertStringContainsString("'name' => 'view-email-management'", $emailManagementPermissionMigration);
        $this->assertStringContainsString("->where('system_key', self::KEY_ADMINISTRATOR)", $emailManagementPermissionMigration);
        $this->assertStringContainsString("'view-email-management' => ['section' => 'Admin Management', 'label' => 'Email Management']", $authorizationController);
        $this->assertStringContainsString('$canViewEmailManagementMenu', $sidebar);
        $this->assertStringContainsString("canViewSidebarMenu('view-email-management')", $sidebar);
        $this->assertStringContainsString("route('email-management.index')", $sidebar);
        $this->assertStringContainsString('Email Management', $sidebar);
    }

    public function test_email_management_controller_models_requests_and_view_are_wired(): void
    {
        $controller = File::get(app_path('Http/Controllers/EmailManagementController.php'));
        $companyModel = File::get(app_path('Models/Company.php'));
        $employeeModel = File::get(app_path('Models/Employee.php'));
        $mailAccessAccountModel = File::get(app_path('Models/MailAccessAccount.php'));
        $mailAccountTakeoverModel = File::get(app_path('Models/MailAccountTakeover.php'));
        $storeAccountRequest = File::get(app_path('Http/Requests/Mail/StoreMailAccessAccountRequest.php'));
        $updateAccountRequest = File::get(app_path('Http/Requests/Mail/UpdateMailAccessAccountRequest.php'));
        $storeTakeoverRequest = File::get(app_path('Http/Requests/Mail/StoreMailAccountTakeoverRequest.php'));
        $view = File::get(resource_path('views/email-management/index.blade.php'));

        $this->assertTrue(View::exists('email-management.index'));
        $this->assertStringContainsString("view('email-management.index'", $controller);
        $this->assertStringContainsString('storeAccount(StoreMailAccessAccountRequest $request)', $controller);
        $this->assertStringContainsString('updateAccount(UpdateMailAccessAccountRequest $request, MailAccessAccount $mailAccessAccount)', $controller);
        $this->assertStringContainsString('destroyAccount(Request $request, MailAccessAccount $mailAccessAccount)', $controller);
        $this->assertStringContainsString('protectedMailAccountEmails', $controller);
        $this->assertStringContainsString('visibleMailAccessAccountQuery', $controller);
        $this->assertStringContainsString('constrainVisibleMailAccessAccountQuery', $controller);
        $this->assertStringContainsString('canManageSensitiveMailAccounts', $controller);
        $this->assertStringContainsString('canManageMailAccessAccount', $controller);
        $this->assertStringContainsString('mailTypeOptions', $controller);
        $this->assertStringContainsString('mailTypeLabels', $controller);
        $this->assertStringContainsString('takeoverEmployeeOptions', $controller);
        $this->assertStringContainsString('mailAccessAccountStaffLabel', $controller);
        $this->assertStringContainsString('employeeBusinessEmailLabel', $controller);
        $this->assertStringContainsString('staffEmailLabel', $controller);
        $this->assertStringContainsString('mailAccessAccountOwnerIsAvailable', $controller);
        $this->assertStringContainsString('takeoverTargetEmployeeIsAvailable', $controller);
        $this->assertStringContainsString('Company::query()->activeForMailManagement()->orderBy', $controller);
        $this->assertStringContainsString('Employee::query()'."\n            ->activeForMailManagement()", $controller);
        $this->assertStringContainsString("orWhereHas('company', fn (Builder \$query): Builder => \$query->activeForMailManagement())", $controller);
        $this->assertStringContainsString("orWhereHas('employee', fn (Builder \$query): Builder => \$query->activeForMailManagement())", $controller);
        $this->assertStringContainsString("whereHas('targetEmployee', fn (Builder \$query): Builder => \$query->activeForMailManagement())", $controller);
        $this->assertStringContainsString("whereIn('type', array_keys(\$this->mailTypeOptions(\$request)))", $controller);
        $this->assertStringContainsString('MailAccessAccount::TYPE_DEPARTMENT', $controller);
        $this->assertStringNotContainsString('return MailAccessAccount::typeOptions();', $controller);
        $this->assertStringContainsString('DEFAULT_MAIL_ACCESS_PIN', $controller);
        $this->assertStringContainsString('Hash::make(self::DEFAULT_MAIL_ACCESS_PIN)', $controller);
        $this->assertStringContainsString("\$data['is_active'] = true;", $controller);
        $this->assertStringContainsString('mailAccessAccountData', $controller);
        $this->assertStringContainsString('isSuperAdministrator()', $controller);
        $this->assertStringContainsString('MailAccessAccount::TYPE_PERSONAL', $controller);
        $this->assertStringContainsString("whereHas(\n                'mailAccessAccount'", $controller);
        $this->assertStringContainsString('Sensitive email account hanya bisa dikelola Super Administrator.', $controller);
        $this->assertStringContainsString('MailAccessAccount::configuredInboxEmails()', $controller);
        $this->assertStringContainsString('->visibleForMailAccess()', $controller);
        $this->assertStringContainsString("config('career_brands.brands'", $controller);
        $this->assertStringContainsString('Email dari config/env tidak bisa dihapus', $controller);
        $this->assertStringContainsString('storeTakeover(StoreMailAccountTakeoverRequest $request)', $controller);
        $this->assertStringContainsString('updateTakeover(StoreMailAccountTakeoverRequest $request, MailAccountTakeover $mailAccountTakeover)', $controller);
        $this->assertStringContainsString('revokeTakeover(Request $request, MailAccountTakeover $mailAccountTakeover)', $controller);
        $this->assertStringContainsString('destroyTakeover(MailAccountTakeover $mailAccountTakeover)', $controller);
        $this->assertStringContainsString('Hash::make', $controller);
        $this->assertStringContainsString('public function employee(): BelongsTo', $mailAccessAccountModel);
        $this->assertStringContainsString('public function takeovers(): HasMany', $mailAccessAccountModel);
        $this->assertStringContainsString("protected \$hidden = [\n        'pin'", $mailAccessAccountModel);
        $this->assertStringContainsString('public function scopeActiveForMailManagement(Builder $query): Builder', $companyModel);
        $this->assertStringContainsString("->where('is_active', true)", $companyModel);
        $this->assertStringContainsString('LOWER(COALESCE(name, "")) NOT LIKE', $companyModel);
        $this->assertStringContainsString('LOWER(COALESCE(legal_name, "")) NOT LIKE', $companyModel);
        $this->assertStringContainsString('public function scopeActiveForMailManagement(Builder $query): Builder', $employeeModel);
        $this->assertStringContainsString('LOWER(COALESCE(status, "")) = ?', $employeeModel);
        $this->assertStringContainsString("whereHas('user', fn (Builder \$query): Builder => \$query->where('is_active', true))", $employeeModel);
        $this->assertStringContainsString("whereHas('deployment', fn (Builder \$query): Builder => \$query->whereRaw('LOWER(COALESCE(status, \"\")) = ?', ['active']))", $employeeModel);
        $this->assertStringContainsString('public function scopeReadable(Builder $query): Builder', $mailAccountTakeoverModel);
        $this->assertStringContainsString("hasAnyPositionPermission(['view-email-management'])", $storeAccountRequest);
        $this->assertStringContainsString("hasAnyPositionPermission(['view-email-management'])", $updateAccountRequest);
        $this->assertStringContainsString("hasAnyPositionPermission(['view-email-management'])", $storeTakeoverRequest);
        $this->assertStringNotContainsString("'starts_at' =>", $storeTakeoverRequest);
        $this->assertStringNotContainsString("'ends_at' =>", $storeTakeoverRequest);
        $this->assertStringNotContainsString("'can_read' =>", $storeTakeoverRequest);
        $this->assertStringNotContainsString("'reason' =>", $storeTakeoverRequest);
        $this->assertStringNotContainsString("'pin' =>", $storeAccountRequest);
        $this->assertStringNotContainsString("'pin' =>", $updateAccountRequest);
        $this->assertStringNotContainsString("'is_active' =>", $storeAccountRequest);
        $this->assertStringNotContainsString("'is_active' =>", $updateAccountRequest);
        $this->assertStringContainsString('Rule::requiredIf', $storeAccountRequest);
        $this->assertStringContainsString('Rule::requiredIf', $updateAccountRequest);
        $this->assertStringContainsString('allowedMailTypeValues', $storeAccountRequest);
        $this->assertStringContainsString('allowedMailTypeValues', $updateAccountRequest);
        $this->assertStringContainsString('isSuperAdministrator()', $storeAccountRequest);
        $this->assertStringContainsString('isSuperAdministrator()', $updateAccountRequest);
        $this->assertStringContainsString('MailAccessAccount::TYPE_DEPARTMENT', $storeAccountRequest);
        $this->assertStringContainsString('MailAccessAccount::TYPE_DEPARTMENT', $updateAccountRequest);
        $this->assertStringContainsString("route('email-management.accounts.store')", $view);
        $this->assertStringContainsString("route('email-management.accounts.update'", $view);
        $this->assertStringContainsString("route('email-management.accounts.destroy'", $view);
        $this->assertStringContainsString("route('email-management.takeovers.store')", $view);
        $this->assertStringContainsString("route('email-management.takeovers.update'", $view);
        $this->assertStringContainsString("route('email-management.takeovers.destroy'", $view);
        $this->assertStringContainsString('data-bs-toggle="modal"', $view);
        $this->assertStringContainsString('$protectedMailAccountEmails->contains', $view);
        $this->assertStringContainsString('@if (! $isConfigManagedAccount)', $view);
        $this->assertStringContainsString('email-management-table-card', $view);
        $this->assertStringNotContainsString('Kelola akun email yang bisa dipakai dan ditakeover.', $view);
        $this->assertStringContainsString('table table-sm mb-0 table-bottom-borderless table-striped align-middle', $view);
        $this->assertStringContainsString("@include('settings.partials.pagination'", $view);
        $this->assertStringContainsString('Create Takeover Access', $view);

        $accountForm = File::get(resource_path('views/email-management/partials/account-form.blade.php'));
        $emailManagementJs = File::get(public_path('assets/js/email-management.js'));

        $this->assertStringContainsString('data-mail-account-type', $accountForm);
        $this->assertStringContainsString('data-mail-account-company-field', $accountForm);
        $this->assertStringContainsString('data-mail-account-owner-field', $accountForm);
        $this->assertStringContainsString('form-select js-skip-selectpicker', $accountForm);
        $this->assertStringNotContainsString('name="pin"', $accountForm);
        $this->assertStringNotContainsString('name="is_active"', $accountForm);
        $this->assertStringContainsString('email-management.js', $view);
        $this->assertStringContainsString("var personalType = 'personal';", $emailManagementJs);
        $this->assertStringContainsString("companyField.classList.toggle('d-none', isPersonal);", $emailManagementJs);
        $this->assertStringContainsString("ownerField.classList.toggle('d-none', ! isPersonal);", $emailManagementJs);

        $takeoverForm = File::get(resource_path('views/email-management/partials/takeover-form.blade.php'));

        $this->assertStringNotContainsString('name="starts_at"', $takeoverForm);
        $this->assertStringNotContainsString('name="ends_at"', $takeoverForm);
        $this->assertStringNotContainsString('name="reason"', $takeoverForm);
        $this->assertStringNotContainsString('name="can_read"', $takeoverForm);
        $this->assertStringContainsString('<div class="col-12">', $takeoverForm);
        $this->assertStringNotContainsString('col-md-6', $takeoverForm);
        $this->assertStringContainsString('Staff Lama', $takeoverForm);
        $this->assertStringContainsString('$account[\'label\']', $takeoverForm);
        $this->assertStringContainsString('$takeoverEmployeeOptions', $view);
        $this->assertStringContainsString('$sourceLabel', $view);
        $this->assertStringContainsString('$targetLabel', $view);
        $this->assertStringContainsString("'can_read' => true", $controller);
        $this->assertStringContainsString("'starts_at' => null", $controller);
        $this->assertStringContainsString("'ends_at' => null", $controller);
        $this->assertStringContainsString("'reason' => null", $controller);
        $this->assertStringContainsString("'revoked_at' => null", $controller);
        $this->assertStringContainsString("'revoked_by_employee_id' => null", $controller);
    }
}
