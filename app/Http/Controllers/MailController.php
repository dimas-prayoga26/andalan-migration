<?php

namespace App\Http\Controllers;

use App\Models\MailAccessAccount;
use App\Services\MailInboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class MailController extends Controller
{
    private const SESSION_KEY = 'mail_access_account_id';

    public function index(Request $request): View|RedirectResponse
    {
        if ($this->currentAccount($request) !== null) {
            return redirect()->route('applicant.email.inbox');
        }

        return view('mail.index', [
            'pendingEmail' => session('mail_pending_email'),
        ]);
    }

    public function checkEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = $this->normalizeEmail((string) $validated['email']);

        $accountExists = MailAccessAccount::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->exists();

        if (! $accountExists) {
            return back()
                ->withErrors(['email' => 'Email tidak terdaftar atau belum aktif.'])
                ->withInput(['email' => $email]);
        }

        return back()
            ->withInput(['email' => $email])
            ->with('mail_pending_email', $email);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'pin_digits' => ['required', 'array', 'size:4'],
            'pin_digits.*' => ['required', 'string', 'regex:/^[0-9]$/'],
        ]);

        $email = $this->normalizeEmail((string) $validated['email']);
        $pin = implode('', $validated['pin_digits']);

        $account = MailAccessAccount::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->first();

        if (! $account?->pinMatches($pin)) {
            return back()
                ->withErrors(['pin' => 'PIN tidak valid.'])
                ->withInput(['email' => $email])
                ->with('mail_pending_email', $email);
        }

        $account->ensurePinIsHashed($pin);
        $account->forceFill(['last_login_at' => now()])->save();

        $request->session()->put(self::SESSION_KEY, $account->id);

        return redirect()->route('applicant.email.inbox');
    }

    public function inbox(Request $request, MailInboxService $mailInbox): View|RedirectResponse
    {
        return $this->mailboxView($request, $mailInbox, MailInboxService::FOLDER_INBOX);
    }

    public function sent(Request $request, MailInboxService $mailInbox): View|RedirectResponse
    {
        return $this->mailboxView($request, $mailInbox, MailInboxService::FOLDER_SENT);
    }

    public function destroy(Request $request, MailInboxService $mailInbox): RedirectResponse
    {
        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()->route('applicant.email.index');
        }

        $validated = $request->validate([
            'folder' => ['required', 'string', Rule::in(MailInboxService::FOLDERS)],
            'uids' => ['required', 'array', 'min:1', 'max:100'],
            'uids.*' => ['required', 'string', 'regex:/^[0-9]+$/'],
        ], [
            'uids.required' => 'Pilih minimal satu email yang akan dihapus.',
        ]);

        $folder = (string) $validated['folder'];
        $uids = array_values(array_unique(array_map('strval', $validated['uids'])));

        try {
            $mailInbox->deleteMessages($account->email, $uids, $folder);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route($this->folderRouteName($folder))
                ->withErrors(['mail' => 'Email belum bisa dihapus. Coba refresh lalu ulangi.']);
        }

        return redirect()
            ->route($this->folderRouteName($folder))
            ->with('mail_status', count($uids).' email berhasil dihapus.');
    }

    public function compose(Request $request): View|RedirectResponse
    {
        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()->route('applicant.email.index');
        }

        return view('mail.compose', [
            'account' => $account,
        ]);
    }

    public function send(Request $request, MailInboxService $mailInbox): RedirectResponse
    {
        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()->route('applicant.email.index');
        }

        $validated = $request->validate([
            'to' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        try {
            $sentMessage = $this->sendOutgoingMail(
                $account,
                (string) $validated['to'],
                (string) $validated['subject'],
                (string) $validated['body'],
                $this->uploadedAttachments($request),
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['mail' => $this->mailFailureMessage($exception, 'Email belum bisa dikirim.')])
                ->withInput();
        }

        $this->storeSentCopy($mailInbox, $account, $sentMessage);

        return redirect()
            ->route('applicant.email.sent')
            ->with('mail_status', 'Email berhasil dikirim.');
    }

    public function read(Request $request, MailInboxService $mailInbox, ?string $uid = null): View|RedirectResponse
    {
        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()->route('applicant.email.index');
        }

        $folder = $this->folderFromRequest($request);

        if ($uid === null) {
            return redirect()->route($this->folderRouteName($folder));
        }

        $message = null;
        $readError = null;

        try {
            $message = $mailInbox->messageFor($account->email, $uid, $folder);
        } catch (Throwable $exception) {
            report($exception);
            $readError = 'Pesan email belum bisa dibuka. Coba refresh inbox lalu klik ulang pesan.';
        }

        return view('mail.read', [
            'account' => $account,
            'folder' => $folder,
            'message' => $message,
            'readError' => $readError,
            'replyTo' => $message ? $this->replyRecipient($message, $folder) : null,
        ]);
    }

    public function reply(Request $request, MailInboxService $mailInbox, string $uid): RedirectResponse
    {
        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()->route('applicant.email.index');
        }

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $folder = $this->folderFromRequest($request);

        try {
            $message = $mailInbox->messageFor($account->email, $uid, $folder);
            $recipient = $this->replyRecipient($message, $folder);

            if ($recipient === null) {
                return back()
                    ->withErrors(['mail' => 'Alamat tujuan balasan tidak valid.'])
                    ->withInput();
            }

            $sentMessage = $this->sendOutgoingMail(
                $account,
                $recipient,
                $this->replySubject((string) ($message['subject'] ?? 'Email')),
                (string) $validated['body'],
                $this->uploadedAttachments($request),
                [
                    'message_id' => (string) ($message['message_id'] ?? ''),
                    'references' => (string) ($message['references'] ?? ''),
                ],
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['mail' => $this->mailFailureMessage($exception, 'Balasan belum bisa dikirim.')])
                ->withInput();
        }

        $this->storeSentCopy($mailInbox, $account, $sentMessage);

        return redirect()
            ->route('applicant.email.read', $this->readRouteParameters($uid, $folder))
            ->with('mail_status', 'Balasan berhasil dikirim.');
    }

    public function attachment(Request $request, MailInboxService $mailInbox, string $uid, string $attachment): Response|RedirectResponse
    {
        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()->route('applicant.email.index');
        }

        try {
            $file = $mailInbox->attachmentFor($account->email, $uid, $attachment, $this->folderFromRequest($request));
        } catch (Throwable $exception) {
            report($exception);

            abort(404);
        }

        $disposition = $request->boolean('inline') && $file['is_image'] ? 'inline' : 'attachment';
        $filename = str_replace(['"', '\\', "\r", "\n"], '', $file['filename']);

        return response($file['content'], 200, [
            'Content-Type' => $file['content_type'],
            'Content-Length' => (string) strlen($file['content']),
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('applicant.email.index');
    }

    private function currentAccount(Request $request): ?MailAccessAccount
    {
        $accountId = $request->session()->get(self::SESSION_KEY);

        if (! is_numeric($accountId)) {
            return null;
        }

        return MailAccessAccount::query()
            ->whereKey((int) $accountId)
            ->where('is_active', true)
            ->first();
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function mailFailureMessage(Throwable $exception, string $prefix): string
    {
        $message = $prefix.' Pastikan konfigurasi SMTP akun ini sudah benar.';

        if (config('app.debug')) {
            $message .= ' Detail: '.$exception->getMessage();
        }

        return $message;
    }

    /**
     * @return list<UploadedFile>
     */
    private function uploadedAttachments(Request $request): array
    {
        $attachments = $request->file('attachments', []);

        if ($attachments instanceof UploadedFile) {
            return [$attachments];
        }

        if (! is_array($attachments)) {
            return [];
        }

        return array_values(array_filter(
            $attachments,
            static fn (mixed $attachment): bool => $attachment instanceof UploadedFile,
        ));
    }

    private function mailboxView(Request $request, MailInboxService $mailInbox, string $folder): View|RedirectResponse
    {
        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()->route('applicant.email.index');
        }

        $messages = [];
        $inboxError = null;
        $searchQuery = trim((string) $request->query('search', ''));

        try {
            $messages = $mailInbox->messagesFor($account->email, folder: $folder);
        } catch (Throwable $exception) {
            report($exception);
            $inboxError = $folder === MailInboxService::FOLDER_SENT
                ? 'Email terkirim belum bisa diambil. Pastikan IMAP aktif untuk email ini.'
                : 'Inbox belum bisa diambil. Pastikan IMAP aktif untuk email ini.';
        }

        $totalInboxCount = count($messages);

        if ($searchQuery !== '') {
            $messages = $this->filterMessages($messages, $searchQuery);
        }

        return view('mail.inbox', [
            'account' => $account,
            'folder' => $folder,
            'inboxError' => $inboxError,
            'messages' => $messages,
            'searchQuery' => $searchQuery,
            'totalInboxCount' => $totalInboxCount,
        ]);
    }

    private function folderFromRequest(Request $request): string
    {
        $folder = (string) $request->input('folder', MailInboxService::FOLDER_INBOX);

        return in_array($folder, MailInboxService::FOLDERS, true) ? $folder : MailInboxService::FOLDER_INBOX;
    }

    private function folderRouteName(string $folder): string
    {
        return $folder === MailInboxService::FOLDER_SENT ? 'applicant.email.sent' : 'applicant.email.inbox';
    }

    /**
     * @return array{uid: string, folder?: string}
     */
    private function readRouteParameters(string $uid, string $folder): array
    {
        return $folder === MailInboxService::FOLDER_SENT
            ? ['uid' => $uid, 'folder' => $folder]
            : ['uid' => $uid];
    }

    /**
     * Replies to a sent message go back to its recipient instead of the account itself.
     *
     * @param  array<string, mixed>  $message
     */
    private function replyRecipient(array $message, string $folder): ?string
    {
        $header = $folder === MailInboxService::FOLDER_SENT ? 'to' : 'from';

        return $this->emailAddressFromHeader((string) ($message[$header] ?? ''));
    }

    /**
     * Saving to the Sent mailbox is best-effort: the email itself was already delivered.
     */
    private function storeSentCopy(MailInboxService $mailInbox, MailAccessAccount $account, ?SentMessage $sentMessage): void
    {
        if ($sentMessage === null) {
            return;
        }

        try {
            $mailInbox->storeSentMessage($account->email, $sentMessage->toString());
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @param  list<UploadedFile>  $attachments
     * @param  array{message_id?: string, references?: string}  $replyHeaders
     */
    private function sendOutgoingMail(MailAccessAccount $account, string $to, string $subject, string $body, array $attachments, array $replyHeaders = []): ?SentMessage
    {
        return Mail::mailer($this->mailerForAccount($account))->html($this->outgoingHtmlBody($body), function ($message) use ($account, $attachments, $replyHeaders, $subject, $to): void {
            $message
                ->from($account->email, $account->email)
                ->to($to)
                ->subject($subject);

            $this->applyReplyHeaders($message, $replyHeaders);

            foreach ($attachments as $attachment) {
                $message->attach($attachment->getRealPath(), [
                    'as' => $attachment->getClientOriginalName(),
                    'mime' => $attachment->getMimeType() ?: 'application/octet-stream',
                ]);
            }
        });
    }

    /**
     * @param  array{message_id?: string, references?: string}  $replyHeaders
     */
    private function applyReplyHeaders(mixed $message, array $replyHeaders): void
    {
        $messageId = trim((string) ($replyHeaders['message_id'] ?? ''));

        if ($messageId === '' || ! method_exists($message, 'getSymfonyMessage')) {
            return;
        }

        $headers = $message->getSymfonyMessage()->getHeaders();
        $references = trim((string) ($replyHeaders['references'] ?? ''));

        $headers->addTextHeader('In-Reply-To', $messageId);
        $headers->addTextHeader('References', trim($references.' '.$messageId));
    }

    private function outgoingHtmlBody(string $body): string
    {
        if ($body !== strip_tags($body)) {
            return $body;
        }

        return nl2br(e($body));
    }

    private function mailerForAccount(MailAccessAccount $account): string
    {
        foreach (config('mail_inboxes.accounts', []) as $key => $inboxAccount) {
            if ($this->normalizeEmail((string) ($inboxAccount['username'] ?? '')) === $account->email) {
                return array_key_exists($key, config('mail.mailers', []))
                    ? (string) $key
                    : (string) config('mail.default', 'smtp');
            }
        }

        return (string) config('mail.default', 'smtp');
    }

    private function emailAddressFromHeader(string $header): ?string
    {
        if (preg_match('/<([^<>@\s]+@[^<>@\s]+\.[^<>@\s]+)>/', $header, $matches) === 1) {
            return $this->normalizeEmail($matches[1]);
        }

        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $header, $matches) === 1) {
            return $this->normalizeEmail($matches[0]);
        }

        return null;
    }

    private function replySubject(string $subject): string
    {
        $subject = trim($subject);

        if ($subject === '') {
            return 'Re: Email';
        }

        if (preg_match('/^re:/i', $subject) === 1) {
            return $subject;
        }

        return 'Re: '.$subject;
    }

    /**
     * @param  list<array<string, mixed>>  $messages
     * @return list<array<string, mixed>>
     */
    private function filterMessages(array $messages, string $searchQuery): array
    {
        $needle = mb_strtolower($searchQuery);

        return array_values(array_filter($messages, static function (array $message) use ($needle): bool {
            $haystack = mb_strtolower(implode(' ', [
                (string) ($message['from'] ?? ''),
                (string) ($message['subject'] ?? ''),
                (string) ($message['date'] ?? ''),
                (string) ($message['time'] ?? ''),
            ]));

            return str_contains($haystack, $needle);
        }));
    }
}
