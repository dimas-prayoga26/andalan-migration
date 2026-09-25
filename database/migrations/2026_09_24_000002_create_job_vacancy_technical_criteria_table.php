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
        if (Schema::hasTable('job_vacancy_technical_criteria')) {
            return;
        }

        Schema::create('job_vacancy_technical_criteria', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_vacancy_id')->constrained('job_vacancies', 'id')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('weight');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['job_vacancy_id', 'sort_order'], 'job_vacancy_criteria_vacancy_sort_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_vacancy_technical_criteria');
    }
};
