<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeFamily;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeFamily>
 */
class EmployeeFamilyFactory extends Factory
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
            'name' => fake()->name(),
            'relationship' => fake()->randomElement(['Suami', 'Istri', 'Anak', 'Ayah', 'Ibu']),
            'gender' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'place_of_birth' => fake()->city(),
            'date_of_birth' => fake()->date(),
            'occupation' => fake()->jobTitle(),
            'phone_number' => fake()->numerify('08##########'),
            'is_dependents' => false,
            'is_emergency_contact' => false,
        ];
    }

    /**
     * Indicate that the family member is the emergency contact.
     */
    public function emergencyContact(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_emergency_contact' => true,
        ]);
    }
}
