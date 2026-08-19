<?php

namespace App\Http\Controllers\DirectorAttendance;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ProjectTask;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DirectorAttendanceTaskController extends Controller
{
    private const EXCLUDED_TASK_EMPLOYEE_EMAILS = [
        'lukman@rnbmanagement.com',
        'rully.priyatno@andalanbersama.com',
        'hilmi.ulwan@andalanbersama.com',
    ];

    public function index(Request $request): View
    {
        return view('director_attendance.task.index', [
            'directorTaskCompanyOptions' => $this->companyOptions(),
            'directorTaskStaffOptions' => $this->staffOptions(),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $companyId = $request->string('company_id')->trim()->toString();
        $staffEmployeeIds = $this->activeStaffEmployeeIds(
            now('Asia/Jakarta')->startOfDay(),
            $companyId !== '' ? $companyId : null,
        );
        $selectedStaffIds = $this->selectedStaffEmployeeIds($request, $staffEmployeeIds);
        if ($selectedStaffIds === false || $selectedStaffIds->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $currentMonthStart = now('Asia/Jakarta')->startOfMonth()->toDateString();
        $currentMonthEnd = now('Asia/Jakarta')->endOfMonth()->toDateString();

        $tasks = ProjectTask::query()
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.user:id,username,email',
                'employee.deployment:id,employee_id,current_company_id',
                'employee.deployment.company:id,name',
                'project:id,name',
                'assignedBy:id,username,email',
            ])
            ->whereIn('employee_id', $selectedStaffIds->all())
            ->whereNull('overtime_id')
            ->whereRaw('DATE(COALESCE(due_date, start_date, created_at)) BETWEEN ? AND ?', [$currentMonthStart, $currentMonthEnd])
            ->orderByRaw('COALESCE(due_date, start_date, created_at) DESC')
            ->get([
                'id',
                'project_id',
                'employee_id',
                'assigned_by',
                'overtime_id',
                'title',
                'description',
                'blockers',
                'attachment_path',
                'status',
                'priority',
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
    private function companyOptions(): Collection
    {
        $companyIds = Employee::query()
            ->whereIn('id', $this->activeStaffEmployeeIds(now('Asia/Jakarta')->startOfDay())->all())
            ->whereHas('deployment', function ($query): void {
                $query->whereNotNull('current_company_id');
            })
            ->with('deployment:id,employee_id,current_company_id')
            ->get(['id'])
            ->pluck('deployment.current_company_id')
            ->filter(fn (mixed $companyId): bool => is_string($companyId) && trim($companyId) !== '')
            ->map(fn (string $companyId): string => trim($companyId))
            ->unique()
            ->values();

        if ($companyIds->isEmpty()) {
            return collect();
        }

        return Company::query()
            ->whereIn('id', $companyIds->all())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Company $company): array => [
                'id' => (string) $company->id,
                'name' => trim((string) $company->name),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{id:string,name:string,company_id:string,company_name:string}>
     */
    private function staffOptions(): Collection
    {
        $staffEmployeeIds = $this->activeStaffEmployeeIds(now('Asia/Jakarta')->startOfDay());
        if ($staffEmployeeIds->isEmpty()) {
            return collect();
        }

        return Employee::query()
            ->with([
                'profile:id,employee_id,name',
                'user:id,username,email',
                'deployment:id,employee_id,current_company_id',
                'deployment.company:id,name',
            ])
            ->whereIn('id', $staffEmployeeIds->all())
            ->get(['id', 'user_id'])
            ->sortBy(fn (Employee $employee): int|false => $staffEmployeeIds->search((string) $employee->id))
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'name' => $this->employeeDisplayName($employee),
                'company_id' => trim((string) ($employee->deployment?->current_company_id ?? '')),
                'company_name' => trim((string) ($employee->deployment?->company?->name ?? '-')),
            ])
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function activeStaffEmployeeIds(Carbon $date, ?string $companyId = null): Collection
    {
        $todayDate = $date->toDateString();
        $companyId = is_string($companyId) ? trim($companyId) : '';

        return Employee::query()
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
            ->whereHas('user', function ($query): void {
                $query
                    ->where('is_active', true)
                    ->whereNotIn('email', self::EXCLUDED_TASK_EMPLOYEE_EMAILS)
                    ->whereDoesntHave('roles', function ($roleQuery): void {
                        $roleQuery->where('name', 'superuser');
                    });
            })
            ->whereHas('deployment', function ($query) use ($todayDate, $companyId): void {
                $query
                    ->whereNull('deleted_at')
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereRaw('LOWER(COALESCE(workplace, "")) <> ?', ['rnb jakarta'])
                    ->where(function ($query) use ($todayDate): void {
                        $query
                            ->whereNull('join_date')
                            ->orWhereDate('join_date', '<=', $todayDate);
                    })
                    ->where(function ($query) use ($todayDate): void {
                        $query
                            ->whereNull('resignation_date')
                            ->orWhereDate('resignation_date', '>=', $todayDate);
                    });

                if ($companyId !== '') {
                    $query->where('current_company_id', $companyId);
                }
            })
            ->pluck('id')
            ->filter(fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->map(fn (string $employeeId): string => trim($employeeId))
            ->values();
    }

    private function selectedStaffEmployeeIds(Request $request, Collection $staffEmployeeIds): Collection|false
    {
        if ($staffEmployeeIds->isEmpty()) {
            return collect();
        }

        $selectedStaffId = $request->string('staff')->trim()->toString();
        if ($selectedStaffId === '') {
            return $staffEmployeeIds;
        }

        return $staffEmployeeIds->contains($selectedStaffId)
            ? collect([$selectedStaffId])
            : false;
    }

    /**
     * @return array<string, string>
     */
    private function taskRow(ProjectTask $projectTask): array
    {
        $isCompleted = $projectTask->status === 'completed' || $projectTask->completed_at !== null;
        $projectName = trim((string) ($projectTask->project?->name ?? 'Daily Task'));

        return [
            'id' => (string) $projectTask->id,
            'staff' => $this->employeeDisplayName($projectTask->employee),
            'company' => trim((string) ($projectTask->employee?->deployment?->company?->name ?? '-')),
            'task' => trim((string) $projectTask->title),
            'description' => trim((string) ($projectTask->description ?? '')),
            'blockers' => trim((string) ($projectTask->blockers ?? '')),
            'attachment_path' => trim((string) ($projectTask->attachment_path ?? '')),
            'project' => $projectName,
            'task_category' => $projectTask->project_id !== null ? 'Project Task' : 'Daily Task',
            'assigned_by' => $this->assignedByLabel($projectTask),
            'due_date' => $this->dateRangeLabel($projectTask->start_date, $projectTask->due_date),
            'priority' => $this->priorityLabel((string) $projectTask->priority),
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

    private function priorityLabel(string $priority): string
    {
        return match (strtolower(trim($priority))) {
            'high' => 'High',
            'low' => 'Low',
            default => 'Medium',
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
