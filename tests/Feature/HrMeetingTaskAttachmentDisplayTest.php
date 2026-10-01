<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HrMeetingTaskAttachmentDisplayTest extends TestCase
{
    public function test_meeting_task_cards_show_short_attachment_link_without_rendering_raw_url_text(): void
    {
        $taskCardsView = File::get(resource_path('views/meetings/admin/partials/task-cards.blade.php'));
        $taskScriptView = File::get(resource_path('views/meetings/admin/partials/task-create-modal-script.blade.php'));
        $staffIndexView = File::get(resource_path('views/meetings/staff/index.blade.php'));
        $staffDetailsView = File::get(resource_path('views/meetings/staff/details.blade.php'));
        $controller = File::get(app_path('Http/Controllers/HrMeetingController.php'));
        $routes = File::get(base_path('routes/web.php'));

        $this->assertStringContainsString('@if ($task[\'attachment_path\'] ?? false)', $taskCardsView);
        $this->assertStringContainsString('title="{{ $task[\'attachment_path\'] }}"', $taskCardsView);
        $this->assertStringContainsString('>Attachment', $taskCardsView);
        $this->assertStringNotContainsString('{{ $task[\'attachment_path\'] }}</a>', $taskCardsView);

        $this->assertStringContainsString('function renderTaskAttachmentLink(task)', $taskScriptView);
        $this->assertStringContainsString('title="\' + escapeAttribute(attachmentValue) + \'"', $taskScriptView);
        $this->assertStringContainsString('<i class="fa fa-paperclip me-1" aria-hidden="true"></i>Attachment', $taskScriptView);
        $this->assertStringContainsString('+ renderTaskAttachmentLink(task) +', $taskScriptView);

        $this->assertStringContainsString("'tasks.projectTask:id,attachment_path'", $controller);
        $this->assertStringContainsString('$meetingAttachmentUrl = trim((string) ($meeting->attachment_link ?? \'\'))', $controller);
        $this->assertStringContainsString("'task_attachment_url' => \$taskAttachmentUrl", $controller);
        $this->assertStringContainsString('$meeting[\'task_attachment_url\']', $staffIndexView);
        $this->assertStringContainsString("{{ \$meeting['task_count'] }} Task", $staffIndexView);
        $this->assertStringContainsString('<span class="mx-1 text-muted">|</span>', $staffIndexView);
        $this->assertStringContainsString('<i class="fa fa-paperclip me-1" aria-hidden="true"></i>Attachment', $staffIndexView);
        $this->assertStringNotContainsString('{{ $meeting[\'task_attachment_url\'] }}</a>', $staffIndexView);

        $this->assertStringContainsString("Route::put('/zoom-meeting/{hrMeeting}/tasks/{hrMeetingTask}'", $routes);
        $this->assertStringContainsString('updateStaffTask', $controller);
        $this->assertStringContainsString("'zoom-meeting.tasks.update'", $controller);
        $this->assertStringContainsString('(string) $projectTask->employee_id !== (string) $employee->id', $controller);
        $this->assertStringContainsString('$this->meetingTaskCards($hrMeeting, $employee, \'zoom-meeting.tasks.update\', true)', $controller);
        $this->assertStringContainsString('can_update', $controller);
        $this->assertStringContainsString('can_delete', $controller);

        $this->assertStringContainsString("'showTaskActions' => true", $staffDetailsView);
        $this->assertStringContainsString("'showTaskDeleteActions' => false", $staffDetailsView);
        $this->assertStringContainsString("'allowTaskAssigneeEdit' => false", $staffDetailsView);
        $this->assertStringContainsString("'useFullTaskEditTemplate' => true", $staffDetailsView);
        $this->assertStringContainsString('task-create-modal-script', $staffDetailsView);
        $this->assertStringContainsString('$useFullTaskEditTemplate = $useFullTaskEditTemplate ?? false', $taskCardsView);
        $this->assertStringContainsString('Judul Task', $taskCardsView);
        $this->assertStringContainsString('Date Range', $taskCardsView);
        $this->assertStringContainsString('id="meetingTaskEditDescription"', $taskCardsView);
        $this->assertStringContainsString('id="meetingTaskEditPriority"', $taskCardsView);
        $this->assertStringContainsString('id="meetingTaskEditAttachment"', $taskCardsView);
        $this->assertStringContainsString('id="meetingTaskEditBlockers"', $taskCardsView);
        $this->assertStringContainsString('id="meetingTaskEditProjectName"', $taskCardsView);
        $this->assertStringContainsString('task.can_update !== false', $taskScriptView);
        $this->assertStringContainsString('task.can_delete !== false', $taskScriptView);
        $this->assertStringContainsString("$('#meetingTaskEditAssigneeHidden').val(task.assignee_id || '')", $taskScriptView);
    }
}
