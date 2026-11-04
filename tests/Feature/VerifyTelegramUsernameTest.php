<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VerifyTelegramUsernameTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_verification_endpoint_is_removed(): void
    {
        $user = User::factory()->activeEmployee(['view-attendance'])->create();

        $this->assertFalse(Route::has('attendance.verify-telegram-username'));

        $this->actingAs($user)
            ->postJson('/attendance/verify-telegram-username')
            ->assertClientError();
    }

    public function test_attendance_modals_no_longer_call_telegram_verification(): void
    {
        $viewPaths = [
            resource_path('views/dashboard.blade.php'),
            resource_path('views/staff_attendance/components/attendance-cards.blade.php'),
        ];

        foreach ($viewPaths as $viewPath) {
            $viewContents = File::get($viewPath);

            $this->assertStringNotContainsString('verifyTelegramUsername', $viewContents, $viewPath);
            $this->assertStringNotContainsString('hasVerifiedTelegram', $viewContents, $viewPath);
            $this->assertStringContainsString('if (!context.hasVerifiedOnsite) {', $viewContents, $viewPath);
        }
    }
}
