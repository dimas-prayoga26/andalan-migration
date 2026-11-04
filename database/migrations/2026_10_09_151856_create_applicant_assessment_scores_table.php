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
        Schema::create('applicant_assessment_scores', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('applicant_assessment_id')->constrained('applicant_assessments', 'id')->cascadeOnDelete();
            $table->string('criterion_key', 100);
            $table->string('criterion_label');
            $table->string('parent_key', 100)->nullable()->index();
            $table->string('source_type', 80)->index();
            $table->foreignUuid('source_id')->nullable()->constrained('job_vacancy_technical_criteria', 'id')->nullOnDelete();
            $table->decimal('weight', 5, 2);
            $table->unsignedTinyInteger('rating')->nullable();
            $table->decimal('raw_score', 6, 2)->nullable();
            $table->decimal('weighted_score', 6, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['applicant_assessment_id', 'criterion_key'], 'applicant_assessment_scores_assessment_criterion_unique');
            $table->index(['applicant_assessment_id', 'sort_order'], 'applicant_assessment_scores_assessment_sort_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_assessment_scores');
    }
};
