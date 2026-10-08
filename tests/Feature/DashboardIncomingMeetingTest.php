<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DashboardIncomingMeetingTest extends TestCase
{
    public function test_dashboard_incoming_meeting_banner_uses_scheduled_meeting_for_authenticated_employee(): void
    {
        $controller = File::get(app_path('Http/Controllers/DashboardController.php'));
        $dashboard = File::get(resource_path('views/dashboard.blade.php'));

        $this->assertStringContainsString('dashboardIncomingMeetingData', $controller);
        $this->assertStringContainsString('dashboardMeetingNotifications', $controller);
        $this->assertStringContainsString("->where('type', 'hr_meeting_scheduled')", $controller);
        $this->assertStringContainsString("->whereNull('read_at')", $controller);
        $this->assertStringContainsString("->whereIn('id', \$meetingNotifications->keys()->all())", $controller);
        $this->assertStringContainsString("->where('status', 'scheduled')", $controller);
        $this->assertStringContainsString("->whereHas('participants'", $controller);
        $this->assertStringContainsString("->where('participant_type', 'all_staff')", $controller);
        $this->assertStringContainsString('Position::KEY_SUPERVISOR', $controller);
        $this->assertStringContainsString("->where('participant_type', 'bod')", $controller);
        $this->assertStringContainsString('incomingMeetingTimeLabel', $controller);
        $this->assertStringContainsString("'join_url' => route('notifications.open', \$notification)", $controller);

        $this->assertStringContainsString('dashboard-incoming-meeting', $dashboard);
        $this->assertStringContainsString('data-dashboard-incoming-meeting-id="{{ $dashboardIncomingMeeting[\'id\'] }}"', $dashboard);
        $this->assertStringContainsString('data-dashboard-incoming-meeting-link', $dashboard);
        $this->assertStringContainsString('Incoming Meeting: {{ $dashboardIncomingMeeting[\'title\'] }}', $dashboard);
        $this->assertStringContainsString('Time to sync up and get aligned. The waiting room is open!', $dashboard);
        $this->assertStringContainsString('Join Zoom Meeting', $dashboard);
    }
}
