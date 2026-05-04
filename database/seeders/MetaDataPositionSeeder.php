<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetaDataPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $positions = [
            'System Administrator',
            'Commissioner Independent',
            'Commissioner',
            'Chief Operating Officer',
            'Director',
            'Legal Officer & Partnership',
            'Finance and Administration Coordinator',
            'Accounting and Taxation',
            'Operations Coordinator',
            'Interior Design',
            'Architecture Design',
            'Web Developer',
            'Documentation Event and Editor Video',
            'Graphic Design',
            'Branding Designer',
        ];

        DB::table('meta_data_positions')->upsert(
            array_map(fn (string $name): array => [
                'name' => $name,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], $positions),
            ['name'],
            ['is_active', 'updated_at'],
        );
    }
}
