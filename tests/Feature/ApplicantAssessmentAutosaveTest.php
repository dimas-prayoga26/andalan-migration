<?php

namespace Tests\Feature;

use App\Http\Controllers\TalentAcquisitionController;
use App\Services\ApplicantAssessmentScoreService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApplicantAssessmentAutosaveTest extends TestCase
{
    public function test_assessment_rating_autosave_is_wired(): void
    {
        $hrRoute = Route::getRoutes()->getByName('applicant.assessment.hr-interview-score.store');
        $technicalRoute = Route::getRoutes()->getByName('applicant.assessment.technical-test-score.store');
        $userRoute = Route::getRoutes()->getByName('applicant.assessment.user-interview-score.store');
        $notesRoute = Route::getRoutes()->getByName('applicant.assessment.notes.store');
        $finalRoute = Route::getRoutes()->getByName('applicant.assessment.final.store');
        $controller = File::get(app_path('Http/Controllers/TalentAcquisitionController.php'));
        $service = File::get(app_path('Services/ApplicantAssessmentScoreService.php'));
        $finalService = File::get(app_path('Services/ApplicantFinalAssessmentService.php'));
        $view = File::get(resource_path('views/applicant_data/assessment.blade.php'));
        $config = File::get(config_path('applicant_assessments.php'));

        $this->assertNotNull($hrRoute);
        $this->assertSame('applicant/{applicant}/assessment/hr-interview-score', $hrRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@storeApplicantHrInterviewScore', $hrRoute?->getActionName());
        $this->assertNotNull($technicalRoute);
        $this->assertSame('applicant/{applicant}/assessment/technical-test-score', $technicalRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@storeApplicantTechnicalTestScore', $technicalRoute?->getActionName());
        $this->assertNotNull($userRoute);
        $this->assertSame('applicant/{applicant}/assessment/user-interview-score', $userRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@storeApplicantUserInterviewScore', $userRoute?->getActionName());
        $this->assertNotNull($notesRoute);
        $this->assertSame('applicant/{applicant}/assessment/{section}/notes', $notesRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@storeApplicantAssessmentNotes', $notesRoute?->getActionName());
        $this->assertNotNull($finalRoute);
        $this->assertSame('applicant/{applicant}/assessment/final', $finalRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@storeApplicantFinalAssessment', $finalRoute?->getActionName());

        $this->assertStringContainsString('storeApplicantHrInterviewScore', $controller);
        $this->assertStringContainsString('storeApplicantTechnicalTestScore', $controller);
        $this->assertStringContainsString('storeApplicantUserInterviewScore', $controller);
        $this->assertStringContainsString('storeApplicantAssessmentNotes', $controller);
        $this->assertStringContainsString('storeApplicantFinalAssessment', $controller);
        $this->assertStringContainsString('storeHrInterviewRating', $controller);
        $this->assertStringContainsString('storeTechnicalTestRating', $controller);
        $this->assertStringContainsString('storeUserInterviewRating', $controller);
        $this->assertStringContainsString('storeSectionNotes', $controller);
        $this->assertStringContainsString('ApplicantFinalAssessmentService', $controller);
        $this->assertStringContainsString('finalAssessmentForView', $controller);
        $this->assertStringContainsString('technicalTestNotes', $controller);
        $this->assertStringContainsString('userInterviewNotes', $controller);
        $this->assertStringContainsString('technicalCriteriaForView', $controller);
        $this->assertStringContainsString('userCriteriaForView', $controller);
        $this->assertStringContainsString('ApplicantAssessmentScoreService', $controller);

        $this->assertStringContainsString('data-assessment-rating', $view);
        $this->assertStringContainsString("route('applicant.assessment.hr-interview-score.store', \$applicant)", $view);
        $this->assertStringContainsString("route('applicant.assessment.technical-test-score.store', \$applicant)", $view);
        $this->assertStringContainsString("route('applicant.assessment.user-interview-score.store', \$applicant)", $view);
        $this->assertStringContainsString('data-score-target="technical-test"', $view);
        $this->assertStringContainsString('is-nested-parent', $view);
        $this->assertStringContainsString('is-nested-child', $view);
        $this->assertStringContainsString('data-nested-parent-score="{{ $criteria[\'key\'] }}"', $view);
        $this->assertStringContainsString('payload.parent_weighted_score_display', $view);
        $this->assertStringNotContainsString('payload.parent_raw_score_display', $view);
        $this->assertStringContainsString('data-score-target="user-interview"', $view);
        $this->assertStringContainsString('data-assessment-score="technical-test"', $view);
        $this->assertStringContainsString('data-assessment-score="user-interview"', $view);
        $this->assertStringContainsString('Kriteria Tes Teknis mengikuti bobot dari lowongan yang dilamar.', $view);
        $this->assertStringContainsString('data-assessment-note', $view);
        $this->assertStringContainsString("route('applicant.assessment.notes.store', [\$applicant, 'technical_test'])", $view);
        $this->assertStringContainsString("route('applicant.assessment.notes.store', [\$applicant, 'interview_user'])", $view);
        $this->assertStringContainsString('placeholder="Tulis catatan reviewer technical test."', $view);
        $this->assertStringContainsString('placeholder="Tulis catatan reviewer interview user."', $view);
        $this->assertStringContainsString('name="technical_test_notes"', $view);
        $this->assertStringContainsString('name="interview_user_notes"', $view);
        $this->assertStringContainsString('class="form-control applicant-assessment-note-input mt-3"', $view);
        $this->assertStringContainsString('{{ $technicalTestNotes }}', $view);
        $this->assertStringContainsString('{{ $userInterviewNotes }}', $view);
        $this->assertStringContainsString("route('applicant.assessment.final.store', \$applicant)", $view);
        $this->assertStringContainsString("asset('assets/vendor/sweetalert2/sweetalert2.min.css')", $view);
        $this->assertStringContainsString("@include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])", $view);
        $this->assertStringContainsString("@include('partials.swal-alert', ['type' => 'error', 'message' => \$errors->first()])", $view);
        $this->assertStringNotContainsString('alert alert-success', $view);
        $this->assertStringNotContainsString('alert alert-danger', $view);
        $this->assertStringContainsString('name="skill_note"', $view);
        $this->assertStringContainsString('name="communication_note"', $view);
        $this->assertStringContainsString('name="experience_note"', $view);
        $this->assertStringContainsString('name="tools_note"', $view);
        $this->assertStringContainsString('name="final_note"', $view);
        $this->assertStringContainsString('name="final_decision"', $view);
        $this->assertStringContainsString("old('skill_note', \$finalAssessment['skill_note'] ?? '')", $view);
        $this->assertStringContainsString("old('final_note', \$finalAssessment['final_note'] ?? '')", $view);
        $this->assertStringContainsString('Save Penilaian Akhir', $view);
        $this->assertStringNotContainsString('Catatan reviewer technical test akan ditampilkan di sini.', $view);
        $this->assertStringNotContainsString('Catatan reviewer interview user akan ditampilkan di sini.', $view);
        $this->assertGreaterThan(
            strpos($view, 'id="user-interview-pane"'),
            strpos($view, 'placeholder="Tulis catatan reviewer interview user."')
        );
        $this->assertLessThan(
            strpos($view, 'id="final-assessment-pane"'),
            strpos($view, 'placeholder="Tulis catatan reviewer interview user."')
        );
        $this->assertStringContainsString('fetch(row.dataset.storeUrl', $view);
        $this->assertStringContainsString('fetch(textarea.dataset.storeUrl', $view);
        $this->assertStringContainsString('notes: textarea.value', $view);
        $this->assertStringNotContainsString("textarea.addEventListener('input'", $view);
        $this->assertStringContainsString("'X-CSRF-TOKEN': csrfToken", $view);
        $this->assertStringNotContainsString("'Clean Output / Kualitas Hasil'", $view);
        $this->assertStringNotContainsString('75 / 100', $view);
        $this->assertStringNotContainsString('Save Nilai', $view);

        $this->assertStringContainsString('storeHrInterviewRating', $service);
        $this->assertStringContainsString('storeTechnicalTestRating', $service);
        $this->assertStringContainsString('storeUserInterviewRating', $service);
        $this->assertStringContainsString('notesForSection', $service);
        $this->assertStringContainsString('storeSectionNotes', $service);
        $this->assertStringContainsString('SOURCE_JOB_VACANCY_TECHNICAL_CRITERION', $service);
        $this->assertStringContainsString('SOURCE_JOB_VACANCY_SKILL_CRITERION', $service);
        $this->assertStringContainsString('storeUserInterviewSkillRating', $service);
        $this->assertStringContainsString('refreshNestedSkillScore', $service);
        $this->assertStringContainsString('parent_key', $service);
        $this->assertStringContainsString('whereNull(\'parent_key\')', $service);
        $this->assertStringContainsString('SOURCE_FIXED_USER', $service);
        $this->assertStringContainsString('updateOrCreate', $service);
        $this->assertStringContainsString('SOURCE_FIXED_HR', $service);
        $this->assertStringContainsString('weightedScore', $service);

        $this->assertStringContainsString('finalAssessmentForView', $finalService);
        $this->assertStringContainsString('updateOrCreate', $finalService);
        $this->assertStringContainsString('skill_note', $finalService);
        $this->assertStringContainsString('final_decision', $finalService);

        $this->assertStringContainsString("'key' => 'communication'", $config);
        $this->assertStringContainsString("'label' => 'Kemampuan Komunikasi'", $config);
        $this->assertStringContainsString("'key' => 'technical_skill'", $config);
        $this->assertStringContainsString("'label' => 'Skill Teknis'", $config);
        $this->assertStringContainsString("'uses_skill_criteria' => true", $config);
        $this->assertStringContainsString("'default_rating' => 0", $config);
    }

    public function test_hr_interview_default_score_uses_weighted_rating_formula(): void
    {
        $service = app(ApplicantAssessmentScoreService::class);

        $this->assertSame(16.0, $service->weightedScore(4, 20));
        $this->assertSame(0.0, $service->calculateTotalScore($service->hrCriteria()));
        $this->assertSame(0.0, $service->calculateTotalScore($service->userCriteria()));
    }

    public function test_nested_skill_score_uses_child_weight_then_parent_weight(): void
    {
        $service = app(ApplicantAssessmentScoreService::class);

        $this->assertSame(19.75, $service->calculateTotalScore([
            [
                'weight' => 25,
                'is_nested' => true,
                'children' => [
                    ['selected' => 4, 'weight' => 50],
                    ['selected' => 3, 'weight' => 20],
                    ['selected' => 5, 'weight' => 15],
                    ['selected' => 4, 'weight' => 15],
                ],
            ],
        ]));
    }
}
