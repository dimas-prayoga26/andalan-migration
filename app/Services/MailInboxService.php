<?php

namespace App\Services;

use RuntimeException;

class MailInboxService
{
    /**
     * @return list<array{uid: string, from: string, subject: string, date: string, time: string, unread: bool}>
     */
    public function messagesFor(string $email, int $limit = 20): array
    {
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
        );

        try {
            $client->login();
            $client->selectInbox();

            return $client->latestMessages($limit);
        } finally {
            $client->logout();
        }
    }

    /**
     * @return array{uid: string, from: string, subject: string, date: string, time: string, unread: bool, body: string, attachments: list<array{id: string, filename: string, content_type: string, size: int, is_image: bool}>, message_id: string, references: string}
     */
    public function messageFor(string $email, string $uid): array
    {
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
        );

        try {
            $client->login();
            $client->selectInbox();

            return $client->message($uid);
        } finally {
            $client->logout();
        }
    }

    /**
     * @return array{id: string, filename: string, content_type: string, size: int, is_image: bool, content: string}
     */
    public function attachmentFor(string $email, string $uid, string $attachmentId): array
    {
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
        );

        try {
            $client->login();
            $client->selectInbox();

            return $client->attachment($uid, $attachmentId);
        } finally {
            $client->logout();
        }
    }

    /**
     * @return array{host: string|null, port: int|string, encryption: string, username: string|null, password: string|null}|null
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

        return null;
    }
}

class SimpleImapClient
{
    /** @var resource|null */
    private $stream = null;

    private int $tagNumber = 1;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $encryption,
        private readonly string $username,
        private readonly string $password,
    ) {}

    public function login(): void
    {
        $scheme = $this->encryption === 'ssl' ? 'ssl' : 'tcp';
        $target = sprintf('%s://%s:%d', $scheme, $this->host, $this->port);
        $stream = @stream_socket_client($target, $errno, $error, 15);

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
        $this->command('SELECT INBOX');
    }

    /**
     * @return list<array{uid: string, from: string, subject: string, date: string, time: string, unread: bool}>
     */
    public function latestMessages(int $limit): array
    {
        $searchLines = $this->command('UID SEARCH ALL');
        $uids = [];

        foreach ($searchLines as $line) {
            if (preg_match('/^\* SEARCH(?:\s+(.*))?$/', $line, $matches) !== 1) {
                continue;
            }

            $uids = array_values(array_filter(preg_split('/\s+/', trim($matches[1] ?? '')) ?: []));
        }

        $uids = array_slice(array_reverse($uids), 0, $limit);
        $messages = [];

        foreach ($uids as $uid) {
            $messages[] = $this->fetchHeader($uid);
        }

        return $messages;
    }

    /**
     * @return array{uid: string, from: string, subject: string, date: string, time: string, unread: bool, body: string, attachments: list<array{id: string, filename: string, content_type: string, size: int, is_image: bool}>, message_id: string, references: string}
     */
    public function message(string $uid): array
    {
        $rawMessage = $this->fetchRawMessage($uid);
        [$rawHeaders, $rawBody] = $this->splitRawMessage($rawMessage);

        $date = $this->headerValue($rawHeaders, 'Date') ?: '';
        $content = $this->messageContent($rawHeaders, $rawBody);

        return [
            'uid' => $uid,
            'from' => $this->decodeHeader($this->headerValue($rawHeaders, 'From') ?: 'Unknown Sender'),
            'subject' => $this->decodeHeader($this->headerValue($rawHeaders, 'Subject') ?: '(No Subject)'),
            'date' => $date,
            'time' => $this->formatDate($date),
            'unread' => ! str_contains($rawMessage, '\\Seen'),
            'body' => $content['body'],
            'attachments' => $content['attachments'],
            'message_id' => $this->headerValue($rawHeaders, 'Message-ID') ?: '',
            'references' => $this->headerValue($rawHeaders, 'References') ?: '',
        ];
    }

    /**
     * @return array{id: string, filename: string, content_type: string, size: int, is_image: bool, content: string}
     */
    public function attachment(string $uid, string $attachmentId): array
    {
        $rawMessage = $this->fetchRawMessage($uid);
        [$rawHeaders, $rawBody] = $this->splitRawMessage($rawMessage);
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
     * @return array{uid: string, from: string, subject: string, date: string, time: string, unread: bool}
     */
    private function fetchHeader(string $uid): array
    {
        $lines = $this->command(sprintf('UID FETCH %s (UID FLAGS BODY.PEEK[HEADER.FIELDS (FROM SUBJECT DATE)])', $uid));
        $raw = implode("\n", $lines);

        return [
            'uid' => $uid,
            'from' => $this->decodeHeader($this->headerValue($raw, 'From') ?: 'Unknown Sender'),
            'subject' => $this->decodeHeader($this->headerValue($raw, 'Subject') ?: '(No Subject)'),
            'date' => $this->headerValue($raw, 'Date') ?: '',
            'time' => $this->formatDate($this->headerValue($raw, 'Date') ?: ''),
            'unread' => ! str_contains($raw, '\\Seen'),
        ];
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
     * @return array{body: string, attachments: list<array{id: string, filename: string, content_type: string, size: int, is_image: bool, content?: string}>}
     */
    private function messageContent(string $rawHeaders, string $rawBody, bool $includeAttachmentContent = false): array
    {
        $plainBody = null;
        $htmlBody = null;
        $attachments = [];

        $this->collectParts($rawHeaders, $rawBody, $plainBody, $htmlBody, $attachments, $includeAttachmentContent);

        return [
            'body' => trim((string) ($plainBody ?? $htmlBody ?? '')),
            'attachments' => $attachments,
        ];
    }

    /**
     * @param  list<array{id: string, filename: string, content_type: string, size: int, is_image: bool, content?: string}>  $attachments
     */
    private function collectParts(
        string $headers,
        string $body,
        ?string &$plainBody,
        ?string &$htmlBody,
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
                $this->collectParts($partHeaders, $partBody, $plainBody, $htmlBody, $attachments, $includeAttachmentContent);
            }

            return;
        }

        $filename = $this->filenameFromHeaders($headers);
        $baseContentType = trim(strtok($contentType, ';') ?: 'application/octet-stream');
        $isAttachment = $filename !== null || str_contains($disposition, 'attachment');
        $decodedBody = $this->decodeTransferBody($headers, $body);

        if ($isAttachment) {
            $attachmentId = (string) (count($attachments) + 1);
            $attachment = [
                'id' => $attachmentId,
                'filename' => $filename ?: "attachment-{$attachmentId}",
                'content_type' => $baseContentType,
                'size' => strlen($decodedBody),
                'is_image' => str_starts_with($baseContentType, 'image/'),
            ];

            if ($includeAttachmentContent) {
                $attachment['content'] = $decodedBody;
            }

            $attachments[] = $attachment;

            return;
        }

        if (str_contains($baseContentType, 'text/plain') && $plainBody === null) {
            $plainBody = $this->cleanBody($decodedBody, false);
        }

        if (str_contains($baseContentType, 'text/html') && $htmlBody === null) {
            $htmlBody = $this->cleanBody($decodedBody, true);
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
            $body = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $body));
        }

        return trim(html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * @return list<string>
     */
    private function command(string $command, bool $failOnError = true): array
    {
        if ($this->stream === null) {
            throw new RuntimeException('Koneksi IMAP belum terbuka.');
        }

        $tag = 'A'.str_pad((string) $this->tagNumber++, 4, '0', STR_PAD_LEFT);
        fwrite($this->stream, "{$tag} {$command}\r\n");

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
