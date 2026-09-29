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
        Schema::create('hr_meeting_mom_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hr_meeting_id')->constrained('hr_meetings', 'id')->cascadeOnDelete();
            $table->string('file_path');
            $table->foreignUuid('generated_by')->nullable()->constrained('employees', 'id')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['hr_meeting_id', 'generated_at'], 'hr_meeting_mom_exports_meeting_generated_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_meeting_mom_exports');
    }
};
