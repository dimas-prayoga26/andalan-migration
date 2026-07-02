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
        $this->assertSame(['Supervisor'], $additionalPositions->invoke($seeder, [
            'email' => 'leonieputri7@gmail.com',
        ]));
        $this->assertTrue($isExcludedLegacyUser->invoke($seeder, [
            'email' => 'adik@andalanbersama.com',
            'name' => 'Adik Wiriyanto',
        ]));

        $legacySeeder = file_get_contents(database_path('seeders/LegacySqlUserSeeder.php'));

        $this->assertIsString($legacySeeder);
        $this->assertStringContainsString('$this->seedExplicitRnbUsers();', $legacySeeder);
        $this->assertStringContainsString("'name' => 'Rully Priyatno'", $legacySeeder);
        $this->assertStringContainsString("'name' => 'Hilmi Ulwan'", $legacySeeder);
        $this->assertStringContainsString("->where('is_primary', false)", $legacySeeder);
        $this->assertStringContainsString("->whereNotIn('position_id', \$positionIds->all())", $legacySeeder);
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
