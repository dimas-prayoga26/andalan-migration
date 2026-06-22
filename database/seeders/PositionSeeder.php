<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $now = now();
            $positions = [
                'System Administrator',
                'Commissioner Independent',
                'Commissioner',
                'Chief Operating Officer',
                'Director',
                'Supervisor',
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

            DB::table('positions')->upsert(
                array_map(static fn (string $name): array => [
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $positions),
                ['name'],
                ['status', 'updated_at'],
            );
        } catch (Throwable $throwable) {
            throw new RuntimeException('PositionSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
