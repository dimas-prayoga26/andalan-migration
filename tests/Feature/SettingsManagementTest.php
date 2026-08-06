<?php

namespace Tests\Feature;

use App\Http\Controllers\Settings\AttendanceRuleController;
use App\Http\Controllers\Settings\DivisionController;
use App\Http\Controllers\Settings\OfficeLocationController;
use App\Http\Controllers\Settings\PositionController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    public function test_settings_routes_sidebar_and_views_are_registered(): void
    {
        $divisionRoute = Route::getRoutes()->getByName('settings.divisions.index');
        $positionRoute = Route::getRoutes()->getByName('settings.positions.index');
        $officeLocationRoute = Route::getRoutes()->getByName('settings.office-locations.index');
        $attendanceRuleRoute = Route::getRoutes()->getByName('settings.attendance-rules.index');

        $this->assertNotNull($divisionRoute);
        $this->assertNotNull($positionRoute);
        $this->assertNotNull($officeLocationRoute);
        $this->assertNotNull($attendanceRuleRoute);
        $this->assertSame(DivisionController::class.'@index', $divisionRoute->getAction('uses'));
        $this->assertSame(PositionController::class.'@index', $positionRoute->getAction('uses'));
        $this->assertSame(OfficeLocationController::class.'@index', $officeLocationRoute->getAction('uses'));
        $this->assertSame(AttendanceRuleController::class.'@index', $attendanceRuleRoute->getAction('uses'));
        $this->assertContains('position.permission:view-settings', $divisionRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $positionRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $officeLocationRoute->gatherMiddleware());
        $this->assertContains('position.permission:view-settings', $attendanceRuleRoute->gatherMiddleware());

        $sidebar = file_get_contents(resource_path('views/layouts/sidebar.blade.php'));
        $settingsNav = file_get_contents(resource_path('views/settings/partials/nav.blade.php'));
        $this->assertStringContainsString('$isSettingsMenu', $sidebar);
        $this->assertStringContainsString('$canViewSettingsMenu', $sidebar);
        $this->assertStringContainsString("canViewSidebarMenu('view-settings')", $sidebar);
        $this->assertStringContainsString('fa-solid fa-gear', $sidebar);
        $this->assertStringContainsString("route('settings.divisions.index')", $sidebar);
        $this->assertStringContainsString("route('settings.positions.index')", $sidebar);
        $this->assertStringContainsString("route('settings.office-locations.index')", $sidebar);
        $this->assertStringContainsString("route('settings.attendance-rules.index')", $sidebar);
        $this->assertStringContainsString("route('settings.office-locations.index')", $settingsNav);
        $this->assertStringContainsString("route('settings.attendance-rules.index')", $settingsNav);
        $this->assertStringContainsString('Office Locations', $settingsNav);
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
        $this->assertFileExists(resource_path('views/settings/office-locations/index.blade.php'));
        $this->assertFileExists(resource_path('views/settings/office-locations/form.blade.php'));
        $this->assertFileExists(resource_path('views/settings/attendance-rules/index.blade.php'));
        $this->assertFileExists(resource_path('views/settings/attendance-rules/form.blade.php'));
    }

    public function test_settings_controllers_manage_operational_division_and_position_tables(): void
    {
        $divisionController = file_get_contents(app_path('Http/Controllers/Settings/DivisionController.php'));
        $positionController = file_get_contents(app_path('Http/Controllers/Settings/PositionController.php'));
        $officeLocationController = file_get_contents(app_path('Http/Controllers/Settings/OfficeLocationController.php'));
        $attendanceRuleController = file_get_contents(app_path('Http/Controllers/Settings/AttendanceRuleController.php'));
        $settingsIndexView = file_get_contents(resource_path('views/settings/index.blade.php'));
        $officeLocationIndexView = file_get_contents(resource_path('views/settings/office-locations/index.blade.php'));
        $officeLocationFormView = file_get_contents(resource_path('views/settings/office-locations/form.blade.php'));
        $attendanceRuleIndexView = file_get_contents(resource_path('views/settings/attendance-rules/index.blade.php'));
        $attendanceRuleFormView = file_get_contents(resource_path('views/settings/attendance-rules/form.blade.php'));
        $deleteConfirmationSwal = file_get_contents(resource_path('views/settings/partials/delete-confirmation-swal.blade.php'));

        $this->assertStringContainsString('Department::query()', $divisionController);
        $this->assertStringContainsString('Str::uuid()', $divisionController);
        $this->assertStringContainsString("Rule::unique('departments', 'name')", $divisionController);
        $this->assertStringContainsString("where('current_department_id'", $divisionController);

        $this->assertStringContainsString('Position::query()', $positionController);
        $this->assertStringContainsString("Rule::unique('positions', 'name')", $positionController);
        $this->assertStringContainsString("where('current_position_id'", $positionController);
        $this->assertStringContainsString("DB::table('employee_deployment_positions')", $positionController);

        $this->assertStringContainsString('OfficeLocation::query()', $officeLocationController);
        $this->assertStringContainsString("Rule::unique('office_locations', 'name')", $officeLocationController);
        $this->assertStringContainsString("where('current_office_location_id'", $officeLocationController);
        $this->assertStringContainsString("DB::table('rules_of_attendaces')", $officeLocationController);
        $this->assertStringContainsString("'latitude' => ['required', 'numeric', 'between:-90,90']", $officeLocationController);
        $this->assertStringContainsString("'longitude' => ['required', 'numeric', 'between:-180,180']", $officeLocationController);
        $this->assertStringContainsString('Manage office master data for attendance rules and employee deployment.', $officeLocationIndexView);
        $this->assertStringContainsString('name="latitude"', $officeLocationFormView);
        $this->assertStringContainsString('name="longitude"', $officeLocationFormView);
        $this->assertStringContainsString('name="address"', $officeLocationFormView);

        $this->assertStringContainsString('RulesOfAttendace::query()', $attendanceRuleController);
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
        $this->assertStringContainsString('data-settings-delete-form', $officeLocationIndexView);
        $this->assertStringContainsString('data-settings-delete-form', $attendanceRuleIndexView);
        $this->assertStringNotContainsString('onsubmit="return confirm', $settingsIndexView.$officeLocationIndexView.$attendanceRuleIndexView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}", $settingsIndexView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}", $officeLocationIndexView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}", $attendanceRuleIndexView);
        $this->assertStringContainsString('delete-confirmation-swal', $settingsIndexView);
        $this->assertStringContainsString('delete-confirmation-swal', $officeLocationIndexView);
        $this->assertStringContainsString('delete-confirmation-swal', $attendanceRuleIndexView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}", $deleteConfirmationSwal);
        $this->assertStringContainsString('Swal.fire({', $deleteConfirmationSwal);
        $this->assertStringContainsString("confirmButtonText: 'Delete'", $deleteConfirmationSwal);
        $this->assertStringNotContainsString('window.bootstrap.Modal', $deleteConfirmationSwal);
    }
}
