<?php

namespace Tests\Feature;

use App\Models\MailAccessAccount;
use App\Models\Role;
use App\Models\User;
use App\Services\MailInboxService;
use App\Services\SimpleImapClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class MailThreadTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<array<string, mixed>>|null */
    public ?array $fakeThread = null;

    private MailAccessAccount $mailAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $test = $this;
        $this->app->instance(MailInboxService::class, new class($test) extends MailInboxService
        {
            public function __construct(private MailThreadTest $test) {}

            public function messageFor(string $email, string $uid, string $folder = self::FOLDER_INBOX): array
            {
                return $this->test->threadMessage($uid, 'hr@rnb.co.id', 'Dimas testin now', 'Fri, 25 Sep 2026 08:29:00 +0700');
            }

            public function threadFor(string $email, array $message, string $folder): array
            {
                return $this->test->fakeThread ?? throw new RuntimeException('IMAP connection failed');
            }
        });

        $this->mailAccount = MailAccessAccount::query()->create([
            'email' => 'department@example.test',
            'type' => MailAccessAccount::TYPE_DEPARTMENT,
            'pin' => Hash::make('1234'),
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function threadMessage(string $uid, string $from, string $body, string $date, array $overrides = []): array
    {
        return [
            'uid' => $uid,
            'from' => $from,
            'to' => 'department@example.test',
            'subject' => 'Testing',
            'date' => $date,
            'time' => date('M d, H:i', strtotime($date)),
            'unread' => false,
            'body' => $body,
            'html_body' => null,
            'attachments' => [],
            'message_id' => "<{$uid}@example.test>",
            'in_reply_to' => '',
            'references' => '',
            ...$overrides,
        ];
    }

    public function test_thread_search_key_matches_message_ids_replies_and_references(): void
    {
        $client = new SimpleImapClient('localhost', 993, 'ssl', 'department@example.test', 'secret');
        $threadSearchKey = new ReflectionMethod($client, 'threadSearchKey');

        $this->assertSame(
            'OR HEADER Message-ID "<a@x>" OR HEADER In-Reply-To "<a@x>" OR HEADER References "<a@x>" '
            .'OR HEADER Message-ID "<b@x>" OR HEADER In-Reply-To "<b@x>" HEADER References "<b@x>"',
            $threadSearchKey->invoke($client, ['<a@x>', '<b@x>'])
        );
        $this->assertNull($threadSearchKey->invoke($client, []));
    }

    public function test_inbox_thread_count_is_read_from_the_reply_chain(): void
    {
        $client = new SimpleImapClient('localhost', 993, 'ssl', 'department@example.test', 'secret');
        $threadCount = new ReflectionMethod($client, 'threadCount');

        $this->assertSame(1, $threadCount->invoke($client, "From: hr@rnb.co.id\nMessage-ID: <first@x>"));
        $this->assertSame(2, $threadCount->invoke($client, "Message-ID: <second@x>\nIn-Reply-To: <first@x>"));
        $this->assertSame(3, $threadCount->invoke($client, "Message-ID: <third@x>\nIn-Reply-To: <SECOND@x>\nReferences: <first@x>\n <second@x> <third@x>"));
    }

    public function test_inbox_list_marks_messages_that_continue_a_conversation(): void
    {
        $this->app->instance(MailInboxService::class, new class extends MailInboxService
        {
            public function messagesFor(string $email, int $limit = 20, string $folder = self::FOLDER_INBOX): array
            {
                return [
                    ['uid' => '13', 'from' => 'Dimas Prayoga <dimasvorx117@gmail.com>', 'to' => 'department@example.test', 'subject' => 'Re: Ini penting 3', 'date' => '', 'time' => 'Oct 20, 11:21', 'unread' => true, 'thread_count' => 3],
                    ['uid' => '12', 'from' => 'hr@rnb.co.id', 'to' => 'department@example.test', 'subject' => 'Undangan', 'date' => '', 'time' => 'Oct 19, 09:00', 'unread' => false, 'thread_count' => 1],
                ];
            }
        });

        $response = $this->actingAsMailUser()->get(route('applicant.email.inbox'));

        $response->assertOk();
        $response->assertSee('<span class="mail-list-thread-count" title="3 pesan dalam percakapan">3</span>', false);
        $this->assertSame(1, substr_count($response->getContent(), 'class="mail-list-thread-count"'));
    }

    public function test_thread_message_ids_keep_the_root_and_the_latest_references(): void
    {
        $mailInbox = new MailInboxService;
        $threadMessageIds = new ReflectionMethod($mailInbox, 'threadMessageIds');
        $references = implode(' ', array_map(fn (int $number): string => "<ref{$number}@x>", range(1, 12)));

        $messageIds = $threadMessageIds->invoke($mailInbox, [
            'references' => $references.' <bad id@x> <"quoted"@x>',
            'in_reply_to' => '<ref12@x>',
            'message_id' => '<current@x>',
        ]);

        $this->assertCount(10, $messageIds);
        $this->assertSame('<ref1@x>', $messageIds[0]);
        $this->assertSame('<current@x>', $messageIds[9]);
        $this->assertNotContains('<bad id@x>', $messageIds);
        $this->assertSame([], $threadMessageIds->invoke($mailInbox, ['message_id' => '', 'references' => '']));
    }

    public function test_message_without_thread_headers_is_its_own_conversation(): void
    {
        $message = $this->threadMessage('5', 'hr@rnb.co.id', 'Halo', 'Fri, 25 Sep 2026 08:29:00 +0700', ['message_id' => '']);

        $thread = (new MailInboxService)->threadFor('department@example.test', $message, MailInboxService::FOLDER_INBOX);

        $this->assertSame([[...$message, 'folder' => 'inbox', 'is_current' => true]], $thread);
    }

    public function test_thread_messages_are_deduplicated_and_ordered_oldest_first(): void
    {
        $mailInbox = new MailInboxService;
        $sortedThreadMessages = new ReflectionMethod($mailInbox, 'sortedThreadMessages');
        $current = [...$this->threadMessage('3', 'hr@rnb.co.id', 'Dimas testin now', 'Fri, 25 Sep 2026 08:29:00 +0700'), 'folder' => 'inbox', 'is_current' => true];
        $original = [...$this->threadMessage('1', 'Dimas Prayoga', 'Testing dimas', 'Tue, 22 Sep 2026 11:07:00 +0700'), 'folder' => 'inbox', 'is_current' => false];
        $reply = [...$this->threadMessage('9', 'hr@rnb.co.id', 'Testing 16:59', 'Wed, 23 Sep 2026 16:59:00 +0700'), 'folder' => 'sent', 'is_current' => false];

        $thread = $sortedThreadMessages->invoke($mailInbox, $current, [
            [...$current, 'is_current' => false],
            $reply,
            $original,
            [...$original, 'uid' => '41', 'folder' => 'sent'],
        ]);

        $this->assertSame(['1', '9', '3'], array_column($thread, 'uid'));
        $this->assertSame(['inbox', 'sent', 'inbox'], array_column($thread, 'folder'));
        $this->assertSame([false, false, true], array_column($thread, 'is_current'));
    }

    public function test_read_page_shows_the_whole_conversation_with_the_opened_message_expanded(): void
    {
        $this->fakeThread = [
            [...$this->threadMessage('1', 'Department <DEPARTMENT@example.test>', 'Testing dimas', 'Tue, 22 Sep 2026 11:07:00 +0700'), 'folder' => 'inbox', 'is_current' => false],
            [...$this->threadMessage('9', 'hr@rnb.co.id', 'Testing 16:59', 'Wed, 23 Sep 2026 16:59:00 +0700', [
                'attachments' => [['id' => '1', 'filename' => 'jadwal.pdf', 'content_type' => 'application/pdf', 'size' => 2048, 'is_image' => false, 'content_id' => null, 'is_inline' => false]],
            ]), 'folder' => 'sent', 'is_current' => false],
            [...$this->threadMessage('3', 'hr@rnb.co.id', 'Dimas testin now', 'Fri, 25 Sep 2026 08:29:00 +0700'), 'folder' => 'inbox', 'is_current' => true],
        ];

        $response = $this->actingAsMailUser()->get(route('applicant.email.read', ['uid' => '3']));

        $response->assertOk();
        $response->assertSee('3 pesan dalam percakapan');
        $response->assertSeeInOrder(['Department &lt;DEPARTMENT@example.test&gt;', 'Testing dimas', 'Testing 16:59', 'Dimas testin now'], false);
        // Own messages (sent by the account, or found in Sent) sit on the right; the other party on the left.
        $this->assertSame(2, substr_count($response->getContent(), 'class="mail-thread-item is-own"'));
        $this->assertSame(1, substr_count($response->getContent(), 'class="mail-thread-item"'));
        $response->assertSee('class="collapse " id="mailThreadMessage0"', false);
        $response->assertSee('class="collapse show" id="mailThreadMessage2"', false);
        $response->assertSee(route('applicant.email.read', ['uid' => '9', 'folder' => 'sent']), false);
        $response->assertSee(route('applicant.email.attachment', ['uid' => '9', 'attachment' => '1', 'folder' => 'sent']), false);
        $response->assertSee('jadwal.pdf');
    }

    public function test_read_page_falls_back_to_the_opened_message_when_the_conversation_cannot_be_loaded(): void
    {
        $response = $this->actingAsMailUser()->get(route('applicant.email.read', ['uid' => '3']));

        $response->assertOk();
        $response->assertSee('Dimas testin now');
        $response->assertSee('class="collapse show" id="mailThreadMessage0"', false);
        $response->assertDontSee('pesan dalam percakapan');
    }

    private function actingAsMailUser(): static
    {
        $user = User::factory()->superAdministrator()->create();
        $user->assignRole(Role::query()->firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']));

        return $this->actingAs($user)
            ->withSession([
                'mail_business_account_id' => $this->mailAccount->id,
                'mail_business_account_selected' => true,
            ]);
    }
}
