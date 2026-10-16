<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDeployment>
 */
class EmployeeDeploymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'current_company_id' => Company::factory(),
            'join_date' => now('Asia/Jakarta')->subYear()->toDateString(),
            'status' => 'Active',
        ];
    }
}
