<?php

namespace Database\Seeders;

use App\Models\Absensi\Absen;
use App\Models\Absensi\Izin;
use App\Models\Absensi\Lembur;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            UserSeeder::class,
        ]);
    }
}
