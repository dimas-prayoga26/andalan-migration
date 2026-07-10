<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AuthorizationSelfPicAssignmentTest extends TestCase
{
    public function test_employee_is_available_as_their_own_pic_option(): void
    {
        $controller = File::get(app_path('Http/Controllers/AuthorizationController.php'));
        $optionsMethod = $this->methodContents(
            $controller,
            'private function dataEmployeeFormOptions',
            'private function validatedDataEmployee',
        );

        $this->assertStringContainsString("'picEmployees' => Employee::query()", $optionsMethod);
        $this->assertStringNotContainsString('whereKeyNot($employee->id)', $optionsMethod);
    }

    public function test_self_pic_assignment_is_not_rejected_during_sync(): void
    {
        $controller = File::get(app_path('Http/Controllers/AuthorizationController.php'));
        $syncMethod = $this->methodContents(
            $controller,
            'private function syncPicAssignment',
            'private function initials',
        );

        $this->assertStringContainsString("if (\$picEmployeeId === '')", $syncMethod);
        $this->assertStringNotContainsString('$picEmployeeId === $employee->id', $syncMethod);
        $this->assertStringContainsString("'supervisor_employee_id' => \$picEmployeeId", $syncMethod);
        $this->assertStringContainsString("'staff_employee_id' => \$employee->id", $syncMethod);
    }

    public function test_edit_form_selects_the_active_pic_assignment(): void
    {
        $view = File::get(resource_path('views/authorization/form.blade.php'));

        $this->assertStringContainsString("old('pic_employee_id', \$employee?->picAssignment?->supervisor_employee_id)", $view);
        $this->assertStringContainsString('=== $picEmployee->id', $view);
    }

    private function methodContents(string $source, string $startMarker, string $endMarker): string
    {
        $start = strpos($source, $startMarker);
        $end = strpos($source, $endMarker, $start === false ? 0 : $start);

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        return substr($source, $start, $end - $start);
    }
}
