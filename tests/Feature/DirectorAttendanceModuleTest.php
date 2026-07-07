<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAttendance\AttendanceRecapController;
use App\Http\Controllers\DirectorAttendance\DirectorAttendanceController;
use App\Http\Controllers\DirectorAttendance\DirectorAttendanceOvertimeController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class DirectorAttendanceModuleTest extends TestCase
{
    public function test_director_attendance_routes_use_separate_controllers(): void
    {
        $attendanceRoute = Route::getRoutes()->getByName('director-attendance.attendance');
        $attendanceMonthlyRoute = Route::getRoutes()->getByName('director-attendance.attendance.monthly-datatable');
        $attendanceDetailRoute = Route::getRoutes()->getByName('director-attendance.attendance.detail-employees');
        $attendanceDetailDatatableRoute = Route::getRoutes()->getByName('director-attendance.attendance.detail-employees.datatable');
        $overtimeRoute = Route::getRoutes()->getByName('director-attendance.overtime');
        $overtimeDetailRoute = Route::getRoutes()->getByName('director-attendance.overtime.detail');

        $this->assertSame(DirectorAttendanceController::class.'@index', $attendanceRoute?->getActionName());
        $this->assertSame(DirectorAttendanceController::class.'@monthlyDatatable', $attendanceMonthlyRoute?->getActionName());
        $this->assertSame(DirectorAttendanceController::class.'@employeeDetails', $attendanceDetailRoute?->getActionName());
        $this->assertSame(DirectorAttendanceController::class.'@employeeDetailsDatatable', $attendanceDetailDatatableRoute?->getActionName());
        $this->assertSame(DirectorAttendanceOvertimeController::class.'@index', $overtimeRoute?->getActionName());
        $this->assertSame(DirectorAttendanceOvertimeController::class.'@detail', $overtimeDetailRoute?->getActionName());
        $this->assertSame('director-attendance', $attendanceRoute?->uri());
        $this->assertSame('director-attendance/monthly-datatable', $attendanceMonthlyRoute?->uri());
        $this->assertSame('director-attendance/attendance/{employee}', $attendanceDetailRoute?->uri());
        $this->assertSame('director-attendance/attendance/{employee}/datatable', $attendanceDetailDatatableRoute?->uri());
        $this->assertSame('director-attendance/overtime', $overtimeRoute?->uri());
        $this->assertSame('director-attendance/overtime/detail/{uid}', $overtimeDetailRoute?->uri());
        $this->assertTrue(is_a(DirectorAttendanceController::class, AttendanceRecapController::class, true));
    }

    public function test_director_module_has_its_own_views_navigation_and_permission(): void
    {
        $directorController = File::get(app_path('Http/Controllers/DirectorAttendance/DirectorAttendanceController.php'));
        $directorDetailView = File::get(resource_path('views/director_attendance/attendance/detail-employees.blade.php'));
        $sidebar = File::get(resource_path('views/layouts/sidebar.blade.php'));
        $navigation = File::get(resource_path('views/director_attendance/layout/navbar.blade.php'));
        $permissionSeeder = File::get(database_path('seeders/PositionPermissionSeeder.php'));
        $legacySeeder = File::get(database_path('seeders/LegacySqlUserSeeder.php'));
        $authorizationController = File::get(app_path('Http/Controllers/AuthorizationController.php'));

        $this->assertStringContainsString('extends AttendanceRecapController', $directorController);
        $this->assertStringContainsString('director_attendance.attendance.index', $directorController);
        $this->assertStringContainsString('director_attendance.attendance.detail-employees', $directorController);
        $this->assertStringNotContainsString('PicAttendanceController', $directorController);
        $this->assertStringNotContainsString('currentCompanyIdFor', $directorController);
        $this->assertStringNotContainsString('current_company_id', $directorController);
        $this->assertStringContainsString('assets/vendor/apexcharts/dist/apexcharts.min.js', $directorDetailView);
        $this->assertStringContainsString("typeof ApexCharts === 'undefined'", $directorDetailView);
        $this->assertStringContainsString('$.fn.peity', $directorDetailView);
        $this->assertTrue(View::exists('director_attendance.attendance.index'));
        $this->assertTrue(View::exists('director_attendance.attendance.detail-employees'));
        $this->assertTrue(View::exists('director_attendance.overtime.index'));
        $this->assertTrue(View::exists('director_attendance.overtime.detail'));
        $this->assertStringContainsString('view-director-attendance', $sidebar);
        $this->assertStringContainsString("route('director-attendance.attendance')", $sidebar);
        $this->assertStringContainsString("route('director-attendance.attendance')", $navigation);
        $this->assertStringContainsString("route('director-attendance.overtime')", $navigation);
        $this->assertStringContainsString('Attendance', $navigation);
        $this->assertStringContainsString('Overtime', $navigation);
        $this->assertStringNotContainsString('Leave', $navigation);
        $this->assertStringNotContainsString('Business Trip', $navigation);
        $this->assertStringContainsString("'view-director-attendance'", $permissionSeeder);
        $this->assertStringContainsString('$directorPermissions = [', $permissionSeeder);
        $this->assertStringContainsString("'Chief Operating Officer' => \$directorPermissions", $permissionSeeder);
        $this->assertStringContainsString("'Director' => \$directorPermissions", $permissionSeeder);
        $this->assertStringContainsString('syncDirectorAttendancePositionPermissions', $legacySeeder);
        $this->assertStringContainsString('push($directorAttendancePermissionId)', $legacySeeder);
        $this->assertStringContainsString('$permissionId !== $adminAttendancePermissionId', $legacySeeder);
        $this->assertMatchesRegularExpression(
            "/\\\$directorPermissions = \\[[^\\]]*'view-director-attendance'[^\\]]*\\];/s",
            $permissionSeeder,
        );
        $this->assertDoesNotMatchRegularExpression(
            "/\\\$directorPermissions = \\[[^\\]]*'view-admin-attendance'[^\\]]*\\];/s",
            $permissionSeeder,
        );
        $this->assertMatchesRegularExpression(
            "/where\\('name', 'Board of Directors'\\)[\\s\\S]*?\\?->syncPermissions\\(\\[[^\\]]*'view-director-attendance'/",
            $permissionSeeder,
        );
        $this->assertDoesNotMatchRegularExpression(
            "/where\\('name', 'Board of Directors'\\)[\\s\\S]*?\\?->syncPermissions\\(\\[[^\\]]*'view-admin-attendance'/",
            $permissionSeeder,
        );
        $this->assertMatchesRegularExpression(
            "/where\\('name', 'Board of Directors'\\)[\\s\\S]*?\\?->syncPermissions\\(\\[[^\\]]*'view-director-attendance'/",
            $legacySeeder,
        );
        $this->assertDoesNotMatchRegularExpression(
            "/where\\('name', 'Board of Directors'\\)[\\s\\S]*?\\?->syncPermissions\\(\\[[^\\]]*'view-admin-attendance'/",
            $legacySeeder,
        );
        $this->assertStringContainsString("'view-director-attendance' => ['section' => 'HR Management', 'label' => 'Director']", $authorizationController);
    }
}
