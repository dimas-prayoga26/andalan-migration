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
        Schema::create('attendance_permission_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_permission_id');
            $table->foreign('attendance_permission_id', 'att_perm_attach_perm_id_fk')
                ->references('id')
                ->on('attendance_permissions')
                ->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('attachment_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_permission_attachments');
    }
};
