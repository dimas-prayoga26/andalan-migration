<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeePicAssignment;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\User;
use App\Support\Employee\EmployeeEmploymentDetailResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeEmploymentDetailResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_placement_and_contract_values_for_a_contract_employee(): void
    {
        $employee = $this->createEmployee('contract.staff', 'EMP-011830');
        $supervisor = $this->createEmployee('supervisor.staff', 'EMP-SPV');
        EmployeeProfile::query()->create(['employee_id' => $supervisor->id, 'name' => 'Erlin Supervisor']);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => Company::query()->create(['name' => 'RNB Management'])->id,
            'current_department_id' => Department::query()->create(['id' => (string) Str::uuid(), 'name' => 'Information Technology', 'status' => 'active'])->id,
            'current_position_id' => Position::query()->create(['name' => 'Web Developer'])->id,
            'workplace' => 'RNB Jogja',
            'join_date' => '2026-06-01',
            'is_contract' => true,
            'contract_end_date' => '2027-05-31',
            'status' => 'Active',
        ]);
        EmployeePicAssignment::query()->create([
            'staff_employee_id' => $employee->id,
            'supervisor_employee_id' => $supervisor->id,
            'is_active' => true,
        ]);

        $details = app(EmployeeEmploymentDetailResolver::class)->forEmployee($employee);

        $this->assertSame('RNB Management', $details['company']);
        $this->assertSame('EMP-011830', $details['employee_code']);
        $this->assertSame('Information Technology', $details['department']);
        $this->assertSame('Web Developer', $details['position']);
        $this->assertSame('Erlin Supervisor', $details['supervisor']);
        $this->assertSame('RNB Jogja', $details['work_location']);
        $this->assertSame('PKWT', $details['employment_status']);
        $this->assertSame('01/06/2026', $details['join_date']);
        $this->assertSame('31/05/2027', $details['contract_end_date']);
        $this->assertNull($details['work_schedule']);
        $this->assertNull($details['base_salary']);
        $this->assertSame([], $details['mcu_records']);
        $this->assertSame([], $details['assets']);
    }

    public function test_permanent_employee_is_probation_until_probation_end_date_and_has_no_contract_end(): void
    {
        $probationEmployee = $this->createEmployee('probation.staff', 'EMP-PROBATION');
        EmployeeDeployment::query()->create([
            'employee_id' => $probationEmployee->id,
            'join_date' => now()->subMonth()->toDateString(),
            'probation_end_date' => now()->addMonth()->toDateString(),
            'contract_end_date' => '2027-01-01',
            'status' => 'Active',
        ]);
        $permanentEmployee = $this->createEmployee('permanent.staff', 'EMP-PERMANENT');
        EmployeeDeployment::query()->create([
            'employee_id' => $permanentEmployee->id,
            'join_date' => '2024-01-01',
            'status' => 'Active',
        ]);

        $resolver = app(EmployeeEmploymentDetailResolver::class);

        $this->assertSame('Probation', $resolver->forEmployee($probationEmployee)['employment_status']);
        $this->assertNull($resolver->forEmployee($probationEmployee)['contract_end_date']);
        $this->assertSame('PKWTT', $resolver->forEmployee($permanentEmployee)['employment_status']);
    }

    public function test_missing_employee_returns_empty_details(): void
    {
        $details = app(EmployeeEmploymentDetailResolver::class)->forEmployee(null);

        $this->assertNull($details['company']);
        $this->assertNull($details['employment_status']);
        $this->assertSame([], $details['assets']);
    }

    private function createEmployee(string $username, string $employeeCode): Employee
    {
        $user = User::query()->create([
            'email' => $username.'@example.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        return Employee::query()->create([
            'user_id' => $user->id,
            'employee_code' => $employeeCode,
            'status' => 'Active',
        ]);
    }
}
