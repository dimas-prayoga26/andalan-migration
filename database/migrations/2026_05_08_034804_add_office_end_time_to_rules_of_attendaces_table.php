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
        Schema::table('rules_of_attendaces', function (Blueprint $table): void {
            if (! Schema::hasColumn('rules_of_attendaces', 'office_end_time')) {
                $table->time('office_end_time')->default('17:00:00')->after('office_start_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rules_of_attendaces', function (Blueprint $table): void {
            if (Schema::hasColumn('rules_of_attendaces', 'office_end_time')) {
                $table->dropColumn('office_end_time');
            }
        });
    }
};
