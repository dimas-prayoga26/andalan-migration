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
            if (! Schema::hasColumn('rules_of_attendaces', 'office_start_time')) {
                $table->time('office_start_time')->default('08:00:00')->after('radius');
            }

            if (! Schema::hasColumn('rules_of_attendaces', 'late_grace_minutes')) {
                $table->unsignedInteger('late_grace_minutes')->default(0)->after('office_start_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rules_of_attendaces', function (Blueprint $table): void {
            if (Schema::hasColumn('rules_of_attendaces', 'alpha_threshold_minutes')) {
                $table->dropColumn('alpha_threshold_minutes');
            }

            if (Schema::hasColumn('rules_of_attendaces', 'late_grace_minutes')) {
                $table->dropColumn('late_grace_minutes');
            }

            if (Schema::hasColumn('rules_of_attendaces', 'office_start_time')) {
                $table->dropColumn('office_start_time');
            }
        });
    }
};
