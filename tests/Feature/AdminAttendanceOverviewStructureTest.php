<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAttendance\AttendanceOverviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AdminAttendanceOverviewStructureTest extends TestCase
{
    public function test_admin_attendance_overview_route_controller_and_view_exist(): void
    {
        $route = Route::getRoutes()->getByName('admin-attendance.overview');
        $controller = File::get(app_path('Http/Controllers/AdminAttendance/AttendanceOverviewController.php'));

        $this->assertNotNull($route);
        $this->assertSame('admin-attendance/overview', $route?->uri());
        $this->assertSame(AttendanceOverviewController::class.'@index', $route?->getActionName());
        $this->assertTrue(View::exists('admin_attendance.overview.index'));
        $this->assertFileExists(app_path('Http/Controllers/AdminAttendance/AttendanceOverviewController.php'));
        $this->assertDirectoryExists(resource_path('views/admin_attendance/overview'));
        $this->assertStringContainsString('attendanceOverviewSeries', $controller);
        $this->assertStringNotContainsString('yearAttendanceOnTimeSeries', $controller);
        $this->assertStringNotContainsString('yearAttendanceLateSeries', $controller);
        $this->assertStringNotContainsString('yearAttendanceLeaveSeries', $controller);
        $this->assertStringNotContainsString('yearLeaveSeries', $controller);
        $this->assertStringNotContainsString('yearSickSeries', $controller);
        $this->assertStringNotContainsString('yearBusinessTripSeries', $controller);
        $this->assertStringNotContainsString('yearOvertimeHoursSeries', $controller);
        $this->assertStringContainsString('dailyAttendanceSummary', $controller);
        $this->assertStringContainsString('dailyAttendanceLists', $controller);
        $this->assertStringContainsString('activeEmployeeIdsFor', $controller);
        $this->assertStringContainsString('currentCompanyIdFor', $controller);
        $this->assertStringContainsString('current_company_id', $controller);
        $this->assertStringContainsString("where('current_company_id', \$companyId)", $controller);
        $this->assertStringContainsString('Attendance::query()', $controller);
        $this->assertStringContainsString('LeaveRequest::query()', $controller);
        $this->assertStringContainsString('BusinessTrip::query()', $controller);
        $this->assertStringContainsString('dailyEarlyBirds', $controller);
        $this->assertStringContainsString('dailyRunningLate', $controller);
        $this->assertStringContainsString('dailyBusinessTrips', $controller);
        $this->assertStringContainsString('dailyLeaves', $controller);
        $this->assertStringContainsString("->whereRaw('LOWER(COALESCE(status, \"\")) = ?', ['masuk'])", $controller);
        $this->assertStringContainsString('weeklyAttendanceCharts', $controller);
        $this->assertStringContainsString('weeklyAttendanceSeries', $controller);
        $this->assertStringContainsString('weeklyOutOfOfficeSeries', $controller);
        $this->assertStringContainsString('weeklyOvertimeHoursSeries', $controller);
        $this->assertStringContainsString('monthlyAttendanceCharts', $controller);
        $this->assertStringContainsString('monthlyAttendanceSeries', $controller);
        $this->assertStringContainsString('monthlyOutOfOfficeSeries', $controller);
        $this->assertStringContainsString('yearToDateAttendanceCharts', $controller);
        $this->assertStringContainsString('yearToDateLeaveSeries', $controller);
        $this->assertStringContainsString('yearToDateOvertimeHoursSeries', $controller);
        $this->assertStringContainsString('AttendanceOvertime::query()', $controller);
        $this->assertStringContainsString('LeaveType::query()', $controller);

        if (! extension_loaded('pdo_sqlite')) {
            $this->assertTrue(true);

            return;
        }

        $renderedOverview = app(AttendanceOverviewController::class)
            ->index(Request::create('/admin-attendance/overview', 'GET'))
            ->render();

        $this->assertStringContainsString('var attendanceOverviewChartSeries = ', $renderedOverview);
        $this->assertStringContainsString('var attendanceOverviewChartColors = ', $renderedOverview);
        $this->assertStringContainsString('var attendanceProgressPercent = ', $renderedOverview);
        $this->assertStringContainsString('var weeklyDayLabels = ', $renderedOverview);
        $this->assertStringContainsString('var weeklyAttendanceSeries = ', $renderedOverview);
        $this->assertStringContainsString('var weeklyOutOfOfficeSeries = ', $renderedOverview);
        $this->assertStringContainsString('var weeklyOvertimeHoursSeries = ', $renderedOverview);
        $this->assertStringContainsString('var monthlyDayLabels = ', $renderedOverview);
        $this->assertStringContainsString('var monthlyAttendanceSeries = ', $renderedOverview);
        $this->assertStringContainsString('var monthlyOutOfOfficeSeries = ', $renderedOverview);
        $this->assertStringContainsString('var yearToDateMonthLabels = ', $renderedOverview);
        $this->assertStringContainsString('var yearToDateLeaveSeries = ', $renderedOverview);
        $this->assertStringContainsString('var yearToDateOvertimeHoursSeries = ', $renderedOverview);
        $this->assertStringNotContainsString('var yearMonthLabels = ', $renderedOverview);
    }

    public function test_admin_attendance_menu_is_registered_in_sidebar(): void
    {
        $sidebarView = File::get(resource_path('views/layouts/sidebar.blade.php'));

        $this->assertStringContainsString('$isAdminAttendanceMenu', $sidebarView);
        $this->assertStringContainsString("route('admin-attendance.overview')", $sidebarView);
        $this->assertStringContainsString('Admin Attendance', $sidebarView);
    }

    public function test_admin_attendance_overview_matches_management_dashboard_structure(): void
    {
        $overviewView = File::get(resource_path('views/admin_attendance/overview/index.blade.php'));

        $this->assertStringContainsString('admin-attendance-daily-grid', $overviewView);
        $this->assertStringContainsString('admin-attendance-list-grid', $overviewView);
        $this->assertStringContainsString('admin-attendance-overview-column', $overviewView);
        $this->assertStringContainsString('admin-attendance-overview-card', $overviewView);
        $this->assertStringContainsString('admin-attendance-summary-card', $overviewView);
        $this->assertStringContainsString('border: 1px solid #d8e0ef;', $overviewView);
        $this->assertStringContainsString('border-radius: 50% !important;', $overviewView);
        $this->assertStringContainsString('aspect-ratio: 1 / 1;', $overviewView);
        $this->assertStringContainsString('width: 48px !important;', $overviewView);
        $this->assertStringContainsString('height: 48px !important;', $overviewView);
        $this->assertStringNotContainsString('admin-attendance-person-avatar avatar rounded', $overviewView);
        $this->assertStringContainsString('min-height: 430px;', $overviewView);
        $this->assertStringContainsString('Daily Staff Attendance', $overviewView);
        $this->assertStringContainsString("Today's Early Birds", $overviewView);
        $this->assertStringContainsString("Today's Running Late", $overviewView);
        $this->assertStringContainsString('On Business Trip', $overviewView);
        $this->assertStringContainsString('Days on Leave (Time Off)', $overviewView);
        $this->assertStringContainsString('$attendanceOverviewChartSeries = array_sum($attendanceOverviewSeries) > 0 ? $attendanceOverviewSeries : [1];', $overviewView);
        $this->assertStringContainsString('var attendanceOverviewChartSeries = @json($attendanceOverviewChartSeries);', $overviewView);
        $this->assertStringContainsString('var attendanceOverviewChartColors = @json($attendanceOverviewChartColors);', $overviewView);
        $this->assertStringContainsString('var attendanceProgressPercent = @json($attendanceProgressPercent);', $overviewView);
        $this->assertStringContainsString('series: attendanceOverviewChartSeries', $overviewView);
        $this->assertStringContainsString('colors: attendanceOverviewChartColors', $overviewView);
        $this->assertStringContainsString('series: [attendanceProgressPercent]', $overviewView);
        $this->assertStringContainsString('Attendance ({{ $weeklyAttendanceRangeLabel }})', $overviewView);
        $this->assertStringContainsString('Out of Office ({{ $weeklyAttendanceRangeLabel }})', $overviewView);
        $this->assertStringContainsString('Overtime Overview ({{ $weeklyAttendanceRangeLabel }})', $overviewView);
        $this->assertStringContainsString('var weeklyDayLabels = @json($weeklyDayLabels);', $overviewView);
        $this->assertStringContainsString('var weeklyAttendanceSeries = @json($weeklyAttendanceSeries);', $overviewView);
        $this->assertStringContainsString('var weeklyOutOfOfficeSeries = @json($weeklyOutOfOfficeSeries);', $overviewView);
        $this->assertStringContainsString('var weeklyOvertimeHoursSeries = @json($weeklyOvertimeHoursSeries);', $overviewView);
        $this->assertStringContainsString('series: weeklyAttendanceSeries', $overviewView);
        $this->assertStringContainsString('series: weeklyOutOfOfficeSeries', $overviewView);
        $this->assertStringContainsString('labels: weeklyDayLabels', $overviewView);
        $this->assertStringContainsString('data: weeklyOvertimeHoursSeries', $overviewView);
        $this->assertStringNotContainsString('01-05 June 2026', $overviewView);
        $this->assertStringNotContainsString("label: 'John Doe'", $overviewView);
        $this->assertStringContainsString('Attendance ({{ $monthlyAttendanceRangeLabel }})', $overviewView);
        $this->assertStringContainsString('Out of Office ({{ $monthlyAttendanceRangeLabel }})', $overviewView);
        $this->assertStringContainsString('Leave Overview ({{ $yearToDateYearLabel }})', $overviewView);
        $this->assertStringContainsString('Overtime Overview ({{ $yearToDateYearLabel }})', $overviewView);
        $this->assertStringContainsString('var monthlyDayLabels = @json($monthlyDayLabels);', $overviewView);
        $this->assertStringContainsString('var monthlyAttendanceSeries = @json($monthlyAttendanceSeries);', $overviewView);
        $this->assertStringContainsString('var monthlyOutOfOfficeSeries = @json($monthlyOutOfOfficeSeries);', $overviewView);
        $this->assertStringContainsString('var yearToDateMonthLabels = @json($yearToDateMonthLabels);', $overviewView);
        $this->assertStringContainsString('var yearToDateLeaveSeries = @json($yearToDateLeaveSeries);', $overviewView);
        $this->assertStringContainsString('var yearToDateOvertimeHoursSeries = @json($yearToDateOvertimeHoursSeries);', $overviewView);
        $this->assertStringContainsString('function integerTickLabel(value)', $overviewView);
        $this->assertStringContainsString("return numericValue < 1 ? '' : Math.round(numericValue).toString();", $overviewView);
        $this->assertStringContainsString('function apexIntegerYAxis(series)', $overviewView);
        $this->assertStringContainsString('function chartJsIntegerYAxis(series)', $overviewView);
        $this->assertStringContainsString('decimalsInFloat: 0', $overviewView);
        $this->assertStringContainsString('series: monthlyAttendanceSeries', $overviewView);
        $this->assertStringContainsString('series: monthlyOutOfOfficeSeries', $overviewView);
        $this->assertStringContainsString('categories: monthlyDayLabels', $overviewView);
        $this->assertStringContainsString('labels: yearToDateMonthLabels', $overviewView);
        $this->assertStringContainsString('data: yearToDateOvertimeHoursSeries', $overviewView);
        $this->assertStringContainsString('datasets: yearToDateLeaveSeries.map', $overviewView);
        $this->assertStringContainsString('yaxis: apexIntegerYAxis(weeklyAttendanceSeries)', $overviewView);
        $this->assertStringContainsString('yaxis: apexIntegerYAxis(weeklyOutOfOfficeSeries)', $overviewView);
        $this->assertStringContainsString('yaxis: apexIntegerYAxis(monthlyAttendanceSeries)', $overviewView);
        $this->assertStringContainsString('yaxis: apexIntegerYAxis(monthlyOutOfOfficeSeries)', $overviewView);
        $this->assertStringContainsString('y: chartJsIntegerYAxis(yearToDateOvertimeHoursSeries)', $overviewView);
        $this->assertStringContainsString('y: chartJsIntegerYAxis(yearToDateLeaveSeries)', $overviewView);
        $this->assertStringNotContainsString('01-30 June 2026', $overviewView);
        $this->assertStringNotContainsString('Annual Leave', $overviewView);
        $this->assertStringNotContainsString('data: [25, 20, 60, 41, 66, 45, 80, 25, 20, 60, 41, 12]', $overviewView);
        $this->assertStringContainsString('@forelse ($dailyEarlyBirds as $staff)', $overviewView);
        $this->assertStringContainsString('@forelse ($dailyRunningLate as $staff)', $overviewView);
        $this->assertStringContainsString('@forelse ($dailyBusinessTrips as $staff)', $overviewView);
        $this->assertStringContainsString('@forelse ($dailyLeaves as $staff)', $overviewView);
        $this->assertStringContainsString('No clock-in data available.', $overviewView);
        $this->assertStringContainsString('No late arrival data available.', $overviewView);
        $this->assertStringContainsString('No business trip data available.', $overviewView);
        $this->assertStringContainsString('No leave data available.', $overviewView);
        $this->assertStringNotContainsString('files/employees', $overviewView);
        $this->assertStringNotContainsString('admin-attendance-rank-trend', $overviewView);
        $this->assertStringNotContainsString('$staff[\'direction\']', $overviewView);
        $this->assertStringContainsString("#{{ \$staff['rank'] }}", $overviewView);
    }
}
