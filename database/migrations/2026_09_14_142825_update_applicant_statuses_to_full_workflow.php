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
            $this->moveAcceptedStatusToOffering();
            $this->upsertApplicantStatuses();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function (): void {
            DB::table('applicant_statuses')
                ->where('value', 1)
                ->update([
                    'name' => 'Interview',
                    'updated_at' => now(),
                ]);

            DB::table('applicant_statuses')
                ->where('value', 4)
                ->update([
                    'name' => 'Diterima',
                    'updated_at' => now(),
                ]);
        });
    }

    private function moveAcceptedStatusToOffering(): void
    {
        $acceptedStatus = DB::table('applicant_statuses')
            ->where('value', 2)
            ->first();

        if (! $acceptedStatus) {
            return;
        }

        $offeringStatus = DB::table('applicant_statuses')
            ->where('value', 4)
            ->first();

        if (! $offeringStatus) {
            DB::table('applicant_statuses')
                ->where('id', $acceptedStatus->id)
                ->update([
                    'value' => 4,
                    'name' => self::STATUSES[4],
                    'updated_at' => now(),
                ]);

            return;
        }

        if (strtolower((string) $acceptedStatus->name) !== 'technical test') {
            DB::table('applicants')
                ->where('applicant_status_id', $acceptedStatus->id)
                ->update(['applicant_status_id' => $offeringStatus->id]);
        }
    }

    private function upsertApplicantStatuses(): void
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
};
