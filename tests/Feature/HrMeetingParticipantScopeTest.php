<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HrMeetingParticipantScopeTest extends TestCase
{
    public function test_all_staff_hr_meeting_participants_exclude_non_core_staff(): void
    {
        $controller = File::get(app_path('Http/Controllers/HrMeetingController.php'));
        $notificationService = File::get(app_path('Services/HrMeetingNotificationService.php'));
        $employeeModel = File::get(app_path('Models/Employee.php'));
        $schemaMigration = File::get(database_path('migrations/2026_10_01_143740_add_is_core_staff_to_employees_table.php'));
        $backfillMigration = File::get(database_path('migrations/2026_10_01_143741_backfill_non_core_hr_meeting_staff.php'));

        $this->assertStringContainsString("->where('is_core_staff', true)", $controller);
        $this->assertStringContainsString('if (! (bool) $employee->is_core_staff)', $controller);
        $this->assertStringContainsString('$employee instanceof Employee && (bool) $employee->is_core_staff', $controller);
        $this->assertStringContainsString('return $this->staffEmployeeQuery()', $controller);
        $this->assertStringNotContainsString('EXCLUDED_HR_MEETING_STAFF_NAME_PATTERNS', $controller);

        $this->assertStringContainsString("->where('is_core_staff', true)", $notificationService);
        $this->assertStringContainsString("'is_core_staff' => false", $employeeModel);
        $this->assertStringContainsString("'is_core_staff' => 'boolean'", $employeeModel);

        $this->assertStringContainsString("boolean('is_core_staff')", $schemaMigration);
        $this->assertStringContainsString('->default(false)', $schemaMigration);
        $this->assertStringContainsString("->index('employees_core_staff_index')", $schemaMigration);

        $this->assertStringContainsString("'is_core_staff' => true", $backfillMigration);
        $this->assertStringContainsString('NON_CORE_STAFF_NAME_PATTERNS', $backfillMigration);
        $this->assertStringContainsString("'arya widi nugroho'", $backfillMigration);
        $this->assertStringContainsString("'hilmi ulwan'", $backfillMigration);
        $this->assertStringContainsString("'yusuf eriansyah'", $backfillMigration);
        $this->assertStringContainsString("'yusuf heriansyah'", $backfillMigration);
        $this->assertStringContainsString("'yusurf heriansyah'", $backfillMigration);
        $this->assertStringContainsString("'yususrf heriansyah'", $backfillMigration);
        $this->assertStringContainsString("'abi rikhardo'", $backfillMigration);
        $this->assertStringContainsString("'dimas rafi putra'", $backfillMigration);
        $this->assertStringContainsString("'is_core_staff' => \$isCoreStaff", $backfillMigration);
    }
}
