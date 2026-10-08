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

        $catchAllEmails = $this->catchAllInboxEmails();

        if ($catchAllEmails === []) {
            return;
        }

        DB::table('mail_access_accounts')
            ->whereIn('email', $catchAllEmails)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    /**
     * @return array<int, string>
     */
    private function catchAllInboxEmails(): array
    {
        return collect(config('mail_inboxes.accounts', []))
            ->reject(static fn (mixed $account, string|int $accountKey): bool => str_ends_with((string) $accountKey, '_hr'))
            ->map(static fn (mixed $account): string => is_array($account) ? mb_strtolower(trim((string) ($account['username'] ?? ''))) : '')
            ->filter(static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }
};
