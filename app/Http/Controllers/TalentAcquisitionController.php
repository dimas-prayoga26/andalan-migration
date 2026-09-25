<?php

namespace App\Http\Controllers;

use App\Mail\ApplicantStatusMail;
use App\Models\Applicant;
use App\Models\ApplicantStatus;
use App\Models\JobVacancy;
use App\Support\CareerBrand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class TalentAcquisitionController extends Controller
{
    public function applicants(): View
    {
        $jobVacancies = JobVacancy::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $applicantStatuses = ApplicantStatus::query()
            ->orderBy('value')
            ->get(['id', 'value', 'name']);

        return view('applicant_data.index', [
            'applicantStatuses' => $applicantStatuses,
            'jobVacancies' => $jobVacancies,
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
                'created_at',
            ])
            ->with([
                'applicantStatus:id,value,name',
                'jobVacancy:id,name',
            ])
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

    public function jobVacancies(): View
    {
        return view('applicant_data.job_vancancies', [
            'jobVacancyStatuses' => JobVacancy::statusOptions(),
        ]);
    }

    public function jobVacanciesDatatable(): JsonResponse
    {
        $jobVacancies = JobVacancy::query()
            ->withCount('applicants')
            ->orderByRaw('CASE WHEN status = 1 THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->get()
            ->map(fn (JobVacancy $jobVacancy): array => [
                'id' => (string) $jobVacancy->id,
                'name' => (string) $jobVacancy->name,
                'status' => (int) $jobVacancy->status,
                'status_css_class' => $jobVacancy->statusCssClass(),
                'applicants_count' => (int) $jobVacancy->applicants_count,
                'created_at' => $jobVacancy->created_at?->format('d M Y H:i') ?? '-',
            ])
            ->values();

        return response()->json([
            'data' => $jobVacancies,
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

    public function showApplicantAssessment(Applicant $applicant): View
    {
        $applicant->load([
            'jobVacancy:id,name',
            'applicantStatus:id,value,name',
        ]);

        return view('applicant_data.assessment', [
            'applicant' => $applicant,
        ]);
    }

    public function updateApplicantStatus(Request $request, Applicant $applicant): RedirectResponse
    {
        $validated = $request->validate([
            'applicant_status_id' => ['required', Rule::exists((new ApplicantStatus)->getTable(), 'id')],
        ]);

        $applicantStatus = ApplicantStatus::query()->findOrFail($validated['applicant_status_id']);

        $applicant->update([
            'applicant_status_id' => $applicantStatus->id,
        ]);

        $applicant->setRelation('applicantStatus', $applicantStatus);

        $mailSent = $this->sendApplicantStatusMail($applicant);

        $response = back()->with('status', 'Status pelamar berhasil diperbarui.');

        if (! $mailSent) {
            return $response->withErrors([
                'mail' => 'Status tersimpan, tapi email gagal dikirim. Periksa kembali username/password SMTP brand.',
            ]);
        }

        return $response;
    }

    private function sendApplicantStatusMail(Applicant $applicant): bool
    {
        if (! filled($applicant->email)) {
            return true;
        }

        $brand = CareerBrand::brand($applicant->brand_key);

        try {
            Mail::mailer($this->mailerForBrand($brand))
                ->to($applicant->email)
                ->send(new ApplicantStatusMail($applicant, $brand));
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $brand
     */
    private function mailerForBrand(array $brand): string
    {
        $brandMailer = (string) ($brand['mailer'] ?? '');

        if (filled($brandMailer) && is_array(config("mail.mailers.{$brandMailer}"))) {
            return $brandMailer;
        }

        $defaultMailer = (string) config('mail.default', 'smtp');

        if (filled($defaultMailer) && is_array(config("mail.mailers.{$defaultMailer}"))) {
            return $defaultMailer;
        }

        return 'smtp';
    }

    public function updateJobVacancyStatus(Request $request, JobVacancy $jobVacancy): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'integer', Rule::in(JobVacancy::statuses())],
        ]);

        $status = JobVacancy::statusValueFor((int) $validated['status']);

        if ($status === JobVacancy::STATUS_ACTIVE && ! $jobVacancy->technicalCriteria()->exists()) {
            return redirect()
                ->route('applicant.job_vacancies.edit', $jobVacancy)
                ->withErrors(['technical_criteria' => 'Isi kriteria tes teknis terlebih dahulu sebelum lowongan diaktifkan.']);
        }

        $jobVacancy->update([
            'status' => $status,
        ]);

        return back()->with('status', 'Status lowongan berhasil diperbarui.');
    }

    public function createJobVacancy(): View
    {
        return view('applicant_data.job_vacancy_form', [
            'jobVacancy' => new JobVacancy(['status' => JobVacancy::STATUS_INACTIVE]),
            'jobVacancyStatuses' => JobVacancy::statusOptions(),
            'technicalCriteria' => collect([
                ['name' => '', 'weight' => ''],
            ]),
            'formAction' => route('applicant.job_vacancies.store'),
            'formMethod' => 'POST',
            'formTitle' => 'Create Job',
            'submitLabel' => 'Create Job',
        ]);
    }

    public function storeJobVacancy(Request $request): RedirectResponse
    {
        $validated = $this->validateJobVacancy($request);

        DB::transaction(function () use ($validated): void {
            $jobVacancy = JobVacancy::query()->create([
                'name' => $validated['name'],
                'status' => JobVacancy::statusValueFor((int) $validated['status']),
            ]);

            $this->syncJobVacancyTechnicalCriteria($jobVacancy, $validated['technical_criteria']);
        });

        return redirect()->route('applicant.job_vacancies')->with('status', 'Lowongan berhasil dibuat.');
    }

    public function editJobVacancy(JobVacancy $jobVacancy): View
    {
        $jobVacancy->load('technicalCriteria');

        return view('applicant_data.job_vacancy_form', [
            'jobVacancy' => $jobVacancy,
            'jobVacancyStatuses' => JobVacancy::statusOptions(),
            'technicalCriteria' => $jobVacancy->technicalCriteria
                ->map(fn ($criterion): array => [
                    'name' => (string) $criterion->name,
                    'weight' => (int) $criterion->weight,
                ]),
            'formAction' => route('applicant.job_vacancies.update', $jobVacancy),
            'formMethod' => 'PATCH',
            'formTitle' => 'Update Job',
            'submitLabel' => 'Update Job',
        ]);
    }

    public function updateJobVacancy(Request $request, JobVacancy $jobVacancy): RedirectResponse
    {
        $validated = $this->validateJobVacancy($request, $jobVacancy);

        DB::transaction(function () use ($jobVacancy, $validated): void {
            $jobVacancy->update([
                'name' => $validated['name'],
                'status' => JobVacancy::statusValueFor((int) $validated['status']),
            ]);

            $this->syncJobVacancyTechnicalCriteria($jobVacancy, $validated['technical_criteria']);
        });

        return redirect()->route('applicant.job_vacancies')->with('status', 'Lowongan berhasil diperbarui.');
    }

    public function destroyJobVacancy(JobVacancy $jobVacancy): RedirectResponse
    {
        $jobVacancy->delete();

        return back()->with('status', 'Lowongan berhasil dihapus.');
    }

    /**
     * @return array{name: string, status: int|string, technical_criteria: array<int, array{name: string, weight: int|string}>}
     */
    private function validateJobVacancy(Request $request, ?JobVacancy $jobVacancy = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique((new JobVacancy)->getTable(), 'name')->ignore($jobVacancy?->getKey()),
            ],
            'status' => ['required', 'integer', Rule::in(JobVacancy::statuses())],
            'technical_criteria' => ['required', 'array', 'min:1'],
            'technical_criteria.*.name' => ['required', 'string', 'max:255'],
            'technical_criteria.*.weight' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $totalWeight = collect($validated['technical_criteria'])->sum(fn (array $criterion): int => (int) $criterion['weight']);

        if ($totalWeight !== 100) {
            throw ValidationException::withMessages([
                'technical_criteria' => 'Total bobot kriteria tes teknis harus tepat 100%.',
            ]);
        }

        return $validated;
    }

    /**
     * @param  array<int, array{name: string, weight: int|string}>  $criteria
     */
    private function syncJobVacancyTechnicalCriteria(JobVacancy $jobVacancy, array $criteria): void
    {
        $jobVacancy->technicalCriteria()->delete();

        foreach (array_values($criteria) as $index => $criterion) {
            $jobVacancy->technicalCriteria()->create([
                'name' => $criterion['name'],
                'weight' => (int) $criterion['weight'],
                'sort_order' => $index,
            ]);
        }
    }

    public function destroyApplicant(Applicant $applicant): RedirectResponse
    {
        $applicant->forceDelete();

        return redirect()->route('applicant')->with('status', 'Data pelamar berhasil dihapus.');
    }
}
