<?php

namespace Tests\Feature;

use App\Http\Controllers\PicAttendance\PicAttendanceController;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeProfile;
use Illuminate\Support\Carbon;
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

    public function test_clock_in_at_office_start_boundary_is_presented_on_time_even_when_status_is_late(): void
    {
        $controller = app(PicAttendanceController::class);
        $method = new ReflectionMethod(PicAttendanceController::class, 'recapAttendanceRow');
        $employee = new Employee;
        $employee->forceFill(['id' => 'pic-employee-boundary-attendance']);
        $employee->setRelation('profile', new EmployeeProfile(['name' => 'Staff Tepat Waktu']));
        $attendance = new Attendance;
        $attendance->forceFill([
            'id' => 'pic-attendance-boundary',
            'clock_in' => Carbon::create(2026, 7, 7, 8, 0, 0, 'Asia/Jakarta'),
            'clock_out' => null,
            'late_minutes' => 0,
            'status' => 'Terlambat',
        ]);
        $attendance->setRelation('employee', $employee);

        $row = $method->invoke($controller, $attendance, null, null);

        $this->assertSame('08:00', $row['clock_in']);
        $this->assertSame('text-success', $row['clock_in_class']);
        $this->assertSame('success', $row['clock_in_badge']);
        $this->assertSame('On Time', $row['note']);
        $this->assertSame('success', $row['attachment_badge']);
        $this->assertSame('text-success', $row['attendance_status_class']);
    }

    public function test_clock_in_after_office_start_boundary_is_presented_late(): void
    {
        $controller = app(PicAttendanceController::class);
        $method = new ReflectionMethod(PicAttendanceController::class, 'recapAttendanceRow');
        $employee = new Employee;
        $employee->forceFill(['id' => 'pic-employee-late-attendance']);
        $employee->setRelation('profile', new EmployeeProfile(['name' => 'Staff Telat']));
        $attendance = new Attendance;
        $attendance->forceFill([
            'id' => 'pic-attendance-late',
            'clock_in' => Carbon::create(2026, 7, 7, 8, 1, 0, 'Asia/Jakarta'),
            'clock_out' => null,
            'late_minutes' => 1,
            'status' => 'Terlambat',
        ]);
        $attendance->setRelation('employee', $employee);

        $row = $method->invoke($controller, $attendance, null, null);

        $this->assertSame('08:01', $row['clock_in']);
        $this->assertSame('text-danger', $row['clock_in_class']);
        $this->assertSame('danger', $row['clock_in_badge']);
        $this->assertSame('Late 1 Minute', $row['note']);
        $this->assertSame('danger', $row['attachment_badge']);
        $this->assertSame('text-danger', $row['attendance_status_class']);
    }

    public function test_full_day_attendance_is_presented_as_effective_working_hours(): void
    {
        $controller = app(PicAttendanceController::class);
        $method = new ReflectionMethod(PicAttendanceController::class, 'recapAttendanceRow');
        $employee = new Employee;
        $employee->forceFill(['id' => 'pic-employee-effective-work-hours']);
        $employee->setRelation('profile', new EmployeeProfile(['name' => 'Staff Jam Efektif']));
        $attendance = new Attendance;
        $attendance->forceFill([
            'id' => 'pic-attendance-effective-work-hours',
            'clock_in' => Carbon::create(2026, 7, 7, 8, 0, 0, 'Asia/Jakarta'),
            'clock_out' => Carbon::create(2026, 7, 7, 17, 0, 0, 'Asia/Jakarta'),
            'late_minutes' => 0,
            'work_hours' => 9,
            'status' => 'Masuk',
        ]);
        $attendance->setRelation('employee', $employee);

        $row = $method->invoke($controller, $attendance, null, null);

        $this->assertSame('8 hours', $row['working_hours']);
    }
}
