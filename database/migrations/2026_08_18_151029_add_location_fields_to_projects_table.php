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
        Schema::table('projects', function (Blueprint $table) {
            $table->char('province_code', 2)->nullable()->after('client_name');
            $table->char('city_code', 4)->nullable()->after('province_code');
            $table->text('address')->nullable()->after('city_code');

            $table->foreign('province_code')
                ->references('code')
                ->on('indonesia_provinces')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('city_code')
                ->references('code')
                ->on('indonesia_cities')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['province_code']);
            $table->dropForeign(['city_code']);
            $table->dropColumn(['province_code', 'city_code', 'address']);
        });
    }
};
