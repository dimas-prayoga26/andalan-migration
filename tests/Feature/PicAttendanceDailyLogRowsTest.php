<?php

namespace Tests\Feature;

use App\Http\Controllers\PicAttendance\PicAttendanceController;
use App\Models\Employee;
use App\Models\EmployeeProfile;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;

class PicAttendanceDailyLogRowsTest extends TestCase
{
    public function test_staff_without_daily_attendance_is_presented_with_dashes(): void
    {
        $employee = new Employee;
        $employee->forceFill(['id' => 'employee-without-attendance']);
        $employee->setRelation('profile', new EmployeeProfile(['name' => 'Staff Belum Absen']));

        $method = new ReflectionMethod(PicAttendanceController::class, 'recapEmptyAttendanceRow');
        $row = $method->invoke(app(PicAttendanceController::class), $employee);

        $this->assertSame('Staff Belum Absen', $row['name']);
        $this->assertFalse($row['has_detail']);

        foreach (['clock_in', 'clock_out', 'note', 'working_hours'] as $field) {
            $this->assertSame('-', $row[$field]);
        }
    }

    public function test_daily_logs_are_built_from_all_active_staff_and_hide_empty_attachment_action(): void
    {
        $controller = File::get(app_path('Http/Controllers/PicAttendance/PicAttendanceController.php'));
        $view = File::get(resource_path('views/pic_attendance/attendance/index.blade.php'));

        $this->assertStringContainsString("->whereIn('id', \$activeEmployeeIds)", $controller);
        $this->assertStringContainsString('return $this->recapEmptyAttendanceRow($employee);', $controller);
        $this->assertStringContainsString("\$attendance->setRelation('employee', \$employee)", $controller);
        $this->assertStringContainsString("\$leaveRequest->setRelation('employee', \$employee)", $controller);
        $this->assertStringContainsString("@if (\$row['has_detail'])", $view);
        $this->assertStringContainsString('<span>-</span>', $view);
    }
}
