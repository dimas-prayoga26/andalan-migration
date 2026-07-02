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
        Schema::create('overtime_lifecycle_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('overtime_id')->constrained('overtimes', 'id')->cascadeOnDelete();
            $table->string('phase', 100);
            $table->string('event_key', 100);
            $table->unsignedInteger('step_order');
            $table->string('title');
            $table->string('status', 50)->default('waiting');
            $table->foreignUuid('actor_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('happened_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['overtime_id', 'event_key'], 'overtime_lifecycle_logs_overtime_event_unique');
            $table->unique(['overtime_id', 'step_order'], 'overtime_lifecycle_logs_overtime_step_unique');
            $table->index(['overtime_id', 'status'], 'overtime_lifecycle_logs_overtime_status_index');
            $table->index(['overtime_id', 'happened_at'], 'overtime_lifecycle_logs_overtime_happened_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_lifecycle_logs');
    }
};
