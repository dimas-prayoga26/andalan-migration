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
        Schema::create('mail_business_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_access_account_id')
                ->constrained('mail_access_accounts')
                ->cascadeOnDelete();
            $table->string('folder', 50)->default('inbox');
            $table->unsignedBigInteger('uid')->nullable();
            $table->string('message_id', 191)->nullable()->index();
            $table->string('thread_key', 191)->nullable()->index();
            $table->string('in_reply_to', 191)->nullable()->index();
            $table->string('subject', 500)->nullable();
            $table->string('from_email')->nullable()->index();
            $table->string('from_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->json('to_recipients')->nullable();
            $table->json('cc_recipients')->nullable();
            $table->json('bcc_recipients')->nullable();
            $table->text('preview')->nullable();
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('raw_headers')->nullable();
            $table->json('flags')->nullable();
            $table->boolean('is_seen')->default(false)->index();
            $table->boolean('is_answered')->default(false)->index();
            $table->boolean('is_flagged')->default(false)->index();
            $table->boolean('is_draft')->default(false)->index();
            $table->boolean('has_attachments')->default(false)->index();
            $table->timestamp('received_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('internal_date')->nullable()->index();
            $table->timestamp('synced_at')->nullable()->index();
            $table->timestamp('remote_deleted_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['mail_access_account_id', 'folder', 'uid'], 'mail_business_messages_account_folder_uid_unique');
            $table->index(['mail_access_account_id', 'folder', 'received_at'], 'mail_business_messages_account_folder_received_index');
            $table->index(['mail_access_account_id', 'folder', 'sent_at'], 'mail_business_messages_account_folder_sent_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_business_messages');
    }
};
