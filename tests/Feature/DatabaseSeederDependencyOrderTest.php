<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseSeederDependencyOrderTest extends TestCase
{
    public function test_company_and_position_seeders_run_before_legacy_user_seeder(): void
    {
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));

        $this->assertStringContainsString('CompanySeeder::class', $databaseSeeder);
        $this->assertStringContainsString('PositionSeeder::class', $databaseSeeder);
        $this->assertStringContainsString('LegacySqlUserSeeder::class', $databaseSeeder);
        $this->assertStringNotContainsString('            UserSeeder::class,', $databaseSeeder);
        $this->assertLessThan(
            strpos($databaseSeeder, 'LegacySqlUserSeeder::class'),
            strpos($databaseSeeder, 'CompanySeeder::class'),
        );
        $this->assertLessThan(
            strpos($databaseSeeder, 'LegacySqlUserSeeder::class'),
            strpos($databaseSeeder, 'PositionSeeder::class'),
        );
    }
}
