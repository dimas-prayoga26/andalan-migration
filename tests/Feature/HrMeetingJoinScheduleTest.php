<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\HrMeeting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrMeetingJoinScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-11-13 08:18:00', 'Asia/Jakarta'));
    }

    public function test_staff_cannot_join_from_zoom_meeting_menu_before_meeting_time(): void
    {
        $staff = $this->createCoreStaff();
        [$meeting, $notification] = $this->createScheduledMeetingFor($staff);

        $this->actingAs($staff)
            ->postJson(route('zoom-meeting.join', $meeting))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Meeting belum dimulai. Join meeting tersedia mulai 13 Nov 2026, 08:30 WIB.')
            ->assertJsonPath('join_opens_in_seconds', 720);

        $this->assertDatabaseMissing('hr_meeting_participants', [
            'hr_meeting_id' => $meeting->id,
            'attendance_status' => 'joined',
        ]);
        $this->assertNull($notification->refresh()->read_at);
    }

    public function test_staff_cannot_join_via_direct_link_before_meeting_time(): void
    {
        $staff = $this->createCoreStaff();
        [$meeting] = $this->createScheduledMeetingFor($staff);

        $this->actingAs($staff)
            ->get(route('zoom-meeting.join', $meeting))
            ->assertRedirect(route('zoom-meeting.index'))
            ->assertSessionHas('error', 'Meeting belum dimulai. Join meeting tersedia mulai 13 Nov 2026, 08:30 WIB.');

        $this->assertDatabaseMissing('hr_meeting_participants', [
            'hr_meeting_id' => $meeting->id,
            'attendance_status' => 'joined',
        ]);
    }

    public function test_opening_meeting_notification_before_meeting_time_keeps_it_unread(): void
    {
        $staff = $this->createCoreStaff();
        [$meeting, $notification] = $this->createScheduledMeetingFor($staff);

        $this->actingAs($staff)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('zoom-meeting.join', $meeting));

        $this->assertNull($notification->refresh()->read_at);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Incoming Meeting: Weekly Meeting', false);
    }

    public function test_staff_can_join_exactly_at_meeting_time(): void
    {
        $staff = $this->createCoreStaff();
        [$meeting, $notification] = $this->createScheduledMeetingFor($staff);

        $this->travelTo(CarbonImmutable::parse('2026-11-13 08:30:00', 'Asia/Jakarta'));

        $this->actingAs($staff)
            ->postJson(route('zoom-meeting.join', $meeting))
            ->assertOk()
            ->assertJsonPath('meeting_link', 'https://zoom.us/j/weekly');

        $this->assertDatabaseHas('hr_meeting_participants', [
            'hr_meeting_id' => $meeting->id,
            'employee_id' => $staff->employee->id,
            'attendance_status' => 'joined',
        ]);
        $this->assertNotNull($notification->refresh()->read_at);
    }

    public function test_dashboard_join_link_is_locked_before_meeting_time(): void
    {
        $staff = $this->createCoreStaff();
        $this->createScheduledMeetingFor($staff);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('In 12 Minutes', false)
            ->assertSee('aria-disabled="true" class="is-disabled"', false)
            ->assertSee('data-join-opens-in-seconds="720"', false)
            ->assertSee('(available at 08:30 WIB)', false)
            ->assertSee('data-header-notification-join-opens-in-seconds="720"', false);
    }

    public function test_dashboard_join_link_is_active_once_meeting_time_arrives(): void
    {
        $staff = $this->createCoreStaff();
        [, $notification] = $this->createScheduledMeetingFor($staff);

        $this->travelTo(CarbonImmutable::parse('2026-11-13 08:30:00', 'Asia/Jakarta'));

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('notifications.open', $notification).'" target="_blank" rel="noopener noreferrer" data-dashboard-incoming-meeting-link', false)
            ->assertDontSee('data-join-url="', false)
            ->assertDontSee('(available at 08:30 WIB)', false)
            ->assertSee('data-header-notification-join-opens-in-seconds="0"', false);
    }

    public function test_zoom_meeting_menu_locks_join_button_before_meeting_time(): void
    {
        $staff = $this->createCoreStaff();
        $this->createScheduledMeetingFor($staff);

        $this->actingAs($staff)
            ->get(route('zoom-meeting.index'))
            ->assertOk()
            ->assertSee('disabled data-join-opens-in-seconds="720"', false)
            ->assertSee('Available at 08:30 WIB', false)
            ->assertDontSee('>Join Now</button>', false);
    }

    public function test_zoom_meeting_menu_enables_join_button_once_meeting_time_arrives(): void
    {
        $staff = $this->createCoreStaff();
        $this->createScheduledMeetingFor($staff);

        $this->travelTo(CarbonImmutable::parse('2026-11-13 08:31:00', 'Asia/Jakarta'));

        $this->actingAs($staff)
            ->get(route('zoom-meeting.index'))
            ->assertOk()
            ->assertSee('>Join Now</button>', false)
            ->assertDontSee('Available at 08:30 WIB', false);
    }

    public function test_zoom_meeting_menu_shows_join_error_flash(): void
    {
        $staff = $this->createCoreStaff();

        $this->actingAs($staff)
            ->withSession(['error' => 'Meeting belum dimulai.'])
            ->get(route('zoom-meeting.index'))
            ->assertOk()
            ->assertSee('Meeting belum dimulai.', false);
    }

    private function createCoreStaff(): User
    {
        $staff = User::factory()->activeEmployee(['view-dashboard', 'view-meeting'])->create();
        $staff->employee->forceFill(['is_core_staff' => true])->save();

        return $staff;
    }

    /**
     * @return array{0: HrMeeting, 1: AppNotification}
     */
    private function createScheduledMeetingFor(User $staff): array
    {
        $meeting = HrMeeting::query()->create([
            'title' => 'Weekly Meeting',
            'type' => 'weekly_meeting',
            'meeting_date' => '2026-11-13',
            'meeting_time' => '08:30:00',
            'meeting_link' => 'https://zoom.us/j/weekly',
            'status' => 'scheduled',
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
