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
            $table->id();
            $table->foreignId('companies_id')->constrained('companies')->cascadeOnDelete();
            $table->string('ip_range');
            $table->unsignedInteger('radius');
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
