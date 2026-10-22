<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyAsset;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyAsset>
 */
class CompanyAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Laptop', 'Monitor', 'Handphone', 'ID Card']),
            'specification' => fake()->words(3, true),
            'serial_number' => fake()->bothify('SN-####-????'),
            'status' => CompanyAsset::STATUS_AVAILABLE,
        ];
    }

    /**
     * Indicate that the asset has been handed over to an employee.
     */
    public function assignedTo(Employee $employee): static
    {
        return $this->state(fn (array $attributes): array => [
            'employee_id' => $employee->id,
            'handover_date' => now()->subMonth()->toDateString(),
            'status' => CompanyAsset::STATUS_ASSIGNED,
        ]);
    }
}
