<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MailTakeoverSchemaTest extends TestCase
{
    public function test_mail_takeover_and_sync_schema_is_registered(): void
    {
        $mailAccessAccountMigration = File::get(database_path('migrations/2026_10_07_095519_add_employee_id_to_mail_access_accounts_table.php'));
        $syncStateMigration = File::get(database_path('migrations/2026_10_07_095520_create_mail_business_account_sync_states_table.php'));
        $mailMessageMigration = File::get(database_path('migrations/2026_10_07_095520_create_mail_business_messages_table.php'));
        $attachmentMigration = File::get(database_path('migrations/2026_10_07_095521_create_mail_business_message_attachments_table.php'));
        $takeoverMigration = File::get(database_path('migrations/2026_10_07_095522_create_mail_business_account_takeovers_table.php'));
        $catchAllCleanupMigration = File::get(database_path('migrations/2026_10_07_111702_remove_catchall_mail_access_accounts.php'));

        $this->assertStringContainsString("foreignUuid('employee_id')", $mailAccessAccountMigration);
        $this->assertStringContainsString("constrained('employees', 'id')", $mailAccessAccountMigration);

        $this->assertStringContainsString("Schema::create('mail_business_account_sync_states'", $syncStateMigration);
        $this->assertStringContainsString("foreignId('mail_access_account_id')", $syncStateMigration);
        $this->assertStringContainsString("unsignedBigInteger('last_uid')", $syncStateMigration);
        $this->assertStringContainsString("unique(['mail_access_account_id', 'folder']", $syncStateMigration);

        $this->assertStringContainsString("Schema::create('mail_business_messages'", $mailMessageMigration);
        $this->assertStringContainsString("foreignId('mail_access_account_id')", $mailMessageMigration);
        $this->assertStringContainsString("unique(['mail_access_account_id', 'folder', 'uid']", $mailMessageMigration);
        $this->assertStringContainsString("json('to_recipients')", $mailMessageMigration);
        $this->assertStringContainsString("longText('body_html')", $mailMessageMigration);
        $this->assertStringContainsString('softDeletes()', $mailMessageMigration);

        $this->assertStringContainsString("Schema::create('mail_business_message_attachments'", $attachmentMigration);
        $this->assertStringContainsString("foreignId('mail_business_message_id')", $attachmentMigration);
        $this->assertStringContainsString("text('storage_path')", $attachmentMigration);

        $this->assertStringContainsString("Schema::create('mail_business_account_takeovers'", $takeoverMigration);
        $this->assertStringContainsString("foreignId('mail_access_account_id')", $takeoverMigration);
        $this->assertStringContainsString("foreignUuid('source_employee_id')", $takeoverMigration);
        $this->assertStringContainsString("foreignUuid('target_employee_id')", $takeoverMigration);
        $this->assertStringContainsString("boolean('can_read')->default(true)", $takeoverMigration);

        $this->assertStringContainsString("config('mail_inboxes.accounts', [])", $catchAllCleanupMigration);
        $this->assertStringContainsString("str_ends_with((string) \$accountKey, '_hr')", $catchAllCleanupMigration);
        $this->assertStringContainsString("DB::table('mail_access_accounts')", $catchAllCleanupMigration);
        $this->assertStringContainsString('->delete()', $catchAllCleanupMigration);
    }
}
