<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RulesOfAttendacesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rnbCompany = Company::query()
            ->where('name', 'RNB')
            ->first();

        if (! $rnbCompany) {
            return;
        }

        DB::table('rules_of_attendaces')->updateOrInsert(
            ['companies_id' => $rnbCompany->id],
            [
                'ip_range' => '182.8',
                'radius' => 75,
                'office_start_time' => '08:00:00',
                'office_end_time' => '17:00:00',
                'late_grace_minutes' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
