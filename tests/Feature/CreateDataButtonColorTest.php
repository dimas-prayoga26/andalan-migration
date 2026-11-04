<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreateDataButtonColorTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function createDataButtons(): array
    {
        return [
            'add employee' => ['authorization/index.blade.php', '<a href="{{ route(\'authorization.create\') }}" class="btn light btn-success btn-sm">'],
            'add event division' => ['authorization/assign-event-divisions.blade.php', 'class="btn light btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addEventDivisionModal"'],
            'create job' => ['applicant_data/job_vancancies.blade.php', 'class="btn light btn-success btn-sm d-inline-flex align-items-center justify-content-center gap-1 talent-create-job-btn"'],
            'create email account' => ['email-management/index.blade.php', 'class="btn light btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createMailAccountModal"'],
            'create takeover access' => ['email-management/index.blade.php', 'class="btn light btn-success btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#createTakeoverModal"'],
            'add overtime' => ['pic_attendance/overtime/index.blade.php', 'class="btn light btn-success btn-sm me-1" data-bs-toggle="modal" data-bs-target="#picAddOvertimeModal"'],
            'add project' => ['project_management/projects/index.blade.php', 'class="btn light btn-success btn-sm project-add-button"'],
            'add attendance rule' => ['settings/attendance-rules/index.blade.php', '<a href="{{ route(\'settings.attendance-rules.create\') }}" class="btn light btn-success btn-sm">'],
            'add company' => ['settings/companies/index.blade.php', '<a href="{{ route(\'settings.companies.create\') }}" class="btn light btn-success btn-sm">'],
            'add office location' => ['settings/office-locations/index.blade.php', '<a href="{{ route(\'settings.office-locations.create\') }}" class="btn light btn-success btn-sm">'],
            'new meeting' => ['meetings/admin/index.blade.php', '<a href="{{ route(\'hr-meetings.create\') }}" class="btn light btn-success btn-sm mb-1">'],
            'create dashboard task' => ['dashboard.blade.php', 'class="btn light btn-success m-3 mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#dashboardTaskFormModal"'],
            'business trip' => ['staff_attendance/business-trips/index.blade.php', '<a href="{{ route(\'attendance.business-trips.create\') }}" class="btn btn-success light btn-sm ms-2">'],
            'add task' => ['project_management/task_list/partials/task-list-items.blade.php', 'data-task-form-mode="create" class="btn btn-success light">Add Task</button>'],
        ];
    }

    #[DataProvider('createDataButtons')]
    public function test_create_data_buttons_use_the_light_green_style(string $view, string $expectedButtonMarkup): void
    {
        $this->assertStringContainsString($expectedButtonMarkup, File::get(resource_path('views/'.$view)));
    }
}
