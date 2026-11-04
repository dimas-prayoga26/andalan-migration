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
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignUuid('employee_id')->nullable()->constrained('employees', 'id')->nullOnDelete();
            $table->string('brand_key')->nullable();
            $table->string('type')->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('icon', 2048)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at'], 'app_notifications_user_read_index');
            $table->index(['employee_id', 'read_at'], 'app_notifications_employee_read_index');
            $table->index(['created_at'], 'app_notifications_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
