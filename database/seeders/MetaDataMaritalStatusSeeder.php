<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetaDataMaritalStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $maritalStatuses = [
            'Single',
            'Married',
            'Divorced',
            'Widowed',
        ];

        DB::table('meta_data_marital_statuses')->upsert(
            array_map(fn (string $name): array => [
                'name' => $name,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], $maritalStatuses),
            ['name'],
            ['is_active', 'updated_at'],
        );
    }
}
