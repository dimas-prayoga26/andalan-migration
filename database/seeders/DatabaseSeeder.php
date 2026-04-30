<?php

namespace Database\Seeders;

use App\Models\Absensi\Absen;
use App\Models\Absensi\Izin;
use App\Models\Absensi\Lembur;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Absen::factory(4)->create();
        Izin::factory(2)->create();
        Lembur::factory(4)->create();
    }
}
