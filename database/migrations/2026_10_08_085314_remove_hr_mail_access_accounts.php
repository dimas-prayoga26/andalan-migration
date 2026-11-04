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
        $hrEmailPattern = 'hr'.'@%';

        DB::table('mail_access_accounts')
            ->where('email', 'like', $hrEmailPattern)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty. Removed HR SMTP access accounts should not be restored automatically.
    }
};
