<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class RulesOfAttendacesSeeder extends Seeder
{
    /**
     * @return array{address: string, latitude: float, longitude: float}
     */
    private function defaultOfficeLocationData(): array
    {
        return [
            'address' => 'Bulurejo, RT.04/RW.02, Gantalan, Minomartani, Kec. Ngaglik, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55581',
            'latitude' => -7.7299965,
            'longitude' => 110.4040011,
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            Company::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->each(fn (Company $company): bool => $this->syncCompanyAttendanceRule($company));
        } catch (Throwable $throwable) {
            throw new RuntimeException('RulesOfAttendacesSeeder gagal dijalankan.', 0, $throwable);
        }
    }

    private function syncCompanyAttendanceRule(Company $company): bool
    {
        $now = now();
        $officeLocationIds = collect();

        if (Schema::hasTable('office_locations')) {
            $officeLocationIds = DB::table('office_locations')
                ->where('company_id', $company->id)
                ->where('is_active', true)
                ->orderBy('created_at')
                ->pluck('id')
                ->filter(static fn (mixed $officeLocationId): bool => is_string($officeLocationId) && trim($officeLocationId) !== '')
                ->values();

            if ($officeLocationIds->isEmpty()) {
                $officeData = $this->defaultOfficeLocationData();
                $officeLocationId = (string) Str::uuid();

                DB::table('office_locations')->insert([
                    'id' => $officeLocationId,
                    'company_id' => $company->id,
                    'address' => $officeData['address'],
                    'latitude' => $officeData['latitude'],
                    'longitude' => $officeData['longitude'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $officeLocationIds = collect([$officeLocationId]);
            }
        }

        if ($officeLocationIds->isEmpty()) {
            $this->syncOfficeAttendanceRule($company, null, $now);

            return true;
        }

        $officeLocationIds->each(function (string $officeLocationId) use ($company, $now): void {
            $this->syncOfficeAttendanceRule($company, $officeLocationId, $now);
        });

        $primaryOfficeLocationId = $officeLocationIds->first();

        if (Schema::hasColumn('employee_deployments', 'current_office_location_id') && is_string($primaryOfficeLocationId)) {
            DB::table('employee_deployments')
                ->where('current_company_id', $company->id)
                ->whereNull('current_office_location_id')
                ->update([
                    'current_office_location_id' => $primaryOfficeLocationId,
                    'updated_at' => $now,
                ]);
        }

        return true;
    }

    private function syncOfficeAttendanceRule(Company $company, ?string $officeLocationId, Carbon $now): void
    {
        $existingRuleId = null;
        if (Schema::hasColumn('rules_of_attendaces', 'office_location_id') && is_string($officeLocationId)) {
            $existingRuleId = DB::table('rules_of_attendaces')
                ->where('office_location_id', $officeLocationId)
                ->value('id')
                ?? DB::table('rules_of_attendaces')
                    ->where('companies_id', $company->id)
                    ->whereNull('office_location_id')
                    ->value('id');
        }

        if ((! is_string($existingRuleId) || trim($existingRuleId) === '') && ! is_string($officeLocationId)) {
            $existingRuleId = DB::table('rules_of_attendaces')
                ->where('companies_id', $company->id)
                ->value('id');
        }
        $ruleId = is_string($existingRuleId) && trim($existingRuleId) !== ''
            ? $existingRuleId
            : (string) Str::uuid();

        $ruleData = [
            'id' => $ruleId,
            'companies_id' => $company->id,
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
    }
}
