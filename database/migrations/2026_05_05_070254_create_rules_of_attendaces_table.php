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
        Schema::create('rules_of_attendaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('companies_id')->constrained('companies', 'id')->cascadeOnDelete();
            $table->string('ip_range');
            $table->unsignedInteger('radius');
            $table->time('office_start_time')->default('08:00:00');
            $table->time('office_end_time')->default('17:00:00');
            $table->unsignedInteger('late_grace_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules_of_attendaces');
    }
};
