<?php

namespace Tests\Feature;

use App\View\Composers\AttendanceProfileComposer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AttendanceNamingConventionTest extends TestCase
{
    public function test_attendance_module_uses_english_route_and_view_names(): void
    {
        $expectedRouteNames = [
            'attendance',
            'attendance.today',
            'attendance.store',
            'attendance.update',
            'attendance.current-ip',
            'attendance.verify-telegram-username',
            'attendance.exceptions.store',
            'attendance.reports',
            'attendance.reports.datatable',
            'attendance.reports.export',
            'attendance.leave-requests',
            'attendance.leave-requests.cards',
            'attendance.leave-requests.upload-image',
            'attendance.leave-requests.delete-uploaded-image',
            'attendance.leave-requests.store',
            'attendance.leave-requests.update',
            'attendance.leave-requests.destroy',
            'attendance.overtimes',
            'attendance.overtimes.datatable',
            'attendance.overtimes.detail',
            'attendance.overtimes.store',
            'attendance.overtimes.show',
            'attendance.overtimes.tasks.store',
            'attendance.overtimes.tasks.update',
            'attendance.overtimes.tasks.destroy',
            'attendance.overtimes.update',
            'attendance.overtimes.destroy',
            'attendance.business-trips',
        ];

        foreach ($expectedRouteNames as $expectedRouteName) {
            $this->assertNotNull(Route::getRoutes()->getByName($expectedRouteName), $expectedRouteName);
        }

        $this->assertSame('attendance/overview', Route::getRoutes()->getByName('attendance')?->uri());
        $this->assertSame('attendance/today', Route::getRoutes()->getByName('attendance.today')?->uri());
        $this->assertSame('attendance/overtimes/{attendanceOvertime}', Route::getRoutes()->getByName('attendance.overtimes.detail')?->uri());
        $this->assertSame('attendance/overtimes/{attendanceOvertime}/data', Route::getRoutes()->getByName('attendance.overtimes.show')?->uri());
        $this->assertSame('attendance/overtimes/{attendanceOvertime}/tasks', Route::getRoutes()->getByName('attendance.overtimes.tasks.store')?->uri());
        $this->assertSame('attendance/overtimes/{attendanceOvertime}/tasks/{projectTask}', Route::getRoutes()->getByName('attendance.overtimes.tasks.update')?->uri());
        $this->assertSame('attendance/overtimes/{attendanceOvertime}/tasks/{projectTask}', Route::getRoutes()->getByName('attendance.overtimes.tasks.destroy')?->uri());

        $this->assertNull(Route::getRoutes()->getByName('absensi'));
        $this->assertNull(Route::getRoutes()->getByName('absensi.izin'));
        $this->assertNull(Route::getRoutes()->getByName('absensi.lembur'));
        $this->assertNull(Route::getRoutes()->getByName('absensi.cuti'));
        $this->assertNull(Route::getRoutes()->getByName('absensi.dinas'));
        $this->assertNull(Route::getRoutes()->getByName('attendance.holidays'));

        $expectedViewNames = [
            'staff_attendance.overview.index',
            'staff_attendance.attendance.index',
            'staff_attendance.reports.index',
            'staff_attendance.leave-requests.index',
            'staff_attendance.leave-requests.partials.history-list-cards',
            'staff_attendance.overtimes.index',
            'staff_attendance.overtimes.detail',
            'staff_attendance.business-trips.index',
            'staff_attendance.components.attendance-cards',
            'staff_attendance.components.card-analytics',
            'staff_attendance.layouts.profile-header',
            'staff_attendance.layouts.profile-index',
            'staff_attendance.layouts.profile-navbar',
        ];

        foreach ($expectedViewNames as $expectedViewName) {
            $this->assertTrue(View::exists($expectedViewName), $expectedViewName);
        }

        $legacyFlatViewNames = [
            'attendance.attendance',
            'attendance.reports',
            'attendance.reports.excel',
            'attendance.reports.pdf',
            'attendance.report-pdf',
            'attendance.leave-requests',
            'attendance.overtimes',
            'attendance.business-trips',
        ];

        foreach ($legacyFlatViewNames as $legacyFlatViewName) {
            $this->assertFalse(View::exists($legacyFlatViewName), $legacyFlatViewName);
        }

        $this->assertDirectoryDoesNotExist(resource_path('views/absensi'));
        $this->assertDirectoryDoesNotExist(resource_path('views/attendance'));
        $this->assertDirectoryExists(resource_path('views/staff_attendance'));
        $this->assertDirectoryExists(resource_path('views/admin_attendance'));
        $this->assertFalse(View::exists('staff_attendance.holidays'));
        $this->assertFileExists(public_path('assets/css/attendance.css'));
        $this->assertFileDoesNotExist(public_path('assets/css/absensi.css'));
        $this->assertTrue(class_exists(AttendanceProfileComposer::class));

        $appServiceProvider = File::get(app_path('Providers/AppServiceProvider.php'));
        $attendanceCss = File::get(public_path('assets/css/attendance.css'));
        $profileNavbarView = File::get(resource_path('views/staff_attendance/layouts/profile-navbar.blade.php'));

        $this->assertStringContainsString("'staff_attendance.overview.index'", $appServiceProvider);
        $this->assertStringContainsString("'staff_attendance.layouts.profile-header'", $appServiceProvider);
        $this->assertStringContainsString("'staff_attendance.components.card-analytics'", $appServiceProvider);
        $this->assertStringContainsString('AttendanceProfileComposer::class', $appServiceProvider);
        $this->assertStringContainsString("request()->routeIs('attendance.today*')", $profileNavbarView);
        $this->assertStringContainsString("request()->routeIs('attendance.reports*')", $profileNavbarView);
        $this->assertStringContainsString("request()->routeIs('attendance.leave-requests*')", $profileNavbarView);
        $this->assertStringContainsString('.attendance-tabs .attendance-tab-btn.active {', $attendanceCss);
        $this->assertStringContainsString('border-bottom-color: var(--bs-primary);', $attendanceCss);

        $attendanceOverviewView = File::get(resource_path('views/staff_attendance/overview/index.blade.php'));

        foreach ([
            'function pieChart()',
            'function radialBar()',
            'function donut()',
            'function barChart_3()',
            'function lineChart_3()',
            'function barChart_1()',
            'function lineChart_2()',
            'pieChart();',
            'radialBar();',
            'donut();',
            'barChart_3();',
            'lineChart_3();',
            'barChart_1();',
            'lineChart_2();',
        ] as $chartInitializer) {
            $this->assertStringContainsString($chartInitializer, $attendanceOverviewView);
        }

        $this->assertStringContainsString('<div class="row align-items-stretch">', $attendanceOverviewView);
        $this->assertSame(2, substr_count($attendanceOverviewView, '<div class="card flex-fill">'));
        $this->assertStringContainsString('row row-cols-2 g-2 list-unstyled mb-0 mx-auto w-100', $attendanceOverviewView);
        $this->assertStringContainsString('$attendanceOverviewSeries = array_values($profileAttendanceOverviewSeries ?? [0, 0, 0, 0]);', $attendanceOverviewView);
        $this->assertStringContainsString('$attendanceOverviewChartSeries = array_sum($attendanceOverviewSeries) > 0 ? $attendanceOverviewSeries : [1];', $attendanceOverviewView);
        $this->assertStringContainsString('$attendanceOverviewChartColors = array_sum($attendanceOverviewSeries) > 0', $attendanceOverviewView);
        $this->assertStringContainsString('Attendance Overview ({{ $attendanceOverviewMonthLabel }})', $attendanceOverviewView);
        $this->assertStringContainsString('Progress ({{ $attendanceOverviewMonthLabel }})', $attendanceOverviewView);
        $this->assertStringContainsString('Days Worked ({{ $attendanceDaysCount }}/{{ $workingDaysCount }} Days)', $attendanceOverviewView);
        $this->assertStringContainsString('series: @json($attendanceOverviewChartSeries)', $attendanceOverviewView);
        $this->assertStringContainsString('colors: @json($attendanceOverviewChartColors)', $attendanceOverviewView);
        $this->assertStringContainsString('series: [{{ $attendanceProgressPercent }}]', $attendanceOverviewView);
        $this->assertStringContainsString('On Time ({{ $progressOnTimePercent }}%)', $attendanceOverviewView);
        $this->assertStringContainsString('Late ({{ $progressLatePercent }}%)', $attendanceOverviewView);
        $this->assertStringContainsString('Weekly Required Hours ({{ $weeklyRequiredHoursPercent }}%)', $attendanceOverviewView);
        $this->assertStringContainsString('Overtime Logged ({{ $weeklyOvertimeHoursPercent }}%)', $attendanceOverviewView);
        $this->assertStringContainsString('$defaultYearMonthLabels = [', $attendanceOverviewView);
        $this->assertStringContainsString('$yearMonthLabels = count($yearMonthLabels) === 12 ? $yearMonthLabels : $defaultYearMonthLabels;', $attendanceOverviewView);
        $this->assertStringContainsString('Attendance Overview ({{ $yearChartYear }})', $attendanceOverviewView);
        $this->assertStringContainsString('Leave Overview ({{ $yearChartYear }})', $attendanceOverviewView);
        $this->assertStringContainsString('Business Trip Overview ({{ $yearChartYear }})', $attendanceOverviewView);
        $this->assertStringContainsString('Overtime Overview ({{ $yearChartYear }})', $attendanceOverviewView);
        $this->assertStringContainsString('labels: @json($yearMonthLabels)', $attendanceOverviewView);
        $this->assertStringContainsString('data: @json($yearAttendanceOnTimeSeries)', $attendanceOverviewView);
        $this->assertStringContainsString('data: @json($yearAttendanceLateSeries)', $attendanceOverviewView);
        $this->assertStringContainsString('data: @json($yearAttendanceLeaveSeries)', $attendanceOverviewView);
        $this->assertStringContainsString('data: @json($yearLeaveSeries)', $attendanceOverviewView);
        $this->assertStringContainsString('data: @json($yearSickSeries)', $attendanceOverviewView);
        $this->assertStringContainsString('data: @json($yearBusinessTripSeries)', $attendanceOverviewView);
        $this->assertStringContainsString('data: @json($yearOvertimeHoursSeries)', $attendanceOverviewView);
        $this->assertStringNotContainsString('series: [16, 1, 1, 2]', $attendanceOverviewView);
        $this->assertStringNotContainsString('series: [77]', $attendanceOverviewView);
        $this->assertStringNotContainsString('Days Worked (17/22 Days)', $attendanceOverviewView);
        $this->assertStringNotContainsString('On Time (45%)', $attendanceOverviewView);
        $this->assertStringNotContainsString('Late (78%)', $attendanceOverviewView);
        $this->assertStringNotContainsString('32 Hrs / 40 Hrs Per Week', $attendanceOverviewView);
        $this->assertStringNotContainsString('data: [18, 17, 20, 19, 16, 0, 0, 0, 0, 0, 0, 0]', $attendanceOverviewView);
        $this->assertStringNotContainsString('data: [1, 2, 1, 3, 2, 0, 0, 0, 0, 0, 0, 0]', $attendanceOverviewView);
        $this->assertStringNotContainsString('data: [4, 8, 6, 10, 18, 0, 0, 0, 0, 0, 0, 0]', $attendanceOverviewView);
        $this->assertStringContainsString("@include('staff_attendance.components.card-analytics')", $attendanceOverviewView);
        $this->assertStringContainsString('.attendance-rate-mobile-slider {', $attendanceOverviewView);
        $this->assertStringContainsString('.attendance-rate-mobile-slide {', $attendanceOverviewView);

        $attendanceTodayView = File::get(resource_path('views/staff_attendance/attendance/index.blade.php'));
        $attendanceReportsView = File::get(resource_path('views/staff_attendance/reports/index.blade.php'));
        $attendanceCardAnalyticsView = File::get(resource_path('views/staff_attendance/components/card-analytics.blade.php'));

        $this->assertStringContainsString("@include('staff_attendance.components.card-analytics')", $attendanceTodayView);
        $this->assertStringContainsString('.attendance-rate-mobile-slider {', $attendanceTodayView);
        $this->assertStringContainsString('.attendance-rate-mobile-slide {', $attendanceTodayView);
        $this->assertStringContainsString('.attendance-tabs .attendance-tab-btn.active {', $attendanceTodayView);
        $this->assertStringContainsString('border-bottom-color: var(--bs-primary);', $attendanceTodayView);
        $this->assertStringContainsString('.attendance-tabs .attendance-tab-btn.active {', $attendanceReportsView);
        $this->assertStringContainsString('border-bottom-color: var(--bs-primary);', $attendanceReportsView);
        $this->assertStringContainsString('row attendance-rate-mobile-slider', $attendanceCardAnalyticsView);
        $this->assertStringContainsString('Attendance Rate', $attendanceCardAnalyticsView);
        $this->assertStringContainsString('On Time Rate', $attendanceCardAnalyticsView);
        $this->assertStringContainsString('Lateness Rate', $attendanceCardAnalyticsView);
        $this->assertStringContainsString('Overtime Rate', $attendanceCardAnalyticsView);
        $this->assertStringContainsString('$attendanceRatePercent = (int) ($profileAttendanceRatePercent ?? 0);', $attendanceCardAnalyticsView);
        $this->assertStringContainsString('$onTimeRatePercent = (int) ($profileOnTimeRatePercent ?? 0);', $attendanceCardAnalyticsView);
        $this->assertStringContainsString('$latenessRatePercent = (int) ($profileLatenessRatePercent ?? 0);', $attendanceCardAnalyticsView);
        $this->assertStringContainsString('$overtimeRatePercent = (int) ($profileOvertimeRatePercent ?? 0);', $attendanceCardAnalyticsView);
        $this->assertStringNotContainsString('>100%</span>', $attendanceCardAnalyticsView);
        $this->assertStringNotContainsString('>95%</span>', $attendanceCardAnalyticsView);
        $this->assertStringNotContainsString('>60%</span>', $attendanceCardAnalyticsView);
        $this->assertSame(4, substr_count($attendanceCardAnalyticsView, 'col-md-3 col-sm-6 attendance-rate-mobile-slide'));

        $overtimeIndexView = File::get(resource_path('views/staff_attendance/overtimes/index.blade.php'));

        $this->assertStringContainsString("\$overtimeItem['detail_url']", $overtimeIndexView);
        $this->assertStringNotContainsString('attendance-overtime-details.html', $overtimeIndexView);

        $businessTripView = File::get(resource_path('views/staff_attendance/business-trips/index.blade.php'));

        $this->assertStringContainsString('row business-trip-summary-mobile-slider', $businessTripView);
        $this->assertStringContainsString('.business-trip-summary-mobile-slider {', $businessTripView);
        $this->assertStringContainsString('.business-trip-summary-mobile-slide {', $businessTripView);
        $this->assertSame(8, substr_count($businessTripView, 'col-md-3 col-sm-6 business-trip-summary-mobile-slide'));

        $profileHeaderView = File::get(resource_path('views/staff_attendance/layouts/profile-header.blade.php'));

        $this->assertStringContainsString('.mobile-stats-slider {', $profileHeaderView);
        $this->assertStringContainsString('margin-top: 1rem;', $profileHeaderView);

        $commonJsView = File::get(resource_path('views/layouts/commonjs.blade.php'));
        $profileIndexView = File::get(resource_path('views/staff_attendance/layouts/profile-index.blade.php'));
        $adminRecapDetailView = File::get(resource_path('views/admin_attendance/recap_attendance/detail-employees.blade.php'));

        $this->assertStringContainsString('vendor/chart-js/chart.bundle.min.js', $attendanceOverviewView);
        $this->assertStringNotContainsString('<script src="vendor/chart-js/chart.bundle.min.js', $commonJsView);
        $this->assertStringContainsString('vendor/peity/jquery.peity.min.js', $commonJsView);
        $this->assertStringNotContainsString('vendor/apexcharts/dist/apexcharts.min.js', $commonJsView);
        $this->assertStringContainsString('vendor/apexcharts/dist/apexcharts.min.js', $profileIndexView);
        $this->assertStringContainsString('vendor/apexcharts/dist/apexcharts.min.js', $adminRecapDetailView);
    }
}
