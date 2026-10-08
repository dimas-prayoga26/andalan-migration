<?php

namespace App\Http\Controllers;

use App\Models\MailAccessAccount;
use App\Models\Position;
use App\Models\User;
use App\Services\MailInboxService;
use Illuminate\Database\Eloquent\Builder;
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

    private const SESSION_SELECTED_KEY = 'mail_access_account_selected';

    private const BUSINESS_SESSION_KEY = 'business_mail_access_account_id';

    private const BUSINESS_SESSION_SELECTED_KEY = 'business_mail_access_account_selected';

    private const OPERATIONAL_ROUTE_PREFIX = 'applicant.email';

    private const BUSINESS_ROUTE_PREFIX = 'business-email';

    public function index(Request $request): View|RedirectResponse
    {
        $this->authorizeMailFeatureAccess($request);
        $this->forgetSelectedAccount($request);

        return redirect()->route($this->routeName($request, 'inbox'));
    }

    public function selectAccount(Request $request): RedirectResponse
    {
        $this->authorizeMailFeatureAccess($request);

        $validated = $request->validate([
            'mail_access_account_id' => ['required', 'integer'],
            'folder' => ['nullable', 'string', Rule::in(MailInboxService::FOLDERS)],
        ]);

        $account = $this->availableAccountQuery($request)
            ->whereKey((int) $validated['mail_access_account_id'])
            ->first();

        if ($account === null) {
            return back()->withErrors(['mail' => 'Email account tidak bisa diakses.']);
        }

        $account->forceFill(['last_login_at' => now()])->save();
        $request->session()->put($this->sessionKey($request), $account->id);
        $request->session()->put($this->selectionSessionKey($request), true);

        $folder = (string) ($validated['folder'] ?? MailInboxService::FOLDER_INBOX);

        return redirect()->route($this->folderRouteName($request, $folder));
    }

    public function checkEmail(Request $request): RedirectResponse
    {
        $this->authorizeMailFeatureAccess($request);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = $this->normalizeEmail((string) $validated['email']);

        $accountExists = $this->availableAccountQuery($request)
            ->where('email', $email)
            ->exists();

        if (! $accountExists) {
            return back()
                ->withErrors(['email' => 'Email tidak terdaftar.'])
                ->withInput(['email' => $email]);
        }

        return back()
            ->withInput(['email' => $email])
            ->with('mail_pending_email', $email);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $this->authorizeMailFeatureAccess($request);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'pin_digits' => ['required', 'array', 'size:4'],
            'pin_digits.*' => ['required', 'string', 'regex:/^[0-9]$/'],
        ]);

        $email = $this->normalizeEmail((string) $validated['email']);
        $pin = implode('', $validated['pin_digits']);

        $account = $this->availableAccountQuery($request)
            ->where('email', $email)
            ->first();

        if (! $account?->pinMatches($pin)) {
            return back()
                ->withErrors(['pin' => 'PIN tidak valid.'])
                ->withInput(['email' => $email])
                ->with('mail_pending_email', $email);
        }

        $account->ensurePinIsHashed($pin);
        $account->forceFill(['last_login_at' => now()])->save();

        $request->session()->put($this->sessionKey($request), $account->id);
        $request->session()->put($this->selectionSessionKey($request), true);

        return redirect()->route($this->routeName($request, 'inbox'));
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
        $this->authorizeMailFeatureAccess($request);

        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()
                ->route($this->routeName($request, 'inbox'))
                ->withErrors(['mail' => 'Pilih email account terlebih dahulu.']);
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
                ->route($this->folderRouteName($request, $folder))
                ->withErrors(['mail' => 'Email belum bisa dihapus. Coba refresh lalu ulangi.']);
        }

        return redirect()
            ->route($this->folderRouteName($request, $folder))
            ->with('mail_status', count($uids).' email berhasil dihapus.');
    }

    public function compose(Request $request): View|RedirectResponse
    {
        $this->authorizeMailFeatureAccess($request);

        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()
                ->route($this->routeName($request, 'inbox'))
                ->withErrors(['mail' => 'Pilih email account terlebih dahulu sebelum compose.']);
        }

        return view('mail.compose', $this->mailViewData($request, [
            'account' => $account,
        ]));
    }

    public function send(Request $request, MailInboxService $mailInbox): RedirectResponse
    {
        $this->authorizeMailFeatureAccess($request);

        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()
                ->route($this->routeName($request, 'inbox'))
                ->withErrors(['mail' => 'Pilih email account terlebih dahulu sebelum mengirim email.']);
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
            ->route($this->routeName($request, 'sent'))
            ->with('mail_status', 'Email berhasil dikirim.');
    }

    public function read(Request $request, MailInboxService $mailInbox, ?string $uid = null): View|RedirectResponse
    {
        $this->authorizeMailFeatureAccess($request);

        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()
                ->route($this->routeName($request, 'inbox'))
                ->withErrors(['mail' => 'Pilih email account terlebih dahulu.']);
        }

        $folder = $this->folderFromRequest($request);

        if ($uid === null) {
            return redirect()->route($this->folderRouteName($request, $folder));
        }

        $message = null;
        $readError = null;

        try {
            $message = $mailInbox->messageFor($account->email, $uid, $folder);
        } catch (Throwable $exception) {
            report($exception);
            $readError = 'Pesan email belum bisa dibuka. Coba refresh inbox lalu klik ulang pesan.';
        }

        return view('mail.read', $this->mailViewData($request, [
            'account' => $account,
            'folder' => $folder,
            'message' => $message,
            'readError' => $readError,
            'replyTo' => $message ? $this->replyRecipient($message, $folder) : null,
        ]));
    }

    public function reply(Request $request, MailInboxService $mailInbox, string $uid): RedirectResponse
    {
        $this->authorizeMailFeatureAccess($request);

        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()
                ->route($this->routeName($request, 'inbox'))
                ->withErrors(['mail' => 'Pilih email account terlebih dahulu sebelum membalas email.']);
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
            ->route($this->routeName($request, 'read'), $this->readRouteParameters($uid, $folder))
            ->with('mail_status', 'Balasan berhasil dikirim.');
    }

    public function attachment(Request $request, MailInboxService $mailInbox, string $uid, string $attachment): Response|RedirectResponse
    {
        $this->authorizeMailFeatureAccess($request);

        $account = $this->currentAccount($request);

        if ($account === null) {
            return redirect()
                ->route($this->routeName($request, 'inbox'))
                ->withErrors(['mail' => 'Pilih email account terlebih dahulu.']);
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
        $this->forgetSelectedAccount($request);

        return redirect()->route($this->routeName($request, 'index'));
    }

    private function currentAccount(Request $request): ?MailAccessAccount
    {
        if ($request->session()->get($this->selectionSessionKey($request), false) !== true) {
            return null;
        }

        $accountId = $request->session()->get($this->sessionKey($request));

        if (! is_numeric($accountId)) {
            return null;
        }

        return $this->availableAccountQuery($request)
            ->whereKey((int) $accountId)
            ->first();
    }

    private function authorizeMailFeatureAccess(Request $request): void
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        if ($this->isBusinessEmailRoute($request)) {
            abort_unless(
                ! $this->isAdminOrSuperAdministrator($user)
                && $user->employee !== null
                && $user->hasAnyPositionPermission(['view-business-email']),
                403
            );

            return;
        }

        abort_unless($user->hasAnyPositionPermission(['view-email-management']), 403);
    }

    private function availableAccountQuery(Request $request): Builder
    {
        $query = MailAccessAccount::query()
            ->visibleForMailAccess()
            ->active();

        if ($this->isBusinessEmailRoute($request)) {
            $employeeId = $request->user()?->employee?->id;

            return $query
                ->where('type', MailAccessAccount::TYPE_PERSONAL)
                ->where('employee_id', $employeeId);
        }

        return $query->where('type', '!=', MailAccessAccount::TYPE_PERSONAL);
    }

    private function isBusinessEmailRoute(Request $request): bool
    {
        return $request->routeIs(self::BUSINESS_ROUTE_PREFIX.'.*');
    }

    private function routePrefix(Request $request): string
    {
        return $this->isBusinessEmailRoute($request)
            ? self::BUSINESS_ROUTE_PREFIX
            : self::OPERATIONAL_ROUTE_PREFIX;
    }

    private function routeName(Request $request, string $name): string
    {
        return $this->routePrefix($request).'.'.$name;
    }

    private function sessionKey(Request $request): string
    {
        return $this->isBusinessEmailRoute($request)
            ? self::BUSINESS_SESSION_KEY
            : self::SESSION_KEY;
    }

    private function selectionSessionKey(Request $request): string
    {
        return $this->isBusinessEmailRoute($request)
            ? self::BUSINESS_SESSION_SELECTED_KEY
            : self::SESSION_SELECTED_KEY;
    }

    private function forgetSelectedAccount(Request $request): void
    {
        $request->session()->forget([
            $this->sessionKey($request),
            $this->selectionSessionKey($request),
        ]);
    }

    private function mailFeatureTitle(Request $request): string
    {
        return $this->isBusinessEmailRoute($request) ? 'Business Email' : 'Email';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mailViewData(Request $request, array $data = []): array
    {
        $selectedAccount = ($data['account'] ?? null) instanceof MailAccessAccount
            ? $data['account']
            : $this->currentAccount($request);

        return array_merge($data, [
            'mailFeatureTitle' => $this->mailFeatureTitle($request),
            'mailRoutePrefix' => $this->routePrefix($request),
            'mailAccounts' => $this->availableAccountQuery($request)
                ->orderBy('email')
                ->get(['id', 'email', 'type', 'company_id', 'employee_id']),
            'selectedMailAccountId' => $selectedAccount?->id,
        ]);
    }

    private function isAdminOrSuperAdministrator(User $user): bool
    {
        if ($user->isSuperAdministrator()) {
            return true;
        }

        return $user->employee?->hasAnyPositionSystemKey([Position::KEY_ADMINISTRATOR]) ?? false;
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function emailDomain(string $email): string
    {
        $parts = explode('@', $this->normalizeEmail($email));

        if (count($parts) !== 2) {
            return '';
        }

        return preg_replace('/^www\./', '', $parts[1]) ?? '';
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
        $this->authorizeMailFeatureAccess($request);

        $account = $this->currentAccount($request);

        $messages = [];
        $inboxError = null;
        $searchQuery = trim((string) $request->query('search', ''));

        if ($account !== null) {
            try {
                $messages = $mailInbox->messagesFor($account->email, folder: $folder);
            } catch (Throwable $exception) {
                report($exception);
                $inboxError = $folder === MailInboxService::FOLDER_SENT
                    ? 'Email terkirim belum bisa diambil. Pastikan IMAP aktif untuk email ini.'
                    : 'Inbox belum bisa diambil. Pastikan IMAP aktif untuk email ini.';
            }
        }

        $totalInboxCount = count($messages);

        if ($searchQuery !== '') {
            $messages = $this->filterMessages($messages, $searchQuery);
        }

        return view('mail.inbox', $this->mailViewData($request, [
            'account' => $account,
            'folder' => $folder,
            'inboxError' => $inboxError,
            'messages' => $messages,
            'searchQuery' => $searchQuery,
            'totalInboxCount' => $totalInboxCount,
        ]));
    }

    private function folderFromRequest(Request $request): string
    {
        $folder = (string) $request->input('folder', MailInboxService::FOLDER_INBOX);

        return in_array($folder, MailInboxService::FOLDERS, true) ? $folder : MailInboxService::FOLDER_INBOX;
    }

    private function folderRouteName(Request $request, string $folder): string
    {
        return $folder === MailInboxService::FOLDER_SENT
            ? $this->routeName($request, 'sent')
            : $this->routeName($request, 'inbox');
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

        $accountDomain = $this->emailDomain((string) $account->email);

        foreach (config('mail_inboxes.accounts', []) as $key => $inboxAccount) {
            if ($accountDomain !== '' && $this->emailDomain((string) ($inboxAccount['username'] ?? '')) === $accountDomain) {
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
