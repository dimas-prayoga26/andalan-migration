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
            'attendance.leave-requests.upload-image',
            'attendance.leave-requests.delete-uploaded-image',
            'attendance.leave-requests.store',
            'attendance.leave-requests.destroy',
            'attendance.overtimes',
            'attendance.overtimes.datatable',
            'attendance.overtimes.store',
            'attendance.overtimes.show',
            'attendance.overtimes.update',
            'attendance.overtimes.destroy',
            'attendance.business-trips',
        ];

        foreach ($expectedRouteNames as $expectedRouteName) {
            $this->assertNotNull(Route::getRoutes()->getByName($expectedRouteName), $expectedRouteName);
        }

        $this->assertSame('attendance', Route::getRoutes()->getByName('attendance')?->uri());
        $this->assertSame('attendance/today', Route::getRoutes()->getByName('attendance.today')?->uri());

        $this->assertNull(Route::getRoutes()->getByName('absensi'));
        $this->assertNull(Route::getRoutes()->getByName('absensi.izin'));
        $this->assertNull(Route::getRoutes()->getByName('absensi.lembur'));
        $this->assertNull(Route::getRoutes()->getByName('absensi.cuti'));
        $this->assertNull(Route::getRoutes()->getByName('absensi.dinas'));
        $this->assertNull(Route::getRoutes()->getByName('attendance.holidays'));

        $expectedViewNames = [
            'attendance.attendance.index',
            'attendance.index',
            'attendance.reports.index',
            'attendance.reports.pdf',
            'attendance.leave-requests.index',
            'attendance.overtimes.index',
            'attendance.business-trips.index',
            'attendance.components.attendance-cards',
            'attendance.layouts.profile-header',
            'attendance.layouts.profile-index',
            'attendance.layouts.profile-navbar',
        ];

        foreach ($expectedViewNames as $expectedViewName) {
            $this->assertTrue(View::exists($expectedViewName), $expectedViewName);
        }

        $legacyFlatViewNames = [
            'attendance.attendance',
            'attendance.reports',
            'attendance.report-pdf',
            'attendance.leave-requests',
            'attendance.overtimes',
            'attendance.business-trips',
        ];

        foreach ($legacyFlatViewNames as $legacyFlatViewName) {
            $this->assertFalse(View::exists($legacyFlatViewName), $legacyFlatViewName);
        }

        $this->assertDirectoryDoesNotExist(resource_path('views/absensi'));
        $this->assertFalse(View::exists('attendance.holidays'));
        $this->assertFileExists(public_path('assets/css/attendance.css'));
        $this->assertFileDoesNotExist(public_path('assets/css/absensi.css'));
        $this->assertTrue(class_exists(AttendanceProfileComposer::class));

        $attendanceOverviewView = File::get(resource_path('views/attendance/index.blade.php'));

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

        $commonJsView = File::get(resource_path('views/layouts/commonjs.blade.php'));

        foreach ([
            'vendor/chart-js/chart.bundle.min.js',
            'vendor/apexcharts/dist/apexcharts.min.js',
            'vendor/peity/jquery.peity.min.js',
        ] as $chartDependency) {
            $this->assertStringContainsString($chartDependency, $commonJsView);
        }
    }
}
