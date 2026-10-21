<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeProfile;
use App\Models\Permission;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthorizationEmployeeListTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_list_table_follows_employee_database_template(): void
    {
        $company = Company::query()->create(['name' => 'RNB Management']);
        $department = $this->createDepartment('Information and Comunication Technology');
        $administratorPosition = Position::query()->create(['name' => 'Administrator']);
        $webPosition = Position::query()->create(['name' => 'IT Web Development']);
        $administrator = $this->createEmployeeUser('Admin Viewer', 'admin.viewer', $company, $department, $administratorPosition);
        $this->assignPositionPermission($administratorPosition, 'view-authorization');
        $probationEndDate = now()->addMonth();

        $contractStaff = $this->createEmployeeUser('Contract Staff', 'contract.staff', $company, $department, $webPosition, [
            'join_date' => '2026-06-01',
            'is_contract' => true,
            'contract_end_date' => '2026-08-01',
        ]);
        $this->createEmployeeUser('Indefinite Staff', 'indefinite.staff', $company, $department, $webPosition, [
            'join_date' => '2025-01-15',
        ]);
        $this->createEmployeeUser('Probation Staff', 'probation.staff', $company, $department, $webPosition, [
            'join_date' => now()->subMonth()->toDateString(),
            'probation_end_date' => $probationEndDate->toDateString(),
        ]);
        $this->createEmployeeUser('Undated Contract Staff', 'undated.staff', $company, $department, $webPosition, [
            'is_contract' => true,
        ]);

        $response = $this->actingAs($administrator)->get(route('authorization', ['search' => 'Staff']));

        $response->assertOk();
        $response->assertSee('<a href="'.route('authorization.create').'" class="btn light btn-success btn-sm">', false);
        $response->assertSeeInOrder([
            '<th class="mw-10">No</th>',
            '<th class="mw-80">Name</th>',
            '<th class="mw-80">Position</th>',
            '<th class="mw-80">Company</th>',
            '<th class="mw-80">Contract</th>',
            '<th class="mw-100">Action</th>',
            '<td>1</td>',
            route('authorization.show', ['employee' => $contractStaff->employee]),
            'Contract Staff',
            'IT Web Development',
            'Information and Comunication Technology',
            'RNB Management',
            '01 Jun 2026',
            '01 Aug 2026',
            'Options',
            'View More',
            route('authorization.edit', ['employee' => $contractStaff->employee]),
            'Delete</button>',
            '<td>2</td>',
            'Indefinite Staff',
            '15 Jan 2025',
            'No End Date',
            '<td>3</td>',
            'Probation Staff',
            'Probation until '.$probationEndDate->format('d M Y'),
            '<td>4</td>',
            'Undated Contract Staff',
            '<span>-</span> <br>',
            '<span>-</span>',
        ], false);
        $response->assertDontSee('<th>NIK</th>', false);
        $response->assertDontSee('<th>PIC</th>', false);
        $response->assertDontSee('<th>Status</th>', false);
        $response->assertDontSee('Permanent');
    }

    public function test_inactive_tab_shows_working_period_until_resignation_date(): void
    {
        $company = Company::query()->create(['name' => 'RNB Management']);
        $department = $this->createDepartment('Operations');
        $administratorPosition = Position::query()->create(['name' => 'Administrator']);
        $staffPosition = Position::query()->create(['name' => 'Staff']);
        $administrator = $this->createEmployeeUser('Admin Viewer', 'admin.viewer', $company, $department, $administratorPosition);
        $this->assignPositionPermission($administratorPosition, 'view-authorization');
        $this->createEmployeeUser('Resigned Staff', 'resigned.staff', $company, $department, $staffPosition, [
            'join_date' => '2023-05-24',
            'resignation_date' => '2023-11-26',
            'status' => 'Inactive',
        ]);
        $this->createEmployeeUser('Undated Inactive Staff', 'undated.inactive', $company, $department, $staffPosition, [
            'join_date' => '2024-02-01',
            'status' => 'Inactive',
        ]);

        $response = $this->actingAs($administrator)->get(route('authorization', ['status' => 'inactive']));

        $response->assertOk();
        $response->assertSeeInOrder([
            '<th class="mw-80">Working Period</th>',
            'Resigned Staff',
            '24 May 2023',
            '26 Nov 2023',
            'Undated Inactive Staff',
            '01 Feb 2024',
            '<span>-</span>',
        ], false);
        $response->assertDontSee('<th class="mw-80">Contract</th>', false);
        $response->assertDontSee('No End Date');
        $response->assertViewHas('users', fn (LengthAwarePaginator $users): bool => collect($users->items())->pluck('name')->all() === [
            'Resigned Staff',
            'Undated Inactive Staff',
        ]);
    }

    public function test_viewer_without_manage_access_only_gets_view_more_option(): void
    {
        $company = Company::query()->create(['name' => 'RNB Management']);
        $department = $this->createDepartment('Operations');
        $viewerPosition = Position::query()->create(['name' => 'Staff']);
        $viewer = $this->createEmployeeUser('Company Viewer', 'company.viewer', $company, $department, $viewerPosition);
        $this->assignPositionPermission($viewerPosition, 'view-authorization');
        $staff = $this->createEmployeeUser('Listed Staff', 'listed.staff', $company, $department, $viewerPosition);

        $response = $this->actingAs($viewer)->get(route('authorization', ['search' => 'Listed']));

        $response->assertOk();
        $response->assertSee('Listed Staff');
        $response->assertSee('View More');
        $response->assertDontSee(route('authorization.edit', ['employee' => $staff->employee]), false);
        $response->assertDontSee('name="_method" value="DELETE"', false);
    }

    private function createDepartment(string $name): Department
    {
        return Department::query()->create([
            'id' => (string) Str::uuid(),
            'name' => $name,
            'status' => 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $deploymentAttributes
     */
    private function createEmployeeUser(
        string $name,
        string $username,
        Company $company,
        Department $department,
        Position $position,
        array $deploymentAttributes = [],
    ): User {
        $user = User::query()->create([
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
        EmployeeDeployment::query()->create($deploymentAttributes + [
            'employee_id' => $employee->id,
            'current_company_id' => $company->id,
            'current_department_id' => $department->id,
            'current_position_id' => $position->id,
            'status' => 'Active',
        ]);

        return $user->load('employee');
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
