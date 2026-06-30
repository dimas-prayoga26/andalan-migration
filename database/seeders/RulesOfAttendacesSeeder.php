<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class RulesOfAttendacesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $rnbCompany = Company::query()
                ->where('name', 'RNB')
                ->first();

            if (! $rnbCompany) {
                return;
            }

            $now = now();
            $officeLocationId = null;

            if (
                Schema::hasTable('office_locations')
                && $rnbCompany->latitude !== null
                && $rnbCompany->longitude !== null
            ) {
                $officeLocationId = DB::table('office_locations')
                    ->where('company_id', $rnbCompany->id)
                    ->where('address', $rnbCompany->address)
                    ->value('id');

                if (! is_string($officeLocationId) || trim($officeLocationId) === '') {
                    $officeLocationId = (string) Str::uuid();

                    DB::table('office_locations')->insert([
                        'id' => $officeLocationId,
                        'company_id' => $rnbCompany->id,
                        'address' => $rnbCompany->address,
                        'latitude' => $rnbCompany->latitude,
                        'longitude' => $rnbCompany->longitude,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $existingRuleId = null;
            if (Schema::hasColumn('rules_of_attendaces', 'office_location_id') && is_string($officeLocationId)) {
                $existingRuleId = DB::table('rules_of_attendaces')
                    ->where('office_location_id', $officeLocationId)
                    ->value('id')
                    ?? DB::table('rules_of_attendaces')
                        ->where('companies_id', $rnbCompany->id)
                        ->whereNull('office_location_id')
                        ->value('id');
            }

            if (! is_string($existingRuleId) || trim($existingRuleId) === '') {
                $existingRuleId = DB::table('rules_of_attendaces')
                    ->where('companies_id', $rnbCompany->id)
                    ->value('id');
            }
            $ruleId = is_string($existingRuleId) && trim($existingRuleId) !== ''
                ? $existingRuleId
                : (string) Str::uuid();

            $ruleData = [
                'id' => $ruleId,
                'companies_id' => $rnbCompany->id,
                'ip_range' => '182.8',
                'radius' => 75,
                'office_start_time' => '08:00:00',
                'office_end_time' => '17:00:00',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('rules_of_attendaces', 'office_location_id')) {
                $ruleData['office_location_id'] = $officeLocationId;
            }

            DB::table('rules_of_attendaces')->updateOrInsert(
                ['id' => $ruleId],
                $ruleData
            );
        } catch (Throwable $throwable) {
            throw new RuntimeException('RulesOfAttendacesSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
