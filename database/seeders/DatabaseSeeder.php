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
            CompanySeeder::class,
            RulesOfAttendacesSeeder::class,
            MetaDataDomiciliSeeder::class,
            MetaDataGenderSeeder::class,
            MetaDataMaritalStatusSeeder::class,
            PositionSeeder::class,
            LeaveTypeSeeder::class,
            AttendanceHolidaySeeder::class,
            LeaveSubTypeSeeder::class,
            UserSeeder::class,
            EmployeePicAssignmentSeeder::class,
            BusinessTripSeeder::class,
            EmployeeProfileSeeder::class,
            EmployeeIdentitySeeder::class,
            EmployeeFamilySeeder::class,
            EmployeeOrganizationSeeder::class,
            EmployeeBankAccountSeeder::class,
            EmployeeAddressSeeder::class,
            LeaveBalanceSeeder::class,
            LeaveRequestHistorySeeder::class,
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
