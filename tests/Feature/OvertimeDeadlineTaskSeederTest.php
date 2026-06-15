<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class OvertimeDeadlineTaskSeederTest extends TestCase
{
    public function test_overtime_deadline_task_seeder_is_registered_and_creates_pending_and_completed_overtime_tasks(): void
    {
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));
        $overtimeSeeder = File::get(database_path('seeders/OvertimeDeadlineTaskSeeder.php'));

        $this->assertStringContainsString('OvertimeDeadlineTaskSeeder::class', $databaseSeeder);
        $this->assertStringContainsString("private const PROJECT_CODE = 'RNB-EVENT-2026';", $overtimeSeeder);
        $this->assertStringContainsString("Carbon::now('Asia/Jakarta')->startOfDay()", $overtimeSeeder);
        $this->assertStringContainsString('$deadline = $today->copy()->addDay();', $overtimeSeeder);
        $this->assertStringContainsString("'overtime_date' => \$deadline->toDateString()", $overtimeSeeder);
        $this->assertStringContainsString("'due_date' => \$deadline->toDateString()", $overtimeSeeder);
        $this->assertStringContainsString("'start_date' => \$today->toDateString()", $overtimeSeeder);
        $this->assertStringContainsString('Seed overtime deadline task for {$username}.', $overtimeSeeder);
        $this->assertStringContainsString('ProjectTask::query()->create', $overtimeSeeder);
        $this->assertStringNotContainsString('private const TASK_DEPARTMENTS = [', $overtimeSeeder);
        $this->assertStringNotContainsString('private function resolveDepartmentIds(): Collection', $overtimeSeeder);
        $this->assertStringNotContainsString("'department_id' =>", $overtimeSeeder);
        $this->assertStringContainsString("private const TASK_TITLE = 'Overtime deadline follow-up';", $overtimeSeeder);
        $this->assertStringContainsString('private const COMPLETED_TASK_TITLES = [', $overtimeSeeder);
        $this->assertStringContainsString('private function seedProjectTasks', $overtimeSeeder);
        $this->assertStringContainsString('private function seededTaskTitles(): array', $overtimeSeeder);
        $this->assertStringContainsString("'title' => \$taskTitle", $overtimeSeeder);
        $this->assertStringContainsString('...self::COMPLETED_TASK_TITLES', $overtimeSeeder);
        $this->assertStringNotContainsString(' "{$taskTitle} - {$username}"', $overtimeSeeder);
        $this->assertStringContainsString('AttendanceOvertime::query()->create', $overtimeSeeder);
        $this->assertStringContainsString('OvertimeLifecycleLog::query()->create', $overtimeSeeder);
        $this->assertStringContainsString('private function resetSeededOvertimeTasks(): void', $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'assigned'", $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'pending'", $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'completed'", $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'complete'", $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'waiting'", $overtimeSeeder);
        $this->assertStringContainsString("'blockers' => 'Waiting for final overtime deliverable update.'", $overtimeSeeder);
        $this->assertStringContainsString("'blockers' => null", $overtimeSeeder);
        $this->assertStringContainsString("'attachment_path' => null", $overtimeSeeder);
        $this->assertStringContainsString("'completed_at' => null", $overtimeSeeder);
        $this->assertStringContainsString("'completed_at' => \$today->copy()->setTime(16, 0)->addMinutes(\$taskIndex)->toDateTimeString()", $overtimeSeeder);

        foreach (['staff31', 'staff32', 'staff33', 'staff34'] as $username) {
            $this->assertStringContainsString("'{$username}'", $overtimeSeeder);
        }

        foreach ([
            'Overtime deadline follow-up',
            'Finalize overtime preparation notes',
            'Submit overtime deliverable evidence',
            'Complete overtime handoff checklist',
        ] as $taskTitle) {
            $this->assertStringContainsString("'{$taskTitle}'", $overtimeSeeder);
        }
        $this->assertStringNotContainsString('Overtime deadline follow-up - Administration', $overtimeSeeder);
        $this->assertStringNotContainsString('Overtime deadline follow-up - Graphic Design', $overtimeSeeder);
        $this->assertStringNotContainsString('Overtime deadline follow-up - 3D Event Design', $overtimeSeeder);
        $this->assertStringNotContainsString('Overtime deadline follow-up - Documentation', $overtimeSeeder);

        $this->assertSame(8, substr_count($overtimeSeeder, "'phase' => '"));
    }
}
