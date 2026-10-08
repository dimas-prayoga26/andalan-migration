<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeProfile;
use App\Models\MailAccessAccount;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\MailInboxService;
use App\Services\SimpleImapClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class MailSentAndDeleteTest extends TestCase
{
    use RefreshDatabase;

    private FakeMailInboxService $mailInbox;

    private MailAccessAccount $mailAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailInbox = new FakeMailInboxService;
        $this->app->instance(MailInboxService::class, $this->mailInbox);

        $this->mailAccount = MailAccessAccount::query()->create([
            'email' => 'hr@example.test',
            'type' => MailAccessAccount::TYPE_DEPARTMENT,
            'pin' => Hash::make('1234'),
            'is_active' => true,
        ]);
    }

    public function test_sent_page_lists_messages_from_sent_folder(): void
    {
        $this->mailInbox->messages = [
            ['uid' => '7', 'from' => 'hr@example.test', 'to' => 'candidate@example.test', 'subject' => 'Interview Invitation', 'date' => 'Fri, 02 Oct 2026 10:00:00 +0700', 'time' => 'Oct 02, 10:00', 'unread' => false],
        ];

        $response = $this->actingAsMailUser()->get(route('applicant.email.sent'));

        $response->assertOk();
        $response->assertSee('To: candidate@example.test');
        $response->assertSee('Interview Invitation');
        $response->assertSee(route('applicant.email.read', ['uid' => '7', 'folder' => 'sent']), false);
        $this->assertSame([['messagesFor', 'hr@example.test', 'sent']], $this->mailInbox->calls);
    }

    public function test_sent_page_shows_empty_state(): void
    {
        $response = $this->actingAsMailUser()->get(route('applicant.email.sent'));

        $response->assertOk();
        $response->assertSee('Belum ada email terkirim.');
    }

    public function test_inbox_page_still_reads_inbox_folder(): void
    {
        $response = $this->actingAsMailUser()->get(route('applicant.email.inbox'));

        $response->assertOk();
        $response->assertSee('Inbox kosong.');
        $this->assertSame([['messagesFor', 'hr@example.test', 'inbox']], $this->mailInbox->calls);
    }

    public function test_selected_messages_can_be_deleted(): void
    {
        $response = $this->actingAsMailUser()->post(route('applicant.email.destroy'), [
            'folder' => 'inbox',
            'uids' => ['12', '15', '12'],
        ]);

        $response->assertRedirect(route('applicant.email.inbox'));
        $response->assertSessionHas('mail_status', '2 email berhasil dihapus.');
        $this->assertSame([['deleteMessages', 'hr@example.test', ['12', '15'], 'inbox']], $this->mailInbox->calls);
    }

    public function test_sent_messages_can_be_deleted(): void
    {
        $response = $this->actingAsMailUser()->post(route('applicant.email.destroy'), [
            'folder' => 'sent',
            'uids' => ['3'],
        ]);

        $response->assertRedirect(route('applicant.email.sent'));
        $this->assertSame([['deleteMessages', 'hr@example.test', ['3'], 'sent']], $this->mailInbox->calls);
    }

    public function test_delete_rejects_invalid_input(): void
    {
        $this->actingAsMailUser()
            ->from(route('applicant.email.inbox'))
            ->post(route('applicant.email.destroy'), ['folder' => 'inbox', 'uids' => ['1 2 3']])
            ->assertSessionHasErrors('uids.0');

        $this->post(route('applicant.email.destroy'), ['folder' => 'trash', 'uids' => ['1']])
            ->assertSessionHasErrors('folder');

        $this->post(route('applicant.email.destroy'), ['folder' => 'inbox'])
            ->assertSessionHasErrors(['uids' => 'Pilih minimal satu email yang akan dihapus.']);

        $this->assertSame([], $this->mailInbox->calls);
    }

    public function test_delete_failure_is_reported_to_user(): void
    {
        $this->mailInbox->failOn = 'deleteMessages';

        $response = $this->actingAsMailUser()->post(route('applicant.email.destroy'), [
            'folder' => 'inbox',
            'uids' => ['5'],
        ]);

        $response->assertRedirect(route('applicant.email.inbox'));
        $response->assertSessionHasErrors(['mail' => 'Email belum bisa dihapus. Coba refresh lalu ulangi.']);
    }

    public function test_delete_requires_selected_mail_account(): void
    {
        $this->actingAs($this->createSuperAdministratorUser())
            ->post(route('applicant.email.destroy'), ['folder' => 'inbox', 'uids' => ['5']])
            ->assertRedirect(route('applicant.email.inbox'));

        $this->assertSame([], $this->mailInbox->calls);
    }

    public function test_sent_email_is_copied_to_sent_folder(): void
    {
        $response = $this->actingAsMailUser()->post(route('applicant.email.send'), [
            'to' => 'candidate@example.test',
            'subject' => 'Offering Letter',
            'body' => 'Halo kandidat',
        ]);

        $response->assertRedirect(route('applicant.email.sent'));
        $response->assertSessionHas('mail_status', 'Email berhasil dikirim.');
        $this->assertCount(1, $this->mailInbox->storedSentMessages);
        $this->assertSame('hr@example.test', $this->mailInbox->storedSentMessages[0]['email']);
        $this->assertStringContainsString('Subject: Offering Letter', $this->mailInbox->storedSentMessages[0]['raw']);
        $this->assertStringContainsString('To: candidate@example.test', $this->mailInbox->storedSentMessages[0]['raw']);
    }

    public function test_sending_still_succeeds_when_sent_copy_fails(): void
    {
        $this->mailInbox->failOn = 'storeSentMessage';

        $response = $this->actingAsMailUser()->post(route('applicant.email.send'), [
            'to' => 'candidate@example.test',
            'subject' => 'Offering Letter',
            'body' => 'Halo kandidat',
        ]);

        $response->assertRedirect(route('applicant.email.sent'));
        $response->assertSessionHas('mail_status', 'Email berhasil dikirim.');
        $response->assertSessionHasNoErrors();
    }

    public function test_reading_sent_message_replies_to_recipient(): void
    {
        $response = $this->actingAsMailUser()->get(route('applicant.email.read', ['uid' => '9', 'folder' => 'sent']));

        $response->assertOk();
        $response->assertSee('Reply akan dikirim ke candidate@example.test');
        $response->assertSee('id="mail-delete-form"', false);
        $response->assertSee(route('applicant.email.reply', ['9', 'folder' => 'sent']), false);
        $this->assertSame([['messageFor', 'hr@example.test', '9', 'sent']], $this->mailInbox->calls);
    }

    public function test_reply_from_sent_message_is_sent_to_recipient_and_stored(): void
    {
        $response = $this->actingAsMailUser()->post(route('applicant.email.reply', ['9', 'folder' => 'sent']), [
            'body' => 'Follow up',
        ]);

        $response->assertRedirect(route('applicant.email.read', ['uid' => '9', 'folder' => 'sent']));
        $this->assertCount(1, $this->mailInbox->storedSentMessages);
        $this->assertStringContainsString('To: candidate@example.test', $this->mailInbox->storedSentMessages[0]['raw']);
        $this->assertStringContainsString('Subject: Re: Interview Invitation', $this->mailInbox->storedSentMessages[0]['raw']);
    }

    public function test_special_mailbox_is_resolved_from_imap_list(): void
    {
        $client = new SimpleImapClient('localhost', 993, 'ssl', 'hr@example.test', 'secret');
        $parse = new ReflectionMethod($client, 'parseMailboxList');
        $match = new ReflectionMethod($client, 'matchSpecialMailbox');

        $cpanelMailboxes = $parse->invoke($client, [
            '* LIST (\HasNoChildren) "." INBOX',
            '* LIST (\HasNoChildren \UnMarked) "." "INBOX.Drafts"',
            '* LIST (\HasNoChildren) "." "INBOX.Sent"',
            '* LIST (\HasNoChildren) "." INBOX.Trash',
            'A0003 OK List completed.',
        ]);
        $specialUseMailboxes = $parse->invoke($client, [
            '* LIST (\HasNoChildren) "/" "INBOX"',
            '* LIST (\HasNoChildren \Sent) "/" "Terkirim"',
        ]);

        $this->assertCount(4, $cpanelMailboxes);
        $this->assertSame('INBOX.Sent', $match->invoke($client, $cpanelMailboxes, '\\Sent', ['Sent']));
        $this->assertSame('INBOX.Trash', $match->invoke($client, $cpanelMailboxes, '\\Trash', ['Trash']));
        $this->assertSame('Terkirim', $match->invoke($client, $specialUseMailboxes, '\\Sent', ['Sent']));
        $this->assertNull($match->invoke($client, $specialUseMailboxes, '\\Trash', ['Trash']));
    }

    private function actingAsMailUser(): static
    {
        return $this->actingAs($this->createSuperAdministratorUser())
            ->withSession(['mail_access_account_id' => $this->mailAccount->id]);
    }

    private function createSuperAdministratorUser(): User
    {
        $user = User::query()->create([
            'username' => 'superadmin_'.uniqid(),
            'email' => uniqid().'@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $user->assignRole(Role::query()->firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']));

        $position = Position::query()->firstOrCreate(['name' => 'Super Administrator']);
        $employee = Employee::query()->create(['user_id' => $user->id, 'status' => 'Active']);
        EmployeeProfile::query()->create(['employee_id' => $employee->id, 'name' => 'Super Administrator']);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_position_id' => $position->id,
            'status' => 'Active',
        ]);

        return $user;
    }
}

class FakeMailInboxService extends MailInboxService
{
    /** @var list<array<string, mixed>> */
    public array $messages = [];

    /** @var list<array<int, mixed>> */
    public array $calls = [];

    /** @var list<array{email: string, raw: string}> */
    public array $storedSentMessages = [];

    public ?string $failOn = null;

    public function messagesFor(string $email, int $limit = 20, string $folder = self::FOLDER_INBOX): array
    {
        $this->calls[] = ['messagesFor', $email, $folder];

        return $this->messages;
    }

    public function messageFor(string $email, string $uid, string $folder = self::FOLDER_INBOX): array
    {
        $this->calls[] = ['messageFor', $email, $uid, $folder];

        return [
            'uid' => $uid,
            'from' => 'HR <hr@example.test>',
            'to' => 'Candidate <candidate@example.test>',
            'subject' => 'Interview Invitation',
            'date' => 'Fri, 02 Oct 2026 10:00:00 +0700',
            'time' => 'Oct 02, 10:00',
            'unread' => false,
            'body' => 'Isi email',
            'attachments' => [],
            'message_id' => '<abc@example.test>',
            'references' => '',
        ];
    }

    public function deleteMessages(string $email, array $uids, string $folder = self::FOLDER_INBOX): void
    {
        $this->failIfRequested('deleteMessages');
        $this->calls[] = ['deleteMessages', $email, $uids, $folder];
    }

    public function storeSentMessage(string $email, string $rawMessage): void
    {
        $this->failIfRequested('storeSentMessage');
        $this->storedSentMessages[] = ['email' => $email, 'raw' => $rawMessage];
    }

    private function failIfRequested(string $method): void
    {
        if ($this->failOn === $method) {
            throw new RuntimeException("{$method} failed");
        }
    }
}
