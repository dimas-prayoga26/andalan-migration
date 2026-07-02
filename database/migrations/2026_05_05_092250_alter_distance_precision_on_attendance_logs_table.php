<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('attendance_logs')) {
            DB::statement('ALTER TABLE attendance_logs MODIFY distance DECIMAL(12,2) UNSIGNED NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendance_logs')) {
            DB::statement('ALTER TABLE attendance_logs MODIFY distance DECIMAL(8,2) UNSIGNED NOT NULL');
        }
    }
};
