<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAttendance\AttendanceRecapController;
use App\Models\Attendance;
use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\EmployeeProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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

    public function test_clock_in_at_office_start_boundary_is_presented_on_time_even_when_status_is_late(): void
    {
        $controller = app(AttendanceRecapController::class);
        $method = new ReflectionMethod(AttendanceRecapController::class, 'recapAttendanceRow');
        $employee = new Employee;
        $employee->forceFill(['id' => 'admin-employee-boundary-attendance']);
        $employee->setRelation('profile', new EmployeeProfile(['name' => 'Staff Tepat Waktu']));
        $attendance = new Attendance;
        $attendance->forceFill([
            'id' => 'admin-attendance-boundary',
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
        $controller = app(AttendanceRecapController::class);
        $method = new ReflectionMethod(AttendanceRecapController::class, 'recapAttendanceRow');
        $employee = new Employee;
        $employee->forceFill(['id' => 'admin-employee-late-attendance']);
        $employee->setRelation('profile', new EmployeeProfile(['name' => 'Staff Telat']));
        $attendance = new Attendance;
        $attendance->forceFill([
            'id' => 'admin-attendance-late',
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
        $controller = app(AttendanceRecapController::class);
        $method = new ReflectionMethod(AttendanceRecapController::class, 'recapAttendanceRow');
        $employee = new Employee;
        $employee->forceFill(['id' => 'admin-employee-effective-work-hours']);
        $employee->setRelation('profile', new EmployeeProfile(['name' => 'Staff Jam Efektif']));
        $attendance = new Attendance;
        $attendance->forceFill([
            'id' => 'admin-attendance-effective-work-hours',
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

    public function test_approved_overtime_minutes_are_calculated_from_approved_times_across_midnight(): void
    {
        $controller = app(AttendanceRecapController::class);
        $method = new ReflectionMethod(AttendanceRecapController::class, 'recapApprovedOvertimeMinutes');
        $overtime = new AttendanceOvertime;
        $overtime->forceFill([
            'overtime_date' => '2026-07-21',
            'approved_start_time' => '17:08:00',
            'approved_end_time' => '02:58:00',
            'calculated_hours' => 1,
        ]);

        $this->assertSame(590, $method->invoke($controller, $overtime));
    }

    public function test_admin_attendance_details_exclude_administrator_accounts(): void
    {
        $controller = File::get(app_path('Http/Controllers/AdminAttendance/AttendanceRecapController.php'));

        $this->assertStringContainsString("->whereDoesntHave('roles'", $controller);
        $this->assertStringContainsString("->where('name', 'superuser')", $controller);
        $this->assertStringContainsString("->whereNotIn('email', self::EXCLUDED_ATTENDANCE_DETAIL_EMAILS)", $controller);
        $this->assertStringContainsString("->whereRaw('LOWER(COALESCE(workplace, \"\")) <> ?', ['rnb jakarta'])", $controller);

        foreach ([
            'lukman@rnbmanagement.com',
            'rully.priyatno@andalanbersama.com',
        ] as $email) {
            $this->assertStringContainsString("'{$email}'", $controller);
        }

        $this->assertStringNotContainsString("'hilmi.ulwan@andalanbersama.com'", $controller);
    }

    public function test_admin_attendance_employee_avatar_uses_default_and_remote_profile_urls(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile-pictures/admin-detail/avatar.jpg', 'avatar');

        $controller = app(AttendanceRecapController::class);
        $method = new ReflectionMethod(AttendanceRecapController::class, 'employeeAvatarUrl');

        $this->assertSame(asset('assets/default_user.jpg'), $method->invoke($controller, null));
        $this->assertSame(asset('assets/default_user.jpg'), $method->invoke($controller, 'missing/profile.jpg'));
        $this->assertSame(asset('storage/profile-pictures/admin-detail/avatar.jpg'), $method->invoke($controller, 'profile-pictures/admin-detail/avatar.jpg'));
        $this->assertSame('https://example.test/profile.jpg', $method->invoke($controller, 'https://example.test/profile.jpg'));
    }
}
