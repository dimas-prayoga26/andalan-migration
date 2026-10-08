<?php

namespace Tests\Feature;

use App\Http\Controllers\Settings\AttendanceRuleController;
use App\Http\Controllers\Settings\CompanyController;
use App\Http\Controllers\Settings\DivisionController;
use App\Http\Controllers\Settings\OfficeLocationController;
use App\Http\Controllers\Settings\PositionController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    public function test_settings_routes_sidebar_and_views_are_registered(): void
    {
        $companyRoute = Route::getRoutes()->getByName('settings.companies.index');
        $companyDatatableRoute = Route::getRoutes()->getByName('settings.companies.datatable');
        $divisionRoute = Route::getRoutes()->getByName('settings.divisions.index');
        $divisionDatatableRoute = Route::getRoutes()->getByName('settings.divisions.datatable');
        $positionRoute = Route::getRoutes()->getByName('settings.positions.index');
        $positionDatatableRoute = Route::getRoutes()->getByName('settings.positions.datatable');
        $officeLocationRoute = Route::getRoutes()->getByName('settings.office-locations.index');
        $officeLocationDatatableRoute = Route::getRoutes()->getByName('settings.office-locations.datatable');
        $attendanceRuleRoute = Route::getRoutes()->getByName('settings.attendance-rules.index');
        $attendanceRuleDatatableRoute = Route::getRoutes()->getByName('settings.attendance-rules.datatable');

        $this->assertNotNull($companyRoute);
        $this->assertNotNull($companyDatatableRoute);
        $this->assertNotNull($divisionRoute);
        $this->assertNotNull($divisionDatatableRoute);
        $this->assertNotNull($positionRoute);
        $this->assertNotNull($positionDatatableRoute);
        $this->assertNotNull($officeLocationRoute);
        $this->assertNotNull($officeLocationDatatableRoute);
        $this->assertNotNull($attendanceRuleRoute);
        $this->assertNotNull($attendanceRuleDatatableRoute);
        $this->assertSame(CompanyController::class.'@index', $companyRoute->getAction('uses'));
        $this->assertSame(CompanyController::class.'@datatable', $companyDatatableRoute->getAction('uses'));
        $this->assertSame(DivisionController::class.'@index', $divisionRoute->getAction('uses'));
        $this->assertSame(DivisionController::class.'@datatable', $divisionDatatableRoute->getAction('uses'));
        $this->assertSame(PositionController::class.'@index', $positionRoute->getAction('uses'));
        $this->assertSame(PositionController::class.'@datatable', $positionDatatableRoute->getAction('uses'));
        $this->assertSame(OfficeLocationController::class.'@index', $officeLocationRoute->getAction('uses'));
        $this->assertSame(OfficeLocationController::class.'@datatable', $officeLocationDatatableRoute->getAction('uses'));
        $this->assertSame(AttendanceRuleController::class.'@index', $attendanceRuleRoute->getAction('uses'));
        $this->assertSame(AttendanceRuleController::class.'@datatable', $attendanceRuleDatatableRoute->getAction('uses'));
        $this->assertContains('position.permission:view-settings', $companyRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $companyDatatableRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $divisionRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $divisionDatatableRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $positionRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $positionDatatableRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $officeLocationRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $officeLocationDatatableRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $attendanceRuleRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $attendanceRuleDatatableRoute->gatherMiddleware());

        $sidebar = file_get_contents(resource_path('views/layouts/sidebar.blade.php'));
        $settingsNav = file_get_contents(resource_path('views/settings/partials/nav.blade.php'));
        $this->assertStringContainsString('$isSettingsMenu', $sidebar);
        $this->assertStringContainsString('$canViewSettingsMenu', $sidebar);
        $this->assertStringContainsString("canViewSidebarMenu('view-settings')", $sidebar);
        $this->assertStringContainsString('fa-solid fa-gear', $sidebar);
        $this->assertStringContainsString("route('settings.companies.index')", $sidebar);
        $this->assertStringContainsString("route('settings.divisions.index')", $sidebar);
        $this->assertStringContainsString("route('settings.positions.index')", $sidebar);
        $this->assertStringContainsString("route('settings.office-locations.index')", $sidebar);
        $this->assertStringContainsString("route('settings.attendance-rules.index')", $sidebar);
        $this->assertStringContainsString("route('settings.companies.index')", $settingsNav);
        $this->assertStringContainsString("route('settings.office-locations.index')", $settingsNav);
        $this->assertStringContainsString("route('settings.attendance-rules.index')", $settingsNav);
        $this->assertStringContainsString('Company', $settingsNav);
        $this->assertStringContainsString('Work Locations', $settingsNav);
        $this->assertStringContainsString('Attendance Rules', $settingsNav);

        $authorizationController = file_get_contents(app_path('Http/Controllers/AuthorizationController.php'));
        $positionPermissionSeeder = file_get_contents(database_path('seeders/PositionPermissionSeeder.php'));

        $this->assertStringContainsString("'view-settings' => ['section' => 'Setting', 'label' => 'Setting']", $authorizationController);
        $this->assertStringContainsString("['section' => 'Setting', 'label' => 'Setting', 'permission' => 'view-settings']", $positionPermissionSeeder);

        $this->assertFileExists(resource_path('views/settings/index.blade.php'));
        $this->assertFileExists(resource_path('views/settings/form.blade.php'));
        $this->assertFileExists(resource_path('views/settings/partials/nav.blade.php'));
        $this->assertFileExists(resource_path('views/settings/partials/delete-confirmation-swal.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/settings/partials/delete-confirmation-modal.blade.php'));
        $this->assertFileExists(resource_path('views/settings/companies/index.blade.php'));
        $this->assertFileExists(resource_path('views/settings/companies/form.blade.php'));
        $this->assertFileExists(resource_path('views/settings/office-locations/index.blade.php'));
        $this->assertFileExists(resource_path('views/settings/office-locations/form.blade.php'));
        $this->assertFileExists(resource_path('views/settings/attendance-rules/index.blade.php'));
        $this->assertFileExists(resource_path('views/settings/attendance-rules/form.blade.php'));
    }

    public function test_settings_controllers_manage_operational_division_and_position_tables(): void
    {
        $companyController = file_get_contents(app_path('Http/Controllers/Settings/CompanyController.php'));
        $divisionController = file_get_contents(app_path('Http/Controllers/Settings/DivisionController.php'));
        $positionController = file_get_contents(app_path('Http/Controllers/Settings/PositionController.php'));
        $officeLocationController = file_get_contents(app_path('Http/Controllers/Settings/OfficeLocationController.php'));
        $attendanceRuleController = file_get_contents(app_path('Http/Controllers/Settings/AttendanceRuleController.php'));
        $settingsIndexView = file_get_contents(resource_path('views/settings/index.blade.php'));
        $companyIndexView = file_get_contents(resource_path('views/settings/companies/index.blade.php'));
        $companyFormView = file_get_contents(resource_path('views/settings/companies/form.blade.php'));
        $officeLocationIndexView = file_get_contents(resource_path('views/settings/office-locations/index.blade.php'));
        $officeLocationFormView = file_get_contents(resource_path('views/settings/office-locations/form.blade.php'));
        $attendanceRuleIndexView = file_get_contents(resource_path('views/settings/attendance-rules/index.blade.php'));
        $attendanceRuleFormView = file_get_contents(resource_path('views/settings/attendance-rules/form.blade.php'));
        $deleteConfirmationSwal = file_get_contents(resource_path('views/settings/partials/delete-confirmation-swal.blade.php'));
        $composerJson = file_get_contents(base_path('composer.json'));

        $this->assertStringContainsString('"yajra/laravel-datatables-oracle": "^13.3"', $composerJson);
        $this->assertStringContainsString('Company::query()', $companyController);
        $this->assertStringContainsString('use Yajra\DataTables\Facades\DataTables;', $companyController);
        $this->assertStringContainsString('public function datatable(): JsonResponse', $companyController);
        $this->assertStringContainsString('DataTables::eloquent($query)', $companyController);
        $this->assertStringNotContainsString('rawColumns', $companyController);
        $this->assertStringNotContainsString('data-settings-delete-form', $companyController);
        $this->assertStringNotContainsString('<<<HTML', $companyController);
        $this->assertStringContainsString("Rule::unique('companies', 'name')", $companyController);
        $this->assertStringContainsString("where('current_company_id'", $companyController);
        $this->assertStringContainsString('$company->users()->exists()', $companyController);
        $this->assertStringContainsString('$company->projects()->exists()', $companyController);
        $this->assertStringContainsString('$company->mailAccessAccounts()->exists()', $companyController);
        $this->assertStringContainsString('$company->jobVacancies()->exists()', $companyController);
        $this->assertStringContainsString('Manage company master data.', $companyIndexView);
        $this->assertStringContainsString("route('settings.companies.datatable')", $companyIndexView);
        $this->assertStringContainsString("jQuery('#companiesTable').DataTable({", $companyIndexView);
        $this->assertStringContainsString('function actionButtons(row)', $companyIndexView);
        $this->assertStringContainsString('function statusBadge(isActive)', $companyIndexView);
        $this->assertStringContainsString('data-settings-delete-form', $companyIndexView);
        $this->assertStringContainsString('serverSide: true', $companyIndexView);
        $this->assertStringContainsString('processing: true', $companyIndexView);
        $this->assertStringContainsString('companyTable.search(searchInput.value).draw();', $companyIndexView);
        $this->assertStringNotContainsString('@forelse ($companies as $company)', $companyIndexView);
        $this->assertStringNotContainsString("settings.partials.pagination', ['items' => \$companies]", $companyIndexView);
        $this->assertStringContainsString('name="legal_name"', $companyFormView);
        $this->assertStringContainsString('name="website"', $companyFormView);
        $this->assertStringContainsString('name="is_active"', $companyFormView);

        $this->assertStringContainsString('Department::query()', $divisionController);
        $this->assertStringContainsString('use Yajra\DataTables\Facades\DataTables;', $divisionController);
        $this->assertStringContainsString('public function datatable(): JsonResponse', $divisionController);
        $this->assertStringContainsString('DataTables::eloquent($query)', $divisionController);
        $this->assertStringNotContainsString('rawColumns', $divisionController);
        $this->assertStringNotContainsString('data-settings-delete-form', $divisionController);
        $this->assertStringNotContainsString('<<<HTML', $divisionController);
        $this->assertStringContainsString('route($datatableRoute)', $settingsIndexView);
        $this->assertStringContainsString('Str::uuid()', $divisionController);
        $this->assertStringContainsString("Rule::unique('departments', 'name')", $divisionController);
        $this->assertStringContainsString("where('current_department_id'", $divisionController);

        $this->assertStringContainsString('Position::query()', $positionController);
        $this->assertStringContainsString('use Yajra\DataTables\Facades\DataTables;', $positionController);
        $this->assertStringContainsString('public function datatable(): JsonResponse', $positionController);
        $this->assertStringContainsString('DataTables::eloquent($query)', $positionController);
        $this->assertStringNotContainsString('rawColumns', $positionController);
        $this->assertStringNotContainsString('data-settings-delete-form', $positionController);
        $this->assertStringNotContainsString('<<<HTML', $positionController);
        $this->assertStringContainsString("Rule::unique('positions', 'name')", $positionController);
        $this->assertStringContainsString("where('current_position_id'", $positionController);
        $this->assertStringContainsString("DB::table('employee_deployment_positions')", $positionController);

        $this->assertStringContainsString('OfficeLocation::query()', $officeLocationController);
        $this->assertStringContainsString('use Yajra\DataTables\Facades\DataTables;', $officeLocationController);
        $this->assertStringContainsString('public function datatable(): JsonResponse', $officeLocationController);
        $this->assertStringContainsString('DataTables::eloquent($query)', $officeLocationController);
        $this->assertStringNotContainsString('rawColumns', $officeLocationController);
        $this->assertStringNotContainsString('data-settings-delete-form', $officeLocationController);
        $this->assertStringNotContainsString('<<<HTML', $officeLocationController);
        $this->assertStringContainsString("Rule::unique('office_locations', 'name')", $officeLocationController);
        $this->assertStringContainsString("where('current_office_location_id'", $officeLocationController);
        $this->assertStringContainsString("DB::table('rules_of_attendaces')", $officeLocationController);
        $this->assertStringContainsString("'latitude' => ['required', 'numeric', 'between:-90,90']", $officeLocationController);
        $this->assertStringContainsString("'longitude' => ['required', 'numeric', 'between:-180,180']", $officeLocationController);
        $this->assertStringContainsString('Manage work location master data for attendance rules and employee deployment.', $officeLocationIndexView);
        $this->assertStringContainsString("route('settings.office-locations.datatable')", $officeLocationIndexView);
        $this->assertStringContainsString("jQuery('#officeLocationsTable').DataTable({", $officeLocationIndexView);
        $this->assertStringContainsString('function actionButtons(row)', $officeLocationIndexView);
        $this->assertStringContainsString('function statusBadge(isActive)', $officeLocationIndexView);
        $this->assertStringContainsString('data-settings-delete-form', $officeLocationIndexView);
        $this->assertStringContainsString('name="latitude"', $officeLocationFormView);
        $this->assertStringContainsString('name="longitude"', $officeLocationFormView);
        $this->assertStringContainsString('name="address"', $officeLocationFormView);

        $this->assertStringContainsString('RulesOfAttendace::query()', $attendanceRuleController);
        $this->assertStringContainsString('use Yajra\DataTables\Facades\DataTables;', $attendanceRuleController);
        $this->assertStringContainsString('public function datatable(Request $request): JsonResponse', $attendanceRuleController);
        $this->assertStringContainsString('DataTables::eloquent($query)', $attendanceRuleController);
        $this->assertStringNotContainsString('rawColumns', $attendanceRuleController);
        $this->assertStringNotContainsString('data-settings-delete-form', $attendanceRuleController);
        $this->assertStringNotContainsString('<<<HTML', $attendanceRuleController);
        $this->assertStringContainsString('->filter(function (Builder $query) use ($request): void', $attendanceRuleController);
        $this->assertStringContainsString('OfficeLocation::query()', $attendanceRuleController);
        $this->assertStringContainsString('Position::query()', $attendanceRuleController);
        $this->assertStringContainsString("'attendance_type' => ['required', 'string', 'in:fixed,flexible']", $attendanceRuleController);
        $this->assertStringContainsString("'position_ids.*' => ['string', 'distinct', 'exists:positions,id']", $attendanceRuleController);
        $this->assertStringContainsString('$attendanceRule->positions()->sync($positionIds);', $attendanceRuleController);
        $this->assertStringNotContainsString("Rule::unique('rules_of_attendaces', 'office_location_id')", $attendanceRuleController);
        $this->assertStringContainsString("'office_start_time' => ['required', 'date_format:H:i']", $attendanceRuleController);
        $this->assertStringContainsString("'office_end_time' => ['required', 'date_format:H:i']", $attendanceRuleController);
        $this->assertStringContainsString('settings.attendance-rules.index', $attendanceRuleController);
        $this->assertStringContainsString('Manage attendance time, IP, and location radius rules.', $attendanceRuleIndexView);
        $this->assertStringContainsString("route('settings.attendance-rules.datatable')", $attendanceRuleIndexView);
        $this->assertStringContainsString("jQuery('#attendanceRulesTable').DataTable({", $attendanceRuleIndexView);
        $this->assertStringContainsString('function actionButtons(row)', $attendanceRuleIndexView);
        $this->assertStringContainsString('function statusBadge(isActive)', $attendanceRuleIndexView);
        $this->assertStringContainsString('data-settings-delete-form', $attendanceRuleIndexView);
        $this->assertStringContainsString('name="office_location_id"', $attendanceRuleFormView);
        $this->assertStringContainsString('name="attendance_type"', $attendanceRuleFormView);
        $this->assertStringContainsString('name="position_ids[]"', $attendanceRuleFormView);
        $this->assertStringContainsString('name="office_start_time"', $attendanceRuleFormView);
        $this->assertStringContainsString('name="office_end_time"', $attendanceRuleFormView);
        $this->assertStringContainsString('name="radius"', $attendanceRuleFormView);
        $this->assertStringContainsString('name="ip_range"', $attendanceRuleFormView);

        $this->assertStringContainsString('settings-table-footer', $settingsIndexView);
        $this->assertStringContainsString('Add {{ $resourceLabel }}', $settingsIndexView);
        $this->assertStringContainsString('Manage {{ strtolower($resourceLabel) }} master data.', $settingsIndexView);
        $this->assertStringContainsString('data-settings-delete-form', $settingsIndexView);
        $this->assertStringContainsString('function actionButtons(row)', $settingsIndexView);
        $this->assertStringContainsString('function statusBadge(status)', $settingsIndexView);
        $this->assertStringContainsString("jQuery('#{{ \$tableId }}').DataTable({", $settingsIndexView);
        $this->assertStringContainsString('serverSide: true', $settingsIndexView);
        $this->assertStringContainsString('serverSide: true', $officeLocationIndexView);
        $this->assertStringContainsString('serverSide: true', $attendanceRuleIndexView);
        $this->assertStringContainsString('settingsTable.search(searchInput.value).draw();', $settingsIndexView);
        $this->assertStringContainsString('officeLocationsTable.search(searchInput.value).draw();', $officeLocationIndexView);
        $this->assertStringContainsString('attendanceRulesTable.search(searchInput.value).draw();', $attendanceRuleIndexView);
        $this->assertStringNotContainsString('@forelse ($items as $item)', $settingsIndexView);
        $this->assertStringNotContainsString('@forelse ($officeLocations as $officeLocation)', $officeLocationIndexView);
        $this->assertStringNotContainsString('@forelse ($attendanceRules as $attendanceRule)', $attendanceRuleIndexView);
        $this->assertStringNotContainsString("settings.partials.pagination', ['items' => \$items]", $settingsIndexView);
        $this->assertStringNotContainsString("settings.partials.pagination', ['items' => \$officeLocations]", $officeLocationIndexView);
        $this->assertStringNotContainsString("settings.partials.pagination', ['items' => \$attendanceRules]", $attendanceRuleIndexView);
        $this->assertStringNotContainsString('onsubmit="return confirm', $settingsIndexView.$companyIndexView.$officeLocationIndexView.$attendanceRuleIndexView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}", $settingsIndexView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}", $companyIndexView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}", $officeLocationIndexView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}", $attendanceRuleIndexView);
        $this->assertStringContainsString('delete-confirmation-swal', $settingsIndexView);
        $this->assertStringContainsString('delete-confirmation-swal', $companyIndexView);
        $this->assertStringContainsString('delete-confirmation-swal', $officeLocationIndexView);
        $this->assertStringContainsString('delete-confirmation-swal', $attendanceRuleIndexView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}", $deleteConfirmationSwal);
        $this->assertStringContainsString('Swal.fire({', $deleteConfirmationSwal);
        $this->assertStringContainsString("form.dataset.deleteConfirmButton || 'Delete'", $deleteConfirmationSwal);
        $this->assertStringNotContainsString('window.bootstrap.Modal', $deleteConfirmationSwal);
    }
}
