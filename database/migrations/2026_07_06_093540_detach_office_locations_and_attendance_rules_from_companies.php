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
            $table->dropForeign(['company_id']);
            $table->dropIndex('office_locations_company_id_is_active_index');
            $table->dropIndex('office_locations_company_name_index');
            $table->dropColumn('company_id');
            $table->unique('name', 'office_locations_name_unique');
        });

        Schema::table('rules_of_attendaces', function (Blueprint $table): void {
            $table->dropForeign(['companies_id']);
            $table->dropColumn('companies_id');
            $table->unique('office_location_id', 'rules_of_attendaces_office_location_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rules_of_attendaces', function (Blueprint $table): void {
            $table->dropUnique('rules_of_attendaces_office_location_unique');
            $table->foreignUuid('companies_id')
                ->nullable()
                ->constrained('companies', 'id')
                ->nullOnDelete();
        });

        Schema::table('office_locations', function (Blueprint $table): void {
            $table->dropUnique('office_locations_name_unique');
            $table->foreignUuid('company_id')
                ->nullable()
                ->constrained('companies', 'id')
                ->nullOnDelete();
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'name'], 'office_locations_company_name_index');
        });
    }
};
