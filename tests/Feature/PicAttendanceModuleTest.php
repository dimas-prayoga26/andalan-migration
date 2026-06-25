<?php

namespace Tests\Feature;

use App\Http\Controllers\PicAttendance\PicAttendanceController;
use App\Http\Controllers\PicAttendance\PicAttendanceLeaveController;
use App\Http\Controllers\PicAttendance\PicAttendanceOvertimeController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PicAttendanceModuleTest extends TestCase
{
    public function test_pic_attendance_routes_use_separate_controllers(): void
    {
        $attendanceRoute = Route::getRoutes()->getByName('pic-attendance.attendance');
        $attendanceDatatableRoute = Route::getRoutes()->getByName('pic-attendance.attendance.monthly-datatable');
        $leaveRoute = Route::getRoutes()->getByName('pic-attendance.leave');
        $leavePendingDatatableRoute = Route::getRoutes()->getByName('pic-attendance.leave.pending-datatable');
        $leaveSupervisorReviewRoute = Route::getRoutes()->getByName('pic-attendance.leave.supervisor-review.update');
        $overtimeRoute = Route::getRoutes()->getByName('pic-attendance.overtime');
        $overtimeStoreRoute = Route::getRoutes()->getByName('pic-attendance.overtime.store');
        $overtimeDetailRoute = Route::getRoutes()->getByName('pic-attendance.overtime.detail');

        $this->assertSame(PicAttendanceController::class.'@index', $attendanceRoute?->getActionName());
        $this->assertSame(PicAttendanceController::class.'@monthlyDatatable', $attendanceDatatableRoute?->getActionName());
        $this->assertSame(PicAttendanceLeaveController::class.'@index', $leaveRoute?->getActionName());
        $this->assertSame(PicAttendanceLeaveController::class.'@pendingDatatable', $leavePendingDatatableRoute?->getActionName());
        $this->assertSame(PicAttendanceLeaveController::class.'@updateSupervisorReview', $leaveSupervisorReviewRoute?->getActionName());
        $this->assertSame(PicAttendanceOvertimeController::class.'@index', $overtimeRoute?->getActionName());
        $this->assertSame(PicAttendanceOvertimeController::class.'@store', $overtimeStoreRoute?->getActionName());
        $this->assertSame(PicAttendanceOvertimeController::class.'@detail', $overtimeDetailRoute?->getActionName());
        $this->assertSame('pic-attendance', $attendanceRoute?->uri());
        $this->assertSame('pic-attendance/leave', $leaveRoute?->uri());
        $this->assertSame('pic-attendance/leave/detail/{uid}/supervisor-review', $leaveSupervisorReviewRoute?->uri());
        $this->assertSame('pic-attendance/overtime', $overtimeRoute?->uri());
        $this->assertSame('pic-attendance/overtime', $overtimeStoreRoute?->uri());
        $this->assertSame('pic-attendance/overtime/detail/{uid}', $overtimeDetailRoute?->uri());
    }

    public function test_pic_module_has_its_own_views_navigation_and_permission(): void
    {
        $sidebar = File::get(resource_path('views/layouts/sidebar.blade.php'));
        $navigation = File::get(resource_path('views/pic_attendance/layout/navbar.blade.php'));
        $permissionSeeder = File::get(database_path('seeders/PositionPermissionSeeder.php'));
        $authorizationController = File::get(app_path('Http/Controllers/AuthorizationController.php'));
        $leaveController = File::get(app_path('Http/Controllers/PicAttendance/PicAttendanceLeaveController.php'));
        $leaveView = File::get(resource_path('views/pic_attendance/leave/detail.blade.php'));
        $overtimeView = File::get(resource_path('views/pic_attendance/overtime/index.blade.php'));

        $this->assertTrue(View::exists('pic_attendance.attendance.index'));
        $this->assertTrue(View::exists('pic_attendance.attendance.detail-employees'));
        $this->assertTrue(View::exists('pic_attendance.leave.index'));
        $this->assertTrue(View::exists('pic_attendance.leave.detail'));
        $this->assertTrue(View::exists('pic_attendance.overtime.index'));
        $this->assertTrue(View::exists('pic_attendance.overtime.detail'));
        $this->assertStringContainsString('view-pic-attendance', $sidebar);
        $this->assertStringContainsString("route('pic-attendance.attendance')", $sidebar);
        $this->assertStringContainsString("route('pic-attendance.attendance')", $navigation);
        $this->assertStringContainsString("route('pic-attendance.leave')", $navigation);
        $this->assertStringContainsString("route('pic-attendance.overtime')", $navigation);
        $this->assertStringNotContainsString('Business Trip', $navigation);
        $this->assertStringContainsString('Overtime', $navigation);
        $this->assertStringContainsString("'view-pic-attendance'", $permissionSeeder);
        $this->assertStringContainsString("'Supervisor' => array_merge", $permissionSeeder);
        $this->assertStringContainsString("'System Administrator' => \$allPermissionsWithoutPic", $permissionSeeder);
        $this->assertStringContainsString("'view-pic-attendance', 'view-director-attendance'", $permissionSeeder);
        $this->assertStringContainsString("'view-pic-attendance' => ['section' => 'HR Management', 'label' => 'PIC']", $authorizationController);
        $this->assertStringContainsString('employee_pic_assignments', $leaveController);
        $this->assertStringContainsString('updateSupervisorReview', $leaveController);
        $this->assertStringContainsString("'event_type' => 'supervisor_review'", $leaveController);
        $this->assertStringContainsString('$query = $this->applySupervisorApprovedReviewFilter($query);', $leaveController);
        $this->assertStringContainsString('pic-attendance.leave.supervisor-review.update', $leaveView);
        $this->assertStringContainsString('w-100 btn-lg', $leaveView);
        $this->assertStringContainsString('Add Overtime', $overtimeView);
        $this->assertStringContainsString('picAddOvertimeModal', $overtimeView);
        $this->assertStringContainsString("route('pic-attendance.overtime.store')", $overtimeView);
    }
}
