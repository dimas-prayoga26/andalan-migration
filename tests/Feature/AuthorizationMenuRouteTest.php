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
        $accessMenusRoute = Route::getRoutes()->getByName('authorization.access-menus');
        $updateRoute = Route::getRoutes()->getByName('authorization.position-permissions.update');

        $this->assertNotNull($route);
        $this->assertSame('authorization', $route?->uri());
        $this->assertSame(AuthorizationController::class.'@index', $route?->getActionName());
        $this->assertNotNull($accessMenusRoute);
        $this->assertSame('authorization/access-menus', $accessMenusRoute?->uri());
        $this->assertSame(AuthorizationController::class.'@accessMenus', $accessMenusRoute?->getActionName());
        $this->assertNotNull($updateRoute);
        $this->assertSame('authorization/position-permissions', $updateRoute?->uri());
        $this->assertContains('POST', $updateRoute?->methods() ?? []);
        $this->assertSame(AuthorizationController::class.'@updatePositionPermissions', $updateRoute?->getActionName());
    }

    public function test_authorization_page_view_is_registered(): void
    {
        $controller = File::get(app_path('Http/Controllers/AuthorizationController.php'));
        $userModel = File::get(app_path('Models/User.php'));
        $positionPermissionMiddleware = File::get(app_path('Http/Middleware/EnsurePositionPermission.php'));
        $bootstrapApp = File::get(base_path('bootstrap/app.php'));
        $routes = File::get(base_path('routes/web.php'));
        $authorizationView = File::get(resource_path('views/authorization/index.blade.php'));
        $accessMenusView = File::get(resource_path('views/authorization/access-menus.blade.php'));
        $customJs = File::get(public_path('assets/js/custom.js'));

        $this->assertTrue(View::exists('authorization.index'));
        $this->assertTrue(View::exists('authorization.access-menus'));
        $this->assertStringContainsString("view('authorization.index'", $controller);
        $this->assertStringContainsString("view('authorization.access-menus'", $controller);
        $this->assertStringContainsString('accessMenus', $controller);
        $this->assertStringContainsString('authorizationUsersFor', $controller);
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
        $this->assertStringContainsString('List Employee', $authorizationView);
        $this->assertStringContainsString("route('authorization.access-menus')", $authorizationView);
        $this->assertStringContainsString('@forelse ($users as $user)', $authorizationView);
        $this->assertStringContainsString('<th>Name</th>', $authorizationView);
        $this->assertStringContainsString('<th>Position</th>', $authorizationView);
        $this->assertStringContainsString('<th>Company</th>', $authorizationView);
        $this->assertStringNotContainsString('<th>Role</th>', $authorizationView);
        $this->assertStringNotContainsString('<th>Department</th>', $authorizationView);
        $this->assertStringNotContainsString("\$user['email']", $authorizationView);
        $this->assertStringContainsString('Assign Permission', $accessMenusView);
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
        $this->assertStringContainsString('Authorization', $sidebarView);
        $this->assertStringContainsString("View::composer('layouts.sidebar', SidebarPermissionComposer::class)", $appServiceProvider);
        $this->assertStringContainsString('hasAnyPositionPermission([$permissionName])', $sidebarPermissionComposer);
        $this->assertStringNotContainsString('positionPermissionNamesFor', $sidebarPermissionComposer);
    }

    public function test_sidebar_hides_menu_without_position_permission(): void
    {
        $sidebar = Blade::render(File::get(resource_path('views/layouts/sidebar.blade.php')), [
            'canViewSidebarMenu' => static fn (string $permissionName): bool => $permissionName === 'view-dashboard',
        ]);

        $this->assertStringContainsString('Dashboard', $sidebar);
        $this->assertStringNotContainsString('Authorization </span>', $sidebar);
        $this->assertStringNotContainsString('Admin Attendance </span>', $sidebar);
    }

    public function test_position_based_sidebar_permission_structure_is_registered(): void
    {
        $positionPermissionMigration = File::get(database_path('migrations/2026_06_21_163635_create_position_has_permissions_table.php'));
        $dropSidebarMenuMigration = File::get(database_path('migrations/2026_06_21_165133_drop_sidebar_menus_table.php'));
        $positionPermissionSeeder = File::get(database_path('seeders/PositionPermissionSeeder.php'));
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));

        $this->assertStringContainsString("Schema::dropIfExists('sidebar_menus')", $dropSidebarMenuMigration);
        $this->assertStringContainsString("Schema::create('position_has_permissions'", $positionPermissionMigration);
        $this->assertStringContainsString("constrained('positions', 'id')", $positionPermissionMigration);
        $this->assertStringContainsString("constrained('permissions', 'uuid')", $positionPermissionMigration);
        $this->assertStringContainsString('PositionPermissionSeeder::class', $databaseSeeder);
        $this->assertStringContainsString('view-authorization', $positionPermissionSeeder);
        $this->assertStringContainsString('syncPositionPermissions', $positionPermissionSeeder);
        $this->assertStringContainsString('menuPermissionData', $positionPermissionSeeder);
        $this->assertStringContainsString("'System Administrator' => \$permissions->keys()->all()", $positionPermissionSeeder);
    }
}
