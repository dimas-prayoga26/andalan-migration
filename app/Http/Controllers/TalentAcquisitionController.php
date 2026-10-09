<?php

namespace App\Http\Controllers;

use App\Mail\ApplicantStatusMail;
use App\Models\Applicant;
use App\Models\ApplicantAssessment;
use App\Models\ApplicantDocument;
use App\Models\ApplicantStatus;
use App\Models\ApplicantUploadRequest;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\MailAccessAccount;
use App\Models\User;
use App\Services\ApplicantAssessmentScoreService;
use App\Services\ApplicantAssessmentUploadLinkService;
use App\Support\CareerBrand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

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

    public function applicantsDatatable(Request $request): JsonResponse
    {
        $applicants = Applicant::query()
            ->select([
                'id',
                'job_vacancy_id',
                'applicant_status_id',
                'full_name',
                'created_at',
            ])
            ->with([
                'applicantStatus:id,value,name',
                'documents:id,applicant_id,document_type,file_path',
                'jobVacancy:id,name',
            ])
            ->when($request->filled('status_value'), function ($query) use ($request): void {
                $query->whereHas('applicantStatus', function ($statusQuery) use ($request): void {
                    $statusQuery->where('value', $request->integer('status_value'));
                });
            })
            ->when($request->filled('job_vacancy_name'), function ($query) use ($request): void {
                $query->whereHas('jobVacancy', function ($jobVacancyQuery) use ($request): void {
                    $jobVacancyQuery->where('name', (string) $request->query('job_vacancy_name'));
                });
            })
            ->latest('created_at')
            ->get()
            ->map(fn (Applicant $applicant): array => [
                'id' => (string) $applicant->id,
                'full_name' => (string) $applicant->full_name,
                'photo' => basename((string) parse_url($applicant->photoUrl() ?? '', PHP_URL_PATH)),
                'photo_url' => $applicant->photoUrl(),
                'job_vacancy_name' => (string) ($applicant->jobVacancy?->name ?? '-'),
                'applicant_status_id' => (string) ($applicant->applicant_status_id ?? ''),
                'applicant_status_value' => (int) ($applicant->applicantStatus?->value ?? ApplicantStatus::VALUE_SUBMITTED),
            ])
            ->values();

        return DataTables::collection($applicants)->toJson();
    }

    public function jobVacancies(): View
    {
        return view('applicant_data.job_vancancies', [
            'jobVacancyStatuses' => JobVacancy::statusOptions(),
            'companyOptions' => $this->companyOptions(),
        ]);
    }

    public function jobVacanciesDatatable(Request $request): JsonResponse
    {
        $jobVacancies = JobVacancy::query()
            ->with('company:id,name')
            ->withCount('applicants')
            ->when($request->filled('company_id'), function ($query) use ($request): void {
                $query->where('company_id', (string) $request->query('company_id'));
            })
            ->orderByRaw('CASE WHEN status = 1 THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->get()
            ->map(fn (JobVacancy $jobVacancy): array => [
                'id' => (string) $jobVacancy->id,
                'company_id' => (string) ($jobVacancy->company_id ?? ''),
                'name' => (string) $jobVacancy->name,
                'company_name' => (string) ($jobVacancy->company?->name ?? '-'),
                'status' => (int) $jobVacancy->status,
                'status_css_class' => $jobVacancy->statusCssClass(),
                'applicants_count' => (int) $jobVacancy->applicants_count,
                'created_at' => $jobVacancy->created_at?->format('d M Y H:i') ?? '-',
            ])
            ->values();

        return DataTables::collection($jobVacancies)->toJson();
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
            'documents:id,applicant_id,document_type,file_path',
            'workExperiences:id,applicant_id,sequence,company_name,role,company_location,start_period,end_period',
        ]);

        return view('applicant_data.show', [
            'applicant' => $applicant,
        ]);
    }

    public function showApplicantAssessment(
        Applicant $applicant,
        ApplicantAssessmentUploadLinkService $uploadLinkService,
        ApplicantAssessmentScoreService $assessmentScoreService
    ): View {
        $applicant->load([
            'jobVacancy:id,name',
            'jobVacancy.technicalCriteria:id,job_vacancy_id,name,weight,sort_order',
            'applicantStatus:id,value,name',
            'documents' => fn ($query) => $query
                ->select(['id', 'applicant_id', 'document_type', 'file_path', 'original_name', 'mime_type'])
                ->where('document_type', ApplicantDocument::TYPE_ASSESSMENT_TEST),
        ]);

        $assessmentUploadRequest = $uploadLinkService->latestUsableRequest($applicant);
        $assessmentDocument = $applicant->documents
            ->firstWhere('document_type', ApplicantDocument::TYPE_ASSESSMENT_TEST);
        $hrCriteria = $assessmentScoreService->hrCriteriaForView($applicant);
        $technicalCriteria = $assessmentScoreService->technicalCriteriaForView($applicant);
        $userCriteria = $assessmentScoreService->userCriteriaForView($applicant);

        return view('applicant_data.assessment', [
            'applicant' => $applicant,
            'assessmentDocument' => $assessmentDocument,
            'assessmentFileUrl' => $assessmentDocument instanceof ApplicantDocument
                ? $uploadLinkService->assessmentFileUrlFor($applicant)
                : null,
            'assessmentUploadBrand' => CareerBrand::brand($applicant->brand_key),
            'assessmentUploadRequest' => $assessmentUploadRequest,
            'assessmentUploadUrl' => $assessmentUploadRequest instanceof ApplicantUploadRequest
                ? $uploadLinkService->urlFor($applicant)
                : null,
            'hrCriteria' => $hrCriteria,
            'hrInterviewScore' => $assessmentScoreService->scoreForSection(
                $applicant,
                ApplicantAssessment::SECTION_HR_INTERVIEW,
                $assessmentScoreService->calculateTotalScore($hrCriteria)
            ),
            'technicalCriteria' => $technicalCriteria,
            'technicalTestScore' => $assessmentScoreService->scoreForSection(
                $applicant,
                ApplicantAssessment::SECTION_TECHNICAL_TEST,
                $assessmentScoreService->calculateTotalScore($technicalCriteria)
            ),
            'technicalTestNotes' => $assessmentScoreService->notesForSection(
                $applicant,
                ApplicantAssessment::SECTION_TECHNICAL_TEST
            ),
            'userCriteria' => $userCriteria,
            'userInterviewScore' => $assessmentScoreService->scoreForSection(
                $applicant,
                ApplicantAssessment::SECTION_INTERVIEW_USER,
                $assessmentScoreService->calculateTotalScore($userCriteria)
            ),
            'userInterviewNotes' => $assessmentScoreService->notesForSection(
                $applicant,
                ApplicantAssessment::SECTION_INTERVIEW_USER
            ),
        ]);
    }

    public function storeApplicantHrInterviewScore(
        Request $request,
        Applicant $applicant,
        ApplicantAssessmentScoreService $assessmentScoreService
    ): JsonResponse {
        $validated = $request->validate([
            'criterion_key' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $assessment = $assessmentScoreService->storeHrInterviewRating(
            $applicant,
            $validated['criterion_key'],
            (int) $validated['rating'],
            $request->user() instanceof User ? $request->user() : null
        );

        return response()->json([
            'criterion_key' => $validated['criterion_key'],
            'rating' => (int) $validated['rating'],
            'total_score' => (float) $assessment->total_score,
            'display_score' => number_format((float) $assessment->total_score, 0).' / 100',
        ]);
    }

    public function storeApplicantTechnicalTestScore(
        Request $request,
        Applicant $applicant,
        ApplicantAssessmentScoreService $assessmentScoreService
    ): JsonResponse {
        $validated = $request->validate([
            'criterion_key' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $assessment = $assessmentScoreService->storeTechnicalTestRating(
            $applicant,
            $validated['criterion_key'],
            (int) $validated['rating'],
            $request->user() instanceof User ? $request->user() : null
        );

        return response()->json([
            'criterion_key' => $validated['criterion_key'],
            'rating' => (int) $validated['rating'],
            'total_score' => (float) $assessment->total_score,
            'display_score' => number_format((float) $assessment->total_score, 0).' / 100',
        ]);
    }

    public function storeApplicantUserInterviewScore(
        Request $request,
        Applicant $applicant,
        ApplicantAssessmentScoreService $assessmentScoreService
    ): JsonResponse {
        $validated = $request->validate([
            'criterion_key' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $assessment = $assessmentScoreService->storeUserInterviewRating(
            $applicant,
            $validated['criterion_key'],
            (int) $validated['rating'],
            $request->user() instanceof User ? $request->user() : null
        );

        return response()->json([
            'criterion_key' => $validated['criterion_key'],
            'rating' => (int) $validated['rating'],
            'total_score' => (float) $assessment->total_score,
            'display_score' => number_format((float) $assessment->total_score, 0).' / 100',
        ]);
    }

    public function storeApplicantAssessmentNotes(
        Request $request,
        Applicant $applicant,
        string $section,
        ApplicantAssessmentScoreService $assessmentScoreService
    ): JsonResponse {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $assessment = $assessmentScoreService->storeSectionNotes(
            $applicant,
            $section,
            $validated['notes'] ?? null,
            $request->user() instanceof User ? $request->user() : null
        );

        return response()->json([
            'section' => $section,
            'notes' => (string) ($assessment->notes ?? ''),
        ]);
    }

    public function storeApplicantAssessmentUploadRequest(
        Request $request,
        Applicant $applicant,
        ApplicantAssessmentUploadLinkService $uploadLinkService
    ): RedirectResponse {
        $uploadLink = $uploadLinkService->create($applicant, $request->user());

        return back()
            ->with('status', 'Link upload assessment berhasil dibuat.')
            ->with('assessment_upload_url', $uploadLink['url']);
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

        $applicant->loadMissing([
            'jobVacancy:id,company_id,name',
            'jobVacancy.company:id,name,website',
            'jobVacancy.company.applicantNotificationMailAccessAccounts:id,company_id,email,type,is_active',
            'jobVacancy.company.departmentMailAccessAccounts:id,company_id,email,type,is_active',
        ]);

        $brand = $this->brandForApplicantStatusMail($applicant);

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
     * @return array<string, mixed>
     */
    private function brandForApplicantStatusMail(Applicant $applicant): array
    {
        $brand = CareerBrand::brand($applicant->brand_key);
        $departmentMailAccount = $this->applicantNotificationMailAccessAccountForApplicant($applicant);

        if (! $departmentMailAccount instanceof MailAccessAccount) {
            return $brand;
        }

        return [
            ...$this->brandForDepartmentMailAccount($departmentMailAccount, $brand),
            'email' => (string) $departmentMailAccount->email,
        ];
    }

    private function applicantNotificationMailAccessAccountForApplicant(Applicant $applicant): ?MailAccessAccount
    {
        $company = $applicant->jobVacancy?->company;

        if (! $company instanceof Company) {
            return null;
        }

        if ($company->relationLoaded('applicantNotificationMailAccessAccounts')) {
            $applicantMailAccount = $company->applicantNotificationMailAccessAccounts->first();

            if ($applicantMailAccount instanceof MailAccessAccount) {
                return $applicantMailAccount;
            }
        } else {
            $applicantMailAccount = $company->applicantNotificationMailAccessAccounts()->first();

            if ($applicantMailAccount instanceof MailAccessAccount) {
                return $applicantMailAccount;
            }
        }

        if ($company->relationLoaded('departmentMailAccessAccounts')) {
            return $company->departmentMailAccessAccounts
                ->first(fn (MailAccessAccount $mailAccessAccount): bool => str_starts_with((string) $mailAccessAccount->email, 'recruitment@'));
        }

        return $company->departmentMailAccessAccounts()
            ->where('email', 'like', 'recruitment@%')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $fallbackBrand
     * @return array<string, mixed>
     */
    private function brandForDepartmentMailAccount(MailAccessAccount $mailAccessAccount, array $fallbackBrand): array
    {
        $mailDomain = $this->emailDomain((string) $mailAccessAccount->email);

        if ($mailDomain === '') {
            return $fallbackBrand;
        }

        foreach (config('career_brands.brands', []) as $brandKey => $brand) {
            if (is_array($brand) && $this->brandMatchesMailDomain($brand, $mailDomain)) {
                return CareerBrand::brand((string) $brandKey);
            }
        }

        return $fallbackBrand;
    }

    /**
     * @param  array<string, mixed>  $brand
     */
    private function brandMatchesMailDomain(array $brand, string $mailDomain): bool
    {
        return in_array($mailDomain, array_filter([
            $this->emailDomain((string) ($brand['email'] ?? '')),
            $this->websiteDomain((string) ($brand['website'] ?? '')),
        ]), true);
    }

    private function emailDomain(string $email): string
    {
        $parts = explode('@', mb_strtolower(trim($email)));

        if (count($parts) !== 2) {
            return '';
        }

        return preg_replace('/^www\./', '', $parts[1]) ?? '';
    }

    private function websiteDomain(string $website): string
    {
        $website = trim($website);

        if ($website === '') {
            return '';
        }

        if (! str_contains($website, '://')) {
            $website = 'https://'.$website;
        }

        $host = parse_url($website, PHP_URL_HOST);

        return preg_replace('/^www\./', '', mb_strtolower((string) $host)) ?? '';
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
            'companyOptions' => $this->companyOptions(),
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
                'company_id' => $validated['company_id'],
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
            'companyOptions' => $this->companyOptions(),
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
                'company_id' => $validated['company_id'],
                'name' => $validated['name'],
                'status' => JobVacancy::statusValueFor((int) $validated['status']),
            ]);

            if ($validated['technical_criteria'] !== []) {
                $this->syncJobVacancyTechnicalCriteria($jobVacancy, $validated['technical_criteria']);
            }
        });

        return redirect()->route('applicant.job_vacancies')->with('status', 'Lowongan berhasil diperbarui.');
    }

    public function destroyJobVacancy(JobVacancy $jobVacancy): RedirectResponse
    {
        $jobVacancy->delete();

        return back()->with('status', 'Lowongan berhasil dihapus.');
    }

    /**
     * @return array{company_id: string, name: string, status: int|string, technical_criteria: array<int, array{name: string, weight: int|string}>}
     */
    private function validateJobVacancy(Request $request, ?JobVacancy $jobVacancy = null): array
    {
        $companyId = (string) $request->input('company_id', '');
        $isUpdate = $jobVacancy instanceof JobVacancy;

        $validated = $request->validate([
            'company_id' => [
                'required',
                'string',
                Rule::exists((new Company)->getTable(), 'id')->where('is_active', true),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique((new JobVacancy)->getTable(), 'name')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($jobVacancy?->getKey()),
            ],
            'status' => ['required', 'integer', Rule::in(JobVacancy::statuses())],
            'technical_criteria' => [$isUpdate ? 'nullable' : 'required', 'array'],
            'technical_criteria.*' => ['array'],
            'technical_criteria.*.name' => [$isUpdate ? 'nullable' : 'required', 'string', 'max:255'],
            'technical_criteria.*.weight' => [$isUpdate ? 'nullable' : 'required', 'integer', 'min:1', 'max:100'],
        ]);

        $technicalCriteria = $this->filledTechnicalCriteria($validated['technical_criteria'] ?? []);

        if ($technicalCriteria === []) {
            if ($isUpdate) {
                $validated['technical_criteria'] = [];

                return $validated;
            }

            throw ValidationException::withMessages([
                'technical_criteria' => 'Total bobot kriteria tes teknis harus tepat 100%.',
            ]);
        }

        if ($this->hasIncompleteTechnicalCriteria($technicalCriteria)) {
            throw ValidationException::withMessages([
                'technical_criteria' => 'Lengkapi nama dan bobot kriteria tes teknis.',
            ]);
        }

        $totalWeight = collect($technicalCriteria)->sum(fn (array $criterion): int => (int) $criterion['weight']);

        if ($totalWeight !== 100) {
            throw ValidationException::withMessages([
                'technical_criteria' => 'Total bobot kriteria tes teknis harus tepat 100%.',
            ]);
        }

        $validated['technical_criteria'] = $technicalCriteria;

        return $validated;
    }

    /**
     * @param  array<int, array{name?: mixed, weight?: mixed}>  $criteria
     * @return array<int, array{name: string, weight: int|string}>
     */
    private function filledTechnicalCriteria(array $criteria): array
    {
        return collect($criteria)
            ->map(fn (array $criterion): array => [
                'name' => trim((string) ($criterion['name'] ?? '')),
                'weight' => $criterion['weight'] ?? '',
            ])
            ->filter(fn (array $criterion): bool => $criterion['name'] !== '' || trim((string) $criterion['weight']) !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{name: string, weight: int|string}>  $criteria
     */
    private function hasIncompleteTechnicalCriteria(array $criteria): bool
    {
        return collect($criteria)->contains(
            fn (array $criterion): bool => $criterion['name'] === '' || trim((string) $criterion['weight']) === '',
        );
    }

    /**
     * @return Collection<int, array{id: string, name: string}>
     */
    private function companyOptions(): Collection
    {
        return Company::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Company $company): array => [
                'id' => (string) $company->id,
                'name' => (string) $company->name,
            ]);
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
