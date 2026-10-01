<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeaveRequestDestroyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is not available.');
        }

        parent::setUp();
    }

    public function test_super_administrator_can_delete_leave_request(): void
    {
        $superuser = $this->createSuperAdministratorUser();
        $leaveRequest = LeaveRequest::query()->create([
            'employee_id' => null,
            'leave_type_id' => null,
            'start_date' => now('Asia/Jakarta')->toDateString(),
            'end_date' => now('Asia/Jakarta')->toDateString(),
            'total_days' => 1,
            'reason' => 'Delete request as super administrator',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($superuser)->deleteJson(route('attendance.leave-requests.destroy', $leaveRequest));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Data izin berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('leave_requests', [
            'id' => $leaveRequest->id,
        ]);
    }

    public function test_non_superuser_role_cannot_delete_leave_request(): void
    {
        $staffUser = $this->createUserWithRole('staff');
        $leaveRequest = LeaveRequest::query()->create([
            'employee_id' => null,
            'leave_type_id' => null,
            'start_date' => now('Asia/Jakarta')->toDateString(),
            'end_date' => now('Asia/Jakarta')->toDateString(),
            'total_days' => 1,
            'reason' => 'Delete request as staff',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($staffUser)->deleteJson(route('attendance.leave-requests.destroy', $leaveRequest));

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus data izin ini.',
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
        ]);
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $user = User::query()->create([
            'email' => strtolower($roleName).'.'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }

    private function createSuperAdministratorUser(): User
    {
        $role = Role::query()->firstOrCreate([
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);
        $position = Position::query()->create([
            'name' => 'Super Administrator',
        ]);

        $user = User::query()->create([
            'email' => 'superadmin.'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole($role);

        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);
        EmployeeProfile::query()->create([
            'employee_id' => $employee->id,
            'name' => 'Super Administrator',
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_position_id' => $position->id,
            'status' => 'Active',
        ]);

        return $user;
    }
}
