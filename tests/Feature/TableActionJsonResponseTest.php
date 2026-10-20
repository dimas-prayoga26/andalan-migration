<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantStatus;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TableActionJsonResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_delete_returns_json_for_ajax_requests(): void
    {
        $company = Company::factory()->create();

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->deleteJson(route('settings.companies.destroy', $company))
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Company has been deleted.',
            ]);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_settings_delete_returns_unprocessable_json_when_deletion_is_refused(): void
    {
        $position = Position::factory()->create(['is_protected' => true]);

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->deleteJson(route('settings.positions.destroy', $position))
            ->assertUnprocessable()
            ->assertExactJson([
                'success' => false,
                'message' => 'Protected position cannot be deleted because it is required by the system.',
            ]);

        $this->assertDatabaseHas('positions', ['id' => $position->id]);
    }

    public function test_settings_delete_still_redirects_for_regular_form_submissions(): void
    {
        $company = Company::factory()->create();

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->delete(route('settings.companies.destroy', $company))
            ->assertRedirect(route('settings.companies.index'))
            ->assertSessionHas('status', 'Company has been deleted.');
    }

    public function test_applicant_delete_and_status_update_return_json_for_ajax_requests(): void
    {
        Mail::fake();

        $user = User::factory()->superAdministrator()->create();
        $applicant = $this->createApplicant();
        $nextStatus = ApplicantStatus::query()
            ->where('value', '!=', ApplicantStatus::VALUE_SUBMITTED)
            ->orderBy('value')
            ->firstOrFail();

        $this->actingAs($user)
            ->patchJson(route('applicant.status.update', $applicant), ['applicant_status_id' => $nextStatus->id])
            ->assertOk()
            ->assertJsonStructure(['success', 'message', 'mail_sent'])
            ->assertJson(['success' => true]);

        $this->assertSame($nextStatus->id, $applicant->refresh()->applicant_status_id);

        $this->actingAs($user)
            ->deleteJson(route('applicant.destroy', $applicant))
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Data pelamar berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('applicants', ['id' => $applicant->id]);
    }

    public function test_applicant_status_update_validates_status_for_ajax_requests(): void
    {
        $this->actingAs(User::factory()->superAdministrator()->create())
            ->patchJson(route('applicant.status.update', $this->createApplicant()), ['applicant_status_id' => 'missing-status'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('applicant_status_id');
    }

    public function test_job_vacancy_activation_without_technical_criteria_returns_edit_url(): void
    {
        $jobVacancy = JobVacancy::query()->create([
            'name' => 'Backend Engineer',
            'status' => JobVacancy::STATUS_INACTIVE,
        ]);

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->patchJson(route('applicant.job_vacancies.status.update', $jobVacancy), ['status' => JobVacancy::STATUS_ACTIVE])
            ->assertUnprocessable()
            ->assertExactJson([
                'success' => false,
                'message' => 'Isi kriteria tes teknis terlebih dahulu sebelum lowongan diaktifkan.',
                'redirect_url' => route('applicant.job_vacancies.edit', $jobVacancy),
            ]);

        $this->assertSame(JobVacancy::STATUS_INACTIVE, (int) $jobVacancy->refresh()->status);
    }

    public function test_job_vacancy_status_update_and_delete_return_json_for_ajax_requests(): void
    {
        $user = User::factory()->superAdministrator()->create();
        $jobVacancy = JobVacancy::query()->create([
            'name' => 'Frontend Engineer',
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->patchJson(route('applicant.job_vacancies.status.update', $jobVacancy), ['status' => JobVacancy::STATUS_INACTIVE])
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Status lowongan berhasil diperbarui.',
            ]);

        $this->assertSame(JobVacancy::STATUS_INACTIVE, (int) $jobVacancy->refresh()->status);

        $this->actingAs($user)
            ->deleteJson(route('applicant.job_vacancies.destroy', $jobVacancy))
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Lowongan berhasil dihapus.',
            ]);
    }

    private function createApplicant(): Applicant
    {
        $jobVacancy = JobVacancy::query()->create([
            'name' => 'Graphic Designer',
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);

        return Applicant::query()->create([
            'job_vacancy_id' => $jobVacancy->id,
            'applicant_status_id' => ApplicantStatus::query()->where('value', ApplicantStatus::VALUE_SUBMITTED)->firstOrFail()->id,
            'full_name' => 'Dimas Prayoga',
            'email' => 'dimas@example.test',
        ]);
    }
}
