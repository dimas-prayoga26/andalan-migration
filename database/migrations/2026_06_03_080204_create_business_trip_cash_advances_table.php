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
        Schema::create('business_trip_cash_advances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_trip_id')->constrained('business_trips', 'id')->cascadeOnDelete();
            $table->foreignUuid('requested_by')->constrained('employees', 'id')->cascadeOnDelete();
            $table->date('date_needed');
            $table->string('category', 100);
            $table->decimal('amount_requested', 12, 2)->default(0);
            $table->decimal('amount_realized', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status', 50)->default('pending');
            $table->decimal('amount_approved', 12, 2)->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('finance_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['business_trip_id', 'status'], 'business_trip_cash_advances_trip_status_index');
            $table->index(['requested_by', 'date_needed'], 'business_trip_cash_advances_requester_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trip_cash_advances');
    }
};
