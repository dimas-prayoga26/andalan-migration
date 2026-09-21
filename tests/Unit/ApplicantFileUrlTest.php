<?php

namespace Tests\Unit;

use App\Models\Applicant;
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
            'applicant_files.legacy_photo_base_url' => 'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/photo/',
        ]);

        $applicant = new Applicant(['photo' => 'profile photo.png']);

        $this->assertSame(
            'https://careers.rnb.co.id/files/photo/profile%20photo.png',
            $applicant->photoUrl(),
        );

        File::deleteDirectory($publicPath);
    }

    public function test_it_keeps_legacy_file_url_when_file_is_not_in_new_careers_folder(): void
    {
        config([
            'applicant_files.careers_public_paths' => [base_path('.codex-temp/missing-applicant-files')],
            'applicant_files.cv_base_url' => 'https://careers.rnb.co.id/files/cv/',
            'applicant_files.legacy_cv_base_url' => 'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/cv/',
        ]);

        $applicant = new Applicant(['cv' => 'old cv.pdf']);

        $this->assertSame(
            'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/cv/old%20cv.pdf',
            $applicant->cvDownloadUrl(),
        );
    }
}
