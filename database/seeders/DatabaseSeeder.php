<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;
use Throwable;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            MetaDataDomiciliSeeder::class,
            MetaDataGenderSeeder::class,
            MetaDataMaritalStatusSeeder::class,
            LeaveTypeSeeder::class,
            AttendanceHolidaySeeder::class,
            LeaveSubTypeSeeder::class,
            PositionSeeder::class,
            LegacySqlUserSeeder::class,
            PositionPermissionSeeder::class,
            EmployeePicAssignmentSeeder::class,
            NiskalaMultiPicLeaveSeeder::class,
            RulesOfAttendacesSeeder::class,
        ];

        foreach ($seeders as $seederClass) {
            try {
                $this->call([$seederClass]);
            } catch (Throwable $throwable) {
                throw new RuntimeException("Seeder {$seederClass} gagal dijalankan.", 0, $throwable);
            }
        }
    }
}
