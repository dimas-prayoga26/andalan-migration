<?php

namespace App\Http\Controllers\PicAttendance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePicAssignment;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PicAttendanceTaskController extends Controller
{
    public function index(Request $request): View
    {
        return view('pic_attendance.task.index', [
            'picTaskStaffOptions' => $this->staffOptionsFor($request->user()),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();
        $supervisorEmployeeId = $this->authenticatedEmployeeId($authenticatedUser);
        if ($supervisorEmployeeId === null) {
            return response()->json(['data' => []]);
        }

        $staffEmployeeIds = $this->supervisedStaffEmployeeIds($supervisorEmployeeId);
        $selectedStaffId = $this->selectedStaffEmployeeId($request, $staffEmployeeIds);
        if ($selectedStaffId === false || $selectedStaffId === null) {
            return response()->json(['data' => []]);
        }

        $visibleEmployeeIds = collect([$selectedStaffId]);

        if ($visibleEmployeeIds->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $tasks = ProjectTask::query()
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.user:id,username,email',
                'project:id,name',
                'assignedBy:id,username,email',
            ])
            ->whereIn('employee_id', $visibleEmployeeIds->all())
            ->whereNull('overtime_id')
            ->orderByRaw('COALESCE(due_date, start_date, created_at) ASC')
            ->get([
                'id',
                'project_id',
                'employee_id',
                'assigned_by',
                'overtime_id',
                'title',
                'status',
                'start_date',
                'due_date',
                'completed_at',
                'created_at',
            ])
            ->map(fn (ProjectTask $projectTask): array => $this->taskRow($projectTask));

        return response()->json(['data' => $tasks]);
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    private function staffOptionsFor(?User $authenticatedUser): Collection
    {
        $supervisorEmployeeId = $this->authenticatedEmployeeId($authenticatedUser);
        if ($supervisorEmployeeId === null) {
            return collect();
        }

        $staffEmployeeIds = $this->supervisedStaffEmployeeIds($supervisorEmployeeId);
        if ($staffEmployeeIds->isEmpty()) {
            return collect();
        }

        return Employee::query()
            ->with(['profile:id,employee_id,name', 'user:id,username,email'])
            ->whereIn('id', $staffEmployeeIds->all())
            ->get(['id', 'user_id'])
            ->sortBy(fn (Employee $employee): int|false => $staffEmployeeIds->search((string) $employee->id))
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'name' => $this->employeeDisplayName($employee),
            ])
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function supervisedStaffEmployeeIds(string $supervisorEmployeeId): Collection
    {
        return EmployeePicAssignment::query()
            ->where('supervisor_employee_id', $supervisorEmployeeId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->pluck('staff_employee_id')
            ->filter(fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->map(fn (string $employeeId): string => trim($employeeId))
            ->unique()
            ->values();
    }

    private function selectedStaffEmployeeId(Request $request, Collection $staffEmployeeIds): string|false|null
    {
        if ($staffEmployeeIds->isEmpty()) {
            return null;
        }

        $selectedStaffId = $request->string('staff')->trim()->toString();
        if ($selectedStaffId === '') {
            return null;
        }

        return $staffEmployeeIds->contains($selectedStaffId) ? $selectedStaffId : false;
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

    /**
     * @return array<string, string>
     */
    private function taskRow(ProjectTask $projectTask): array
    {
        $isCompleted = $projectTask->status === 'completed' || $projectTask->completed_at !== null;

        return [
            'staff' => $this->employeeDisplayName($projectTask->employee),
            'task' => trim((string) $projectTask->title),
            'project' => trim((string) ($projectTask->project?->name ?? 'Daily Task')),
            'assigned_by' => $this->assignedByLabel($projectTask),
            'due_date' => $this->dateRangeLabel($projectTask->start_date, $projectTask->due_date),
            'status' => $this->statusLabel((string) $projectTask->status, $isCompleted),
            'status_class' => $isCompleted ? 'success' : 'warning',
        ];
    }

    private function statusLabel(string $status, bool $isCompleted): string
    {
        if ($isCompleted) {
            return 'Finished';
        }

        return match (strtolower(trim($status))) {
            'in_progress' => 'On Progress',
            'cancelled' => 'Cancelled',
            default => 'Pending',
        };
    }

    private function employeeDisplayName(?Employee $employee): string
    {
        $profileName = $employee?->profile?->name;
        if (is_string($profileName) && trim($profileName) !== '') {
            return trim($profileName);
        }

        $username = $employee?->user?->username;
        if (is_string($username) && trim($username) !== '') {
            return trim($username);
        }

        $email = $employee?->user?->email;
        if (is_string($email) && trim($email) !== '') {
            return str($email)->before('@')->toString();
        }

        return 'Unknown Staff';
    }

    private function assignedByLabel(ProjectTask $projectTask): string
    {
        $username = trim((string) ($projectTask->assignedBy?->username ?? ''));
        if ($username !== '') {
            return $username;
        }

        $email = trim((string) ($projectTask->assignedBy?->email ?? ''));
        if ($email !== '') {
            return str($email)->before('@')->toString();
        }

        return 'Self';
    }

    private function dateRangeLabel(?CarbonInterface $startDate, ?CarbonInterface $dueDate): string
    {
        if ($startDate === null && $dueDate === null) {
            return '-';
        }

        if ($startDate === null) {
            return $dueDate?->format('d M Y') ?? '-';
        }

        if ($dueDate === null || $startDate->isSameDay($dueDate)) {
            return $startDate->format('d M Y');
        }

        if ($startDate->format('M Y') === $dueDate->format('M Y')) {
            return $startDate->format('d').' - '.$dueDate->format('d M Y');
        }

        return $startDate->format('d M Y').' - '.$dueDate->format('d M Y');
    }
}
