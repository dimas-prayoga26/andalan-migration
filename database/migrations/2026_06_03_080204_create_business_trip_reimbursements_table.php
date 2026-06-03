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
        Schema::create('business_trip_reimbursements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_trip_id')->constrained('business_trips', 'id')->cascadeOnDelete();
            $table->foreignUuid('requested_by')->constrained('employees', 'id')->cascadeOnDelete();
            $table->date('expense_date');
            $table->string('category', 100);
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('status', 50)->default('pending');
            $table->decimal('amount_approved', 12, 2)->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('finance_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['business_trip_id', 'status'], 'business_trip_reimbursements_trip_status_index');
            $table->index(['requested_by', 'expense_date'], 'business_trip_reimbursements_requester_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trip_reimbursements');
    }
};
