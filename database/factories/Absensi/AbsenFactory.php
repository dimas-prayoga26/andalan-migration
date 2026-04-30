<?php

namespace Database\Factories\Absensi;

use App\Models\Absensi\Absen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Absen>
 */
class AbsenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_user' => 1,
            'domisili' => $this->faker->randomElement(['Yogyakarta', 'Jakarta']),
            'tanggal' => $this->faker->dateTimeBetween('2026-04-20', '2026-04-23')->format('Y-m-d'),
            'jam_masuk' => $this->faker->time(),
            'jam_keluar' => $this->faker->time(),
            'status' => $this->faker->randomElement(['Terlambat', 'Hadir']),
        ];
    }
}
