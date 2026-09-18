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
        Schema::create('user_activity_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignUuid('attendance_id')->nullable()->constrained('attendances', 'id')->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('event', 80)->index();
            $table->string('route_name')->nullable()->index();
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'event', 'created_at']);
            $table->index(['attendance_id', 'event']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
