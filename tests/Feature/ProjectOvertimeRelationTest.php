<?php

namespace Tests\Feature;

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
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProjectOvertimeRelationTest extends TestCase
{
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
        $overtimeController = File::get(app_path('Http/Controllers/AttendanceOvertimeController.php'));
        $overtimeModel = File::get(app_path('Models/AttendanceOvertime.php'));
        $overtimeIndexView = File::get(resource_path('views/attendance/overtimes/index.blade.php'));
        $overtimeDetailView = File::get(resource_path('views/attendance/overtimes/detail.blade.php'));
        $profileNavbarView = File::get(resource_path('views/attendance/layouts/profile-navbar.blade.php'));

        foreach ([
            "private const OVERTIME_STATUS_ASSIGNED = 'assigned';",
            "private const OVERTIME_STATUS_IN_PROGRESS = 'in_progress';",
            "private const OVERTIME_STATUS_COMPLETED = 'completed';",
            "private const OVERTIME_STATUS_CANCELLED = 'cancelled';",
            "'status' => ['nullable', 'in:assigned,in_progress,completed,cancelled']",
            'private function resolveOvertimeStatus',
            'private function createInitialOvertimeLifecycleLogs',
            'private function syncOvertimeLifecycleLogs',
            'private function buildOvertimeDetailSummary',
            'private function resolveOvertimeDirectorApprover',
            'private function overtimeLifecycleLog',
            'private function formatOvertimeDetailLogDateTime',
            'private function formatOvertimeModalDateTimeLabel',
            'private function formatDurationSummaryLabel',
            'private function calculateDurationClockLabel',
            'private function buildOvertimeLifecycleTracker',
            'private function buildOvertimeTaskItems',
            'private function buildOvertimeTaskQuery',
            'private function overtimeTaskItemValue',
            'public function storeTask',
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
            'private function normalizeOvertimeIndexStatusFilter',
            'private function overtimeLifecycleProgressPercent',
            'private function overtimeFooterStatusLabel',
            "route('attendance.overtimes.detail', \$overtime)",
            "'record_number' => (string) (\$overtime->record_number ?? ''),",
            "return '#'.\$recordNumber;",
            "->where('status', self::OVERTIME_STATUS_COMPLETED)",
            "->whereIn('status', [",
            "->orWhereNull('overtime_id')",
            "'overtime_id' => (string) \$attendanceOvertime->id,",
            "'project_id' => \$projectId",
            "'delete_url' => route('attendance.overtimes.tasks.destroy'",
            'Task berhasil dihapus.',
            'ProjectMember::query()',
            "'board_of_rector'",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $overtimeController);
        }
        $this->assertStringNotContainsString("->with('employee.deployment.department:id,name')", $overtimeController);

        foreach ([
            'protected static function generateRecordNumber(mixed $overtimeDate): string',
            "\$prefix = 'OVT-'.\$date->format('ym').'-';",
            "->where('record_number', 'like', \$prefix.'%')",
            '$overtime->record_number = static::generateRecordNumber($overtime->overtime_date);',
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $overtimeModel);
        }

        $this->assertStringContainsString('Assigned Hours', $overtimeIndexView);
        $this->assertStringContainsString('Completed & Locked', $overtimeIndexView);
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
        $this->assertStringNotContainsString('#OVT-2605-0101', $overtimeIndexView);
        $this->assertStringContainsString('<option value="cancelled"', $overtimeIndexView);
        $this->assertStringContainsString("{{ \$overtimeReference ?? '#OVT' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['staff_name'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['supervisor_name'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("Overtime Log ({{ \$overtimeDetail['log_status'] ?? '-' }})", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['instruction'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['estimated_earnings'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['payout_period'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['overtime_card_duration'] ?? '--:--' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['overtime_card_start'] ?? '--:--' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['overtime_card_ended'] ?? '--:--' }}", $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeDetail['overtime_card_current_time'] ?? '--:--:--' }}", $overtimeDetailView);
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
        $this->assertStringContainsString('id="createTaskForm"', $overtimeDetailView);
        $this->assertStringContainsString('name="task_category"', $overtimeDetailView);
        $this->assertStringContainsString('name="project_id"', $overtimeDetailView);
        $this->assertStringContainsString('Belum ada pilihan project tersedia', $overtimeDetailView);
        $this->assertStringContainsString('type="date" class="form-control" name="start_date"', $overtimeDetailView);
        $this->assertStringContainsString('type="date" class="form-control" name="due_date"', $overtimeDetailView);
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
        $this->assertStringContainsString('function openDeleteTaskModal(event)', $overtimeDetailView);
        $this->assertStringContainsString('function submitDeleteTaskForm(event)', $overtimeDetailView);
        $this->assertStringContainsString('function updateUpdateTaskProjectState()', $overtimeDetailView);
        $this->assertStringContainsString('var taskItemsById = @js($taskItemPayload);', $overtimeDetailView);
        $this->assertStringContainsString("form.find('[name=\"attachment_path\"]').val(taskItem.attachment_path || '');", $overtimeDetailView);
        $this->assertStringContainsString("form.find('[name=\"blockers\"]').val(taskItem.blockers || '');", $overtimeDetailView);
        $this->assertStringContainsString('data-bs-target="#delete"', $overtimeDetailView);
        $this->assertStringContainsString('data-bs-target="#update"', $overtimeDetailView);
        $this->assertStringContainsString("data-task-update-url=\"{{ \$taskItem['update_url'] ?? '#' }}\"", $overtimeDetailView);
        $this->assertStringContainsString("data-task-delete-url=\"{{ \$taskItem['delete_url'] ?? '#' }}\"", $overtimeDetailView);
        $this->assertStringContainsString("showSwalAlert('error', 'Gagal', responseMessage);", $overtimeDetailView);
        $this->assertStringContainsString('overtime-task-item', $overtimeDetailView);
        $this->assertStringContainsString('data-task-id="{{ $taskItem[\'id\'] ?? \'\' }}"', $overtimeDetailView);
        $this->assertStringContainsString('overtime-task-actions', $overtimeDetailView);
        $this->assertStringContainsString('overtime-task-action', $overtimeDetailView);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr) auto;', $overtimeDetailView);
        $this->assertStringNotContainsString("{{ \$taskItem['status'] ?? 'Completed' }}", $overtimeDetailView);
        $this->assertStringNotContainsString('Pending SPV Approval', $overtimeIndexView);
        $this->assertStringNotContainsString('Approved & Locked', $overtimeIndexView);
    }
}
