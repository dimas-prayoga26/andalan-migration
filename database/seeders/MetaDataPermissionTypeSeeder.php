<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class MetaDataPermissionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $now = now();
            $permissionTypes = [
                ['name' => 'Masuk', 'color' => '#16a34a'],
                ['name' => 'Pulang', 'color' => '#0ea5e9'],
                ['name' => 'Terlambat', 'color' => '#f97316'],
                ['name' => 'Sakit', 'color' => '#ef4444'],
                ['name' => 'Cuti Khusus', 'color' => '#f59e0b'],
                ['name' => 'Cuti Tahunan', 'color' => '#d97706'],
                ['name' => 'Izin Dinas Dalam Kota', 'color' => '#0f766e'],
                ['name' => 'Izin Dinas Luar Kota', 'color' => '#1d4ed8'],
            ];

            foreach ($permissionTypes as $permissionType) {
                DB::table('meta_data_permission_types')->updateOrInsert(
                    ['name' => $permissionType['name']],
                    [
                        'color' => $permissionType['color'],
                        'created_at' => $now,
                    ],
                );
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('MetaDataPermissionTypeSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
