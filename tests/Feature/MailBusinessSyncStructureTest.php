<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MailBusinessSyncStructureTest extends TestCase
{
    public function test_mail_business_sync_command_models_and_schedule_are_wired(): void
    {
        $command = File::get(app_path('Console/Commands/SyncMailBusinessMessages.php'));
        $messageModel = File::get(app_path('Models/MailBusinessMessage.php'));
        $attachmentModel = File::get(app_path('Models/MailBusinessMessageAttachment.php'));
        $syncStateModel = File::get(app_path('Models/MailBusinessAccountSyncState.php'));
        $consoleRoutes = File::get(base_path('routes/console.php'));

        $this->assertStringContainsString("#[Signature('mail-business:sync", $command);
        $this->assertStringContainsString('visibleForMailAccess()', $command);
        $this->assertStringContainsString('->active()', $command);
        $this->assertStringContainsString('messagesFor((string) $account->email', $command);
        $this->assertStringContainsString('messageFor((string) $account->email', $command);
        $this->assertStringContainsString('MailBusinessMessage::query()->updateOrCreate', $command);
        $this->assertStringContainsString('MailBusinessAccountSyncState::query()->updateOrCreate', $command);
        $this->assertStringContainsString("'mail_business_account_id' => \$account->id", $command);
        $this->assertStringContainsString("'to_recipients' => \$this->emailList", $command);
        $this->assertStringContainsString("'body_text' => \$message['body'] ?? null", $command);
        $this->assertStringContainsString('public function attachments(): HasMany', $messageModel);
        $this->assertStringContainsString('use SoftDeletes;', $messageModel);
        $this->assertStringContainsString("protected \$table = 'mail_business_messages';", $messageModel);
        $this->assertStringContainsString("protected \$table = 'mail_business_message_attachments';", $attachmentModel);
        $this->assertStringContainsString('public function message(): BelongsTo', $attachmentModel);
        $this->assertStringContainsString("protected \$table = 'mail_business_account_sync_states';", $syncStateModel);
        $this->assertStringContainsString('public function mailBusinessAccount(): BelongsTo', $syncStateModel);
        $this->assertStringContainsString('public function mailAccessAccount(): BelongsTo', $syncStateModel);
        $this->assertStringContainsString("Schedule::command('mail-business:sync --folder=inbox --limit=50')", $consoleRoutes);
        $this->assertStringContainsString('->everyFiveMinutes()', $consoleRoutes);
    }
}
