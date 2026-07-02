<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class EmployeeProfilePictureConsolidationTest extends TestCase
{
    public function test_profile_picture_is_consolidated_into_employee_profiles(): void
    {
        $attendanceProfileHeader = File::get(resource_path('views/staff_attendance/layouts/profile-header.blade.php'));
        $projectProfileHeader = File::get(resource_path('views/project_management/layouts/profile-header.blade.php'));
        $attendanceProfileComposer = File::get(app_path('View/Composers/AttendanceProfileComposer.php'));
        $projectProfileComposer = File::get(app_path('View/Composers/ProjectManagementProfileComposer.php'));
        $employeeProfileMigration = File::get(database_path('migrations/2026_05_12_095327_create_employee_profiles_table.php'));
        $userProfileReferences = File::glob(database_path('migrations/*user_profile*'));

        $this->assertSame([], $userProfileReferences);
        $this->assertStringContainsString("\$table->string('profile_picture_path')->nullable();", $employeeProfileMigration);
        $this->assertFileDoesNotExist(app_path('Models/UserProfile.php'));
        $this->assertStringContainsString("asset('assets/default_user.jpg')", $attendanceProfileHeader);
        $this->assertStringContainsString("asset('assets/default_user.jpg')", $projectProfileHeader);
        $this->assertStringNotContainsString('fa fa-circle border border-3 border-white text-success', $attendanceProfileHeader);
        $this->assertStringNotContainsString('fa fa-circle border border-3 border-white text-success', $projectProfileHeader);
        $this->assertStringContainsString('File::exists(public_path($publicPath)) ? $publicPath : null', $attendanceProfileComposer);
        $this->assertStringContainsString('File::exists(public_path($publicPath)) ? $publicPath : null', $projectProfileComposer);
    }
}
