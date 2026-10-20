<?php

namespace App\Services;

use RuntimeException;
use ValueError;

class MailInboxService
{
    public const FOLDER_INBOX = 'inbox';

    public const FOLDER_SENT = 'sent';

    public const FOLDERS = [self::FOLDER_INBOX, self::FOLDER_SENT];

    private const SENT_MAILBOX_NAMES = ['Sent', 'Sent Items', 'Sent Messages', 'Sent Mail'];

    private const TRASH_MAILBOX_NAMES = ['Trash', 'Deleted Items', 'Deleted Messages'];

    private const THREAD_MESSAGE_LIMIT = 20;

    private const THREAD_SEARCH_ID_LIMIT = 10;

    /**
     * @return list<array{uid: string, from: string, to: string, subject: string, date: string, time: string, unread: bool, thread_count: int}>
     */
    public function messagesFor(string $email, int $limit = 20, string $folder = self::FOLDER_INBOX): array
    {
        return $this->withClient($email, function (SimpleImapClient $client, ?string $accountEmailFilter) use ($folder, $limit): array {
            $mailbox = $this->mailboxFor($client, $folder);

            if ($mailbox === null) {
                return [];
            }

            $client->selectMailbox($mailbox);

            return $client->latestMessages($limit, $accountEmailFilter, $this->accountEmailFilterMode($folder));
        });
    }

    /**
     * @return array{uid: string, from: string, to: string, subject: string, date: string, time: string, unread: bool, body: string, attachments: list<array{id: string, filename: string, content_type: string, size: int, is_image: bool}>, message_id: string, references: string}
     */
    public function messageFor(string $email, string $uid, string $folder = self::FOLDER_INBOX): array
    {
        return $this->withClient($email, function (SimpleImapClient $client, ?string $accountEmailFilter) use ($folder, $uid): array {
            $client->selectMailbox($this->existingMailboxFor($client, $folder));

            return $client->message($uid, $accountEmailFilter, $this->accountEmailFilterMode($folder));
        });
    }

    /**
     * The conversation the given message belongs to, collected from Inbox and Sent through its
     * Message-ID / In-Reply-To / References headers, oldest first. The given message is always part of it.
     *
     * @param  array<string, mixed>  $message
     * @return list<array<string, mixed>> Messages with their 'folder' and an 'is_current' flag.
     */
    public function threadFor(string $email, array $message, string $folder): array
    {
        $currentMessage = [...$message, 'folder' => $folder, 'is_current' => true];
        $threadMessageIds = $this->threadMessageIds($message);

        if ($threadMessageIds === []) {
            return [$currentMessage];
        }

        $relatedMessages = $this->withClient($email, function (SimpleImapClient $client, ?string $accountEmailFilter) use ($threadMessageIds): array {
            $relatedMessages = [];

            foreach (self::FOLDERS as $threadFolder) {
                $mailbox = $this->mailboxFor($client, $threadFolder);

                if ($mailbox === null) {
                    continue;
                }

                $client->selectMailbox($mailbox);

                foreach (array_slice(array_reverse($client->threadUids($threadMessageIds)), 0, self::THREAD_MESSAGE_LIMIT) as $uid) {
                    try {
                        $relatedMessage = $client->message($uid, $accountEmailFilter, $this->accountEmailFilterMode($threadFolder));
                    } catch (RuntimeException) {
                        // Messages outside the account's own view are not part of its conversation.
                        continue;
                    }

                    $relatedMessages[] = [...$relatedMessage, 'folder' => $threadFolder, 'is_current' => false];
                }
            }

            return $relatedMessages;
        });

        return $this->sortedThreadMessages($currentMessage, $relatedMessages);
    }

    /**
     * @return array{id: string, filename: string, content_type: string, size: int, is_image: bool, content: string}
     */
    public function attachmentFor(string $email, string $uid, string $attachmentId, string $folder = self::FOLDER_INBOX): array
    {
        return $this->withClient($email, function (SimpleImapClient $client, ?string $accountEmailFilter) use ($attachmentId, $folder, $uid): array {
            $client->selectMailbox($this->existingMailboxFor($client, $folder));

            return $client->attachment($uid, $attachmentId, $accountEmailFilter, $this->accountEmailFilterMode($folder));
        });
    }

    /**
     * Move the given messages to Trash, or delete them permanently when no Trash mailbox exists.
     *
     * @param  list<string>  $uids
     */
    public function deleteMessages(string $email, array $uids, string $folder = self::FOLDER_INBOX): void
    {
        $uids = array_values(array_filter($uids, static fn (string $uid): bool => ctype_digit($uid)));

        if ($uids === []) {
            return;
        }

        $this->withClient($email, function (SimpleImapClient $client, ?string $accountEmailFilter) use ($folder, $uids): void {
            $mailbox = $this->existingMailboxFor($client, $folder);
            $trashMailbox = $client->specialMailbox('\\Trash', self::TRASH_MAILBOX_NAMES);

            $client->selectMailbox($mailbox);
            $client->deleteMessages($uids, $trashMailbox === $mailbox ? null : $trashMailbox, $accountEmailFilter, $this->accountEmailFilterMode($folder));
        });
    }

    /**
     * Mark every unread message of the folder as read and return how many messages were marked.
     */
    public function markAllAsRead(string $email, string $folder = self::FOLDER_INBOX): int
    {
        return $this->withClient($email, function (SimpleImapClient $client, ?string $accountEmailFilter) use ($folder): int {
            $client->selectMailbox($this->existingMailboxFor($client, $folder));

            return $client->markAllAsSeen($accountEmailFilter, $this->accountEmailFilterMode($folder));
        });
    }

    /**
     * Save a copy of an outgoing message into the Sent mailbox, creating it when missing.
     */
    public function storeSentMessage(string $email, string $rawMessage): void
    {
        $this->withClient($email, function (SimpleImapClient $client) use ($rawMessage): void {
            $sentMailbox = $client->specialMailbox('\\Sent', self::SENT_MAILBOX_NAMES);

            if ($sentMailbox === null) {
                $sentMailbox = $client->defaultMailboxName('Sent');
                $client->createMailbox($sentMailbox);
            }

            $client->appendMessage($sentMailbox, $rawMessage);
        });
    }

    /**
     * @template TResult
     *
     * @param  callable(SimpleImapClient, string|null): TResult  $callback
     * @return TResult
     */
    private function withClient(string $email, callable $callback): mixed
    {
        $email = mb_strtolower(trim($email));
        $account = $this->accountFor($email);

        if ($account === null) {
            throw new RuntimeException('Konfigurasi inbox untuk email ini belum tersedia.');
        }

        $client = new SimpleImapClient(
            host: (string) $account['host'],
            port: (int) $account['port'],
            encryption: (string) $account['encryption'],
            username: (string) $account['username'],
            password: (string) $account['password'],
            verifySsl: filter_var($account['verify_ssl'] ?? true, FILTER_VALIDATE_BOOLEAN),
        );

        try {
            $client->login();

            return $callback($client, $this->recipientFilterFor($email, $account));
        } finally {
            $client->logout();
        }
    }

    private function mailboxFor(SimpleImapClient $client, string $folder): ?string
    {
        return match ($folder) {
            self::FOLDER_SENT => $client->specialMailbox('\\Sent', self::SENT_MAILBOX_NAMES),
            default => 'INBOX',
        };
    }

    private function existingMailboxFor(SimpleImapClient $client, string $folder): string
    {
        return $this->mailboxFor($client, $folder)
            ?? throw new RuntimeException('Folder email tidak ditemukan.');
    }

    /**
     * @return array{host: string|null, port: int|string, encryption: string, username: string|null, password: string|null, verify_ssl?: bool|string}|null
     */
    private function accountFor(string $email): ?array
    {
        $email = mb_strtolower(trim($email));

        foreach (config('mail_inboxes.accounts', []) as $account) {
            if (mb_strtolower((string) ($account['username'] ?? '')) !== $email) {
                continue;
            }

            if (blank($account['host'] ?? null) || blank($account['username'] ?? null) || blank($account['password'] ?? null)) {
                return null;
            }

            return $account;
        }

        $emailDomain = $this->emailDomain($email);

        foreach (config('mail_inboxes.accounts', []) as $account) {
            if ($emailDomain === '' || $this->emailDomain((string) ($account['username'] ?? '')) !== $emailDomain) {
                continue;
            }

            if (blank($account['host'] ?? null) || blank($account['username'] ?? null) || blank($account['password'] ?? null)) {
                return null;
            }

            return $account;
        }

        return null;
    }

    private function emailDomain(string $email): string
    {
        $parts = explode('@', mb_strtolower(trim($email)));

        if (count($parts) !== 2) {
            return '';
        }

        return preg_replace('/^www\./', '', $parts[1]) ?? '';
    }

    /**
     * When the configured IMAP username is a catchall account for the same domain,
     * restrict visible messages to the business email that logged in.
     *
     * @param  array{username: string|null}  $account
     */
    private function recipientFilterFor(string $email, array $account): ?string
    {
        $username = mb_strtolower(trim((string) ($account['username'] ?? '')));

        return $username !== $email ? $email : null;
    }

    private function accountEmailFilterMode(string $folder): string
    {
        return $folder === self::FOLDER_SENT ? SimpleImapClient::EMAIL_FILTER_SENDER : SimpleImapClient::EMAIL_FILTER_RECIPIENT;
    }

    /**
     * Message-IDs that identify the conversation: the thread root, the latest references and the message itself.
     *
     * @param  array<string, mixed>  $message
     * @return list<string>
     */
    private function threadMessageIds(array $message): array
    {
        $headerValues = implode(' ', [
            (string) ($message['references'] ?? ''),
            (string) ($message['in_reply_to'] ?? ''),
            (string) ($message['message_id'] ?? ''),
        ]);

        preg_match_all('/<[^<>\s"\\\\]+>/', $headerValues, $matches);
        $messageIds = array_values(array_unique($matches[0]));

        if (count($messageIds) <= self::THREAD_SEARCH_ID_LIMIT) {
            return $messageIds;
        }

        return array_values(array_unique([$messageIds[0], ...array_slice($messageIds, -(self::THREAD_SEARCH_ID_LIMIT - 1))]));
    }

    /**
     * Drop duplicates (the current message wins), keep the newest messages within the limit, and order oldest first.
     *
     * @param  array<string, mixed>  $currentMessage
     * @param  list<array<string, mixed>>  $relatedMessages
     * @return list<array<string, mixed>>
     */
    private function sortedThreadMessages(array $currentMessage, array $relatedMessages): array
    {
        $threadKey = static fn (array $threadMessage): string => trim((string) ($threadMessage['message_id'] ?? '')) !== ''
            ? mb_strtolower(trim((string) $threadMessage['message_id']))
            : $threadMessage['folder'].':'.$threadMessage['uid'];
        $messageTimestamp = static fn (array $threadMessage): int => strtotime((string) ($threadMessage['date'] ?? '')) ?: 0;

        $uniqueMessages = [$threadKey($currentMessage) => $currentMessage];

        foreach ($relatedMessages as $relatedMessage) {
            $isCurrentMessage = $relatedMessage['folder'] === $currentMessage['folder'] && (string) $relatedMessage['uid'] === (string) $currentMessage['uid'];

            if (! $isCurrentMessage) {
                $uniqueMessages[$threadKey($relatedMessage)] ??= $relatedMessage;
            }
        }

        $otherMessages = array_values(array_filter($uniqueMessages, static fn (array $threadMessage): bool => ! $threadMessage['is_current']));
        usort($otherMessages, static fn (array $first, array $second): int => $messageTimestamp($first) <=> $messageTimestamp($second));

        $threadMessages = [...array_slice($otherMessages, -(self::THREAD_MESSAGE_LIMIT - 1)), $currentMessage];
        usort($threadMessages, static fn (array $first, array $second): int => $messageTimestamp($first) <=> $messageTimestamp($second));

        return $threadMessages;
    }
}

class SimpleImapClient
{
    public const EMAIL_FILTER_RECIPIENT = 'recipient';

    public const EMAIL_FILTER_SENDER = 'sender';

    private const RECIPIENT_HEADERS = [
        'To',
        'Cc',
        'Bcc',
        'Delivered-To',
        'X-Original-To',
        'Envelope-To',
        'Apparently-To',
    ];

    /** @var resource|null */
    private $stream = null;

    private int $tagNumber = 1;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $encryption,
        private readonly string $username,
        private readonly string $password,
        private readonly bool $verifySsl = true,
    ) {}

    public function login(): void
    {
        $scheme = $this->encryption === 'ssl' ? 'ssl' : 'tcp';
        $target = sprintf('%s://%s:%d', $scheme, $this->host, $this->port);
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => $this->verifySsl,
                'verify_peer_name' => $this->verifySsl,
                'peer_name' => $this->host,
            ],
        ]);
        $stream = @stream_socket_client($target, $errno, $error, 15, STREAM_CLIENT_CONNECT, $context);

        if ($stream === false) {
            throw new RuntimeException("Tidak bisa konek ke IMAP server: {$error}");
        }

        stream_set_timeout($stream, 15);
        $this->stream = $stream;
        $this->readLine();

        if ($this->encryption === 'tls') {
            $this->command('STARTTLS');
            stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        }

        $this->command(sprintf('LOGIN %s %s', $this->quote($this->username), $this->quote($this->password)));
    }

    public function selectInbox(): void
    {
        $this->selectMailbox('INBOX');
    }

    public function selectMailbox(string $mailbox): void
    {
        $this->command('SELECT '.$this->quote($mailbox));
    }

    /**
     * Find a mailbox by its special-use attribute (RFC 6154), falling back to common folder names.
     *
     * @param  list<string>  $fallbackNames
     */
    public function specialMailbox(string $attribute, array $fallbackNames): ?string
    {
        return $this->matchSpecialMailbox($this->mailboxes(), $attribute, $fallbackNames);
    }

    /**
     * Build a top-level mailbox name that follows the server namespace, e.g. "INBOX.Sent" on cPanel.
     */
    public function defaultMailboxName(string $name): string
    {
        foreach ($this->mailboxes() as $mailbox) {
            $delimiter = $mailbox['delimiter'];

            if ($delimiter !== '' && str_starts_with(mb_strtoupper($mailbox['name']), 'INBOX'.$delimiter)) {
                return 'INBOX'.$delimiter.$name;
            }
        }

        return $name;
    }

    public function createMailbox(string $mailbox): void
    {
        $this->command('CREATE '.$this->quote($mailbox));
        $this->command('SUBSCRIBE '.$this->quote($mailbox), false);
    }

    public function appendMessage(string $mailbox, string $rawMessage): void
    {
        $rawMessage = preg_replace("/\r?\n/", "\r\n", $rawMessage) ?? $rawMessage;
        $tag = $this->nextTag();

        $this->write(sprintf("%s APPEND %s (\\Seen) {%d}\r\n", $tag, $this->quote($mailbox), strlen($rawMessage)));

        while (($line = $this->readLine()) !== null) {
            if (str_starts_with($line, '+')) {
                break;
            }

            if (str_starts_with($line, "{$tag} ")) {
                throw new RuntimeException('Perintah IMAP gagal: '.$line);
            }
        }

        $this->write($rawMessage."\r\n");
        $this->readTaggedResponse($tag, true);
    }

    /**
     * Delete messages from the selected mailbox, copying them to Trash first when given.
     *
     * @param  list<string>  $uids
     */
    public function deleteMessages(array $uids, ?string $trashMailbox, ?string $accountEmailFilter = null, string $accountEmailFilterMode = self::EMAIL_FILTER_RECIPIENT): void
    {
        if ($accountEmailFilter !== null) {
            $uids = array_values(array_filter(
                $uids,
                fn (string $uid): bool => $this->messageUidBelongsToEmail($uid, $accountEmailFilter, $accountEmailFilterMode),
            ));
        }

        if ($uids === []) {
            return;
        }

        $uidSet = implode(',', $uids);

        if ($trashMailbox !== null) {
            $this->command(sprintf('UID COPY %s %s', $uidSet, $this->quote($trashMailbox)));
        }

        $this->command(sprintf('UID STORE %s +FLAGS.SILENT (\\Deleted)', $uidSet));
        $this->command($this->hasCapability('UIDPLUS') ? "UID EXPUNGE {$uidSet}" : 'EXPUNGE');
    }

    /**
     * Flag every unread message of the selected mailbox that the account can see as read.
     */
    public function markAllAsSeen(?string $accountEmailFilter = null, string $accountEmailFilterMode = self::EMAIL_FILTER_RECIPIENT): int
    {
        $uids = $this->searchUids('UNSEEN');

        if ($accountEmailFilter !== null) {
            $uids = array_values(array_filter(
                $uids,
                fn (string $uid): bool => $this->fetchHeader($uid, $accountEmailFilter, $accountEmailFilterMode) !== null,
            ));
        }

        foreach (array_chunk($uids, 200) as $uidChunk) {
            $this->command(sprintf('UID STORE %s +FLAGS.SILENT (\\Seen)', implode(',', $uidChunk)));
        }

        return count($uids);
    }

    /**
     * @return list<array{uid: string, from: string, to: string, subject: string, date: string, time: string, unread: bool, thread_count: int}>
     */
    public function latestMessages(int $limit, ?string $accountEmailFilter = null, string $accountEmailFilterMode = self::EMAIL_FILTER_RECIPIENT): array
    {
        $uids = array_reverse($this->searchUids('ALL'));
        $messages = [];

        foreach ($uids as $uid) {
            $message = $this->fetchHeader($uid, $accountEmailFilter, $accountEmailFilterMode);

            if ($message === null) {
                continue;
            }

            $messages[] = $message;

            if (count($messages) >= $limit) {
                break;
            }
        }

        return $messages;
    }

    /**
     * @return array{uid: string, from: string, to: string, subject: string, date: string, time: string, unread: bool, body: string, html_body: ?string, attachments: list<array{id: string, filename: string, content_type: string, size: int, is_image: bool, content_id: ?string, is_inline: bool}>, message_id: string, in_reply_to: string, references: string}
     */
    public function message(string $uid, ?string $accountEmailFilter = null, string $accountEmailFilterMode = self::EMAIL_FILTER_RECIPIENT): array
    {
        $rawMessage = $this->fetchRawMessage($uid);
        [$rawHeaders, $rawBody] = $this->splitRawMessage($rawMessage);

        if ($accountEmailFilter !== null && ! $this->messageBelongsToEmail($rawHeaders, $accountEmailFilter, $accountEmailFilterMode)) {
            throw new RuntimeException('Pesan email tidak ditemukan.');
        }

        $date = $this->headerValue($rawHeaders, 'Date') ?: '';
        $content = $this->messageContent($rawHeaders, $rawBody);

        return [
            'uid' => $uid,
            'from' => $this->decodeHeader($this->headerValue($rawHeaders, 'From') ?: 'Unknown Sender'),
            'to' => $this->decodeHeader($this->headerValue($rawHeaders, 'To') ?: ''),
            'subject' => $this->decodeHeader($this->headerValue($rawHeaders, 'Subject') ?: '(No Subject)'),
            'date' => $date,
            'time' => $this->formatDate($date),
            'unread' => ! str_contains($rawMessage, '\\Seen'),
            'body' => $content['body'],
            'html_body' => $content['html_body'],
            'attachments' => $content['attachments'],
            'message_id' => $this->headerValue($rawHeaders, 'Message-ID') ?: '',
            'in_reply_to' => $this->headerValue($rawHeaders, 'In-Reply-To') ?: '',
            'references' => $this->headerValue($rawHeaders, 'References') ?: '',
        ];
    }

    /**
     * UIDs in the selected mailbox that belong to the conversation of the given Message-IDs:
     * the messages themselves plus every message that replies to or references one of them.
     *
     * @param  list<string>  $messageIds
     * @return list<string>
     */
    public function threadUids(array $messageIds): array
    {
        $searchKey = $this->threadSearchKey($messageIds);

        return $searchKey === null ? [] : $this->searchUids($searchKey);
    }

    /**
     * @return array{id: string, filename: string, content_type: string, size: int, is_image: bool, content_id: ?string, is_inline: bool, content: string}
     */
    public function attachment(string $uid, string $attachmentId, ?string $accountEmailFilter = null, string $accountEmailFilterMode = self::EMAIL_FILTER_RECIPIENT): array
    {
        $rawMessage = $this->fetchRawMessage($uid);
        [$rawHeaders, $rawBody] = $this->splitRawMessage($rawMessage);

        if ($accountEmailFilter !== null && ! $this->messageBelongsToEmail($rawHeaders, $accountEmailFilter, $accountEmailFilterMode)) {
            throw new RuntimeException('Attachment tidak ditemukan.');
        }

        $content = $this->messageContent($rawHeaders, $rawBody, true);

        foreach ($content['attachments'] as $attachment) {
            if ($attachment['id'] === $attachmentId) {
                return $attachment;
            }
        }

        throw new RuntimeException('Attachment tidak ditemukan.');
    }

    public function logout(): void
    {
        if ($this->stream === null) {
            return;
        }

        try {
            $this->command('LOGOUT', false);
        } finally {
            fclose($this->stream);
            $this->stream = null;
        }
    }

    /**
     * @return array{uid: string, from: string, to: string, subject: string, date: string, time: string, unread: bool, thread_count: int}
     */
    private function fetchHeader(string $uid, ?string $accountEmailFilter = null, string $accountEmailFilterMode = self::EMAIL_FILTER_RECIPIENT): ?array
    {
        $lines = $this->command(sprintf('UID FETCH %s (UID FLAGS BODY.PEEK[HEADER.FIELDS (FROM TO CC BCC DELIVERED-TO X-ORIGINAL-TO ENVELOPE-TO APPARENTLY-TO SUBJECT DATE MESSAGE-ID IN-REPLY-TO REFERENCES)])', $uid));
        $raw = implode("\n", $lines);

        if ($accountEmailFilter !== null && ! $this->messageBelongsToEmail($raw, $accountEmailFilter, $accountEmailFilterMode)) {
            return null;
        }

        return [
            'uid' => $uid,
            'from' => $this->decodeHeader($this->headerValue($raw, 'From') ?: 'Unknown Sender'),
            'to' => $this->decodeHeader($this->headerValue($raw, 'To') ?: ''),
            'subject' => $this->decodeHeader($this->headerValue($raw, 'Subject') ?: '(No Subject)'),
            'date' => $this->headerValue($raw, 'Date') ?: '',
            'time' => $this->formatDate($this->headerValue($raw, 'Date') ?: ''),
            'unread' => ! str_contains($raw, '\\Seen'),
            'thread_count' => $this->threadCount($raw),
        ];
    }

    /**
     * How many messages the conversation holds up to this one: the message itself plus every earlier
     * message listed in its In-Reply-To / References chain.
     */
    private function threadCount(string $rawHeaders): int
    {
        $messageId = mb_strtolower(trim((string) $this->headerValue($rawHeaders, 'Message-ID')));
        $chain = ($this->headerValue($rawHeaders, 'References') ?: '').' '.($this->headerValue($rawHeaders, 'In-Reply-To') ?: '');

        preg_match_all('/<[^<>\s"\\\\]+>/', $chain, $matches);
        $earlierMessageIds = array_diff(array_unique(array_map('mb_strtolower', $matches[0])), [$messageId]);

        return count($earlierMessageIds) + 1;
    }

    private function messageUidBelongsToEmail(string $uid, string $email, string $mode): bool
    {
        try {
            $rawMessage = $this->fetchRawMessage($uid);
            [$rawHeaders] = $this->splitRawMessage($rawMessage);

            return $this->messageBelongsToEmail($rawHeaders, $email, $mode);
        } catch (RuntimeException) {
            return false;
        }
    }

    private function fetchRawMessage(string $uid): string
    {
        $lines = $this->command(sprintf('UID FETCH %s (BODY.PEEK[])', $uid));
        $messageLines = [];
        $isReadingLiteral = false;

        foreach ($lines as $line) {
            if (! $isReadingLiteral && preg_match('/^\* \d+ FETCH .*BODY\[\]\s+\{\d+\}/', $line) === 1) {
                $isReadingLiteral = true;

                continue;
            }

            if (! $isReadingLiteral) {
                continue;
            }

            if ($line === ')' || preg_match('/^A\d{4} /', $line) === 1) {
                break;
            }

            $messageLines[] = $line;
        }

        if ($messageLines === []) {
            throw new RuntimeException('Isi email tidak ditemukan.');
        }

        return implode("\n", $messageLines);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitRawMessage(string $rawMessage): array
    {
        $parts = preg_split("/\n\s*\n/", $rawMessage, 2);

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }

    /**
     * @return array{body: string, html_body: ?string, attachments: list<array{id: string, filename: string, content_type: string, size: int, is_image: bool, content_id: ?string, is_inline: bool, content?: string}>}
     */
    private function messageContent(string $rawHeaders, string $rawBody, bool $includeAttachmentContent = false): array
    {
        $plainBody = null;
        $htmlBody = null;
        $htmlSource = null;
        $attachments = [];

        $this->collectParts($rawHeaders, $rawBody, $plainBody, $htmlBody, $htmlSource, $attachments, $includeAttachmentContent);

        return [
            'body' => trim((string) ($plainBody ?? $htmlBody ?? '')),
            'html_body' => $htmlSource === null || trim($htmlSource) === '' ? null : $htmlSource,
            'attachments' => $attachments,
        ];
    }

    /**
     * @param  list<array{id: string, filename: string, content_type: string, size: int, is_image: bool, content_id: ?string, is_inline: bool, content?: string}>  $attachments
     */
    private function collectParts(
        string $headers,
        string $body,
        ?string &$plainBody,
        ?string &$htmlBody,
        ?string &$htmlSource,
        array &$attachments,
        bool $includeAttachmentContent
    ): void {
        $contentTypeHeader = $this->headerValue($headers, 'Content-Type') ?: 'text/plain';
        $contentType = mb_strtolower($contentTypeHeader);
        $dispositionHeader = $this->headerValue($headers, 'Content-Disposition') ?: '';
        $disposition = mb_strtolower($dispositionHeader);

        if (preg_match('/boundary="?([^";]+)"?/i', $contentTypeHeader, $matches) === 1) {
            foreach ($this->splitMultipartBody($body, $matches[1]) as $part) {
                [$partHeaders, $partBody] = $this->splitRawMessage($part);
                $this->collectParts($partHeaders, $partBody, $plainBody, $htmlBody, $htmlSource, $attachments, $includeAttachmentContent);
            }

            return;
        }

        $filename = $this->filenameFromHeaders($headers);
        $baseContentType = trim(strtok($contentType, ';') ?: 'application/octet-stream');
        $contentId = trim((string) ($this->headerValue($headers, 'Content-ID') ?: ''), " \t<>");
        $isInline = $contentId !== '' && ! str_contains($disposition, 'attachment');
        $isAttachment = $filename !== null
            || str_contains($disposition, 'attachment')
            || ($contentId !== '' && ! str_starts_with($baseContentType, 'text/'));
        $decodedBody = $this->decodeTransferBody($headers, $body);

        if ($isAttachment) {
            $attachmentId = (string) (count($attachments) + 1);
            $attachment = [
                'id' => $attachmentId,
                'filename' => $filename ?: "attachment-{$attachmentId}",
                'content_type' => $baseContentType,
                'size' => strlen($decodedBody),
                'is_image' => str_starts_with($baseContentType, 'image/'),
                'content_id' => $contentId === '' ? null : $contentId,
                'is_inline' => $isInline,
            ];

            if ($includeAttachmentContent) {
                $attachment['content'] = $decodedBody;
            }

            $attachments[] = $attachment;

            return;
        }

        $decodedBody = $this->convertToUtf8($decodedBody, $this->headerParameter($contentTypeHeader, 'charset'));

        if (str_contains($baseContentType, 'text/plain') && $plainBody === null) {
            $plainBody = $this->cleanBody($decodedBody, false);
        }

        if (str_contains($baseContentType, 'text/html') && $htmlBody === null) {
            $htmlSource = $decodedBody;
            $htmlBody = $this->cleanBody($decodedBody, true);
        }
    }

    /**
     * Convert a text part to UTF-8 using its declared charset, keeping the original bytes for unknown charsets.
     */
    private function convertToUtf8(string $body, ?string $charset): string
    {
        $charset = $charset === null ? '' : strtoupper(trim($charset, " \t\"'"));

        if ($charset === '' || in_array($charset, ['UTF-8', 'UTF8', 'US-ASCII', 'ASCII'], true)) {
            return $body;
        }

        try {
            return mb_convert_encoding($body, 'UTF-8', $charset);
        } catch (ValueError) {
            return $body;
        }
    }

    /**
     * @return list<string>
     */
    private function splitMultipartBody(string $body, string $boundary): array
    {
        $parts = [];
        $segments = explode('--'.$boundary, $body);
        array_shift($segments);

        foreach ($segments as $segment) {
            $segment = trim($segment);

            if ($segment === '' || str_starts_with($segment, '--')) {
                continue;
            }

            $parts[] = $segment;
        }

        return $parts;
    }

    private function filenameFromHeaders(string $headers): ?string
    {
        $disposition = $this->headerValue($headers, 'Content-Disposition') ?: '';
        $contentType = $this->headerValue($headers, 'Content-Type') ?: '';
        $filename = $this->headerParameter($disposition, 'filename*')
            ?? $this->headerParameter($disposition, 'filename')
            ?? $this->headerParameter($contentType, 'name*')
            ?? $this->headerParameter($contentType, 'name');

        return $filename === null ? null : $this->decodeHeader($filename);
    }

    private function headerParameter(string $header, string $parameter): ?string
    {
        if (preg_match('/(?:^|;)\s*'.preg_quote($parameter, '/').'=("[^"]+"|[^;]+)/i', $header, $matches) !== 1) {
            return null;
        }

        $value = trim($matches[1], " \t\"");

        if (str_ends_with($parameter, '*') && str_contains($value, "''")) {
            [, $value] = explode("''", $value, 2);
            $value = rawurldecode($value);
        }

        return $value;
    }

    private function decodeTransferBody(string $headers, string $body): string
    {
        $encoding = mb_strtolower($this->headerValue($headers, 'Content-Transfer-Encoding') ?: '');

        if ($encoding === 'base64') {
            $decoded = base64_decode(preg_replace('/\s+/', '', $body) ?: '', true);

            return $decoded === false ? $body : $decoded;
        }

        if ($encoding === 'quoted-printable') {
            return quoted_printable_decode($body);
        }

        return $body;
    }

    private function cleanBody(string $body, bool $isHtml): string
    {
        if ($isHtml) {
            // Drop non-visible blocks first so their CSS/JS source does not leak into the plain-text body.
            $body = preg_replace('/<(head|style|script|title)\b[^>]*>.*?<\/\1\s*>|<!--.*?-->/is', '', $body) ?? $body;
            $body = preg_replace('/<br\s*\/?>|<\/(p|div|tr|li|h[1-6]|table|blockquote)\s*>/i', "\n", $body) ?? $body;
            $body = strip_tags($body);
        }

        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($isHtml) {
            $body = preg_replace('/[ \t\x{00A0}]+/u', ' ', $body) ?? $body;
            $body = preg_replace('/ *\n */', "\n", $body) ?? $body;
            $body = preg_replace('/\n{3,}/', "\n\n", $body) ?? $body;
        }

        return trim($body);
    }

    /**
     * @return list<string>
     */
    private function command(string $command, bool $failOnError = true): array
    {
        if ($this->stream === null) {
            throw new RuntimeException('Koneksi IMAP belum terbuka.');
        }

        $tag = $this->nextTag();
        $this->write("{$tag} {$command}\r\n");

        return $this->readTaggedResponse($tag, $failOnError);
    }

    /**
     * @return list<string>
     */
    private function readTaggedResponse(string $tag, bool $failOnError): array
    {
        $lines = [];

        while (($line = $this->readLine()) !== null) {
            $lines[] = $line;

            if (str_starts_with($line, "{$tag} ")) {
                if ($failOnError && preg_match("/^{$tag} (NO|BAD)/", $line) === 1) {
                    throw new RuntimeException('Perintah IMAP gagal: '.$line);
                }

                break;
            }
        }

        return $lines;
    }

    private function nextTag(): string
    {
        return 'A'.str_pad((string) $this->tagNumber++, 4, '0', STR_PAD_LEFT);
    }

    private function write(string $data): void
    {
        if ($this->stream === null) {
            throw new RuntimeException('Koneksi IMAP belum terbuka.');
        }

        while ($data !== '') {
            $written = fwrite($this->stream, $data);

            if ($written === false || $written === 0) {
                throw new RuntimeException('Gagal mengirim data ke IMAP server.');
            }

            $data = substr($data, $written);
        }
    }

    private function hasCapability(string $capability): bool
    {
        foreach ($this->command('CAPABILITY') as $line) {
            if (str_starts_with($line, '* CAPABILITY') && in_array(mb_strtoupper($capability), explode(' ', mb_strtoupper($line)), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array{name: string, delimiter: string, attributes: list<string>}>
     */
    private function mailboxes(): array
    {
        return $this->parseMailboxList($this->command('LIST "" "*"'));
    }

    /**
     * @param  list<string>  $lines
     * @return list<array{name: string, delimiter: string, attributes: list<string>}>
     */
    private function parseMailboxList(array $lines): array
    {
        $mailboxes = [];

        foreach ($lines as $line) {
            if (preg_match('/^\* LIST \(([^)]*)\) (?:"((?:[^"\\\\]|\\\\.)*)"|NIL) (?:"((?:[^"\\\\]|\\\\.)*)"|(\S+))$/i', $line, $matches) !== 1) {
                continue;
            }

            $name = ($matches[4] ?? '') !== '' ? $matches[4] : stripslashes($matches[3]);

            $mailboxes[] = [
                'name' => $name,
                'delimiter' => stripslashes($matches[2]),
                'attributes' => array_values(array_filter(explode(' ', $matches[1]))),
            ];
        }

        return $mailboxes;
    }

    /**
     * @param  list<array{name: string, delimiter: string, attributes: list<string>}>  $mailboxes
     * @param  list<string>  $fallbackNames
     */
    private function matchSpecialMailbox(array $mailboxes, string $attribute, array $fallbackNames): ?string
    {
        foreach ($mailboxes as $mailbox) {
            foreach ($mailbox['attributes'] as $mailboxAttribute) {
                if (strcasecmp($mailboxAttribute, $attribute) === 0) {
                    return $mailbox['name'];
                }
            }
        }

        foreach ($fallbackNames as $fallbackName) {
            foreach ($mailboxes as $mailbox) {
                $segments = $mailbox['delimiter'] === '' ? [$mailbox['name']] : explode($mailbox['delimiter'], $mailbox['name']);

                if (strcasecmp((string) end($segments), $fallbackName) === 0) {
                    return $mailbox['name'];
                }
            }
        }

        return null;
    }

    private function readLine(): ?string
    {
        if ($this->stream === null) {
            return null;
        }

        $line = fgets($this->stream, 8192);

        if ($line === false) {
            return null;
        }

        return rtrim($line, "\r\n");
    }

    /**
     * Build one IMAP search key matching any message whose Message-ID, In-Reply-To or References
     * contains one of the given Message-IDs, as a chain of binary ORs.
     *
     * @param  list<string>  $messageIds
     */
    private function threadSearchKey(array $messageIds): ?string
    {
        $criteria = [];

        foreach ($messageIds as $messageId) {
            $quotedMessageId = $this->quote($messageId);
            array_push(
                $criteria,
                "HEADER Message-ID {$quotedMessageId}",
                "HEADER In-Reply-To {$quotedMessageId}",
                "HEADER References {$quotedMessageId}",
            );
        }

        if ($criteria === []) {
            return null;
        }

        $searchKey = array_pop($criteria);

        while ($criteria !== []) {
            $searchKey = 'OR '.array_pop($criteria).' '.$searchKey;
        }

        return $searchKey;
    }

    /**
     * @return list<string>
     */
    private function searchUids(string $searchKey): array
    {
        $uids = [];

        foreach ($this->command('UID SEARCH '.$searchKey) as $line) {
            if (preg_match('/^\* SEARCH(?:\s+(.*))?$/', $line, $matches) !== 1) {
                continue;
            }

            $uids = array_values(array_filter(preg_split('/\s+/', trim($matches[1] ?? '')) ?: []));
        }

        return $uids;
    }

    private function quote(string $value): string
    {
        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }

    private function headerValue(string $raw, string $header): ?string
    {
        if (preg_match('/^'.preg_quote($header, '/').':\s*(.+(?:\n[ \t].+)*)/mi', $raw, $matches) !== 1) {
            return null;
        }

        return trim(preg_replace('/\n[ \t]+/', ' ', $matches[1]));
    }

    /**
     * @return list<string>
     */
    private function headerValues(string $raw, string $header): array
    {
        if (preg_match_all('/^'.preg_quote($header, '/').':\s*(.+(?:\n[ \t].+)*)/mi', $raw, $matches) !== false) {
            return array_values(array_map(
                static fn (string $value): string => trim(preg_replace('/\n[ \t]+/', ' ', $value) ?? $value),
                $matches[1] ?? [],
            ));
        }

        return [];
    }

    private function messageBelongsToEmail(string $rawHeaders, string $email, string $mode): bool
    {
        $email = mb_strtolower(trim($email));

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        $headers = $mode === self::EMAIL_FILTER_SENDER ? ['From'] : self::RECIPIENT_HEADERS;

        foreach ($headers as $header) {
            foreach ($this->headerValues($rawHeaders, $header) as $headerValue) {
                if ($this->headerContainsEmail($headerValue, $email)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function headerContainsEmail(string $headerValue, string $email): bool
    {
        if (preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $headerValue, $matches) === false) {
            return false;
        }

        return collect($matches[0] ?? [])
            ->map(static fn (string $matchedEmail): string => mb_strtolower(trim($matchedEmail)))
            ->contains($email);
    }

    private function decodeHeader(string $value): string
    {
        $decoded = iconv_mime_decode($value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

        return $decoded === false ? $value : $decoded;
    }

    private function formatDate(string $date): string
    {
        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return '';
        }

        return date('M d, H:i', $timestamp);
    }
}
