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
            'applicant.email.select' => 'applicant/email/select',
            'applicant.email.check' => 'applicant/email/check',
            'applicant.email.login' => 'applicant/email/login',
            'applicant.email.inbox' => 'applicant/email/inbox',
            'applicant.email.sent' => 'applicant/email/sent',
            'applicant.email.destroy' => 'applicant/email/delete',
            'applicant.email.compose' => 'applicant/email/compose',
            'applicant.email.send' => 'applicant/email/send',
            'applicant.email.attachment' => 'applicant/email/read/{uid}/attachments/{attachment}',
            'applicant.email.reply' => 'applicant/email/read/{uid}/reply',
            'applicant.email.read' => 'applicant/email/read/{uid?}',
            'applicant.email.logout' => 'applicant/email/logout',
            'business-email.index' => 'business-email',
            'business-email.select' => 'business-email/select',
            'business-email.check' => 'business-email/check',
            'business-email.login' => 'business-email/login',
            'business-email.inbox' => 'business-email/inbox',
            'business-email.sent' => 'business-email/sent',
            'business-email.destroy' => 'business-email/delete',
            'business-email.compose' => 'business-email/compose',
            'business-email.send' => 'business-email/send',
            'business-email.attachment' => 'business-email/read/{uid}/attachments/{attachment}',
            'business-email.reply' => 'business-email/read/{uid}/reply',
            'business-email.read' => 'business-email/read/{uid?}',
            'business-email.logout' => 'business-email/logout',
        ];

        foreach ($routes as $routeName => $uri) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route);
            $this->assertSame($uri, $route?->uri());
        }

        $this->assertSame(MailController::class.'@index', Route::getRoutes()->getByName('applicant.email.index')?->getActionName());
        $this->assertSame(MailController::class.'@selectAccount', Route::getRoutes()->getByName('applicant.email.select')?->getActionName());
        $this->assertSame(MailController::class.'@checkEmail', Route::getRoutes()->getByName('applicant.email.check')?->getActionName());
        $this->assertSame(MailController::class.'@authenticate', Route::getRoutes()->getByName('applicant.email.login')?->getActionName());
        $this->assertSame(MailController::class.'@inbox', Route::getRoutes()->getByName('applicant.email.inbox')?->getActionName());
        $this->assertSame(MailController::class.'@sent', Route::getRoutes()->getByName('applicant.email.sent')?->getActionName());
        $this->assertSame(MailController::class.'@destroy', Route::getRoutes()->getByName('applicant.email.destroy')?->getActionName());
        $this->assertSame(MailController::class.'@compose', Route::getRoutes()->getByName('applicant.email.compose')?->getActionName());
        $this->assertSame(MailController::class.'@send', Route::getRoutes()->getByName('applicant.email.send')?->getActionName());
        $this->assertSame(MailController::class.'@attachment', Route::getRoutes()->getByName('applicant.email.attachment')?->getActionName());
        $this->assertSame(MailController::class.'@reply', Route::getRoutes()->getByName('applicant.email.reply')?->getActionName());
        $this->assertSame(MailController::class.'@read', Route::getRoutes()->getByName('applicant.email.read')?->getActionName());
        $this->assertSame(MailController::class.'@logout', Route::getRoutes()->getByName('applicant.email.logout')?->getActionName());
        $this->assertSame(MailController::class.'@index', Route::getRoutes()->getByName('business-email.index')?->getActionName());
        $this->assertSame(MailController::class.'@selectAccount', Route::getRoutes()->getByName('business-email.select')?->getActionName());
        $this->assertContains('position.permission:view-email-management', Route::getRoutes()->getByName('applicant.email.index')?->gatherMiddleware() ?? []);
        $this->assertContains('position.permission:view-business-email', Route::getRoutes()->getByName('business-email.index')?->gatherMiddleware() ?? []);
    }

    public function test_mail_views_are_wired_to_routes(): void
    {
        $loginView = File::get(resource_path('views/mail/index.blade.php'));
        $inboxView = File::get(resource_path('views/mail/inbox.blade.php'));
        $composeView = File::get(resource_path('views/mail/compose.blade.php'));
        $readView = File::get(resource_path('views/mail/read.blade.php'));
        $mailSummaryCardsView = File::get(resource_path('views/mail/partials/summary-cards.blade.php'));
        $mailAccountSelectorStyles = File::get(resource_path('views/mail/partials/account-selector-styles.blade.php'));
        $mailAccountSelectorScript = File::get(resource_path('views/mail/partials/account-selector-script.blade.php'));
        $mailSidebarView = File::get(resource_path('views/mail/partials/sidebar.blade.php'));
        $sidebarView = File::get(resource_path('views/layouts/sidebar.blade.php'));
        $controller = File::get(app_path('Http/Controllers/MailController.php'));
        $inboxService = File::get(app_path('Services/MailInboxService.php'));
        $mailConfig = File::get(config_path('mail.php'));
        $mailInboxConfig = File::get(config_path('mail_inboxes.php'));
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));
        $mailAccessSeeder = File::get(database_path('seeders/MailAccessAccountSeeder.php'));
        $mailAccessTypeMigration = File::get(database_path('migrations/2026_10_05_150204_add_type_to_mail_access_accounts_table.php'));
        $mailAccessCompanyMigration = File::get(database_path('migrations/2026_10_05_160923_add_company_id_to_mail_access_accounts_table.php'));
        $mailAccessApplicantTypeMigration = File::get(database_path('migrations/2026_10_06_085102_update_applicant_mail_sender_type_on_mail_access_accounts_table.php'));
        $mailAccessDropApplicantSenderMigration = File::get(database_path('migrations/2026_10_06_085103_drop_is_applicant_mail_sender_from_mail_access_accounts_table.php'));
        $businessEmailPermissionMigration = File::get(database_path('migrations/2026_10_07_143106_add_view_business_email_permission.php'));
        $positionPermissionSeeder = File::get(database_path('seeders/PositionPermissionSeeder.php'));
        $authorizationController = File::get(app_path('Http/Controllers/AuthorizationController.php'));

        $this->assertStringContainsString('redirect()->route($this->routeName($request, \'inbox\'))', $controller);
        $this->assertStringContainsString('$this->forgetSelectedAccount($request);', $controller);
        $this->assertStringContainsString('selectAccount(Request $request)', $controller);
        $this->assertStringContainsString("'mail_access_account_id' => ['required', 'integer']", $controller);
        $this->assertStringContainsString('$request->session()->put($this->sessionKey($request), $account->id)', $controller);
        $this->assertStringContainsString('$request->session()->put($this->selectionSessionKey($request), true)', $controller);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.check')", $loginView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.login')", $loginView);
        $this->assertStringContainsString("@include('mail.partials.summary-cards')", $loginView);
        $this->assertStringContainsString('name="pin_digits[]"', $loginView);
        $this->assertStringContainsString('mail-pin-grid', $loginView);
        $this->assertStringContainsString('Socials', $inboxView);
        $this->assertStringContainsString('Promotion', $inboxView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.read', \$readRouteParameters)", $inboxView);
        $this->assertStringContainsString('route($folderRoute)', $inboxView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.destroy')", $inboxView);
        $this->assertStringContainsString('data-delete-confirmation-form', $inboxView);
        $this->assertStringContainsString('Belum ada email terkirim.', $inboxView);
        $this->assertStringContainsString('name="search"', $inboxView);
        $this->assertStringContainsString('Tidak ada email yang cocok dengan pencarian.', $inboxView);
        $this->assertStringContainsString('Pilih email account terlebih dahulu.', $inboxView);
        $this->assertStringContainsString('$hasAvailableMailAccounts = $mailAccounts->isNotEmpty();', $inboxView);
        $this->assertStringContainsString('mail-empty-info', $inboxView);
        $this->assertStringContainsString('fa-circle-info', $inboxView);
        $this->assertStringContainsString('Hubungi administrator untuk mendapatkan akses email personal business', $inboxView);
        $this->assertStringContainsString('mail-list-clean', $inboxView);
        $this->assertStringContainsString("@include('mail.partials.summary-cards')", $inboxView);
        $this->assertStringContainsString('@forelse ($messages as $mail)', $inboxView);
        $this->assertStringContainsString('Inbox kosong.', $inboxView);
        $this->assertStringContainsString('compose-wrapper', $composeView);
        $this->assertStringContainsString("@include('mail.partials.summary-cards')", $composeView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.send')", $composeView);
        $this->assertStringContainsString('enctype="multipart/form-data"', $composeView);
        $this->assertStringContainsString('name="attachments[]"', $composeView);
        $this->assertStringContainsString('data-mail-attachment-input', $composeView);
        $this->assertStringContainsString('data-mail-attachment-list', $composeView);
        $this->assertStringContainsString('mail-selected-file', $composeView);
        $this->assertStringNotContainsString('Socials', $composeView);
        $this->assertStringContainsString('read-wapper', $readView);
        $this->assertStringContainsString("@include('mail.partials.summary-cards')", $readView);
        $this->assertStringContainsString('$message[\'body\']', $readView);
        $this->assertStringContainsString('mail-attachments', $readView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.attachment'", $readView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.reply'", $readView);
        $this->assertStringContainsString('name="attachments[]"', $readView);
        $this->assertStringContainsString('name="body"', $readView);
        $this->assertStringContainsString('data-mail-attachment-input', $readView);
        $this->assertStringContainsString('data-mail-attachment-list', $readView);
        $this->assertStringContainsString('mail-selected-file', $readView);
        $this->assertStringContainsString('mail-reply-editor', $readView);
        $this->assertStringContainsString('mail-reply-target', $readView);
        $this->assertStringContainsString('Reply akan dikirim ke', $readView);
        $this->assertStringContainsString('$replyTo ?? $message[\'from\']', $readView);
        $this->assertStringContainsString('Email Account', $mailSummaryCardsView);
        $this->assertStringContainsString('Storage Email', $mailSummaryCardsView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.select')", $mailSummaryCardsView);
        $this->assertStringContainsString('name="mail_access_account_id"', $mailSummaryCardsView);
        $this->assertStringContainsString('data-mail-account-trigger', $mailSummaryCardsView);
        $this->assertStringContainsString('data-mail-account-menu', $mailSummaryCardsView);
        $this->assertStringContainsString('mail-account-select2', $mailSummaryCardsView);
        $this->assertStringContainsString('Cari email account', $mailSummaryCardsView);
        $this->assertStringContainsString('$mailSummaryEmail', $mailSummaryCardsView);
        $this->assertStringContainsString('$mailSummaryStorage', $mailSummaryCardsView);
        $this->assertStringNotContainsString('10MB / 100MB', $mailSummaryCardsView);
        $this->assertStringNotContainsString('effect bg-success', $mailSummaryCardsView);
        $this->assertStringNotContainsString('effect bg-secondary', $mailSummaryCardsView);
        $this->assertStringNotContainsString('progress-bar-striped', $mailSummaryCardsView);
        $this->assertStringNotContainsString('repeating-linear-gradient', $mailSummaryCardsView);
        $this->assertStringContainsString('style="width: 100%; height:5px;" aria-label="Email account active"', $mailSummaryCardsView);
        $this->assertStringContainsString('style="width: 100%; height:5px;" aria-label="Email storage usage"', $mailSummaryCardsView);
        $this->assertStringNotContainsString('$mailSummaryAccount ? \'100\' : \'0\'', $mailSummaryCardsView);
        $this->assertStringContainsString("asset('assets/vendor/select2/css/select2.min.css')", $mailAccountSelectorStyles);
        $this->assertStringContainsString('.mail-account-picker.is-open .mail-account-trigger i', $mailAccountSelectorStyles);
        $this->assertStringNotContainsString('.mail-account-menu::before', $mailAccountSelectorStyles);
        $this->assertStringNotContainsString('linear-gradient(90deg, #22c55e', $mailAccountSelectorStyles);
        $this->assertStringContainsString('.mail-account-menu .selection', $mailAccountSelectorStyles);
        $this->assertStringContainsString('display: none !important;', $mailAccountSelectorStyles);
        $this->assertStringContainsString('.mail-account-menu .select2-search--dropdown .select2-search__field', $mailAccountSelectorStyles);
        $this->assertStringContainsString('.mail-account-menu .select2-results__option--highlighted[aria-selected]', $mailAccountSelectorStyles);
        $this->assertStringContainsString("asset('assets/vendor/select2/js/select2.full.min.js')", $mailAccountSelectorScript);
        $this->assertStringContainsString('selectElement.select2({', $mailAccountSelectorScript);
        $this->assertStringContainsString("dropdownCssClass: 'mail-account-select2-dropdown'", $mailAccountSelectorScript);
        $this->assertStringContainsString('minimumResultsForSearch: 0', $mailAccountSelectorScript);
        $this->assertStringContainsString("picker.toggleClass('is-open'", $mailAccountSelectorScript);
        $this->assertStringContainsString('selectElement.select2(\'open\')', $mailAccountSelectorScript);
        $this->assertStringNotContainsString('onchange=', $mailSummaryCardsView);
        $this->assertStringNotContainsString('Login sebagai', $mailSidebarView);
        $this->assertStringNotContainsString('Keluar Email', $mailSidebarView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.compose')", $mailSidebarView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.inbox')", $mailSidebarView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.sent')", $mailSidebarView);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.destroy')", $readView);
        $this->assertStringNotContainsString('Categories', $mailSidebarView);
        $this->assertStringContainsString('MailInboxService $mailInbox', $controller);
        $this->assertStringContainsString("private const BUSINESS_ROUTE_PREFIX = 'business-email';", $controller);
        $this->assertStringContainsString("private const BUSINESS_SESSION_KEY = 'business_mail_access_account_id';", $controller);
        $this->assertStringContainsString("private const SESSION_SELECTED_KEY = 'mail_access_account_selected';", $controller);
        $this->assertStringContainsString("private const BUSINESS_SESSION_SELECTED_KEY = 'business_mail_access_account_selected';", $controller);
        $this->assertStringContainsString('selectionSessionKey(Request $request): string', $controller);
        $this->assertStringContainsString('forgetSelectedAccount(Request $request): void', $controller);
        $this->assertStringContainsString('$request->session()->get($this->selectionSessionKey($request), false) !== true', $controller);
        $this->assertStringNotContainsString('session()->boolean(', $controller);
        $this->assertStringContainsString('authorizeMailFeatureAccess($request)', $controller);
        $this->assertStringContainsString('availableAccountQuery($request)', $controller);
        $this->assertStringContainsString("'mailAccounts' => \$this->availableAccountQuery(\$request)", $controller);
        $this->assertStringContainsString("'selectedMailAccountId' => \$selectedAccount?->id", $controller);
        $this->assertStringContainsString("where('type', MailAccessAccount::TYPE_PERSONAL)", $controller);
        $this->assertStringContainsString("where('employee_id', \$employeeId)", $controller);
        $this->assertStringContainsString("where('type', '!=', MailAccessAccount::TYPE_PERSONAL)", $controller);
        $this->assertStringContainsString("hasAnyPositionPermission(['view-business-email'])", $controller);
        $this->assertStringContainsString("hasAnyPositionPermission(['view-email-management'])", $controller);
        $this->assertStringContainsString('isAdminOrSuperAdministrator', $controller);
        $this->assertStringContainsString("'mailFeatureTitle' => \$this->mailFeatureTitle(\$request)", $controller);
        $this->assertStringContainsString("'mailRoutePrefix' => \$this->routePrefix(\$request)", $controller);
        $this->assertStringContainsString('$request->query(\'search\', \'\')', $controller);
        $this->assertStringContainsString('filterMessages($messages, $searchQuery)', $controller);
        $this->assertStringContainsString('messageFor($account->email, $uid, $folder)', $controller);
        $this->assertStringContainsString('emailDomain((string) $account->email)', $controller);
        $this->assertStringContainsString("'replyTo' => \$message ? \$this->replyRecipient(\$message, \$folder)", $controller);
        $this->assertStringContainsString('attachmentFor($account->email, $uid, $attachment, $this->folderFromRequest($request))', $controller);
        $this->assertStringContainsString("'attachments.*' => ['file', 'max:10240']", $controller);
        $this->assertStringContainsString('Mail::mailer($this->mailerForAccount($account))->html', $controller);
        $this->assertStringContainsString('outgoingHtmlBody($body)', $controller);
        $this->assertStringContainsString('applyReplyHeaders($message, $replyHeaders)', $controller);
        $this->assertStringContainsString("addTextHeader('In-Reply-To'", $controller);
        $this->assertStringContainsString('sendOutgoingMail(', $controller);
        $this->assertStringContainsString('UID SEARCH ALL', $inboxService);
        $this->assertStringContainsString('verifySsl:', $inboxService);
        $this->assertStringContainsString('stream_context_create', $inboxService);
        $this->assertStringContainsString("'verify_peer' => \$this->verifySsl", $inboxService);
        $this->assertStringContainsString('$emailDomain = $this->emailDomain($email)', $inboxService);
        $this->assertStringContainsString('$this->emailDomain((string) ($account[\'username\'] ?? \'\')) !== $emailDomain', $inboxService);
        $this->assertStringContainsString('recipientFilterFor', $inboxService);
        $this->assertStringContainsString('accountEmailFilterMode', $inboxService);
        $this->assertStringContainsString('messageBelongsToEmail', $inboxService);
        $this->assertStringContainsString('Delivered-To', $inboxService);
        $this->assertStringContainsString('X-Original-To', $inboxService);
        $this->assertStringContainsString('BODY.PEEK[]', $inboxService);
        $this->assertStringContainsString("'message_id' => \$this->headerValue(\$rawHeaders, 'Message-ID')", $inboxService);
        $this->assertStringContainsString('filenameFromHeaders', $inboxService);
        $this->assertStringContainsString('MailAccessAccountSeeder::class', $databaseSeeder);
        $this->assertStringNotContainsString("config('mail_inboxes.accounts', [])", $mailAccessSeeder);
        $this->assertStringContainsString('seedHrAccessAccounts', $mailAccessSeeder);
        $this->assertStringContainsString("['email' => 'hr@'.\$domain]", $mailAccessSeeder);
        $this->assertStringContainsString('seedDepartmentAccounts', $mailAccessSeeder);
        $this->assertStringContainsString("'type' => MailAccessAccount::TYPE_APPLICANT_NOTIFICATION", $mailAccessSeeder);
        $this->assertStringContainsString("'company_id' => \$this->companyIdForBrand", $mailAccessSeeder);
        $this->assertStringContainsString("env('MAIL_ACCESS_DEFAULT_PIN', '0000')", $mailAccessSeeder);
        $this->assertStringContainsString('CATCHALL_MAIL_USERNAME', $mailConfig);
        $this->assertStringContainsString('CATCHALL_MAIL_PASSWORD', $mailConfig);
        $this->assertStringContainsString('HR_MAIL_PASSWORD', $mailConfig);
        $this->assertStringContainsString("'rnb_hr' => [", $mailConfig);
        $this->assertStringContainsString("env('RNB_HR_MAIL_USERNAME', 'hr@rnb.co.id')", $mailConfig);
        $this->assertStringContainsString("'niskala_hr' => [", $mailConfig);
        $this->assertStringContainsString("env('NISKALA_HR_MAIL_USERNAME', 'hr@coffeeniskala.com')", $mailConfig);
        $this->assertStringContainsString("'tms_hr' => [", $mailConfig);
        $this->assertStringContainsString("env('TMS_HR_MAIL_USERNAME', 'hr@tims.co.id')", $mailConfig);
        $this->assertStringContainsString("'rne_hr' => [", $mailConfig);
        $this->assertStringContainsString("env('RNE_HR_MAIL_USERNAME', 'hr@rne.co.id')", $mailConfig);
        $this->assertStringContainsString("'trah_hr' => [", $mailConfig);
        $this->assertStringContainsString("env('TRAH_HR_MAIL_USERNAME', 'hr@trah.co.id')", $mailConfig);
        $this->assertStringContainsString("'kma_hr' => [", $mailConfig);
        $this->assertStringContainsString("env('KMA_HR_MAIL_USERNAME', 'hr@karpetmerah.id')", $mailConfig);
        $this->assertStringContainsString("\$catchAllUsername('rnb.co.id')", $mailConfig);
        $this->assertStringNotContainsString('RNB_MAIL_USERNAME', $mailConfig);
        $this->assertStringNotContainsString('recruitment@rnb.co.id', $mailConfig);
        $this->assertStringContainsString("'andalanku' => [", $mailInboxConfig);
        $this->assertStringContainsString('HR_MAIL_PASSWORD', $mailInboxConfig);
        $this->assertStringContainsString("'rnb_hr' => [", $mailInboxConfig);
        $this->assertStringContainsString("env('RNB_HR_IMAP_USERNAME', 'hr@rnb.co.id')", $mailInboxConfig);
        $this->assertStringContainsString("'niskala_hr' => [", $mailInboxConfig);
        $this->assertStringContainsString("env('NISKALA_HR_IMAP_USERNAME', 'hr@coffeeniskala.com')", $mailInboxConfig);
        $this->assertStringContainsString("'tms_hr' => [", $mailInboxConfig);
        $this->assertStringContainsString("env('TMS_HR_IMAP_USERNAME', 'hr@tims.co.id')", $mailInboxConfig);
        $this->assertStringContainsString("'rne_hr' => [", $mailInboxConfig);
        $this->assertStringContainsString("env('RNE_HR_IMAP_USERNAME', 'hr@rne.co.id')", $mailInboxConfig);
        $this->assertStringContainsString("'trah_hr' => [", $mailInboxConfig);
        $this->assertStringContainsString("env('TRAH_HR_IMAP_USERNAME', 'hr@trah.co.id')", $mailInboxConfig);
        $this->assertStringContainsString("'kma_hr' => [", $mailInboxConfig);
        $this->assertStringContainsString("env('KMA_HR_IMAP_USERNAME', 'hr@karpetmerah.id')", $mailInboxConfig);
        foreach (['RNB', 'ANDALANKU', 'NISKALA', 'TMS', 'RNE', 'TRAH', 'KMA'] as $brandKey) {
            $this->assertStringContainsString("env('{$brandKey}_IMAP_HOST'", $mailInboxConfig);
            $this->assertStringContainsString("env('{$brandKey}_IMAP_USERNAME'", $mailInboxConfig);
            $this->assertStringContainsString("env('{$brandKey}_IMAP_PASSWORD'", $mailInboxConfig);
            $this->assertStringContainsString("env('{$brandKey}_IMAP_VERIFY_SSL'", $mailInboxConfig);
        }
        $this->assertStringContainsString("string('type', 30)", $mailAccessTypeMigration);
        $this->assertStringContainsString("->default('personal')", $mailAccessTypeMigration);
        $this->assertStringContainsString("'applicant_notification'", $mailAccessApplicantTypeMigration);
        $this->assertStringContainsString("->where('email', 'like', 'recruitment@%')", $mailAccessApplicantTypeMigration);
        $this->assertStringContainsString("dropColumn('is_applicant_mail_sender')", $mailAccessDropApplicantSenderMigration);
        $this->assertStringContainsString("foreignUuid('company_id')", $mailAccessCompanyMigration);
        $this->assertStringContainsString("->constrained('companies', 'id')", $mailAccessCompanyMigration);
        $this->assertStringContainsString('->nullOnDelete()', $mailAccessCompanyMigration);
        $this->assertStringContainsString("route('applicant.email.index')", $sidebarView);
        $this->assertStringContainsString("route('business-email.index')", $sidebarView);
        $this->assertStringContainsString('Business Email', $sidebarView);
        $this->assertStringContainsString("canViewSidebarMenu('view-business-email')", $sidebarView);
        $this->assertStringContainsString('! $isAdminOrSuperAdministrator', $sidebarView);
        $this->assertStringContainsString("'name' => 'view-business-email'", $businessEmailPermissionMigration);
        $this->assertStringContainsString('KEY_ADMINISTRATOR', $businessEmailPermissionMigration);
        $this->assertStringContainsString('KEY_SUPER_ADMINISTRATOR', $businessEmailPermissionMigration);
        $this->assertStringContainsString("'permission' => 'view-business-email'", $positionPermissionSeeder);
        $this->assertStringContainsString("'view-business-email' => ['section' => 'Siap', 'label' => 'Business Email']", $authorizationController);
        $this->assertStringNotContainsString("route('mail.index')", $sidebarView);
    }

    public function test_mail_account_is_selected_from_summary_card_without_mail_login(): void
    {
        $summaryCardsView = File::get(resource_path('views/mail/partials/summary-cards.blade.php'));
        $mailAccountSelectorScript = File::get(resource_path('views/mail/partials/account-selector-script.blade.php'));
        $mailSidebarView = File::get(resource_path('views/mail/partials/sidebar.blade.php'));
        $inboxView = File::get(resource_path('views/mail/inbox.blade.php'));
        $controller = File::get(app_path('Http/Controllers/MailController.php'));

        $this->assertStringContainsString('selectAccount(Request $request)', $controller);
        $this->assertStringContainsString("'mail_access_account_id' => ['required', 'integer']", $controller);
        $this->assertStringContainsString('if ($account !== null) {', $controller);
        $this->assertStringContainsString("route(\$mailRoutePrefix.'.select')", $summaryCardsView);
        $this->assertStringContainsString('data-mail-account-trigger', $summaryCardsView);
        $this->assertStringContainsString('mail-account-select2', $summaryCardsView);
        $this->assertStringNotContainsString('onchange=', $summaryCardsView);
        $this->assertStringContainsString('selectElement.select2(\'open\')', $mailAccountSelectorScript);
        $this->assertStringContainsString('this.form.submit();', $mailAccountSelectorScript);
        $this->assertStringContainsString('Pilih email account', $summaryCardsView);
        $this->assertStringContainsString('Pilih email account terlebih dahulu.', $inboxView);
        $this->assertStringNotContainsString('Login sebagai', $mailSidebarView);
        $this->assertStringNotContainsString('Keluar Email', $mailSidebarView);
        $this->assertStringContainsString('->visibleForMailAccess()', $controller);
    }
}
