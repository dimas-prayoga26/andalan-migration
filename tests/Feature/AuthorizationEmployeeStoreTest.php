<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorizationEmployeeStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is not available.');
        }

        parent::setUp();
    }

    public function test_created_employee_user_is_assigned_staff_role(): void
    {
        $manager = $this->createAuthorizationManager();

        $response = $this->actingAs($manager)->post(route('authorization.store'), [
            'is_active' => '1',
            'name' => 'Muhamad Nur Shiddiq',
            'email' => 'mnshiddiq.01@example.test',
            'username' => 'muhamad',
        ]);

        $createdUser = User::query()
            ->where('username', 'muhamad')
            ->firstOrFail();
        $staffRole = Role::query()
            ->where('name', 'Staff')
            ->where('guard_name', 'web')
            ->firstOrFail();

        $response->assertRedirect(route('authorization.show', ['employee' => $createdUser->employee]));
        $this->assertTrue(Hash::check('password', $createdUser->password));
        $this->assertTrue($createdUser->hasRole('Staff'));
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $staffRole->uuid,
            'model_type' => User::class,
            'model_uuid' => $createdUser->id,
        ]);
    }

    private function createAuthorizationManager(): User
    {
        $manager = User::query()->create([
            'username' => 'superadmin',
            'email' => 'superadmin@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        Role::query()->firstOrCreate([
            'name' => 'superuser',
            'guard_name' => 'web',
        ]);

        $manager->assignRole('superuser');

        $employee = Employee::query()->create([
            'user_id' => $manager->id,
            'status' => 'Active',
        ]);

        EmployeeProfile::query()->create([
            'employee_id' => $employee->id,
            'name' => 'Super Administrator',
        ]);

        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'status' => 'Active',
        ]);

        return $manager;
    }
}
