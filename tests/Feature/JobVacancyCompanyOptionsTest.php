<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobVacancyCompanyOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_tabs_only_show_companies_with_a_career_brand(): void
    {
        $kma = Company::factory()->create(['name' => 'KMA']);
        $trah = Company::factory()->create(['name' => 'Trah']);
        $birendra = Company::factory()->create(['name' => 'Birendra']);
        $rmg = Company::factory()->create(['name' => 'RMG']);

        $this->actingAs($this->createTalentAcquisitionUser())
            ->get(route('applicant.job_vacancies'))
            ->assertOk()
            ->assertSee('data-company-filter="'.$kma->id.'"', false)
            ->assertSee('data-company-filter="'.$trah->id.'"', false)
            ->assertDontSee('data-company-filter="'.$birendra->id.'"', false)
            ->assertDontSee('data-company-filter="'.$rmg->id.'"', false);
    }

    public function test_company_tabs_hide_inactive_career_brand_companies(): void
    {
        $inactiveNiskala = Company::factory()->create(['name' => 'Niskala', 'is_active' => false]);

        $this->actingAs($this->createTalentAcquisitionUser())
            ->get(route('applicant.job_vacancies'))
            ->assertOk()
            ->assertDontSee('data-company-filter="'.$inactiveNiskala->id.'"', false);
    }

    public function test_job_vacancy_form_only_lists_companies_with_a_career_brand(): void
    {
        $rnb = Company::factory()->create(['name' => 'RNB']);
        $andalanku = Company::factory()->create(['name' => 'AndalanKu']);

        $this->actingAs($this->createTalentAcquisitionUser())
            ->get(route('applicant.job_vacancies.create'))
            ->assertOk()
            ->assertSee('<option value="'.$rnb->id.'"', false)
            ->assertDontSee('<option value="'.$andalanku->id.'"', false);
    }

    public function test_storing_a_job_vacancy_rejects_company_without_a_career_brand(): void
    {
        $birendra = Company::factory()->create(['name' => 'Birendra']);

        $this->actingAs($this->createTalentAcquisitionUser())
            ->post(route('applicant.job_vacancies.store'), $this->jobVacancyPayload($birendra))
            ->assertSessionHasErrors('company_id');

        $this->assertDatabaseMissing('job_vacancies', ['company_id' => $birendra->id]);
    }

    public function test_storing_a_job_vacancy_accepts_company_with_a_career_brand(): void
    {
        $tms = Company::factory()->create(['name' => 'TMS']);

        $this->actingAs($this->createTalentAcquisitionUser())
            ->post(route('applicant.job_vacancies.store'), $this->jobVacancyPayload($tms))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('applicant.job_vacancies'));

        $this->assertDatabaseHas('job_vacancies', [
            'company_id' => $tms->id,
            'name' => 'Graphic Designer',
        ]);
    }

    private function createTalentAcquisitionUser(): User
    {
        return User::factory()->activeEmployee(['view-talent-acquisition'])->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function jobVacancyPayload(Company $company): array
    {
        return [
            'company_id' => $company->id,
            'name' => 'Graphic Designer',
            'status' => JobVacancy::STATUS_ACTIVE,
            'technical_criteria' => [
                ['name' => 'Portfolio', 'weight' => 100],
            ],
        ];
    }
}
