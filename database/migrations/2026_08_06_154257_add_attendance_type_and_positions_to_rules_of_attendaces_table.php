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
        if (Schema::hasTable('rules_of_attendaces')) {
            Schema::table('rules_of_attendaces', function (Blueprint $table): void {
                $table->index('office_location_id', 'rules_attendance_office_location_id_index');

                try {
                    $table->dropUnique('rules_of_attendaces_office_location_unique');
                } catch (Throwable) {
                    //
                }

                if (! Schema::hasColumn('rules_of_attendaces', 'attendance_type')) {
                    $table->string('attendance_type', 20)->default('fixed')->after('office_reset_time');
                    $table->index(['office_location_id', 'attendance_type', 'is_active'], 'rules_attendance_location_type_active_index');
                }
            });
        }

        if (! Schema::hasTable('attendance_rule_positions')) {
            Schema::create('attendance_rule_positions', function (Blueprint $table): void {
                $table->foreignUuid('attendance_rule_id')->constrained('rules_of_attendaces', 'id')->cascadeOnDelete();
                $table->foreignUuid('position_id')->constrained('positions', 'id')->cascadeOnDelete();
                $table->timestamps();

                $table->primary(['attendance_rule_id', 'position_id'], 'attendance_rule_positions_primary');
                $table->index('position_id', 'attendance_rule_positions_position_id_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_rule_positions');

        if (Schema::hasTable('rules_of_attendaces')) {
            Schema::table('rules_of_attendaces', function (Blueprint $table): void {
                if (Schema::hasColumn('rules_of_attendaces', 'attendance_type')) {
                    try {
                        $table->dropIndex('rules_attendance_location_type_active_index');
                    } catch (Throwable) {
                        //
                    }

                    $table->dropColumn('attendance_type');
                }

                try {
                    $table->unique('office_location_id', 'rules_of_attendaces_office_location_unique');
                } catch (Throwable) {
                    //
                }

                try {
                    $table->dropIndex('rules_attendance_office_location_id_index');
                } catch (Throwable) {
                    //
                }
            });
        }
    }
};
