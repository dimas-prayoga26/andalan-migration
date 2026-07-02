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
        Schema::table('leave_balances', function (Blueprint $table) {
            if (Schema::hasColumn('leave_balances', 'is_monthly_limit_used')) {
                $table->dropColumn('is_monthly_limit_used');
            }

            if (Schema::hasColumn('leave_balances', 'is_montly_limit_user')) {
                $table->dropColumn('is_montly_limit_user');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_balances', 'is_monthly_limit_used')) {
                $table->boolean('is_monthly_limit_used')
                    ->default(false)
                    ->after('remaining_quota');
            }
        });
    }
};
