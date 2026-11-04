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
        Schema::create('employee_certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->string('certificate_name')->nullable();
            $table->string('issuer')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('expired_at')->nullable();
            $table->string('city')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('document_path')->nullable();
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->timestamps();

            $table->index(['employee_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_certifications');
    }
};
