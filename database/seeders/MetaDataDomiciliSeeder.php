<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class MetaDataDomiciliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $now = now();
            $domicili = [
                'Yogyakarta',
                'Jakarta',
            ];

            DB::table('meta_data_domicili')->upsert(
                array_map(fn (string $name): array => [
                    'name' => $name,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $domicili),
                ['name'],
                ['is_active', 'updated_at'],
            );
        } catch (Throwable $throwable) {
            throw new RuntimeException('MetaDataDomiciliSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
