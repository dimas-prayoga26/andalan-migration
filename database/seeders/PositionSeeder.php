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
                'Super Administrator',
                'Administrator',
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
                'Driver',
            ];

            foreach ($positions as $name) {
                $positionId = DB::table('positions')->where('name', $name)->value('id');

                if (is_string($positionId) && trim($positionId) !== '') {
                    DB::table('positions')
                        ->where('id', $positionId)
                        ->update([
                            'status' => 'active',
                            'updated_at' => $now,
                        ]);

                    continue;
                }

                DB::table('positions')->insert([
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('PositionSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
