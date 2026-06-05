<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BusinessTripAttachmentModalPreviewTest extends TestCase
{
    public function test_business_trip_attachments_are_previewed_inside_modal(): void
    {
        $detailView = File::get(resource_path('views/attendance/business-trips/detail.blade.php'));

        $this->assertStringContainsString('id="businessTripAttachmentPreviewModal"', $detailView);
        $this->assertStringContainsString('id="businessTripAttachmentPreviewFrame"', $detailView);
        $this->assertStringContainsString('class="js-business-trip-attachment-preview"', $detailView);
        $this->assertStringContainsString('data-attachment-url="{{ $expenseItem[\'attachment_url\'] }}"', $detailView);
        $this->assertStringContainsString('data-attachment-url="{{ $reimbursementItem[\'receipt_url\'] }}"', $detailView);
        $this->assertStringContainsString("$('#businessTripAttachmentPreviewFrame').attr('src', attachmentUrl);", $detailView);
        $this->assertStringContainsString("$('#businessTripAttachmentPreviewFrame').attr('src', '');", $detailView);
        $this->assertStringNotContainsString('target="_blank"', $detailView);
    }
}
