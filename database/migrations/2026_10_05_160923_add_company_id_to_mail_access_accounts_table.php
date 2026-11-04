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
            $table->foreignUuid('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies', 'id')
                ->nullOnDelete();

            $table->index(['company_id', 'type', 'is_active'], 'mail_access_accounts_company_type_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_access_accounts', function (Blueprint $table) {
            $table->dropIndex('mail_access_accounts_company_type_active_index');
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
