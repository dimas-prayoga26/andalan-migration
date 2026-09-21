<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantStatus;
use App\Models\JobVacancy;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApplicantDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_applicant_permanently_deletes_applicant_and_related_rows(): void
    {
        $jobVacancy = JobVacancy::query()->create([
            'name' => 'Graphic Designer',
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);

        $status = ApplicantStatus::query()
            ->where('value', ApplicantStatus::VALUE_SUBMITTED)
            ->firstOrFail();

        $applicant = Applicant::query()->create([
            'job_vacancy_id' => $jobVacancy->id,
            'applicant_status_id' => $status->id,
            'full_name' => 'Dimas Prayoga',
            'email' => 'dimas@example.test',
        ]);

        $education = $applicant->educations()->create([
            'sequence' => 1,
            'institution' => 'Example University',
        ]);

        $workExperience = $applicant->workExperiences()->create([
            'sequence' => 1,
            'company_name' => 'Example Studio',
        ]);

        $this->actingAs($this->createSuperuser())
            ->delete(route('applicant.destroy', $applicant))
            ->assertRedirect(route('applicant'));

        $this->assertDatabaseMissing('applicants', [
            'id' => $applicant->id,
        ]);

        $this->assertDatabaseMissing('applicant_educations', [
            'id' => $education->id,
        ]);

        $this->assertDatabaseMissing('applicant_work_experiences', [
            'id' => $workExperience->id,
        ]);
    }

    public function test_talent_acquisition_schema_no_longer_keeps_import_columns(): void
    {
        $this->assertFalse(Schema::hasColumn('applicants', 'legacy_applicant_id'));
        $this->assertFalse(Schema::hasColumn('applicants', 'job_applied_legacy_value'));
        $this->assertFalse(Schema::hasColumn('applicants', 'legacy_created_at'));
        $this->assertFalse(Schema::hasColumn('job_vacancies', 'legacy_created_at'));
        $this->assertFalse(Schema::hasColumn('education_levels', 'legacy_value'));
    }

    private function createSuperuser(): User
    {
        $user = User::query()->create([
            'username' => 'superadmin_'.uniqid(),
            'email' => uniqid().'@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        Role::query()->firstOrCreate([
            'name' => 'superuser',
            'guard_name' => 'web',
        ]);

        $user->assignRole('superuser');

        return $user;
    }
}
