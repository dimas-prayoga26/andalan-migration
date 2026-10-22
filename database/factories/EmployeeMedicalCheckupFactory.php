<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeMedicalCheckup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeMedicalCheckup>
 */
class EmployeeMedicalCheckupFactory extends Factory
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
            'checkup_date' => fake()->dateTimeBetween('-2 years')->format('Y-m-d'),
            'clinic_name' => 'Klinik '.fake()->lastName(),
            'result_status' => EmployeeMedicalCheckup::RESULT_FIT,
        ];
    }
}
