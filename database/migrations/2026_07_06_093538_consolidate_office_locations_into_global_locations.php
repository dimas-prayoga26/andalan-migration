<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('office_locations')) {
            return;
        }

        DB::transaction(function (): void {
            $officeLocations = DB::table('office_locations')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['id', 'name', 'address']);

            $officeLocations
                ->groupBy(fn (object $officeLocation): string => $this->cityFor($officeLocation))
                ->each(function (Collection $locations, string $city): void {
                    $canonicalLocation = $locations->first();
                    if (! is_object($canonicalLocation) || ! is_string($canonicalLocation->id)) {
                        return;
                    }

                    $locationIds = $locations->pluck('id')->filter()->values();
                    $canonicalLocationId = $canonicalLocation->id;

                    DB::table('office_locations')
                        ->where('id', $canonicalLocationId)
                        ->update([
                            'name' => $city,
                            'updated_at' => now(),
                        ]);

                    if (Schema::hasColumn('employee_deployments', 'current_office_location_id')) {
                        DB::table('employee_deployments')
                            ->whereIn('current_office_location_id', $locationIds)
                            ->update([
                                'current_office_location_id' => $canonicalLocationId,
                                'workplace' => $city,
                                'updated_at' => now(),
                            ]);
                    }

                    if (Schema::hasColumn('rules_of_attendaces', 'office_location_id')) {
                        $attendanceRules = DB::table('rules_of_attendaces')
                            ->whereIn('office_location_id', $locationIds)
                            ->orderByDesc('is_active')
                            ->orderByDesc('updated_at')
                            ->orderByDesc('created_at')
                            ->get(['id']);
                        $canonicalRule = $attendanceRules->first();

                        if (is_object($canonicalRule) && is_string($canonicalRule->id)) {
                            DB::table('rules_of_attendaces')
                                ->where('id', $canonicalRule->id)
                                ->update([
                                    'office_location_id' => $canonicalLocationId,
                                    'updated_at' => now(),
                                ]);

                            DB::table('rules_of_attendaces')
                                ->whereIn('office_location_id', $locationIds)
                                ->where('id', '!=', $canonicalRule->id)
                                ->delete();
                        }
                    }

                    DB::table('office_locations')
                        ->whereIn('id', $locationIds)
                        ->where('id', '!=', $canonicalLocationId)
                        ->delete();
                });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The original company-specific duplicate locations cannot be reconstructed safely.
    }

    private function cityFor(object $officeLocation): string
    {
        $locationText = Str::of(trim((string) $officeLocation->name).' '.trim((string) $officeLocation->address))->lower();

        return $locationText->contains('jakarta') ? 'Jakarta' : 'Yogyakarta';
    }
};
