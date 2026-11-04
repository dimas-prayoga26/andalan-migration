<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantAssessment;
use App\Models\ApplicantStatus;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantDatatableAssessmentScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_datatable_returns_the_score_of_each_assessed_stage(): void
    {
        $applicant = $this->createApplicant();

        foreach ([
            ApplicantAssessment::SECTION_HR_INTERVIEW => 85.4,
            ApplicantAssessment::SECTION_INTERVIEW_USER => 72,
        ] as $section => $totalScore) {
            ApplicantAssessment::query()->create([
                'applicant_id' => $applicant->id,
                'section' => $section,
                'total_score' => $totalScore,
                'status' => ApplicantAssessment::STATUS_SUBMITTED,
            ]);
        }

        $response = $this->actingAs($this->createTalentAcquisitionUser())
            ->getJson(route('applicant.datatable'))
            ->assertOk();

        $this->assertSame([
            'hr_interview' => 85.4,
            'technical_test' => null,
            'interview_user' => 72,
        ], $response->json('data.0.assessment_scores'));
    }

    public function test_datatable_returns_empty_scores_for_an_applicant_that_was_not_assessed(): void
    {
        $this->createApplicant();

        $response = $this->actingAs($this->createTalentAcquisitionUser())
            ->getJson(route('applicant.datatable'))
            ->assertOk();

        $this->assertSame([
            'hr_interview' => null,
            'technical_test' => null,
            'interview_user' => null,
        ], $response->json('data.0.assessment_scores'));
    }

    private function createTalentAcquisitionUser(): User
    {
        return User::factory()->activeEmployee(['view-talent-acquisition'])->create();
    }

    private function createApplicant(): Applicant
    {
        $jobVacancy = JobVacancy::query()->create([
            'name' => 'Graphic Designer',
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);

        return Applicant::query()->create([
            'job_vacancy_id' => $jobVacancy->id,
            'applicant_status_id' => ApplicantStatus::query()->where('value', ApplicantStatus::VALUE_HR_INTERVIEW)->value('id'),
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ]);
    }
}
