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
        Schema::table('positions', function (Blueprint $table) {
            $table->string('system_key', 100)
                ->nullable()
                ->after('name')
                ->unique();
            $table->boolean('is_protected')
                ->default(false)
                ->after('status')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique(['system_key']);
            $table->dropColumn(['system_key', 'is_protected']);
        });
    }
};
