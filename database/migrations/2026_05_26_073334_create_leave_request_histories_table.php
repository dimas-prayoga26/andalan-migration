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
        Schema::create('leave_request_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('leave_request_id')->constrained('leave_requests', 'id')->cascadeOnDelete();
            $table->foreignUuid('actor_user_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->string('event_type', 50);
            $table->string('title', 120);
            $table->enum('from_status', ['pending', 'approved', 'rejected', 'refused'])->nullable();
            $table->enum('to_status', ['pending', 'approved', 'rejected', 'refused'])->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('happened_at');
            $table->timestamps();

            $table->index(['leave_request_id', 'happened_at'], 'leave_request_histories_request_happened_index');
            $table->index('event_type', 'leave_request_histories_event_type_index');
            $table->index('to_status', 'leave_request_histories_to_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_histories');
    }
};
