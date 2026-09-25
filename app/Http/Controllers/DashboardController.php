<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\AttendanceIndexRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeePicAssignment;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use App\Services\Attendance\AttendanceCardsViewDataService;
use App\Services\Attendance\AttendanceMutationService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private AttendanceCardsViewDataService $attendanceCardsViewDataService,
        private AttendanceMutationService $attendanceMutationService
    ) {}

    public function index(AttendanceIndexRequest $request): View
    {
        $authenticatedUser = Auth::user();
        $attendanceCardsData = $this->attendanceCardsViewDataService->build(
            $authenticatedUser instanceof User ? $authenticatedUser : null,
            Auth::id(),
            $request->validated('client_ip'),
            $request->ip()
        );

        return view('dashboard', array_merge(
            $attendanceCardsData,
            $this->dashboardTaskData($authenticatedUser instanceof User ? $authenticatedUser : null)
        ));
    }

    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        try {
            $storeResult = $this->attendanceMutationService->store(
                $request->validated(),
                $request->ip(),
                $request->userAgent(),
                Auth::user(),
                Auth::id(),
            );

            return response()->json($storeResult['payload'], $storeResult['status']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absen masuk.',
            ], 500);
        }
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        try {
            $updateResult = $this->attendanceMutationService->update(
                $request->validated(),
                $request->ip(),
                $request->userAgent(),
                $attendance,
                Auth::user(),
                Auth::id(),
            );

            return response()->json($updateResult['payload'], $updateResult['status']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absen pulang.',
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardTaskData(?User $authenticatedUser): array
    {
        $currentDate = CarbonImmutable::now('Asia/Jakarta');
        $defaultData = [
            'dashboardTaskMonthLabel' => $currentDate->format('M'),
            'dashboardTaskTodayLabel' => $currentDate->format('D'),
            'dashboardMonthlyTaskTotal' => 0,
            'dashboardTodayDeadlineTaskTotal' => 0,
            'dashboardTaskProjectOptions' => collect(),
            'dashboardTaskAssignableStaffOptions' => collect(),
            'dashboardTaskProjectOptionsByEmployee' => [],
            'dashboardTaskStoreUrl' => route('project_management.task_list.tasks.store'),
        ];

        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        if ($employeeId === null) {
            return $defaultData;
        }

        $taskQuery = $this->projectTaskQueryForEmployee($employeeId);
        $monthStart = $currentDate->startOfMonth()->toDateString();
        $monthEnd = $currentDate->endOfMonth()->toDateString();
        $today = $currentDate->toDateString();
        $assignableStaffOptions = $this->taskAssignableStaffOptions($employeeId);
        $assignableEmployeeIds = $assignableStaffOptions
            ->pluck('id')
            ->filter(fn (mixed $assignableEmployeeId): bool => is_string($assignableEmployeeId) && trim($assignableEmployeeId) !== '')
            ->values();

        return array_merge($defaultData, [
            'dashboardMonthlyTaskTotal' => (clone $taskQuery)
                ->whereRaw('DATE(COALESCE(start_date, due_date, created_at)) <= ?', [$monthEnd])
                ->whereRaw('DATE(COALESCE(due_date, start_date, created_at)) >= ?', [$monthStart])
                ->count(),
            'dashboardTodayDeadlineTaskTotal' => (clone $taskQuery)
                ->whereDate('due_date', $today)
                ->count(),
            'dashboardTaskProjectOptions' => $this->taskProjectOptions($employeeId),
            'dashboardTaskAssignableStaffOptions' => $assignableStaffOptions,
            'dashboardTaskProjectOptionsByEmployee' => $this->taskProjectOptionsByEmployee($assignableEmployeeIds),
        ]);
    }

    private function authenticatedEmployeeId(?User $authenticatedUser): ?string
    {
        if (! $authenticatedUser instanceof User) {
            return null;
        }

        $authenticatedUser->loadMissing('employee:id,user_id');
        $employeeId = trim((string) ($authenticatedUser->employee?->id ?? ''));

        return $employeeId !== '' ? $employeeId : null;
    }

    private function projectTaskQueryForEmployee(string $employeeId): Builder
    {
        $authenticatedUserId = trim((string) (Auth::id() ?? ''));
        $subordinateEmployeeIds = $this->subordinateTaskEmployeeIdsForPic($employeeId);
        $visibleMembershipEmployeeIds = collect([$employeeId])
            ->merge($subordinateEmployeeIds)
            ->unique()
            ->values();

        return ProjectTask::query()
            ->where(function (Builder $query) use ($authenticatedUserId, $employeeId, $subordinateEmployeeIds): void {
                $query->where('employee_id', $employeeId);

                if ($authenticatedUserId !== '' && $subordinateEmployeeIds->isNotEmpty()) {
                    $query->orWhere(function (Builder $staffTaskQuery) use ($authenticatedUserId, $subordinateEmployeeIds): void {
                        $staffTaskQuery
                            ->whereIn('employee_id', $subordinateEmployeeIds->all())
                            ->where('assigned_by', $authenticatedUserId);
                    });
                }
            })
            ->where(function (Builder $query) use ($visibleMembershipEmployeeIds): void {
                $query->whereNotNull('overtime_id')
                    ->orWhereNull('project_id')
                    ->orWhereHas('project.memberships', function (Builder $membershipQuery) use ($visibleMembershipEmployeeIds): void {
                        $membershipQuery
                            ->whereIn('employee_id', $visibleMembershipEmployeeIds->all())
                            ->where('status', 'active')
                            ->whereNull('left_at');
                    });
            });
    }

    /**
     * @return Collection<int, string>
     */
    private function subordinateTaskEmployeeIdsForPic(string $employeeId): Collection
    {
        return EmployeePicAssignment::query()
            ->where('supervisor_employee_id', $employeeId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->pluck('staff_employee_id')
            ->filter(fn (mixed $staffEmployeeId): bool => is_string($staffEmployeeId) && trim($staffEmployeeId) !== '')
            ->map(fn (string $staffEmployeeId): string => trim($staffEmployeeId))
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    private function taskProjectOptions(string $employeeId): Collection
    {
        return Project::query()
            ->select(['id', 'name'])
            ->whereHas('memberships', function (Builder $query) use ($employeeId): void {
                $query
                    ->where('employee_id', $employeeId)
                    ->where('status', 'active')
                    ->whereNull('left_at');
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Project $project): array => [
                'id' => (string) $project->id,
                'name' => trim((string) $project->name),
            ])
            ->values();
    }

    /**
     * @param  Collection<int, string>  $employeeIds
     * @return array<string, array<int, array{id:string,name:string}>>
     */
    private function taskProjectOptionsByEmployee(Collection $employeeIds): array
    {
        return $employeeIds
            ->mapWithKeys(fn (string $employeeId): array => [
                $employeeId => $this->taskProjectOptions($employeeId)->all(),
            ])
            ->all();
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    private function taskAssignableStaffOptions(string $employeeId): Collection
    {
        $assignableEmployeeIds = collect([$employeeId])
            ->merge($this->subordinateTaskEmployeeIdsForPic($employeeId))
            ->unique()
            ->values();

        return Employee::query()
            ->with(['profile:id,employee_id,name', 'user:id,username,email'])
            ->whereIn('id', $assignableEmployeeIds->all())
            ->get()
            ->sortBy(fn (Employee $employee): int|false => $assignableEmployeeIds->search((string) $employee->id))
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'name' => $this->employeeOptionName($employee),
            ])
            ->values();
    }

    private function employeeOptionName(Employee $employee): string
    {
        $profileName = trim((string) ($employee->profile?->name ?? ''));
        if ($profileName !== '') {
            return $profileName;
        }

        $username = trim((string) ($employee->user?->username ?? ''));
        if ($username !== '') {
            return $username;
        }

        $email = trim((string) ($employee->user?->email ?? ''));
        if ($email !== '') {
            return str($email)->before('@')->toString();
        }

        return (string) $employee->id;
    }
}
