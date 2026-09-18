<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_record_attendance_activity_event(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->postJson(route('user-activity-log.store'), [
                'event' => UserActivityLog::ClockInClicked,
                'metadata' => [
                    'source' => 'dashboard',
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $activityLog = UserActivityLog::query()->firstOrFail();

        $this->assertSame($user->id, $activityLog->user_id);
        $this->assertSame(UserActivityLog::ClockInClicked, $activityLog->event);
        $this->assertSame('dashboard', $activityLog->metadata['source']);
    }

    public function test_activity_event_must_be_known(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->postJson(route('user-activity-log.store'), [
                'event' => 'unknown_event',
            ])
            ->assertUnprocessable();
    }
}
