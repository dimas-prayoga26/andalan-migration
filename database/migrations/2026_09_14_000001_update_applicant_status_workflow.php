<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private const STATUSES = [
        0 => 'Submitted',
        1 => 'HR Interview',
        2 => 'Technical test',
        3 => 'User Interview',
        4 => 'Offering',
        5 => 'Not Suitable',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $this->moveProductionAcceptedStatusToOffering();
            $this->upsertProductionStatuses();
        });

        DB::connection('legacy_mysql')->transaction(function (): void {
            $this->moveLegacyAcceptedApplicantsToOffering();
            $this->moveLegacyAcceptedStatusToOffering();
            $this->upsertLegacyStatuses();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function moveProductionAcceptedStatusToOffering(): void
    {
        DB::table('applicant_statuses')
            ->where('name', 'Diterima')
            ->update([
                'value' => 4,
                'name' => self::STATUSES[4],
                'updated_at' => now(),
            ]);
    }

    private function upsertProductionStatuses(): void
    {
        $now = now();

        foreach (self::STATUSES as $value => $name) {
            $statusExists = DB::table('applicant_statuses')
                ->where('value', $value)
                ->exists();

            if ($statusExists) {
                DB::table('applicant_statuses')
                    ->where('value', $value)
                    ->update([
                        'name' => $name,
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('applicant_statuses')->insert([
                'id' => (string) Str::uuid(),
                'value' => $value,
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function moveLegacyAcceptedApplicantsToOffering(): void
    {
        DB::connection('legacy_mysql')
            ->table('applicants')
            ->where('nb', 2)
            ->update(['nb' => 4]);
    }

    private function moveLegacyAcceptedStatusToOffering(): void
    {
        DB::connection('legacy_mysql')
            ->table('opt_applicants_status')
            ->where('name', 'Diterima')
            ->update([
                'value' => 4,
                'name' => self::STATUSES[4],
            ]);
    }

    private function upsertLegacyStatuses(): void
    {
        $now = now();

        foreach (self::STATUSES as $value => $name) {
            $statusExists = DB::connection('legacy_mysql')
                ->table('opt_applicants_status')
                ->where('value', $value)
                ->exists();

            if ($statusExists) {
                DB::connection('legacy_mysql')
                    ->table('opt_applicants_status')
                    ->where('value', $value)
                    ->update(['name' => $name]);

                continue;
            }

            DB::connection('legacy_mysql')
                ->table('opt_applicants_status')
                ->insert([
                    'name' => $name,
                    'value' => $value,
                    'created_at' => $now,
                ]);
        }
    }
};
