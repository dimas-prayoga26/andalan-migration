<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyAsset;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeMedicalCheckup;
use App\Models\EmployeePicAssignment;
use App\Models\EmployeeProfile;
use App\Models\OfficeLocation;
use App\Models\Position;
use App\Models\RulesOfAttendace;
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

    public function test_work_schedule_follows_the_active_attendance_rule_of_the_current_office_location(): void
    {
        $fixedEmployee = $this->createEmployeeAtOfficeLocation('fixed.staff', $this->createOfficeLocation('Jogja'));
        RulesOfAttendace::query()->create($this->attendanceRuleAttributes($fixedEmployee->deployment->current_office_location_id, [
            'office_start_time' => '08:30:00',
            'office_end_time' => '17:30:00',
        ]));
        $flexibleEmployee = $this->createEmployeeAtOfficeLocation('flexible.staff', $this->createOfficeLocation('Jakarta'));
        RulesOfAttendace::query()->create($this->attendanceRuleAttributes($flexibleEmployee->deployment->current_office_location_id, [
            'attendance_type' => RulesOfAttendace::TYPE_FLEXIBLE,
        ]));
        $inactiveRuleEmployee = $this->createEmployeeAtOfficeLocation('inactive.rule.staff', $this->createOfficeLocation('Balikpapan'));
        RulesOfAttendace::query()->create($this->attendanceRuleAttributes($inactiveRuleEmployee->deployment->current_office_location_id, [
            'is_active' => false,
        ]));

        $resolver = app(EmployeeEmploymentDetailResolver::class);

        $this->assertSame('Fixed (08:30 – 17:30)', $resolver->forEmployee($fixedEmployee)['work_schedule']);
        $this->assertSame('Flexible', $resolver->forEmployee($flexibleEmployee)['work_schedule']);
        $this->assertNull($resolver->forEmployee($inactiveRuleEmployee)['work_schedule']);
    }

    public function test_it_resolves_compensation_medical_checkups_and_assets_held_by_the_employee(): void
    {
        $employee = $this->createEmployee('asset.holder', 'EMP-ASSET');
        $otherEmployee = $this->createEmployee('other.holder', 'EMP-OTHER');
        EmployeeCompensation::factory()->for($employee)->create([
            'base_salary' => '8500000',
            'allowance' => '1250000',
        ]);
        EmployeeMedicalCheckup::factory()->for($employee)->create([
            'checkup_date' => '2025-01-10',
            'clinic_name' => 'Klinik Pratama',
            'result_status' => EmployeeMedicalCheckup::RESULT_FIT,
        ]);
        EmployeeMedicalCheckup::factory()->for($employee)->create([
            'checkup_date' => '2026-02-15',
            'clinic_name' => 'RS Panti Rapih',
            'result_status' => EmployeeMedicalCheckup::RESULT_FIT_WITH_NOTE,
        ]);
        $company = Company::factory()->create();
        CompanyAsset::factory()->for($company)->assignedTo($employee)->create([
            'name' => 'Laptop',
            'specification' => 'Lenovo ThinkPad E14',
            'serial_number' => 'SN-001',
            'handover_date' => '2026-06-01',
        ]);
        CompanyAsset::factory()->for($company)->assignedTo($otherEmployee)->create(['name' => 'Monitor']);
        CompanyAsset::factory()->for($company)->create(['name' => 'Spare Laptop']);

        $details = app(EmployeeEmploymentDetailResolver::class)->forEmployee($employee);

        $this->assertSame('8.500.000', $details['base_salary']);
        $this->assertSame('1.250.000', $details['allowance']);
        $this->assertSame([
            ['date' => '15/02/2026', 'clinic' => 'RS Panti Rapih', 'status' => 'Fit with Note'],
            ['date' => '10/01/2025', 'clinic' => 'Klinik Pratama', 'status' => 'Fit'],
        ], $details['mcu_records']);
        $this->assertSame([
            ['name' => 'Laptop', 'specification' => 'Lenovo ThinkPad E14', 'serial_number' => 'SN-001', 'handover_date' => '01/06/2026'],
        ], $details['assets']);
    }

    public function test_missing_employee_returns_empty_details(): void
    {
        $details = app(EmployeeEmploymentDetailResolver::class)->forEmployee(null);

        $this->assertNull($details['company']);
        $this->assertNull($details['employment_status']);
        $this->assertSame([], $details['assets']);
    }

    private function createOfficeLocation(string $name): OfficeLocation
    {
        return OfficeLocation::query()->create([
            'name' => $name,
            'latitude' => -7.7956,
            'longitude' => 110.3695,
            'is_active' => true,
        ]);
    }

    private function createEmployeeAtOfficeLocation(string $username, OfficeLocation $officeLocation): Employee
    {
        $employee = $this->createEmployee($username, Str::upper(Str::random(8)));
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_office_location_id' => $officeLocation->id,
            'join_date' => '2025-01-01',
            'status' => 'Active',
        ]);

        return $employee->load('deployment');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function attendanceRuleAttributes(string $officeLocationId, array $overrides = []): array
    {
        return $overrides + [
            'office_location_id' => $officeLocationId,
            'ip_range' => '0.0.0.0/0',
            'radius' => 100,
            'office_start_time' => '08:00:00',
            'office_end_time' => '17:00:00',
            'attendance_type' => RulesOfAttendace::TYPE_FIXED,
            'is_active' => true,
        ];
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
