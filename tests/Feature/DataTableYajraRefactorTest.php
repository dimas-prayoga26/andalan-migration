<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DataTableYajraRefactorTest extends TestCase
{
    #[Test]
    public function non_setting_datatable_controllers_use_yajra_collection_responses(): void
    {
        $controllerPaths = [
            app_path('Http/Controllers/EmployeeDataController.php'),
            app_path('Http/Controllers/TalentAcquisitionController.php'),
            app_path('Http/Controllers/HrMeetingController.php'),
            app_path('Http/Controllers/AdminAttendance/AttendanceRecapController.php'),
            app_path('Http/Controllers/AdminAttendance/AttendanceLeaveController.php'),
            app_path('Http/Controllers/PicAttendance/PicAttendanceController.php'),
            app_path('Http/Controllers/PicAttendance/PicAttendanceLeaveController.php'),
            app_path('Http/Controllers/DirectorAttendance/DirectorAttendanceTaskController.php'),
            app_path('Http/Controllers/PicAttendance/PicAttendanceTaskController.php'),
            app_path('Http/Controllers/StaffAttendance/AttendanceReportController.php'),
            app_path('Http/Controllers/StaffAttendance/AttendanceOvertimeController.php'),
        ];

        foreach ($controllerPaths as $controllerPath) {
            $controller = File::get($controllerPath);

            $this->assertStringContainsString('use Yajra\DataTables\Facades\DataTables;', $controller);
            $this->assertStringContainsString('DataTables::collection', $controller);
            $this->assertStringNotContainsString("response()->json(['data' =>", $controller);
            $this->assertStringNotContainsString("'data' => \$tableRows", $controller);
        }
    }

    #[Test]
    public function non_setting_datatable_views_are_configured_for_server_side_yajra(): void
    {
        $viewPaths = [
            resource_path('views/applicant_data/index.blade.php'),
            resource_path('views/applicant_data/job_vancancies.blade.php'),
            resource_path('views/employee_data/index.blade.php'),
            resource_path('views/employee_data/authorization.blade.php'),
            resource_path('views/meetings/admin/index.blade.php'),
            resource_path('views/admin_attendance/leave/index.blade.php'),
            resource_path('views/pic_attendance/leave/index.blade.php'),
            resource_path('views/admin_attendance/recap_attendance/index.blade.php'),
            resource_path('views/director_attendance/attendance/index.blade.php'),
            resource_path('views/pic_attendance/attendance/index.blade.php'),
            resource_path('views/admin_attendance/recap_attendance/detail-employees.blade.php'),
            resource_path('views/director_attendance/attendance/detail-employees.blade.php'),
            resource_path('views/pic_attendance/attendance/detail-employees.blade.php'),
            resource_path('views/staff_attendance/reports/index.blade.php'),
            resource_path('views/director_attendance/task/index.blade.php'),
            resource_path('views/pic_attendance/task/index.blade.php'),
        ];

        foreach ($viewPaths as $viewPath) {
            $view = File::get($viewPath);

            $this->assertStringContainsString('DataTable({', $view);
            $this->assertStringContainsString('processing: true', $view);
            $this->assertStringContainsString('serverSide: true', $view);
        }
    }

    #[Test]
    public function applicant_filters_are_sent_to_server_for_server_side_tables(): void
    {
        $applicantsView = File::get(resource_path('views/applicant_data/index.blade.php'));
        $jobVacanciesView = File::get(resource_path('views/applicant_data/job_vancancies.blade.php'));
        $controller = File::get(app_path('Http/Controllers/TalentAcquisitionController.php'));

        $this->assertStringContainsString('requestData.status_value = selectedApplicantStatus;', $applicantsView);
        $this->assertStringContainsString("requestData.job_vacancy_name = $('#positionFilter').val();", $applicantsView);
        $this->assertStringContainsString('requestData.company_id = selectedCompanyId;', $jobVacanciesView);
        $this->assertStringNotContainsString('$.fn.dataTable.ext.search.push', $applicantsView);
        $this->assertStringNotContainsString('$.fn.dataTable.ext.search.push', $jobVacanciesView);
        $this->assertStringContainsString("filled('status_value')", $controller);
        $this->assertStringContainsString("filled('job_vacancy_name')", $controller);
        $this->assertStringContainsString("filled('company_id')", $controller);
    }
}
