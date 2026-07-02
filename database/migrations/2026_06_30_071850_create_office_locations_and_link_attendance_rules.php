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
        if (! Schema::hasTable('office_locations')) {
            Schema::create('office_locations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('company_id')->constrained('companies', 'id')->cascadeOnDelete();
                $table->text('address')->nullable();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['company_id', 'is_active']);
            });
        }

        if (Schema::hasTable('employee_deployments') && ! Schema::hasColumn('employee_deployments', 'current_office_location_id')) {
            Schema::table('employee_deployments', function (Blueprint $table): void {
                $table->foreignUuid('current_office_location_id')
                    ->nullable()
                    ->after('current_company_id')
                    ->constrained('office_locations', 'id')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('rules_of_attendaces') && ! Schema::hasColumn('rules_of_attendaces', 'office_location_id')) {
            Schema::table('rules_of_attendaces', function (Blueprint $table): void {
                $table->foreignUuid('office_location_id')
                    ->nullable()
                    ->after('companies_id')
                    ->constrained('office_locations', 'id')
                    ->nullOnDelete();
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rules_of_attendaces') && Schema::hasColumn('rules_of_attendaces', 'office_location_id')) {
            Schema::table('rules_of_attendaces', function (Blueprint $table): void {
                $table->dropForeign(['office_location_id']);
                $table->dropColumn('office_location_id');
            });
        }

        if (Schema::hasTable('employee_deployments') && Schema::hasColumn('employee_deployments', 'current_office_location_id')) {
            Schema::table('employee_deployments', function (Blueprint $table): void {
                $table->dropForeign(['current_office_location_id']);
                $table->dropColumn('current_office_location_id');
            });
        }

        Schema::dropIfExists('office_locations');
    }
};
