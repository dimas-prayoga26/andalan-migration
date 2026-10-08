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
        Schema::create('mail_business_account_sync_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_access_account_id')
                ->constrained('mail_access_accounts')
                ->cascadeOnDelete();
            $table->string('folder', 50)->default('inbox');
            $table->unsignedBigInteger('last_uid')->nullable();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->string('status', 30)->default('idle')->index();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['mail_access_account_id', 'folder'], 'mail_business_account_sync_states_account_folder_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_business_account_sync_states');
    }
};
