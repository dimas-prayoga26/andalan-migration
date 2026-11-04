<?php

namespace Tests\Unit;

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApplicantFileUrlTest extends TestCase
{
    public function test_it_uses_new_careers_file_url_when_uploaded_file_exists(): void
    {
        $publicPath = base_path('.codex-temp/applicant-files');
        $photoDirectory = $publicPath.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'photo';

        File::ensureDirectoryExists($photoDirectory);
        File::put($photoDirectory.DIRECTORY_SEPARATOR.'profile photo.png', 'fake image');

        config([
            'applicant_files.careers_public_paths' => [$publicPath],
            'applicant_files.photo_base_url' => 'https://careers.rnb.co.id/files/photo/',
            'applicant_files.fallback_photo_base_url' => 'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/photo/',
        ]);

        $applicant = $this->applicantWithDocument(ApplicantDocument::TYPE_PHOTO_PROFILE, 'profile photo.png');

        $this->assertSame(
            'https://careers.rnb.co.id/files/photo/profile%20photo.png',
            $applicant->photoUrl(),
        );

        File::deleteDirectory($publicPath);
    }

    public function test_it_uses_archived_upload_url_when_file_is_not_in_configured_public_folder(): void
    {
        config([
            'applicant_files.careers_public_paths' => [base_path('.codex-temp/missing-applicant-files')],
            'applicant_files.cv_base_url' => 'https://careers.rnb.co.id/files/cv/',
            'applicant_files.fallback_cv_base_url' => 'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/cv/',
        ]);

        $applicant = $this->applicantWithDocument(ApplicantDocument::TYPE_CV, 'old cv.pdf');

        $this->assertSame(
            'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/cv/old%20cv.pdf',
            $applicant->cvDownloadUrl(),
        );
    }

    public function test_it_does_not_duplicate_upload_directory_when_database_value_contains_path(): void
    {
        config([
            'applicant_files.careers_public_paths' => [base_path('.codex-temp/missing-applicant-files')],
            'applicant_files.fallback_photo_base_url' => 'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/photo/',
        ]);

        $applicant = $this->applicantWithDocument(ApplicantDocument::TYPE_PHOTO_PROFILE, 'files/photo/profile photo.png');

        $this->assertSame(
            'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/photo/profile%20photo.png',
            $applicant->photoUrl(),
        );
    }

    public function test_it_uses_applicant_document_paths(): void
    {
        $applicant = new Applicant;
        $applicant->setRelation('documents', collect([
            new ApplicantDocument([
                'document_type' => ApplicantDocument::TYPE_CV,
                'file_path' => 'document cv.pdf',
            ]),
            new ApplicantDocument([
                'document_type' => ApplicantDocument::TYPE_PHOTO_PROFILE,
                'file_path' => 'document photo.jpg',
            ]),
        ]));

        $this->assertSame(
            'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/cv/document%20cv.pdf',
            $applicant->cvDownloadUrl(),
        );

        $this->assertSame(
            'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/photo/document%20photo.jpg',
            $applicant->photoUrl(),
        );
    }

    public function test_it_supports_legacy_photo_document_type(): void
    {
        $applicant = $this->applicantWithDocument(ApplicantDocument::TYPE_PHOTO, 'legacy photo.jpg');

        $this->assertSame(
            'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/photo/legacy%20photo.jpg',
            $applicant->photoUrl(),
        );
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
