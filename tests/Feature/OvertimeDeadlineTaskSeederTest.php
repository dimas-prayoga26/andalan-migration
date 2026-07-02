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
        $this->assertStringContainsString("\$now = Carbon::now('Asia/Jakarta');", $overtimeSeeder);
        $this->assertStringContainsString('$today = $now->copy()->startOfDay();', $overtimeSeeder);
        $this->assertStringContainsString('private const OVERTIME_SCENARIOS = [', $overtimeSeeder);
        $this->assertStringContainsString('$overtimeDate = $today->copy()->addDays((int) $scenario[\'date_offset_days\']);', $overtimeSeeder);
        $this->assertStringContainsString("'overtime_date' => \$overtimeDate->toDateString()", $overtimeSeeder);
        $this->assertStringContainsString("'planned_start_time' => \$scenario['planned_start_time']", $overtimeSeeder);
        $this->assertStringContainsString("'planned_end_time' => \$scenario['planned_end_time']", $overtimeSeeder);
        $this->assertStringContainsString("'actual_start_time' => \$scenario['actual_start_time']", $overtimeSeeder);
        $this->assertStringContainsString("'actual_end_time' => \$scenario['actual_end_time']", $overtimeSeeder);
        $this->assertStringContainsString("'calculated_hours' => \$scenario['calculated_hours']", $overtimeSeeder);
        $this->assertStringContainsString("'status' => \$scenario['status']", $overtimeSeeder);
        $this->assertStringContainsString("'due_date' => \$deadline->toDateString()", $overtimeSeeder);
        $this->assertStringContainsString('Seed task untuk overtime dengan deadline hari ini.', $overtimeSeeder);
        $this->assertStringContainsString("'start_date' => \$today->toDateString()", $overtimeSeeder);
        $this->assertStringContainsString('Seed overtime deadline task for {$username} ({$scenario[\'key\']}).', $overtimeSeeder);
        $this->assertStringContainsString('ProjectTask::query()->create', $overtimeSeeder);
        $this->assertStringContainsString("'overtime_id' => \$overtime->id,\n            'title' => self::TASK_TITLE", $overtimeSeeder);
        $this->assertStringContainsString("'overtime_id' => \$overtime->id,\n                'title' => \$taskTitle", $overtimeSeeder);
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
        $this->assertStringContainsString("'status' => 'in_progress'", $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'completed'", $overtimeSeeder);
        $this->assertStringContainsString("? 'pending' : 'completed'", $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'complete'", $overtimeSeeder);
        $this->assertStringContainsString("'status' => 'waiting'", $overtimeSeeder);
        $this->assertStringContainsString("'session_started' => 'clock_in'", $overtimeSeeder);
        $this->assertStringContainsString("'session_ended' => 'clock_out'", $overtimeSeeder);
        $this->assertStringContainsString("'task_hours_verification' => 'pending'", $overtimeSeeder);
        $this->assertStringContainsString("'task_hours_verification' => 'verified'", $overtimeSeeder);
        $this->assertStringContainsString("'payroll_processing' => 'calculated_locked'", $overtimeSeeder);
        $this->assertStringContainsString("'director_approval' => 'approved'", $overtimeSeeder);
        $this->assertStringContainsString("'key' => 'payment_distribution_upcoming'", $overtimeSeeder);
        $this->assertStringContainsString("'payment_disbursement' => 'upcoming'", $overtimeSeeder);
        $this->assertStringNotContainsString("'session_started' => 'upcoming'", $overtimeSeeder);
        $this->assertStringContainsString("'title' => 'Payment Distribution'", $overtimeSeeder);
        $this->assertStringContainsString("'blockers' => 'Waiting for final overtime deliverable update.'", $overtimeSeeder);
        $this->assertStringContainsString("'blockers' => null", $overtimeSeeder);
        $this->assertStringContainsString("'attachment_path' => null", $overtimeSeeder);
        $this->assertStringContainsString('? null', $overtimeSeeder);
        $this->assertStringContainsString("'completed_at' => \$deadline->copy()->setTime(16, 0)->addMinutes(\$taskIndex)->toDateTimeString()", $overtimeSeeder);
        $this->assertStringContainsString('private function resolveLifecycleHappenedAt', $overtimeSeeder);
        $this->assertStringContainsString('private function resolveLifecycleActorId', $overtimeSeeder);

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
        $this->assertSame(4, substr_count($overtimeSeeder, "'key' => '"));
    }
}
