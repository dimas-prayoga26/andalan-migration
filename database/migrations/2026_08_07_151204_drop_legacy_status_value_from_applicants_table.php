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
        if (! Schema::hasColumn('applicants', 'legacy_status_value')) {
            return;
        }

        Schema::table('applicants', function (Blueprint $table): void {
            $table->dropIndex(['legacy_status_value']);
            $table->dropColumn('legacy_status_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('applicants', 'legacy_status_value')) {
            return;
        }

        Schema::table('applicants', function (Blueprint $table): void {
            $table->unsignedInteger('legacy_status_value')->nullable()->after('slug')->index();
        });
    }
};
