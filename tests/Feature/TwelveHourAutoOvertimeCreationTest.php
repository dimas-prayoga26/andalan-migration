<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TwelveHourAutoOvertimeCreationTest extends TestCase
{
    public function test_auto_overtime_resolves_active_pic_supervisor_before_staff_actor(): void
    {
        $service = File::get(app_path('Services/Attendance/TwelveHourAutoOvertimeService.php'));

        $this->assertStringContainsString("'employee.picAssignment.supervisor.user'", $service);
        $this->assertStringContainsString(
            '$actorUser = $this->resolveSupervisorUser($employee) ?? ($actor instanceof User ? $actor : $employee->user);',
            $service
        );
        $this->assertStringContainsString('private function resolveSupervisorUser(Employee $employee): ?User', $service);
        $this->assertStringContainsString('$supervisorUser = $employee->picAssignment?->supervisor?->user;', $service);
    }
}
