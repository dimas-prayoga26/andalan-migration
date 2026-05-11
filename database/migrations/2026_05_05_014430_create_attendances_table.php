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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->foreignUuid('leave_request_id')->nullable()->constrained('leave_requests')->nullOnDelete();
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->unsignedInteger('late_minutes')->default(0);
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->foreignUuid('overtime_id')->nullable()->constrained('overtimes')->nullOnDelete();
            $table->boolean('is_overtime')->default(false);
            $table->string('status');
            $table->text('location_in')->nullable();
            $table->text('location_out')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
