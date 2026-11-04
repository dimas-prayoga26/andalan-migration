<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSeederRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_only_active_application_roles(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'Staff', 'guard_name' => 'web']);
        $this->assertDatabaseMissing('roles', ['name' => 'superUser', 'guard_name' => 'web']);
        $this->assertDatabaseMissing('roles', ['name' => 'superuser', 'guard_name' => 'web']);
        $this->assertDatabaseMissing('roles', ['name' => 'Board of Directors', 'guard_name' => 'web']);
        $this->assertDatabaseMissing('roles', ['name' => 'admin', 'guard_name' => 'web']);
        $this->assertDatabaseMissing('roles', ['name' => 'Supervisor', 'guard_name' => 'web']);
    }
}
