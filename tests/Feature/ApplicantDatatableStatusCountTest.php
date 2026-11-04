<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantStatus;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantDatatableStatusCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_datatable_returns_applicant_counts_per_status(): void
    {
        $graphicDesigner = $this->createJobVacancy('Graphic Designer');

        $this->createApplicant($graphicDesigner, ApplicantStatus::VALUE_SUBMITTED);
        $this->createApplicant($graphicDesigner, ApplicantStatus::VALUE_SUBMITTED);
        $this->createApplicant($graphicDesigner, ApplicantStatus::VALUE_HR_INTERVIEW);
        $this->createApplicant($graphicDesigner, ApplicantStatus::VALUE_NOT_SUITABLE);

        $response = $this->actingAs($this->createTalentAcquisitionUser())
            ->getJson(route('applicant.datatable'))
            ->assertOk()
            ->assertJsonPath('total_count', 4);

        $this->assertSame([
            ApplicantStatus::VALUE_SUBMITTED => 2,
            ApplicantStatus::VALUE_HR_INTERVIEW => 1,
            ApplicantStatus::VALUE_TECHNICAL_TEST => 0,
            ApplicantStatus::VALUE_USER_INTERVIEW => 0,
            ApplicantStatus::VALUE_OFFERING => 0,
            ApplicantStatus::VALUE_NOT_SUITABLE => 1,
        ], $response->json('status_counts'));
    }

    public function test_status_filter_limits_rows_but_keeps_counts_for_every_status(): void
    {
        $graphicDesigner = $this->createJobVacancy('Graphic Designer');

        $this->createApplicant($graphicDesigner, ApplicantStatus::VALUE_SUBMITTED);
        $this->createApplicant($graphicDesigner, ApplicantStatus::VALUE_OFFERING);

        $response = $this->actingAs($this->createTalentAcquisitionUser())
            ->getJson(route('applicant.datatable', ['status_value' => ApplicantStatus::VALUE_OFFERING]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.applicant_status_value', ApplicantStatus::VALUE_OFFERING)
            ->assertJsonPath('total_count', 2);

        $this->assertSame(1, $response->json('status_counts.'.ApplicantStatus::VALUE_SUBMITTED));
        $this->assertSame(1, $response->json('status_counts.'.ApplicantStatus::VALUE_OFFERING));
    }

    public function test_position_filter_scopes_status_counts(): void
    {
        $graphicDesigner = $this->createJobVacancy('Graphic Designer');
        $estimator = $this->createJobVacancy('Estimator');

        $this->createApplicant($graphicDesigner, ApplicantStatus::VALUE_SUBMITTED);
        $this->createApplicant($estimator, ApplicantStatus::VALUE_SUBMITTED);
        $this->createApplicant($estimator, ApplicantStatus::VALUE_TECHNICAL_TEST);

        $response = $this->actingAs($this->createTalentAcquisitionUser())
            ->getJson(route('applicant.datatable', ['job_vacancy_name' => 'Estimator']))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('total_count', 2);

        $this->assertSame(1, $response->json('status_counts.'.ApplicantStatus::VALUE_SUBMITTED));
        $this->assertSame(1, $response->json('status_counts.'.ApplicantStatus::VALUE_TECHNICAL_TEST));
    }

    public function test_status_counts_ignore_deleted_applicants(): void
    {
        $graphicDesigner = $this->createJobVacancy('Graphic Designer');

        $this->createApplicant($graphicDesigner, ApplicantStatus::VALUE_USER_INTERVIEW);
        $this->createApplicant($graphicDesigner, ApplicantStatus::VALUE_USER_INTERVIEW)->delete();

        $response = $this->actingAs($this->createTalentAcquisitionUser())
            ->getJson(route('applicant.datatable'))
            ->assertOk()
            ->assertJsonPath('total_count', 1);

        $this->assertSame(1, $response->json('status_counts.'.ApplicantStatus::VALUE_USER_INTERVIEW));
    }

    private function createTalentAcquisitionUser(): User
    {
        return User::factory()->activeEmployee(['view-talent-acquisition'])->create();
    }

    private function createJobVacancy(string $name): JobVacancy
    {
        return JobVacancy::query()->create([
            'name' => $name,
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);
    }

    private function createApplicant(JobVacancy $jobVacancy, int $statusValue): Applicant
    {
        return Applicant::query()->create([
            'job_vacancy_id' => $jobVacancy->id,
            'applicant_status_id' => ApplicantStatus::query()->where('value', $statusValue)->value('id'),
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ]);
    }
}
