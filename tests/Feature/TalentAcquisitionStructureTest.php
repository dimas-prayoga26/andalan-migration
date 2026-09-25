<?php

namespace Tests\Feature;

use App\Http\Controllers\TalentAcquisitionController;
use App\Models\ApplicantStatus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TalentAcquisitionStructureTest extends TestCase
{
    public function test_applicant_status_workflow_uses_expected_order(): void
    {
        $this->assertSame([
            ApplicantStatus::VALUE_SUBMITTED => 'Submitted',
            ApplicantStatus::VALUE_HR_INTERVIEW => 'HR Interview',
            ApplicantStatus::VALUE_TECHNICAL_TEST => 'Technical test',
            ApplicantStatus::VALUE_USER_INTERVIEW => 'User Interview',
            ApplicantStatus::VALUE_OFFERING => 'Offering',
            ApplicantStatus::VALUE_NOT_SUITABLE => 'Not Suitable',
        ], ApplicantStatus::defaultStatuses());
    }

    public function test_talent_acquisition_routes_use_controller(): void
    {
        $applicantsRoute = Route::getRoutes()->getByName('applicant');
        $applicantsDatatableRoute = Route::getRoutes()->getByName('applicant.datatable');
        $jobVacanciesRoute = Route::getRoutes()->getByName('applicant.job_vacancies');
        $jobVacanciesDatatableRoute = Route::getRoutes()->getByName('applicant.job_vacancies.datatable');
        $createJobVacancyRoute = Route::getRoutes()->getByName('applicant.job_vacancies.create');
        $storeJobVacancyRoute = Route::getRoutes()->getByName('applicant.job_vacancies.store');
        $updateJobVacancyStatusRoute = Route::getRoutes()->getByName('applicant.job_vacancies.status.update');
        $editJobVacancyRoute = Route::getRoutes()->getByName('applicant.job_vacancies.edit');
        $updateJobVacancyRoute = Route::getRoutes()->getByName('applicant.job_vacancies.update');
        $destroyJobVacancyRoute = Route::getRoutes()->getByName('applicant.job_vacancies.destroy');
        $showApplicantRoute = Route::getRoutes()->getByName('applicant.show');
        $showApplicantAssessmentRoute = Route::getRoutes()->getByName('applicant.assessment');
        $updateStatusRoute = Route::getRoutes()->getByName('applicant.status.update');
        $destroyApplicantRoute = Route::getRoutes()->getByName('applicant.destroy');

        $this->assertNotNull($applicantsRoute);
        $this->assertSame('applicant', $applicantsRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@applicants', $applicantsRoute?->getActionName());

        $this->assertNotNull($applicantsDatatableRoute);
        $this->assertSame('applicant/datatable', $applicantsDatatableRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@applicantsDatatable', $applicantsDatatableRoute?->getActionName());

        $this->assertNotNull($jobVacanciesRoute);
        $this->assertSame('applicant/job-vacancies', $jobVacanciesRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@jobVacancies', $jobVacanciesRoute?->getActionName());

        $this->assertNotNull($jobVacanciesDatatableRoute);
        $this->assertSame('applicant/job-vacancies/datatable', $jobVacanciesDatatableRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@jobVacanciesDatatable', $jobVacanciesDatatableRoute?->getActionName());

        $this->assertNotNull($createJobVacancyRoute);
        $this->assertSame('applicant/job-vacancies/create', $createJobVacancyRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@createJobVacancy', $createJobVacancyRoute?->getActionName());

        $this->assertNotNull($storeJobVacancyRoute);
        $this->assertSame('applicant/job-vacancies', $storeJobVacancyRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@storeJobVacancy', $storeJobVacancyRoute?->getActionName());

        $this->assertNotNull($updateJobVacancyStatusRoute);
        $this->assertSame('applicant/job-vacancies/{jobVacancy}/status', $updateJobVacancyStatusRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@updateJobVacancyStatus', $updateJobVacancyStatusRoute?->getActionName());

        $this->assertNotNull($editJobVacancyRoute);
        $this->assertSame('applicant/job-vacancies/{jobVacancy}/edit', $editJobVacancyRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@editJobVacancy', $editJobVacancyRoute?->getActionName());

        $this->assertNotNull($updateJobVacancyRoute);
        $this->assertSame('applicant/job-vacancies/{jobVacancy}', $updateJobVacancyRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@updateJobVacancy', $updateJobVacancyRoute?->getActionName());

        $this->assertNotNull($destroyJobVacancyRoute);
        $this->assertSame('applicant/job-vacancies/{jobVacancy}', $destroyJobVacancyRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@destroyJobVacancy', $destroyJobVacancyRoute?->getActionName());

        $this->assertNotNull($showApplicantRoute);
        $this->assertSame('applicant/{applicant}', $showApplicantRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@showApplicant', $showApplicantRoute?->getActionName());

        $this->assertNotNull($showApplicantAssessmentRoute);
        $this->assertSame('applicant/{applicant}/assessment', $showApplicantAssessmentRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@showApplicantAssessment', $showApplicantAssessmentRoute?->getActionName());

        $this->assertNotNull($updateStatusRoute);
        $this->assertSame('applicant/{applicant}/status', $updateStatusRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@updateApplicantStatus', $updateStatusRoute?->getActionName());

        $this->assertNotNull($destroyApplicantRoute);
        $this->assertSame('applicant/{applicant}', $destroyApplicantRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@destroyApplicant', $destroyApplicantRoute?->getActionName());
    }

    public function test_talent_acquisition_menu_and_views_are_registered(): void
    {
        $sidebar = File::get(resource_path('views/layouts/sidebar.blade.php'));
        $routes = File::get(base_path('routes/web.php'));
        $controller = File::get(app_path('Http/Controllers/TalentAcquisitionController.php'));
        $applicantsView = File::get(resource_path('views/applicant_data/index.blade.php'));
        $applicantDetailView = File::get(resource_path('views/applicant_data/show.blade.php'));
        $applicantAssessmentView = File::get(resource_path('views/applicant_data/assessment.blade.php'));
        $jobVacanciesView = File::get(resource_path('views/applicant_data/job_vancancies.blade.php'));
        $jobVacancyFormView = File::get(resource_path('views/applicant_data/job_vacancy_form.blade.php'));
        $jobVacancyModel = File::get(app_path('Models/JobVacancy.php'));
        $jobVacancyTechnicalCriterionModel = File::get(app_path('Models/JobVacancyTechnicalCriterion.php'));
        $jobVacancyCriteriaMigration = File::get(database_path('migrations/2026_09_24_000002_create_job_vacancy_technical_criteria_table.php'));
        $applicantStatusModel = File::get(app_path('Models/ApplicantStatus.php'));
        $applicantStatusWorkflowMigration = File::get(database_path('migrations/2026_09_14_142825_update_applicant_statuses_to_full_workflow.php'));

        $this->assertStringContainsString('Talent Acquisition', $sidebar);
        $this->assertStringContainsString('Applicants', $sidebar);
        $this->assertStringContainsString('Job Vacancies', $sidebar);
        $this->assertStringContainsString('Email', $sidebar);
        $this->assertStringContainsString("canViewSidebarMenu('view-talent-acquisition')", $sidebar);
        $this->assertStringContainsString("route('applicant')", $sidebar);
        $this->assertStringContainsString("route('applicant.job_vacancies')", $sidebar);
        $this->assertStringContainsString("route('applicant.email.index')", $sidebar);
        $this->assertStringContainsString('position.permission:view-talent-acquisition', $routes);
        $this->assertStringContainsString('applicantsDatatable', $controller);
        $this->assertStringContainsString('jobVacanciesDatatable', $controller);
        $this->assertStringContainsString('updateApplicantStatus', $controller);
        $this->assertStringContainsString('showApplicantAssessment', $controller);
        $this->assertStringContainsString('destroyApplicant', $controller);
        $this->assertStringContainsString('updateJobVacancyStatus', $controller);
        $this->assertStringContainsString('createJobVacancy', $controller);
        $this->assertStringContainsString('storeJobVacancy', $controller);
        $this->assertStringContainsString('editJobVacancy', $controller);
        $this->assertStringContainsString('updateJobVacancy', $controller);
        $this->assertStringContainsString('destroyJobVacancy', $controller);
        $this->assertStringContainsString('validateJobVacancy', $controller);
        $this->assertStringContainsString('syncJobVacancyTechnicalCriteria', $controller);
        $this->assertStringContainsString('Total bobot kriteria tes teknis harus tepat 100%.', $controller);
        $this->assertStringContainsString('JobVacancy::statusOptions()', $controller);
        $this->assertStringContainsString('JobVacancy::statusValueFor', $controller);
        $this->assertStringContainsString("'jobVacancy:id,name'", $controller);
        $this->assertStringContainsString("'applicant_status_id'", $controller);
        $this->assertStringContainsString("->latest('created_at')", $controller);
        $this->assertStringContainsString("'educations:id,applicant_id,education_level_id,sequence,institution,gpa,department,start_period,graduate_period'", $controller);
        $this->assertStringContainsString("'educations.educationLevel:id,name'", $controller);
        $this->assertStringContainsString("'workExperiences:id,applicant_id,sequence,company_name,role,company_location,start_period,end_period'", $controller);
        $this->assertStringContainsString("withCount('applicants')", $controller);
        $this->assertStringContainsString('<th class="mw-80">No</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-100">Photo</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-220">Nama Lengkap</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-220">Posisi Dilamar</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-420">Keterangan</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-180">Penilaian</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-120">Action</th>', $applicantsView);
        $this->assertStringContainsString('<tbody></tbody>', $applicantsView);
        $this->assertStringContainsString('.talent-photo img', $applicantsView);
        $this->assertStringContainsString("route('applicant.datatable')", $applicantsView);
        $this->assertStringContainsString("dataSrc: 'data'", $applicantsView);
        $this->assertStringContainsString('columns: [', $applicantsView);
        $this->assertStringContainsString("data: 'full_name'", $applicantsView);
        $this->assertStringContainsString("data: 'job_vacancy_name'", $applicantsView);
        $this->assertStringContainsString('renderApplicantPhoto', $applicantsView);
        $this->assertStringContainsString('renderApplicantStatus', $applicantsView);
        $this->assertStringContainsString('renderAssessmentSummary', $applicantsView);
        $this->assertStringContainsString('renderApplicantAction', $applicantsView);
        $this->assertStringContainsString('order: []', $applicantsView);
        $this->assertStringContainsString('targets: [0, 1, 5, 6]', $applicantsView);
        $this->assertStringContainsString('meta.settings._iDisplayStart', $applicantsView);
        $this->assertStringContainsString('talent-status-select', $applicantsView);
        $this->assertStringContainsString('talent-status-select-shell', $applicantsView);
        $this->assertStringContainsString('appearance: none;', $applicantsView);
        $this->assertStringContainsString('talent-status-tabs', $applicantsView);
        $this->assertStringContainsString('talent-status-tab', $applicantsView);
        $this->assertStringContainsString('data-status-value="{{ $applicantStatus->value }}"', $applicantsView);
        $this->assertStringContainsString('selectedApplicantStatus', $applicantsView);
        $this->assertStringContainsString('$.fn.dataTable.ext.search.push', $applicantsView);
        $this->assertStringContainsString('applicant.applicant_status_value', $applicantsView);
        $this->assertStringContainsString('.talent-status-select.status-value-0', $applicantsView);
        $this->assertStringContainsString('.talent-status-select.status-value-1', $applicantsView);
        $this->assertStringContainsString('.talent-status-select.status-value-2', $applicantsView);
        $this->assertStringContainsString('.talent-status-select.status-value-3', $applicantsView);
        $this->assertStringContainsString('.talent-status-select.status-value-4', $applicantsView);
        $this->assertStringContainsString('.talent-status-select.status-value-5', $applicantsView);
        $this->assertStringContainsString('.talent-assessment-summary', $applicantsView);
        $this->assertStringContainsString('.talent-assessment-badge', $applicantsView);
        $this->assertStringNotContainsString("label: 'Belum Dinilai'", $applicantsView);
        $this->assertStringContainsString("selectElement.classList.remove('status-value-0', 'status-value-1', 'status-value-2', 'status-value-3', 'status-value-4', 'status-value-5')", $applicantsView);
        $this->assertStringContainsString('updateApplicantStatusColor', $applicantsView);
        $this->assertStringContainsString('#applicantsTable_wrapper .dt-layout-row:first-child', $applicantsView);
        $this->assertStringContainsString('#applicantsTable_wrapper .dt-search input', $applicantsView);
        $this->assertStringContainsString('#applicantsTable_wrapper .dt-length select', $applicantsView);
        $this->assertStringContainsString("route('applicant.status.update'", $applicantsView);
        $this->assertStringContainsString("route('applicant.show'", $applicantsView);
        $this->assertStringContainsString("route('applicant.assessment'", $applicantsView);
        $this->assertStringContainsString('talent-assessment-button', $applicantsView);
        $this->assertStringContainsString('bi-clipboard-check', $applicantsView);
        $this->assertStringContainsString('aria-label="Form Penilaian"', $applicantsView);
        $this->assertStringNotContainsString('<span>Form Penilaian</span>', $applicantsView);
        $this->assertStringNotContainsString("note: 'Form HR Interview'", $applicantsView);
        $this->assertStringContainsString("route('applicant.destroy'", $applicantsView);
        $this->assertStringContainsString('assets/vendor/sweetalert2/sweetalert2.min.css', $applicantsView);
        $this->assertStringContainsString("@include('settings.partials.delete-confirmation-swal')", $applicantsView);
        $this->assertStringContainsString('@stack(\'scripts\')', $applicantsView);
        $this->assertStringContainsString('data-delete-confirmation-form', $applicantsView);
        $this->assertStringContainsString('data-delete-confirm-button="Ya, hapus"', $applicantsView);
        $this->assertStringNotContainsString("onsubmit=\"return confirm('Hapus data pelamar ini?')\"", $applicantsView);
        $this->assertStringNotContainsString('js-applicant-delete-form', $applicantsView);
        $this->assertStringNotContainsString('@forelse ($applicants as $applicant)', $applicantsView);
        $this->assertStringNotContainsString('data-photo-src="{{ $photoUrl }}"', $applicantsView);
        $this->assertStringNotContainsString('loadVisibleApplicantPhotos', $applicantsView);
        $this->assertStringNotContainsString('<th class="mw-260">Pendidikan</th>', $applicantsView);
        $this->assertStringNotContainsString('<th class="mw-320">Pengalaman Kerja</th>', $applicantsView);
        $this->assertStringNotContainsString('$applicant->educations', $applicantsView);
        $this->assertStringNotContainsString('$applicant->workExperiences', $applicantsView);
        $this->assertStringNotContainsString('$applicant->gender?->name', $applicantsView);
        $this->assertStringNotContainsString('$applicant->maritalStatus?->name', $applicantsView);
        $this->assertStringContainsString('<tbody></tbody>', $jobVacanciesView);
        $this->assertStringContainsString("route('applicant.job_vacancies.datatable')", $jobVacanciesView);
        $this->assertStringContainsString("route('applicant.job_vacancies.create')", $jobVacanciesView);
        $this->assertStringContainsString("route('applicant.job_vacancies.edit'", $jobVacanciesView);
        $this->assertStringContainsString("route('applicant.job_vacancies.destroy'", $jobVacanciesView);
        $this->assertStringContainsString("data: 'applicants_count'", $jobVacanciesView);
        $this->assertStringContainsString("data: 'created_at'", $jobVacanciesView);
        $this->assertStringContainsString('Created At', $jobVacanciesView);
        $this->assertStringNotContainsString("data: 'technical_criteria_count'", $jobVacanciesView);
        $this->assertStringNotContainsString('<th class="mw-160">Kriteria</th>', $jobVacanciesView);
        $this->assertStringContainsString('Create Job', $jobVacanciesView);
        $this->assertStringContainsString('talent-create-job-btn', $jobVacanciesView);
        $this->assertStringContainsString('renderJobVacancyAction', $jobVacanciesView);
        $this->assertStringContainsString('data-delete-confirmation-form', $jobVacanciesView);
        $this->assertStringContainsString('talent-vacancy-status-select', $jobVacanciesView);
        $this->assertStringContainsString('talent-vacancy-status-select-shell', $jobVacanciesView);
        $this->assertStringContainsString('renderJobVacancyStatus', $jobVacanciesView);
        $this->assertStringContainsString('updateJobVacancyStatusColor', $jobVacanciesView);
        $this->assertStringContainsString("route('applicant.job_vacancies.status.update'", $jobVacanciesView);
        $this->assertStringContainsString('Kriteria Tes Teknis', $jobVacancyFormView);
        $this->assertStringContainsString('technical_criteria[{{ $index }}][name]', $jobVacancyFormView);
        $this->assertStringContainsString('technical_criteria[{{ $index }}][weight]', $jobVacancyFormView);
        $this->assertStringContainsString('data-add-criterion', $jobVacancyFormView);
        $this->assertStringContainsString('data-criteria-total', $jobVacancyFormView);
        $this->assertStringContainsString('Total bobot wajib tepat 100%', $jobVacancyFormView);
        $this->assertStringContainsString('technicalCriteria', $jobVacancyModel);
        $this->assertStringContainsString('JobVacancyTechnicalCriterion::class', $jobVacancyModel);
        $this->assertStringContainsString('job_vacancy_technical_criteria', $jobVacancyTechnicalCriterionModel);
        $this->assertStringContainsString("Schema::create('job_vacancy_technical_criteria'", $jobVacancyCriteriaMigration);
        $this->assertStringContainsString("foreignUuid('job_vacancy_id')", $jobVacancyCriteriaMigration);
        $this->assertStringContainsString("unsignedTinyInteger('weight')", $jobVacancyCriteriaMigration);
        $this->assertStringNotContainsString('@forelse ($jobVacancies as $jobVacancy)', $jobVacanciesView);
        $this->assertStringNotContainsString('talent-status-badge', $jobVacanciesView);
        $this->assertStringContainsString('Applicant Detail', $applicantDetailView);
        $this->assertStringContainsString('$applicant->photoUrl()', $applicantDetailView);
        $this->assertStringContainsString('$applicant->statusLabel()', $applicantDetailView);
        $this->assertStringContainsString('applicant-cv-photo', $applicantDetailView);
        $this->assertStringContainsString('applicant-cv-list', $applicantDetailView);
        $this->assertStringContainsString('applicant-cv-item', $applicantDetailView);
        $this->assertStringContainsString('applicant-cv-item-heading', $applicantDetailView);
        $this->assertStringContainsString('applicant-cv-item-period', $applicantDetailView);
        $this->assertStringContainsString("\$education->educationLevel?->name ?? 'Education'", $applicantDetailView);
        $this->assertStringContainsString('- {{ $education->institution }}', $applicantDetailView);
        $this->assertStringContainsString("\$education->gpa ? 'GPA '.\$education->gpa : null", $applicantDetailView);
        $this->assertStringContainsString("{{ \$experience->company_name ?? 'Work Experience' }}", $applicantDetailView);
        $this->assertStringContainsString('<strong>{{ $experience->role }}</strong>', $applicantDetailView);
        $this->assertStringContainsString('{{ $experience->company_location ?? \'-\' }}', $applicantDetailView);
        $this->assertStringContainsString('$applicant->educations', $applicantDetailView);
        $this->assertStringContainsString('$applicant->workExperiences', $applicantDetailView);
        $this->assertStringContainsString('$applicant->portfolioLinks()', $applicantDetailView);
        $this->assertStringContainsString('$applicant->whatsAppUrl()', $applicantDetailView);
        $this->assertStringContainsString('$applicant->cvDownloadUrl()', $applicantDetailView);
        $this->assertStringContainsString('applicant-inline-action', $applicantDetailView);
        $this->assertStringContainsString('applicant-link-actions', $applicantDetailView);
        $this->assertStringNotContainsString('Penilaian Interview', $applicantDetailView);
        $this->assertStringNotContainsString('applicant-assessment-overview', $applicantDetailView);
        $this->assertStringNotContainsString('applicant-rating-button', $applicantDetailView);
        $this->assertStringNotContainsString('Form Interview HR', $applicantDetailView);
        $this->assertStringNotContainsString('Final Verdict', $applicantDetailView);
        $this->assertStringContainsString('Download CV', $applicantDetailView);
        $this->assertStringContainsString('Open Portfolio', $applicantDetailView);
        $this->assertStringContainsString('target="_blank"', $applicantDetailView);
        $this->assertStringContainsString('mailto:{{ $applicant->email }}', $applicantDetailView);
        $this->assertStringContainsString('WhatsApp', $applicantDetailView);
        $this->assertStringContainsString('Applicant Assessment', $applicantAssessmentView);
        $this->assertStringContainsString('route(\'applicant.show\', $applicant)', $applicantAssessmentView);
        $this->assertStringContainsString('Form Interview HR', $applicantAssessmentView);
        $this->assertStringContainsString('Assessment Test', $applicantAssessmentView);
        $this->assertStringContainsString('Technical Test', $applicantAssessmentView);
        $this->assertStringContainsString('Interview User', $applicantAssessmentView);
        $this->assertStringContainsString('Final Verdict', $applicantAssessmentView);
        $this->assertStringContainsString('Skill', $applicantAssessmentView);
        $this->assertStringContainsString('Komunikasi', $applicantAssessmentView);
        $this->assertStringContainsString('Kesesuaian Pengalaman', $applicantAssessmentView);
        $this->assertStringContainsString('Tools', $applicantAssessmentView);
        $this->assertStringContainsString('Recommended', $applicantAssessmentView);
        $this->assertStringContainsString('Adobe Illustrator, Photoshop, Figma.', $applicantAssessmentView);
        $this->assertStringNotContainsString('applicant-assessment-stage-grid', $applicantAssessmentView);
        $this->assertStringNotContainsString('<div class="applicant-detail-label">Photo</div>', $applicantDetailView);
        $this->assertStringNotContainsString('<div class="applicant-detail-label">Agreement</div>', $applicantDetailView);
        $this->assertStringNotContainsString('<table class="table table-sm table-striped align-middle">', $applicantDetailView);
        $this->assertStringContainsString("self::VALUE_HR_INTERVIEW => 'HR Interview'", $applicantStatusModel);
        $this->assertStringContainsString("self::VALUE_TECHNICAL_TEST => 'Technical test'", $applicantStatusModel);
        $this->assertStringContainsString("self::VALUE_USER_INTERVIEW => 'User Interview'", $applicantStatusModel);
        $this->assertStringContainsString("self::VALUE_OFFERING => 'Offering'", $applicantStatusModel);
        $this->assertStringContainsString("self::VALUE_NOT_SUITABLE => 'Not Suitable'", $applicantStatusModel);
        $this->assertStringContainsString("4 => 'Offering'", $applicantStatusWorkflowMigration);
        $this->assertStringContainsString('moveAcceptedStatusToOffering', $applicantStatusWorkflowMigration);
    }
}
