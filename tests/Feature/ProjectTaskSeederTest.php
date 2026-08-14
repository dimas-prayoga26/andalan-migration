<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProjectTaskSeederTest extends TestCase
{
    public function test_project_task_seeder_is_removed_from_database_seed_flow(): void
    {
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));

        $this->assertFileDoesNotExist(database_path('seeders/ProjectTaskSeeder.php'));
        $this->assertStringNotContainsString('ProjectTaskSeeder::class', $databaseSeeder);
    }
}
