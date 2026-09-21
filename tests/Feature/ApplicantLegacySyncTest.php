<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\JobVacancy;
use Tests\TestCase;

class ApplicantLegacySyncTest extends TestCase
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

    public function test_cv_download_url_points_to_legacy_cv_folder(): void
    {
        $this->assertSame(
            'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/cv/saktian%20cv.pdf',
            (new Applicant(['cv' => 'saktian cv.pdf']))->cvDownloadUrl()
        );

        $this->assertSame(
            'https://example.com/cv.pdf',
            (new Applicant(['cv' => 'https://example.com/cv.pdf']))->cvDownloadUrl()
        );

        $this->assertNull((new Applicant(['cv' => null]))->cvDownloadUrl());
    }

    public function test_photo_url_points_to_legacy_photo_folder(): void
    {
        $this->assertSame(
            'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/photo/saktian%20photo.jpg',
            (new Applicant(['photo' => 'saktian photo.jpg']))->photoUrl()
        );

        $this->assertSame(
            'https://example.com/photo.jpg',
            (new Applicant(['photo' => 'https://example.com/photo.jpg']))->photoUrl()
        );

        $this->assertNull((new Applicant(['photo' => null]))->photoUrl());
    }

    public function test_job_vacancy_status_maps_to_legacy_values(): void
    {
        $this->assertSame(1, JobVacancy::statusValueFor(JobVacancy::STATUS_ACTIVE));
        $this->assertSame(2, JobVacancy::statusValueFor(JobVacancy::STATUS_INACTIVE));
        $this->assertSame([
            JobVacancy::STATUS_ACTIVE => 'Active',
            JobVacancy::STATUS_INACTIVE => 'Non Active',
        ], JobVacancy::statusOptions());
    }
}
