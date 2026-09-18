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
        Schema::table('project_division_event', function (Blueprint $table) {
            $table->string('folder_id')->nullable()->after('google_drive_url')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_division_event', function (Blueprint $table) {
            $table->dropIndex(['folder_id']);
            $table->dropColumn('folder_id');
        });
    }
};
