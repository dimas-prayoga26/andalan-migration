<?php

namespace Database\Factories\Absensi;

use App\Models\Absensi\Lembur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lembur>
 */
class LemburFactory extends Factory
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
            'tanggal_lembur' => $this->faker->dateTimeBetween('2026-04-28', '2026-04-30')->format('Y-m-d'),
            'jam_mulai' => $this->faker->time(),
            'jam_selesai' => $this->faker->time(),
            'keterangan' => $this->faker->sentence(),
            'catatan' => $this->faker->sentence(),
            'status_persetujuan' => $this->faker->randomElement(['Disetujui', 'Ditolak']),
        ];
    }
}
