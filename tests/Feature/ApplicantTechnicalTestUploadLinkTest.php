<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantStatus;
use App\Models\ApplicantTechnicalTestUploadRequest;
use App\Models\ApplicantUploadRequest;
use App\Models\JobVacancy;
use App\Models\User;
use App\Services\ApplicantTechnicalTestUploadLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantTechnicalTestUploadLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_builds_brand_specific_technical_test_links(): void
    {
        $applicant = new Applicant(['brand_key' => 'tms']);
        $applicant->id = '11111111-1111-8111-8111-111111111111';
        $service = app(ApplicantTechnicalTestUploadLinkService::class);

        $this->assertSame(
            'https://technical-test.tims.co.id/'.$applicant->id.'/upload-file/verify-applicant',
            $service->urlFor($applicant),
        );
        $this->assertSame(
            'https://technical-test.tims.co.id/'.$applicant->id.'/technical-test-file',
            $service->technicalTestFileUrlFor($applicant),
        );

        $applicant->brand_key = 'unknown-brand';

        $this->assertSame(
            'https://technical-test.rnb.co.id/'.$applicant->id.'/upload-file/verify-applicant',
            $service->urlFor($applicant),
        );
    }

    public function test_generating_a_technical_test_link_revokes_only_previous_technical_test_links(): void
    {
        $applicant = $this->createApplicant(ApplicantStatus::VALUE_TECHNICAL_TEST);
        $assessmentUploadRequest = $applicant->uploadRequests()->create([
            'token_hash' => hash('sha256', 'assessment-token'),
            'expires_at' => now()->addDays(7),
        ]);
        $service = app(ApplicantTechnicalTestUploadLinkService::class);

        $firstLink = $service->create($applicant, null);
        $secondLink = $service->create($applicant, null);

        $this->assertNotNull($firstLink['uploadRequest']->refresh()->revoked_at);
        $this->assertNull($secondLink['uploadRequest']->refresh()->revoked_at);
        $this->assertTrue($secondLink['uploadRequest']->is($service->latestUsableRequest($applicant)));
        $this->assertNull($assessmentUploadRequest->refresh()->revoked_at);
        $this->assertSame(1, ApplicantUploadRequest::query()->count());
    }

    public function test_pic_can_generate_and_revoke_technical_test_link_during_technical_test_stage(): void
    {
        $applicant = $this->createApplicant(ApplicantStatus::VALUE_TECHNICAL_TEST);
        $pic = User::factory()->activeEmployee(['view-talent-acquisition'])->create();

        $this->actingAs($pic)
            ->from(route('applicant.assessment', $applicant))
            ->post(route('applicant.technical-test.upload-request.store', $applicant))
            ->assertRedirect(route('applicant.assessment', $applicant))
            ->assertSessionHas('technical_test_upload_url', 'https://technical-test.rnb.co.id/'.$applicant->id.'/upload-file/verify-applicant')
            ->assertSessionHas('assessment_active_tab', 'technical-test-tab');

        $uploadRequest = ApplicantTechnicalTestUploadRequest::query()->sole();
        $this->assertSame((string) $pic->id, (string) $uploadRequest->created_by);
        $this->assertTrue($uploadRequest->isUsable());

        $this->actingAs($pic)
            ->from(route('applicant.assessment', $applicant))
            ->patch(route('applicant.technical-test.upload-request.revoke', $applicant))
            ->assertRedirect(route('applicant.assessment', $applicant))
            ->assertSessionHas('status', 'Link upload technical test berhasil direvoke.');

        $this->assertNotNull($uploadRequest->refresh()->revoked_at);
    }

    public function test_pic_cannot_generate_technical_test_link_outside_technical_test_stage(): void
    {
        $applicant = $this->createApplicant(ApplicantStatus::VALUE_SUBMITTED);
        $pic = User::factory()->activeEmployee(['view-talent-acquisition'])->create();

        $this->actingAs($pic)
            ->post(route('applicant.technical-test.upload-request.store', $applicant))
            ->assertForbidden();

        $this->assertSame(0, ApplicantTechnicalTestUploadRequest::query()->count());
    }

    public function test_revoke_without_active_technical_test_link_reports_an_error(): void
    {
        $applicant = $this->createApplicant(ApplicantStatus::VALUE_SUBMITTED);
        $superAdministrator = User::factory()->superAdministrator()->create();

        $this->actingAs($superAdministrator)
            ->from(route('applicant.assessment', $applicant))
            ->patch(route('applicant.technical-test.upload-request.revoke', $applicant))
            ->assertRedirect(route('applicant.assessment', $applicant))
            ->assertSessionHas('error', 'Tidak ada link upload technical test aktif untuk direvoke.');
    }

    public function test_assessment_page_shows_technical_test_attachment_section(): void
    {
        $applicant = $this->createApplicant(ApplicantStatus::VALUE_TECHNICAL_TEST);
        $superAdministrator = User::factory()->superAdministrator()->create();
        app(ApplicantTechnicalTestUploadLinkService::class)->create($applicant, $superAdministrator);

        $this->actingAs($superAdministrator)
            ->get(route('applicant.assessment', $applicant))
            ->assertOk()
            ->assertSee('Lampiran Technical Test')
            ->assertSee('Upload hasil technical test')
            ->assertSee(route('applicant.technical-test.upload-request.store', $applicant), false)
            ->assertSee(route('applicant.technical-test.upload-request.revoke', $applicant), false)
            ->assertSee('https://technical-test.rnb.co.id/'.$applicant->id.'/upload-file/verify-applicant', false);

        ApplicantDocument::query()->create([
            'applicant_id' => $applicant->id,
            'document_type' => ApplicantDocument::TYPE_TECHNICAL_TEST,
            'file_path' => 'uploaded-files/technical-test.pdf',
            'original_name' => 'technical-test.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($superAdministrator)
            ->get(route('applicant.assessment', $applicant))
            ->assertOk()
            ->assertSee('Hasil technical test sudah diupload')
            ->assertSee('technical-test.pdf')
            ->assertSee('https://technical-test.rnb.co.id/'.$applicant->id.'/technical-test-file', false)
            ->assertSee('Upload hasil assessment');
    }

    private function createApplicant(int $statusValue): Applicant
    {
        $jobVacancy = JobVacancy::query()->create([
            'name' => 'Web Developer',
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);

        return Applicant::query()->create([
            'job_vacancy_id' => $jobVacancy->id,
            'applicant_status_id' => ApplicantStatus::query()->where('value', $statusValue)->value('id'),
            'full_name' => 'Technical Test Candidate',
            'email' => 'technical.candidate@example.test',
            'brand_key' => 'rnb',
        ]);
    }
}
