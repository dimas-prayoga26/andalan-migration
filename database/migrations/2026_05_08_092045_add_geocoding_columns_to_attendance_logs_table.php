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
        Schema::table('attendance_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('attendance_logs', 'location_source')) {
                $table->string('location_source', 20)->nullable()->after('device_hash');
            }

            if (! Schema::hasColumn('attendance_logs', 'formatted_address')) {
                $table->text('formatted_address')->nullable()->after('location_source');
            }

            if (! Schema::hasColumn('attendance_logs', 'address_village')) {
                $table->string('address_village')->nullable()->after('formatted_address');
            }

            if (! Schema::hasColumn('attendance_logs', 'address_district')) {
                $table->string('address_district')->nullable()->after('address_village');
            }

            if (! Schema::hasColumn('attendance_logs', 'address_regency')) {
                $table->string('address_regency')->nullable()->after('address_district');
            }

            if (! Schema::hasColumn('attendance_logs', 'address_city')) {
                $table->string('address_city')->nullable()->after('address_regency');
            }

            if (! Schema::hasColumn('attendance_logs', 'address_province')) {
                $table->string('address_province')->nullable()->after('address_city');
            }

            if (! Schema::hasColumn('attendance_logs', 'address_postal_code')) {
                $table->string('address_postal_code', 20)->nullable()->after('address_province');
            }

            if (! Schema::hasColumn('attendance_logs', 'geocoding_provider')) {
                $table->string('geocoding_provider', 30)->nullable()->after('address_postal_code');
            }

            if (! Schema::hasColumn('attendance_logs', 'geocoded_at')) {
                $table->timestamp('geocoded_at')->nullable()->after('geocoding_provider');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table): void {
            $columns = [
                'geocoded_at',
                'geocoding_provider',
                'address_postal_code',
                'address_province',
                'address_city',
                'address_regency',
                'address_district',
                'address_village',
                'formatted_address',
                'location_source',
            ];

            foreach ($columns as $columnName) {
                if (Schema::hasColumn('attendance_logs', $columnName)) {
                    $table->dropColumn($columnName);
                }
            }
        });
    }
};
