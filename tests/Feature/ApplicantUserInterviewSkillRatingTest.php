<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantAssessment;
use App\Models\ApplicantAssessmentScore;
use App\Models\ApplicantStatus;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeProfile;
use App\Models\JobVacancy;
use App\Models\JobVacancySkillCriterion;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ApplicantUserInterviewSkillRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_skill_sub_ratings_are_averaged_by_weight_into_skill_teknis(): void
    {
        $user = $this->createSuperuser();
        $jobVacancy = $this->createJobVacancyWithSkillCriteria();
        $applicant = $this->createApplicant($jobVacancy);
        $skillCriteria = $jobVacancy->skillCriteria()->get()->keyBy('name');

        $this->actingAs($user);

        foreach (['Desain Grafis' => 4, 'Illustration' => 3, 'Motion Graphic' => 5] as $name => $rating) {
            $this->rateUserInterview($applicant, (string) $skillCriteria[$name]->id, $rating)->assertOk();
        }

        $this->rateUserInterview($applicant, (string) $skillCriteria['UI/UX']->id, 4)
            ->assertOk()
            ->assertJson([
                'parent_key' => 'technical_skill',
                'parent_raw_score' => 3.95,
                'parent_raw_score_display' => '3.95 / 5',
                'parent_weighted_score' => 19.75,
                'parent_weighted_score_display' => '19.75 / 25',
                'total_score' => 19.75,
            ]);

        $this->rateUserInterview($applicant, 'creativity', 5)
            ->assertOk()
            ->assertJson([
                'parent_key' => null,
                'total_score' => 39.75,
            ]);

        $assessment = ApplicantAssessment::query()
            ->whereBelongsTo($applicant)
            ->where('section', ApplicantAssessment::SECTION_INTERVIEW_USER)
            ->firstOrFail();

        $this->assertSame('39.75', $assessment->total_score);
        $this->assertDatabaseHas('applicant_assessment_scores', [
            'applicant_assessment_id' => $assessment->id,
            'criterion_key' => 'technical_skill',
            'rating' => null,
            'raw_score' => 3.95,
            'weighted_score' => 19.75,
        ]);
        $this->assertDatabaseHas('applicant_assessment_scores', [
            'applicant_assessment_id' => $assessment->id,
            'criterion_key' => (string) $skillCriteria['Desain Grafis']->id,
            'parent_key' => 'technical_skill',
            'source_type' => ApplicantAssessmentScore::SOURCE_JOB_VACANCY_SKILL_CRITERION,
            'rating' => 4,
        ]);
    }

    public function test_skill_teknis_cannot_be_rated_directly_when_vacancy_has_skill_criteria(): void
    {
        $jobVacancy = $this->createJobVacancyWithSkillCriteria();
        $applicant = $this->createApplicant($jobVacancy);

        $this->actingAs($this->createSuperuser())
            ->rateUserInterview($applicant, 'technical_skill', 5)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('criterion_key');

        $this->assertDatabaseMissing('applicant_assessments', [
            'applicant_id' => $applicant->id,
            'section' => ApplicantAssessment::SECTION_INTERVIEW_USER,
        ]);
    }

    public function test_skill_teknis_is_rated_directly_when_vacancy_has_no_skill_criteria(): void
    {
        $jobVacancy = JobVacancy::query()->create([
            'name' => 'Admin Staff',
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);
        $applicant = $this->createApplicant($jobVacancy);

        $this->actingAs($this->createSuperuser())
            ->rateUserInterview($applicant, 'technical_skill', 4)
            ->assertOk()
            ->assertJson([
                'parent_key' => null,
                'total_score' => 20,
            ]);
    }

    public function test_skill_criterion_of_another_vacancy_is_rejected(): void
    {
        $applicant = $this->createApplicant($this->createJobVacancyWithSkillCriteria());
        $otherSkillCriterion = $this->createJobVacancyWithSkillCriteria('Motion Designer')->skillCriteria()->firstOrFail();

        $this->actingAs($this->createSuperuser())
            ->rateUserInterview($applicant, (string) $otherSkillCriterion->id, 5)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('criterion_key');
    }

    public function test_ratings_of_removed_skill_criteria_are_ignored(): void
    {
        $user = $this->createSuperuser();
        $jobVacancy = $this->createJobVacancyWithSkillCriteria();
        $applicant = $this->createApplicant($jobVacancy);

        $this->actingAs($user);

        $oldSkillCriterionIds = $jobVacancy->skillCriteria()->pluck('id')->all();

        foreach ($jobVacancy->skillCriteria()->get() as $skillCriterion) {
            $this->rateUserInterview($applicant, (string) $skillCriterion->id, 5)->assertOk();
        }

        $jobVacancy->skillCriteria()->delete();
        $newSkillCriterion = $jobVacancy->skillCriteria()->create([
            'name' => 'Branding',
            'weight' => 100,
            'sort_order' => 0,
        ]);

        $this->rateUserInterview($applicant->fresh(), (string) $newSkillCriterion->id, 2)
            ->assertOk()
            ->assertJson([
                'parent_raw_score' => 2,
                'total_score' => 10,
            ]);

        foreach ($oldSkillCriterionIds as $oldSkillCriterionId) {
            $this->assertDatabaseMissing('applicant_assessment_scores', [
                'criterion_key' => (string) $oldSkillCriterionId,
                'parent_key' => 'technical_skill',
            ]);
        }
    }

    public function test_interview_user_tab_renders_skill_sub_criteria_under_skill_teknis(): void
    {
        $user = $this->createSuperuser();
        $jobVacancy = $this->createJobVacancyWithSkillCriteria();
        $applicant = $this->createApplicant($jobVacancy);
        $skillCriteria = $jobVacancy->skillCriteria()->get()->keyBy('name');
        $designCriterion = $skillCriteria['Desain Grafis'];

        $this->actingAs($user);

        foreach (['Desain Grafis' => 4, 'Illustration' => 3, 'Motion Graphic' => 3, 'UI/UX' => 3] as $name => $rating) {
            $this->rateUserInterview($applicant, (string) $skillCriteria[$name]->id, $rating)->assertOk();
        }

        $this->get(route('applicant.assessment', $applicant))
            ->assertOk()
            ->assertSeeInOrder(['Skill Teknis', '17.50 / 25', 'poin', 'Desain Grafis', 'Illustration', 'Motion Graphic', 'UI/UX', 'Kreativitas'])
            ->assertDontSee('3.50 / 5')
            ->assertSee('data-nested-parent-score="technical_skill"', false)
            ->assertSee('data-criterion-key="'.$designCriterion->id.'" data-parent-key="technical_skill"', false)
            ->assertDontSee('data-criterion-key="technical_skill"', false);
    }

    private function rateUserInterview(Applicant $applicant, string $criterionKey, int $rating): TestResponse
    {
        return $this->postJson(route('applicant.assessment.user-interview-score.store', $applicant), [
            'criterion_key' => $criterionKey,
            'rating' => $rating,
        ]);
    }

    private function createJobVacancyWithSkillCriteria(string $name = 'Graphics Designer & Illustrator'): JobVacancy
    {
        $jobVacancy = JobVacancy::query()->create([
            'name' => $name,
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);

        foreach ([['Desain Grafis', 50], ['Illustration', 20], ['Motion Graphic', 15], ['UI/UX', 15]] as $index => [$criterionName, $weight]) {
            JobVacancySkillCriterion::query()->create([
                'job_vacancy_id' => $jobVacancy->id,
                'name' => $criterionName,
                'weight' => $weight,
                'sort_order' => $index,
            ]);
        }

        return $jobVacancy;
    }

    private function createApplicant(JobVacancy $jobVacancy): Applicant
    {
        $status = ApplicantStatus::query()
            ->where('value', ApplicantStatus::VALUE_SUBMITTED)
            ->firstOrFail();

        return Applicant::query()->create([
            'job_vacancy_id' => $jobVacancy->id,
            'applicant_status_id' => $status->id,
            'full_name' => 'Farabi Akil Azra',
            'email' => uniqid().'@example.test',
        ]);
    }

    private function createSuperuser(): User
    {
        $user = User::query()->create([
            'email' => uniqid().'@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);

        $user->assignRole($role);
        $position = Position::query()->create(['name' => 'Super Administrator']);
        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);
        EmployeeProfile::query()->create([
            'employee_id' => $employee->id,
            'name' => 'Super Administrator',
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_position_id' => $position->id,
            'status' => 'Active',
        ]);

        return $user;
    }
}
