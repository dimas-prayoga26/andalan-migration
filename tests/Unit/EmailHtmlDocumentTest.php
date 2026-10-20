<?php

namespace Tests\Unit;

use App\Support\Mail\EmailHtmlDocument;
use PHPUnit\Framework\TestCase;

class EmailHtmlDocumentTest extends TestCase
{
    private EmailHtmlDocument $emailHtmlDocument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailHtmlDocument = new EmailHtmlDocument;
    }

    public function test_document_loads_images_but_blocks_other_remote_resources(): void
    {
        $document = $this->emailHtmlDocument->render('<p>Halo</p><img src="https://cdn.example.test/logo.png">', []);

        $this->assertStringContainsString('http-equiv="Content-Security-Policy"', $document);
        $this->assertStringContainsString("default-src 'none'", $document);
        $this->assertStringContainsString("img-src data: 'self' https: http:;", $document);
        $this->assertStringContainsString("form-action 'none'", $document);
        $this->assertStringNotContainsString('script-src', $document);
        $this->assertStringContainsString('<base target="_blank">', $document);
        $this->assertStringContainsString('<body><p>Halo</p><img src="https://cdn.example.test/logo.png"></body>', $document);
    }

    public function test_active_content_is_removed_while_styles_and_layout_are_kept(): void
    {
        $html = '<html><head><meta http-equiv="refresh" content="0;url=https://evil.test"><link rel="stylesheet" href="https://evil.test/a.css">'
            .'<style>.btn{color:#fff}</style></head><body>'
            .'<script>alert(1)</script><iframe src="https://evil.test"></iframe><object data="x"></object>'
            .'<form action="https://evil.test"><input name="password"></form>'
            .'<a href="javascript:alert(1)" onclick="steal()">Klik</a><img src="x" onerror="alert(1)">'
            .'<table><tr><td class="btn">Verify</td></tr></table></body></html>';

        $document = $this->emailHtmlDocument->render($html, []);

        foreach (['<script', 'alert(1)</script>', '<iframe', '<object', '<form', 'http-equiv="refresh"', '<link', 'onclick=', 'onerror=', 'javascript:'] as $removedFragment) {
            $this->assertStringNotContainsString($removedFragment, $document);
        }

        $this->assertStringContainsString('<style>.btn{color:#fff}</style>', $document);
        $this->assertStringContainsString('<td class="btn">Verify</td>', $document);
        $this->assertStringContainsString('<input name="password">', $document);
        $this->assertStringContainsString('<a target="_blank" rel="noopener noreferrer" href="#">Klik</a>', $document);
    }

    public function test_inline_content_id_images_point_to_attachment_urls(): void
    {
        $html = '<img src="cid:logo@example.test"><div style="background:url(cid:banner@example.test)"></div><img src="cid:unknown@example.test">';

        $document = $this->emailHtmlDocument->render($html, [
            'logo@example.test' => 'https://siap.example.test/business-email/read/5/attachments/1?inline=1&folder=sent',
            'banner@example.test' => 'https://siap.example.test/business-email/read/5/attachments/2?inline=1',
        ]);

        $this->assertStringContainsString('<img src="https://siap.example.test/business-email/read/5/attachments/1?inline=1&amp;folder=sent">', $document);
        $this->assertStringContainsString('background:url(https://siap.example.test/business-email/read/5/attachments/2?inline=1)', $document);
        $this->assertStringContainsString('<img src="cid:unknown@example.test">', $document);
        $this->assertSame(['logo@example.test', 'banner@example.test', 'unknown@example.test'], $this->emailHtmlDocument->referencedContentIds($html));
    }
}
