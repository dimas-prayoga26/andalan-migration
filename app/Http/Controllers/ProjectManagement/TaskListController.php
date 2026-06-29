<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
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
        $projectId = $this->validatedProjectId($employeeId, $validated);
        if ($projectId === false) {
            return response()->json([
                'success' => false,
                'message' => 'Project tidak tersedia untuk staff ini.',
            ], 422);
        }

        $status = strtolower(trim((string) ($validated['status'] ?? 'pending')));

        DB::transaction(function () use ($authenticatedUser, $employeeId, $projectId, $status, $validated): void {
            ProjectTask::query()->create([
                'project_id' => $projectId,
                'employee_id' => $employeeId,
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
        $projectId = $this->validatedProjectId($employeeId, $validated);
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

    public function destroyTask(ProjectTask $projectTask): JsonResponse
    {
        $employeeId = $this->authenticatedEmployeeId(Auth::user());
        if ($employeeId === null || ! $this->canManageTaskListTask($projectTask, $employeeId)) {
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

        $taskQuery = $this->projectTaskQueryForEmployee($employeeId)
            ->whereNull('overtime_id');

        $monthTaskQuery = $this->projectTaskQueryForMonth(
            $taskQuery,
            (int) $selectedMonth->year,
            (int) $selectedMonth->month,
        );

        $tasks = (clone $monthTaskQuery)
            ->with(['project:id,name', 'assignedBy:id,username'])
            ->orderByRaw('COALESCE(due_date, start_date, created_at) ASC')
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
            ->with(['project:id,name'])
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

        return array_merge($defaultTaskListData, [
            'taskListItems' => $tasks,
            'taskListOngoingItems' => $ongoingTasks,
            'taskListDoneItems' => $doneTasks,
            'taskListWeekPlanItems' => $weekPlanTasks,
            'taskListPastIncompleteCount' => $pastIncompleteCount,
            'taskListProjectOptions' => $this->taskListProjectOptions($employeeId),
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
            'taskListWeekPlanItems' => collect(),
            'taskListProjectOptions' => collect(),
            'taskListPastIncompleteCount' => 0,
            'taskListStoreUrl' => route('project_management.task_list.tasks.store'),
        ];
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
     * @return array<string, mixed>
     */
    private function taskListItemValue(ProjectTask $projectTask): array
    {
        $taskDate = $this->taskOverviewDate($projectTask);
        $startDate = $projectTask->start_date;
        $dueDate = $projectTask->due_date;
        $isCompleted = $this->isCompletedTask($projectTask);

        return [
            'id' => (string) $projectTask->id,
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
            'assigned_by' => trim((string) ($projectTask->assignedBy?->username ?? 'self')),
            'start_date' => $startDate?->toDateString() ?? '',
            'due_date' => $dueDate?->toDateString() ?? '',
            'date_day' => $taskDate?->format('d') ?? '-',
            'date_weekday' => $taskDate?->format('D') ?? '',
            'date_range_label' => $this->taskDateRangeLabel($startDate, $dueDate),
            'due_label' => $this->taskDueLabel($dueDate, $isCompleted),
            'update_url' => route('project_management.task_list.tasks.update', $projectTask),
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
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
        ]);
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
        return (string) $projectTask->employee_id === $employeeId
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
        return ProjectTask::query()
            ->where('employee_id', $employeeId)
            ->where(function (Builder $query) use ($employeeId): void {
                $query->whereNull('project_id')
                    ->orWhereHas('project.memberships', function (Builder $membershipQuery) use ($employeeId): void {
                        $membershipQuery
                            ->where('employee_id', $employeeId)
                            ->where('status', 'active')
                            ->whereNull('left_at');
                    });
            });
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
