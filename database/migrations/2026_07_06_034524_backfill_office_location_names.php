<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companyNames = DB::table('companies')->pluck('name', 'id');

        DB::table('office_locations')
            ->orderBy('company_id')
            ->orderBy('created_at')
            ->get(['id', 'company_id', 'address'])
            ->groupBy('company_id')
            ->each(function ($officeLocations, string $companyId) use ($companyNames): void {
                $companyName = trim((string) ($companyNames[$companyId] ?? 'Office'));

                $officeLocations->values()->each(function (object $officeLocation) use ($companyName): void {
                    $normalizedAddress = Str::lower((string) $officeLocation->address);

                    $locationName = match (true) {
                        Str::contains($normalizedAddress, 'jakarta') => 'Jakarta',
                        Str::contains($normalizedAddress, ['yogyakarta', 'sleman']) => 'Yogyakarta',
                        default => $companyName,
                    };

                    DB::table('office_locations')
                        ->where('id', $officeLocation->id)
                        ->update(['name' => $locationName]);
                });
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('office_locations')->update(['name' => null]);
    }
};
