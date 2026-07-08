<?php

namespace Tests\Feature;

use App\Http\Controllers\StaffAttendance\AttendanceOvertimeController;
use App\Models\AttendanceOvertime;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\OvertimeLifecycleLog;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;

class ProjectOvertimeRelationTest extends TestCase
{
    public function test_overtime_clock_in_window_matches_the_pic_schedule(): void
    {
        $overtime = new AttendanceOvertime([
            'overtime_date' => '2026-06-15',
            'planned_start_time' => '08:00:00',
            'planned_end_time' => '10:00:00',
        ]);
        $method = new ReflectionMethod(AttendanceOvertimeController::class, 'resolveOvertimeClockInWindow');
        $method->setAccessible(true);
        $controller = app(AttendanceOvertimeController::class);

        $beforeWindow = $method->invoke($controller, $overtime, Carbon::parse('2026-06-15 07:29:00', 'Asia/Jakarta'));
        $windowStart = $method->invoke($controller, $overtime, Carbon::parse('2026-06-15 07:30:00', 'Asia/Jakarta'));
        $scheduledStart = $method->invoke($controller, $overtime, Carbon::parse('2026-06-15 08:00:00', 'Asia/Jakarta'));
        $windowEnd = $method->invoke($controller, $overtime, Carbon::parse('2026-06-15 08:30:00', 'Asia/Jakarta'));
        $afterWindow = $method->invoke($controller, $overtime, Carbon::parse('2026-06-15 08:31:00', 'Asia/Jakarta'));

        $this->assertFalse($beforeWindow['is_allowed']);
        $this->assertTrue($windowStart['is_allowed']);
        $this->assertTrue($scheduledStart['is_allowed']);
        $this->assertTrue($windowEnd['is_allowed']);
        $this->assertFalse($afterWindow['is_allowed']);
        $this->assertSame('before_window', $beforeWindow['state']);
        $this->assertSame('allowed', $windowStart['state']);
        $this->assertSame('allowed', $scheduledStart['state']);
        $this->assertSame('allowed', $windowEnd['state']);
        $this->assertSame('after_window', $afterWindow['state']);
        $this->assertSame('Waktu absen lembur sudah melewati batas yang ditetapkan PIC. Silakan hubungi PIC untuk mengubah jadwal lemburnya.', $afterWindow['message']);
    }

    public function test_overtime_clock_in_window_supports_overnight_schedule(): void
    {
        $overtime = new AttendanceOvertime([
            'overtime_date' => '2026-06-30',
            'planned_start_time' => '23:00:00',
            'planned_end_time' => '01:00:00',
        ]);
        $method = new ReflectionMethod(AttendanceOvertimeController::class, 'resolveOvertimeClockInWindow');
        $method->setAccessible(true);
        $controller = app(AttendanceOvertimeController::class);

        $beforeWindow = $method->invoke($controller, $overtime, Carbon::parse('2026-06-30 22:29:00', 'Asia/Jakarta'));
        $windowStart = $method->invoke($controller, $overtime, Carbon::parse('2026-06-30 22:30:00', 'Asia/Jakarta'));
        $scheduledStart = $method->invoke($controller, $overtime, Carbon::parse('2026-06-30 23:00:00', 'Asia/Jakarta'));
        $windowEnd = $method->invoke($controller, $overtime, Carbon::parse('2026-06-30 23:30:00', 'Asia/Jakarta'));
        $afterWindow = $method->invoke($controller, $overtime, Carbon::parse('2026-06-30 23:31:00', 'Asia/Jakarta'));

        $this->assertFalse($beforeWindow['is_allowed']);
        $this->assertTrue($windowStart['is_allowed']);
        $this->assertTrue($scheduledStart['is_allowed']);
        $this->assertTrue($windowEnd['is_allowed']);
        $this->assertFalse($afterWindow['is_allowed']);
    }

    public function test_overtime_clock_out_window_uses_thirty_minutes_before_and_after_scheduled_end(): void
    {
        $overtime = new AttendanceOvertime([
            'id' => 'overtime-window-test',
            'employee_id' => 'employee-window-test',
            'overtime_date' => '2026-06-15',
            'planned_start_time' => '08:00:00',
            'planned_end_time' => '10:00:00',
        ]);
        $method = new ReflectionMethod(AttendanceOvertimeController::class, 'resolveOvertimeClockOutWindow');
        $method->setAccessible(true);
        $controller = app(AttendanceOvertimeController::class);

        $beforeWindow = $method->invoke($controller, $overtime, Carbon::parse('2026-06-15 09:29:00', 'Asia/Jakarta'));
        $windowStart = $method->invoke($controller, $overtime, Carbon::parse('2026-06-15 09:30:00', 'Asia/Jakarta'));
        $scheduledEnd = $method->invoke($controller, $overtime, Carbon::parse('2026-06-15 10:00:00', 'Asia/Jakarta'));
        $windowEnd = $method->invoke($controller, $overtime, Carbon::parse('2026-06-15 10:30:00', 'Asia/Jakarta'));
        $afterWindow = $method->invoke($controller, $overtime, Carbon::parse('2026-06-15 10:31:00', 'Asia/Jakarta'));

        $this->assertFalse($beforeWindow['is_allowed']);
        $this->assertSame('Clock Out Lembur Belum Tersedia', $beforeWindow['title']);
        $this->assertTrue($windowStart['is_allowed']);
        $this->assertTrue($scheduledEnd['is_allowed']);
        $this->assertTrue($windowEnd['is_allowed']);
        $this->assertFalse($afterWindow['is_allowed']);
        $this->assertSame('Batas Clock Out Lembur Sudah Lewat', $afterWindow['title']);
    }

    public function test_overtime_duration_label_uses_hours_and_minutes_for_midnight_ranges(): void
    {
        $method = new ReflectionMethod(AttendanceOvertimeController::class, 'calculateDurationLabel');
        $method->setAccessible(true);
        $controller = app(AttendanceOvertimeController::class);

        $this->assertSame('01 Jam 30 Menit', $method->invoke($controller, '00:00:00', '01:30:00'));
        $this->assertSame('02 Jam 00 Menit', $method->invoke($controller, '23:00:00', '01:00:00'));
    }

    public function test_overtime_task_can_be_created_after_clock_in_even_after_clock_out(): void
    {
        $method = new ReflectionMethod(AttendanceOvertimeController::class, 'canCreateOvertimeTask');
        $controller = app(AttendanceOvertimeController::class);

        $beforeClockIn = new AttendanceOvertime(['status' => 'assigned']);
        $duringSession = new AttendanceOvertime([
            'status' => 'in_progress',
            'actual_start_time' => '08:00:00',
        ]);
        $afterClockOut = new AttendanceOvertime([
            'status' => 'completed',
            'actual_start_time' => '08:00:00',
            'actual_end_time' => '10:00:00',
        ]);

        $this->assertFalse($method->invoke($controller, $beforeClockIn));
        $this->assertTrue($method->invoke($controller, $duringSession));
        $this->assertTrue($method->invoke($controller, $afterClockOut));
    }

    public function test_project_task_and_overtime_relationships_are_available(): void
    {
        $project = new Project;
        $projectMember = new ProjectMember;
        $projectTask = new ProjectTask;
        $department = new Department;
        $employee = new Employee;
        $employeeDeployment = new EmployeeDeployment;
        $overtime = new AttendanceOvertime;
        $overtimeLifecycleLog = new OvertimeLifecycleLog;
        $user = new User;

        $this->assertFalse($project->incrementing);
        $this->assertFalse($projectMember->incrementing);
        $this->assertFalse($projectTask->incrementing);
        $this->assertFalse($department->incrementing);
        $this->assertFalse($overtimeLifecycleLog->incrementing);
        $this->assertSame('string', $project->getKeyType());
        $this->assertSame('string', $projectMember->getKeyType());
        $this->assertSame('string', $projectTask->getKeyType());
        $this->assertSame('string', $department->getKeyType());
        $this->assertSame('string', $employeeDeployment->getKeyType());
        $this->assertSame('string', $overtimeLifecycleLog->getKeyType());

        $this->assertInstanceOf(BelongsTo::class, $project->company());
        $this->assertInstanceOf(BelongsTo::class, $project->creator());
        $this->assertInstanceOf(HasMany::class, $project->memberships());
        $this->assertInstanceOf(BelongsToMany::class, $project->members());
        $this->assertInstanceOf(HasMany::class, $project->tasks());
        $this->assertInstanceOf(BelongsTo::class, $projectMember->project());
        $this->assertInstanceOf(BelongsTo::class, $projectMember->employee());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->project());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->employee());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->assignedBy());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->overtime());
        $this->assertInstanceOf(BelongsTo::class, $employeeDeployment->department());
        $this->assertInstanceOf(BelongsToMany::class, $employee->projects());
        $this->assertInstanceOf(HasMany::class, $employee->projectMemberships());
        $this->assertInstanceOf(HasMany::class, $employee->projectTasks());
        $this->assertInstanceOf(HasMany::class, $employee->overtimes());
        $this->assertInstanceOf(HasMany::class, $overtime->projectTasks());
        $this->assertInstanceOf(HasMany::class, $overtime->lifecycleLogs());
        $this->assertInstanceOf(BelongsTo::class, $overtimeLifecycleLog->overtime());
        $this->assertInstanceOf(BelongsTo::class, $overtimeLifecycleLog->actor());
        $this->assertInstanceOf(HasMany::class, $user->createdProjects());
    }

    public function test_project_overtime_migrations_define_required_columns(): void
    {
        $projectMigration = File::get(database_path('migrations/2026_06_11_042441_create_projects_table.php'));
        $projectMemberMigration = File::get(database_path('migrations/2026_06_11_042442_create_project_members_table.php'));
        $projectTaskMigration = File::get(database_path('migrations/2026_06_11_042442_create_project_tasks_table.php'));
        $projectTaskAssignedByMigration = File::get(database_path('migrations/2026_06_28_131803_add_assigned_by_to_project_tasks_table.php'));
        $overtimeMigration = File::get(database_path('migrations/2026_05_05_014427_create_overtimes_table.php'));
        $overtimeLifecycleLogMigration = File::get(database_path('migrations/2026_06_12_015139_create_overtime_lifecycle_logs_table.php'));
        $overtimeRecordNumberMigration = File::get(database_path('migrations/2026_06_12_022759_add_record_number_to_overtimes_table.php'));

        foreach ([
            "Schema::create('projects'",
            "\$table->uuid('id')->primary();",
            "\$table->foreignUuid('company_id')->constrained('companies', 'id')->cascadeOnDelete();",
            "\$table->string('code');",
            "\$table->string('name');",
            "\$table->string('status')->default('active');",
            "\$table->foreignUuid('created_by')->nullable()->constrained('users', 'id')->nullOnDelete();",
            "\$table->unique(['company_id', 'code'], 'projects_company_code_unique');",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $projectMigration);
        }

        foreach ([
            "Schema::create('project_members'",
            "\$table->foreignUuid('project_id')->constrained('projects', 'id')->cascadeOnDelete();",
            "\$table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();",
            "\$table->unique(['project_id', 'employee_id'], 'project_members_project_employee_unique');",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $projectMemberMigration);
        }
        $this->assertStringNotContainsString("\$table->string('role')", $projectMemberMigration);

        foreach ([
            "Schema::create('project_tasks'",
            "\$table->foreignUuid('project_id')->nullable()->constrained('projects', 'id')->nullOnDelete();",
            "\$table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();",
            "\$table->foreignUuid('overtime_id')->nullable()->constrained('overtimes', 'id')->nullOnDelete();",
            "\$table->string('title');",
            "\$table->text('blockers')->nullable();",
            "\$table->string('attachment_path')->nullable();",
            "\$table->string('status')->default('pending');",
            "\$table->string('priority')->default('medium');",
            "\$table->index(['overtime_id', 'status'], 'project_tasks_overtime_status_index');",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $projectTaskMigration);
        }
        $this->assertStringNotContainsString("\$table->foreignUuid('department_id')", $projectTaskMigration);
        $this->assertStringNotContainsString('project_tasks_department_status_index', $projectTaskMigration);
        $this->assertStringNotContainsString("\$table->foreignUuid('created_by')", $projectTaskMigration);
        $this->assertStringContainsString("\$table->foreignUuid('assigned_by')->nullable()->after('employee_id')->constrained('users', 'id')->nullOnDelete();", $projectTaskAssignedByMigration);
        $this->assertStringContainsString("\$table->index(['assigned_by', 'status'], 'project_tasks_assigned_by_status_index');", $projectTaskAssignedByMigration);
        $this->assertStringContainsString("\$table->dropForeign(['assigned_by']);", $projectTaskAssignedByMigration);
        $this->assertStringContainsString("\$table->dropColumn('assigned_by');", $projectTaskAssignedByMigration);

        $this->assertFileDoesNotExist(database_path('migrations/2026_06_11_042442_create_project_sections_table.php'));
        $this->assertFileDoesNotExist(database_path('migrations/2026_06_12_063842_replace_project_task_sections_with_departments.php'));

        $this->assertStringContainsString(
            "\$table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled'])->default('assigned');",
            $overtimeMigration
        );
        $this->assertFileDoesNotExist(database_path('migrations/2026_06_11_042443_add_task_id_to_overtimes_table.php'));
        $this->assertFileDoesNotExist(database_path('migrations/2026_06_11_064143_update_overtime_status_flow_values.php'));
        $this->assertFileDoesNotExist(database_path('migrations/2026_06_11_065337_move_overtime_task_relation_to_project_tasks_table.php'));

        foreach ([
            "Schema::create('overtime_lifecycle_logs'",
            "\$table->uuid('id')->primary();",
            "\$table->foreignUuid('overtime_id')->constrained('overtimes', 'id')->cascadeOnDelete();",
            "\$table->string('phase', 100);",
            "\$table->string('event_key', 100);",
            "\$table->unsignedInteger('step_order');",
            "\$table->string('title');",
            "\$table->string('status', 50)->default('waiting');",
            "\$table->foreignUuid('actor_id')->nullable()->constrained('users', 'id')->nullOnDelete();",
            "\$table->timestamp('happened_at')->nullable();",
            "\$table->json('metadata')->nullable();",
            'overtime_lifecycle_logs_overtime_event_unique',
            'overtime_lifecycle_logs_overtime_step_unique',
            'overtime_lifecycle_logs_overtime_status_index',
            'overtime_lifecycle_logs_overtime_happened_index',
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $overtimeLifecycleLogMigration);
        }

        foreach ([
            "Schema::table('overtimes'",
            "\$table->string('record_number', 50)->nullable()->unique()->after('id');",
            "\$table->dropUnique('overtimes_record_number_unique');",
            "\$table->dropColumn('record_number');",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $overtimeRecordNumberMigration);
        }
    }

    public function test_overtime_status_flow_uses_assignment_lifecycle_values(): void
    {
        $overtimeController = File::get(app_path('Http/Controllers/StaffAttendance/AttendanceOvertimeController.php'));
        $overtimeModel = File::get(app_path('Models/AttendanceOvertime.php'));
        $overtimeIndexView = File::get(resource_path('views/staff_attendance/overtimes/index.blade.php'));
        $overtimeDetailView = File::get(resource_path('views/staff_attendance/overtimes/detail.blade.php'));
        $profileNavbarView = File::get(resource_path('views/staff_attendance/layouts/profile-navbar.blade.php'));

        foreach ([
            "private const OVERTIME_STATUS_ASSIGNED = 'assigned';",
            "private const OVERTIME_STATUS_IN_PROGRESS = 'in_progress';",
            "private const OVERTIME_STATUS_COMPLETED = 'completed';",
            "private const OVERTIME_STATUS_CANCELLED = 'cancelled';",
            'private const OVERTIME_CLOCK_TOLERANCE_MINUTES = 30;',
            "'can_create_task' => \$this->canCreateOvertimeTask(\$overtime)",
            "'status' => ['nullable', 'in:assigned,in_progress,completed,cancelled']",
            'private function resolveOvertimeStatus',
            'private function resolveOvertimeClockInWindow',
            'private function resolveOvertimeClockOutWindow',
            'private function formatOvertimeClockInWindowLabel',
            'private function resolveOvertimeClockOutReadiness',
            'private function buildCurrentOvertimeTaskQuery',
            'Absen Kehadiran Belum Dilakukan',
            'Absen Lembur Sudah Dilakukan',
            'Absen Lembur Belum Tersedia',
            'Batas Absen Lembur Sudah Lewat',
            'Clock Out Lembur Belum Tersedia',
            'Batas Clock Out Lembur Sudah Lewat',
            'Task Lembur Belum Disubmit',
            'Task Lembur Belum Completed',
            'Anda belum melakukan absen kehadiran hari ini. Silakan absen masuk terlebih dahulu sebelum absen lembur.',
            'Sesi lembur sudah berjalan. Silakan selesaikan task lembur, lalu lakukan clock out setelah task sudah dikerjakan.',
            'Waktu absen lembur sudah melewati batas yang ditetapkan PIC. Silakan hubungi PIC untuk mengubah jadwal lemburnya.',
            'Silakan submit minimal satu task yang dikerjakan selama lembur, lalu ubah status task tersebut menjadi Completed sebelum mengakhiri sesi.',
            'Harap submit task lembur yang dikerjakan setelah clock-in, lalu pastikan statusnya Completed sebelum mengakhiri sesi.',
            'private function createInitialOvertimeLifecycleLogs',
            'private function syncOvertimeLifecycleLogs',
            'private function buildOvertimeDetailSummary',
            "'log_status' => \$isPicVerified ? 'Completed'",
            "'approved_time_range' => \$approvedTimeRange",
            "'log_time_range' => \$logTimeRange",
            "'approved_duration' => \$approvedDuration",
            "'log_duration' => \$logDuration",
            "'is_pic_verified' => \$isPicVerified",
            'private function resolveOvertimeDirectorApprover',
            'private function overtimeLifecycleLog',
            'private function formatOvertimeDetailLogDateTime',
            'private function formatOvertimeModalDateTimeLabel',
            'private function formatDurationSummaryLabel',
            'private function calculateDurationClockLabel',
            'private function buildOvertimeLifecycleTracker',
            'private function buildOvertimeTaskItems',
            'private function buildOvertimeTaskQuery',
            'private function completedOvertimeTaskSubmittedAt',
            'private function overtimeTaskItemValue',
            'public function storeTask',
            'Task lembur hanya dapat ditambahkan setelah Overtime Clock In.',
            'public function updateTask',
            'public function destroyTask',
            'private function canUpdateOvertimeTask',
            'private function buildTaskProjectOptions',
            'private function employeeIsProjectMember',
            "'task_category' => ['nullable', 'in:daily,project']",
            "'attachment_path' => ['nullable', 'string', 'max:255']",
            "'blockers' => ['nullable', 'string', 'max:5000']",
            'private function buildOvertimeIndexList',
            'private function buildOvertimeIndexCard',
            'private function buildOvertimeIndexSummary',
            '$isPendingSupervisorOvertimeApproval = function (AttendanceOvertime $overtime): bool',
            '$isVerifiedSupervisorOvertime = function (AttendanceOvertime $overtime): bool',
            "strtolower(trim((string) \$verificationLog->status)) === 'verified'",
            '->filter($isVerifiedSupervisorOvertime)',
            "->where('event_key', 'task_hours_verification')",
            "->whereRaw('LOWER(status) = ?', ['verified'])",
            'private function approvedOrActualDurationMinutes',
            '->sum(fn (AttendanceOvertime $overtime): int => $this->approvedOrActualDurationMinutes($overtime))',
            'private function durationMinutesFromTimeValues',
            'private function formatOvertimeSummaryHours',
            'private function normalizeOvertimeIndexStatusFilter',
            "\$normalizedStatus === '' || \$normalizedStatus === 'all'",
            'private function overtimeLifecycleProgressPercent',
            'private function overtimeFooterStatusLabel',
            "route('attendance.overtimes.detail', \$overtime)",
            "'record_number' => (string) (\$overtime->record_number ?? ''),",
            "return '#'.\$recordNumber;",
            "->whereIn('status', [",
            "->orWhereNull('overtime_id')",
            "'overtime_id' => (string) \$attendanceOvertime->id,",
            "'project_id' => \$projectId",
            '$clockOutReadiness = $this->resolveOvertimeClockOutReadiness($attendanceOvertime, $actualStartTime, false);',
            "'message' => \$clockOutReadiness['message'],",
            'DB::transaction(function () use ($attendanceOvertime, $projectTask, $updatePayload, $authenticatedUser): void',
            'DB::transaction(function () use ($attendanceOvertime, $projectId, $status, $validated, $authenticatedUser): void',
            '$completedTaskSubmittedAt = $actualStartAt instanceof Carbon',
            "\$completedTaskSubmittedAt instanceof Carbon ? 'complete' : 'pending'",
            "\$completedTaskSubmittedAt instanceof Carbon ? 'pending' : 'waiting'",
            "'task_hours_verification',\n            'pending',\n            null,\n            null",
            "->where('completed_at', '>=', \$actualStartAt->toDateTimeString())",
            "->orderBy('completed_at')",
            "'completed_after_clock_in_count' => \$completedAfterClockInCount,",
            "'calculated_hours' => '-',",
            "'estimated_earnings' => '-',",
            "'calculated_hours' => null,",
            "'overtimeSummary' => \$this->buildOvertimeIndexSummary",
            "'pending_spv_approval_hours_label' => \$this->formatOvertimeSummaryHours",
            "'pending_spv_approval_hours_progress' => min",
            "'estimated_extra_earnings_label' => 'Rp 0'",
            "'disputed_hours_label' => '0 Hours'",
            "'delete_url' => route('attendance.overtimes.tasks.destroy'",
            'Task berhasil dihapus.',
            'ProjectMember::query()',
            "'board_of_rector'",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $overtimeController);
        }
        $this->assertStringNotContainsString("->with('employee.deployment.department:id,name')", $overtimeController);
        $this->assertStringNotContainsString('Waiting for Payroll Calculation', $overtimeController);
        $this->assertStringNotContainsString('private function calculateDurationHours', $overtimeController);

        foreach ([
            'protected static function generateRecordNumber(mixed $overtimeDate): string',
            "\$prefix = 'OVT-'.\$date->format('ym').'-';",
            "->where('record_number', 'like', \$prefix.'%')",
            '$overtime->record_number = static::generateRecordNumber($overtime->overtime_date);',
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $overtimeModel);
        }

        $this->assertStringContainsString('Pending SPV Approval', $overtimeIndexView);
        $this->assertStringNotContainsString('Assigned Hours', $overtimeIndexView);
        $this->assertStringContainsString('Completed & Locked', $overtimeIndexView);
        $this->assertStringContainsString('$overtimeSummaryCards = [', $overtimeIndexView);
        $this->assertStringContainsString('@foreach ($overtimeSummaryCards as $summaryCard)', $overtimeIndexView);
        $this->assertStringContainsString("'value' => \$overtimeSummary['total_logged_hours_label'] ?? '0 Hours'", $overtimeIndexView);
        $this->assertStringContainsString("'value' => \$overtimeSummary['overtime_cap_label'] ?? '0 H (0%)'", $overtimeIndexView);
        $this->assertStringContainsString("'value' => \$overtimeSummary['average_extra_hours_label'] ?? '0 H / Week'", $overtimeIndexView);
        $this->assertStringContainsString("'value' => \$overtimeSummary['tasks_finalized_label'] ?? '0 Tasks'", $overtimeIndexView);
        $this->assertStringContainsString("'value' => \$overtimeSummary['pending_spv_approval_hours_label'] ?? '0 Hours'", $overtimeIndexView);
        $this->assertStringContainsString("'sr_label' => (\$overtimeSummary['pending_spv_approval_hours_progress'] ?? 0).'% Pending SPV Approval'", $overtimeIndexView);
        $this->assertStringContainsString("'value' => \$overtimeSummary['completed_locked_hours_label'] ?? '0 Hours'", $overtimeIndexView);
        $this->assertStringContainsString("'value' => \$overtimeSummary['estimated_extra_earnings_label'] ?? 'Rp 0'", $overtimeIndexView);
        $this->assertStringContainsString("'value' => \$overtimeSummary['disputed_hours_label'] ?? '0 Hours'", $overtimeIndexView);
        $this->assertStringContainsString("'icon' => 'fa-solid fa-clock'", $overtimeIndexView);
        $this->assertStringContainsString("'icon' => 'fa-solid fa-gauge-high'", $overtimeIndexView);
        $this->assertStringContainsString("'icon' => 'fa-solid fa-chart-line'", $overtimeIndexView);
        $this->assertStringContainsString("'icon' => 'fa-solid fa-list-check'", $overtimeIndexView);
        $this->assertStringContainsString("'icon' => 'fa-solid fa-user-clock'", $overtimeIndexView);
        $this->assertStringContainsString("'icon' => 'fa-solid fa-lock'", $overtimeIndexView);
        $this->assertStringContainsString("'icon' => 'fa-solid fa-coins'", $overtimeIndexView);
        $this->assertStringContainsString("'icon' => 'fa-solid fa-triangle-exclamation'", $overtimeIndexView);
        $this->assertStringContainsString("'avatar_class' => 'avatar-info'", $overtimeIndexView);
        $this->assertStringContainsString("'progress_class' => 'bg-info'", $overtimeIndexView);
        $this->assertStringContainsString("'effect_class' => 'bg-secondary'", $overtimeIndexView);
        $this->assertStringNotContainsString("'icon_bg' =>", $overtimeIndexView);
        $this->assertStringNotContainsString("'accent_color' =>", $overtimeIndexView);
        $this->assertStringNotContainsString('<span class="title text-black fs-28 fw-semibold">15 Hours</span>', $overtimeIndexView);
        $this->assertStringContainsString('<option value="in_progress"', $overtimeIndexView);
        $this->assertStringContainsString('id="overtime-list"', $overtimeIndexView);
        $this->assertStringContainsString('@forelse (($overtimeList ?? collect()) as $overtimeItem)', $overtimeIndexView);
        $this->assertStringContainsString("{{ \$overtimeItem['detail_url'] ?? route('attendance.overtimes') }}", $overtimeIndexView);
        $this->assertStringContainsString("request()->routeIs('attendance.overtimes*')", $profileNavbarView);
        $this->assertStringContainsString("{{ \$overtimeItem['instruction'] ?? '-' }}", $overtimeIndexView);
        $this->assertStringContainsString("{{ \$overtimeItem['progress_label'] ?? 'Complete' }}", $overtimeIndexView);
        $this->assertStringContainsString("{{ \$overtimeItem['footer_status_label'] ?? 'Pending' }}", $overtimeIndexView);
        $this->assertStringNotContainsString("{{ \$overtimeItem['task_title']", $overtimeIndexView);
        $this->assertStringNotContainsString("{{ \$overtimeItem['staff_name']", $overtimeIndexView);
        $this->assertStringNotContainsString("{{ \$overtimeItem['department_name']", $overtimeIndexView);
        $this->assertStringContainsString('name="status"', $overtimeIndexView);
        $this->assertStringContainsString('name="timeframe"', $overtimeIndexView);
        $this->assertStringContainsString("\$overtimeStatusFilterValue = \$overtimeStatusFilter ?? 'all';", $overtimeIndexView);
        $this->assertStringContainsString("\$overtimeTimeframeFilterValue = \$overtimeTimeframeFilter ?? 'year_to_date';", $overtimeIndexView);
        $this->assertStringContainsString('$activeOvertimeFilterCount', $overtimeIndexView);
        $this->assertStringContainsString('id="overtimeFilterForm"', $overtimeIndexView);
        $this->assertStringContainsString('id="overtimeStatusFilter"', $overtimeIndexView);
        $this->assertStringContainsString('id="overtimeTimeframeFilter"', $overtimeIndexView);
        $this->assertStringContainsString('<option value="all" @selected($overtimeStatusFilterValue === \'all\')>Select All</option>', $overtimeIndexView);
        $this->assertStringContainsString('<option value="all" @selected($overtimeTimeframeFilterValue === \'all\')>Select All</option>', $overtimeIndexView);
        $this->assertStringContainsString("$('#filter').on('shown.bs.modal', function ()", $overtimeIndexView);
        $this->assertStringContainsString("$(this).find('.selectpicker').selectpicker('refresh');", $overtimeIndexView);
        $this->assertStringNotContainsString('#OVT-2605-0101', $overtimeIndexView);
        $this->assertStringContainsString('<option value="cancelled"', $overtimeIndexView);
        $this->assertStringContainsString("{{ \$overtimeReference ?? '#OVT' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['staff_name'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['supervisor_name'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("Overtime Log ({{ \$overtimeDetail['log_status'] ?? '-' }})", $overtimeDetailView);
        $this->assertStringContainsString("\$overtimeDetail['is_pic_verified'] ?? false", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['log_time_range'] ?? (\$overtimeDetail['approved_time_range'] ?? '-') }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['log_duration'] ?? (\$overtimeDetail['approved_duration'] ?? '-') }}", $overtimeDetailView);
        $this->assertStringContainsString("text-decoration-line-through\">{{ \$overtimeDetail['planned_time_range'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("text-decoration-line-through\">{{ \$overtimeDetail['planned_duration'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['instruction'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['estimated_earnings'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['payout_period'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['overtime_card_duration'] ?? '--:--' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['overtime_card_start'] ?? '--:--' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['overtime_card_ended'] ?? '--:--' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['overtime_card_current_time'] ?? '--:--:--' }}", $overtimeDetailView);
        $this->assertStringContainsString("\$canClockInOvertime = (bool) (\$overtimeDetail['clock_in_allowed'] ?? false);", $overtimeDetailView);
        $this->assertStringContainsString("\$clockInUnavailableTitle = trim((string) (\$overtimeDetail['clock_in_unavailable_title'] ?? ''));", $overtimeDetailView);
        $this->assertStringContainsString("\$clockInUnavailableMessage = trim((string) (\$overtimeDetail['clock_in_unavailable_message'] ?? ''));", $overtimeDetailView);
        $this->assertStringContainsString("\$canClockOutOvertime = (bool) (\$overtimeDetail['clock_out_allowed'] ?? false);", $overtimeDetailView);
        $this->assertStringContainsString("\$clockOutUnavailableTitle = trim((string) (\$overtimeDetail['clock_out_unavailable_title'] ?? ''));", $overtimeDetailView);
        $this->assertStringContainsString("\$clockOutUnavailableMessage = trim((string) (\$overtimeDetail['clock_out_unavailable_message'] ?? ''));", $overtimeDetailView);
        $this->assertStringContainsString('overtime-clock-in-blocked', $overtimeDetailView);
        $this->assertStringContainsString('data-overtime-clock-in-blocked-title="{{ $clockInUnavailableTitle }}"', $overtimeDetailView);
        $this->assertStringContainsString('data-overtime-clock-in-blocked-message="{{ $clockInUnavailableMessage }}"', $overtimeDetailView);
        $this->assertStringContainsString("$('[data-overtime-clock-in-blocked]').on('click', function (event)", $overtimeDetailView);
        $this->assertStringContainsString('overtime-clock-out-blocked', $overtimeDetailView);
        $this->assertStringContainsString('data-overtime-clock-out-blocked-title="{{ $clockOutUnavailableTitle }}"', $overtimeDetailView);
        $this->assertStringContainsString('data-overtime-clock-out-blocked-message="{{ $clockOutUnavailableMessage }}"', $overtimeDetailView);
        $this->assertStringContainsString("$('[data-overtime-clock-out-blocked]').on('click', function (event)", $overtimeDetailView);
        $this->assertStringContainsString('event.preventDefault();', $overtimeDetailView);
        $this->assertStringNotContainsString('overtime-clock-in-status', $overtimeDetailView);
        $this->assertStringNotContainsString('data-overtime-clock-in-status', $overtimeDetailView);
        $this->assertStringNotContainsString('data-overtime-clock-in-warning', $overtimeDetailView);
        $this->assertStringNotContainsString('overtime-clock-in-warning', $overtimeDetailView);
        $this->assertStringNotContainsString('Clock in window:', $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['scheduled_start_label'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['scheduled_end_label'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['actual_start_label'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['actual_end_label'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['target_duration_label'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['actual_duration_label'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}", $overtimeDetailView);
        $this->assertStringContainsString('function showSwalAlert(iconType, titleText, messageText)', $overtimeDetailView);
        $this->assertStringContainsString('Swal.fire({', $overtimeDetailView);
        $this->assertStringNotContainsString('window.alert', $overtimeDetailView);
        $this->assertStringNotContainsString('window.toastr', $overtimeDetailView);
        $this->assertStringContainsString('data-overtime-current-time', $overtimeDetailView);
        $this->assertStringContainsString('function updateOvertimeCurrentTime()', $overtimeDetailView);
        $this->assertStringContainsString('function submitOvertimeSession(action, button)', $overtimeDetailView);
        $this->assertStringContainsString("submitOvertimeSession('clock_in'", $overtimeDetailView);
        $this->assertStringContainsString("submitOvertimeSession('clock_out'", $overtimeDetailView);
        $this->assertStringContainsString('var overtimePayload = @js($overtimeSessionPayload);', $overtimeDetailView);
        $this->assertStringContainsString("'update_url' => \$overtimeDetail['update_url'] ?? null", $overtimeDetailView);
        $this->assertStringContainsString("'clock_in_allowed' => (bool) (\$overtimeDetail['clock_in_allowed'] ?? false)", $overtimeDetailView);
        $this->assertStringContainsString("'clock_in_unavailable_title' => \$overtimeDetail['clock_in_unavailable_title'] ?? null", $overtimeDetailView);
        $this->assertStringContainsString("'clock_out_allowed' => (bool) (\$overtimeDetail['clock_out_allowed'] ?? false)", $overtimeDetailView);
        $this->assertStringContainsString("'clock_out_unavailable_title' => \$overtimeDetail['clock_out_unavailable_title'] ?? null", $overtimeDetailView);
        $this->assertStringContainsString("if (action === 'clock_in' && !overtimePayload.clock_in_allowed) {", $overtimeDetailView);
        $this->assertStringContainsString("overtimePayload.clock_in_unavailable_title || 'Absen Lembur Belum Tersedia'", $overtimeDetailView);
        $this->assertStringContainsString("if (action === 'clock_out' && !overtimePayload.clock_out_allowed) {", $overtimeDetailView);
        $this->assertStringContainsString("overtimePayload.clock_out_unavailable_title || 'Clock Out Belum Tersedia'", $overtimeDetailView);
        $this->assertStringContainsString('actual_start_time: actualStartTime', $overtimeDetailView);
        $this->assertStringContainsString('actual_end_time: actualEndTime', $overtimeDetailView);
        $this->assertStringContainsString('id="overtimeClockInSubmit"', $overtimeDetailView);
        $this->assertStringContainsString('id="overtimeClockOutSubmit"', $overtimeDetailView);
        $this->assertStringContainsString("timeZone: 'Asia/Jakarta'", $overtimeDetailView);
        $this->assertStringContainsString('<span class="badge badge-sm badge-warning light">Pending</span>', $overtimeDetailView);
        $this->assertStringNotContainsString('Thomas Jefferson', $overtimeDetailView);
        $this->assertStringNotContainsString('Michael Scott', $overtimeDetailView);
        $this->assertStringNotContainsString('Renovasi fasad dan desain interior rumah bintaro.', $overtimeDetailView);
        $this->assertStringNotContainsString('Rp. 100.000', $overtimeDetailView);
        $this->assertStringContainsString('@forelse (($overtimeLifecycleTracker ?? collect()) as $lifecyclePhase)', $overtimeDetailView);
        $this->assertStringContainsString("{{ \$lifecycleItem['step_order'] ?? '-' }}. {{ \$lifecycleItem['title'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("@forelse ((\$overtimeTaskItems['pending'] ?? collect()) as \$taskItem)", $overtimeDetailView);
        $this->assertStringContainsString("@forelse ((\$overtimeTaskItems['finished'] ?? collect()) as \$taskItem)", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$taskItem['title'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$taskItem['date_label'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString('overtime-task-toggle', $overtimeDetailView);
        $this->assertStringContainsString('overtime-task-checkbox', $overtimeDetailView);
        $this->assertStringContainsString('overtime-task-checkbox-label', $overtimeDetailView);
        $this->assertStringContainsString('user-select: none;', $overtimeDetailView);
        $this->assertStringContainsString('pointer-events: none;', $overtimeDetailView);
        $this->assertStringContainsString('id="createTaskForm"', $overtimeDetailView);
        $this->assertStringContainsString("\$overtimeDetail['can_create_task']", $overtimeDetailView);
        $this->assertStringContainsString('Lakukan Overtime Clock In terlebih dahulu', $overtimeDetailView);
        $this->assertStringContainsString('name="task_category"', $overtimeDetailView);
        $this->assertStringContainsString('name="project_id"', $overtimeDetailView);
        $this->assertStringContainsString('Belum ada pilihan project tersedia', $overtimeDetailView);
        $this->assertStringContainsString('id="createTaskStartDateValue" value="{{ $taskDefaultDate ?? now(\'Asia/Jakarta\')->toDateString() }}" required', $overtimeDetailView);
        $this->assertStringContainsString('id="createTaskStartDatePicker" data-date-target="#createTaskStartDateValue"', $overtimeDetailView);
        $this->assertStringContainsString('id="createTaskDueDateValue" value="{{ $taskDefaultDate ?? now(\'Asia/Jakarta\')->toDateString() }}" required', $overtimeDetailView);
        $this->assertStringContainsString('id="createTaskDueDatePicker" data-date-target="#createTaskDueDateValue"', $overtimeDetailView);
        $this->assertStringContainsString('id="updateTaskStartDateValue" required', $overtimeDetailView);
        $this->assertStringContainsString('id="updateTaskStartDatePicker" data-date-target="#updateTaskStartDateValue"', $overtimeDetailView);
        $this->assertStringContainsString('id="updateTaskDueDateValue" required', $overtimeDetailView);
        $this->assertStringContainsString('id="updateTaskDueDatePicker" data-date-target="#updateTaskDueDateValue"', $overtimeDetailView);
        $this->assertStringContainsString('class="form-control overtime-task-single-date-picker"', $overtimeDetailView);
        $this->assertStringContainsString('singleDatePicker: true', $overtimeDetailView);
        $this->assertStringContainsString('function initTaskSingleDatePickers()', $overtimeDetailView);
        $this->assertStringNotContainsString('type="date" class="form-control" name="start_date"', $overtimeDetailView);
        $this->assertStringNotContainsString('type="date" class="form-control" name="due_date"', $overtimeDetailView);
        $this->assertStringContainsString('function submitCreateTaskForm(event)', $overtimeDetailView);
        $this->assertStringContainsString('function updateCreateTaskProjectState()', $overtimeDetailView);
        $this->assertStringContainsString('id="updateTaskForm"', $overtimeDetailView);
        $this->assertStringContainsString('id="updateTaskProjectSelect"', $overtimeDetailView);
        $this->assertStringContainsString('id="updateTaskCategoryProject"', $overtimeDetailView);
        $this->assertStringContainsString('id="deleteTaskForm"', $overtimeDetailView);
        $this->assertStringContainsString('id="deleteTaskTitle"', $overtimeDetailView);
        $this->assertStringContainsString('id="deleteTaskSubmit"', $overtimeDetailView);
        $this->assertStringContainsString('function openUpdateTaskModal(event)', $overtimeDetailView);
        $this->assertStringContainsString('function submitUpdateTaskForm(event)', $overtimeDetailView);
        $this->assertStringContainsString('function toggleTaskStatusFromCheckbox(event)', $overtimeDetailView);
        $this->assertStringContainsString('function openDeleteTaskModal(event)', $overtimeDetailView);
        $this->assertStringContainsString('function submitDeleteTaskForm(event)', $overtimeDetailView);
        $this->assertStringContainsString('function updateUpdateTaskProjectState()', $overtimeDetailView);
        $this->assertStringContainsString("$('.overtime-task-list').on('click', '[data-task-toggle-url]', toggleTaskStatusFromCheckbox);", $overtimeDetailView);
        $this->assertStringContainsString("$('.overtime-task-list').on('keydown', '[data-task-toggle-url]', function (event)", $overtimeDetailView);
        $this->assertStringContainsString('var toggleControl = $(event.currentTarget);', $overtimeDetailView);
        $this->assertStringContainsString("var willComplete = !checkbox.prop('checked');", $overtimeDetailView);
        $this->assertStringContainsString("var previousCheckedState = checkbox.prop('checked');", $overtimeDetailView);
        $this->assertStringContainsString("checkbox.prop('checked', nextCheckedState);", $overtimeDetailView);
        $this->assertStringContainsString("toggleControl.attr('aria-checked', nextCheckedState ? 'true' : 'false');", $overtimeDetailView);
        $this->assertStringContainsString('var taskItemsById = @js($taskItemPayload);', $overtimeDetailView);
        $this->assertStringContainsString("form.find('[name=\"attachment_path\"]').val(taskItem.attachment_path || '');", $overtimeDetailView);
        $this->assertStringContainsString("form.find('[name=\"blockers\"]').val(taskItem.blockers || '');", $overtimeDetailView);
        $this->assertStringContainsString('data-bs-target="#delete"', $overtimeDetailView);
        $this->assertStringContainsString('data-bs-target="#update"', $overtimeDetailView);
        $this->assertStringContainsString("data-task-update-url=\"{{ \$taskItem['update_url'] ?? '#' }}\"", $overtimeDetailView);
        $this->assertStringContainsString("data-task-delete-url=\"{{ \$taskItem['delete_url'] ?? '#' }}\"", $overtimeDetailView);
        $this->assertStringContainsString("data-task-toggle-url=\"{{ \$taskItem['update_url'] ?? '#' }}\"", $overtimeDetailView);
        $this->assertStringContainsString("var nextStatus = willComplete ? 'completed' : 'pending';", $overtimeDetailView);
        $this->assertStringContainsString('Buka Kembali Task?', $overtimeDetailView);
        $this->assertStringContainsString('Tandai Task Completed?', $overtimeDetailView);
        $this->assertStringContainsString('akan ditandai completed.', $overtimeDetailView);
        $this->assertStringContainsString("showSwalAlert('error', 'Gagal', responseMessage);", $overtimeDetailView);
        $this->assertStringContainsString('overtime-task-item', $overtimeDetailView);
        $this->assertStringContainsString('data-task-id="{{ $taskItem[\'id\'] ?? \'\' }}"', $overtimeDetailView);
        $this->assertStringContainsString('overtime-task-actions', $overtimeDetailView);
        $this->assertStringContainsString('overtime-task-action', $overtimeDetailView);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr) auto;', $overtimeDetailView);
        $this->assertStringNotContainsString("<span class=\"badge badge-sm badge-warning light\">{{ \$taskItem['status'] ?? 'Pending' }}</span>", $overtimeDetailView);
        $this->assertStringNotContainsString("{{ \$taskItem['status'] ?? 'Completed' }}", $overtimeDetailView);
        $this->assertStringNotContainsString('Approved & Locked', $overtimeIndexView);
    }
}
