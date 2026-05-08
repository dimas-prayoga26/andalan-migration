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
        if (! Schema::hasColumn('rules_of_attendaces', 'late_grace_minutes')) {
            return;
        }

        DB::statement(
            'ALTER TABLE `rules_of_attendaces` MODIFY `late_grace_minutes` INT UNSIGNED NOT NULL DEFAULT 60'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('rules_of_attendaces', 'late_grace_minutes')) {
            return;
        }

        DB::statement(
            'ALTER TABLE `rules_of_attendaces` MODIFY `late_grace_minutes` INT UNSIGNED NOT NULL DEFAULT 0'
        );
    }
};
