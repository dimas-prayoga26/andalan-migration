<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantStatus;
use App\Models\ApplicantUploadRequest;
use App\Models\JobVacancy;
use App\Models\User;
use App\Services\ApplicantAssessmentUploadLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApplicantAssessmentUploadLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_upload_requests_are_stored_in_the_renamed_table(): void
    {
        $this->assertTrue(Schema::hasTable('applicant_assessment_test_upload_requests'));
        $this->assertFalse(Schema::hasTable('applicant_upload_requests'));
        $this->assertSame('applicant_assessment_test_upload_requests', (new ApplicantUploadRequest)->getTable());

        $applicant = Applicant::query()->create([
            'job_vacancy_id' => JobVacancy::query()->create(['name' => 'Web Developer', 'status' => JobVacancy::STATUS_ACTIVE])->id,
            'applicant_status_id' => ApplicantStatus::query()->where('value', ApplicantStatus::VALUE_HR_INTERVIEW)->value('id'),
            'full_name' => 'Assessment Candidate',
            'email' => 'assessment.candidate@example.test',
            'brand_key' => 'rnb',
        ]);
        $service = app(ApplicantAssessmentUploadLinkService::class);
        $uploadRequest = $service->create($applicant, null)['uploadRequest'];

        $this->assertDatabaseHas('applicant_assessment_test_upload_requests', ['id' => $uploadRequest->id]);
        $this->assertTrue($uploadRequest->is($service->latestUsableRequest($applicant)));

        $document = ApplicantDocument::query()->create([
            'applicant_id' => $applicant->id,
            'applicant_upload_request_id' => $uploadRequest->id,
            'document_type' => ApplicantDocument::TYPE_ASSESSMENT_TEST,
            'file_path' => 'uploaded-images/assessment.png',
        ]);

        $this->assertTrue($document->uploadRequest->is($uploadRequest));
    }

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
            'https://assessment-test.coffeeniskala.com/'.$applicant->id.'/upload-file/verify-applicant',
            $url,
        );
        $this->assertSame(
            'https://assessment-test.coffeeniskala.com/'.$applicant->id.'/assessment-file',
            $assessmentFileUrl,
        );
        $this->assertSame(
            'https://assessment-test.coffeeniskala.com/'.$applicant->id.'/assessment-file?download=1',
            $service->assessmentFileDownloadUrlFor($applicant),
        );
        $this->assertNull(config('assessment_upload'));
        $this->assertSame(7, config('assessment_test_upload.expires_in_days'));
    }

    public function test_assessment_document_detects_previewable_file_types(): void
    {
        $pdf = new ApplicantDocument(['file_path' => 'uploaded-images/abc.pdf', 'original_name' => 'Hasil.pdf', 'mime_type' => 'application/pdf']);
        $image = new ApplicantDocument(['file_path' => 'uploaded-images/abc.png', 'original_name' => 'Hasil.PNG', 'mime_type' => null]);
        $word = new ApplicantDocument([
            'file_path' => 'uploaded-images/abc.docx',
            'original_name' => null,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $this->assertTrue($pdf->isPdf());
        $this->assertFalse($pdf->isImage());
        $this->assertSame('Hasil.pdf', $pdf->displayName());

        $this->assertTrue($image->isImage());
        $this->assertFalse($image->isPdf());
        $this->assertSame('png', $image->fileExtension());

        $this->assertFalse($word->isImage());
        $this->assertFalse($word->isPdf());
        $this->assertSame('abc.docx', $word->displayName());
    }

    public function test_assessment_page_previews_and_offers_download_for_uploaded_pdf(): void
    {
        $applicant = $this->createApplicantWithAssessmentDocument('1001131098.pdf', 'application/pdf');

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->get(route('applicant.assessment', $applicant))
            ->assertOk()
            ->assertSee('Hasil assessment sudah diupload')
            ->assertSee('1001131098.pdf')
            ->assertSee('<iframe src="https://assessment-test.rnb.co.id/'.$applicant->id.'/assessment-file"', false)
            ->assertSee('Lihat File')
            ->assertSee('href="https://assessment-test.rnb.co.id/'.$applicant->id.'/assessment-file?download=1"', false)
            ->assertDontSee('<img src="https://assessment-test.rnb.co.id/'.$applicant->id.'/assessment-file"', false);
    }

    public function test_assessment_page_previews_uploaded_image(): void
    {
        $applicant = $this->createApplicantWithAssessmentDocument('hasil.jpg', 'image/jpeg');

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->get(route('applicant.assessment', $applicant))
            ->assertOk()
            ->assertSee('<img src="https://assessment-test.rnb.co.id/'.$applicant->id.'/assessment-file"', false)
            ->assertSee('Lihat File')
            ->assertSee('assessment-file?download=1', false);
    }

    public function test_assessment_page_only_offers_download_for_non_previewable_document(): void
    {
        $applicant = $this->createApplicantWithAssessmentDocument(
            'hasil.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        );

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->get(route('applicant.assessment', $applicant))
            ->assertOk()
            ->assertSee('hasil.docx')
            ->assertSee('bi-file-earmark-word', false)
            ->assertSee('tidak bisa dipreview di browser')
            ->assertSee('assessment-file?download=1', false)
            ->assertDontSee('Lihat File')
            ->assertDontSee('<iframe src="https://assessment-test.rnb.co.id/'.$applicant->id.'/assessment-file"', false);
    }

    public function test_assessment_upload_link_generation_is_wired_to_route_view_and_request_table(): void
    {
        $routes = File::get(base_path('routes/web.php'));
        $controller = File::get(app_path('Http/Controllers/TalentAcquisitionController.php'));
        $service = File::get(app_path('Services/ApplicantAssessmentUploadLinkService.php'));
        $view = File::get(resource_path('views/applicant_data/assessment.blade.php'));

        $this->assertStringContainsString('applicant.assessment.upload-request.store', $routes);
        $this->assertStringContainsString('applicant.assessment.upload-request.revoke', $routes);
        $this->assertStringContainsString('storeApplicantAssessmentUploadRequest', $controller);
        $this->assertStringContainsString('revokeApplicantAssessmentUploadRequest', $controller);
        $this->assertStringContainsString('ApplicantAssessmentUploadLinkService', $controller);
        $this->assertStringContainsString('ApplicantDocument::TYPE_ASSESSMENT_TEST', $controller);
        $this->assertStringContainsString('assessmentFileUrlFor', $service);
        $this->assertStringContainsString('revokeLatestUsableRequest', $service);
        $this->assertStringContainsString("'revoked_at' => now()", $service);
        $this->assertStringContainsString("'token_hash' => \$this->uniqueTokenHash()", $service);
        $this->assertStringContainsString('Generate Link Upload', $view);
        $this->assertStringContainsString('Revoke Link', $view);
        $this->assertStringContainsString("@method('PATCH')", $view);
        $this->assertStringContainsString('assessmentUploadUrl', $view);
        $this->assertStringContainsString('assessmentFileUrl', $view);
        $this->assertStringContainsString('Sudah Upload', $view);
    }

    private function createApplicantWithAssessmentDocument(string $originalName, string $mimeType): Applicant
    {
        $applicant = Applicant::query()->create([
            'job_vacancy_id' => JobVacancy::query()->create(['name' => 'Graphic Designer', 'status' => JobVacancy::STATUS_ACTIVE])->id,
            'applicant_status_id' => ApplicantStatus::query()->where('value', ApplicantStatus::VALUE_HR_INTERVIEW)->value('id'),
            'full_name' => 'Assessment Candidate',
            'email' => 'assessment.candidate@example.test',
            'brand_key' => 'rnb',
        ]);

        ApplicantDocument::query()->create([
            'applicant_id' => $applicant->id,
            'document_type' => ApplicantDocument::TYPE_ASSESSMENT_TEST,
            'file_path' => 'uploaded-images/'.$originalName,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
        ]);

        return $applicant;
    }
}
