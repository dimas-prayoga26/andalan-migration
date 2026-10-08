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
        Schema::create('mail_business_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_business_message_id')
                ->constrained('mail_business_messages', indexName: 'mail_bus_msg_attach_msg_fk')
                ->cascadeOnDelete();
            $table->unsignedInteger('attachment_index')->default(0);
            $table->string('filename')->nullable();
            $table->string('mime_type', 191)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('content_id', 191)->nullable()->index();
            $table->string('disposition', 30)->nullable();
            $table->boolean('is_inline')->default(false)->index();
            $table->string('storage_disk', 50)->default('local');
            $table->text('storage_path')->nullable();
            $table->string('checksum', 191)->nullable()->index();
            $table->timestamps();

            $table->unique(['mail_business_message_id', 'attachment_index'], 'mail_business_message_attachments_message_index_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_business_message_attachments');
    }
};
