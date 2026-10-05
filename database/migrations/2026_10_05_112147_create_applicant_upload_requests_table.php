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
        Schema::create('applicant_upload_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('applicant_id')->constrained('applicants', 'id')->cascadeOnDelete();
            $table->string('token_hash')->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamps();

            $table->index(['applicant_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_upload_requests');
    }
};
