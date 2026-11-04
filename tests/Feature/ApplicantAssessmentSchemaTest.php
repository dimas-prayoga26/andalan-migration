<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApplicantAssessmentSchemaTest extends TestCase
{
    public function test_applicant_assessment_tables_and_models_are_registered(): void
    {
        $assessmentMigration = File::get($this->migrationPath('create_applicant_assessments_table'));
        $scoreMigration = File::get($this->migrationPath('create_applicant_assessment_scores_table'));
        $finalAssessmentMigration = File::get($this->migrationPath('create_applicant_final_assessments_table'));
        $applicantAssessment = File::get(app_path('Models/ApplicantAssessment.php'));
        $applicantAssessmentScore = File::get(app_path('Models/ApplicantAssessmentScore.php'));
        $applicantFinalAssessment = File::get(app_path('Models/ApplicantFinalAssessment.php'));
        $applicant = File::get(app_path('Models/Applicant.php'));

        $this->assertStringContainsString("Schema::create('applicant_assessments'", $assessmentMigration);
        $this->assertStringContainsString("\$table->uuid('id')->primary()", $assessmentMigration);
        $this->assertStringContainsString("foreignUuid('applicant_id')->constrained('applicants', 'id')->cascadeOnDelete()", $assessmentMigration);
        $this->assertStringContainsString("string('section', 40)->index()", $assessmentMigration);
        $this->assertStringContainsString("decimal('total_score', 5, 2)->default(0)", $assessmentMigration);
        $this->assertStringContainsString("string('status', 40)->default('draft')->index()", $assessmentMigration);
        $this->assertStringContainsString("foreignUuid('assessed_by')->nullable()->constrained('users', 'id')->nullOnDelete()", $assessmentMigration);
        $this->assertStringContainsString("unique(['applicant_id', 'section'])", $assessmentMigration);

        $this->assertStringContainsString("Schema::create('applicant_assessment_scores'", $scoreMigration);
        $this->assertStringContainsString("foreignUuid('applicant_assessment_id')->constrained('applicant_assessments', 'id')->cascadeOnDelete()", $scoreMigration);
        $this->assertStringContainsString("string('criterion_key', 100)", $scoreMigration);
        $this->assertStringContainsString("string('criterion_label')", $scoreMigration);
        $this->assertStringContainsString("string('parent_key', 100)->nullable()->index()", $scoreMigration);
        $this->assertStringContainsString("string('source_type', 80)->index()", $scoreMigration);
        $this->assertStringContainsString("foreignUuid('source_id')->nullable()->constrained('job_vacancy_technical_criteria', 'id')->nullOnDelete()", $scoreMigration);
        $this->assertStringContainsString("decimal('weight', 5, 2)", $scoreMigration);
        $this->assertStringContainsString("unsignedTinyInteger('rating')->nullable()", $scoreMigration);
        $this->assertStringContainsString("decimal('weighted_score', 6, 2)->default(0)", $scoreMigration);

        $this->assertStringContainsString("Schema::create('applicant_final_assessments'", $finalAssessmentMigration);
        $this->assertStringContainsString("\$table->uuid('id')->primary()", $finalAssessmentMigration);
        $this->assertStringContainsString("foreignUuid('applicant_id')->constrained('applicants', 'id')->cascadeOnDelete()", $finalAssessmentMigration);
        $this->assertStringContainsString("text('skill_note')->nullable()", $finalAssessmentMigration);
        $this->assertStringContainsString("text('communication_note')->nullable()", $finalAssessmentMigration);
        $this->assertStringContainsString("text('experience_note')->nullable()", $finalAssessmentMigration);
        $this->assertStringContainsString("text('tools_note')->nullable()", $finalAssessmentMigration);
        $this->assertStringContainsString("text('final_note')->nullable()", $finalAssessmentMigration);
        $this->assertStringContainsString("string('final_decision', 40)->nullable()->index()", $finalAssessmentMigration);
        $this->assertStringContainsString("foreignUuid('assessed_by')->nullable()->constrained('users', 'id')->nullOnDelete()", $finalAssessmentMigration);
        $this->assertStringContainsString("unique('applicant_id')", $finalAssessmentMigration);

        $this->assertStringContainsString("public const SECTION_HR_INTERVIEW = 'hr_interview';", $applicantAssessment);
        $this->assertStringContainsString("public const SECTION_TECHNICAL_TEST = 'technical_test';", $applicantAssessment);
        $this->assertStringContainsString("public const SECTION_INTERVIEW_USER = 'interview_user';", $applicantAssessment);
        $this->assertStringContainsString('public function scores(): HasMany', $applicantAssessment);
        $this->assertStringContainsString('public function assessments(): HasMany', $applicant);
        $this->assertStringContainsString('public function finalAssessment(): HasOne', $applicant);

        $this->assertStringContainsString("public const SOURCE_FIXED_HR = 'fixed_hr';", $applicantAssessmentScore);
        $this->assertStringContainsString("public const SOURCE_FIXED_USER = 'fixed_user';", $applicantAssessmentScore);
        $this->assertStringContainsString("public const SOURCE_JOB_VACANCY_TECHNICAL_CRITERION = 'job_vacancy_technical_criterion';", $applicantAssessmentScore);
        $this->assertStringContainsString("public const SOURCE_JOB_VACANCY_SKILL_CRITERION = 'job_vacancy_skill_criterion';", $applicantAssessmentScore);
        $this->assertStringContainsString('public function technicalCriterion(): BelongsTo', $applicantAssessmentScore);
        $this->assertStringContainsString('public function skillCriterion(): BelongsTo', $applicantAssessmentScore);

        $this->assertStringContainsString("public const DECISION_HIRED = 'hired';", $applicantFinalAssessment);
        $this->assertStringContainsString("public const DECISION_KEEP_IN_VIEW = 'keep_in_view';", $applicantFinalAssessment);
        $this->assertStringContainsString("public const DECISION_REJECTED = 'rejected';", $applicantFinalAssessment);
        $this->assertStringContainsString('public function applicant(): BelongsTo', $applicantFinalAssessment);
        $this->assertStringContainsString('public function assessor(): BelongsTo', $applicantFinalAssessment);
    }

    private function migrationPath(string $migrationName): string
    {
        $paths = glob(database_path("migrations/*_{$migrationName}.php"));

        $this->assertNotFalse($paths);
        $this->assertNotEmpty($paths);

        return $paths[0];
    }
}
