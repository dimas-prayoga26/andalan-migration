<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseSeederDependencyOrderTest extends TestCase
{
    public function test_company_and_position_seeders_run_before_user_seeder(): void
    {
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));

        $this->assertStringContainsString('CompanySeeder::class', $databaseSeeder);
        $this->assertStringContainsString('PositionSeeder::class', $databaseSeeder);
        $this->assertStringContainsString('UserSeeder::class', $databaseSeeder);
        $this->assertLessThan(
            strpos($databaseSeeder, 'UserSeeder::class'),
            strpos($databaseSeeder, 'CompanySeeder::class'),
        );
        $this->assertLessThan(
            strpos($databaseSeeder, 'UserSeeder::class'),
            strpos($databaseSeeder, 'PositionSeeder::class'),
        );
    }
}
