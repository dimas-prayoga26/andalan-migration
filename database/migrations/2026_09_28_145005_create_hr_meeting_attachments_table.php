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
        Schema::create('hr_meeting_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hr_meeting_id')->constrained('hr_meetings', 'id')->cascadeOnDelete();
            $table->string('type')->default('link');
            $table->string('name')->nullable();
            $table->text('url')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_meeting_attachments');
    }
};
