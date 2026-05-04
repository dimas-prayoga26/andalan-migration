<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('user_profiles', 'address')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->text('address')->nullable()->after('phone');
            });
        }

        if (Schema::hasTable('user_addresses')) {
            DB::table('user_profiles')
                ->join('user_addresses', 'user_profiles.user_id', '=', 'user_addresses.user_id')
                ->whereNull('user_profiles.address')
                ->whereNotNull('user_addresses.address')
                ->update(['user_profiles.address' => DB::raw('user_addresses.address')]);

            Schema::dropIfExists('user_addresses');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('user_addresses')) {
            Schema::create('user_addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('user_profiles', 'address')) {
            DB::table('user_addresses')->insertUsing(
                ['user_id', 'address', 'created_at', 'updated_at'],
                DB::table('user_profiles')
                    ->select(['user_id', 'address', 'created_at', 'updated_at'])
                    ->whereNotNull('address'),
            );

            Schema::table('user_profiles', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }
};
