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
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function test_administrator_sees_employee_list_from_all_companies(): void
    {
        $rnbCompany = Company::query()->create(['name' => 'RNB']);
        $otherCompany = Company::query()->create(['name' => 'ABG']);
        $operationsDepartment = $this->createDepartment('Operations');
        $staffPosition = Position::query()->create(['name' => 'Staff']);
        $administratorPosition = Position::query()->create(['name' => 'Administrator']);

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
        $response->assertSee('Other Company Staff');
        $response->assertSee('ABG');
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

    public function test_employee_search_filters_the_authorized_company_dataset(): void
    {
        $rnbCompany = Company::query()->create(['name' => 'RNB']);
        $otherCompany = Company::query()->create(['name' => 'ABG']);
        $operationsDepartment = $this->createDepartment('Operations');
        $staffPosition = Position::query()->create(['name' => 'Staff']);
        $administratorPosition = Position::query()->create(['name' => 'Administrator']);

        $administrator = $this->createEmployeeUser(
            name: 'RNB Administrator',
            username: 'rnb.admin',
            company: $rnbCompany,
            department: $operationsDepartment,
            position: $administratorPosition,
        );
        $matchingStaff = $this->createEmployeeUser(
            name: 'Target Employee',
            username: 'target.employee',
            company: $rnbCompany,
            department: $operationsDepartment,
            position: $staffPosition,
        );
        $nonMatchingStaff = $this->createEmployeeUser(
            name: 'Different Employee',
            username: 'different.employee',
            company: $rnbCompany,
            department: $operationsDepartment,
            position: $staffPosition,
        );
        $otherCompanyStaff = $this->createEmployeeUser(
            name: 'Target Other Company',
            username: 'target.other',
            company: $otherCompany,
            department: $operationsDepartment,
            position: $staffPosition,
        );

        foreach ([$administrator, $matchingStaff, $nonMatchingStaff, $otherCompanyStaff] as $user) {
            $this->assignRole($user, 'Staff');
        }
        $this->assignPositionPermission($administratorPosition, 'view-authorization');

        $this->actingAs($administrator)
            ->get(route('authorization', ['search' => 'Target']))
            ->assertOk()
            ->assertSee('Target Employee')
            ->assertDontSee('Different Employee')
            ->assertSee('Target Other Company')
            ->assertViewHas('search', 'Target');
    }

    public function test_employee_list_is_paginated_by_ten_records(): void
    {
        $rnbCompany = Company::query()->create(['name' => 'RNB']);
        $operationsDepartment = $this->createDepartment('Operations');
        $staffPosition = Position::query()->create(['name' => 'Staff']);
        $administratorPosition = Position::query()->create(['name' => 'Administrator']);
        $administrator = $this->createEmployeeUser(
            name: 'RNB Administrator',
            username: 'rnb.admin',
            company: $rnbCompany,
            department: $operationsDepartment,
            position: $administratorPosition,
        );
        $this->assignRole($administrator, 'Staff');
        $this->assignPositionPermission($administratorPosition, 'view-authorization');

        foreach (range(1, 11) as $index) {
            $staff = $this->createEmployeeUser(
                name: 'Employee '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                username: 'employee.'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                company: $rnbCompany,
                department: $operationsDepartment,
                position: $staffPosition,
            );
            $this->assignRole($staff, 'Staff');
        }

        $this->actingAs($administrator)
            ->get(route('authorization'))
            ->assertOk()
            ->assertViewHas('users', function (mixed $users): bool {
                return $users instanceof LengthAwarePaginator
                    && $users->perPage() === 10
                    && $users->total() === 12
                    && $users->count() === 10;
            });
    }

    public function test_coo_can_view_company_employee_list_and_manage_permissions(): void
    {
        $rnbCompany = Company::query()->create(['name' => 'RNB']);
        $otherCompany = Company::query()->create(['name' => 'ABG']);
        $operationsDepartment = $this->createDepartment('Operations');
        $cooPosition = Position::query()->create(['name' => 'Chief Operating Officer']);
        $staffPosition = Position::query()->create(['name' => 'Staff']);

        $coo = $this->createEmployeeUser(
            name: 'Lukman Prabowo',
            username: 'lukman',
            company: $rnbCompany,
            department: $operationsDepartment,
            position: $cooPosition,
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

        $this->assignRole($coo, 'Staff');
        $this->assignRole($rnbStaff, 'Staff');
        $this->assignRole($otherStaff, 'Staff');
        $authorizationPermission = $this->assignPositionPermission($cooPosition, 'view-authorization');

        $this->actingAs($coo)
            ->get(route('authorization'))
            ->assertOk()
            ->assertSee('Lukman Prabowo')
            ->assertSee('RNB Staff')
            ->assertDontSee('Other Company Staff')
            ->assertSee('Assign Permission');

        $this->actingAs($coo)
            ->get(route('authorization.access-menus'))
            ->assertOk();

        $this->actingAs($coo)
            ->post(route('authorization.position-permissions.update'), [
                'permission_positions' => [
                    (string) $authorizationPermission->uuid => [(string) $cooPosition->id],
                ],
            ])
            ->assertRedirect(route('authorization.access-menus'));
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

    private function assignPositionPermission(Position $position, string $permissionName): Permission
    {
        $permission = Permission::query()->create([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);

        $position->permissions()->sync([$permission->uuid]);

        return $permission;
    }
}
