<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('applicant_statuses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedTinyInteger('value')->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();
        DB::table('applicant_statuses')->insert([
            [
                'id' => (string) Str::uuid(),
                'value' => 0,
                'name' => 'Submitted',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'value' => 1,
                'name' => 'Interview',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'value' => 2,
                'name' => 'Diterima',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::table('applicants', function (Blueprint $table): void {
            $table->foreignUuid('applicant_status_id')
                ->nullable()
                ->after('slug')
                ->constrained('applicant_statuses', 'id');
        });

        $statusIdsByValue = DB::table('applicant_statuses')->pluck('id', 'value');

        if (Schema::hasColumn('applicants', 'status')) {
            DB::table('applicants')
                ->where('status', 'interview')
                ->update(['applicant_status_id' => $statusIdsByValue->get(1)]);

            DB::table('applicants')
                ->where('status', 'diterima')
                ->update(['applicant_status_id' => $statusIdsByValue->get(2)]);
        }

        DB::table('applicants')
            ->whereNull('applicant_status_id')
            ->update(['applicant_status_id' => $statusIdsByValue->get(0)]);

        if (Schema::hasColumn('applicants', 'status')) {
            Schema::table('applicants', function (Blueprint $table): void {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table): void {
            $table->string('status')->default('submitted')->after('slug')->index();
        });

        $statusValuesById = DB::table('applicant_statuses')->pluck('value', 'id');

        foreach ($statusValuesById as $statusId => $statusValue) {
            DB::table('applicants')
                ->where('applicant_status_id', $statusId)
                ->update([
                    'status' => match ((int) $statusValue) {
                        1 => 'interview',
                        2 => 'diterima',
                        default => 'submitted',
                    },
                ]);
        }

        Schema::table('applicants', function (Blueprint $table): void {
            $table->dropForeign(['applicant_status_id']);
            $table->dropColumn('applicant_status_id');
        });

        Schema::dropIfExists('applicant_statuses');
    }
};
