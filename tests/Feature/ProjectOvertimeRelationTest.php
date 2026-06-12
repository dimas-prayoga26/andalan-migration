<?php

namespace Tests\Feature;

use App\Models\AttendanceOvertime;
use App\Models\Department;
use App\Models\Employee;
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
        $this->assertSame('string', $overtimeLifecycleLog->getKeyType());

        $this->assertInstanceOf(BelongsTo::class, $project->company());
        $this->assertInstanceOf(BelongsTo::class, $project->creator());
        $this->assertInstanceOf(HasMany::class, $project->memberships());
        $this->assertInstanceOf(BelongsToMany::class, $project->members());
        $this->assertInstanceOf(HasMany::class, $project->tasks());
        $this->assertInstanceOf(BelongsTo::class, $projectMember->project());
        $this->assertInstanceOf(BelongsTo::class, $projectMember->employee());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->project());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->department());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->employee());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->overtime());
        $this->assertInstanceOf(HasMany::class, $department->projectTasks());
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
            "\$table->foreignUuid('project_id')->constrained('projects', 'id')->cascadeOnDelete();",
            "\$table->foreignUuid('department_id')->constrained('departments', 'id')->cascadeOnDelete();",
            "\$table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();",
            "\$table->foreignUuid('overtime_id')->nullable()->constrained('overtimes', 'id')->nullOnDelete();",
            "\$table->string('title');",
            "\$table->string('status')->default('pending');",
            "\$table->string('priority')->default('medium');",
            "\$table->index(['department_id', 'status'], 'project_tasks_department_status_index');",
            "\$table->index(['overtime_id', 'status'], 'project_tasks_overtime_status_index');",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $projectTaskMigration);
        }
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

        foreach ([
            "private const OVERTIME_STATUS_ASSIGNED = 'assigned';",
            "private const OVERTIME_STATUS_IN_PROGRESS = 'in_progress';",
            "private const OVERTIME_STATUS_COMPLETED = 'completed';",
            "private const OVERTIME_STATUS_CANCELLED = 'cancelled';",
            "'status' => ['nullable', 'in:assigned,in_progress,completed,cancelled']",
            'private function resolveOvertimeStatus',
            'private function createInitialOvertimeLifecycleLogs',
            'private function syncOvertimeLifecycleLogs',
            'private function buildOvertimeLifecycleTracker',
            "'record_number' => (string) (\$overtime->record_number ?? ''),",
            "return '#'.\$recordNumber;",
            "->where('status', self::OVERTIME_STATUS_COMPLETED)",
            "->whereIn('status', [",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $overtimeController);
        }

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
        $this->assertStringContainsString('<option>In Progress</option>', $overtimeIndexView);
        $this->assertStringContainsString('<option>Cancelled</option>', $overtimeDetailView);
        $this->assertStringContainsString("{{ \$overtimeReference ?? '#OVT' }}", $overtimeDetailView);
        $this->assertStringContainsString('@forelse (($overtimeLifecycleTracker ?? collect()) as $lifecyclePhase)', $overtimeDetailView);
        $this->assertStringContainsString("{{ \$lifecycleItem['step_order'] ?? '-' }}. {{ \$lifecycleItem['title'] ?? '-' }}", $overtimeDetailView);
        $this->assertStringNotContainsString('Pending SPV Approval', $overtimeIndexView);
        $this->assertStringNotContainsString('Approved & Locked', $overtimeIndexView);
    }
}
