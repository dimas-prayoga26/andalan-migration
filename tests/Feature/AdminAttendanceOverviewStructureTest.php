<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAttendance\AttendanceOverviewController;
use App\Http\Controllers\AdminAttendance\AttendanceRecapController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AdminAttendanceOverviewStructureTest extends TestCase
{
    public function test_overview_and_recap_use_separate_controllers(): void
    {
        $overviewRoute = Route::getRoutes()->getByName('admin-attendance.overview');
        $recapRoute = Route::getRoutes()->getByName('admin-attendance.recap');
        $monthlyDatatableRoute = Route::getRoutes()->getByName('admin-attendance.recap.monthly-datatable');
        $detailRoute = Route::getRoutes()->getByName('admin-attendance.recap.detail-employees');
        $detailDatatableRoute = Route::getRoutes()->getByName('admin-attendance.recap.detail-employees.datatable');
        $overviewController = File::get(app_path('Http/Controllers/AdminAttendance/AttendanceOverviewController.php'));
        $recapController = File::get(app_path('Http/Controllers/AdminAttendance/AttendanceRecapController.php'));

        $this->assertSame(AttendanceOverviewController::class.'@index', $overviewRoute?->getActionName());
        $this->assertSame(AttendanceRecapController::class.'@index', $recapRoute?->getActionName());
        $this->assertSame(AttendanceRecapController::class.'@monthlyDatatable', $monthlyDatatableRoute?->getActionName());
        $this->assertSame(AttendanceRecapController::class.'@employeeDetails', $detailRoute?->getActionName());
        $this->assertSame('admin-attendance/recap-attendance/datatable', $monthlyDatatableRoute?->uri());
        $this->assertSame(AttendanceRecapController::class.'@employeeDetailsDatatable', $detailDatatableRoute?->getActionName());
        $this->assertSame('admin-attendance/recap-attendance/{employee}', $detailRoute?->uri());
        $this->assertSame('admin-attendance/recap-attendance/{employee}/datatable', $detailDatatableRoute?->uri());
        $this->assertStringContainsString('overviewViewData', $overviewController);
        $this->assertStringNotContainsString('function recap', $overviewController);
        $this->assertStringNotContainsString('AttendanceOverviewController', $recapController);
        $this->assertStringContainsString('recapViewData', $recapController);
        $this->assertStringContainsString('monthlyDatatable', $recapController);
        $this->assertStringContainsString('recapEmployeeDetailData', $recapController);
        $this->assertStringContainsString('recapVirtualHolidayRows', $recapController);
        $this->assertStringContainsString('recapDaysLabel', $recapController);
        $this->assertStringContainsString('recapEmployeeWorkDays', $recapController);
        $this->assertStringContainsString('employeeDetailsDatatable', $recapController);
        $this->assertStringContainsString('activeEmployeeIdsFor($now, $companyId)', $recapController);
        $this->assertStringContainsString("'recapDetailMonth' => \$detailContext['month']", $recapController);
        $this->assertStringContainsString('recapAttendanceLogRows', $recapController);
        $this->assertStringContainsString('recapMonthlyRows', $recapController);
        $this->assertTrue(View::exists('admin_attendance.overview.index'));
        $this->assertTrue(View::exists('admin_attendance.recap_attendance.index'));
        $this->assertTrue(View::exists('admin_attendance.recap_attendance.detail-employees'));
    }

    public function test_admin_attendance_navigation_links_to_overview_and_recap(): void
    {
        $navbar = File::get(resource_path('views/admin_attendance/layout/navbar.blade.php'));
        $sidebar = File::get(resource_path('views/layouts/sidebar.blade.php'));

        $this->assertStringContainsString("route('admin-attendance.overview')", $navbar);
        $this->assertStringContainsString("route('admin-attendance.recap')", $navbar);
        $this->assertStringContainsString("request()->routeIs('admin-attendance.recap*')", $navbar);
        $this->assertStringContainsString("route('admin-attendance.overview')", $sidebar);
    }

    public function test_recap_views_keep_dynamic_data_bindings(): void
    {
        $recapView = File::get(resource_path('views/admin_attendance/recap_attendance/index.blade.php'));
        $detailView = File::get(resource_path('views/admin_attendance/recap_attendance/detail-employees.blade.php'));

        $this->assertStringContainsString('@forelse ($recapAttendanceLogRows as $row)', $recapView);
        $this->assertStringContainsString('id="recapMonthlyTable"', $recapView);
        $this->assertStringContainsString('admin-attendance.recap.monthly-datatable', $recapView);
        $this->assertStringContainsString('employeeDetailBaseUrl', $recapView);
        $this->assertStringContainsString('monthlyTable.ajax.reload()', $recapView);
        $this->assertStringContainsString('Attendance Recap', $detailView);
        $this->assertStringContainsString("recapDetailEmployee['employee_code']", $detailView);
        $this->assertStringContainsString("recapDetailEmployee['id']", $detailView);
        $this->assertStringContainsString('id="recapDetailPeriodFilter"', $detailView);
        $this->assertStringContainsString('id="recapDetailMonthFilter"', $detailView);
        $this->assertStringContainsString('id="recapDetailYearFilter"', $detailView);
        $this->assertStringContainsString("detailMonthFilter.addEventListener('change', reloadDetailTable)", $detailView);
        $this->assertStringContainsString('admin-attendance.recap.detail-employees.datatable', $detailView);
        $this->assertStringContainsString('detailTable.ajax.reload()', $detailView);
        $this->assertStringNotContainsString('history.replaceState', $detailView);
        $this->assertStringContainsString('id="recapDetailAttendanceTable"', $detailView);
        $this->assertStringNotContainsString("$('#tableLicenseUsage').DataTable", $detailView);
        $this->assertStringNotContainsString('scrollX: true', $detailView);
    }
}
