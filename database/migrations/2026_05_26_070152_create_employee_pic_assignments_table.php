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
        Schema::create('employee_pic_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('supervisor_employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->foreignUuid('staff_employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['supervisor_employee_id', 'staff_employee_id'],
                'employee_pic_assignments_supervisor_staff_unique'
            );
            $table->index(['supervisor_employee_id', 'is_active'], 'employee_pic_assignments_supervisor_active_index');
            $table->index(['staff_employee_id', 'is_active'], 'employee_pic_assignments_staff_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_pic_assignments');
    }
};
