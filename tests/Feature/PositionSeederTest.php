<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PositionSeederTest extends TestCase
{
    public function test_position_seeders_include_supervisor_position(): void
    {
        $positionSeeder = File::get(database_path('seeders/PositionSeeder.php'));
        $metaDataPositionSeeder = File::get(database_path('seeders/MetaDataPositionSeeder.php'));

        $this->assertStringContainsString("'Supervisor'", $positionSeeder);
        $this->assertStringContainsString("'Supervisor'", $metaDataPositionSeeder);
    }
}
