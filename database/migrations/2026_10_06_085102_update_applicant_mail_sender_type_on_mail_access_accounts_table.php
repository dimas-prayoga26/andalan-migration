<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('mail_access_accounts')) {
            return;
        }

        if (Schema::hasColumn('mail_access_accounts', 'is_applicant_mail_sender')) {
            DB::table('mail_access_accounts')
                ->where('is_applicant_mail_sender', true)
                ->update(['type' => 'applicant_notification']);
        }

        DB::table('mail_access_accounts')
            ->where('email', 'like', 'recruitment@%')
            ->update(['type' => 'applicant_notification']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('mail_access_accounts')) {
            return;
        }

        DB::table('mail_access_accounts')
            ->where('type', 'applicant_notification')
            ->update(['type' => 'department']);
    }
};
