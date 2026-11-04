<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Assets owned by a company; employee_id is the employee currently holding the asset.
     */
    public function up(): void
    {
        Schema::create('company_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies', 'id')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees', 'id')->nullOnDelete();
            $table->string('name');
            $table->string('specification')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('handover_date')->nullable();
            $table->string('status', 30)->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_assets');
    }
};
