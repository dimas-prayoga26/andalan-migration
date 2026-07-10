<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AuthRememberMeDurationTest extends TestCase
{
    public function test_remember_me_cookie_duration_is_limited_to_seven_days(): void
    {
        $authConfig = File::get(config_path('auth.php'));
        $appServiceProvider = File::get(app_path('Providers/AppServiceProvider.php'));
        $loginView = File::get(resource_path('views/auth/login.blade.php'));

        $this->assertStringContainsString("'remember_me_lifetime' => (int) env('AUTH_REMEMBER_ME_LIFETIME', 10080)", $authConfig);
        $this->assertStringContainsString('use Illuminate\Auth\SessionGuard;', $appServiceProvider);
        $this->assertStringContainsString("Auth::guard('web')", $appServiceProvider);
        $this->assertStringContainsString("setRememberDuration((int) config('auth.remember_me_lifetime', 10080))", $appServiceProvider);
        $this->assertStringContainsString('Remember me', $loginView);
        $this->assertStringNotContainsString('Remember my preference', $loginView);
    }
}
