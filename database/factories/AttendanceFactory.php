<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->dateTimeBetween('2026-04-20', '2026-04-23')->format('Y-m-d'),
            'check_in' => $this->faker->time(),
            'check_out' => $this->faker->time(),
            'status' => $this->faker->randomElement(['Masuk', 'Terlambat']),
        ];
    }
}
