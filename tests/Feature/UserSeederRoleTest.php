<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSeederRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_admin_role(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
    }
}
