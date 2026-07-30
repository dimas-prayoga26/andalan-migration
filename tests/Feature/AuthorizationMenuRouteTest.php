<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthorizationController;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AuthorizationMenuRouteTest extends TestCase
{
    public function test_authorization_route_uses_controller(): void
    {
        $route = Route::getRoutes()->getByName('authorization');
        $createRoute = Route::getRoutes()->getByName('authorization.create');
        $storeRoute = Route::getRoutes()->getByName('authorization.store');
        $accessMenusRoute = Route::getRoutes()->getByName('authorization.access-menus');
        $updateRoute = Route::getRoutes()->getByName('authorization.position-permissions.update');
        $showRoute = Route::getRoutes()->getByName('authorization.show');
        $editRoute = Route::getRoutes()->getByName('authorization.edit');
        $dataEmployeeUpdateRoute = Route::getRoutes()->getByName('authorization.update');
        $destroyRoute = Route::getRoutes()->getByName('authorization.destroy');

        $this->assertNotNull($route);
        $this->assertSame('authorization', $route?->uri());
        $this->assertSame(AuthorizationController::class.'@index', $route?->getActionName());
        $this->assertSame('authorization/create', $createRoute?->uri());
        $this->assertSame(AuthorizationController::class.'@create', $createRoute?->getActionName());
        $this->assertSame('authorization', $storeRoute?->uri());
        $this->assertContains('POST', $storeRoute?->methods() ?? []);
        $this->assertNotNull($accessMenusRoute);
        $this->assertSame('authorization/access-menus', $accessMenusRoute?->uri());
        $this->assertSame(AuthorizationController::class.'@accessMenus', $accessMenusRoute?->getActionName());
        $this->assertNotNull($updateRoute);
        $this->assertSame('authorization/position-permissions', $updateRoute?->uri());
        $this->assertContains('POST', $updateRoute?->methods() ?? []);
        $this->assertSame(AuthorizationController::class.'@updatePositionPermissions', $updateRoute?->getActionName());
        $this->assertSame('authorization/{employee}', $showRoute?->uri());
        $this->assertSame('authorization/{employee}/edit', $editRoute?->uri());
        $this->assertSame('authorization/{employee}', $dataEmployeeUpdateRoute?->uri());
        $this->assertSame('authorization/{employee}', $destroyRoute?->uri());
        $this->assertContains('DELETE', $destroyRoute?->methods() ?? []);
    }

    public function test_authorization_page_view_is_registered(): void
    {
        $controller = File::get(app_path('Http/Controllers/AuthorizationController.php'));
        $userModel = File::get(app_path('Models/User.php'));
        $positionPermissionMiddleware = File::get(app_path('Http/Middleware/EnsurePositionPermission.php'));
        $bootstrapApp = File::get(base_path('bootstrap/app.php'));
        $routes = File::get(base_path('routes/web.php'));
        $authorizationView = File::get(resource_path('views/authorization/index.blade.php'));
        $authorizationFormView = File::get(resource_path('views/authorization/form.blade.php'));
        $authorizationShowView = File::get(resource_path('views/authorization/show.blade.php'));
        $accessMenusView = File::get(resource_path('views/authorization/access-menus.blade.php'));
        $customJs = File::get(public_path('assets/js/custom.js'));

        $this->assertTrue(View::exists('authorization.index'));
        $this->assertTrue(View::exists('authorization.form'));
        $this->assertTrue(View::exists('authorization.show'));
        $this->assertTrue(View::exists('authorization.access-menus'));
        $this->assertStringContainsString("view('authorization.index'", $controller);
        $this->assertStringContainsString("view('authorization.access-menus'", $controller);
        $this->assertStringContainsString('accessMenus', $controller);
        $this->assertStringContainsString('authorizationUsersFor', $controller);
        $this->assertStringContainsString("->where('is_active', true)", $controller);
        $this->assertStringContainsString("->whereDoesntHave('roles'", $controller);
        $this->assertStringContainsString("->where('name', 'superuser')", $controller);
        $this->assertStringContainsString("'authorization_company_name' => \$this->authorizationCompanyNameSubquery()", $controller);
        $this->assertStringContainsString("'authorization_pic_name' => \$this->authorizationPicNameSubquery()", $controller);
        $this->assertStringContainsString("->orderBy('authorization_company_name')", $controller);
        $this->assertStringContainsString("->orderBy('authorization_pic_name')", $controller);
        $this->assertStringContainsString("->orderBy('authorization_employee_name')", $controller);
        $this->assertStringContainsString('updatePositionPermissions', $controller);
        $this->assertStringContainsString('permission_positions', $controller);
        $this->assertStringContainsString('positions()->sync', $controller);
        $this->assertStringContainsString('position.permission:view-authorization', $routes);
        $this->assertStringContainsString("'position.permission' => EnsurePositionPermission::class", $bootstrapApp);
        $this->assertStringContainsString('hasAnyPositionPermission', $userModel);
        $this->assertStringContainsString('abort_unless', $positionPermissionMiddleware);
        $this->assertStringContainsString('hasAnyPositionPermission($permissionNames)', $positionPermissionMiddleware);
        $this->assertStringContainsString("route('authorization.access-menus')", $controller);
        $this->assertStringContainsString('current_company_id', $controller);
        $this->assertStringContainsString('isSuperuser', $controller);
        $this->assertStringContainsString('syncDataEmployeeRelations', $controller);
        $this->assertStringContainsString("'view-authorization' => ['section' => 'HR Management', 'label' => 'Employee Data']", $controller);
        $this->assertStringContainsString('Employee Data', $authorizationView);
        $this->assertStringContainsString('Employee List', $authorizationView);
        $this->assertStringContainsString("route('authorization.create')", $authorizationView);
        $this->assertStringContainsString('Add Employee', $authorizationView);
        $this->assertStringContainsString('Employee, deployment, identity, and PIC data.', $authorizationView);
        $this->assertStringContainsString('session(\'status\')', $authorizationView);
        $this->assertStringNotContainsString('Create User', $authorizationView);
        $this->assertStringContainsString('class="authorization-list-actions"', $authorizationView);
        $this->assertStringContainsString('class="authorization-employee-search"', $authorizationView);
        $this->assertStringContainsString('method="GET"', $authorizationView);
        $this->assertStringContainsString('id="authorizationEmployeeSearch"', $authorizationView);
        $this->assertStringContainsString('name="search"', $authorizationView);
        $this->assertStringContainsString('type="search"', $authorizationView);
        $this->assertStringContainsString('placeholder="Search employee"', $authorizationView);
        $this->assertStringContainsString('id="authorizationEmployeeTable"', $authorizationView);
        $this->assertStringContainsString('class="card authorization-table-card"', $authorizationView);
        $this->assertStringContainsString('table table-sm mb-0 table-bottom-borderless table-striped', $authorizationView);
        $this->assertStringContainsString('authorization-table-footer dataTables_wrapper no-footer', $authorizationView);
        $this->assertStringContainsString('dataTables_info', $authorizationView);
        $this->assertStringContainsString('dataTables_paginate paging_simple_numbers', $authorizationView);
        $this->assertStringContainsString('paginate_button previous', $authorizationView);
        $this->assertStringContainsString('paginate_button next', $authorizationView);
        $this->assertStringContainsString('$users->getUrlRange(1, $users->lastPage())', $authorizationView);
        $this->assertStringContainsString('$users->total()', $authorizationView);
        $this->assertStringNotContainsString("searchInput.addEventListener('input'", $authorizationView);
        $this->assertStringContainsString("\$request->string('search')->trim()->toString()", $controller);
        $this->assertStringContainsString('->paginate(10)', $controller);
        $this->assertStringContainsString('->withQueryString()', $controller);
        $this->assertStringContainsString("->orWhereHas('employee'", $controller);
        $this->assertStringContainsString('if (! $this->canManageAuthorization($viewer))', $controller);
        $this->assertStringContainsString('@forelse ($users as $user)', $authorizationView);
        $this->assertStringContainsString('<th>Name</th>', $authorizationView);
        $this->assertStringContainsString('<th>NIK</th>', $authorizationView);
        $this->assertStringContainsString('<th>Position</th>', $authorizationView);
        $this->assertStringContainsString('<th>Company</th>', $authorizationView);
        $this->assertStringContainsString('<th>PIC</th>', $authorizationView);
        $this->assertStringContainsString('Detail</a>', $authorizationView);
        $this->assertStringContainsString('Update</a>', $authorizationView);
        $this->assertStringContainsString('Delete</button>', $authorizationView);
        $this->assertStringNotContainsString('Manage Access', $authorizationView);
        $this->assertStringNotContainsString('<th>Role</th>', $authorizationView);
        $this->assertStringNotContainsString('<th>Department</th>', $authorizationView);
        $this->assertStringNotContainsString("\$user['email']", $authorizationView);
        $this->assertStringContainsString('bpjs_ketenagakerjaan', $authorizationFormView);
        $this->assertStringContainsString('bpjs_kesehatan', $authorizationFormView);
        $this->assertStringContainsString('npwp', $authorizationFormView);
        $this->assertStringContainsString('pic_employee_id', $authorizationFormView);
        $this->assertStringContainsString('js-data-employee-date', $authorizationFormView);
        $this->assertStringContainsString("format: 'DD/MM/YYYY'", $authorizationFormView);
        $this->assertStringContainsString('name="employee_status" value="Active"', $authorizationFormView);
        $this->assertStringContainsString('Add Employee', $authorizationFormView);
        $this->assertStringContainsString('Update Employee', $authorizationFormView);
        $this->assertStringContainsString('Default password for the new employee account is <strong>passwrod</strong>.', $authorizationFormView);
        $this->assertStringContainsString('Employee Status</label>', $authorizationFormView);
        $this->assertStringContainsString('Full Name', $authorizationFormView);
        $this->assertStringContainsString('Place of Birth', $authorizationFormView);
        $this->assertStringContainsString('Date of Birth', $authorizationFormView);
        $this->assertStringContainsString('ID Number / NIK', $authorizationFormView);
        $this->assertStringContainsString('Company', $authorizationFormView);
        $this->assertStringContainsString('Division', $authorizationFormView);
        $this->assertStringContainsString('PIC / Person in Charge', $authorizationFormView);
        $this->assertStringContainsString('The data is not valid.', $authorizationFormView);
        $this->assertStringNotContainsString('name="password"', $authorizationFormView);
        $this->assertStringNotContainsString('name="employee_code"', $authorizationFormView);
        $this->assertStringNotContainsString('<label class="form-label">Password', $authorizationFormView);
        $this->assertStringNotContainsString('<label class="form-label">Employee Code</label>', $authorizationFormView);
        $this->assertStringNotContainsString('@foreach ([\'Active\', \'Pending\', \'Inactive\'] as $status)', $authorizationFormView);
        $this->assertStringNotContainsString('type="date"', $authorizationFormView);
        $this->assertStringContainsString('Employee Details', $authorizationShowView);
        $this->assertStringContainsString('session(\'status\')', $authorizationShowView);
        $this->assertStringContainsString('Employment BPJS', $authorizationShowView);
        $this->assertStringContainsString('Healthcare BPJS', $authorizationShowView);
        $this->assertStringContainsString("private const DEFAULT_EMPLOYEE_PASSWORD = 'passwrod';", $controller);
        $this->assertStringContainsString('Hash::make(self::DEFAULT_EMPLOYEE_PASSWORD)', $controller);
        $this->assertStringContainsString('$user->assignRole($this->defaultStaffRole());', $controller);
        $this->assertStringContainsString('private function defaultStaffRole(): Role', $controller);
        $this->assertStringContainsString('Role::query()->firstOrCreate', $controller);
        $this->assertStringContainsString('private function generateEmployeeCode(User $user): string', $controller);
        $this->assertStringContainsString("'employee_code' => \$this->generateEmployeeCode(\$user)", $controller);
        $this->assertStringContainsString('Employee has been added successfully.', $controller);
        $this->assertStringContainsString('Employee has been updated successfully.', $controller);
        $this->assertStringContainsString('Assign Permission', $accessMenusView);
        $this->assertStringContainsString('Employee List', $accessMenusView);
        $this->assertStringContainsString('Assign positions that can access each menu.', $accessMenusView);
        $this->assertStringContainsString("route('authorization')", $accessMenusView);
        $this->assertStringContainsString('<th>Menu</th>', $accessMenusView);
        $this->assertStringNotContainsString('<th>Section</th>', $accessMenusView);
        $this->assertStringNotContainsString("{{ \$permission['section'] }}", $accessMenusView);
        $this->assertStringContainsString('Assign Position', $accessMenusView);
        $this->assertStringContainsString('@forelse ($menuPermissions as $permission)', $accessMenusView);
        $this->assertStringContainsString('permission_positions[{{ $permission[\'id\'] }}][]', $accessMenusView);
        $this->assertStringContainsString('multiple', $accessMenusView);
        $this->assertStringContainsString('authorization-position-select', $accessMenusView);
        $this->assertStringContainsString('authorization-select2', $accessMenusView);
        $this->assertStringContainsString('js-skip-selectpicker', $accessMenusView);
        $this->assertStringContainsString('.authorization-access-form .bootstrap-select', $accessMenusView);
        $this->assertStringContainsString('initializeAuthorizationSelect2', $accessMenusView);
        $this->assertStringContainsString('formatPositionOption', $accessMenusView);
        $this->assertStringContainsString('aria-selected=true', $accessMenusView);
        $this->assertStringContainsString('var(--bs-primary)', $accessMenusView);
        $this->assertStringNotContainsString('#b91444', $accessMenusView);
        $this->assertStringNotContainsString('authorization-select2-option-check', $accessMenusView);
        $this->assertStringNotContainsString('fa-solid fa-check', $accessMenusView);
        $this->assertStringContainsString('templateResult: formatPositionOption', $accessMenusView);
        $this->assertStringContainsString('select2.full.min.js', $accessMenusView);
        $this->assertStringContainsString("not('.js-skip-selectpicker')", $customJs);
        $this->assertStringContainsString("route('authorization.position-permissions.update')", $accessMenusView);
        $this->assertStringContainsString('@csrf', $accessMenusView);
    }

    public function test_authorization_menu_is_registered_in_sidebar(): void
    {
        $sidebarView = File::get(resource_path('views/layouts/sidebar.blade.php'));
        $appServiceProvider = File::get(app_path('Providers/AppServiceProvider.php'));
        $sidebarPermissionComposer = File::get(app_path('View/Composers/SidebarPermissionComposer.php'));

        $this->assertStringContainsString('$isAuthorizationMenu', $sidebarView);
        $this->assertStringContainsString('$canViewAuthorizationMenu', $sidebarView);
        $this->assertStringContainsString("canViewSidebarMenu('view-authorization')", $sidebarView);
        $this->assertStringContainsString('@if ($canViewAuthorizationMenu)', $sidebarView);
        $this->assertStringContainsString("route('authorization')", $sidebarView);
        $this->assertStringNotContainsString("route('authorization.access-menus')", $sidebarView);
        $this->assertStringNotContainsString('Assign Permission', $sidebarView);
        $this->assertStringContainsString('Employee Data', $sidebarView);
        $this->assertStringNotContainsString('Zoom Meeting', $sidebarView);
        $this->assertStringNotContainsString('Employee Database', $sidebarView);
        $this->assertStringNotContainsString('Talent Acquisition', $sidebarView);
        $this->assertStringNotContainsString('Payroll', $sidebarView);
        $this->assertStringContainsString("View::composer('layouts.sidebar', SidebarPermissionComposer::class)", $appServiceProvider);
        $this->assertStringContainsString('hasAnyPositionPermission([$permissionName])', $sidebarPermissionComposer);
        $this->assertStringNotContainsString('positionPermissionNamesFor', $sidebarPermissionComposer);
    }

    public function test_assign_permission_tab_is_only_rendered_for_authorization_managers(): void
    {
        $authorizationIndex = File::get(resource_path('views/authorization/index.blade.php'));
        $authorizationController = File::get(app_path('Http/Controllers/AuthorizationController.php'));

        $this->assertStringContainsString('@if ($canManagePositionPermissions)', $authorizationIndex);
        $this->assertStringContainsString("route('authorization.access-menus')", $authorizationIndex);
        $this->assertStringContainsString('private function viewerCompanyId(User $user): ?string', $authorizationController);
        $this->assertStringContainsString('private function canManagePositionPermissions(User $user): bool', $authorizationController);
        $this->assertStringContainsString("=== 'chief operating officer'", $authorizationController);
        $this->assertStringNotContainsString('private function administratorCompanyId(User $user): ?string', $authorizationController);
    }

    public function test_sidebar_hides_menu_without_position_permission(): void
    {
        $sidebar = Blade::render(File::get(resource_path('views/layouts/sidebar.blade.php')), [
            'canViewSidebarMenu' => static fn (string $permissionName): bool => $permissionName === 'view-dashboard',
        ]);

        $this->assertStringContainsString('Dashboard', $sidebar);
        $this->assertStringNotContainsString('Employee Data </span>', $sidebar);
        $this->assertStringNotContainsString('Admin Attendance </span>', $sidebar);
    }

    public function test_sidebar_javascript_keeps_parent_menu_active_on_module_sub_routes(): void
    {
        $customJs = File::get(public_path('assets/js/custom.js'));

        $this->assertStringContainsString('var currentSidebarPath = currentPath;', $customJs);
        $this->assertStringContainsString("currentSidebarPath = '/attendance/overview';", $customJs);
        $this->assertStringContainsString("currentSidebarPath = '/admin-attendance/overview';", $customJs);
        $this->assertStringContainsString("currentSidebarPath = '/project-management/overview';", $customJs);
        $this->assertStringContainsString('var isExactMatch = currentSidebarPath === linkPath;', $customJs);
        $this->assertStringContainsString("currentSidebarPath.startsWith(linkPath + '/')", $customJs);
    }

    public function test_position_based_sidebar_permission_structure_is_registered(): void
    {
        $positionPermissionMigration = File::get(database_path('migrations/2026_06_21_163635_create_position_has_permissions_table.php'));
        $dropSidebarMenuMigration = File::get(database_path('migrations/2026_06_21_165133_drop_sidebar_menus_table.php'));
        $positionPermissionSeeder = File::get(database_path('seeders/PositionPermissionSeeder.php'));
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));
        $authorizationController = File::get(app_path('Http/Controllers/AuthorizationController.php'));
        $sidebar = File::get(resource_path('views/layouts/sidebar.blade.php'));

        $this->assertStringContainsString("Schema::dropIfExists('sidebar_menus')", $dropSidebarMenuMigration);
        $this->assertStringContainsString("Schema::create('position_has_permissions'", $positionPermissionMigration);
        $this->assertStringContainsString("constrained('positions', 'id')", $positionPermissionMigration);
        $this->assertStringContainsString("constrained('permissions', 'uuid')", $positionPermissionMigration);
        $this->assertStringContainsString('PositionPermissionSeeder::class', $databaseSeeder);
        $this->assertStringContainsString('view-authorization', $positionPermissionSeeder);
        $this->assertStringContainsString('syncPositionPermissions', $positionPermissionSeeder);
        $this->assertStringContainsString('menuPermissionData', $positionPermissionSeeder);
        $this->assertStringContainsString("'label' => 'Activity Calendar'", $positionPermissionSeeder);
        $this->assertStringContainsString("'label' => 'Activity Calendar'", $authorizationController);
        $this->assertStringContainsString('data-i18n="Activity Calendar">Activity Calendar', $sidebar);
        $this->assertStringNotContainsString('Google Calendar', $positionPermissionSeeder.$authorizationController.$sidebar);
        $this->assertStringContainsString("'Administrator' => \$allPermissionsWithoutPic", $positionPermissionSeeder);
        $this->assertStringContainsString("'Web Developer' => \$baseStaffPermissions", $positionPermissionSeeder);
        $this->assertStringNotContainsString("'Web Developer' => array_merge(\$baseStaffPermissions, ['view-authorization'])", $positionPermissionSeeder);
        $this->assertStringContainsString("->where('name', 'Super Administrator')", $positionPermissionSeeder);
        $this->assertStringContainsString('->permissions()->detach()', $positionPermissionSeeder);
        $this->assertStringNotContainsString("'System Administrator' =>", $positionPermissionSeeder);
    }
}
