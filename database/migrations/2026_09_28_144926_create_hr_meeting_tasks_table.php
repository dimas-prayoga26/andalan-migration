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
        Schema::create('hr_meeting_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hr_meeting_id')->constrained('hr_meetings', 'id')->cascadeOnDelete();
            $table->string('category')->default('others');
            $table->foreignUuid('project_task_id')->nullable()->constrained('project_tasks', 'id')->nullOnDelete();
            $table->timestamps();

            $table->index(['hr_meeting_id', 'category'], 'hr_meeting_tasks_meeting_category_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_meeting_tasks');
    }
};
