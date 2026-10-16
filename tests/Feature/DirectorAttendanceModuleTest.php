<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAttendance\AttendanceRecapController;
use App\Http\Controllers\DirectorAttendance\DirectorAttendanceController;
use App\Http\Controllers\DirectorAttendance\DirectorAttendanceOvertimeController;
use App\Http\Controllers\DirectorAttendance\DirectorAttendanceTaskController;
use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\EmployeeProfile;
use App\Models\HrMeetingTask;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use ReflectionMethod;
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
        $directorView = File::get(resource_path('views/director_attendance/attendance/index.blade.php'));
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
        $this->assertStringContainsString("\$request->query('card_month', \$fallbackMonth)", $directorOvertimeController);
        $this->assertStringContainsString("\$request->query('pending_month', \$fallbackMonth)", $directorOvertimeController);
        $this->assertStringContainsString("\$request->query('approved_month', \$fallbackMonth)", $directorOvertimeController);
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
        $this->assertStringContainsString('id="recapDailyAttendanceFilter"', $directorView);
        $this->assertStringContainsString("action=\"{{ route('director-attendance.attendance') }}\"", $directorView);
        $this->assertStringContainsString('id="recapAttendanceDateFilter"', $directorView);
        $this->assertStringContainsString('name="date"', $directorView);
        $this->assertStringContainsString('value="{{ $recapAttendanceDateInput }}"', $directorView);
        $this->assertStringContainsString('class="form-control js-recap-attendance-date"', $directorView);
        $this->assertStringContainsString('function initializeRecapAttendanceDatePicker()', $directorView);
        $this->assertStringContainsString('maxDate: moment()', $directorView);
        $this->assertStringContainsString('name="month" value="{{ $recapMonthlySelectedMonth }}"', $directorView);
        $this->assertStringContainsString('name="year" value="{{ $recapMonthlySelectedYear }}"', $directorView);
        $this->assertTrue(View::exists('director_attendance.attendance.index'));
        $this->assertTrue(View::exists('director_attendance.attendance.detail-employees'));
        $this->assertTrue(View::exists('director_attendance.overtime.index'));
        $this->assertTrue(View::exists('director_attendance.overtime.detail'));
        $this->assertTrue(View::exists('director_attendance.task.index'));
        $this->assertStringContainsString('view-director-attendance', $sidebar);
        $this->assertStringContainsString('$isDirectorTaskMenu', $sidebar);
        $this->assertStringContainsString("route('director-attendance.attendance')", $sidebar);
        $this->assertStringContainsString("route('director-attendance.task')", $sidebar);
        $this->assertStringContainsString('Task Management</span>', $sidebar);
        $this->assertStringContainsString("route('director-attendance.attendance')", $navigation);
        $this->assertStringContainsString("route('director-attendance.overtime')", $navigation);
        $this->assertStringNotContainsString("route('director-attendance.task')", $navigation);
        $this->assertStringNotContainsString("request()->routeIs('director-attendance.task*')", $navigation);
        $this->assertStringContainsString('Attendance', $navigation);
        $this->assertStringContainsString('Overtime', $navigation);
        $this->assertStringNotContainsString('Leave', $navigation);
        $this->assertStringNotContainsString('Business Trip', $navigation);
        $this->assertStringContainsString("@section('navbarTitle', 'Task Management')", $directorTaskView);
        $this->assertStringNotContainsString("@include('director_attendance.layout.navbar')", $directorTaskView);
        $this->assertStringContainsString('Task Management', $directorTaskView);
        $this->assertStringContainsString('A comprehensive list of all tasks performed by the team.', $directorTaskView);
        $this->assertStringContainsString('data-bs-target="#filterTaskAndStaff"', $directorTaskView);
        $this->assertStringContainsString('$directorTaskFilterLabel', $directorTaskView);
        $this->assertStringContainsString('@foreach ($directorTaskCategoryCards as $categoryKey => $categoryCard)', $directorTaskView);
        $this->assertStringContainsString('$isSelectedCategory = $selectedDirectorTaskCategory === $categoryKey;', $directorTaskView);
        $this->assertStringContainsString('class="text-decoration-none director-task-category-link"', $directorTaskView);
        $this->assertStringContainsString('data-director-task-category="{{ $isSelectedCategory ? \'\' : $categoryKey }}"', $directorTaskView);
        $this->assertStringContainsString('data-director-task-category-key="{{ $categoryKey }}"', $directorTaskView);
        $this->assertStringContainsString("'bg-' . \$categoryColor", $directorTaskView);
        $this->assertStringContainsString('$categoryCard[\'subtitle\']', $directorTaskView);
        $this->assertStringContainsString('$categoryCard[\'title\']', $directorTaskView);
        $this->assertStringContainsString('$categoryCard[\'icon\']', $directorTaskView);
        $this->assertStringContainsString('id="directorTaskDetailModal"', $directorTaskView);
        $this->assertStringContainsString("window.jQuery('#directorTaskTable tbody').on('click', 'tr.director-task-row'", $directorTaskView);
        $this->assertStringContainsString('renderAssigneeAvatar(row)', $directorTaskView);
        $this->assertStringContainsString('row.assignee_avatar_url', $directorTaskView);
        $this->assertStringNotContainsString('Task Completion Rate', $directorTaskView);
        $this->assertStringNotContainsString('Total Tasks', $directorTaskView);
        $this->assertStringNotContainsString('In Progress', $directorTaskView);
        $this->assertStringContainsString('<th class="mw-50">No</th>', $directorTaskView);
        $this->assertStringContainsString('Task Name', $directorTaskView);
        $this->assertStringContainsString('Project', $directorTaskView);
        $this->assertStringContainsString('Assignee', $directorTaskView);
        $this->assertStringContainsString('Priority', $directorTaskView);
        $this->assertStringContainsString('Due Date', $directorTaskView);
        $this->assertStringContainsString('id="directorTaskTable"', $directorTaskView);
        $this->assertStringContainsString('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js', $directorTaskView);
        $this->assertStringContainsString('DataTable({', $directorTaskView);
        $this->assertStringContainsString("url: \"{{ route('director-attendance.task.datatable') }}\"", $directorTaskView);
        $this->assertStringContainsString('requestData.category = selectedCategory;', $directorTaskView);
        $this->assertStringContainsString("requestData.staff = filterValue('#directorTaskStaffFilter');", $directorTaskView);
        $this->assertStringContainsString("requestData.month = filterValue('#directorTaskMonthFilter');", $directorTaskView);
        $this->assertStringContainsString("requestData.year = filterValue('#directorTaskYearFilter');", $directorTaskView);
        $this->assertStringContainsString('var visibleTaskCategories = [];', $directorTaskView);
        $this->assertStringContainsString('dataSrc: function (json)', $directorTaskView);
        $this->assertStringContainsString('visibleTaskCategories = (json.data || [])', $directorTaskView);
        $this->assertStringContainsString('return row.category || \'\';', $directorTaskView);
        $this->assertStringContainsString('updateCategoryCards(visibleTaskCategories);', $directorTaskView);
        $this->assertStringContainsString("var isAllStaff = filterValue('#directorTaskStaffFilter') === '';", $directorTaskView);
        $this->assertStringContainsString("selectedCategory === '' && !isAllStaff && categories.indexOf(category) !== -1", $directorTaskView);
        $this->assertStringNotContainsString("(selectedCategory === '' && categories.indexOf(category) !== -1)", $directorTaskView);
        $this->assertStringContainsString("link.setAttribute('data-director-task-category', selectedCategory === category ? '' : category);", $directorTaskView);
        $this->assertStringContainsString('selectedCategory = link.getAttribute(\'data-director-task-category\') || \'\';', $directorTaskView);
        $this->assertStringContainsString('taskTable.ajax.reload();', $directorTaskView);
        $this->assertStringContainsString('No task data available.', $directorTaskView);
        $this->assertStringContainsString("'<span class=\"text-black fw-semibold\">' + escapeHtml(data || '-') + '</span>'", $directorTaskView);
        $this->assertStringContainsString("'<h6 class=\"mb-0\">' + escapeHtml(data || '-') + '</h6>'", $directorTaskView);
        $this->assertStringContainsString('id="filterTaskAndStaff"', $directorTaskView);
        $this->assertStringContainsString('id="directorTaskFilterForm"', $directorTaskView);
        $this->assertStringContainsString('id="directorTaskCategoryInput"', $directorTaskView);
        $this->assertStringContainsString('name="staff"', $directorTaskView);
        $this->assertStringContainsString('All Staff', $directorTaskView);
        $this->assertStringContainsString('$directorTaskStaffOptions as $staffOption', $directorTaskView);
        $this->assertStringContainsString('name="month"', $directorTaskView);
        $this->assertStringContainsString('$directorTaskMonthOptions as $monthValue => $monthLabel', $directorTaskView);
        $this->assertStringContainsString('name="year"', $directorTaskView);
        $this->assertStringContainsString('$directorTaskYearOptions as $yearOption', $directorTaskView);
        $this->assertStringContainsString('type="submit"', $directorTaskView);
        $this->assertStringContainsString('btn btn-danger light', $directorTaskView);
        $this->assertStringNotContainsString('$directorTaskCompanyOptions', $directorTaskView);
        $this->assertStringNotContainsString('@forelse ($directorTaskRows as $taskRow)', $directorTaskView);
        $this->assertStringContainsString('use App\Models\Company;', $directorTaskController);
        $this->assertStringContainsString('private const EXCLUDED_TASK_EMPLOYEE_EMAILS', $directorTaskController);
        $this->assertStringContainsString('private function companyOptions(): Collection', $directorTaskController);
        $this->assertStringContainsString('use App\Models\HrMeetingTask;', $directorTaskController);
        $this->assertStringContainsString('public function index(Request $request): View', $directorTaskController);
        $this->assertStringContainsString("'directorTaskCategoryCards' => \$directorTaskCategoryCards", $directorTaskController);
        $this->assertStringContainsString("'selectedDirectorTaskCategory' => \$selectedDirectorTaskCategory", $directorTaskController);
        $this->assertStringContainsString("'directorTaskStaffOptions' => \$directorTaskStaffOptions", $directorTaskController);
        $this->assertStringContainsString("'selectedDirectorTaskStaff' => \$selectedDirectorTaskStaff", $directorTaskController);
        $this->assertStringContainsString("'selectedDirectorTaskMonth' => \$selectedDirectorTaskMonth", $directorTaskController);
        $this->assertStringContainsString("'selectedDirectorTaskYear' => \$selectedDirectorTaskYear", $directorTaskController);
        $this->assertStringContainsString("'directorTaskMonthOptions' => \$this->directorTaskMonthOptions()", $directorTaskController);
        $this->assertStringContainsString("'directorTaskYearOptions' => \$this->directorTaskYearOptions(\$selectedDirectorTaskYear)", $directorTaskController);
        $this->assertStringContainsString("'directorTaskBaseFilters' => \$directorTaskBaseFilters", $directorTaskController);
        $this->assertStringContainsString("'directorTaskFilterLabel' => \$this->directorTaskFilterLabel(", $directorTaskController);
        $this->assertStringContainsString('public function datatable(Request $request): JsonResponse', $directorTaskController);
        $this->assertStringContainsString('$this->hrMeetingTaskRows($selectedCategory, $selectedStaff, $selectedMonth, $selectedYear, $categoryCards)', $directorTaskController);
        $this->assertStringContainsString(')->escapeColumns([])->toJson();', $directorTaskController);
        $this->assertStringContainsString('private function directorTaskCategoryCards(): array', $directorTaskController);
        $this->assertStringContainsString("'administration' => [", $directorTaskController);
        $this->assertStringContainsString("'title' => 'Administration'", $directorTaskController);
        $this->assertStringContainsString("'subtitle' => 'Administrative operational support'", $directorTaskController);
        $this->assertStringContainsString("'event_management_kma' => [", $directorTaskController);
        $this->assertStringContainsString("'title' => 'Event Management (KMA)'", $directorTaskController);
        $this->assertStringContainsString("'subtitle' => 'Event Planning & Operations'", $directorTaskController);
        $this->assertStringContainsString("'property_construction_trah' => [", $directorTaskController);
        $this->assertStringContainsString("'title' => 'Property and Construction (TRAH)'", $directorTaskController);
        $this->assertStringContainsString("'subtitle' => 'Property & Construction Operations'", $directorTaskController);
        $this->assertStringContainsString("'it_social_media_tms' => [", $directorTaskController);
        $this->assertStringContainsString("'title' => 'IT and Social Media (TMS)'", $directorTaskController);
        $this->assertStringContainsString("'subtitle' => 'Digital publication and IT operational support'", $directorTaskController);
        $this->assertStringContainsString("'others' => [", $directorTaskController);
        $this->assertStringContainsString("'title' => 'Others'", $directorTaskController);
        $this->assertStringContainsString("'subtitle' => 'General & Miscellaneous Support'", $directorTaskController);
        $this->assertStringContainsString('private function selectedDirectorTaskCategory(Request $request, array $categoryCards): ?string', $directorTaskController);
        $this->assertStringContainsString('private function hrMeetingTaskStaffOptions(): Collection', $directorTaskController);
        $this->assertStringContainsString('private function selectedDirectorTaskStaff(Request $request, Collection $staffOptions): ?string', $directorTaskController);
        $this->assertStringContainsString('private function selectedDirectorTaskMonth(Request $request): int', $directorTaskController);
        $this->assertStringContainsString('private function selectedDirectorTaskYear(Request $request): int', $directorTaskController);
        $this->assertStringContainsString('private function directorTaskMonthOptions(): array', $directorTaskController);
        $this->assertStringContainsString('private function directorTaskYearOptions(int $selectedYear): array', $directorTaskController);
        $this->assertStringContainsString('private function directorTaskFilterLabel(?string $selectedStaff, Collection $staffOptions, int $month, int $year): string', $directorTaskController);
        $this->assertStringContainsString('private function hrMeetingTaskRows(?string $selectedCategory, ?string $selectedStaff, int $selectedMonth, int $selectedYear, array $categoryCards): Collection', $directorTaskController);
        $this->assertStringContainsString('HrMeetingTask::query()', $directorTaskController);
        $this->assertStringContainsString("->when(\$selectedCategory !== null, fn (\$query) => \$query->where('category', \$selectedCategory))", $directorTaskController);
        $this->assertStringContainsString('if ($selectedStaff !== null)', $directorTaskController);
        $this->assertStringContainsString("->where('employee_id', \$selectedStaff)", $directorTaskController);
        $this->assertStringContainsString('DATE(COALESCE(due_date, start_date, created_at)) BETWEEN ? AND ?', $directorTaskController);
        $this->assertStringContainsString('private function hrMeetingTaskRow(HrMeetingTask $hrMeetingTask, array $categoryCards): array', $directorTaskController);
        $this->assertStringContainsString("'project' => trim((string) (\$projectTask?->project?->name ?? '')) ?: 'Daily Task'", $directorTaskController);
        $this->assertStringContainsString("'description' => trim((string) (\$projectTask?->description ?? ''))", $directorTaskController);
        $this->assertStringContainsString("'blockers' => trim((string) (\$projectTask?->blockers ?? ''))", $directorTaskController);
        $this->assertStringContainsString("\$attachmentPath = trim((string) (\$projectTask?->attachment_path ?? ''));", $directorTaskController);
        $this->assertStringContainsString("'priority' => \$this->priorityLabel((string) (\$projectTask?->priority ?? 'medium'))", $directorTaskController);
        $this->assertStringContainsString("'status' => \$this->hrMeetingTaskStatusLabel((string) (\$projectTask?->status ?? 'pending'))", $directorTaskController);
        $this->assertStringContainsString('private function activeStaffEmployeeIds(Carbon $date, ?string $companyId = null): Collection', $directorTaskController);
        $this->assertStringContainsString("->whereRaw('LOWER(COALESCE(status, \"\")) = ?', ['active'])", $directorTaskController);
        $this->assertStringContainsString("if (\$companyId !== '')", $directorTaskController);
        $this->assertStringContainsString("->where('current_company_id', \$companyId)", $directorTaskController);
        $this->assertStringNotContainsString("->whereNull('overtime_id')", $directorTaskController);
        $this->assertStringContainsString("'view-director-attendance'", $permissionSeeder);
        $this->assertStringContainsString('$directorPermissions = [', $permissionSeeder);
        $this->assertStringContainsString('Position::KEY_CHIEF_OPERATING_OFFICER => $directorPermissions', $permissionSeeder);
        $this->assertStringContainsString('Position::KEY_DIRECTOR => $directorPermissions', $permissionSeeder);
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
        $this->assertStringNotContainsString("where('name', 'Board of Directors')", $permissionSeeder);
        $this->assertStringNotContainsString("where('name', 'Board of Directors')", $legacySeeder);
        $this->assertStringContainsString('Position::KEY_CHIEF_OPERATING_OFFICER => $directorPermissions', $permissionSeeder);
        $this->assertStringContainsString('Position::KEY_DIRECTOR => $directorPermissions', $permissionSeeder);
        $this->assertStringContainsString("'view-director-attendance' => ['section' => 'Director Management', 'label' => 'Director']", $authorizationController);
    }

    public function test_director_task_monitoring_context_labels_follow_pic_task_source(): void
    {
        $controller = app(DirectorAttendanceTaskController::class);
        $method = new ReflectionMethod(DirectorAttendanceTaskController::class, 'taskRow');
        $method->setAccessible(true);

        $overtimeTask = new ProjectTask([
            'id' => 'director-task-overtime',
            'overtime_id' => 'director-overtime-1',
            'title' => 'Rekap Absensi',
            'status' => 'pending',
        ]);
        $overtimeTask->setRelation('overtime', new AttendanceOvertime([
            'id' => 'director-overtime-1',
            'record_number' => 'OVT-2608-0001',
        ]));

        $projectTask = new ProjectTask([
            'id' => 'director-task-project',
            'project_id' => 'director-project-1',
            'title' => 'Venue Report',
            'status' => 'pending',
            'start_date' => Carbon::create(2026, 9, 21, 0, 0, 0, 'Asia/Jakarta'),
            'due_date' => Carbon::create(2026, 9, 23, 0, 0, 0, 'Asia/Jakarta'),
        ]);
        $projectTask->setRelation('project', new Project([
            'id' => 'director-project-1',
            'name' => 'Muktamar PKB',
        ]));

        $dailyTask = new ProjectTask([
            'id' => 'director-task-daily',
            'title' => 'Daily Report',
            'status' => 'pending',
        ]);

        $overtimeRow = $method->invoke($controller, $overtimeTask);
        $projectRow = $method->invoke($controller, $projectTask);
        $dailyRow = $method->invoke($controller, $dailyTask);

        $this->assertSame('OVT-2608-0001', $overtimeRow['task_context']);
        $this->assertSame('overtime', $overtimeRow['task_context_type']);
        $this->assertSame('Overtime Task', $overtimeRow['task_category']);

        $this->assertSame('Task (Muktamar PKB)', $projectRow['task_context']);
        $this->assertSame('project', $projectRow['task_context_type']);
        $this->assertSame('Task (Muktamar PKB)', $projectRow['task_category']);
        $this->assertSame('Senin, 21 - Rabu, 23 Sep 2026', $projectRow['due_date']);

        $this->assertSame('Daily Task', $dailyRow['task_context']);
        $this->assertSame('daily', $dailyRow['task_context_type']);
        $this->assertSame('Daily Task', $dailyRow['task_category']);
    }

    public function test_director_hr_meeting_task_row_uses_meeting_task_category_and_project_task_details(): void
    {
        $controller = app(DirectorAttendanceTaskController::class);
        $categoryMethod = new ReflectionMethod(DirectorAttendanceTaskController::class, 'directorTaskCategoryCards');
        $rowMethod = new ReflectionMethod(DirectorAttendanceTaskController::class, 'hrMeetingTaskRow');
        $categoryMethod->setAccessible(true);
        $rowMethod->setAccessible(true);

        $employee = new Employee(['id' => 'employee-hr-task', 'user_id' => 'user-hr-task']);
        $employee->setRelation('profile', new EmployeeProfile([
            'employee_id' => 'employee-hr-task',
            'name' => 'Rifka Febriza',
        ]));
        $employee->setRelation('user', new User([
            'id' => 'user-hr-task',
            'username' => 'rifka',
            'email' => 'rifka@example.test',
        ]));

        $projectTask = new ProjectTask([
            'id' => 'project-task-hr',
            'employee_id' => 'employee-hr-task',
            'title' => 'Membuat brief konten sosial media',
            'description' => 'Konten mingguan',
            'status' => 'in_progress',
            'priority' => 'high',
            'start_date' => Carbon::create(2026, 10, 9, 0, 0, 0, 'Asia/Jakarta'),
            'due_date' => Carbon::create(2026, 10, 16, 0, 0, 0, 'Asia/Jakarta'),
        ]);
        $projectTask->setRelation('employee', $employee);

        $meetingTask = new HrMeetingTask([
            'id' => 'hr-meeting-task-row',
            'category' => 'it_social_media_tms',
            'project_task_id' => 'project-task-hr',
        ]);
        $meetingTask->setRelation('projectTask', $projectTask);

        $row = $rowMethod->invoke($controller, $meetingTask, $categoryMethod->invoke($controller));

        $this->assertSame('it_social_media_tms', $row['category']);
        $this->assertSame('IT and Social Media (TMS)', $row['category_label']);
        $this->assertSame('Membuat brief konten sosial media', $row['task']);
        $this->assertSame('Daily Task', $row['project']);
        $this->assertSame('Rifka Febriza', $row['assignee']);
        $this->assertSame('R', $row['assignee_initial']);
        $this->assertSame('High', $row['priority']);
        $this->assertSame('danger', $row['priority_class']);
        $this->assertSame('On Progress', $row['status']);
        $this->assertSame('primary', $row['status_class']);
        $this->assertSame('Jum\'at, 16 Oct 2026', $row['due_date']);
        $this->assertSame('Jum\'at, 09 - Jum\'at, 16 Oct 2026', $row['date_range']);
        $this->assertSame('Digital publication and IT operational support', $row['category_subtitle']);
        $this->assertSame('Self', $row['assigned_by']);
        $this->assertNull($row['assignee_avatar_url']);
        $this->assertNull($row['attachment_url']);

        $projectTask->due_date = null;
        $rowWithoutDueDate = $rowMethod->invoke($controller, $meetingTask, $categoryMethod->invoke($controller));

        $this->assertSame('-', $rowWithoutDueDate['due_date']);
    }

    public function test_director_hr_meeting_task_row_exposes_profile_photo_and_task_detail_fields(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('employee-profile-pictures/rifka.jpg', 'photo');

        $controller = app(DirectorAttendanceTaskController::class);
        $categoryMethod = new ReflectionMethod(DirectorAttendanceTaskController::class, 'directorTaskCategoryCards');
        $rowMethod = new ReflectionMethod(DirectorAttendanceTaskController::class, 'hrMeetingTaskRow');

        $employee = new Employee(['id' => 'employee-with-photo', 'user_id' => 'user-with-photo']);
        $employee->setRelation('profile', new EmployeeProfile([
            'employee_id' => 'employee-with-photo',
            'name' => 'Rifka Febriza',
            'profile_picture_path' => 'employee-profile-pictures/rifka.jpg',
        ]));

        $projectTask = new ProjectTask([
            'id' => 'project-task-with-detail',
            'employee_id' => 'employee-with-photo',
            'title' => 'Membuat layout opsi untuk cafe kemang',
            'description' => 'membuat layout opsi lain untuk cafe kemang',
            'blockers' => 'Menunggu feedback klien',
            'attachment_path' => 'https://drive.google.com/file/d/layout',
            'status' => 'completed',
            'priority' => 'medium',
            'start_date' => Carbon::create(2026, 10, 12, 0, 0, 0, 'Asia/Jakarta'),
            'due_date' => Carbon::create(2026, 10, 12, 0, 0, 0, 'Asia/Jakarta'),
        ]);
        $projectTask->setRelation('employee', $employee);
        $projectTask->setRelation('assignedBy', new User(['username' => 'erlin', 'email' => 'erlin@example.test']));

        $meetingTask = new HrMeetingTask([
            'id' => 'hr-meeting-task-with-detail',
            'category' => 'property_construction_trah',
            'project_task_id' => 'project-task-with-detail',
        ]);
        $meetingTask->setRelation('projectTask', $projectTask);

        $row = $rowMethod->invoke($controller, $meetingTask, $categoryMethod->invoke($controller));

        $this->assertSame(asset('storage/employee-profile-pictures/rifka.jpg'), $row['assignee_avatar_url']);
        $this->assertSame('R', $row['assignee_initial']);
        $this->assertSame('erlin', $row['assigned_by']);
        $this->assertSame('Senin, 12 Oct 2026', $row['date_range']);
        $this->assertSame('Menunggu feedback klien', $row['blockers']);
        $this->assertSame('https://drive.google.com/file/d/layout', $row['attachment_url']);
        $this->assertSame('Completed', $row['status']);
        $this->assertSame('success', $row['status_class']);

        $employee->profile->profile_picture_path = 'employee-profile-pictures/missing.jpg';
        $projectTask->attachment_path = 'javascript:alert(1)';
        $rowWithMissingPhoto = $rowMethod->invoke($controller, $meetingTask, $categoryMethod->invoke($controller));

        $this->assertNull($rowWithMissingPhoto['assignee_avatar_url']);
        $this->assertNull($rowWithMissingPhoto['attachment_url']);
        $this->assertSame('javascript:alert(1)', $rowWithMissingPhoto['attachment_path']);
    }

    public function test_director_task_filter_helpers_keep_month_year_and_staff_selection(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 14, 10, 0, 0, 'Asia/Jakarta'));

        $controller = app(DirectorAttendanceTaskController::class);
        $staffMethod = new ReflectionMethod(DirectorAttendanceTaskController::class, 'selectedDirectorTaskStaff');
        $monthMethod = new ReflectionMethod(DirectorAttendanceTaskController::class, 'selectedDirectorTaskMonth');
        $yearMethod = new ReflectionMethod(DirectorAttendanceTaskController::class, 'selectedDirectorTaskYear');
        $labelMethod = new ReflectionMethod(DirectorAttendanceTaskController::class, 'directorTaskFilterLabel');
        $staffMethod->setAccessible(true);
        $monthMethod->setAccessible(true);
        $yearMethod->setAccessible(true);
        $labelMethod->setAccessible(true);

        $staffOptions = collect([
            ['id' => 'employee-syafiq', 'name' => 'Muhammad Syafiq'],
            ['id' => 'employee-rifka', 'name' => 'Rifka Febriza'],
        ]);
        $request = Request::create('/director-attendance/task', 'GET', [
            'staff' => 'employee-syafiq',
            'month' => '9',
            'year' => '2026',
        ]);

        $selectedStaff = $staffMethod->invoke($controller, $request, $staffOptions);
        $selectedMonth = $monthMethod->invoke($controller, $request);
        $selectedYear = $yearMethod->invoke($controller, $request);

        $this->assertSame('employee-syafiq', $selectedStaff);
        $this->assertSame(9, $selectedMonth);
        $this->assertSame(2026, $selectedYear);
        $this->assertSame(
            'Muhammad Syafiq | September 2026',
            $labelMethod->invoke($controller, $selectedStaff, $staffOptions, $selectedMonth, $selectedYear),
        );

        Carbon::setTestNow();
    }
}
