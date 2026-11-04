<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('positions')
            ->where('name', 'Digital Marketing')
            ->whereNull('system_key')
            ->update([
                'system_key' => 'digital_marketing',
                'is_protected' => false,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('positions')
            ->where('system_key', 'digital_marketing')
            ->update([
                'system_key' => null,
                'is_protected' => false,
                'updated_at' => now(),
            ]);
    }
};
