<?php

namespace Tests\Feature;

use App\Mail\ApplicantStatusMail;
use App\Models\Applicant;
use App\Models\ApplicantStatus;
use App\Models\JobVacancy;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicantStatusMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_applicant_status_sends_email_branded_for_the_applicants_company(): void
    {
        Mail::fake();

        $jobVacancy = JobVacancy::query()->create([
            'name' => 'Backend Engineer',
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);

        $submittedStatus = ApplicantStatus::query()->where('value', ApplicantStatus::VALUE_SUBMITTED)->firstOrFail();
        $hrInterviewStatus = ApplicantStatus::query()->where('value', ApplicantStatus::VALUE_HR_INTERVIEW)->firstOrFail();

        $applicant = Applicant::query()->create([
            'job_vacancy_id' => $jobVacancy->id,
            'applicant_status_id' => $submittedStatus->id,
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'brand_key' => 'tms',
        ]);

        $this->actingAs($this->createSuperuser())
            ->patch(route('applicant.status.update', $applicant), [
                'applicant_status_id' => $hrInterviewStatus->id,
            ])
            ->assertRedirect();

        $this->assertSame($hrInterviewStatus->id, $applicant->refresh()->applicant_status_id);

        Mail::assertSent(ApplicantStatusMail::class, function (ApplicantStatusMail $mail) use ($applicant, $hrInterviewStatus): bool {
            return $mail->hasTo('jane@example.com')
                && $mail->applicant->is($applicant)
                && $mail->applicant->applicant_status_id === $hrInterviewStatus->id
                && $mail->brand['key'] === 'tms'
                && $mail->brand['name'] === 'TMS';
        });
    }

    public function test_null_brand_key_falls_back_to_rnb_branding(): void
    {
        config(['career_brands.default_brand' => 'tms']);

        Mail::fake();

        $jobVacancy = JobVacancy::query()->create([
            'name' => 'Backend Engineer',
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);

        $status = ApplicantStatus::query()->where('value', ApplicantStatus::VALUE_SUBMITTED)->firstOrFail();

        $applicant = Applicant::query()->create([
            'job_vacancy_id' => $jobVacancy->id,
            'applicant_status_id' => $status->id,
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'brand_key' => null,
        ]);

        $this->actingAs($this->createSuperuser())
            ->patch(route('applicant.status.update', $applicant), [
                'applicant_status_id' => $status->id,
            ])
            ->assertRedirect();

        Mail::assertSent(ApplicantStatusMail::class, fn (ApplicantStatusMail $mail): bool => $mail->brand['key'] === 'rnb'
            && $mail->brand['name'] === 'RNB Management');
    }

    public function test_status_update_is_not_blocked_when_applicant_has_no_email(): void
    {
        Mail::fake();

        $jobVacancy = JobVacancy::query()->create([
            'name' => 'Backend Engineer',
            'status' => JobVacancy::STATUS_ACTIVE,
        ]);

        $status = ApplicantStatus::query()->where('value', ApplicantStatus::VALUE_SUBMITTED)->firstOrFail();

        $applicant = Applicant::query()->create([
            'job_vacancy_id' => $jobVacancy->id,
            'applicant_status_id' => $status->id,
            'full_name' => 'No Email Applicant',
            'email' => null,
            'brand_key' => null,
        ]);

        $this->actingAs($this->createSuperuser())
            ->patch(route('applicant.status.update', $applicant), [
                'applicant_status_id' => $status->id,
            ])
            ->assertRedirect();

        Mail::assertNothingSent();
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
