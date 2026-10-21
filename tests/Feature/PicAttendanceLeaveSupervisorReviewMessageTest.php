<?php

namespace Tests\Feature;

use App\Http\Controllers\PicAttendance\PicAttendanceLeaveController;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeePicAssignment;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PicAttendanceLeaveSupervisorReviewMessageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function supervisorDecisions(): array
    {
        return [
            'approved' => ['approved', 'Pengajuan cuti disetujui dan diteruskan ke HR untuk verifikasi.'],
            'rejected' => ['rejected', 'Pengajuan cuti telah ditolak.'],
        ];
    }

    #[DataProvider('supervisorDecisions')]
    public function test_supervisor_review_flashes_clear_decision_message(string $decision, string $expectedMessage): void
    {
        $company = Company::query()->create(['name' => 'RNB']);
        $picUser = $this->createEmployeeUser($company);
        $staffEmployee = $this->createEmployeeUser($company)->employee;

        EmployeePicAssignment::query()->create([
            'id' => (string) Str::uuid(),
            'supervisor_employee_id' => $picUser->employee->id,
            'staff_employee_id' => $staffEmployee->id,
            'is_active' => true,
        ]);

        $leaveRequest = $this->createPendingLeaveRequest($staffEmployee);

        $request = Request::create('/pic-attendance/leave/detail/'.$leaveRequest->id.'/supervisor-review', 'PUT', ['status' => $decision]);
        $request->setUserResolver(static fn (): User => $picUser);

        $response = app(PicAttendanceLeaveController::class)->updateSupervisorReview($request, $leaveRequest->id);

        $this->assertSame($expectedMessage, $response->getSession()->get('success'));
        $this->assertSame(
            $decision,
            LeaveRequestHistory::query()
                ->where('leave_request_id', $leaveRequest->id)
                ->where('event_type', 'supervisor_review')
                ->value('to_status'),
        );
    }

    private function createEmployeeUser(Company $company): User
    {
        $user = User::query()->create([
            'username' => 'user_'.uniqid(),
            'email' => uniqid('user_', true).'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => $company->id,
            'join_date' => '2026-01-01',
            'status' => 'Active',
        ]);

        return $user->load('employee');
    }

    private function createPendingLeaveRequest(Employee $staffEmployee): LeaveRequest
    {
        $leaveType = LeaveType::query()->create([
            'code' => 'SICK',
            'name' => 'Sakit',
            'accrual_method' => 'yearly',
            'monthly_accrual_rate' => 0,
            'is_encashable' => false,
            'is_active' => true,
        ]);

        $leaveRequest = LeaveRequest::query()->create([
            'employee_id' => $staffEmployee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-10-21',
            'end_date' => '2026-10-21',
            'total_days' => 1,
            'reason' => 'Supervisor review message test.',
            'is_active' => true,
            'status' => 'pending',
        ]);

        LeaveRequestHistory::query()->create([
            'leave_request_id' => $leaveRequest->id,
            'event_type' => 'supervisor_review',
            'title' => 'Supervisor Review',
            'from_status' => 'pending',
            'to_status' => 'pending',
            'happened_at' => now('Asia/Jakarta'),
        ]);

        return $leaveRequest;
    }
}
