<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAttendance\AttendanceRecapController;
use App\Models\Employee;
use App\Models\EmployeeProfile;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;

class AdminAttendanceDailyLogRowsTest extends TestCase
{
    public function test_staff_without_daily_attendance_is_presented_with_dashes(): void
    {
        $employee = new Employee;
        $employee->forceFill(['id' => 'admin-employee-without-attendance']);
        $employee->setRelation('profile', new EmployeeProfile(['name' => 'Staff Belum Absen']));

        $method = new ReflectionMethod(AttendanceRecapController::class, 'recapEmptyAttendanceRow');
        $row = $method->invoke(app(AttendanceRecapController::class), $employee);

        $this->assertSame('Staff Belum Absen', $row['name']);
        $this->assertFalse($row['has_detail']);

        foreach (['clock_in', 'clock_out', 'note', 'location_address', 'working_hours'] as $field) {
            $this->assertSame('-', $row[$field]);
        }
    }

    public function test_admin_daily_logs_are_built_from_all_active_staff_and_hide_empty_attachment_action(): void
    {
        $controller = File::get(app_path('Http/Controllers/AdminAttendance/AttendanceRecapController.php'));
        $view = File::get(resource_path('views/admin_attendance/recap_attendance/index.blade.php'));

        $this->assertStringContainsString("->whereIn('id', \$activeEmployeeIds)", $controller);
        $this->assertStringContainsString('return $this->recapEmptyAttendanceRow($employee);', $controller);
        $this->assertStringContainsString("\$attendance->setRelation('employee', \$employee)", $controller);
        $this->assertStringContainsString("\$leaveRequest->setRelation('employee', \$employee)", $controller);
        $this->assertStringContainsString("@if (\$row['has_detail'])", $view);
        $this->assertStringContainsString('<span>-</span>', $view);
    }

    public function test_admin_attendance_details_exclude_administrator_accounts(): void
    {
        $controller = File::get(app_path('Http/Controllers/AdminAttendance/AttendanceRecapController.php'));

        $this->assertStringContainsString("->whereDoesntHave('roles'", $controller);
        $this->assertStringContainsString("->where('name', 'superuser')", $controller);
        $this->assertStringContainsString("->whereNotIn('email', self::EXCLUDED_ATTENDANCE_DETAIL_EMAILS)", $controller);

        foreach ([
            'lukman@rnbmanagement.com',
            'rully.priyatno@andalanbersama.com',
            'hilmi.ulwan@andalanbersama.com',
        ] as $email) {
            $this->assertStringContainsString("'{$email}'", $controller);
        }
    }
}
