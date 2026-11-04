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
use Tests\TestCase;

class MailHtmlRenderingTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $fakeMessage = [];

    private MailAccessAccount $mailAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $test = $this;
        $this->app->instance(MailInboxService::class, new class($test) extends MailInboxService
        {
            public function __construct(private MailHtmlRenderingTest $test) {}

            public function messageFor(string $email, string $uid, string $folder = self::FOLDER_INBOX): array
            {
                return $this->test->fakeMessage($uid);
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
    public function fakeMessage(string $uid): array
    {
        return [
            'uid' => $uid,
            'from' => 'The Postman Team <noreply@example.test>',
            'to' => 'department@example.test',
            'subject' => 'Verification code',
            'date' => 'Tue, 20 Oct 2026 03:29:12 +0000',
            'time' => 'Oct 20, 10:29',
            'unread' => false,
            'body' => 'Please use the code 701075',
            'html_body' => null,
            'attachments' => [],
            'message_id' => '<code@example.test>',
            'references' => '',
            ...$this->fakeMessage,
        ];
    }

    public function test_html_only_email_keeps_html_and_plain_body_has_no_css_source(): void
    {
        $rawHeaders = "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 7bit";
        $rawBody = '<html><head><title>Code</title><style>body{margin:0;font-size:14px}</style></head><body><p>Please use the code</p><div id="otp-code">701075</div></body></html>';

        $content = $this->messageContent($rawHeaders, $rawBody);

        $this->assertSame("Please use the code\n701075", $content['body']);
        $this->assertStringNotContainsString('margin:0', $content['body']);
        $this->assertSame($rawBody, $content['html_body']);
    }

    public function test_inline_images_are_collected_with_content_ids_and_plain_text_still_wins_for_body(): void
    {
        $rawHeaders = 'Content-Type: multipart/related; boundary="related"';
        $rawBody = implode("\r\n", [
            '--related',
            'Content-Type: multipart/alternative; boundary="alternative"',
            '',
            '--alternative',
            'Content-Type: text/plain; charset=ISO-8859-1',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            'Caf=E9 terbuka',
            '--alternative',
            'Content-Type: text/html; charset=UTF-8',
            '',
            '<p>Cafe terbuka</p><img src="cid:logo@example.test">',
            '--alternative--',
            '--related',
            'Content-Type: image/png',
            'Content-ID: <logo@example.test>',
            'Content-Transfer-Encoding: base64',
            '',
            base64_encode('fake-png'),
            '--related--',
        ]);

        $content = $this->messageContent($rawHeaders, $rawBody);

        $this->assertSame('Café terbuka', $content['body']);
        $this->assertSame('<p>Cafe terbuka</p><img src="cid:logo@example.test">', $content['html_body']);
        $this->assertCount(1, $content['attachments']);
        $this->assertSame('logo@example.test', $content['attachments'][0]['content_id']);
        $this->assertTrue($content['attachments'][0]['is_inline']);
        $this->assertTrue($content['attachments'][0]['is_image']);
    }

    public function test_read_page_renders_html_email_in_a_script_free_sandboxed_frame(): void
    {
        $this->fakeMessage = [
            'html_body' => '<style>.code{letter-spacing:2px}</style><p class="code">701075</p>'
                .'<img src="https://cdn.example.test/logo.png"><img src="cid:banner@example.test"><script>alert(1)</script>',
            'attachments' => [
                ['id' => '1', 'filename' => 'banner.png', 'content_type' => 'image/png', 'size' => 2048, 'is_image' => true, 'content_id' => 'banner@example.test', 'is_inline' => true],
                ['id' => '2', 'filename' => 'invoice.pdf', 'content_type' => 'application/pdf', 'size' => 4096, 'is_image' => false, 'content_id' => null, 'is_inline' => false],
            ],
        ];

        $response = $this->actingAsMailUser()->get(route('administration-finance.email.read', ['uid' => '11']));

        $response->assertOk();
        $response->assertSee('sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"', false);
        $response->assertDontSee('allow-scripts', false);
        $response->assertSee("default-src 'none'");
        $response->assertSee('<p class="code">701075</p>');
        $response->assertDontSee('<script>alert(1)</script>');
        $response->assertSee(route('administration-finance.email.attachment', ['uid' => '11', 'attachment' => '1', 'inline' => 1]));
        $response->assertSee('<img src="https://cdn.example.test/logo.png">');
        $response->assertSee('https: http:;');
        $response->assertDontSee('Tampilkan gambar');
        $response->assertSee('invoice.pdf');
        $response->assertDontSee('banner.png');
    }

    public function test_plain_text_email_keeps_the_text_layout(): void
    {
        $response = $this->actingAsMailUser()->get(route('administration-finance.email.read', ['uid' => '11']));

        $response->assertOk();
        $response->assertSee('Please use the code 701075');
        $response->assertDontSee('data-mail-html-frame title', false);
    }

    /**
     * @return array{body: string, html_body: ?string, attachments: list<array<string, mixed>>}
     */
    private function messageContent(string $rawHeaders, string $rawBody): array
    {
        $client = new SimpleImapClient('localhost', 993, 'ssl', 'department@example.test', 'secret');

        return (new ReflectionMethod($client, 'messageContent'))->invoke($client, $rawHeaders, $rawBody);
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
