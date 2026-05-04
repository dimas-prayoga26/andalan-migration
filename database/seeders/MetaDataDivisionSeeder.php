<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetaDataDivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $divisions = [
            'Branding Designer',
            'Administrator',
            'Board of Directors',
            'Administration, Finance and Legal',
            'Operations',
            'Project Planning and Development',
            'Information and Communications Technology',
            'Marketing and Promotion',
        ];

        DB::table('meta_data_divisions')->upsert(
            array_map(fn (string $name): array => [
                'name' => $name,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], $divisions),
            ['name'],
            ['is_active', 'updated_at'],
        );
    }
}
