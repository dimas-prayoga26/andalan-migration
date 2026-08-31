<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePicAssignment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaskListController extends Controller
{
    public function index(Request $request): View
    {
        return view('project_management.task_list.index', array_merge(
            $this->profileMetricData(),
            $this->taskListData($request),
        ));
    }

    public function filter(Request $request): JsonResponse
    {
        $taskListData = $this->taskListData($request);

        return response()->json([
            'success' => true,
            'message' => 'Filter task berhasil diterapkan.',
            'selected_month' => $taskListData['taskListSelectedMonth'],
            'selected_month_number' => $taskListData['taskListSelectedMonthNumber'],
            'selected_year' => $taskListData['taskListSelectedYear'],
            'selected_month_label' => $taskListData['taskListSelectedMonthLabel'],
            'fragments' => $this->taskListFragments($taskListData),
        ]);
    }

    public function storeTask(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        if ($employeeId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Data employee login tidak ditemukan.',
            ], 403);
        }

        $validated = $this->validateTaskPayload($request);
        $taskEmployeeId = $this->validatedTaskEmployeeId($employeeId, $validated);
        if ($taskEmployeeId === false) {
            return response()->json([
                'success' => false,
                'message' => 'Staff yang dipilih tidak berada di bawah PIC login.',
            ], 422);
        }

        $projectId = $this->validatedProjectId($taskEmployeeId, $validated);
        if ($projectId === false) {
            return response()->json([
                'success' => false,
                'message' => 'Project tidak tersedia untuk staff ini.',
            ], 422);
        }

        $status = strtolower(trim((string) ($validated['status'] ?? 'pending')));

        DB::transaction(function () use ($authenticatedUser, $taskEmployeeId, $projectId, $status, $validated): void {
            ProjectTask::query()->create([
                'project_id' => $projectId,
                'employee_id' => $taskEmployeeId,
                'assigned_by' => $authenticatedUser?->id,
                'title' => trim((string) $validated['title']),
                'description' => $this->nullableStringValue($validated['description'] ?? null),
                'blockers' => $this->nullableStringValue($validated['blockers'] ?? null),
                'attachment_path' => $this->nullableStringValue($validated['attachment_path'] ?? null),
                'status' => $status,
                'priority' => strtolower(trim((string) $validated['priority'])),
                'start_date' => $validated['start_date'],
                'due_date' => $validated['due_date'],
                'completed_at' => $status === 'completed' ? now('Asia/Jakarta') : null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil ditambahkan.',
        ]);
    }

    public function updateTask(Request $request, ProjectTask $projectTask): JsonResponse
    {
        $employeeId = $this->authenticatedEmployeeId(Auth::user());
        if ($employeeId === null || ! $this->canManageTaskListTask($projectTask, $employeeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah task ini.',
            ], 403);
        }

        $validated = $this->validateTaskPayload($request);
        $taskEmployeeId = trim((string) $projectTask->employee_id);
        $projectId = $this->validatedProjectId($taskEmployeeId !== '' ? $taskEmployeeId : $employeeId, $validated);
        if ($projectId === false) {
            return response()->json([
                'success' => false,
                'message' => 'Project tidak tersedia untuk staff ini.',
            ], 422);
        }

        $status = strtolower(trim((string) $validated['status']));

        $projectTask->update([
            'project_id' => $projectId,
            'title' => trim((string) $validated['title']),
            'description' => $this->nullableStringValue($validated['description'] ?? null),
            'blockers' => $this->nullableStringValue($validated['blockers'] ?? null),
            'attachment_path' => $this->nullableStringValue($validated['attachment_path'] ?? null),
            'status' => $status,
            'priority' => strtolower(trim((string) $validated['priority'])),
            'start_date' => $validated['start_date'],
            'due_date' => $validated['due_date'],
            'completed_at' => $status === 'completed' ? ($projectTask->completed_at ?? now('Asia/Jakarta')) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil diperbarui.',
        ]);
    }

    public function completeTask(ProjectTask $projectTask): JsonResponse
    {
        $employeeId = $this->authenticatedEmployeeId(Auth::user());
        if ($employeeId === null || ! $this->canManageTaskListTask($projectTask, $employeeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menyelesaikan task ini.',
            ], 403);
        }

        $projectTask->update([
            'status' => 'completed',
            'completed_at' => now('Asia/Jakarta'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil ditandai selesai.',
        ]);
    }

    public function updateTaskStatus(Request $request, ProjectTask $projectTask): JsonResponse
    {
        $employeeId = $this->authenticatedEmployeeId(Auth::user());
        if ($employeeId === null || ! $this->canManageTaskListTask($projectTask, $employeeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah status task ini.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        $status = strtolower(trim((string) $validated['status']));

        $projectTask->update([
            'status' => $status,
            'completed_at' => $status === 'completed' ? ($projectTask->completed_at ?? now('Asia/Jakarta')) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status task berhasil diperbarui.',
        ]);
    }

    public function destroyTask(ProjectTask $projectTask): JsonResponse
    {
        $employeeId = $this->authenticatedEmployeeId(Auth::user());
        if ($employeeId === null || ! $this->canDeleteTaskListTask($projectTask, $employeeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus task ini.',
            ], 403);
        }

        $projectTask->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil dihapus.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function profileMetricData(): array
    {
        $currentDate = now('Asia/Jakarta');
        $defaultData = $this->defaultProfileMetricData($currentDate);
        $employeeId = $this->authenticatedEmployeeId(Auth::user());
        if ($employeeId === null) {
            return $defaultData;
        }

        $taskQuery = $this->projectTaskQueryForEmployee($employeeId);
        $monthlyTaskQuery = $this->projectTaskQueryForMonth(
            $taskQuery,
            (int) $currentDate->year,
            (int) $currentDate->month,
        );

        $totalTasksCount = (clone $monthlyTaskQuery)->count();
        $completedTasksCount = $this->completedTaskQuery($monthlyTaskQuery)->count();
        $inProgressTasksCount = (clone $monthlyTaskQuery)
            ->where('status', 'in_progress')
            ->whereNull('completed_at')
            ->count();
        $dailyTasksCount = (clone $monthlyTaskQuery)
            ->whereNull('project_id')
            ->count();
        $projectTasksCount = (clone $monthlyTaskQuery)
            ->whereNotNull('project_id')
            ->count();
        $taskCompletionRate = $this->percentage($completedTasksCount, $totalTasksCount);

        return array_merge($defaultData, [
            'profileMonthlyAttendanceSeries' => $this->monthlyCompletedTaskSeries($taskQuery, (int) $currentDate->year),
            'profileMonthlyAttendanceDelta' => $taskCompletionRate,
            'projectTasksCompletedCount' => $completedTasksCount,
            'projectTasksInProgressCount' => $inProgressTasksCount,
            'projectTotalTasksCount' => $totalTasksCount,
            'projectDailyTasksCount' => $dailyTasksCount,
            'projectProjectTasksCount' => $projectTasksCount,
            'projectWorkloadPercent' => $this->percentage($completedTasksCount + $inProgressTasksCount, $totalTasksCount),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultProfileMetricData(CarbonInterface $currentDate): array
    {
        return [
            'profileMonthlyAttendanceLabels' => $this->monthlyProgressLabels((int) $currentDate->year),
            'profileMonthlyAttendanceSeries' => array_fill(0, 12, 0),
            'profileMonthlyAttendanceDelta' => 0,
            'projectTasksCompletedCount' => 0,
            'projectTasksInProgressCount' => 0,
            'projectTotalTasksCount' => 0,
            'projectDailyTasksCount' => 0,
            'projectProjectTasksCount' => 0,
            'projectWorkloadPercent' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function taskListData(Request $request): array
    {
        $selectedMonth = $this->selectedTaskListMonth($request->query('month'), $request->query('year'));
        $defaultTaskListData = $this->defaultTaskListData($selectedMonth);
        $employeeId = $this->authenticatedEmployeeId(Auth::user());

        if ($employeeId === null) {
            return $defaultTaskListData;
        }

        $taskQuery = $this->projectTaskQueryForEmployee($employeeId);

        $monthTaskQuery = $this->projectTaskQueryForMonth(
            $taskQuery,
            (int) $selectedMonth->year,
            (int) $selectedMonth->month,
        );

        $tasks = (clone $monthTaskQuery)
            ->with([
                'project:id,name',
                'assignedBy:id,username',
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.user:id,username,email',
            ])
            ->orderByRaw('COALESCE(due_date, start_date, created_at) DESC')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ProjectTask $projectTask): array => $this->taskListItemValue($projectTask));

        $ongoingTasks = $tasks
            ->filter(fn (array $task): bool => ($task['is_completed'] ?? false) !== true)
            ->values();
        $doneTasks = $tasks
            ->filter(fn (array $task): bool => ($task['is_completed'] ?? false) === true)
            ->values();

        $weekStart = now('Asia/Jakarta')->startOfWeek();
        $weekEnd = now('Asia/Jakarta')->endOfWeek();
        $weekPlanTasks = (clone $taskQuery)
            ->with([
                'project:id,name',
                'assignedBy:id,username',
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.user:id,username,email',
            ])
            ->whereRaw('DATE(COALESCE(due_date, start_date, created_at)) >= ?', [$weekStart->toDateString()])
            ->whereRaw('DATE(COALESCE(due_date, start_date, created_at)) <= ?', [$weekEnd->toDateString()])
            ->orderByRaw('COALESCE(due_date, start_date, created_at) ASC')
            ->limit(5)
            ->get()
            ->map(fn (ProjectTask $projectTask): array => $this->taskListItemValue($projectTask));

        $pastIncompleteCount = (clone $taskQuery)
            ->whereRaw('DATE(COALESCE(due_date, start_date, created_at)) < ?', [$selectedMonth->startOfMonth()->toDateString()])
            ->where(function (Builder $query): void {
                $query->where('status', '!=', 'completed')
                    ->whereNull('completed_at');
            })
            ->count();

        $assignableStaffOptions = $this->taskListAssignableStaffOptions($employeeId);
        $assignableEmployeeIds = $assignableStaffOptions
            ->pluck('id')
            ->filter(fn (mixed $assignableEmployeeId): bool => is_string($assignableEmployeeId) && trim($assignableEmployeeId) !== '')
            ->values();

        return array_merge($defaultTaskListData, [
            'taskListItems' => $tasks,
            'taskListOngoingItems' => $ongoingTasks,
            'taskListDoneItems' => $doneTasks,
            'taskListKanbanGroups' => $this->taskListKanbanGroups($tasks),
            'taskListWeekPlanItems' => $weekPlanTasks,
            'taskListPastIncompleteCount' => $pastIncompleteCount,
            'taskListProjectOptions' => $this->taskListProjectOptions($employeeId),
            'taskListAssignableStaffOptions' => $assignableStaffOptions,
            'taskListProjectOptionsByEmployee' => $this->taskListProjectOptionsByEmployee($assignableEmployeeIds),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultTaskListData(CarbonInterface $selectedMonth): array
    {
        return [
            'taskListSelectedMonth' => $selectedMonth->format('Y-m'),
            'taskListSelectedMonthNumber' => (int) $selectedMonth->month,
            'taskListSelectedYear' => (int) $selectedMonth->year,
            'taskListSelectedMonthLabel' => $selectedMonth->format('F Y'),
            'taskListMonthOptions' => $this->taskListMonthOptions(),
            'taskListYearOptions' => $this->taskListYearOptions((int) $selectedMonth->year),
            'taskListItems' => collect(),
            'taskListOngoingItems' => collect(),
            'taskListDoneItems' => collect(),
            'taskListKanbanGroups' => $this->taskListKanbanGroups(collect()),
            'taskListWeekPlanItems' => collect(),
            'taskListProjectOptions' => collect(),
            'taskListAssignableStaffOptions' => collect(),
            'taskListProjectOptionsByEmployee' => [],
            'taskListPastIncompleteCount' => 0,
            'taskListStoreUrl' => route('project_management.task_list.tasks.store'),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $tasks
     * @return Collection<int, array{id:string,label:string,status:string,dot_class:string,progress_class:string,progress:int,empty:string,items:Collection<int, array<string, mixed>>}>
     */
    private function taskListKanbanGroups(Collection $tasks): Collection
    {
        $tasksByStatus = $tasks->groupBy(fn (array $task): string => (string) ($task['status'] ?? ''));

        return collect([
            [
                'id' => 'pending',
                'label' => 'To-Do List',
                'status' => 'pending',
                'dot_class' => 'text-secondary',
                'progress_class' => 'bg-secondary',
                'progress' => 20,
                'empty' => 'No pending task.',
            ],
            [
                'id' => 'in_progress',
                'label' => 'In Progress',
                'status' => 'in_progress',
                'dot_class' => 'text-warning',
                'progress_class' => 'bg-warning',
                'progress' => 65,
                'empty' => 'No task in progress.',
            ],
            [
                'id' => 'completed',
                'label' => 'Done',
                'status' => 'completed',
                'dot_class' => 'text-success',
                'progress_class' => 'bg-success',
                'progress' => 100,
                'empty' => 'No completed task.',
            ],
        ])->map(function (array $group) use ($tasksByStatus): array {
            $group['items'] = $tasksByStatus->get($group['status'], collect())->values();

            return $group;
        });
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

    private function selectedTaskListMonth(mixed $month, mixed $year = null): CarbonInterface
    {
        if (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            return CarbonImmutable::createFromFormat('Y-m-d', $month.'-01', 'Asia/Jakarta')->startOfMonth();
        }

        $monthNumber = is_numeric($month) ? (int) $month : 0;
        $yearNumber = is_numeric($year) ? (int) $year : 0;

        if ($monthNumber >= 1 && $monthNumber <= 12 && $yearNumber >= 2020 && $yearNumber <= 2100) {
            return CarbonImmutable::create($yearNumber, $monthNumber, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        }

        return CarbonImmutable::now('Asia/Jakarta')->startOfMonth();
    }

    /**
     * @return list<array{value:int,label:string}>
     */
    private function taskListMonthOptions(): array
    {
        return array_map(
            static function (int $month): array {
                $monthDate = CarbonImmutable::create(2000, $month, 1, 0, 0, 0, 'Asia/Jakarta');

                return [
                    'value' => $month,
                    'label' => $monthDate->format('F'),
                ];
            },
            range(1, 12),
        );
    }

    /**
     * @return list<int>
     */
    private function taskListYearOptions(int $selectedYear): array
    {
        $currentYear = (int) now('Asia/Jakarta')->year;

        return collect(range($currentYear - 3, $currentYear + 1))
            ->push($selectedYear)
            ->unique()
            ->filter(static fn (int $year): bool => $year >= 2020 && $year <= 2100)
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $taskListData
     * @return array{task_list:string,week_plan:string,project_grid:string}
     */
    private function taskListFragments(array $taskListData): array
    {
        return [
            'task_list' => view('project_management.task_list.partials.task-list-items', $taskListData)->render(),
            'week_plan' => view('project_management.task_list.partials.week-plan', $taskListData)->render(),
            'project_grid' => view('project_management.task_list.partials.project-grid', $taskListData)->render(),
        ];
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    private function taskListProjectOptions(string $employeeId): Collection
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
    private function taskListProjectOptionsByEmployee(Collection $employeeIds): array
    {
        return $employeeIds
            ->mapWithKeys(fn (string $employeeId): array => [
                $employeeId => $this->taskListProjectOptions($employeeId)->all(),
            ])
            ->all();
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    private function taskListAssignableStaffOptions(string $employeeId): Collection
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

    /**
     * @return array<string, mixed>
     */
    private function taskListItemValue(ProjectTask $projectTask): array
    {
        $taskDate = $this->taskOverviewDate($projectTask);
        $startDate = $projectTask->start_date;
        $dueDate = $projectTask->due_date;
        $isCompleted = $this->isCompletedTask($projectTask);
        $assignedByUserId = trim((string) ($projectTask->assigned_by ?? ''));
        $authenticatedUserId = trim((string) (Auth::id() ?? ''));
        $authenticatedEmployeeId = $this->authenticatedEmployeeId(Auth::user());
        $taskEmployeeId = (string) $projectTask->employee_id;
        $assignedByUsername = trim((string) ($projectTask->assignedBy?->username ?? 'self'));
        $isOvertimeTask = trim((string) ($projectTask->overtime_id ?? '')) !== '';
        $assigneeLabel = $projectTask->employee instanceof Employee
            ? $this->employeeOptionName($projectTask->employee)
            : trim($taskEmployeeId);

        return [
            'id' => (string) $projectTask->id,
            'employee_id' => $taskEmployeeId,
            'overtime_id' => $isOvertimeTask ? (string) $projectTask->overtime_id : '',
            'is_overtime_task' => $isOvertimeTask,
            'overtime_label' => 'Overtime',
            'can_manage_from_task_list' => true,
            'can_delete_from_task_list' => ! $isOvertimeTask,
            'title' => trim((string) $projectTask->title),
            'description' => trim((string) ($projectTask->description ?? '')),
            'blockers' => trim((string) ($projectTask->blockers ?? '')),
            'attachment_path' => trim((string) ($projectTask->attachment_path ?? '')),
            'status' => trim((string) $projectTask->status),
            'status_label' => $this->taskStatusLabel((string) $projectTask->status, $projectTask->completed_at !== null),
            'status_class' => $isCompleted ? 'text-success' : 'text-danger',
            'is_completed' => $isCompleted,
            'priority' => trim((string) $projectTask->priority),
            'priority_label' => $this->taskPriorityLabel((string) $projectTask->priority),
            'priority_class' => $this->taskPriorityClass((string) $projectTask->priority),
            'project_id' => $projectTask->project_id !== null ? (string) $projectTask->project_id : '',
            'project_name' => trim((string) ($projectTask->project?->name ?? 'Daily Task')),
            'task_category' => $projectTask->project_id === null ? 'daily' : 'project',
            'task_category_label' => $projectTask->project_id === null ? 'Daily Task' : 'Project Task',
            'assignee_label' => $assigneeLabel,
            'is_assigned_to_other_employee' => is_string($authenticatedEmployeeId) && $authenticatedEmployeeId !== '' && $taskEmployeeId !== $authenticatedEmployeeId,
            'assigned_by' => $assignedByUsername,
            'assigned_by_label' => $assignedByUsername,
            'is_assigned_by_other_user' => $assignedByUserId !== '' && $authenticatedUserId !== '' && $assignedByUserId !== $authenticatedUserId,
            'start_date' => $startDate?->toDateString() ?? '',
            'due_date' => $dueDate?->toDateString() ?? '',
            'date_day' => $taskDate?->format('d') ?? '-',
            'date_weekday' => $taskDate?->format('D') ?? '',
            'date_range_label' => $this->taskDateRangeLabel($startDate, $dueDate),
            'due_label' => $this->taskDueLabel($dueDate, $isCompleted),
            'update_url' => route('project_management.task_list.tasks.update', $projectTask),
            'status_update_url' => route('project_management.task_list.tasks.status.update', $projectTask),
            'complete_url' => route('project_management.task_list.tasks.complete', $projectTask),
            'delete_url' => route('project_management.task_list.tasks.destroy', $projectTask),
        ];
    }

    private function taskStatusLabel(string $status, bool $hasCompletedAt): string
    {
        if ($status === 'completed' || $hasCompletedAt) {
            return 'Finished';
        }

        return match ($status) {
            'in_progress' => 'On Progress',
            'cancelled' => 'Cancelled',
            default => 'Unfinished',
        };
    }

    private function taskPriorityLabel(string $priority): string
    {
        return match ($priority) {
            'high' => 'High',
            'low' => 'Low',
            default => 'Medium',
        };
    }

    private function taskPriorityClass(string $priority): string
    {
        return match ($priority) {
            'high' => 'text-danger',
            'low' => 'text-success',
            default => 'text-warning',
        };
    }

    private function taskDateRangeLabel(?CarbonInterface $startDate, ?CarbonInterface $dueDate): string
    {
        if ($startDate === null && $dueDate === null) {
            return 'No date';
        }

        if ($startDate === null) {
            return $dueDate?->format('d M Y') ?? 'No date';
        }

        if ($dueDate === null || $startDate->isSameDay($dueDate)) {
            return $startDate->format('d M Y');
        }

        if ($startDate->format('M Y') === $dueDate->format('M Y')) {
            return $startDate->format('d').' - '.$dueDate->format('d M Y');
        }

        return $startDate->format('d M Y').' - '.$dueDate->format('d M Y');
    }

    private function taskDueLabel(?CarbonInterface $dueDate, bool $isCompleted): string
    {
        if ($isCompleted) {
            return 'Completed';
        }

        if ($dueDate === null) {
            return 'No due date';
        }

        $today = CarbonImmutable::now('Asia/Jakarta')->startOfDay();
        $dueDate = CarbonImmutable::instance($dueDate)->startOfDay();
        $days = $today->diffInDays($dueDate, false);

        if ($days < 0) {
            return 'Overdue '.abs((int) $days).' day'.(abs((int) $days) === 1 ? '' : 's');
        }

        if ((int) $days === 0) {
            return 'Due today';
        }

        return (int) $days.' day'.((int) $days === 1 ? '' : 's').' left';
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTaskPayload(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'priority' => ['required', 'in:low,medium,high'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'blockers' => ['nullable', 'string', 'max:5000'],
            'task_category' => ['required', 'in:daily,project'],
            'project_id' => ['nullable', 'uuid'],
            'assigned_employee_id' => ['nullable', 'uuid'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
        ]);
    }

    private function validatedTaskEmployeeId(string $employeeId, array $validated): string|false
    {
        $requestedEmployeeId = is_string($validated['assigned_employee_id'] ?? null)
            ? trim($validated['assigned_employee_id'])
            : '';

        if ($requestedEmployeeId === '' || $requestedEmployeeId === $employeeId) {
            return $employeeId;
        }

        $isAssignedStaff = EmployeePicAssignment::query()
            ->where('supervisor_employee_id', $employeeId)
            ->where('staff_employee_id', $requestedEmployeeId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->exists();

        return $isAssignedStaff ? $requestedEmployeeId : false;
    }

    private function validatedProjectId(string $employeeId, array $validated): string|false|null
    {
        if (($validated['task_category'] ?? 'daily') === 'daily') {
            return null;
        }

        $projectId = is_string($validated['project_id'] ?? null) ? trim($validated['project_id']) : '';
        if ($projectId === '' || ! $this->employeeIsProjectMember($employeeId, $projectId)) {
            return false;
        }

        return $projectId;
    }

    private function employeeIsProjectMember(string $employeeId, string $projectId): bool
    {
        return ProjectMember::query()
            ->where('employee_id', trim($employeeId))
            ->where('project_id', trim($projectId))
            ->where('status', 'active')
            ->whereNull('left_at')
            ->exists();
    }

    private function canManageTaskListTask(ProjectTask $projectTask, string $employeeId): bool
    {
        $taskEmployeeId = trim((string) $projectTask->employee_id);
        if ($taskEmployeeId === $employeeId) {
            return true;
        }

        $authenticatedUserId = trim((string) (Auth::id() ?? ''));
        if ($authenticatedUserId === '' || trim((string) $projectTask->assigned_by) !== $authenticatedUserId) {
            return false;
        }

        return $this->subordinateTaskEmployeeIdsForPic($employeeId)->contains($taskEmployeeId);
    }

    private function canDeleteTaskListTask(ProjectTask $projectTask, string $employeeId): bool
    {
        return $this->canManageTaskListTask($projectTask, $employeeId)
            && ($projectTask->overtime_id === null || trim((string) $projectTask->overtime_id) === '');
    }

    private function nullableStringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
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

    private function projectTaskQueryForMonth(Builder $taskQuery, int $year, int $month): Builder
    {
        return (clone $taskQuery)
            ->whereRaw('YEAR(COALESCE(due_date, start_date, created_at)) = ?', [$year])
            ->whereRaw('MONTH(COALESCE(due_date, start_date, created_at)) = ?', [$month]);
    }

    private function completedTaskQuery(Builder $taskQuery): Builder
    {
        return (clone $taskQuery)
            ->where(function (Builder $query): void {
                $query->where('status', 'completed')
                    ->orWhereNotNull('completed_at');
            });
    }

    private function percentage(int $value, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }

    /**
     * @return list<string>
     */
    private function monthlyProgressLabels(int $year): array
    {
        return array_map(
            static fn (int $month): string => CarbonImmutable::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->format('F'),
            range(1, 12),
        );
    }

    /**
     * @return list<int>
     */
    private function monthlyCompletedTaskSeries(Builder $taskQuery, int $year): array
    {
        $series = array_fill(0, 12, 0);

        $this->completedTaskQuery($taskQuery)
            ->get(['due_date', 'start_date', 'created_at', 'updated_at'])
            ->each(function (ProjectTask $projectTask) use (&$series, $year): void {
                $taskDate = $this->taskOverviewDate($projectTask);

                if ($taskDate === null || (int) $taskDate->format('Y') !== $year) {
                    return;
                }

                $monthIndex = (int) $taskDate->format('n') - 1;
                if ($monthIndex >= 0 && $monthIndex <= 11) {
                    $series[$monthIndex]++;
                }
            });

        return $series;
    }

    private function taskOverviewDate(ProjectTask $projectTask): ?CarbonInterface
    {
        return $projectTask->due_date
            ?? $projectTask->start_date
            ?? $projectTask->created_at
            ?? $projectTask->updated_at;
    }

    private function isCompletedTask(ProjectTask $projectTask): bool
    {
        return $projectTask->status === 'completed' || $projectTask->completed_at !== null;
    }
}
