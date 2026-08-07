<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantStatus;
use App\Models\JobVacancy;
use App\Services\Applicants\LegacyApplicantSyncService;
use Illuminate\Support\Collection;
use ReflectionClass;
use Tests\TestCase;

class ApplicantLegacySyncTest extends TestCase
{
    public function test_legacy_pipe_values_are_combined_by_matching_index(): void
    {
        $rows = $this->combinedRows([
            'company_name' => 'Upwork.com||Fiverr.com',
            'role' => 'Pekerja Lepas Desainer Grafis||Pekerja Lepas Desainer Grafis',
            'company_location' => 'California, Amerika Serikat||Israel',
            'start_period' => '2022-03||2022-04',
            'end_period' => '2026-02||2025-10',
        ]);

        $this->assertSame([
            [
                'company_name' => 'Upwork.com',
                'role' => 'Pekerja Lepas Desainer Grafis',
                'company_location' => 'California, Amerika Serikat',
                'start_period' => '2022-03',
                'end_period' => '2026-02',
            ],
            [
                'company_name' => 'Fiverr.com',
                'role' => 'Pekerja Lepas Desainer Grafis',
                'company_location' => 'Israel',
                'start_period' => '2022-04',
                'end_period' => '2025-10',
            ],
        ], $rows->all());
    }

    public function test_legacy_html_entities_and_empty_dash_rows_are_normalized(): void
    {
        $rows = $this->combinedRows([
            'name' => 'Graphics Designer &amp; Illustrator||-',
            'value' => '15||-',
        ]);

        $this->assertSame([
            [
                'name' => 'Graphics Designer & Illustrator',
                'value' => '15',
            ],
        ], $rows->all());
    }

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

    public function test_legacy_applicant_status_is_limited_to_three_status_values(): void
    {
        $service = app(LegacyApplicantSyncService::class);

        $this->assertSame(ApplicantStatus::VALUE_SUBMITTED, $this->applicantStatusValueFor($service, 0));
        $this->assertSame(ApplicantStatus::VALUE_INTERVIEW, $this->applicantStatusValueFor($service, 1));
        $this->assertSame(ApplicantStatus::VALUE_DITERIMA, $this->applicantStatusValueFor($service, 2));
        $this->assertSame(ApplicantStatus::VALUE_SUBMITTED, $this->applicantStatusValueFor($service, 99));
    }

    public function test_job_vacancy_status_maps_to_legacy_values(): void
    {
        $this->assertSame(1, JobVacancy::legacyStatusValueFor(JobVacancy::STATUS_ACTIVE));
        $this->assertSame(2, JobVacancy::legacyStatusValueFor(JobVacancy::STATUS_INACTIVE));
        $this->assertSame([
            JobVacancy::STATUS_ACTIVE => 'Active',
            JobVacancy::STATUS_INACTIVE => 'Non Active',
        ], JobVacancy::statusOptions());
    }

    /**
     * @param  array<string, string>  $legacyColumns
     * @return Collection<int, array<string, string|null>>
     */
    private function combinedRows(array $legacyColumns): Collection
    {
        $reflection = new ReflectionClass(LegacyApplicantSyncService::class);
        $method = $reflection->getMethod('combinedRows');

        return $method->invoke(app(LegacyApplicantSyncService::class), $legacyColumns);
    }

    private function applicantStatusValueFor(LegacyApplicantSyncService $service, int $legacyStatus): int
    {
        $reflection = new ReflectionClass(LegacyApplicantSyncService::class);
        $method = $reflection->getMethod('applicantStatusValueFor');

        return $method->invoke($service, $legacyStatus);
    }
}
