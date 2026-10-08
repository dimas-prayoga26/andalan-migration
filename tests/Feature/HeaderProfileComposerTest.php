<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HeaderProfileComposerTest extends TestCase
{
    public function test_header_uses_employee_picture_and_primary_position(): void
    {
        $header = File::get(resource_path('views/layouts/header.blade.php'));
        $composer = File::get(app_path('View/Composers/HeaderProfileComposer.php'));
        $provider = File::get(app_path('Providers/AppServiceProvider.php'));
        $routes = File::get(base_path('routes/web.php'));

        $this->assertStringContainsString("View::composer('layouts.header', HeaderProfileComposer::class);", $provider);
        $this->assertStringContainsString('employee.profile:id,employee_id,name,profile_picture_path', $composer);
        $this->assertStringContainsString('employee.deployment.position:id,name', $composer);
        $this->assertStringContainsString('visibleUnreadNotifications', $composer);
        $this->assertStringContainsString("->whereNull('read_at')", $composer);
        $this->assertStringContainsString('scheduledMeetingNotificationIds', $composer);
        $this->assertStringContainsString("->where('status', 'scheduled')", $composer);
        $this->assertStringContainsString('$scheduledMeetingIds->contains($meetingId)', $composer);
        $this->assertStringContainsString("asset('assets/default_user.jpg')", $composer);
        $this->assertStringContainsString('use Illuminate\Support\Facades\Storage;', $composer);
        $this->assertStringContainsString("return asset('storage/'.\$storagePath);", $composer);
        $this->assertStringNotContainsString('getRoleNames()', $composer);
        $this->assertStringContainsString("Route::get('/profile', [ProfileController::class, 'index'])->name('profile');", $routes);
        $this->assertStringContainsString("\Illuminate\Support\Facades\Route::has('profile')", $header);
        $this->assertStringContainsString("? route('profile')", $header);
        $this->assertStringContainsString(": url('/profile')", $header);
        $this->assertStringContainsString('href="{{ $headerProfileUrl }}"', $header);
        $this->assertStringNotContainsString('{{ $loop->iteration }}.', $header);
        $this->assertStringContainsString('data-header-notification-item', $header);
        $this->assertStringContainsString('data-header-notification-meeting-id="{{ $notification->meeting_id ?? \'\' }}"', $header);
        $this->assertStringContainsString('data-dashboard-incoming-meeting-id', $header);
        $this->assertStringContainsString('data-dashboard-incoming-meeting-link', $header);
        $this->assertStringContainsString('header-notification-count-badge', $header);
        $this->assertStringContainsString('background: #dc3545 !important;', $header);
        $this->assertStringContainsString('data-header-notification-count', $header);
        $this->assertStringContainsString('data-count-value="{{ (int) ($headerUnreadNotificationsCount ?? 0) }}"', $header);
        $this->assertStringContainsString("notificationCount.textContent = nextCount > 99 ? '99+' : String(nextCount);", $header);
        $this->assertStringNotContainsString('data-header-notification-pulse', $header);
        $this->assertStringContainsString('<span class="ms-2">Edit Profile</span>', $header);
        $this->assertStringNotContainsString('<span class="ms-2">Profile</span>', $header);
        $this->assertStringNotContainsString('<span class="ms-2">Message </span>', $header);
        $this->assertStringNotContainsString('<span class="ms-2">Notification </span>', $header);
        $this->assertStringNotContainsString('<span class="ms-2">Settings </span>', $header);
        $this->assertStringNotContainsString('DB::table', $header);
        $this->assertSame(2, substr_count($header, '{{ $headerUserAvatarUrl }}'));
        $this->assertSame(2, substr_count($header, '{{ $headerUserPositionLabel }}'));
    }
}
