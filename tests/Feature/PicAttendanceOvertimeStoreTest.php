<?php

namespace Tests\Feature;

use App\Http\Controllers\PicAttendance\PicAttendanceOvertimeController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;

class PicAttendanceOvertimeStoreTest extends TestCase
{
    public function test_pic_overtime_add_modal_posts_to_store_route(): void
    {
        $view = File::get(resource_path('views/pic_attendance/overtime/index.blade.php'));

        $this->assertStringContainsString('data-bs-target="#picAddOvertimeModal"', $view);
        $this->assertStringContainsString("route('pic-attendance.overtime.store')", $view);
        $this->assertStringNotContainsString('id="pic-overtime-task"', $view);
        $this->assertLessThan(
            strpos($view, 'name="overtime_date"'),
            strpos($view, 'name="instruction"')
        );
        $this->assertStringContainsString('name="overtime_date"', $view);
        $this->assertStringNotContainsString('id="pic-overtime-start-date"', $view);
        $this->assertStringNotContainsString('id="pic-overtime-end-date"', $view);
        $this->assertStringContainsString('name="start_time"', $view);
        $this->assertStringContainsString('name="end_time"', $view);
        $this->assertStringContainsString('name="ends_next_day" value="0"', $view);
        $this->assertStringContainsString('id="pic-overtime-ends-next-day" name="ends_next_day" value="1"', $view);
        $this->assertStringContainsString('Berakhir hari berikutnya', $view);
        $this->assertStringContainsString('id="pic-overtime-schedule-preview"', $view);
        $this->assertStringContainsString('name="employee_id"', $view);
        $this->assertStringContainsString('name="instruction"', $view);
        $this->assertStringContainsString('$assignableStaffOptions', $view);
        $this->assertStringContainsString('$overtimeCards', $view);
        $this->assertStringContainsString('$overtimeCard[\'current_log\']', $view);
        $this->assertStringNotContainsString('$overtimeCard[\'logs\']', $view);
        $this->assertStringContainsString('$picOvertimeStaffGroups', $view);
        $this->assertStringContainsString('@forelse (($picOvertimeStaffGroups ?? collect()) as $staffGroup)', $view);
        $this->assertStringNotContainsString('Evelyn Hope', $view);
        $this->assertStringNotContainsString('Accordion Header Two', $view);
    }

    public function test_pic_overtime_store_uses_supervisor_staff_assignment_scope(): void
    {
        $controller = File::get(app_path('Http/Controllers/PicAttendance/PicAttendanceOvertimeController.php'));

        $this->assertStringContainsString("validateWithBag('picOvertimeStore'", $controller);
        $this->assertStringNotContainsString("'task' => ['required'", $controller);
        $this->assertStringContainsString('employee_pic_assignments', $controller);
        $this->assertStringContainsString("->where('supervisor_employee_id', \$supervisorEmployeeId)", $controller);
        $this->assertStringContainsString("->pluck('staff_employee_id')", $controller);
        $this->assertStringContainsString("->where('is_active', true)", $controller);
        $this->assertStringContainsString('AttendanceOvertime::query()->create', $controller);
        $this->assertStringContainsString("'assigned_by' => \$authenticatedUser->id", $controller);
        $this->assertStringNotContainsString('$startTime >= $endTime', $controller);
        $this->assertStringNotContainsString('End time harus lebih besar dari start time.', $controller);
        $this->assertStringNotContainsString('ProjectTask::query()->create', $controller);
        $this->assertStringNotContainsString('$projectTaskTitle', $controller);
        $this->assertStringContainsString('createInitialOvertimeLifecycleLogs', $controller);
        $this->assertStringContainsString("'event_key' => 'task_hours_verification'", $controller);
        $this->assertMatchesRegularExpression(
            "/'event_key' => 'task_hours_verification'.*?'status' => 'waiting'/s",
            $controller
        );
        $this->assertStringContainsString('picOvertimeCardsFor', $controller);
        $this->assertStringContainsString('if ($supervisorEmployeeId === null) {', $controller);
        $this->assertStringNotContainsString('if ($supervisorEmployeeId === null || $companyId === null) {', $controller);
        $this->assertStringContainsString("'status' => 'complete'", $controller);
        $this->assertStringContainsString("'status' => 'pending'", $controller);
        $this->assertGreaterThan(
            strpos($controller, "'event_key' => 'task_deliverables_submitted'"),
            strpos($controller, "'event_key' => 'session_ended'")
        );
        $this->assertStringContainsString('currentPicLifecycleLog', $controller);
        $this->assertStringContainsString('isCompletedPicLifecycleStatus', $controller);
        $this->assertStringContainsString('public function detail(string $uid): View', $controller);
        $this->assertStringContainsString("route('pic-attendance.overtime.detail', ['uid' => \$overtime->id])", $controller);
        $this->assertStringContainsString('picOvertimeStaffGroupsFor', $controller);
        $this->assertStringContainsString('activeSupervisedEmployeeIdsFor($user, $companyId', $controller);
        $this->assertStringContainsString('staffGridGroupLabel', $controller);
        $this->assertStringContainsString('paidOutOvertimeMinutes', $controller);
        $this->assertStringContainsString("'overtime_date' => ['required', 'date_format:Y-m-d']", $controller);
        $this->assertStringContainsString("'ends_next_day' => ['required', 'boolean']", $controller);
        $this->assertStringContainsString('overtimeEndDateTime', $controller);
        $this->assertStringContainsString('Durasi overtime tidak boleh lebih dari 24 jam.', $controller);
    }

    public function test_pic_overtime_store_resolves_next_day_end_datetime(): void
    {
        $method = new ReflectionMethod(PicAttendanceOvertimeController::class, 'overtimeEndDateTime');
        $method->setAccessible(true);
        $controller = app(PicAttendanceOvertimeController::class);

        $sameDayEnd = $method->invoke(
            $controller,
            Carbon::parse('2026-07-06', 'Asia/Jakarta')->startOfDay(),
            '22:00:00',
            false
        );
        $overnightEnd = $method->invoke(
            $controller,
            Carbon::parse('2026-07-06', 'Asia/Jakarta')->startOfDay(),
            '02:00:00',
            true
        );

        $this->assertSame('2026-07-06 22:00:00', $sameDayEnd->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-07 02:00:00', $overnightEnd->format('Y-m-d H:i:s'));
    }

    public function test_pic_overtime_card_displays_overnight_date_range_and_badge(): void
    {
        $controller = app(PicAttendanceOvertimeController::class);
        $isOvernightMethod = new ReflectionMethod(PicAttendanceOvertimeController::class, 'isOvernightTimeRange');
        $dateLabelMethod = new ReflectionMethod(PicAttendanceOvertimeController::class, 'overtimeDateLabel');
        $view = File::get(resource_path('views/pic_attendance/overtime/index.blade.php'));

        $this->assertTrue($isOvernightMethod->invoke($controller, '23:00:00', '02:00:00'));
        $this->assertFalse($isOvernightMethod->invoke($controller, '18:00:00', '22:00:00'));
        $this->assertSame('06 Jul 2026 → 07 Jul 2026', $dateLabelMethod->invoke($controller, '2026-07-06', true));
        $this->assertSame('06 Jul 2026', $dateLabelMethod->invoke($controller, '2026-07-06', false));
        $this->assertStringContainsString("\$overtimeCard['is_overnight']", $view);
        $this->assertStringContainsString('>Overnight</span>', $view);
    }

    public function test_pic_overtime_detail_can_submit_session_verification(): void
    {
        $view = File::get(resource_path('views/pic_attendance/overtime/detail.blade.php'));
        $controller = File::get(app_path('Http/Controllers/PicAttendance/PicAttendanceOvertimeController.php'));
        $routes = File::get(base_path('routes/web.php'));

        $this->assertStringContainsString("route('pic-attendance.overtime.verify-session'", $view);
        $this->assertStringContainsString('name="approved_start_time"', $view);
        $this->assertStringContainsString('name="approved_end_time"', $view);
        $this->assertStringContainsString('$overtimeDetail[\'verification_start_time\']', $view);
        $this->assertStringContainsString('$overtimeDetail[\'verification_end_time\']', $view);
        $this->assertStringContainsString('public function verifySession(Request $request, string $uid): RedirectResponse', $controller);
        $this->assertStringContainsString("validateWithBag('picOvertimeVerify'", $controller);
        $this->assertStringContainsString('isTaskDeliverablesSubmitted', $controller);
        $this->assertStringContainsString("'task_hours_verification',", $controller);
        $this->assertStringContainsString("'verified',", $controller);
        $this->assertStringNotContainsString('$approvedStartTime >= $approvedEndTime', $controller);
        $this->assertStringNotContainsString('Approved end harus lebih besar dari approved start.', $controller);
        $this->assertStringContainsString("'payroll_processing',", $controller);
        $this->assertStringContainsString("'pending',", $controller);
        $this->assertStringContainsString("Route::post('/pic-attendance/overtime/detail/{uid}/verify-session'", $routes);
        $this->assertStringContainsString("->name('pic-attendance.overtime.verify-session')", $routes);
    }

    public function test_pic_overtime_detail_can_edit_staff_task_items(): void
    {
        $view = File::get(resource_path('views/pic_attendance/overtime/detail.blade.php'));
        $controller = File::get(app_path('Http/Controllers/PicAttendance/PicAttendanceOvertimeController.php'));
        $routes = File::get(base_path('routes/web.php'));

        $this->assertStringContainsString('$overtimeTaskItems', $view);
        $this->assertStringContainsString('$taskItemPayload', $view);
        $this->assertStringContainsString('class="btn btn-square btn-primary light btn-sm ms-1 pic-overtime-task-edit"', $view);
        $this->assertStringContainsString('id="updateTaskModal"', $view);
        $this->assertStringContainsString('id="picUpdateTaskForm"', $view);
        $this->assertStringContainsString('function submitUpdateTaskForm(event)', $view);
        $this->assertStringContainsString("method: 'PUT'", $view);
        $this->assertStringContainsString('public function updateTask(Request $request, AttendanceOvertime $attendanceOvertime, ProjectTask $projectTask): JsonResponse', $controller);
        $this->assertStringContainsString('canUpdatePicOvertimeTask', $controller);
        $this->assertStringContainsString('buildOvertimeTaskItems', $controller);
        $this->assertStringContainsString("route('pic-attendance.overtime.tasks.update'", $controller);
        $this->assertStringContainsString("Route::put('/pic-attendance/overtime/detail/{attendanceOvertime}/tasks/{projectTask}'", $routes);
        $this->assertStringContainsString("->name('pic-attendance.overtime.tasks.update')", $routes);
    }
}
