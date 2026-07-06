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
        Schema::table('office_locations', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('company_id');
            $table->index(['company_id', 'name'], 'office_locations_company_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_locations', function (Blueprint $table): void {
            $table->dropIndex('office_locations_company_name_index');
            $table->dropColumn('name');
        });
    }
};
