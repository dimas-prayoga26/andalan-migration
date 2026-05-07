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
        Schema::table('attendance_permissions', function (Blueprint $table) {
            $table->string('permission_types')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_types')->change();
        });
    }
};
