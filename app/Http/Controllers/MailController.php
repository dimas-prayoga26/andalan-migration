<?php

namespace App\Http\Controllers;

use App\Models\MailAccessAccount;
use App\Services\MailInboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
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
        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()->route('applicant.email.index');
        }

        $messages = [];
        $inboxError = null;
        $searchQuery = trim((string) $request->query('search', ''));

        try {
            $messages = $mailInbox->messagesFor($account->email);
        } catch (Throwable $exception) {
            report($exception);
            $inboxError = 'Inbox belum bisa diambil. Pastikan IMAP aktif untuk email ini.';
        }

        $totalInboxCount = count($messages);

        if ($searchQuery !== '') {
            $messages = $this->filterMessages($messages, $searchQuery);
        }

        return view('mail.inbox', [
            'account' => $account,
            'inboxError' => $inboxError,
            'messages' => $messages,
            'searchQuery' => $searchQuery,
            'totalInboxCount' => $totalInboxCount,
        ]);
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

    public function send(Request $request): RedirectResponse
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
            $this->sendOutgoingMail(
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

        return redirect()
            ->route('applicant.email.inbox')
            ->with('mail_status', 'Email berhasil dikirim.');
    }

    public function read(Request $request, MailInboxService $mailInbox, ?string $uid = null): View|RedirectResponse
    {
        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()->route('applicant.email.index');
        }

        if ($uid === null) {
            return redirect()->route('applicant.email.inbox');
        }

        $message = null;
        $readError = null;

        try {
            $message = $mailInbox->messageFor($account->email, $uid);
        } catch (Throwable $exception) {
            report($exception);
            $readError = 'Pesan email belum bisa dibuka. Coba refresh inbox lalu klik ulang pesan.';
        }

        return view('mail.read', [
            'account' => $account,
            'message' => $message,
            'readError' => $readError,
            'replyTo' => $message ? $this->emailAddressFromHeader((string) ($message['from'] ?? '')) : null,
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

        try {
            $message = $mailInbox->messageFor($account->email, $uid);
            $recipient = $this->emailAddressFromHeader((string) ($message['from'] ?? ''));

            if ($recipient === null) {
                return back()
                    ->withErrors(['mail' => 'Alamat tujuan balasan tidak valid.'])
                    ->withInput();
            }

            $this->sendOutgoingMail(
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

        return redirect()
            ->route('applicant.email.read', $uid)
            ->with('mail_status', 'Balasan berhasil dikirim.');
    }

    public function attachment(Request $request, MailInboxService $mailInbox, string $uid, string $attachment): Response|RedirectResponse
    {
        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()->route('applicant.email.index');
        }

        try {
            $file = $mailInbox->attachmentFor($account->email, $uid, $attachment);
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

    /**
     * @param  list<UploadedFile>  $attachments
     * @param  array{message_id?: string, references?: string}  $replyHeaders
     */
    private function sendOutgoingMail(MailAccessAccount $account, string $to, string $subject, string $body, array $attachments, array $replyHeaders = []): void
    {
        Mail::mailer($this->mailerForAccount($account))->html($this->outgoingHtmlBody($body), function ($message) use ($account, $attachments, $replyHeaders, $subject, $to): void {
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
