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
        if (Schema::hasTable('attendance_logs')) {
            if (Schema::hasColumn('attendance_logs', 'distance') && ! Schema::hasColumn('attendance_logs', 'distance_in')) {
                DB::statement('ALTER TABLE attendance_logs CHANGE distance distance_in DECIMAL(12,2) UNSIGNED NOT NULL');
            }

            if (! Schema::hasColumn('attendance_logs', 'distance_out')) {
                Schema::table('attendance_logs', function (Blueprint $table): void {
                    $table->decimal('distance_out', 12, 2)->unsigned()->nullable()->after('distance_in');
                });
            }
        }

        if (Schema::hasTable('rules_of_attendaces') && Schema::hasColumn('rules_of_attendaces', 'late_grace_minutes')) {
            Schema::table('rules_of_attendaces', function (Blueprint $table): void {
                $table->dropColumn('late_grace_minutes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendance_logs')) {
            if (Schema::hasColumn('attendance_logs', 'distance_out')) {
                Schema::table('attendance_logs', function (Blueprint $table): void {
                    $table->dropColumn('distance_out');
                });
            }

            if (Schema::hasColumn('attendance_logs', 'distance_in') && ! Schema::hasColumn('attendance_logs', 'distance')) {
                DB::statement('ALTER TABLE attendance_logs CHANGE distance_in distance DECIMAL(12,2) UNSIGNED NOT NULL');
            }
        }

        if (Schema::hasTable('rules_of_attendaces') && ! Schema::hasColumn('rules_of_attendaces', 'late_grace_minutes')) {
            Schema::table('rules_of_attendaces', function (Blueprint $table): void {
                $table->unsignedInteger('late_grace_minutes')->nullable()->after('office_start_time');
            });
        }
    }
};
