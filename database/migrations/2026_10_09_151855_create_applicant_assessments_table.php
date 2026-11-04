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
        Schema::create('applicant_assessments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('applicant_id')->constrained('applicants', 'id')->cascadeOnDelete();
            $table->string('section', 40)->index();
            $table->decimal('total_score', 5, 2)->default(0);
            $table->string('status', 40)->default('draft')->index();
            $table->text('summary')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('assessed_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable()->index();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['applicant_id', 'section']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_assessments');
    }
};
