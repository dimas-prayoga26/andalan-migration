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
        if (! Schema::hasColumn('mail_access_accounts', 'is_applicant_mail_sender')) {
            return;
        }

        Schema::table('mail_access_accounts', function (Blueprint $table) {
            $table->dropColumn('is_applicant_mail_sender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('mail_access_accounts', 'is_applicant_mail_sender')) {
            return;
        }

        Schema::table('mail_access_accounts', function (Blueprint $table) {
            $table->boolean('is_applicant_mail_sender')
                ->default(false)
                ->after('type')
                ->index();
        });
    }
};
