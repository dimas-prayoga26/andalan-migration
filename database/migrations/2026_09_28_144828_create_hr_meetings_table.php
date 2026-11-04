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
        Schema::create('hr_meetings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('type')->default('weekly_meeting');
            $table->date('meeting_date');
            $table->time('meeting_time');
            $table->string('meeting_link')->nullable();
            $table->string('status')->default('scheduled');
            $table->string('attachment_link')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('employees', 'id')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'meeting_date'], 'hr_meetings_status_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_meetings');
    }
};
