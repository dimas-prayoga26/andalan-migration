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
        Schema::table('mail_access_accounts', function (Blueprint $table) {
            $table->foreignUuid('employee_id')
                ->nullable()
                ->after('company_id')
                ->constrained('employees', 'id')
                ->nullOnDelete();

            $table->index(['employee_id', 'type', 'is_active'], 'mail_access_accounts_employee_type_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_access_accounts', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropIndex('mail_access_accounts_employee_type_active_index');
            $table->dropColumn('employee_id');
        });
    }
};
