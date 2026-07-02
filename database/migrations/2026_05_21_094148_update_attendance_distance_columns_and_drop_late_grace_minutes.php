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
        if (Schema::hasTable('rules_of_attendaces') && ! Schema::hasColumn('rules_of_attendaces', 'late_grace_minutes')) {
            Schema::table('rules_of_attendaces', function (Blueprint $table): void {
                $table->unsignedInteger('late_grace_minutes')->nullable()->after('office_start_time');
            });
        }
    }
};
