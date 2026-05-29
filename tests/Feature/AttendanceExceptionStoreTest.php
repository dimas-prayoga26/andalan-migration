<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->postJson(route('absensi.exceptions.store'), [
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
