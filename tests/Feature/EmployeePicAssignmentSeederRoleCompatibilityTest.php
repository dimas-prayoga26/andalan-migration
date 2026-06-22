<?php

namespace Tests\Feature;

use Tests\TestCase;

class EmployeePicAssignmentSeederRoleCompatibilityTest extends TestCase
{
    public function test_employee_pic_assignment_seeder_uses_seeded_pic_users_instead_of_removed_supervisor_role(): void
    {
        $seeder = file_get_contents(database_path('seeders/EmployeePicAssignmentSeeder.php'));

        $this->assertIsString($seeder);
        $this->assertStringContainsString("->where('username', 'like', 'supervisor%')", $seeder);
        $this->assertStringNotContainsString("whereRaw('LOWER(name) = ?', ['supervisor'])", $seeder);
    }
}
