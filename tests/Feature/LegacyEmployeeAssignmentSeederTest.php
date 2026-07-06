<?php

namespace Tests\Feature;

use Database\Seeders\LegacySqlUserSeeder;
use ReflectionMethod;
use Tests\TestCase;

class LegacyEmployeeAssignmentSeederTest extends TestCase
{
    public function test_legacy_user_position_configuration_is_registered(): void
    {
        $seeder = new LegacySqlUserSeeder;
        $additionalPositions = new ReflectionMethod($seeder, 'additionalPositionNamesForLegacyUser');
        $isExcludedLegacyUser = new ReflectionMethod($seeder, 'isExcludedLegacyUser');

        $this->assertSame(['Supervisor'], $additionalPositions->invoke($seeder, [
            'email' => 'lukman@rnbmanagement.com',
        ]));
        $this->assertSame(['Administrator', 'Supervisor'], $additionalPositions->invoke($seeder, [
            'email' => 'leonieputri7@gmail.com',
        ]));
        $this->assertTrue($isExcludedLegacyUser->invoke($seeder, [
            'email' => 'adik@andalanbersama.com',
            'name' => 'Adik Wiriyanto',
        ]));

        $legacySeeder = file_get_contents(database_path('seeders/LegacySqlUserSeeder.php'));

        $this->assertIsString($legacySeeder);
        $this->assertStringContainsString('$this->seedExplicitRnbUsers();', $legacySeeder);
        $this->assertStringContainsString('$this->syncRnbJakartaOfficeAssignments();', $legacySeeder);
        $this->assertStringContainsString('$this->syncLatestStaffDeployments();', $legacySeeder);
        $this->assertStringContainsString('$this->deactivateRemovedLegacyStaff();', $legacySeeder);
        $this->assertStringContainsString('RNB_JAKARTA_OFFICE', $legacySeeder);
        $this->assertStringContainsString('LATEST_STAFF_DEPLOYMENTS', $legacySeeder);
        $this->assertStringContainsString('DEACTIVATED_LEGACY_STAFF_EMAILS', $legacySeeder);
        $this->assertStringContainsString('Jl. Bhineka Blok Bhineka No.26', $legacySeeder);
        $this->assertStringContainsString("'latitude' => -6.3636699", $legacySeeder);
        $this->assertStringContainsString("'longitude' => 106.8016359", $legacySeeder);
        $this->assertStringContainsString("'lukman@rnbmanagement.com'", $legacySeeder);
        $this->assertStringContainsString("'name' => 'Rully Priyatno'", $legacySeeder);
        $this->assertStringContainsString("'name' => 'Hilmi Ulwan'", $legacySeeder);
        $this->assertStringContainsString("'workplace' => 'RNB Branch Jakarta'", $legacySeeder);
        $this->assertStringContainsString("'workplace' => 'RNB Branch Jogja'", $legacySeeder);
        $this->assertStringContainsString("'company' => 'Niskala'", $legacySeeder);
        $this->assertStringContainsString("'company' => 'RNE'", $legacySeeder);
        $this->assertStringContainsString("'company' => 'TMS'", $legacySeeder);
        $this->assertStringContainsString("'airarizqi22@gmail.com'", $legacySeeder);
        $this->assertStringContainsString("'workplace' => 'RNB Jakarta'", $legacySeeder);
        $this->assertStringContainsString("->where('is_primary', false)", $legacySeeder);
        $this->assertStringContainsString("->whereNotIn('position_id', \$positionIds->all())", $legacySeeder);
        $this->assertStringContainsString("->orderBy('created_at')", $legacySeeder);
    }

    public function test_self_and_lukman_pic_assignments_are_registered(): void
    {
        $picSeeder = file_get_contents(database_path('seeders/EmployeePicAssignmentSeeder.php'));

        $this->assertIsString($picSeeder);

        foreach ([
            'lukman@rnbmanagement.com',
            'leonieputri7@gmail.com',
            'fahmil@andalanbersama.com',
            'fuadmfahrudin@gmail.com',
            'rexy@andalanbersama.com',
            'msyafiq.dev@gmail.com',
        ] as $email) {
            $this->assertSame(2, substr_count($picSeeder, "'{$email}'"));
        }

        $this->assertStringContainsString("'rully.priyatno@andalanbersama.com'", $picSeeder);
        $this->assertStringContainsString("'hilmi.ulwan@andalanbersama.com'", $picSeeder);
    }
}
