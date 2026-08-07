<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantStatus;
use App\Models\JobVacancy;
use App\Services\Applicants\LegacyApplicantSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class TalentAcquisitionController extends Controller
{
    public function applicants(LegacyApplicantSyncService $legacyApplicantSync): View
    {
        $syncResult = $legacyApplicantSync->sync();

        $applicants = Applicant::query()
            ->select([
                'id',
                'job_vacancy_id',
                'applicant_status_id',
                'full_name',
                'photo',
                'legacy_created_at',
            ])
            ->with([
                'jobVacancy:id,name',
            ])
            ->latest('legacy_created_at')
            ->latest('legacy_applicant_id')
            ->latest('created_at')
            ->get();

        $jobVacancies = JobVacancy::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $applicantStatuses = ApplicantStatus::query()
            ->orderBy('value')
            ->get(['id', 'value', 'name']);

        return view('applicant_data.index', [
            'applicants' => $applicants,
            'applicantStatuses' => $applicantStatuses,
            'jobVacancies' => $jobVacancies,
            'syncResult' => $syncResult,
        ]);
    }

    public function jobVacancies(LegacyApplicantSyncService $legacyApplicantSync): View
    {
        $syncResult = $legacyApplicantSync->sync();

        $jobVacancies = JobVacancy::query()
            ->withCount('applicants')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('applicant_data.job_vancancies', [
            'jobVacancies' => $jobVacancies,
            'jobVacancyStatuses' => JobVacancy::statusOptions(),
            'syncResult' => $syncResult,
        ]);
    }

    public function showApplicant(Applicant $applicant): View
    {
        $applicant->load([
            'jobVacancy:id,name',
            'applicantStatus:id,value,name',
            'gender:id,name',
            'maritalStatus:id,name',
            'educations:id,applicant_id,education_level_id,sequence,institution,gpa,department,start_period,graduate_period',
            'educations.educationLevel:id,name',
            'workExperiences:id,applicant_id,sequence,company_name,role,company_location,start_period,end_period',
        ]);

        return view('applicant_data.show', [
            'applicant' => $applicant,
        ]);
    }

    public function updateApplicantStatus(Request $request, Applicant $applicant): RedirectResponse
    {
        $validated = $request->validate([
            'applicant_status_id' => ['required', Rule::exists((new ApplicantStatus)->getTable(), 'id')],
        ]);

        $applicantStatus = ApplicantStatus::query()->findOrFail($validated['applicant_status_id']);

        try {
            DB::transaction(function () use ($applicant, $applicantStatus): void {
                if ($applicant->legacy_applicant_id !== null) {
                    DB::connection('legacy_mysql')
                        ->table('applicants')
                        ->where('id', $applicant->legacy_applicant_id)
                        ->update(['nb' => $applicantStatus->value]);
                }

                $applicant->update([
                    'applicant_status_id' => $applicantStatus->id,
                ]);
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->withErrors([
                'applicant_status_id' => 'Status pelamar gagal diperbarui.',
            ]);
        }

        return back()->with('status', 'Status pelamar berhasil diperbarui.');
    }

    public function updateJobVacancyStatus(Request $request, JobVacancy $jobVacancy): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(JobVacancy::statuses())],
        ]);

        $legacyStatusValue = JobVacancy::legacyStatusValueFor($validated['status']);

        try {
            DB::transaction(function () use ($jobVacancy, $validated, $legacyStatusValue): void {
                if ($jobVacancy->legacy_vacancy_id !== null) {
                    DB::connection('legacy_mysql')
                        ->table('opt_applicants_vacancies')
                        ->where('id', $jobVacancy->legacy_vacancy_id)
                        ->update(['status' => $legacyStatusValue]);
                }

                $jobVacancy->update([
                    'status' => $validated['status'],
                    'legacy_status_value' => $legacyStatusValue,
                ]);
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->withErrors([
                'status' => 'Status lowongan gagal diperbarui.',
            ]);
        }

        return back()->with('status', 'Status lowongan berhasil diperbarui.');
    }

    public function destroyApplicant(Applicant $applicant): RedirectResponse
    {
        $applicant->delete();

        return redirect()->route('applicant')->with('status', 'Data pelamar berhasil dihapus.');
    }
}
