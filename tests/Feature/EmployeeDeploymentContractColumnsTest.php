<?php

namespace Tests\Feature;

use App\Models\EmployeeDeployment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeDeploymentContractColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_deployments_table_has_contract_and_probation_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('employee_deployments', [
            'is_contract',
            'contract_end_date',
            'probation_end_date',
        ]));
    }

    public function test_new_deployment_defaults_to_permanent_without_contract_or_probation_dates(): void
    {
        $employeeDeployment = EmployeeDeployment::factory()->create()->fresh();

        $this->assertFalse($employeeDeployment->is_contract);
        $this->assertNull($employeeDeployment->contract_end_date);
        $this->assertNull($employeeDeployment->probation_end_date);
        $this->assertFalse($employeeDeployment->isOnProbation());
    }

    public function test_contract_deployment_stores_contract_end_date(): void
    {
        $employeeDeployment = EmployeeDeployment::factory()->create([
            'join_date' => '2026-06-01',
            'is_contract' => true,
            'contract_end_date' => '2026-08-01',
        ])->fresh();

        $this->assertTrue($employeeDeployment->is_contract);
        $this->assertSame('2026-08-01', $employeeDeployment->contract_end_date->toDateString());
    }

    public function test_permanent_employee_is_on_probation_until_probation_end_date(): void
    {
        $employeeDeployment = EmployeeDeployment::factory()->create([
            'join_date' => '2026-06-01',
            'probation_end_date' => '2026-09-01',
        ])->fresh();

        $this->assertTrue($employeeDeployment->isOnProbation(Carbon::parse('2026-07-15')));
        $this->assertTrue($employeeDeployment->isOnProbation(Carbon::parse('2026-09-01 23:00')));
        $this->assertFalse($employeeDeployment->isOnProbation(Carbon::parse('2026-09-02')));
    }

    public function test_contract_employee_is_never_on_probation(): void
    {
        $employeeDeployment = EmployeeDeployment::factory()->create([
            'is_contract' => true,
            'contract_end_date' => '2026-12-31',
            'probation_end_date' => '2026-09-01',
        ])->fresh();

        $this->assertFalse($employeeDeployment->isOnProbation(Carbon::parse('2026-07-15')));
    }
}
