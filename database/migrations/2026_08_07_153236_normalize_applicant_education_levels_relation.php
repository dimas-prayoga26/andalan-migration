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
        if (! Schema::hasTable('education_levels')) {
            Schema::create('education_levels', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->unsignedInteger('legacy_education_level_id')->nullable()->unique();
                $table->unsignedInteger('legacy_value')->nullable()->unique();
                $table->string('name');
                $table->timestamp('legacy_created_at')->nullable();
                $table->timestamps();
            });
        }

        $this->seedDefaultEducationLevels();

        if (! Schema::hasColumn('applicant_educations', 'education_level_id')) {
            Schema::table('applicant_educations', function (Blueprint $table): void {
                $table->foreignUuid('education_level_id')
                    ->nullable()
                    ->after('applicant_id')
                    ->constrained('education_levels', 'id')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('applicant_educations', 'educational_level')) {
            $educationLevelIdsByLegacyValue = DB::table('education_levels')->pluck('id', 'legacy_value');

            foreach ($educationLevelIdsByLegacyValue as $legacyValue => $educationLevelId) {
                DB::table('applicant_educations')
                    ->where('educational_level', (string) $legacyValue)
                    ->update(['education_level_id' => $educationLevelId]);
            }

            Schema::table('applicant_educations', function (Blueprint $table): void {
                $table->dropColumn('educational_level');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('applicant_educations', 'educational_level')) {
            Schema::table('applicant_educations', function (Blueprint $table): void {
                $table->text('educational_level')->nullable()->after('sequence');
            });
        }

        $educationLevelValuesById = DB::table('education_levels')->pluck('legacy_value', 'id');

        foreach ($educationLevelValuesById as $educationLevelId => $legacyValue) {
            DB::table('applicant_educations')
                ->where('education_level_id', $educationLevelId)
                ->update(['educational_level' => $legacyValue === null ? null : (string) $legacyValue]);
        }

        if (Schema::hasColumn('applicant_educations', 'education_level_id')) {
            Schema::table('applicant_educations', function (Blueprint $table): void {
                $table->dropForeign(['education_level_id']);
                $table->dropColumn('education_level_id');
            });
        }

        Schema::dropIfExists('education_levels');
    }

    private function seedDefaultEducationLevels(): void
    {
        $now = now();

        foreach ($this->defaultEducationLevels() as $legacyValue => $name) {
            DB::table('education_levels')->updateOrInsert(
                ['legacy_value' => $legacyValue],
                [
                    'id' => DB::table('education_levels')->where('legacy_value', $legacyValue)->value('id') ?? (string) Str::uuid(),
                    'legacy_education_level_id' => $legacyValue,
                    'name' => $name,
                    'created_at' => DB::table('education_levels')->where('legacy_value', $legacyValue)->value('created_at') ?? $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function defaultEducationLevels(): array
    {
        return [
            1 => 'SMA',
            2 => 'Diploma',
            3 => 'Strata 1',
            4 => 'Strata 2',
            5 => 'Strata 3',
        ];
    }
};
