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
        Schema::create('employee_licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->string('license_type')->nullable();
            $table->string('license_number')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expired_at')->nullable();
            $table->string('issuer')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'license_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_licenses');
    }
};
