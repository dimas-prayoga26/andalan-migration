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
        Schema::create('telegram_users_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('telegram_user_id')->nullable()->constrained('telegram_users', 'id')->nullOnDelete();
            $table->foreignUuid('attendance_id')->nullable()->constrained('attendances', 'id')->nullOnDelete();
            $table->string('notification_type', 50);
            $table->text('message');
            $table->boolean('is_success')->default(false);
            $table->unsignedSmallInteger('http_status_code')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['telegram_user_id', 'created_at']);
            $table->index(['attendance_id', 'created_at']);
            $table->index(['is_success', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_users_logs');
    }
};
