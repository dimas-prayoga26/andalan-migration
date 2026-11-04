<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->dropColumnsIfPresent('applicants', [
            'legacy_applicant_id',
            'job_applied_legacy_value',
            'legacy_created_at',
        ]);

        $this->dropColumnsIfPresent('job_vacancies', [
            'legacy_vacancy_id',
            'legacy_value',
            'legacy_status_value',
            'legacy_created_at',
        ]);

        $this->dropColumnsIfPresent('education_levels', [
            'legacy_education_level_id',
            'legacy_value',
            'legacy_created_at',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table): void {
            if (! Schema::hasColumn('applicants', 'legacy_applicant_id')) {
                $table->unsignedInteger('legacy_applicant_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('applicants', 'job_applied_legacy_value')) {
                $table->unsignedInteger('job_applied_legacy_value')->nullable()->index()->after('address');
            }

            if (! Schema::hasColumn('applicants', 'legacy_created_at')) {
                $table->timestamp('legacy_created_at')->nullable()->index()->after('agreement');
            }
        });

        Schema::table('job_vacancies', function (Blueprint $table): void {
            if (! Schema::hasColumn('job_vacancies', 'legacy_vacancy_id')) {
                $table->unsignedInteger('legacy_vacancy_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('job_vacancies', 'legacy_value')) {
                $table->unsignedInteger('legacy_value')->nullable()->unique()->after('legacy_vacancy_id');
            }

            if (! Schema::hasColumn('job_vacancies', 'legacy_status_value')) {
                $table->unsignedInteger('legacy_status_value')->nullable()->after('status');
            }

            if (! Schema::hasColumn('job_vacancies', 'legacy_created_at')) {
                $table->timestamp('legacy_created_at')->nullable()->index()->after('legacy_status_value');
            }
        });

        Schema::table('education_levels', function (Blueprint $table): void {
            if (! Schema::hasColumn('education_levels', 'legacy_education_level_id')) {
                $table->unsignedInteger('legacy_education_level_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('education_levels', 'legacy_value')) {
                $table->unsignedInteger('legacy_value')->nullable()->unique()->after('legacy_education_level_id');
            }

            if (! Schema::hasColumn('education_levels', 'legacy_created_at')) {
                $table->timestamp('legacy_created_at')->nullable()->after('name');
            }
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropColumnsIfPresent(string $table, array $columns): void
    {
        $existingColumns = array_values(array_filter(
            $columns,
            static fn (string $column): bool => Schema::hasColumn($table, $column),
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($existingColumns): void {
            $table->dropColumn($existingColumns);
        });
    }
};
