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
        Schema::table('projects', function (Blueprint $table) {
            $table->date('live_event_start_date')->nullable()->after('client_name');
            $table->date('live_event_end_date')->nullable()->after('live_event_start_date');
            $table->index(['live_event_start_date', 'live_event_end_date'], 'projects_live_event_dates_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_live_event_dates_index');
            $table->dropColumn(['live_event_start_date', 'live_event_end_date']);
        });
    }
};
