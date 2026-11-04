<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->renameTableIfPresent('mail_access_accounts', 'mail_business_accounts');
        $this->renameColumnIfPresent('mail_business_account_sync_states', 'mail_access_account_id', 'mail_business_account_id');
        $this->renameColumnIfPresent('mail_business_messages', 'mail_access_account_id', 'mail_business_account_id');
        $this->renameColumnIfPresent('mail_business_account_takeovers', 'mail_access_account_id', 'mail_business_account_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->renameColumnIfPresent('mail_business_account_takeovers', 'mail_business_account_id', 'mail_access_account_id');
        $this->renameColumnIfPresent('mail_business_messages', 'mail_business_account_id', 'mail_access_account_id');
        $this->renameColumnIfPresent('mail_business_account_sync_states', 'mail_business_account_id', 'mail_access_account_id');
        $this->renameTableIfPresent('mail_business_accounts', 'mail_access_accounts');
    }

    private function renameTableIfPresent(string $from, string $to): void
    {
        if (! Schema::hasTable($from) || Schema::hasTable($to)) {
            return;
        }

        Schema::rename($from, $to);
    }

    private function renameColumnIfPresent(string $table, string $from, string $to): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $from) || Schema::hasColumn($table, $to)) {
            return;
        }

        Schema::table($table, function ($table) use ($from, $to): void {
            $table->renameColumn($from, $to);
        });
    }
};
