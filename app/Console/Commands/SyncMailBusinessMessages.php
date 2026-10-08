<?php

namespace App\Console\Commands;

use App\Models\MailAccessAccount;
use App\Models\MailBusinessAccountSyncState;
use App\Models\MailBusinessMessage;
use App\Services\MailInboxService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

#[Signature('mail-business:sync {--account= : Sync one mail access account email} {--folder=inbox : inbox, sent, or all} {--limit=50 : Maximum messages fetched per account folder}')]
#[Description('Synchronize business mail messages from IMAP into the local archive tables')]
class SyncMailBusinessMessages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(MailInboxService $mailInbox): int
    {
        $folderOption = strtolower((string) $this->option('folder'));
        $folders = $folderOption === 'all' ? MailInboxService::FOLDERS : [$folderOption];
        $limit = max(1, min(200, (int) $this->option('limit')));
        $syncedCount = 0;

        if (array_diff($folders, MailInboxService::FOLDERS) !== []) {
            $this->error('Folder harus inbox, sent, atau all.');

            return self::FAILURE;
        }

        $accounts = MailAccessAccount::query()
            ->visibleForMailAccess()
            ->active()
            ->when(
                filled($this->option('account')),
                fn ($query) => $query->where('email', mb_strtolower(trim((string) $this->option('account')))),
            )
            ->orderBy('email')
            ->get(['id', 'email']);

        foreach ($accounts as $account) {
            foreach ($folders as $folder) {
                $syncedCount += $this->syncAccountFolder($mailInbox, $account, $folder, $limit);
            }
        }

        $this->info("Mail business sync selesai. Messages synced: {$syncedCount}.");

        return self::SUCCESS;
    }

    private function syncAccountFolder(MailInboxService $mailInbox, MailAccessAccount $account, string $folder, int $limit): int
    {
        $state = MailBusinessAccountSyncState::query()->updateOrCreate(
            [
                'mail_access_account_id' => $account->id,
                'folder' => $folder,
            ],
            [
                'status' => 'running',
                'last_error' => null,
            ],
        );
        $syncedCount = 0;
        $lastUid = (int) ($state->last_uid ?? 0);

        try {
            foreach ($mailInbox->messagesFor((string) $account->email, $limit, $folder) as $mailHeader) {
                $uid = (int) ($mailHeader['uid'] ?? 0);

                if ($uid <= 0) {
                    continue;
                }

                $message = $mailInbox->messageFor((string) $account->email, (string) $uid, $folder);
                $this->storeMessage($account, $folder, $uid, $message);
                $lastUid = max($lastUid, $uid);
                $syncedCount++;
            }

            $state->update([
                'last_uid' => $lastUid > 0 ? $lastUid : $state->last_uid,
                'last_synced_at' => now(),
                'status' => 'idle',
                'last_error' => null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $state->update([
                'last_synced_at' => now(),
                'status' => 'failed',
                'last_error' => $exception->getMessage(),
            ]);

            $this->warn("Sync gagal untuk {$account->email} ({$folder}): {$exception->getMessage()}");
        }

        return $syncedCount;
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function storeMessage(MailAccessAccount $account, string $folder, int $uid, array $message): void
    {
        $messageDate = $this->parseMailDate((string) ($message['date'] ?? ''));
        $attachments = $message['attachments'] ?? [];
        $mailBusinessMessage = MailBusinessMessage::query()->updateOrCreate(
            [
                'mail_access_account_id' => $account->id,
                'folder' => $folder,
                'uid' => $uid,
            ],
            [
                'message_id' => $this->nullableString($message['message_id'] ?? null, 191),
                'thread_key' => $this->threadKey($message),
                'in_reply_to' => null,
                'subject' => $this->nullableString($message['subject'] ?? null, 500),
                'from_email' => $this->firstEmail((string) ($message['from'] ?? '')),
                'from_name' => $this->displayName((string) ($message['from'] ?? '')),
                'reply_to_email' => null,
                'to_recipients' => $this->emailList((string) ($message['to'] ?? '')),
                'cc_recipients' => [],
                'bcc_recipients' => [],
                'preview' => Str::limit((string) ($message['body'] ?? ''), 240, ''),
                'body_text' => $message['body'] ?? null,
                'body_html' => null,
                'raw_headers' => null,
                'flags' => ['unread' => (bool) ($message['unread'] ?? false)],
                'is_seen' => ! (bool) ($message['unread'] ?? false),
                'is_answered' => false,
                'is_flagged' => false,
                'is_draft' => false,
                'has_attachments' => is_array($attachments) && $attachments !== [],
                'received_at' => $folder === MailInboxService::FOLDER_INBOX ? $messageDate : null,
                'sent_at' => $folder === MailInboxService::FOLDER_SENT ? $messageDate : null,
                'internal_date' => $messageDate,
                'synced_at' => now(),
                'remote_deleted_at' => null,
            ],
        );

        $this->storeAttachments($mailBusinessMessage, is_array($attachments) ? $attachments : []);
    }

    /**
     * @param  list<array<string, mixed>>  $attachments
     */
    private function storeAttachments(MailBusinessMessage $message, array $attachments): void
    {
        foreach ($attachments as $index => $attachment) {
            $message->attachments()->updateOrCreate(
                ['attachment_index' => $index],
                [
                    'filename' => $this->nullableString($attachment['filename'] ?? null, 191),
                    'mime_type' => $this->nullableString($attachment['content_type'] ?? null, 191),
                    'size' => $attachment['size'] ?? null,
                    'content_id' => null,
                    'disposition' => null,
                    'is_inline' => false,
                    'storage_disk' => 'local',
                    'storage_path' => null,
                    'checksum' => null,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function threadKey(array $message): ?string
    {
        $messageId = trim((string) ($message['message_id'] ?? ''));

        if ($messageId !== '') {
            return $this->nullableString($messageId, 191);
        }

        return $this->nullableString(Str::slug((string) ($message['subject'] ?? '')), 191);
    }

    /**
     * @return list<string>
     */
    private function emailList(string $header): array
    {
        if (preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $header, $matches) === false) {
            return [];
        }

        return collect($matches[0] ?? [])
            ->map(static fn (string $email): string => mb_strtolower(trim($email)))
            ->unique()
            ->values()
            ->all();
    }

    private function firstEmail(string $header): ?string
    {
        return $this->emailList($header)[0] ?? null;
    }

    private function displayName(string $header): ?string
    {
        $name = trim(preg_replace('/<[^>]+>/', '', $header) ?? '', " \t\n\r\0\x0B\"'");

        return $name !== '' ? $this->nullableString($name, 191) : null;
    }

    private function parseMailDate(string $date): ?CarbonImmutable
    {
        $timestamp = strtotime($date);

        return $timestamp === false ? null : CarbonImmutable::createFromTimestamp($timestamp);
    }

    private function nullableString(mixed $value, int $limit): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? Str::limit($value, $limit, '') : null;
    }
}
