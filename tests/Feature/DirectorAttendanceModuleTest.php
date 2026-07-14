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
        $directorOvertimeController = File::get(app_path('Http/Controllers/DirectorAttendance/DirectorAttendanceOvertimeController.php'));
        $directorOvertimeDetailView = File::get(resource_path('views/director_attendance/overtime/detail.blade.php'));
        $sidebar = File::get(resource_path('views/layouts/sidebar.blade.php'));
        $navigation = File::get(resource_path('views/director_attendance/layout/navbar.blade.php'));
        $permissionSeeder = File::get(database_path('seeders/PositionPermissionSeeder.php'));
        $legacySeeder = File::get(database_path('seeders/LegacySqlUserSeeder.php'));
        $authorizationController = File::get(app_path('Http/Controllers/AuthorizationController.php'));

        $this->assertStringContainsString('extends AttendanceRecapController', $directorController);
        $this->assertStringContainsString('director_attendance.attendance.index', $directorController);
        $this->assertStringContainsString('director_attendance.attendance.detail-employees', $directorController);
        $this->assertStringNotContainsString('PicAttendanceController', $directorController);
        $this->assertStringContainsString('$actualEndDateTime = $this->formatActualEndDateTime($overtime);', $directorOvertimeController);
        $this->assertStringContainsString("'verified_datetime' => \$actualEndDateTime !== '-' ? \$actualEndDateTime : \$this->formatLifecycleDateTime(\$verificationLog)", $directorOvertimeController);
        $this->assertStringContainsString("'approved_time_range' => \$approvedTimeRange", $directorOvertimeController);
        $this->assertStringContainsString("'approved_duration' => \$approvedDuration", $directorOvertimeController);
        $this->assertStringContainsString("'is_task_hours_verified' => \$taskHoursVerified", $directorOvertimeController);
        $this->assertStringContainsString('private function isTaskHoursVerified(AttendanceOvertime $overtime): bool', $directorOvertimeController);
        $this->assertStringContainsString('private function formatActualEndDateTime(AttendanceOvertime $overtime): string', $directorOvertimeController);
        $this->assertStringContainsString("'overtimeDetail' => \$this->directorOvertimeDetailSummary(\$overtime, \$authenticatedUser)", $directorOvertimeController);
        $this->assertStringContainsString('private function directorOvertimeDetailSummary(AttendanceOvertime $overtime, ?User $defaultDirectorApprover = null): array', $directorOvertimeController);
        $this->assertStringContainsString('$directorApprover = $directorApprovalLog?->actor instanceof User', $directorOvertimeController);
        $this->assertStringContainsString(': $defaultDirectorApprover;', $directorOvertimeController);
        $this->assertStringNotContainsString("['label' => \$plannedTimeLabel, 'strike' => true]", $directorOvertimeController);
        $this->assertStringContainsString("'director_approver' => \$directorApprover instanceof User ? \$this->userDisplayName(\$directorApprover) : '-'", $directorOvertimeController);
        $this->assertStringContainsString("'director_datetime' => \$this->formatLifecycleDateTime(\$directorApprovalLog)", $directorOvertimeController);
        $this->assertStringContainsString("\$tableBuilder->buildForContext('director', null, null", $directorOvertimeController);
        $this->assertStringContainsString("'overtimeSummary' => \$metricBuilder->summarizeForActiveEmployees()", $directorOvertimeController);
        $this->assertStringContainsString('private function directorOvertimeQuery(): Builder', $directorOvertimeController);
        $this->assertStringContainsString("'payment_disbursement',", $directorOvertimeController);
        $this->assertStringContainsString("'description' => (string) (\$projectTask->description ?? '')", $directorOvertimeController);
        $this->assertStringContainsString("'status_value' => \$status !== '' ? \$status : 'pending'", $directorOvertimeController);
        $this->assertStringContainsString("'date_range_label' => \$this->taskDateRangeLabel", $directorOvertimeController);
        $this->assertStringContainsString("'status_label' => \$this->projectTaskDetailStatusLabel", $directorOvertimeController);
        $this->assertStringContainsString("'task_category_label' => \$projectTask->project_id !== null ? 'Project Task' : 'Daily Task'", $directorOvertimeController);
        $this->assertStringContainsString("'assigned_by' => trim((string) (\$projectTask->assignedBy?->username ?? 'self')) ?: 'self'", $directorOvertimeController);
        $this->assertStringContainsString("'attachment_path' => (string) (\$projectTask->attachment_path ?? '')", $directorOvertimeController);
        $this->assertStringContainsString('private function projectTaskDetailStatusLabel', $directorOvertimeController);
        $this->assertStringContainsString('private function taskDateRangeLabel', $directorOvertimeController);
        $this->assertStringContainsString('private function formatDateInputValue(mixed $dateValue): string', $directorOvertimeController);
        $this->assertStringNotContainsString('private function currentCompanyIdFor', $directorOvertimeController);
        $this->assertStringNotContainsString("->where('current_company_id', trim(\$companyId))", $directorOvertimeController);
        $this->assertStringContainsString('Compensation & Payroll Details', $directorOvertimeDetailView);
        $this->assertStringContainsString('<span>Rate Multiplier</span>', $directorOvertimeDetailView);
        $this->assertStringContainsString('<span>Estimated Calculated Earnings</span>', $directorOvertimeDetailView);
        $this->assertStringContainsString('Staff Start ClockIn', $directorOvertimeDetailView);
        $this->assertStringContainsString('Staff Start ClockOut', $directorOvertimeDetailView);
        $this->assertStringContainsString('$overtimeDetail[\'actual_start_time\']', $directorOvertimeDetailView);
        $this->assertStringContainsString('$overtimeDetail[\'actual_end_time\']', $directorOvertimeDetailView);
        $this->assertStringContainsString('$overtimeDetail[\'actual_time_range\']', $directorOvertimeDetailView);
        $this->assertStringContainsString('$overtimeDetail[\'approved_time_range\']', $directorOvertimeDetailView);
        $this->assertStringContainsString('$overtimeDetail[\'actual_duration\']', $directorOvertimeDetailView);
        $this->assertStringContainsString('$overtimeDetail[\'approved_duration\']', $directorOvertimeDetailView);
        $this->assertStringNotContainsString('Scheduled Start', $directorOvertimeDetailView);
        $this->assertStringNotContainsString('Scheduled End', $directorOvertimeDetailView);
        $this->assertStringNotContainsString('$overtimeDetail[\'planned_time_range\']', $directorOvertimeDetailView);
        $this->assertStringNotContainsString('$overtimeDetail[\'planned_duration\']', $directorOvertimeDetailView);
        $this->assertStringNotContainsString('Rp. 100.000', $directorOvertimeDetailView);
        $this->assertStringNotContainsString('<span class="text-gray fw-semibold">1.5x</span>', $directorOvertimeDetailView);
        $this->assertStringContainsString('<span class="text-gray fw-semibold text-decoration-line-through">-</span>', $directorOvertimeDetailView);
        $this->assertStringContainsString('$taskItemPayload = $taskItems->keyBy(\'id\')->toArray();', $directorOvertimeDetailView);
        $this->assertStringContainsString('director-overtime-task-detail', $directorOvertimeDetailView);
        $this->assertStringContainsString('id="directorTaskDetailModal"', $directorOvertimeDetailView);
        $this->assertStringContainsString('Task Details', $directorOvertimeDetailView);
        $this->assertStringContainsString('id="directorTaskDetailCategory"', $directorOvertimeDetailView);
        $this->assertStringContainsString('id="directorTaskDetailProject"', $directorOvertimeDetailView);
        $this->assertStringContainsString('id="directorTaskDetailAssignedBy"', $directorOvertimeDetailView);
        $this->assertStringContainsString('var taskItemsById = @js($taskItemPayload ?? []);', $directorOvertimeDetailView);
        $this->assertStringContainsString("$('#directorTaskDetailTitle').text(nullableTaskText(taskItem.title));", $directorOvertimeDetailView);
        $this->assertStringContainsString("$('#directorTaskDetailBlockers').text(nullableTaskText(taskItem.blockers));", $directorOvertimeDetailView);
        $this->assertStringContainsString("$('#directorTaskDetailAssignedBy').text('@' + (nullableTaskValue(taskItem.assigned_by) || 'self'));", $directorOvertimeDetailView);
        $this->assertStringContainsString("renderTaskAttachment('#directorTaskDetailAttachment', taskItem.attachment_path);", $directorOvertimeDetailView);
        $this->assertStringNotContainsString('currentCompanyIdFor', $directorController);
        $this->assertStringNotContainsString('current_company_id', $directorController);
        $this->assertStringContainsString('assets/vendor/apexcharts/dist/apexcharts.min.js', $directorDetailView);
        $this->assertStringContainsString("typeof ApexCharts === 'undefined'", $directorDetailView);
        $this->assertStringContainsString('$.fn.peity', $directorDetailView);
        $this->assertStringContainsString('<th class="mw-160">Address</th>', $directorDetailView);
        $this->assertStringContainsString("{ data: 'location_address', render: function(data) { return escapeHtml(data); } }", $directorDetailView);
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
