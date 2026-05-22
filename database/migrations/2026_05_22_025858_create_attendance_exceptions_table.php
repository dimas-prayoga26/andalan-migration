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
        Schema::create('attendance_exceptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendance_id')->nullable()->constrained('attendances', 'id')->nullOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->date('exception_date');
            $table->enum('type', ['late_arrival', 'early_departure']);
            $table->text('note')->nullable();
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->string('status')->default('approved');
            $table->softDeletes();
            $table->timestamps();

            $table->unique('attendance_id', 'attendance_exceptions_attendance_id_unique');
            $table->index(['employee_id', 'exception_date'], 'attendance_exceptions_employee_date_index');
            $table->index('status', 'attendance_exceptions_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_exceptions');
    }
};
