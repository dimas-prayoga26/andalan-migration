<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LeaveRequestSickAttachmentOptionalTest extends TestCase
{
    public function test_controller_does_not_require_attachment_for_sick_leave(): void
    {
        $controller = File::get(app_path('Http/Controllers/StaffAttendance/AttendanceLeaveRequestController.php'));

        $this->assertStringContainsString("'attachment_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:1024']", $controller);
        $this->assertStringNotContainsString('Lampiran wajib diisi untuk Sick Leave.', $controller);
        $this->assertStringNotContainsString('$normalizedPermissionType === \'sakit\' && ! $hasNewAttachment', $controller);
        $this->assertStringNotContainsString('$normalizedPermissionType === \'sakit\' && ! $hasUploadFile', $controller);
    }

    public function test_leave_attachment_inputs_do_not_use_global_image_upload_handlers(): void
    {
        $view = File::get(resource_path('views/staff_attendance/leave-requests/index.blade.php'));

        $this->assertStringContainsString('id="leaveAttachmentFileInput" name="attachment_file"', $view);
        $this->assertStringContainsString('id="leaveUpdateAttachmentFileInput" name="attachment_file"', $view);
        $this->assertStringNotContainsString('class="imageUpload d-none" id="leaveAttachmentFileInput"', $view);
        $this->assertStringNotContainsString('class="imageUpload d-none" id="leaveUpdateAttachmentFileInput"', $view);
        $this->assertStringNotContainsString('bg-white shadow-sm upload-trigger" id="leaveAttachmentUploadTrigger"', $view);
        $this->assertStringNotContainsString('bg-white shadow-sm upload-trigger" id="leaveUpdateAttachmentUploadTrigger"', $view);
        $this->assertStringContainsString('$attachmentFileInput.prop(\'required\', false);', $view);
        $this->assertStringContainsString('$leaveUpdateAttachmentFileInput.prop(\'required\', false);', $view);
    }
}
