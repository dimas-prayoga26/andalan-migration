<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAttendance\AttendanceRecapController;
use App\Http\Controllers\DirectorAttendance\DirectorAttendanceController;
use App\Http\Controllers\DirectorAttendance\DirectorAttendanceOvertimeController;
use App\Http\Controllers\DirectorAttendance\DirectorAttendanceTaskController;
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
        $taskRoute = Route::getRoutes()->getByName('director-attendance.task');
        $taskDatatableRoute = Route::getRoutes()->getByName('director-attendance.task.datatable');

        $this->assertSame(DirectorAttendanceController::class.'@index', $attendanceRoute?->getActionName());
        $this->assertSame(DirectorAttendanceController::class.'@monthlyDatatable', $attendanceMonthlyRoute?->getActionName());
        $this->assertSame(DirectorAttendanceController::class.'@employeeDetails', $attendanceDetailRoute?->getActionName());
        $this->assertSame(DirectorAttendanceController::class.'@employeeDetailsDatatable', $attendanceDetailDatatableRoute?->getActionName());
        $this->assertSame(DirectorAttendanceOvertimeController::class.'@index', $overtimeRoute?->getActionName());
        $this->assertSame(DirectorAttendanceOvertimeController::class.'@detail', $overtimeDetailRoute?->getActionName());
        $this->assertSame(DirectorAttendanceTaskController::class.'@index', $taskRoute?->getActionName());
        $this->assertSame(DirectorAttendanceTaskController::class.'@datatable', $taskDatatableRoute?->getActionName());
        $this->assertSame('director-attendance', $attendanceRoute?->uri());
        $this->assertSame('director-attendance/monthly-datatable', $attendanceMonthlyRoute?->uri());
        $this->assertSame('director-attendance/attendance/{employee}', $attendanceDetailRoute?->uri());
        $this->assertSame('director-attendance/attendance/{employee}/datatable', $attendanceDetailDatatableRoute?->uri());
        $this->assertSame('director-attendance/overtime', $overtimeRoute?->uri());
        $this->assertSame('director-attendance/overtime/detail/{uid}', $overtimeDetailRoute?->uri());
        $this->assertSame('director-attendance/task', $taskRoute?->uri());
        $this->assertContains('position.permission:view-director-attendance', $taskRoute?->gatherMiddleware() ?? []);
        $this->assertSame('director-attendance/task/datatable', $taskDatatableRoute?->uri());
        $this->assertContains('position.permission:view-director-attendance', $taskDatatableRoute?->gatherMiddleware() ?? []);
        $this->assertTrue(is_a(DirectorAttendanceController::class, AttendanceRecapController::class, true));
    }

    public function test_director_module_has_its_own_views_navigation_and_permission(): void
    {
        $directorController = File::get(app_path('Http/Controllers/DirectorAttendance/DirectorAttendanceController.php'));
        $directorDetailView = File::get(resource_path('views/director_attendance/attendance/detail-employees.blade.php'));
        $directorOvertimeController = File::get(app_path('Http/Controllers/DirectorAttendance/DirectorAttendanceOvertimeController.php'));
        $directorOvertimeDetailView = File::get(resource_path('views/director_attendance/overtime/detail.blade.php'));
        $directorTaskController = File::get(app_path('Http/Controllers/DirectorAttendance/DirectorAttendanceTaskController.php'));
        $directorTaskView = File::get(resource_path('views/director_attendance/task/index.blade.php'));
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
        $this->assertStringContainsString('$pendingTableData = $tableBuilder->buildForContext(', $directorOvertimeController);
        $this->assertStringContainsString('$approvedTableData = $tableBuilder->buildForContext(', $directorOvertimeController);
        $this->assertStringContainsString("'director',", $directorOvertimeController);
        $this->assertStringContainsString("\$request->query('card_month', \$legacyMonth)", $directorOvertimeController);
        $this->assertStringContainsString("\$request->query('pending_month', \$legacyMonth)", $directorOvertimeController);
        $this->assertStringContainsString("\$request->query('approved_month', \$legacyMonth)", $directorOvertimeController);
        $this->assertStringContainsString('$overtimeSummary = $metricBuilder->summarizeForPeriod(null, null, $cardMonth, $cardYear);', $directorOvertimeController);
        $this->assertStringContainsString("'overtimeSummary' => \$overtimeSummary", $directorOvertimeController);
        $this->assertStringContainsString("'overtimeMetricCards' => \$metricBuilder->metricCards(\$overtimeSummary)", $directorOvertimeController);
        $this->assertStringContainsString("'selectedCardMonth' => \$cardMonth", $directorOvertimeController);
        $this->assertStringContainsString("'selectedPendingMonth' => \$pendingTableData['selectedMonth']", $directorOvertimeController);
        $this->assertStringContainsString("'selectedApprovedMonth' => \$approvedTableData['selectedMonth']", $directorOvertimeController);
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
        $this->assertTrue(View::exists('director_attendance.task.index'));
        $this->assertStringContainsString('view-director-attendance', $sidebar);
        $this->assertStringContainsString("route('director-attendance.attendance')", $sidebar);
        $this->assertStringContainsString("route('director-attendance.attendance')", $navigation);
        $this->assertStringContainsString("route('director-attendance.overtime')", $navigation);
        $this->assertStringContainsString("route('director-attendance.task')", $navigation);
        $this->assertStringContainsString("request()->routeIs('director-attendance.task*')", $navigation);
        $this->assertStringContainsString('Attendance', $navigation);
        $this->assertStringContainsString('Overtime', $navigation);
        $this->assertStringContainsString('Task', $navigation);
        $this->assertStringNotContainsString('Leave', $navigation);
        $this->assertStringNotContainsString('Business Trip', $navigation);
        $this->assertStringContainsString('Task Monitoring', $directorTaskView);
        $this->assertStringContainsString('$directorTaskCompanyOptions', $directorTaskView);
        $this->assertStringContainsString('id="directorTaskCompanyFilter"', $directorTaskView);
        $this->assertStringContainsString('name="company_filter"', $directorTaskView);
        $this->assertStringContainsString('Semua Company', $directorTaskView);
        $this->assertStringContainsString('Tidak ada company', $directorTaskView);
        $this->assertStringContainsString('id="directorTaskStaffFilter"', $directorTaskView);
        $this->assertStringContainsString('name="staff_filter"', $directorTaskView);
        $this->assertStringContainsString('data-company-id="{{ $staffOption[\'company_id\'] }}"', $directorTaskView);
        $this->assertStringContainsString('Semua Staff', $directorTaskView);
        $this->assertStringContainsString('Tidak ada staff', $directorTaskView);
        $this->assertStringContainsString('$directorTaskStaffOptions', $directorTaskView);
        $this->assertStringContainsString('id="directorTaskTable"', $directorTaskView);
        $this->assertStringContainsString('<th>Staff</th>', $directorTaskView);
        $this->assertStringContainsString('<th>Task</th>', $directorTaskView);
        $this->assertStringContainsString('<th>Company</th>', $directorTaskView);
        $this->assertStringContainsString('<th>Due Date</th>', $directorTaskView);
        $this->assertStringContainsString('<th>Status</th>', $directorTaskView);
        $this->assertStringContainsString('<th>Action</th>', $directorTaskView);
        $this->assertStringContainsString('id="directorTaskDetailModal"', $directorTaskView);
        $this->assertStringContainsString('Task Details', $directorTaskView);
        $this->assertStringContainsString('id="directorTaskDetailTitle"', $directorTaskView);
        $this->assertStringContainsString('Task Name', $directorTaskView);
        $this->assertStringContainsString('Task Description', $directorTaskView);
        $this->assertStringContainsString('Date - Due Date', $directorTaskView);
        $this->assertStringContainsString('Assigned by', $directorTaskView);
        $this->assertStringContainsString('id="directorTaskDetailAttachment"', $directorTaskView);
        $this->assertStringContainsString('btn btn-danger light', $directorTaskView);
        $this->assertStringContainsString("route('director-attendance.task.datatable')", $directorTaskView);
        $this->assertStringContainsString('function initDirectorTaskTable()', $directorTaskView);
        $this->assertStringContainsString('function refreshStaffOptions()', $directorTaskView);
        $this->assertStringContainsString('var allStaffOptions = @js($directorTaskStaffOptions->values()->all());', $directorTaskView);
        $this->assertStringContainsString('staffFilter.innerHTML =', $directorTaskView);
        $this->assertStringContainsString("allStaffOption.textContent = 'Semua Staff';", $directorTaskView);
        $this->assertStringContainsString('visibleStaffOptions.forEach(function (staffOption)', $directorTaskView);
        $this->assertStringContainsString("option.setAttribute('data-company-id'", $directorTaskView);
        $this->assertStringContainsString('refreshSelectPlugin(staffFilter);', $directorTaskView);
        $this->assertStringContainsString('function renderAttachment(value)', $directorTaskView);
        $this->assertStringContainsString('function statusTextClass(value)', $directorTaskView);
        $this->assertStringContainsString('function assignedByText(value)', $directorTaskView);
        $this->assertStringContainsString('DataTable', $directorTaskView);
        $this->assertStringContainsString('requestData.company_id = companyFilter ? companyFilter.value :', $directorTaskView);
        $this->assertStringContainsString('taskTable.ajax.reload();', $directorTaskView);
        $this->assertStringContainsString("'.director-task-detail-button'", $directorTaskView);
        $this->assertStringContainsString("$('#directorTaskTable tbody td.dataTables_empty')", $directorTaskView);
        $this->assertStringContainsString('No task data available.', $directorTaskView);
        $this->assertStringNotContainsString('<form', $directorTaskView);
        $this->assertStringContainsString('use App\Models\Company;', $directorTaskController);
        $this->assertStringContainsString('private const EXCLUDED_TASK_EMPLOYEE_EMAILS', $directorTaskController);
        $this->assertStringContainsString('private function companyOptions(): Collection', $directorTaskController);
        $this->assertStringContainsString('private function activeStaffEmployeeIds(Carbon $date, ?string $companyId = null): Collection', $directorTaskController);
        $this->assertStringContainsString("->whereRaw('LOWER(COALESCE(status, \"\")) = ?', ['active'])", $directorTaskController);
        $this->assertStringContainsString("if (\$companyId !== '')", $directorTaskController);
        $this->assertStringContainsString("->where('current_company_id', \$companyId)", $directorTaskController);
        $this->assertStringContainsString("->whereNull('overtime_id')", $directorTaskController);
        $this->assertStringContainsString("->whereIn('employee_id', \$selectedStaffIds->all())", $directorTaskController);
        $this->assertStringContainsString("'company' => trim((string) (\$projectTask->employee?->deployment?->company?->name ?? '-'))", $directorTaskController);
        $this->assertStringContainsString("'description' => trim((string) (\$projectTask->description ?? ''))", $directorTaskController);
        $this->assertStringContainsString("'blockers' => trim((string) (\$projectTask->blockers ?? ''))", $directorTaskController);
        $this->assertStringContainsString("'attachment_path' => trim((string) (\$projectTask->attachment_path ?? ''))", $directorTaskController);
        $this->assertStringContainsString("'task_category' => \$projectTask->project_id !== null ? 'Project Task' : 'Daily Task'", $directorTaskController);
        $this->assertStringContainsString("'due_date' => \$this->dateRangeLabel(\$projectTask->start_date, \$projectTask->due_date)", $directorTaskController);
        $this->assertStringContainsString("'priority' => \$this->priorityLabel((string) \$projectTask->priority)", $directorTaskController);
        $this->assertStringContainsString("'status' => \$this->statusLabel(", $directorTaskController);
        $this->assertStringContainsString("'status_class' => \$isCompleted ? 'success' : 'warning'", $directorTaskController);
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
