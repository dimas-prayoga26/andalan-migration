<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceExceptionStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_early_departure_exception_is_created_with_approved_status(): void
    {
        [$user, $employee] = $this->createAuthenticatedUserWithEmployee('staff_exception_pending');

        $response = $this
            ->actingAs($user)
            ->postJson(route('attendance.exceptions.store'), [
                'type' => 'early_departure',
                'note' => 'Pulang lebih awal untuk keperluan keluarga',
                'from_time' => '12:00',
                'to_time' => '17:00',
                'exception_date' => now('Asia/Jakarta')->toDateString(),
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'exception_type' => 'early_departure',
            ]);

        $this->assertDatabaseHas('attendance_exceptions', [
            'employee_id' => $employee->id,
            'type' => 'early_departure',
            'status' => 'approved',
        ]);
    }

    public function test_early_departure_exception_keeps_late_attendance_status_when_clock_in_was_late(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-18 15:00:00', 'Asia/Jakarta'));

        try {
            [$user, $employee] = $this->createAuthenticatedUserWithEmployee('staff_late_then_early_departure');

            Attendance::query()->create([
                'employee_id' => $employee->id,
                'date' => '2026-06-18',
                'clock_in' => '08:45:00',
                'clock_out' => null,
                'late_minutes' => 45,
                'work_hours' => null,
                'status' => 'Terlambat',
            ]);

            $response = $this
                ->actingAs($user)
                ->postJson(route('attendance.exceptions.store'), [
                    'type' => 'early_departure',
                    'note' => 'Izin pulang lebih awal setelah datang terlambat',
                    'from_time' => '15:00',
                    'to_time' => '17:00',
                    'exception_date' => '2026-06-18',
                ]);

            $response
                ->assertOk()
                ->assertJson([
                    'success' => true,
                    'exception_type' => 'early_departure',
                    'clock_in_label' => '08:45',
                    'clock_out_label' => '15:00',
                    'attendance_status' => 'Terlambat',
                    'has_attendance_exception_today' => true,
                    'has_early_departure_exception' => true,
                ]);

            $this->assertIsArray($response->json('calendar_event'));

            $this->assertDatabaseHas('attendances', [
                'employee_id' => $employee->id,
                'date' => '2026-06-18',
                'clock_in' => '08:45:00',
                'clock_out' => '15:00:00',
                'late_minutes' => 45,
                'status' => 'Terlambat',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function createAuthenticatedUserWithEmployee(string $username): array
    {
        $user = User::query()->create([
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);

        return [$user, $employee];
    }
}
