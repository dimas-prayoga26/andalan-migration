<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AssessmentTestProjectContractTest extends TestCase
{
    public function test_assessment_test_contract_matches_upload_project(): void
    {
        $uploadProjectPath = $this->uploadProjectPath();

        if (! File::isDirectory($uploadProjectPath)) {
            $this->markTestSkipped('Hasil-Assessment-Test project is not available beside andalan-migration.');
        }

        $service = File::get(app_path('Services/ApplicantAssessmentUploadLinkService.php'));
        $documentModel = File::get(app_path('Models/ApplicantDocument.php'));
        $assessmentConfig = File::get(config_path('assessment_upload.php'));
        $uploadRoutes = File::get($uploadProjectPath.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php');
        $uploadAccess = File::get($uploadProjectPath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Support'.DIRECTORY_SEPARATOR.'AssessmentUploadAccess.php');
        $assessmentFileController = File::get($uploadProjectPath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'AssessmentFileController.php');

        $this->assertStringContainsString("public const TYPE_ASSESSMENT_TEST = 'assessment_test';", $documentModel);
        $this->assertStringContainsString('/upload-file/verify-applicant', $service);
        $this->assertStringContainsString('/assessment-file', $service);
        $this->assertStringContainsString("'trah' => 'https://technical-test.trah.co.id'", $assessmentConfig);

        $this->assertStringContainsString('/{applicant}/upload-file/verify-applicant', $uploadRoutes);
        $this->assertStringContainsString('/{applicant}/assessment-file', $uploadRoutes);
        $this->assertStringContainsString('assessment-files.show', $uploadRoutes);
        $this->assertStringContainsString("config('assessment_upload.document_type', 'assessment_test')", $uploadAccess);
        $this->assertStringContainsString('brandKeyForHost', $uploadAccess);
        $this->assertStringContainsString('brandMatchesApplicant', $uploadAccess);
        $this->assertStringNotContainsString('brand_connections', $uploadAccess);
        $this->assertStringContainsString('possiblePublicFilePaths', $assessmentFileController);
    }

    private function uploadProjectPath(): string
    {
        return base_path('..'.DIRECTORY_SEPARATOR.'Hasil-Assessment-Test');
    }
}
