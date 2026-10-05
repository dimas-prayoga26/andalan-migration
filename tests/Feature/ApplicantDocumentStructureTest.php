<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApplicantDocumentStructureTest extends TestCase
{
    public function test_applicant_document_tables_and_models_are_registered(): void
    {
        $applicantModel = File::get(app_path('Models/Applicant.php'));
        $documentModel = File::get(app_path('Models/ApplicantDocument.php'));
        $uploadRequestModel = File::get(app_path('Models/ApplicantUploadRequest.php'));
        $documentMigration = $this->migrationContents('create_applicant_documents_table');
        $dropLegacyMigration = $this->migrationContents('drop_cv_and_photo_from_applicants_table');
        $uploadRequestMigration = $this->migrationContents('create_applicant_upload_requests_table');
        $controller = File::get(app_path('Http/Controllers/TalentAcquisitionController.php'));

        $this->assertStringContainsString('function documents(): HasMany', $applicantModel);
        $this->assertStringContainsString('function uploadRequests(): HasMany', $applicantModel);
        $this->assertStringContainsString('ApplicantDocument::TYPE_CV', $applicantModel);
        $this->assertStringContainsString('ApplicantDocument::TYPE_PHOTO_PROFILE', $applicantModel);
        $this->assertStringContainsString('documentFilePath', $applicantModel);
        $this->assertStringContainsString("'documents:id,applicant_id,document_type,file_path'", $controller);
        $this->assertStringNotContainsString("'photo',", $controller);

        $this->assertStringContainsString("public const TYPE_CV = 'cv';", $documentModel);
        $this->assertStringContainsString("public const TYPE_PHOTO_PROFILE = 'photo_profile';", $documentModel);
        $this->assertStringContainsString("public const TYPE_ASSESSMENT_TEST = 'assessment_test';", $documentModel);
        $this->assertStringContainsString('function applicant(): BelongsTo', $documentModel);
        $this->assertStringContainsString('function uploadRequest(): BelongsTo', $documentModel);

        $this->assertStringContainsString('function applicant(): BelongsTo', $uploadRequestModel);
        $this->assertStringContainsString('function document(): HasOne', $uploadRequestModel);
        $this->assertStringContainsString('function isUsable(): bool', $uploadRequestModel);
        $this->assertStringContainsString("'token_hash'", $uploadRequestModel);

        $this->assertStringContainsString("Schema::create('applicant_upload_requests'", $uploadRequestMigration);
        $this->assertStringContainsString("foreignUuid('applicant_id')", $uploadRequestMigration);
        $this->assertStringContainsString("string('token_hash')->unique()", $uploadRequestMigration);
        $this->assertStringContainsString("timestamp('expires_at')->index()", $uploadRequestMigration);
        $this->assertStringContainsString("foreignUuid('created_by')->nullable()->constrained('users', 'id')->nullOnDelete()", $uploadRequestMigration);

        $this->assertStringContainsString("Schema::create('applicant_documents'", $documentMigration);
        $this->assertStringContainsString("foreignUuid('applicant_id')", $documentMigration);
        $this->assertStringContainsString("foreignUuid('applicant_upload_request_id')->nullable()->constrained('applicant_upload_requests', 'id')->nullOnDelete()", $documentMigration);
        $this->assertStringContainsString("string('document_type', 40)->index()", $documentMigration);
        $this->assertStringContainsString("unique(['applicant_id', 'document_type'])", $documentMigration);
        $this->assertStringContainsString("unique('applicant_upload_request_id')", $documentMigration);
        $this->assertStringContainsString('backfillApplicantDocuments', $documentMigration);
        $this->assertStringContainsString("'photo_profile' => \$applicant->photo", $documentMigration);

        $this->assertStringContainsString("dropColumn(['cv', 'photo'])", $dropLegacyMigration);
        $this->assertStringContainsString("text('cv')->nullable()", $dropLegacyMigration);
        $this->assertStringContainsString("text('photo')->nullable()", $dropLegacyMigration);
    }

    private function migrationContents(string $name): string
    {
        $matches = File::glob(database_path("migrations/*_{$name}.php"));

        $this->assertNotEmpty($matches, "Migration [{$name}] was not found.");

        return File::get($matches[0]);
    }
}
