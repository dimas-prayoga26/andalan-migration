<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Medical check-up (MCU) history recorded by HR; the result file itself lives in the document vault.
     */
    public function up(): void
    {
        Schema::create('employee_medical_checkups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->date('checkup_date');
            $table->string('clinic_name')->nullable();
            $table->string('result_status', 30);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'checkup_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_medical_checkups');
    }
};
