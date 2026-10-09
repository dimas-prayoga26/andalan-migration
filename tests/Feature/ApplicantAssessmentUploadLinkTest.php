<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Services\ApplicantAssessmentUploadLinkService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApplicantAssessmentUploadLinkTest extends TestCase
{
    public function test_service_builds_brand_specific_assessment_upload_link(): void
    {
        $applicant = new Applicant([
            'brand_key' => 'niskala',
        ]);
        $applicant->id = '11111111-1111-8111-8111-111111111111';

        $service = app(ApplicantAssessmentUploadLinkService::class);
        $url = $service->urlFor($applicant);
        $assessmentFileUrl = $service->assessmentFileUrlFor($applicant);

        $this->assertSame(
            'https://technical-test.coffeeniskala.com/'.$applicant->id.'/upload-file/verify-applicant',
            $url,
        );
        $this->assertSame(
            'https://technical-test.coffeeniskala.com/'.$applicant->id.'/assessment-file',
            $assessmentFileUrl,
        );
    }

    public function test_assessment_upload_link_generation_is_wired_to_route_view_and_request_table(): void
    {
        $routes = File::get(base_path('routes/web.php'));
        $controller = File::get(app_path('Http/Controllers/TalentAcquisitionController.php'));
        $service = File::get(app_path('Services/ApplicantAssessmentUploadLinkService.php'));
        $view = File::get(resource_path('views/applicant_data/assessment.blade.php'));

        $this->assertStringContainsString('applicant.assessment.upload-request.store', $routes);
        $this->assertStringContainsString('storeApplicantAssessmentUploadRequest', $controller);
        $this->assertStringContainsString('ApplicantAssessmentUploadLinkService', $controller);
        $this->assertStringContainsString('ApplicantDocument::TYPE_ASSESSMENT_TEST', $controller);
        $this->assertStringContainsString('assessmentFileUrlFor', $service);
        $this->assertStringContainsString("'revoked_at' => now()", $service);
        $this->assertStringContainsString("'token_hash' => \$this->uniqueTokenHash()", $service);
        $this->assertStringContainsString('Generate Link Upload', $view);
        $this->assertStringContainsString('assessmentUploadUrl', $view);
        $this->assertStringContainsString('assessmentFileUrl', $view);
        $this->assertStringContainsString('Sudah Upload', $view);
    }
}
