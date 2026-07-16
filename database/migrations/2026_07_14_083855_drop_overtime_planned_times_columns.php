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
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropColumn(['planned_start_time', 'planned_end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtimes', function (Blueprint $table) {
            $table->time('planned_start_time')->nullable()->after('overtime_date');
            $table->time('planned_end_time')->nullable()->after('planned_start_time');
        });
    }
};
