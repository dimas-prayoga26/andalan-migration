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
        Schema::create('employee_deployments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->unique()->constrained('employees', 'id')->cascadeOnDelete();
            $table->foreignUuid('current_department_id')->nullable()->constrained('departments', 'id')->nullOnDelete();
            $table->foreignUuid('current_position_id')->nullable()->constrained('positions', 'id')->nullOnDelete();
            $table->foreignUuid('current_company_id')->nullable()->constrained('companies', 'id')->nullOnDelete();
            $table->date('join_date')->nullable();
            $table->date('resignation_date')->nullable();
            $table->string('workplace')->nullable();
            $table->string('status')->default('Active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_deployments');
    }
};
