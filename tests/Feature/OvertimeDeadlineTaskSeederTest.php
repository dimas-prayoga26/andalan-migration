<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class OvertimeDeadlineTaskSeederTest extends TestCase
{
    public function test_overtime_deadline_task_seeder_is_registered_and_creates_one_overtime_task_per_staff(): void
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
        $this->assertStringContainsString('AttendanceOvertime::query()->create', $overtimeSeeder);
        $this->assertStringContainsString('OvertimeLifecycleLog::query()->create', $overtimeSeeder);
        $this->assertStringContainsString('private function resetSeededOvertimeTasks(): void', $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'assigned'", $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'pending'", $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'complete'", $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'waiting'", $overtimeSeeder);

        foreach (['staff31', 'staff32', 'staff33', 'staff34'] as $username) {
            $this->assertStringContainsString("'{$username}'", $overtimeSeeder);
        }

        foreach ([
            'Overtime deadline follow-up - Administration',
            'Overtime deadline follow-up - Graphic Design',
            'Overtime deadline follow-up - 3D Event Design',
            'Overtime deadline follow-up - Documentation',
        ] as $taskTitle) {
            $this->assertStringContainsString("'{$taskTitle}'", $overtimeSeeder);
        }

        $this->assertSame(8, substr_count($overtimeSeeder, "'phase' => '"));
    }
}
