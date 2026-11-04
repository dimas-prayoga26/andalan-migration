<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeCompensation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeCompensation>
 */
class EmployeeCompensationFactory extends Factory
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
            'base_salary' => (string) (fake()->numberBetween(5, 20) * 1_000_000),
            'allowance' => (string) (fake()->numberBetween(1, 5) * 500_000),
            'effective_date' => now()->startOfYear()->toDateString(),
        ];
    }
}
