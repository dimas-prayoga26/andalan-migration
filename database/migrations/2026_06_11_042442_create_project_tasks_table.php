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
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->nullable()->constrained('projects', 'id')->nullOnDelete();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->foreignUuid('overtime_id')->nullable()->constrained('overtimes', 'id')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('blockers')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('pending');
            $table->string('priority')->default('medium');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'status'], 'project_tasks_project_status_index');
            $table->index(['employee_id', 'status'], 'project_tasks_employee_status_index');
            $table->index(['overtime_id', 'status'], 'project_tasks_overtime_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};
