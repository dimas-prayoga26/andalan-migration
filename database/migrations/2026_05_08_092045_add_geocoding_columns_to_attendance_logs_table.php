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
            if (! Schema::hasColumn('attendance_logs', 'address_village')) {
                $table->string('address_village')->nullable()->after('device_hash');
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

            if (! Schema::hasColumn('attendance_logs', 'geocoded_at')) {
                $table->timestamp('geocoded_at')->nullable()->after('address_postal_code');
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
                'address_postal_code',
                'address_province',
                'address_city',
                'address_regency',
                'address_district',
                'address_village',
            ];

            foreach ($columns as $columnName) {
                if (Schema::hasColumn('attendance_logs', $columnName)) {
                    $table->dropColumn($columnName);
                }
            }
        });
    }
};
