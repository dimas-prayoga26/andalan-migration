<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetaDataPermissionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $permissionTypes = [
            ['name' => 'Masuk', 'color' => '#16a34a'],
            ['name' => 'Pulang', 'color' => '#0ea5e9'],
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
    }
}
