<?php

namespace Tests\Feature;

use App\Http\Controllers\MailController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MailFeatureStructureTest extends TestCase
{
    public function test_mail_routes_are_registered(): void
    {
        $routes = [
            'applicant.email.index' => 'applicant/email',
            'applicant.email.check' => 'applicant/email/check',
            'applicant.email.login' => 'applicant/email/login',
            'applicant.email.inbox' => 'applicant/email/inbox',
            'applicant.email.compose' => 'applicant/email/compose',
            'applicant.email.send' => 'applicant/email/send',
            'applicant.email.attachment' => 'applicant/email/read/{uid}/attachments/{attachment}',
            'applicant.email.reply' => 'applicant/email/read/{uid}/reply',
            'applicant.email.read' => 'applicant/email/read/{uid?}',
            'applicant.email.logout' => 'applicant/email/logout',
        ];

        foreach ($routes as $routeName => $uri) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route);
            $this->assertSame($uri, $route?->uri());
        }

        $this->assertSame(MailController::class.'@index', Route::getRoutes()->getByName('applicant.email.index')?->getActionName());
        $this->assertSame(MailController::class.'@checkEmail', Route::getRoutes()->getByName('applicant.email.check')?->getActionName());
        $this->assertSame(MailController::class.'@authenticate', Route::getRoutes()->getByName('applicant.email.login')?->getActionName());
        $this->assertSame(MailController::class.'@inbox', Route::getRoutes()->getByName('applicant.email.inbox')?->getActionName());
        $this->assertSame(MailController::class.'@compose', Route::getRoutes()->getByName('applicant.email.compose')?->getActionName());
        $this->assertSame(MailController::class.'@send', Route::getRoutes()->getByName('applicant.email.send')?->getActionName());
        $this->assertSame(MailController::class.'@attachment', Route::getRoutes()->getByName('applicant.email.attachment')?->getActionName());
        $this->assertSame(MailController::class.'@reply', Route::getRoutes()->getByName('applicant.email.reply')?->getActionName());
        $this->assertSame(MailController::class.'@read', Route::getRoutes()->getByName('applicant.email.read')?->getActionName());
        $this->assertSame(MailController::class.'@logout', Route::getRoutes()->getByName('applicant.email.logout')?->getActionName());
    }

    public function test_mail_views_are_wired_to_routes(): void
    {
        $loginView = File::get(resource_path('views/mail/index.blade.php'));
        $inboxView = File::get(resource_path('views/mail/inbox.blade.php'));
        $composeView = File::get(resource_path('views/mail/compose.blade.php'));
        $readView = File::get(resource_path('views/mail/read.blade.php'));
        $mailSidebarView = File::get(resource_path('views/mail/partials/sidebar.blade.php'));
        $sidebarView = File::get(resource_path('views/layouts/sidebar.blade.php'));
        $controller = File::get(app_path('Http/Controllers/MailController.php'));
        $inboxService = File::get(app_path('Services/MailInboxService.php'));
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));
        $mailAccessSeeder = File::get(database_path('seeders/MailAccessAccountSeeder.php'));

        $this->assertStringContainsString("route('applicant.email.check')", $loginView);
        $this->assertStringContainsString("route('applicant.email.login')", $loginView);
        $this->assertStringContainsString('name="pin_digits[]"', $loginView);
        $this->assertStringContainsString('mail-pin-grid', $loginView);
        $this->assertStringContainsString('Socials', $inboxView);
        $this->assertStringContainsString('Promotion', $inboxView);
        $this->assertStringContainsString("route('applicant.email.read', \$mail['uid'])", $inboxView);
        $this->assertStringContainsString("route('applicant.email.inbox')", $inboxView);
        $this->assertStringContainsString('name="search"', $inboxView);
        $this->assertStringContainsString('Tidak ada email yang cocok dengan pencarian.', $inboxView);
        $this->assertStringContainsString('mail-list-clean', $inboxView);
        $this->assertStringContainsString('@forelse ($messages as $mail)', $inboxView);
        $this->assertStringContainsString('Inbox kosong.', $inboxView);
        $this->assertStringContainsString('compose-wrapper', $composeView);
        $this->assertStringContainsString("route('applicant.email.send')", $composeView);
        $this->assertStringContainsString('enctype="multipart/form-data"', $composeView);
        $this->assertStringContainsString('name="attachments[]"', $composeView);
        $this->assertStringContainsString('data-mail-attachment-input', $composeView);
        $this->assertStringContainsString('data-mail-attachment-list', $composeView);
        $this->assertStringContainsString('mail-selected-file', $composeView);
        $this->assertStringNotContainsString('Socials', $composeView);
        $this->assertStringContainsString('read-wapper', $readView);
        $this->assertStringContainsString('$message[\'body\']', $readView);
        $this->assertStringContainsString('mail-attachments', $readView);
        $this->assertStringContainsString("route('applicant.email.attachment'", $readView);
        $this->assertStringContainsString("route('applicant.email.reply'", $readView);
        $this->assertStringContainsString('name="attachments[]"', $readView);
        $this->assertStringContainsString('name="body"', $readView);
        $this->assertStringContainsString('data-mail-attachment-input', $readView);
        $this->assertStringContainsString('data-mail-attachment-list', $readView);
        $this->assertStringContainsString('mail-selected-file', $readView);
        $this->assertStringContainsString('mail-reply-editor', $readView);
        $this->assertStringContainsString('mail-reply-target', $readView);
        $this->assertStringContainsString('Reply akan dikirim ke', $readView);
        $this->assertStringContainsString('$replyTo ?? $message[\'from\']', $readView);
        $this->assertStringContainsString("route('applicant.email.logout')", $mailSidebarView);
        $this->assertStringContainsString("route('applicant.email.compose')", $mailSidebarView);
        $this->assertStringContainsString("route('applicant.email.inbox')", $mailSidebarView);
        $this->assertStringNotContainsString('Categories', $mailSidebarView);
        $this->assertStringContainsString('MailInboxService $mailInbox', $controller);
        $this->assertStringContainsString('$request->query(\'search\', \'\')', $controller);
        $this->assertStringContainsString('filterMessages($messages, $searchQuery)', $controller);
        $this->assertStringContainsString('messageFor($account->email, $uid)', $controller);
        $this->assertStringContainsString("'replyTo' => \$message ? \$this->emailAddressFromHeader", $controller);
        $this->assertStringContainsString('attachmentFor($account->email, $uid, $attachment)', $controller);
        $this->assertStringContainsString("'attachments.*' => ['file', 'max:10240']", $controller);
        $this->assertStringContainsString('Mail::mailer($this->mailerForAccount($account))->html', $controller);
        $this->assertStringContainsString('outgoingHtmlBody($body)', $controller);
        $this->assertStringContainsString('applyReplyHeaders($message, $replyHeaders)', $controller);
        $this->assertStringContainsString("addTextHeader('In-Reply-To'", $controller);
        $this->assertStringContainsString('sendOutgoingMail(', $controller);
        $this->assertStringContainsString('UID SEARCH ALL', $inboxService);
        $this->assertStringContainsString('BODY.PEEK[]', $inboxService);
        $this->assertStringContainsString("'message_id' => \$this->headerValue(\$rawHeaders, 'Message-ID')", $inboxService);
        $this->assertStringContainsString('filenameFromHeaders', $inboxService);
        $this->assertStringContainsString('MailAccessAccountSeeder::class', $databaseSeeder);
        $this->assertStringContainsString("config('mail_inboxes.accounts', [])", $mailAccessSeeder);
        $this->assertStringContainsString("env('MAIL_ACCESS_DEFAULT_PIN', '0000')", $mailAccessSeeder);
        $this->assertStringContainsString("route('applicant.email.index')", $sidebarView);
        $this->assertStringNotContainsString("route('mail.index')", $sidebarView);
    }

    public function test_mail_login_uses_two_step_email_then_pin_flow(): void
    {
        $loginView = File::get(resource_path('views/mail/index.blade.php'));
        $controller = File::get(app_path('Http/Controllers/MailController.php'));

        $this->assertStringContainsString('! $pendingEmail', $loginView);
        $this->assertStringContainsString('Masukkan email yang sudah terdaftar.', $loginView);
        $this->assertStringContainsString('Masukkan PIN', $loginView);
        $this->assertStringContainsString("with('mail_pending_email', \$email)", $controller);
        $this->assertStringContainsString("withErrors(['email' => 'Email tidak terdaftar atau belum aktif.'])", $controller);
        $this->assertStringContainsString("withErrors(['pin' => 'PIN tidak valid.'])", $controller);
    }
}
