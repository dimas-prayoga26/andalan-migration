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
        if (! Schema::hasTable('attendances') || ! Schema::hasColumn('attendances', 'status')) {
            return;
        }

        DB::table('attendances')
            ->where('status', 'on_time')
            ->update(['status' => 'Masuk']);

        DB::table('attendances')
            ->where('status', 'late')
            ->update(['status' => 'Terlambat']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('attendances') || ! Schema::hasColumn('attendances', 'status')) {
            return;
        }

        DB::table('attendances')
            ->where('status', 'Masuk')
            ->update(['status' => 'on_time']);

        DB::table('attendances')
            ->where('status', 'Terlambat')
            ->update(['status' => 'late']);
    }
};
