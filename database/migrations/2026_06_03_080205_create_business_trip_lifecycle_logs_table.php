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
        Schema::create('business_trip_lifecycle_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_trip_id')->constrained('business_trips', 'id')->cascadeOnDelete();
            $table->string('phase', 100);
            $table->string('event_key', 100);
            $table->unsignedInteger('step_order');
            $table->string('title');
            $table->string('status', 50)->default('waiting');
            $table->foreignUuid('actor_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('happened_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['business_trip_id', 'event_key'], 'business_trip_lifecycle_logs_trip_event_unique');
            $table->unique(['business_trip_id', 'step_order'], 'business_trip_lifecycle_logs_trip_step_unique');
            $table->index(['business_trip_id', 'status'], 'business_trip_lifecycle_logs_trip_status_index');
            $table->index(['business_trip_id', 'happened_at'], 'business_trip_lifecycle_logs_trip_happened_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trip_lifecycle_logs');
    }
};
