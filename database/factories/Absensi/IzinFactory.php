<?php

namespace Database\Factories\Absensi;

use App\Models\Absensi\Izin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Izin>
 */
class IzinFactory extends Factory
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
            'tanggal_awal' => $this->faker->dateTimeBetween('2026-04-23', '2026-04-25')->format('Y-m-d'),
            'tanggal_akhir' => $this->faker->dateTimeBetween('2026-04-25', '2026-04-27')->format('Y-m-d'),
            'alasan' => $this->faker->sentence(),
            'status_persetujuan' => $this->faker->randomElement(['Disetujui', 'Ditolak']),
        ];
    }
}
