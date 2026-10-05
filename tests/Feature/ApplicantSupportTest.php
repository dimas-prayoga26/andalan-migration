<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\JobVacancy;
use Tests\TestCase;

class ApplicantSupportTest extends TestCase
{
    public function test_portfolio_links_extract_multiple_urls(): void
    {
        $applicant = new Applicant([
            'portfolio_web_address' => 'https://www.behance.net/saktianrajasa dan https://www.instagram.com/wizardesign.id/',
        ]);

        $this->assertSame([
            'https://www.behance.net/saktianrajasa',
            'https://www.instagram.com/wizardesign.id/',
        ], $applicant->portfolioLinks());
    }

    public function test_whatsapp_url_normalizes_indonesian_phone_number(): void
    {
        $this->assertSame('https://wa.me/6285714420450', (new Applicant(['phone' => '0857-1442-0450']))->whatsAppUrl());
        $this->assertSame('https://wa.me/6285714420450', (new Applicant(['phone' => '+62 857 1442 0450']))->whatsAppUrl());
        $this->assertSame('https://wa.me/6285714420450', (new Applicant(['phone' => '85714420450']))->whatsAppUrl());
        $this->assertNull((new Applicant(['phone' => null]))->whatsAppUrl());
    }

    public function test_cv_download_url_points_to_archived_upload_folder_when_local_file_is_missing(): void
    {
        $this->assertSame(
            'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/cv/saktian%20cv.pdf',
            $this->applicantWithDocument(ApplicantDocument::TYPE_CV, 'saktian cv.pdf')->cvDownloadUrl()
        );

        $this->assertSame(
            'https://example.com/cv.pdf',
            $this->applicantWithDocument(ApplicantDocument::TYPE_CV, 'https://example.com/cv.pdf')->cvDownloadUrl()
        );

        $this->assertNull($this->applicantWithDocument(ApplicantDocument::TYPE_CV, null)->cvDownloadUrl());
    }

    public function test_photo_url_points_to_archived_upload_folder_when_local_file_is_missing(): void
    {
        $this->assertSame(
            'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/photo/saktian%20photo.jpg',
            $this->applicantWithDocument(ApplicantDocument::TYPE_PHOTO_PROFILE, 'saktian photo.jpg')->photoUrl()
        );

        $this->assertSame(
            'https://example.com/photo.jpg',
            $this->applicantWithDocument(ApplicantDocument::TYPE_PHOTO_PROFILE, 'https://example.com/photo.jpg')->photoUrl()
        );

        $this->assertNull($this->applicantWithDocument(ApplicantDocument::TYPE_PHOTO_PROFILE, null)->photoUrl());
    }

    public function test_job_vacancy_status_uses_current_values(): void
    {
        $this->assertSame(1, JobVacancy::statusValueFor(JobVacancy::STATUS_ACTIVE));
        $this->assertSame(2, JobVacancy::statusValueFor(JobVacancy::STATUS_INACTIVE));
        $this->assertSame([
            JobVacancy::STATUS_ACTIVE => 'Active',
            JobVacancy::STATUS_INACTIVE => 'Non Active',
        ], JobVacancy::statusOptions());
    }

    private function applicantWithDocument(string $documentType, ?string $filePath): Applicant
    {
        $applicant = new Applicant;

        $documents = $filePath === null
            ? collect()
            : collect([
                new ApplicantDocument([
                    'document_type' => $documentType,
                    'file_path' => $filePath,
                ]),
            ]);

        $applicant->setRelation('documents', $documents);

        return $applicant;
    }
}
