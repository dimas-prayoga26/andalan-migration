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
        Schema::create('device_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees', 'id')->nullOnDelete();
            $table->string('brand_key')->nullable();
            $table->text('endpoint');
            $table->string('endpoint_hash', 64);
            $table->text('public_key')->nullable();
            $table->text('auth_token')->nullable();
            $table->string('content_encoding')->default('aes128gcm');
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('last_subscribed_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique('endpoint_hash', 'device_subscriptions_endpoint_hash_unique');
            $table->index(['user_id', 'brand_key'], 'device_subscriptions_user_brand_index');
            $table->index(['employee_id', 'brand_key'], 'device_subscriptions_employee_brand_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_subscriptions');
    }
};
