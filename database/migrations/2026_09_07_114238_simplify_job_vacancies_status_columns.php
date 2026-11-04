<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table): void {
            $table->unsignedTinyInteger('status_value')->default(2)->after('name');
        });

        DB::table('job_vacancies')->update([
            'status_value' => DB::raw("CASE WHEN legacy_status_value = 1 OR status = 'active' THEN 1 ELSE 2 END"),
        ]);

        Schema::table('job_vacancies', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'legacy_vacancy_id',
                'legacy_value',
                'status',
                'legacy_status_value',
            ]);
        });

        Schema::table('job_vacancies', function (Blueprint $table): void {
            $table->renameColumn('status_value', 'status');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table): void {
            $table->unsignedInteger('legacy_vacancy_id')->nullable()->unique()->after('id');
            $table->unsignedInteger('legacy_value')->nullable()->unique()->after('legacy_vacancy_id');
            $table->string('status_label')->default('inactive')->after('name');
            $table->unsignedInteger('legacy_status_value')->nullable()->after('status');
        });

        DB::table('job_vacancies')->update([
            'status_label' => DB::raw("CASE WHEN status = 1 THEN 'active' ELSE 'inactive' END"),
            'legacy_status_value' => DB::raw('status'),
        ]);

        Schema::table('job_vacancies', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });

        Schema::table('job_vacancies', function (Blueprint $table): void {
            $table->renameColumn('status_label', 'status');
            $table->index('status');
        });
    }
};
