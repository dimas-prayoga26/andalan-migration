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
        Schema::dropIfExists('project_departments');

        Schema::create('project_division_event', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects', 'id')->cascadeOnDelete();
            $table->foreignUuid('event_division_id')->constrained('event_divisions', 'id')->cascadeOnDelete();
            $table->string('google_drive_url', 2048)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['project_id', 'event_division_id'], 'project_division_event_project_event_division_unique');
            $table->index(['event_division_id', 'status'], 'project_division_event_event_division_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_division_event');

        Schema::create('project_departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects', 'id')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('departments', 'id')->cascadeOnDelete();
            $table->string('google_drive_url', 2048)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['project_id', 'department_id'], 'project_departments_project_department_unique');
            $table->index(['department_id', 'status'], 'project_departments_department_status_index');
        });
    }
};
