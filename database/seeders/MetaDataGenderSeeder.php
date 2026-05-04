<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetaDataGenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $genders = [
            'Male',
            'Female',
        ];

        DB::table('meta_data_gender')->upsert(
            array_map(fn (string $name): array => [
                'name' => $name,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], $genders),
            ['name'],
            ['is_active', 'updated_at'],
        );
    }
}
