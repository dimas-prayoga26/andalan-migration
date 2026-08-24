<?php

namespace Tests\Feature;

use App\Http\Controllers\PicAttendance\PicAttendanceController;
use App\Http\Controllers\PicAttendance\PicAttendanceLeaveController;
use App\Http\Controllers\PicAttendance\PicAttendanceOvertimeController;
use App\Http\Controllers\PicAttendance\PicAttendanceTaskController;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use ReflectionMethod;
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
        $taskRoute = Route::getRoutes()->getByName('pic-attendance.task');
        $taskDatatableRoute = Route::getRoutes()->getByName('pic-attendance.task.datatable');

        $this->assertSame(PicAttendanceController::class.'@index', $attendanceRoute?->getActionName());
        $this->assertSame(PicAttendanceController::class.'@monthlyDatatable', $attendanceDatatableRoute?->getActionName());
        $this->assertSame(PicAttendanceLeaveController::class.'@index', $leaveRoute?->getActionName());
        $this->assertSame(PicAttendanceLeaveController::class.'@pendingDatatable', $leavePendingDatatableRoute?->getActionName());
        $this->assertSame(PicAttendanceLeaveController::class.'@updateSupervisorReview', $leaveSupervisorReviewRoute?->getActionName());
        $this->assertSame(PicAttendanceOvertimeController::class.'@index', $overtimeRoute?->getActionName());
        $this->assertSame(PicAttendanceOvertimeController::class.'@store', $overtimeStoreRoute?->getActionName());
        $this->assertSame(PicAttendanceOvertimeController::class.'@detail', $overtimeDetailRoute?->getActionName());
        $this->assertSame(PicAttendanceTaskController::class.'@index', $taskRoute?->getActionName());
        $this->assertSame(PicAttendanceTaskController::class.'@datatable', $taskDatatableRoute?->getActionName());
        $this->assertSame('pic-attendance', $attendanceRoute?->uri());
        $this->assertSame('pic-attendance/leave', $leaveRoute?->uri());
        $this->assertSame('pic-attendance/leave/detail/{uid}/supervisor-review', $leaveSupervisorReviewRoute?->uri());
        $this->assertSame('pic-attendance/overtime', $overtimeRoute?->uri());
        $this->assertSame('pic-attendance/overtime', $overtimeStoreRoute?->uri());
        $this->assertNotNull($taskRoute);
        $this->assertSame('pic-attendance/task', $taskRoute?->uri());
        $this->assertContains('position.permission:view-pic-attendance', $taskRoute?->gatherMiddleware() ?? []);
        $this->assertSame('pic-attendance/task/datatable', $taskDatatableRoute?->uri());
        $this->assertContains('position.permission:view-pic-attendance', $taskDatatableRoute?->gatherMiddleware() ?? []);
        $this->assertSame('pic-attendance/overtime/detail/{uid}', $overtimeDetailRoute?->uri());
    }

    public function test_pic_attendance_detail_progress_icons_match_their_metrics(): void
    {
        $attendanceDetailView = File::get(resource_path('views/pic_attendance/attendance/detail-employees.blade.php'));

        $this->assertStringContainsString('fa-solid fa-user-check fs-20 text-success', $attendanceDetailView);
        $this->assertStringContainsString('fa-solid fa-clock fs-20 text-danger', $attendanceDetailView);
        $this->assertStringContainsString('fa-solid fa-hourglass-half fs-20 text-secondary', $attendanceDetailView);
        $this->assertStringContainsString('fa-solid fa-business-time fs-20 text-info', $attendanceDetailView);
        $this->assertStringContainsString('<th class="mw-160">Address</th>', $attendanceDetailView);
        $this->assertStringContainsString("{ data: 'location_address', render: function(data) { return escapeHtml(data); } }", $attendanceDetailView);
        $this->assertStringNotContainsString('clip-path="url(#clip3)"', $attendanceDetailView);
        $this->assertStringNotContainsString('clip-path="url(#clip4)"', $attendanceDetailView);
        $this->assertStringNotContainsString('clip-path="url(#clip5)"', $attendanceDetailView);
        $this->assertStringNotContainsString('clip-path="url(#clip8)"', $attendanceDetailView);
    }

    public function test_pic_module_has_its_own_views_navigation_and_permission(): void
    {
        $sidebar = File::get(resource_path('views/layouts/sidebar.blade.php'));
        $navigation = File::get(resource_path('views/pic_attendance/layout/navbar.blade.php'));
        $permissionSeeder = File::get(database_path('seeders/PositionPermissionSeeder.php'));
        $authorizationController = File::get(app_path('Http/Controllers/AuthorizationController.php'));
        $attendanceController = File::get(app_path('Http/Controllers/PicAttendance/PicAttendanceController.php'));
        $leaveController = File::get(app_path('Http/Controllers/PicAttendance/PicAttendanceLeaveController.php'));
        $attendanceView = File::get(resource_path('views/pic_attendance/attendance/index.blade.php'));
        $attendanceDetailView = File::get(resource_path('views/pic_attendance/attendance/detail-employees.blade.php'));
        $leaveView = File::get(resource_path('views/pic_attendance/leave/detail.blade.php'));
        $overtimeController = File::get(app_path('Http/Controllers/PicAttendance/PicAttendanceOvertimeController.php'));
        $overtimeView = File::get(resource_path('views/pic_attendance/overtime/index.blade.php'));
        $taskView = File::get(resource_path('views/pic_attendance/task/index.blade.php'));
        $taskController = File::get(app_path('Http/Controllers/PicAttendance/PicAttendanceTaskController.php'));

        $this->assertTrue(View::exists('pic_attendance.attendance.index'));
        $this->assertTrue(View::exists('pic_attendance.attendance.detail-employees'));
        $this->assertTrue(View::exists('pic_attendance.leave.index'));
        $this->assertTrue(View::exists('pic_attendance.leave.detail'));
        $this->assertTrue(View::exists('pic_attendance.overtime.index'));
        $this->assertTrue(View::exists('pic_attendance.overtime.detail'));
        $this->assertTrue(View::exists('pic_attendance.task.index'));
        $this->assertStringContainsString('view-pic-attendance', $sidebar);
        $this->assertStringContainsString("route('pic-attendance.attendance')", $sidebar);
        $this->assertStringContainsString("route('pic-attendance.attendance')", $navigation);
        $this->assertStringContainsString("route('pic-attendance.leave')", $navigation);
        $this->assertStringContainsString("route('pic-attendance.overtime')", $navigation);
        $this->assertStringContainsString("route('pic-attendance.task')", $navigation);
        $this->assertStringContainsString("request()->routeIs('pic-attendance.task*')", $navigation);
        $this->assertStringNotContainsString('Business Trip', $navigation);
        $this->assertStringContainsString('Overtime', $navigation);
        $this->assertStringContainsString("'view-pic-attendance'", $permissionSeeder);
        $this->assertStringContainsString("'Supervisor' => array_merge", $permissionSeeder);
        $this->assertStringContainsString("'Administrator' => \$allPermissionsWithoutPic", $permissionSeeder);
        $this->assertStringNotContainsString("'System Administrator' =>", $permissionSeeder);
        $this->assertStringContainsString("'view-pic-attendance', 'view-director-attendance'", $permissionSeeder);
        $this->assertStringContainsString("'view-pic-attendance' => ['section' => 'HR Management', 'label' => 'PIC']", $authorizationController);
        $this->assertStringContainsString('employee_pic_assignments', $leaveController);
        $this->assertStringNotContainsString("->where('current_company_id', \$companyId)", $leaveController);
        $this->assertStringContainsString('employee_pic_assignments', $attendanceController);
        $this->assertStringContainsString("'profile:id,employee_id,name,profile_picture_path'", $attendanceController);
        $this->assertStringContainsString("'avatar_url' => \$this->employeeAvatarUrl(\$employee->profile?->profile_picture_path)", $attendanceController);
        $this->assertStringNotContainsString("['id', 'user_id', 'employee_code', 'attachment_path']", $attendanceController);
        $this->assertStringNotContainsString('filled($employee->attachment_path)', $attendanceController);
        $this->assertStringContainsString('$monthlyWorkingDaysCount = $this->recapWorkDaysBetween(', $attendanceController);
        $this->assertStringContainsString('$monthlyExpectedWorkMinutes = $monthlyWorkingDaysCount * 8 * 60;', $attendanceController);
        $this->assertStringContainsString("'working_days' => \$attendedDateKeys->count().' / '.\$monthlyWorkingDaysCount.' days',", $attendanceController);
        $this->assertStringContainsString('recapCompactMinutesLabel($monthlyExpectedWorkMinutes)', $attendanceController);
        $this->assertStringContainsString("private const TASK_HOURS_VERIFICATION = 'task_hours_verification';", $attendanceController);
        $this->assertStringContainsString("->whereNotNull('approved_start_time')", $attendanceController);
        $this->assertStringContainsString("->whereNotNull('approved_end_time')", $attendanceController);
        $this->assertStringContainsString("->where('event_key', self::TASK_HOURS_VERIFICATION)", $attendanceController);
        $this->assertStringContainsString('recapApprovedOvertimeMinutes', $attendanceController);
        $this->assertStringContainsString('$end->addDay();', $attendanceController);
        $this->assertStringNotContainsString("->get(['employee_id', 'overtime_date', 'calculated_hours'])", $attendanceController);
        $this->assertStringNotContainsString("->get(['calculated_hours'])", $attendanceController);
        $this->assertStringNotContainsString("'working_days' => \$attendedDateKeys->count().' / '.\$employeeWorkDays->count().' days'", $attendanceController);
        $this->assertStringContainsString('private function currentCompanyIdFor(User $user): ?string', $attendanceController);
        $this->assertStringContainsString('protected function activeEmployeeIdsFor(Carbon $date, ?string $companyId): Collection', $attendanceController);
        $this->assertStringNotContainsString("->where('current_company_id', \$companyId)", $attendanceController);
        $this->assertStringContainsString('updateSupervisorReview', $leaveController);
        $this->assertStringContainsString("'event_type' => 'supervisor_review'", $leaveController);
        $this->assertStringContainsString("'event_type' => 'hr_verification'", $leaveController);
        $this->assertStringContainsString('->push($this->supervisorEmployeeId)', $leaveController);
        $this->assertStringContainsString('->unique()', $leaveController);
        $this->assertStringContainsString("'leavePendingCards' => \$this->pendingLeaveCardsFor(\$request),", $leaveController);
        $this->assertStringContainsString('private function pendingLeaveCardsFor(Request $request): Collection', $leaveController);
        $this->assertStringNotContainsString('pendingLeaveCardsFor($request, $selectedPeriod)', $leaveController);
        $this->assertStringNotContainsString('Tidak dapat menyetujui pengajuan cuti milik sendiri.', $leaveController);
        $this->assertStringContainsString('$query = $this->applySupervisorApprovedReviewFilter($query);', $leaveController);
        $this->assertStringContainsString('pic-attendance.leave.supervisor-review.update', $leaveView);
        $this->assertStringContainsString('w-100 btn-lg', $leaveView);
        $this->assertStringContainsString('card leave-summary-card mb-5', $leaveView);
        $this->assertStringContainsString('leave-summary-icon--eligibility', $leaveView);
        $this->assertStringContainsString('leave-summary-icon--tracker', $leaveView);
        $this->assertStringContainsString('href="#Eligibility"', $leaveView);
        $this->assertStringContainsString('href="#Tracker"', $leaveView);
        $this->assertStringContainsString('id="Eligibility"', $leaveView);
        $this->assertStringContainsString('id="Tracker"', $leaveView);
        $this->assertStringNotContainsString('href="#Running"', $leaveView);
        $this->assertStringNotContainsString('href="#Cycling"', $leaveView);
        $this->assertStringContainsString('id="recapAttendanceCaptureButton"', $attendanceView);
        $this->assertStringContainsString('id="recapAttendanceCaptureArea"', $attendanceView);
        $this->assertStringContainsString('id="recapAttendanceCaptureTable"', $attendanceView);
        $this->assertStringContainsString('data-capture-tone="{{ $row[\'attachment_badge\'] }}"', $attendanceView);
        $this->assertStringContainsString('function downloadRecapAttendanceImage()', $attendanceView);
        $this->assertStringContainsString('function captureToneFromElement(element)', $attendanceView);
        $this->assertStringContainsString('function captureTonePalette(tone)', $attendanceView);
        $this->assertStringContainsString('drawCaptureBadge(context, lines[0], palette', $attendanceView);
        $this->assertStringContainsString("captureButton.addEventListener('click', downloadRecapAttendanceImage)", $attendanceView);
        $this->assertStringContainsString("asset('assets/vendor/apexcharts/dist/apexcharts.min.js')", $attendanceDetailView);
        $this->assertStringContainsString("typeof window.ApexCharts === 'undefined'", $attendanceDetailView);
        $this->assertStringContainsString("typeof \$.fn.peity !== 'function'", $attendanceDetailView);
        $this->assertStringContainsString('<th class="mw-160">Address</th>', $attendanceDetailView);
        $this->assertStringContainsString("{ data: 'location_address', render: function(data) { return escapeHtml(data); } }", $attendanceDetailView);
        $this->assertStringContainsString('employeeAvatarUrl', $attendanceController);
        $this->assertStringContainsString("asset('assets/default_user.jpg')", $attendanceController);
        $this->assertStringContainsString('Add Overtime', $overtimeView);
        $this->assertStringContainsString('picAddOvertimeModal', $overtimeView);
        $this->assertStringContainsString("route('pic-attendance.overtime.store')", $overtimeView);
        $this->assertStringContainsString('$legacyMonth = $request->query(\'month\');', $overtimeController);
        $this->assertStringContainsString('$picOvertimeCompanyId = is_string($assignedByUserId) && trim($assignedByUserId) !== \'\' ? null : $companyId;', $overtimeController);
        $this->assertStringContainsString('$cardMonth = $this->normalizeMonth($request->query(\'card_month\', $legacyMonth));', $overtimeController);
        $this->assertStringContainsString('$picOvertimeCompanyId,', $overtimeController);
        $this->assertStringContainsString('$request->query(\'pending_month\', $legacyMonth)', $overtimeController);
        $this->assertStringContainsString('$request->query(\'approved_month\', $legacyMonth)', $overtimeController);
        $this->assertStringContainsString('$metricBuilder->summarizeForPeriod(', $overtimeController);
        $this->assertStringContainsString('$cardMonth,', $overtimeController);
        $this->assertStringContainsString('$cardYear', $overtimeController);
        $this->assertStringContainsString("'selectedCardMonth' => \$cardMonth", $overtimeController);
        $this->assertStringContainsString("'selectedPendingMonth' => \$pendingTableData['selectedMonth']", $overtimeController);
        $this->assertStringContainsString("'selectedApprovedMonth' => \$approvedTableData['selectedMonth']", $overtimeController);
        $this->assertStringContainsString('private function yearOptionsForFilters(mixed ...$selectedYearsAndOptions): array', $overtimeController);
        $this->assertStringNotContainsString('name="month"', $overtimeView);
        $this->assertStringNotContainsString('name="year"', $overtimeView);
        $this->assertStringContainsString('name="card_month"', $overtimeView);
        $this->assertStringContainsString('name="pending_month"', $overtimeView);
        $this->assertStringContainsString('name="approved_month"', $overtimeView);
        $this->assertStringContainsString('pic-overtime-card-filter', $overtimeView);
        $this->assertStringContainsString('Task Monitoring', $taskView);
        $this->assertStringContainsString('id="picTaskStaffFilter"', $taskView);
        $this->assertStringContainsString('name="staff_filter"', $taskView);
        $this->assertStringNotContainsString('Semua Staff', $taskView);
        $this->assertStringContainsString('Pilih Staff', $taskView);
        $this->assertStringContainsString('<option value="" selected disabled>Pilih Staff</option>', $taskView);
        $this->assertStringContainsString('Tidak ada staff', $taskView);
        $this->assertStringContainsString('$picTaskStaffOptions', $taskView);
        $this->assertStringContainsString('id="picTaskTable"', $taskView);
        $this->assertStringContainsString('<th>Staff</th>', $taskView);
        $this->assertStringContainsString('<th>Task</th>', $taskView);
        $this->assertStringContainsString('<th>Due Date</th>', $taskView);
        $this->assertStringNotContainsString('<th>Priority</th>', $taskView);
        $this->assertStringContainsString('<th>Status</th>', $taskView);
        $this->assertStringContainsString('<th>Action</th>', $taskView);
        $this->assertStringContainsString('picTaskDetailModal', $taskView);
        $this->assertStringContainsString('Task Details', $taskView);
        $this->assertStringContainsString('Task Name', $taskView);
        $this->assertStringContainsString('Task Description', $taskView);
        $this->assertStringContainsString('Date - Due Date', $taskView);
        $this->assertStringContainsString('Assigned by', $taskView);
        $this->assertStringContainsString('btn btn-danger light', $taskView);
        $this->assertStringContainsString('pic-task-detail-button', $taskView);
        $this->assertStringContainsString('renderAttachment', $taskView);
        $this->assertStringContainsString('statusTextClass', $taskView);
        $this->assertStringContainsString('assignedByText', $taskView);
        $this->assertStringContainsString('row.description', $taskView);
        $this->assertStringContainsString('row.status_class', $taskView);
        $this->assertStringContainsString('row.status', $taskView);
        $this->assertStringContainsString("route('pic-attendance.task.datatable')", $taskView);
        $this->assertStringContainsString('DataTable', $taskView);
        $this->assertStringContainsString('taskTable.ajax.reload();', $taskView);
        $this->assertStringContainsString("$('#picTaskTable tbody td.dataTables_empty')", $taskView);
        $this->assertStringContainsString("addClass('text-center py-4 text-muted')", $taskView);
        $this->assertStringContainsString('EmployeePicAssignment::query()', $taskController);
        $this->assertStringContainsString("->where('supervisor_employee_id', \$supervisorEmployeeId)", $taskController);
        $this->assertStringContainsString("->whereIn('employee_id', \$visibleEmployeeIds->all())", $taskController);
        $this->assertStringContainsString('$visibleEmployeeIds = collect([$selectedStaffId]);', $taskController);
        $this->assertStringNotContainsString('return $staffEmployeeIds->first();', $taskController);
        $this->assertStringNotContainsString("->whereNull('overtime_id')", $taskController);
        $this->assertStringContainsString("'due_date' => \$this->dateRangeLabel(\$projectTask->start_date, \$projectTask->due_date)", $taskController);
        $this->assertStringContainsString("'description' => trim((string) (\$projectTask->description ?? ''))", $taskController);
        $this->assertStringContainsString("'blockers' => trim((string) (\$projectTask->blockers ?? ''))", $taskController);
        $this->assertStringContainsString("'attachment_path' => trim((string) (\$projectTask->attachment_path ?? ''))", $taskController);
        $this->assertStringContainsString("'task_context' => \$taskContext", $taskController);
        $this->assertStringContainsString("'task_context_type' => \$isOvertimeTask ? 'overtime' : (\$projectTask->project_id !== null ? 'project' : 'daily')", $taskController);
        $this->assertStringContainsString('renderTaskContext', $taskView);
        $this->assertStringContainsString('renderTaskTitle', $taskView);
        $this->assertStringContainsString('badge badge-info light ms-1 align-middle">Overtime</span>', $taskView);
        $this->assertStringContainsString("row.task_context_type === 'overtime' ? '' : renderTaskContext(row)", $taskView);
        $this->assertStringContainsString("'priority' => \$this->priorityLabel((string) \$projectTask->priority)", $taskController);
        $this->assertStringContainsString("'status' => \$this->statusLabel(", $taskController);
        $this->assertStringContainsString("'status_class' => \$isCompleted ? 'success' : 'warning'", $taskController);
        $this->assertStringNotContainsString('Staff Task List', $taskView);
        $this->assertStringContainsString('No task data available.', $taskView);
        $this->assertStringNotContainsString('<form', $taskView);
    }

    public function test_pic_task_monitoring_context_labels_follow_task_source(): void
    {
        $controller = app(PicAttendanceTaskController::class);
        $method = new ReflectionMethod(PicAttendanceTaskController::class, 'taskRow');
        $method->setAccessible(true);

        $overtimeTask = new ProjectTask([
            'id' => 'task-overtime',
            'overtime_id' => 'overtime-1',
            'title' => 'Rekap Absensi',
            'status' => 'pending',
        ]);
        $projectTask = new ProjectTask([
            'id' => 'task-project',
            'project_id' => 'project-1',
            'title' => 'Venue Report',
            'status' => 'pending',
        ]);
        $projectTask->setRelation('project', new Project([
            'id' => 'project-1',
            'name' => 'Muktamar PKB',
        ]));

        $dailyTask = new ProjectTask([
            'id' => 'task-daily',
            'title' => 'Daily Report',
            'status' => 'pending',
        ]);

        $overtimeRow = $method->invoke($controller, $overtimeTask);
        $projectRow = $method->invoke($controller, $projectTask);
        $dailyRow = $method->invoke($controller, $dailyTask);

        $this->assertSame('Overtime', $overtimeRow['task_context']);
        $this->assertSame('overtime', $overtimeRow['task_context_type']);
        $this->assertSame('Overtime Task', $overtimeRow['task_category']);

        $this->assertSame('Task (Muktamar PKB)', $projectRow['task_context']);
        $this->assertSame('project', $projectRow['task_context_type']);
        $this->assertSame('Task (Muktamar PKB)', $projectRow['task_category']);

        $this->assertSame('Daily Task', $dailyRow['task_context']);
        $this->assertSame('daily', $dailyRow['task_context_type']);
        $this->assertSame('Daily Task', $dailyRow['task_category']);
    }
}
