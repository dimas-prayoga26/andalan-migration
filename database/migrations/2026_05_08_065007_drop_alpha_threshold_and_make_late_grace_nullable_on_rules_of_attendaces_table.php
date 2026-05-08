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
        if (Schema::hasColumn('rules_of_attendaces', 'alpha_threshold_minutes')) {
            DB::statement(
                'ALTER TABLE `rules_of_attendaces` DROP COLUMN `alpha_threshold_minutes`'
            );
        }

        if (! Schema::hasColumn('rules_of_attendaces', 'late_grace_minutes')) {
            return;
        }

        DB::statement(
            'ALTER TABLE `rules_of_attendaces` MODIFY `late_grace_minutes` INT UNSIGNED NULL DEFAULT NULL'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('rules_of_attendaces', 'late_grace_minutes')) {
            DB::statement(
                'ALTER TABLE `rules_of_attendaces` MODIFY `late_grace_minutes` INT UNSIGNED NOT NULL DEFAULT 60'
            );
        }

        if (! Schema::hasColumn('rules_of_attendaces', 'alpha_threshold_minutes')) {
            DB::statement(
                'ALTER TABLE `rules_of_attendaces` ADD COLUMN `alpha_threshold_minutes` INT UNSIGNED NOT NULL DEFAULT 180 AFTER `late_grace_minutes`'
            );
        }
    }
};
