<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            MetaDataLeaveCompanySeeder::class,
            RulesOfAttendacesSeeder::class,
            MetaDataDomiciliSeeder::class,
            MetaDataGenderSeeder::class,
            MetaDataMaritalStatusSeeder::class,
            MetaDataDivisionSeeder::class,
            MetaDataPositionSeeder::class,
            MetaDataPermissionTypeSeeder::class,
            UserSeeder::class,
            LeaveBalanceSeeder::class,
        ]);
    }
}
