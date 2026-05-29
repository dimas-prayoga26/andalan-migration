<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeaveHistoryYearFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_leave_history_year_options_follow_staff_join_date(): void
    {
        $user = User::query()->create([
            'email' => 'history-options@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-HISTORY-YEAR',
            'status' => 'Active',
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'join_date' => '2024-03-01',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->get(route('absensi.izin'));

        $response->assertOk();
        $response->assertSee('2024');
    }

    public function test_leave_history_can_be_filtered_by_year_query(): void
    {
        $user = User::query()->create([
            'email' => 'history-filter@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-HISTORY-FILTER',
            'status' => 'Active',
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'join_date' => '2024-01-01',
            'status' => 'Active',
        ]);
        LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => null,
            'start_date' => '2025-06-10',
            'end_date' => '2025-06-10',
            'total_days' => 1,
            'reason' => 'History Reason 2025',
            'is_active' => true,
            'status' => 'pending',
        ]);
        LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => null,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-10',
            'total_days' => 1,
            'reason' => 'History Reason 2026',
            'is_active' => true,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('absensi.izin', [
            'history_year' => 2026,
        ]));

        $response->assertOk();
        $response->assertSee('History Reason 2026');
        $response->assertDontSee('History Reason 2025');
    }
}
