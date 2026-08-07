<?php

namespace Tests\Feature;

use App\Http\Controllers\TalentAcquisitionController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TalentAcquisitionStructureTest extends TestCase
{
    public function test_talent_acquisition_routes_use_controller(): void
    {
        $applicantsRoute = Route::getRoutes()->getByName('applicant');
        $jobVacanciesRoute = Route::getRoutes()->getByName('applicant.job_vacancies');
        $updateJobVacancyStatusRoute = Route::getRoutes()->getByName('applicant.job_vacancies.status.update');
        $showApplicantRoute = Route::getRoutes()->getByName('applicant.show');
        $updateStatusRoute = Route::getRoutes()->getByName('applicant.status.update');
        $destroyApplicantRoute = Route::getRoutes()->getByName('applicant.destroy');

        $this->assertNotNull($applicantsRoute);
        $this->assertSame('applicant', $applicantsRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@applicants', $applicantsRoute?->getActionName());

        $this->assertNotNull($jobVacanciesRoute);
        $this->assertSame('applicant/job-vacancies', $jobVacanciesRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@jobVacancies', $jobVacanciesRoute?->getActionName());

        $this->assertNotNull($updateJobVacancyStatusRoute);
        $this->assertSame('applicant/job-vacancies/{jobVacancy}/status', $updateJobVacancyStatusRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@updateJobVacancyStatus', $updateJobVacancyStatusRoute?->getActionName());

        $this->assertNotNull($showApplicantRoute);
        $this->assertSame('applicant/{applicant}', $showApplicantRoute?->uri());
        $this->assertSame(TalentAcquisitionController::class.'@showApplicant', $showApplicantRoute?->getActionName());

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
        $jobVacanciesView = File::get(resource_path('views/applicant_data/job_vancancies.blade.php'));
        $databaseConfig = File::get(config_path('database.php'));

        $this->assertStringContainsString('Talent Acquisition', $sidebar);
        $this->assertStringContainsString('Applicants', $sidebar);
        $this->assertStringContainsString('Job Vacancies', $sidebar);
        $this->assertStringContainsString("canViewSidebarMenu('view-talent-acquisition')", $sidebar);
        $this->assertStringContainsString("route('applicant')", $sidebar);
        $this->assertStringContainsString("route('applicant.job_vacancies')", $sidebar);
        $this->assertStringContainsString('position.permission:view-talent-acquisition', $routes);
        $this->assertStringContainsString('LegacyApplicantSyncService', $controller);
        $this->assertStringContainsString('updateApplicantStatus', $controller);
        $this->assertStringContainsString('destroyApplicant', $controller);
        $this->assertStringContainsString('updateJobVacancyStatus', $controller);
        $this->assertStringContainsString("DB::connection('legacy_mysql')", $controller);
        $this->assertStringContainsString("->table('opt_applicants_vacancies')", $controller);
        $this->assertStringContainsString('JobVacancy::statusOptions()', $controller);
        $this->assertStringContainsString('JobVacancy::legacyStatusValueFor', $controller);
        $this->assertStringContainsString("'jobVacancy:id,name'", $controller);
        $this->assertStringContainsString("'applicant_status_id'", $controller);
        $this->assertStringContainsString("->latest('legacy_created_at')", $controller);
        $this->assertStringContainsString("->latest('legacy_applicant_id')", $controller);
        $this->assertStringContainsString("'educations:id,applicant_id,education_level_id,sequence,institution,gpa,department,start_period,graduate_period'", $controller);
        $this->assertStringContainsString("'educations.educationLevel:id,name'", $controller);
        $this->assertStringContainsString("'workExperiences:id,applicant_id,sequence,company_name,role,company_location,start_period,end_period'", $controller);
        $this->assertStringContainsString("withCount('applicants')", $controller);
        $this->assertStringContainsString("max('legacy_applicant_id')", File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringContainsString("->where('id', '>', \$lastSyncedLegacyApplicantId)", File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringContainsString('syncExistingApplicantStatuses', File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringContainsString("'applicant_status_id' => \$statusIdsByValue->get(\$statusValue)", File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringContainsString("'gender_id' => \$this->genderIdFor(\$legacyApplicant->gender)", File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringContainsString("'marital_status_id' => \$this->maritalStatusIdFor(\$legacyApplicant->marital_status)", File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringContainsString('LegacyEducationLevel', File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringContainsString("'education_level_id' => \$educationLevelId", File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringContainsString("'sequence' => \$index + 1", File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringNotContainsString("'gender' => \$this->normalizeNullableText(\$legacyApplicant->gender)", File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringNotContainsString("'marital_status' => \$this->normalizeNullableText(\$legacyApplicant->marital_status)", File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringNotContainsString("'legacy_status_value' => \$this->integerOrNull(\$legacyApplicant->nb)", File::get(app_path('Services/Applicants/LegacyApplicantSyncService.php')));
        $this->assertStringContainsString("'legacy_mysql'", $databaseConfig);
        $this->assertStringContainsString("env('LEGACY_DB_DATABASE', 'andalan_bersama_lama')", $databaseConfig);
        $this->assertStringContainsString('@forelse ($applicants as $applicant)', $applicantsView);
        $this->assertStringContainsString('<th class="mw-80">No</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-100">Photo</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-220">Nama Lengkap</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-220">Posisi Dilamar</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-420">Keterangan</th>', $applicantsView);
        $this->assertStringContainsString('<th class="mw-120">Action</th>', $applicantsView);
        $this->assertStringContainsString('$applicant->full_name', $applicantsView);
        $this->assertStringContainsString('$applicant->applicant_status_id', $applicantsView);
        $this->assertStringContainsString('order: []', $applicantsView);
        $this->assertStringContainsString('targets: [0, 1, 5]', $applicantsView);
        $this->assertStringContainsString('pageInfo.start + index + 1', $applicantsView);
        $this->assertStringContainsString('talent-status-select', $applicantsView);
        $this->assertStringContainsString('status-value-{{ $applicantStatuses->firstWhere', $applicantsView);
        $this->assertStringContainsString('.talent-status-select.status-value-0', $applicantsView);
        $this->assertStringContainsString('.talent-status-select.status-value-1', $applicantsView);
        $this->assertStringContainsString('.talent-status-select.status-value-2', $applicantsView);
        $this->assertStringContainsString('updateApplicantStatusColor', $applicantsView);
        $this->assertStringContainsString('#applicantsTable_wrapper .dt-layout-row:first-child', $applicantsView);
        $this->assertStringContainsString('#applicantsTable_wrapper .dt-search input', $applicantsView);
        $this->assertStringContainsString('#applicantsTable_wrapper .dt-length select', $applicantsView);
        $this->assertStringContainsString("route('applicant.status.update'", $applicantsView);
        $this->assertStringContainsString("route('applicant.show'", $applicantsView);
        $this->assertStringContainsString("route('applicant.destroy'", $applicantsView);
        $this->assertStringContainsString("@method('DELETE')", $applicantsView);
        $this->assertStringNotContainsString('<th class="mw-260">Pendidikan</th>', $applicantsView);
        $this->assertStringNotContainsString('<th class="mw-320">Pengalaman Kerja</th>', $applicantsView);
        $this->assertStringNotContainsString('$applicant->educations', $applicantsView);
        $this->assertStringNotContainsString('$applicant->workExperiences', $applicantsView);
        $this->assertStringNotContainsString('$applicant->gender?->name', $applicantsView);
        $this->assertStringNotContainsString('$applicant->maritalStatus?->name', $applicantsView);
        $this->assertStringContainsString('@forelse ($jobVacancies as $jobVacancy)', $jobVacanciesView);
        $this->assertStringContainsString('$jobVacancy->applicants_count', $jobVacanciesView);
        $this->assertStringContainsString('talent-vacancy-status-select', $jobVacanciesView);
        $this->assertStringContainsString('updateJobVacancyStatusColor', $jobVacanciesView);
        $this->assertStringContainsString("route('applicant.job_vacancies.status.update'", $jobVacanciesView);
        $this->assertStringContainsString('@foreach ($jobVacancyStatuses as $statusValue => $statusLabel)', $jobVacanciesView);
        $this->assertStringNotContainsString('talent-status-badge', $jobVacanciesView);
        $this->assertStringContainsString('Applicant Detail', $applicantDetailView);
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
        $this->assertStringContainsString('Download CV', $applicantDetailView);
        $this->assertStringContainsString('Open Portfolio', $applicantDetailView);
        $this->assertStringContainsString('target="_blank"', $applicantDetailView);
        $this->assertStringContainsString('mailto:{{ $applicant->email }}', $applicantDetailView);
        $this->assertStringContainsString('WhatsApp', $applicantDetailView);
        $this->assertStringNotContainsString('<div class="applicant-detail-label">Photo</div>', $applicantDetailView);
        $this->assertStringNotContainsString('<div class="applicant-detail-label">Agreement</div>', $applicantDetailView);
        $this->assertStringNotContainsString('<table class="table table-sm table-striped align-middle">', $applicantDetailView);
    }
}
