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
        Schema::create('hr_meeting_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hr_meeting_id')->constrained('hr_meetings', 'id')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees', 'id')->nullOnDelete();
            $table->string('participant_type')->default('employee');
            $table->string('attendance_status')->default('invited');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->index(['hr_meeting_id', 'attendance_status'], 'hr_meeting_participants_meeting_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_meeting_participants');
    }
};
