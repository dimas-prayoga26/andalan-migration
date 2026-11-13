<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\HrMeeting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DashboardIncomingMeetingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-11-13 09:15:00', 'Asia/Jakarta'));
    }

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

    public function test_banner_shows_upcoming_meeting_countdown(): void
    {
        $staff = $this->createCoreStaff();
        $this->createScheduledMeetingFor($staff, ['meeting_time' => '09:30:00']);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Incoming Meeting: Weekly Meeting', false)
            ->assertSee('In 15 Minutes', false);
    }

    public function test_banner_stays_visible_after_meeting_time_passes_until_staff_joins(): void
    {
        $staff = $this->createCoreStaff();
        $this->createScheduledMeetingFor($staff, ['meeting_time' => '08:30:00']);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Incoming Meeting: Weekly Meeting', false)
            ->assertSee('Started 08:30 WIB', false);
    }

    public function test_banner_stays_visible_for_unjoined_meeting_from_previous_day(): void
    {
        $staff = $this->createCoreStaff();
        $this->createScheduledMeetingFor($staff, [
            'meeting_date' => '2026-11-12',
            'meeting_time' => '08:30:00',
        ]);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Incoming Meeting: Weekly Meeting', false)
            ->assertSee('Started 12 Nov 2026, 08:30 WIB', false);
    }

    public function test_banner_disappears_after_joining_from_dashboard_notification(): void
    {
        $staff = $this->createCoreStaff();
        [$meeting, $notification] = $this->createScheduledMeetingFor($staff, ['meeting_time' => '08:30:00']);

        $this->actingAs($staff)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('zoom-meeting.join', $meeting));

        $this->actingAs($staff)
            ->get(route('zoom-meeting.join', $meeting))
            ->assertRedirect('https://zoom.us/j/weekly');

        $this->assertNotNull($notification->refresh()->read_at);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Incoming Meeting: Weekly Meeting', false);
    }

    public function test_banner_disappears_after_joining_from_zoom_meeting_menu(): void
    {
        $staff = $this->createCoreStaff();
        [$meeting, $notification] = $this->createScheduledMeetingFor($staff, ['meeting_time' => '08:30:00']);

        $this->actingAs($staff)
            ->postJson(route('zoom-meeting.join', $meeting))
            ->assertOk()
            ->assertJsonPath('meeting_link', 'https://zoom.us/j/weekly');

        $this->assertNotNull($notification->refresh()->read_at);
        $this->assertDatabaseHas('hr_meeting_participants', [
            'hr_meeting_id' => $meeting->id,
            'employee_id' => $staff->employee->id,
            'attendance_status' => 'joined',
        ]);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Incoming Meeting: Weekly Meeting', false);
    }

    public function test_joining_a_meeting_does_not_mark_other_meeting_notifications_as_read(): void
    {
        $staff = $this->createCoreStaff();
        [$joinedMeeting] = $this->createScheduledMeetingFor($staff, ['meeting_time' => '08:30:00']);
        [, $otherNotification] = $this->createScheduledMeetingFor($staff, [
            'title' => 'Project Sync',
            'meeting_time' => '13:00:00',
        ]);

        $this->actingAs($staff)
            ->postJson(route('zoom-meeting.join', $joinedMeeting))
            ->assertOk();

        $this->assertNull($otherNotification->refresh()->read_at);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Incoming Meeting: Weekly Meeting', false)
            ->assertSee('Incoming Meeting: Project Sync', false);
    }

    public function test_banner_is_hidden_when_staff_already_joined_even_if_notification_is_unread(): void
    {
        $staff = $this->createCoreStaff();
        [$meeting] = $this->createScheduledMeetingFor($staff, ['meeting_time' => '08:30:00']);

        $meeting->participants()
            ->where('employee_id', $staff->employee->id)
            ->update(['attendance_status' => 'joined', 'joined_at' => now()]);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Incoming Meeting: Weekly Meeting', false);
    }

    public function test_banner_disappears_when_meeting_is_completed(): void
    {
        $staff = $this->createCoreStaff();
        [$meeting] = $this->createScheduledMeetingFor($staff, ['meeting_time' => '08:30:00']);

        $meeting->update(['status' => 'completed']);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Incoming Meeting: Weekly Meeting', false);
    }

    private function createCoreStaff(): User
    {
        $staff = User::factory()->activeEmployee(['view-dashboard', 'view-meeting'])->create();
        $staff->employee->forceFill(['is_core_staff' => true])->save();

        return $staff;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{0: HrMeeting, 1: AppNotification}
     */
    private function createScheduledMeetingFor(User $staff, array $attributes = []): array
    {
        $meeting = HrMeeting::query()->create([
            'title' => 'Weekly Meeting',
            'type' => 'weekly_meeting',
            'meeting_date' => '2026-11-13',
            'meeting_time' => '08:30:00',
            'meeting_link' => 'https://zoom.us/j/weekly',
            'status' => 'scheduled',
            ...$attributes,
        ]);

        $meeting->participants()->create([
            'employee_id' => $staff->employee->id,
            'participant_type' => 'all_staff',
            'attendance_status' => 'invited',
        ]);

        $notification = AppNotification::query()->create([
            'user_id' => $staff->id,
            'employee_id' => $staff->employee->id,
            'type' => 'hr_meeting_scheduled',
            'title' => 'Meeting Baru: '.$meeting->title,
            'url' => route('zoom-meeting.join', $meeting),
        ]);

        return [$meeting, $notification];
    }
}
