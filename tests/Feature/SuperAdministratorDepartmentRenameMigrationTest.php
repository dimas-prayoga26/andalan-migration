<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperAdministratorDepartmentRenameMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_department_seed_uses_super_administrator_name(): void
    {
        $this->assertDatabaseHas('departments', ['name' => 'Super Administrator']);
        $this->assertDatabaseMissing('departments', ['name' => 'Super User']);
        $this->assertDatabaseMissing('departments', ['name' => 'Superuser']);
    }

    public function test_legacy_super_user_departments_are_renamed_and_merged(): void
    {
        DB::table('departments')->where('name', 'Super Administrator')->delete();

        $superUserDepartmentId = $this->createDepartment('Super User');
        $superuserDepartmentId = $this->createDepartment('Superuser');
        $deploymentId = $this->createDeployment($superuserDepartmentId);

        $this->migration()->up();

        $this->assertSame(1, DB::table('departments')->where('name', 'Super Administrator')->count());
        $this->assertDatabaseHas('departments', ['id' => $superUserDepartmentId, 'name' => 'Super Administrator']);
        $this->assertDatabaseMissing('departments', ['id' => $superuserDepartmentId]);
        $this->assertDatabaseMissing('departments', ['name' => 'Super User']);
        $this->assertDatabaseHas('employee_deployments', [
            'id' => $deploymentId,
            'current_department_id' => $superUserDepartmentId,
        ]);
    }

    public function test_legacy_departments_are_merged_into_existing_super_administrator(): void
    {
        $superAdministratorDepartmentId = (string) DB::table('departments')->where('name', 'Super Administrator')->value('id');
        $superUserDepartmentId = $this->createDepartment('Super User');
        $deploymentId = $this->createDeployment($superUserDepartmentId);

        $this->migration()->up();

        $this->assertSame(1, DB::table('departments')->where('name', 'Super Administrator')->count());
        $this->assertDatabaseMissing('departments', ['id' => $superUserDepartmentId]);
        $this->assertDatabaseHas('employee_deployments', [
            'id' => $deploymentId,
            'current_department_id' => $superAdministratorDepartmentId,
        ]);
    }

    public function test_migration_runs_without_legacy_departments(): void
    {
        $this->migration()->up();

        $this->assertSame(1, DB::table('departments')->where('name', 'Super Administrator')->count());
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_10_02_064103_rename_super_user_department_to_super_administrator.php');
    }

    private function createDepartment(string $name): string
    {
        $departmentId = (string) Str::uuid();

        DB::table('departments')->insert([
            'id' => $departmentId,
            'name' => $name,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $departmentId;
    }

    private function createDeployment(string $departmentId): string
    {
        $employeeId = (string) Str::uuid();
        $deploymentId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'user_id' => User::query()->create([
                'username' => 'user_'.uniqid(),
                'email' => uniqid().'@example.test',
                'password' => 'password',
                'is_active' => true,
            ])->getKey(),
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employee_deployments')->insert([
            'id' => $deploymentId,
            'employee_id' => $employeeId,
            'current_department_id' => $departmentId,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $deploymentId;
    }
}
