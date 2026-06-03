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
        Schema::create('business_trip_expense_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_trip_id')->constrained('business_trips', 'id')->cascadeOnDelete();
            $table->string('category', 100);
            $table->date('date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('source', 50)->default('company');
            $table->string('attachment_path')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['business_trip_id', 'category'], 'business_trip_expense_items_trip_category_index');
            $table->index(['business_trip_id', 'source'], 'business_trip_expense_items_trip_source_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trip_expense_items');
    }
};
