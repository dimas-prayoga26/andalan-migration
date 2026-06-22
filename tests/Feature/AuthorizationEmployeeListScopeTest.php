<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeProfile;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthorizationEmployeeListScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is not available.');
        }

        parent::setUp();
    }

    public function test_rnb_administrator_only_sees_rnb_employee_list(): void
    {
        $rnbCompany = Company::query()->create(['name' => 'RNB']);
        $otherCompany = Company::query()->create(['name' => 'ABG']);
        $operationsDepartment = $this->createDepartment('Operations');
        $staffPosition = Position::query()->create(['name' => 'Staff']);
        $administratorPosition = Position::query()->create(['name' => 'System Administrator']);

        $rnbAdministrator = $this->createEmployeeUser(
            name: 'RNB Administrator',
            username: 'rnb.admin',
            company: $rnbCompany,
            department: $operationsDepartment,
            position: $administratorPosition,
        );
        $rnbStaff = $this->createEmployeeUser(
            name: 'RNB Staff',
            username: 'rnb.staff',
            company: $rnbCompany,
            department: $operationsDepartment,
            position: $staffPosition,
        );
        $otherStaff = $this->createEmployeeUser(
            name: 'Other Company Staff',
            username: 'other.staff',
            company: $otherCompany,
            department: $operationsDepartment,
            position: $staffPosition,
        );

        $this->assignRole($rnbAdministrator, 'Staff');
        $this->assignRole($rnbStaff, 'Staff');
        $this->assignRole($otherStaff, 'Staff');
        $this->assignPositionPermission($administratorPosition, 'view-authorization');

        $response = $this->actingAs($rnbAdministrator)->get(route('authorization'));

        $response->assertOk();
        $response->assertSee('RNB Administrator');
        $response->assertSee('RNB Staff');
        $response->assertSee('RNB');
        $response->assertDontSee('Other Company Staff');
        $response->assertDontSee('ABG');
    }

    public function test_staff_without_authorization_permission_cannot_open_authorization_url(): void
    {
        $rnbCompany = Company::query()->create(['name' => 'RNB']);
        $operationsDepartment = $this->createDepartment('Operations');
        $staffPosition = Position::query()->create(['name' => 'Staff']);
        $staff = $this->createEmployeeUser(
            name: 'RNB Staff',
            username: 'rnb.staff',
            company: $rnbCompany,
            department: $operationsDepartment,
            position: $staffPosition,
        );

        $this->assignRole($staff, 'Staff');

        $response = $this->actingAs($staff)->get(route('authorization'));

        $response->assertForbidden();
    }

    public function test_superuser_sees_employee_list_from_all_companies(): void
    {
        $rnbCompany = Company::query()->create(['name' => 'RNB']);
        $otherCompany = Company::query()->create(['name' => 'ABG']);
        $superuserDepartment = $this->createDepartment('Superuser');
        $operationsDepartment = $this->createDepartment('Operations');
        $staffPosition = Position::query()->create(['name' => 'Staff']);

        $superuser = $this->createEmployeeUser(
            name: 'Main Superuser',
            username: 'main.superuser',
            company: $rnbCompany,
            department: $superuserDepartment,
            position: $staffPosition,
        );
        $rnbStaff = $this->createEmployeeUser(
            name: 'RNB Staff',
            username: 'rnb.staff',
            company: $rnbCompany,
            department: $operationsDepartment,
            position: $staffPosition,
        );
        $otherStaff = $this->createEmployeeUser(
            name: 'Other Company Staff',
            username: 'other.staff',
            company: $otherCompany,
            department: $operationsDepartment,
            position: $staffPosition,
        );

        $this->assignRole($superuser, 'superuser');
        $this->assignRole($rnbStaff, 'Staff');
        $this->assignRole($otherStaff, 'Staff');

        $response = $this->actingAs($superuser)->get(route('authorization'));

        $response->assertOk();
        $response->assertSee('Main Superuser');
        $response->assertSee('RNB Staff');
        $response->assertSee('Other Company Staff');
        $response->assertSee('RNB');
        $response->assertSee('ABG');
    }

    private function createDepartment(string $name): Department
    {
        return Department::query()->create([
            'id' => (string) Str::uuid(),
            'name' => $name,
            'status' => 'active',
        ]);
    }

    private function createEmployeeUser(
        string $name,
        string $username,
        Company $company,
        Department $department,
        Position $position,
    ): User {
        $user = User::query()->create([
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);

        EmployeeProfile::query()->create([
            'employee_id' => $employee->id,
            'name' => $name,
        ]);

        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => $company->id,
            'current_department_id' => $department->id,
            'current_position_id' => $position->id,
            'status' => 'Active',
        ]);

        return $user;
    }

    private function assignRole(User $user, string $roleName): void
    {
        $role = Role::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $user->assignRole($role);
    }

    private function assignPositionPermission(Position $position, string $permissionName): void
    {
        $permission = Permission::query()->create([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);

        $position->permissions()->sync([$permission->uuid]);
    }
}
