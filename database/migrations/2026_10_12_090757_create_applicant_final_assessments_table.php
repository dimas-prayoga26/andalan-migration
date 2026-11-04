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
        Schema::create('applicant_final_assessments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('applicant_id')->constrained('applicants', 'id')->cascadeOnDelete();
            $table->text('skill_note')->nullable();
            $table->text('communication_note')->nullable();
            $table->text('experience_note')->nullable();
            $table->text('tools_note')->nullable();
            $table->text('final_note')->nullable();
            $table->string('final_decision', 40)->nullable()->index();
            $table->foreignUuid('assessed_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamps();

            $table->unique('applicant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_final_assessments');
    }
};
