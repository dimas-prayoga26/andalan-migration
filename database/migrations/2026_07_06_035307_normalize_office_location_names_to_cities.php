<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('office_locations')
            ->get(['id', 'address', 'name'])
            ->each(function (object $officeLocation): void {
                $normalizedAddress = Str::lower((string) $officeLocation->address);
                $locationName = match (true) {
                    Str::contains($normalizedAddress, 'jakarta') => 'Jakarta',
                    Str::contains($normalizedAddress, ['yogyakarta', 'sleman']) => 'Yogyakarta',
                    default => (string) $officeLocation->name,
                };

                DB::table('office_locations')
                    ->where('id', $officeLocation->id)
                    ->update(['name' => $locationName]);

                DB::table('employee_deployments')
                    ->where('current_office_location_id', $officeLocation->id)
                    ->update(['workplace' => $locationName]);
            });
    }

    public function down(): void
    {
        $companyNames = DB::table('companies')->pluck('name', 'id');

        DB::table('office_locations')
            ->get(['id', 'company_id', 'address'])
            ->each(function (object $officeLocation) use ($companyNames): void {
                $companyName = (string) ($companyNames[$officeLocation->company_id] ?? 'Office');
                $normalizedAddress = Str::lower((string) $officeLocation->address);
                $locationName = match (true) {
                    Str::contains($normalizedAddress, 'jakarta') => $companyName.' Branch Jakarta',
                    Str::lower($companyName) === 'rnb' => 'RNB Branch Jogja',
                    default => $companyName,
                };

                DB::table('office_locations')
                    ->where('id', $officeLocation->id)
                    ->update(['name' => $locationName]);

                DB::table('employee_deployments')
                    ->where('current_office_location_id', $officeLocation->id)
                    ->update(['workplace' => $locationName]);
            });
    }
};
