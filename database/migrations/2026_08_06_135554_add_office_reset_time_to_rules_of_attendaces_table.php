<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('rules_of_attendaces') || Schema::hasColumn('rules_of_attendaces', 'office_reset_time')) {
            return;
        }

        Schema::table('rules_of_attendaces', function (Blueprint $table): void {
            $table->time('office_reset_time')->default('00:00:00')->after('office_end_time');
        });

        DB::table('rules_of_attendaces')
            ->where('office_start_time', '09:00:00')
            ->update(['office_reset_time' => '04:00:00']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('rules_of_attendaces') || ! Schema::hasColumn('rules_of_attendaces', 'office_reset_time')) {
            return;
        }

        Schema::table('rules_of_attendaces', function (Blueprint $table): void {
            $table->dropColumn('office_reset_time');
        });
    }
};
