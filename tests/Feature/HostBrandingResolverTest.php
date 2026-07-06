<?php

namespace Tests\Feature;

use App\Support\Branding\HostBrandingResolver;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HostBrandingResolverTest extends TestCase
{
    public function test_branding_resolves_logo_by_request_host(): void
    {
        $resolver = new HostBrandingResolver;

        $this->assertSame('Dev', $resolver->resolve('dev.siap.rnb.co.id')['name']);
        $this->assertSame('images/images.png', $resolver->resolve('dev.siap.rnb.co.id')['logo_path']);
        $this->assertSame('RNB', $resolver->resolve('siap.rnb.co.id')['name']);
        $this->assertSame('images/Logo RNB.png', $resolver->resolve('siap.rnb.co.id')['logo_path']);
        $this->assertSame('images/Logo Trah.png', $resolver->resolve('siap.trah.co.id')['logo_path']);
        $this->assertSame('images/Logo KMA.png', $resolver->resolve('siap.karpetmerah.id')['logo_path']);
        $this->assertSame('images/Logo RNE.png', $resolver->resolve('siap.rne.co.id')['logo_path']);
        $this->assertSame('images/Logo Niskala.png', $resolver->resolve('siap.kopiniskala.com')['logo_path']);
        $this->assertSame('images/Logo TMS.png', $resolver->resolve('siap.tims.co.id')['logo_path']);
        $this->assertSame('images/images.png', $resolver->resolve('dyms-dev.my.id')['logo_path']);
        $this->assertSame('Dev', $resolver->resolve('127.0.0.1:8000')['name']);
        $this->assertSame('images/images.png', $resolver->resolve('127.0.0.1:8000')['logo_path']);
    }

    public function test_layout_and_login_use_host_branding_instead_of_logo_carousel(): void
    {
        $mainLayout = File::get(resource_path('views/layouts/main.blade.php'));
        $mainHead = File::get(resource_path('views/layouts/mainhead.blade.php'));
        $loginView = File::get(resource_path('views/auth/login.blade.php'));
        $authController = File::get(app_path('Http/Controllers/AuthController.php'));
        $appServiceProvider = File::get(app_path('Providers/AppServiceProvider.php'));

        $this->assertStringContainsString('$brandLogoUrl', $mainLayout);
        $this->assertStringContainsString('brand-logo-text', $mainLayout);
        $this->assertStringContainsString('brand-logo-desktop', $mainLayout);
        $this->assertStringContainsString('$brandLogoUrl', $mainHead);
        $this->assertStringContainsString("\$documentTitle = (\$brandName ?? 'Dev').' - Siap';", $mainHead);
        $this->assertStringContainsString('<title>{{ $documentTitle }}</title>', $mainHead);
        $this->assertStringContainsString('<meta property="og:title" content="{{ $documentTitle }}">', $mainHead);
        $this->assertStringContainsString('color: #000;', $mainHead);
        $this->assertStringContainsString('text-transform: uppercase;', $mainHead);
        $this->assertStringContainsString('$brandLogoUrl', $loginView);
        $this->assertStringContainsString("\$documentTitle = (\$brandName ?? 'Dev').' - Siap';", $loginView);
        $this->assertStringContainsString('<title>{{ $documentTitle }}</title>', $loginView);
        $this->assertStringContainsString('<meta property="og:title" content="{{ $documentTitle }}">', $loginView);
        $this->assertStringNotContainsString('<title>Test</title>', $loginView);
        $this->assertStringContainsString("public_path('assets/' . ltrim(\$path, '/'))", $loginView);
        $this->assertStringContainsString("asset('assets/css/style.css')", $loginView);
        $this->assertStringContainsString("\$assetVersion('css/style.css')", $loginView);
        $this->assertStringContainsString("asset('assets/icons/font-awesome/css/all.min.css')", $loginView);
        $this->assertStringContainsString('type="button" class="show-pass', $loginView);
        $this->assertStringContainsString('aria-label="Tampilkan password"', $loginView);
        $this->assertStringContainsString('showPassButton.setAttribute(\'aria-pressed\'', $loginView);
        $this->assertStringNotContainsString('<base href="{{ asset(\'assets\') }}/">', $loginView);
        $this->assertStringContainsString(HostBrandingResolver::class, $appServiceProvider);
        $this->assertStringNotContainsString('$logoPaths', $loginView);
        $this->assertStringNotContainsString('company-logo-carousel', $loginView);
        $this->assertStringNotContainsString('File::files', $authController);
    }

    public function test_login_document_and_open_graph_titles_follow_the_request_host(): void
    {
        $this->call('GET', 'http://siap.rnb.co.id/login', server: [
            'HTTP_HOST' => 'siap.rnb.co.id',
            'SERVER_NAME' => 'siap.rnb.co.id',
        ])
            ->assertOk()
            ->assertSee('<title>RNB - Siap</title>', false)
            ->assertSee('<meta property="og:title" content="RNB - Siap">', false);
    }
}
