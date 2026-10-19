<?php

namespace Tests\Feature;

use App\Models\HrMeeting;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HrMeetingAttachmentLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_meeting_attachments_table_is_dropped(): void
    {
        $this->assertFalse(Schema::hasTable('hr_meeting_attachments'));
        $this->assertTrue(Schema::hasColumn('hr_meetings', 'attachment_link'));
    }

    public function test_storing_a_meeting_keeps_attachment_link_on_the_meeting(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('hr-meetings.store'), [
                'title' => 'Weekly Meeting',
                'type' => 'weekly_meeting',
                'meeting_date' => '2026-10-19',
                'meeting_time' => '11:00',
                'status' => 'completed',
                'attachment_link' => 'https://docs.google.com/presentation/d/weekly',
                'participant_group' => 'custom',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('hr_meetings', [
            'title' => 'Weekly Meeting',
            'attachment_link' => 'https://docs.google.com/presentation/d/weekly',
        ]);
    }

    public function test_updating_a_meeting_changes_its_attachment_link(): void
    {
        $meeting = $this->createMeeting();

        $this->actingAs($this->createAdmin())
            ->put(route('hr-meetings.update.save', $meeting), [
                'title' => $meeting->title,
                'type' => $meeting->type,
                'meeting_date' => '2026-10-19',
                'meeting_time' => '11:00',
                'status' => 'completed',
                'attachment_link' => 'https://docs.google.com/presentation/d/updated',
                'participant_group' => 'custom',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('hr-meetings.details', $meeting));

        $this->assertSame('https://docs.google.com/presentation/d/updated', $meeting->refresh()->attachment_link);
    }

    public function test_destroying_a_meeting_with_an_attachment_link_succeeds(): void
    {
        $meeting = $this->createMeeting();

        $this->actingAs($this->createAdmin())
            ->delete(route('hr-meetings.destroy', $meeting))
            ->assertRedirect(route('hr-meetings.index'));

        $this->assertDatabaseMissing('hr_meetings', ['id' => $meeting->id]);
    }

    public function test_staff_meeting_card_uses_meeting_attachment_link_instead_of_task_attachment(): void
    {
        $staff = $this->createCoreStaff();
        $meeting = $this->createMeeting();
        $this->attachTaskWithAttachment($meeting, $staff, 'https://drive.google.com/task-file');

        $this->actingAs($staff)
            ->get(route('zoom-meeting.index', ['completed_month' => 10, 'completed_year' => 2026]))
            ->assertOk()
            ->assertSee('href="https://docs.google.com/presentation/d/weekly"', false)
            ->assertDontSee('https://drive.google.com/task-file', false);
    }

    public function test_staff_meeting_card_hides_attachment_when_meeting_has_no_attachment_link(): void
    {
        $staff = $this->createCoreStaff();
        $meeting = $this->createMeeting(['attachment_link' => null]);
        $this->attachTaskWithAttachment($meeting, $staff, 'https://drive.google.com/task-file');

        $this->actingAs($staff)
            ->get(route('zoom-meeting.index', ['completed_month' => 10, 'completed_year' => 2026]))
            ->assertOk()
            ->assertSee('1 Task', false)
            ->assertDontSee('aria-label="Open meeting attachment"', false)
            ->assertDontSee('https://drive.google.com/task-file', false);
    }

    private function createCoreStaff(): User
    {
        $staff = User::factory()->activeEmployee(['view-meeting'])->create();
        $staff->employee->forceFill(['is_core_staff' => true])->save();

        return $staff;
    }

    private function attachTaskWithAttachment(HrMeeting $meeting, User $staff, string $attachmentPath): void
    {
        $meeting->participants()->create([
            'employee_id' => $staff->employee->id,
            'participant_type' => 'employee',
        ]);

        $projectTask = ProjectTask::query()->create([
            'employee_id' => $staff->employee->id,
            'title' => 'Prepare weekly report',
            'attachment_path' => $attachmentPath,
        ]);

        $meeting->tasks()->create([
            'category' => 'administration',
            'project_task_id' => $projectTask->id,
        ]);
    }

    private function createAdmin(): User
    {
        return User::factory()->activeEmployee(['view-admin-attendance'])->create();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createMeeting(array $attributes = []): HrMeeting
    {
        return HrMeeting::query()->create([
            'title' => 'Weekly Meeting',
            'type' => 'weekly_meeting',
            'meeting_date' => '2026-10-19',
            'meeting_time' => '11:00',
            'status' => 'completed',
            'attachment_link' => 'https://docs.google.com/presentation/d/weekly',
            ...$attributes,
        ]);
    }
}
