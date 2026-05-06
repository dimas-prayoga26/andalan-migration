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
        Schema::create('attendance_permission_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_permission_id');
            $table->foreign('attendance_permission_id', 'att_perm_qr_perm_id_fk')
                ->references('id')
                ->on('attendance_permissions')
                ->cascadeOnDelete();
            $table->string('qr_token')->unique();
            $table->json('qr_payload');
            $table->unsignedBigInteger('issued_by');
            $table->foreign('issued_by', 'att_perm_qr_issued_by_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('scan_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_permission_qr_codes');
    }
};
