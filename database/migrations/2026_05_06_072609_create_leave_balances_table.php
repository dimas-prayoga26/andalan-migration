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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees', 'id')->nullOnDelete();
            $table->foreignUuid('leave_type_id')->nullable()->constrained('leave_types', 'id')->nullOnDelete();
            $table->year('period_year')->nullable();
            $table->decimal('earned_quota', 8, 2)->default(0);
            $table->decimal('used_quota', 8, 2)->default(0);
            $table->decimal('remaining_quota', 8, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
