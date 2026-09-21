<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantStatus;
use App\Models\JobVacancy;
use App\Services\Applicants\LegacyApplicantSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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

        $jobVacancies = JobVacancy::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $applicantStatuses = ApplicantStatus::query()
            ->orderBy('value')
            ->get(['id', 'value', 'name']);

        return view('applicant_data.index', [
            'applicantStatuses' => $applicantStatuses,
            'jobVacancies' => $jobVacancies,
            'syncResult' => $syncResult,
        ]);
    }

    public function applicantsDatatable(): JsonResponse
    {
        $applicants = Applicant::query()
            ->select([
                'id',
                'job_vacancy_id',
                'applicant_status_id',
                'full_name',
                'photo',
                'legacy_created_at',
                'legacy_applicant_id',
                'created_at',
            ])
            ->with([
                'applicantStatus:id,value,name',
                'jobVacancy:id,name',
            ])
            ->latest('legacy_created_at')
            ->latest('legacy_applicant_id')
            ->latest('created_at')
            ->get()
            ->map(fn (Applicant $applicant): array => [
                'id' => (string) $applicant->id,
                'full_name' => (string) $applicant->full_name,
                'photo' => (string) ($applicant->photo ?? ''),
                'photo_url' => $applicant->photoUrl(),
                'job_vacancy_name' => (string) ($applicant->jobVacancy?->name ?? '-'),
                'applicant_status_id' => (string) ($applicant->applicant_status_id ?? ''),
                'applicant_status_value' => (int) ($applicant->applicantStatus?->value ?? ApplicantStatus::VALUE_SUBMITTED),
            ])
            ->values();

        return response()->json([
            'data' => $applicants,
        ]);
    }

    public function jobVacancies(LegacyApplicantSyncService $legacyApplicantSync): View
    {
        $syncResult = $legacyApplicantSync->sync();

        return view('applicant_data.job_vancancies', [
            'jobVacancyStatuses' => JobVacancy::statusOptions(),
            'syncResult' => $syncResult,
        ]);
    }

    public function jobVacanciesDatatable(): JsonResponse
    {
        $jobVacancies = JobVacancy::query()
            ->withCount('applicants')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->map(fn (JobVacancy $jobVacancy): array => [
                'id' => (string) $jobVacancy->id,
                'name' => (string) $jobVacancy->name,
                'status' => (string) $jobVacancy->status,
                'status_css_class' => $jobVacancy->statusCssClass(),
                'applicants_count' => (int) $jobVacancy->applicants_count,
                'legacy_created_at' => $jobVacancy->legacy_created_at?->format('d M Y H:i') ?? '-',
            ])
            ->values();

        return response()->json([
            'data' => $jobVacancies,
        ]);
    }

    public function createJobVacancy(): View
    {
        return view('applicant_data.job_vacancy_create', [
            'jobVacancyStatuses' => JobVacancy::statusOptions(),
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

    public function storeJobVacancy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique((new JobVacancy)->getTable(), 'name')],
            'status' => ['required', Rule::in(JobVacancy::statuses())],
        ]);

        $name = trim($validated['name']);
        $status = $validated['status'];
        $legacyStatusValue = JobVacancy::legacyStatusValueFor($status);

        try {
            DB::connection('legacy_mysql')->transaction(function () use ($name, $status, $legacyStatusValue): void {
                $legacyConnection = DB::connection('legacy_mysql');
                $legacyName = htmlentities($name, ENT_QUOTES, 'UTF-8', false);
                $now = now();

                if ($legacyConnection->table('opt_applicants_vacancies')->whereIn('name', [$name, $legacyName])->exists()) {
                    throw new \RuntimeException('Nama lowongan sudah ada di database legacy.');
                }

                $legacyValue = ((int) $legacyConnection
                    ->table('opt_applicants_vacancies')
                    ->lockForUpdate()
                    ->max('value')) + 1;

                $legacyVacancyId = (int) $legacyConnection
                    ->table('opt_applicants_vacancies')
                    ->insertGetId([
                        'name' => $legacyName,
                        'value' => $legacyValue,
                        'status' => $legacyStatusValue,
                        'created_at' => $now->format('Y-m-d H:i:s'),
                    ]);

                DB::transaction(function () use ($legacyVacancyId, $legacyValue, $name, $status, $legacyStatusValue, $now): void {
                    JobVacancy::query()->create([
                        'legacy_vacancy_id' => $legacyVacancyId,
                        'legacy_value' => $legacyValue,
                        'name' => $name,
                        'status' => $status,
                        'legacy_status_value' => $legacyStatusValue,
                        'legacy_created_at' => $now,
                    ]);
                });
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'Lowongan gagal ditambahkan. Pastikan nama belum ada dan database legacy tersedia.',
                ]);
        }

        return redirect()->route('applicant.job_vacancies')->with('status', 'Lowongan berhasil ditambahkan.');
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
