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
        Schema::table('business_trip_cash_advances', function (Blueprint $table) {
            $table->date('date_needed_until')->nullable()->after('date_needed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_trip_cash_advances', function (Blueprint $table) {
            $table->dropColumn('date_needed_until');
        });
    }
};
