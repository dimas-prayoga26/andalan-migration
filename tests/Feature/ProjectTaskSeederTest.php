<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProjectTaskSeederTest extends TestCase
{
    public function test_project_task_seeder_is_registered_and_targets_rnb_staff_project_tasks(): void
    {
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));
        $projectTaskSeeder = File::get(database_path('seeders/ProjectTaskSeeder.php'));

        $this->assertStringContainsString('ProjectTaskSeeder::class', $databaseSeeder);
        $this->assertStringContainsString("private const PROJECT_CODE = 'RNB-EVENT-2026';", $projectTaskSeeder);
        $this->assertStringContainsString("DB::table('companies')->where('name', 'RNB')->value('id')", $projectTaskSeeder);
        $this->assertStringContainsString("->where('staff_employee_id', \$staffEmployeeId)", $projectTaskSeeder);
        $this->assertStringContainsString("'created_by' => \$supervisorUserId", $projectTaskSeeder);
        $this->assertStringNotContainsString("'created_by' => \$staffUser->id", $projectTaskSeeder);
        $this->assertStringNotContainsString('seedProjectTasks($project, $departments, $staffUsers, $supervisorUserId)', $projectTaskSeeder);
        $this->assertStringNotContainsString('private const PROJECT_DEPARTMENTS = [', $projectTaskSeeder);
        $this->assertStringNotContainsString('private const STAFF_DEPARTMENTS = [', $projectTaskSeeder);
        $this->assertStringNotContainsString("\$departmentKey = self::STAFF_DEPARTMENTS[\$taskData['username']] ?? \$taskData['department'];", $projectTaskSeeder);

        foreach (['staff31', 'staff32', 'staff33', 'staff34'] as $username) {
            $this->assertStringContainsString("'{$username}'", $projectTaskSeeder);
            $this->assertSame(5, substr_count($projectTaskSeeder, "'username' => '{$username}'"));
        }

        $this->assertSame(0, substr_count($projectTaskSeeder, "'department' =>"));

        foreach ([
            'Prepare project administration checklist',
            'Create event key visual adaptation',
            'Draft 3D event booth layout',
            'Prepare documentation shot list',
            'Publish technical coordination brief',
            'Prepare publication archive delivery',
        ] as $taskTitle) {
            $this->assertStringContainsString("'title' => '{$taskTitle}'", $projectTaskSeeder);
        }

        $this->assertStringContainsString('ProjectMember::query()->create', $projectTaskSeeder);
        $this->assertStringNotContainsString('private function resolveDepartments(): Collection', $projectTaskSeeder);
        $this->assertStringNotContainsString("'department_id' =>", $projectTaskSeeder);
        $this->assertStringContainsString('ProjectTask::query()->create', $projectTaskSeeder);
        $this->assertStringContainsString("'project_id' => (\$taskData['is_daily'] ?? false) === true ? null : \$project->id", $projectTaskSeeder);
        $this->assertStringContainsString("'blockers' => \$taskData['blockers'] ?? null", $projectTaskSeeder);
        $this->assertStringContainsString("'attachment_path' => \$taskData['attachment_path'] ?? null", $projectTaskSeeder);
        $this->assertStringContainsString("'blockers' => 'Waiting for updated manpower confirmation from supervisor.'", $projectTaskSeeder);
        $this->assertStringContainsString("'blockers' => 'Waiting for venue access confirmation.'", $projectTaskSeeder);
        $this->assertStringContainsString('private function dailyTaskTitles(): array', $projectTaskSeeder);
        $this->assertStringContainsString("->whereNull('project_id')", $projectTaskSeeder);
    }
}
