<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSeederRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is not available.');
        }

        parent::setUp();
    }

    public function test_user_seeder_creates_admin_role(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
    }
}
