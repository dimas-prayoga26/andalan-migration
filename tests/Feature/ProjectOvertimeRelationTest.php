<?php

namespace Tests\Feature;

use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectSection;
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
        $projectSection = new ProjectSection;
        $projectTask = new ProjectTask;
        $employee = new Employee;
        $overtime = new AttendanceOvertime;
        $user = new User;

        $this->assertFalse($project->incrementing);
        $this->assertFalse($projectMember->incrementing);
        $this->assertFalse($projectSection->incrementing);
        $this->assertFalse($projectTask->incrementing);
        $this->assertSame('string', $project->getKeyType());
        $this->assertSame('string', $projectMember->getKeyType());
        $this->assertSame('string', $projectSection->getKeyType());
        $this->assertSame('string', $projectTask->getKeyType());

        $this->assertInstanceOf(BelongsTo::class, $project->company());
        $this->assertInstanceOf(BelongsTo::class, $project->creator());
        $this->assertInstanceOf(HasMany::class, $project->memberships());
        $this->assertInstanceOf(BelongsToMany::class, $project->members());
        $this->assertInstanceOf(HasMany::class, $project->sections());
        $this->assertInstanceOf(HasMany::class, $project->tasks());
        $this->assertInstanceOf(BelongsTo::class, $projectMember->project());
        $this->assertInstanceOf(BelongsTo::class, $projectMember->employee());
        $this->assertInstanceOf(BelongsTo::class, $projectSection->project());
        $this->assertInstanceOf(HasMany::class, $projectSection->tasks());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->project());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->section());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->employee());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->creator());
        $this->assertInstanceOf(BelongsTo::class, $projectTask->overtime());
        $this->assertInstanceOf(BelongsToMany::class, $employee->projects());
        $this->assertInstanceOf(HasMany::class, $employee->projectMemberships());
        $this->assertInstanceOf(HasMany::class, $employee->projectTasks());
        $this->assertInstanceOf(HasMany::class, $employee->overtimes());
        $this->assertInstanceOf(HasMany::class, $overtime->projectTasks());
        $this->assertInstanceOf(HasMany::class, $user->createdProjects());
        $this->assertInstanceOf(HasMany::class, $user->createdProjectTasks());
    }

    public function test_project_overtime_migrations_define_required_columns(): void
    {
        $projectMigration = File::get(database_path('migrations/2026_06_11_042441_create_projects_table.php'));
        $projectMemberMigration = File::get(database_path('migrations/2026_06_11_042442_create_project_members_table.php'));
        $projectSectionMigration = File::get(database_path('migrations/2026_06_11_042442_create_project_sections_table.php'));
        $projectTaskMigration = File::get(database_path('migrations/2026_06_11_042442_create_project_tasks_table.php'));
        $overtimeMigration = File::get(database_path('migrations/2026_05_05_014427_create_overtimes_table.php'));

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
            "Schema::create('project_sections'",
            "\$table->uuid('id')->primary();",
            "\$table->foreignUuid('project_id')->constrained('projects', 'id')->cascadeOnDelete();",
            "\$table->string('name');",
            "\$table->unsignedInteger('sort_order')->default(0);",
            "\$table->string('status')->default('active');",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $projectSectionMigration);
        }

        foreach ([
            "Schema::create('project_tasks'",
            "\$table->foreignUuid('project_id')->constrained('projects', 'id')->cascadeOnDelete();",
            "\$table->foreignUuid('project_section_id')->constrained('project_sections', 'id')->cascadeOnDelete();",
            "\$table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();",
            "\$table->foreignUuid('overtime_id')->nullable()->constrained('overtimes', 'id')->nullOnDelete();",
            "\$table->string('title');",
            "\$table->string('status')->default('pending');",
            "\$table->string('priority')->default('medium');",
            "\$table->index(['project_section_id', 'status'], 'project_tasks_section_status_index');",
            "\$table->index(['overtime_id', 'status'], 'project_tasks_overtime_status_index');",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $projectTaskMigration);
        }

        $this->assertStringContainsString(
            "\$table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled'])->default('assigned');",
            $overtimeMigration
        );
        $this->assertFileDoesNotExist(database_path('migrations/2026_06_11_042443_add_task_id_to_overtimes_table.php'));
        $this->assertFileDoesNotExist(database_path('migrations/2026_06_11_064143_update_overtime_status_flow_values.php'));
        $this->assertFileDoesNotExist(database_path('migrations/2026_06_11_065337_move_overtime_task_relation_to_project_tasks_table.php'));
    }

    public function test_overtime_status_flow_uses_assignment_lifecycle_values(): void
    {
        $overtimeController = File::get(app_path('Http/Controllers/AttendanceOvertimeController.php'));
        $overtimeIndexView = File::get(resource_path('views/attendance/overtimes/index.blade.php'));
        $overtimeDetailView = File::get(resource_path('views/attendance/overtimes/detail.blade.php'));

        foreach ([
            "private const OVERTIME_STATUS_ASSIGNED = 'assigned';",
            "private const OVERTIME_STATUS_IN_PROGRESS = 'in_progress';",
            "private const OVERTIME_STATUS_COMPLETED = 'completed';",
            "private const OVERTIME_STATUS_CANCELLED = 'cancelled';",
            "'status' => ['nullable', 'in:assigned,in_progress,completed,cancelled']",
            'private function resolveOvertimeStatus',
            "->where('status', self::OVERTIME_STATUS_COMPLETED)",
            "->whereIn('status', [",
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $overtimeController);
        }

        $this->assertStringContainsString('Assigned Hours', $overtimeIndexView);
        $this->assertStringContainsString('Completed & Locked', $overtimeIndexView);
        $this->assertStringContainsString('<option>In Progress</option>', $overtimeIndexView);
        $this->assertStringContainsString('<option>Cancelled</option>', $overtimeDetailView);
        $this->assertStringNotContainsString('Pending SPV Approval', $overtimeIndexView);
        $this->assertStringNotContainsString('Approved & Locked', $overtimeIndexView);
    }
}
