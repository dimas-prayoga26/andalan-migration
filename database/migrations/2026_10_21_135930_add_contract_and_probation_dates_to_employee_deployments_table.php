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
        Schema::table('employee_deployments', function (Blueprint $table) {
            $table->boolean('is_contract')->default(false)->after('join_date');
            $table->date('contract_end_date')->nullable()->after('is_contract');
            $table->date('probation_end_date')->nullable()->after('contract_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_deployments', function (Blueprint $table) {
            $table->dropColumn(['is_contract', 'contract_end_date', 'probation_end_date']);
        });
    }
};
