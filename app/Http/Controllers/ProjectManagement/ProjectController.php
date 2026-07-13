<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $employeeId = $this->authenticatedEmployeeId(Auth::user());

        return view('project_management.projects.index', array_merge($this->profileMetricData($employeeId), [
            'projectCards' => $employeeId !== null
                ? $this->projectCards($employeeId)
                : collect(),
        ]));
    }

    public function detailFallback(): RedirectResponse
    {
        $employeeId = $this->authenticatedEmployeeId(Auth::user());
        $project = $employeeId !== null
            ? $this->projectsForEmployee($employeeId)->first()
            : null;

        if (! $project instanceof Project) {
            return redirect()->route('project_management.projects');
        }

        return redirect()->route('project_management.projects.detail', $project);
    }

    public function detail(Project $project): View
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        abort_if($employeeId === null || ! $this->employeeCanViewProject($project, $employeeId), 403);

        $ownDepartmentId = $this->authenticatedDepartmentId($authenticatedUser);
        $project->loadMissing([
            'creator:id,username',
            'memberships.employee:id,user_id',
            'memberships.employee.user:id,username',
            'memberships.employee.profile:id,employee_id,name,profile_picture_path',
            'memberships.employee.deployment:id,employee_id,current_department_id',
            'memberships.employee.deployment.department:id,name',
        ]);

        $tasks = ProjectTask::query()
            ->where('project_id', $project->id)
            ->with([
                'assignedBy:id,username',
                'employee:id,user_id',
                'employee.user:id,username',
                'employee.profile:id,employee_id,name,profile_picture_path',
                'employee.deployment:id,employee_id,current_department_id',
                'employee.deployment.department:id,name',
            ])
            ->orderByRaw('COALESCE(due_date, start_date, created_at) ASC')
            ->get();

        $projectDepartmentGroups = $this->projectDepartmentGroups($project, $tasks, $ownDepartmentId);

        return view('project_management.projects.detail', array_merge($this->profileMetricData($employeeId), [
            'projectDetail' => $this->projectDetailValue($project),
            'projectDepartmentGroups' => $projectDepartmentGroups,
            'projectSummary' => $this->projectSummaryValue($project, $tasks),
            'projectTaskTimeline' => $this->projectTaskTimelineValue($project, $tasks),
        ]));
    }

    public function toggleTask(Request $request, Project $project, ProjectTask $projectTask): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $ownDepartmentId = $this->authenticatedDepartmentId($authenticatedUser);

        if (
            $employeeId === null
            || $ownDepartmentId === null
            || ! $this->employeeCanViewProject($project, $employeeId)
            || (string) $projectTask->project_id !== (string) $project->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah task project ini.',
            ], 403);
        }

        $projectTask->loadMissing('employee.deployment:id,employee_id,current_department_id');
        if ((string) ($projectTask->employee?->deployment?->current_department_id ?? '') !== $ownDepartmentId) {
            return response()->json([
                'success' => false,
                'message' => 'Task hanya bisa diubah oleh department yang sesuai.',
            ], 403);
        }

        $validated = $request->validate([
            'completed' => ['required', 'boolean'],
        ]);
        $isCompleted = (bool) $validated['completed'];

        $projectTask->update([
            'status' => $isCompleted ? 'completed' : 'pending',
            'completed_at' => $isCompleted ? now('Asia/Jakarta') : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $isCompleted ? 'Task department berhasil diselesaikan.' : 'Task department dikembalikan ke pending.',
            'task' => $this->projectTaskValue($projectTask->fresh(['assignedBy:id,username', 'employee:id,user_id', 'employee.user:id,username', 'employee.profile:id,employee_id,name', 'employee.deployment:id,employee_id,current_department_id', 'employee.deployment.department:id,name']), $ownDepartmentId),
        ]);
    }

    public function storeTask(Request $request, Project $project): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $ownDepartmentId = $this->authenticatedDepartmentId($authenticatedUser);

        if ($employeeId === null || $ownDepartmentId === null || ! $this->employeeCanViewProject($project, $employeeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menambahkan task project ini.',
            ], 403);
        }

        $validated = $this->validateProjectTaskPayload($request);
        $status = strtolower(trim((string) $validated['status']));

        ProjectTask::query()->create([
            'project_id' => $project->id,
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

        return response()->json([
            'success' => true,
            'message' => 'Task project berhasil ditambahkan.',
        ]);
    }

    public function updateTask(Request $request, Project $project, ProjectTask $projectTask): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $ownDepartmentId = $this->authenticatedDepartmentId($authenticatedUser);

        if (
            $employeeId === null
            || $ownDepartmentId === null
            || ! $this->employeeCanViewProject($project, $employeeId)
            || ! $this->canManageProjectTask($project, $projectTask, $ownDepartmentId)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah task project ini.',
            ], 403);
        }

        $validated = $this->validateProjectTaskPayload($request);
        $status = strtolower(trim((string) $validated['status']));

        $projectTask->update([
            'assigned_by' => $projectTask->assigned_by ?? $authenticatedUser?->id,
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
            'message' => 'Task project berhasil diperbarui.',
        ]);
    }

    public function destroyTask(Project $project, ProjectTask $projectTask): JsonResponse
    {
        $employeeId = $this->authenticatedEmployeeId(Auth::user());
        $ownDepartmentId = $this->authenticatedDepartmentId(Auth::user());

        if (
            $employeeId === null
            || $ownDepartmentId === null
            || ! $this->employeeCanViewProject($project, $employeeId)
            || ! $this->canManageProjectTask($project, $projectTask, $ownDepartmentId)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus task project ini.',
            ], 403);
        }

        $projectTask->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task project berhasil dihapus.',
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

    private function authenticatedDepartmentId(?User $authenticatedUser): ?string
    {
        if (! $authenticatedUser instanceof User) {
            return null;
        }

        $authenticatedUser->loadMissing('employee.deployment:id,employee_id,current_department_id');
        $departmentId = trim((string) ($authenticatedUser->employee?->deployment?->current_department_id ?? ''));

        return $departmentId !== '' ? $departmentId : null;
    }

    private function employeeCanViewProject(Project $project, string $employeeId): bool
    {
        return $project->memberships()
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->whereNull('left_at')
            ->exists();
    }

    private function projectsForEmployee(string $employeeId): Builder
    {
        return Project::query()
            ->whereHas('memberships', function (Builder $query) use ($employeeId): void {
                $query
                    ->where('employee_id', $employeeId)
                    ->where('status', 'active')
                    ->whereNull('left_at');
            })
            ->withCount([
                'memberships as active_members_count' => function (Builder $query): void {
                    $query->where('status', 'active')->whereNull('left_at');
                },
                'tasks as project_tasks_count',
                'tasks as completed_tasks_count' => function (Builder $query): void {
                    $query->where(function (Builder $completedQuery): void {
                        $completedQuery->where('status', 'completed')
                            ->orWhereNotNull('completed_at');
                    });
                },
            ])
            ->orderByDesc('start_date')
            ->orderBy('name');
    }

    /**
     * @return array<string, mixed>
     */
    private function profileMetricData(?string $employeeId): array
    {
        $currentDate = now('Asia/Jakarta');
        $defaultData = $this->defaultProfileMetricData($currentDate);

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

    private function projectTaskQueryForEmployee(string $employeeId): Builder
    {
        return ProjectTask::query()
            ->where('employee_id', $employeeId)
            ->where(function (Builder $query) use ($employeeId): void {
                $query->whereNotNull('overtime_id')
                    ->orWhereNull('project_id')
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

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function projectCards(string $employeeId): Collection
    {
        return $this->projectsForEmployee($employeeId)
            ->get()
            ->map(fn (Project $project): array => $this->projectCardValue($project));
    }

    /**
     * @return array<string, mixed>
     */
    private function projectCardValue(Project $project): array
    {
        $totalTasks = (int) ($project->project_tasks_count ?? 0);
        $completedTasks = (int) ($project->completed_tasks_count ?? 0);

        return [
            'id' => (string) $project->id,
            'name' => trim((string) $project->name),
            'description' => trim((string) ($project->description ?? 'Project task coordination workspace.')),
            'client_name' => trim((string) ($project->client_name ?? '-')),
            'status_label' => $this->projectStatusLabel((string) $project->status),
            'status_class' => $this->projectStatusClass((string) $project->status),
            'date_label' => $this->projectDateRangeLabel($project->start_date, $project->end_date),
            'due_label' => $project->end_date?->format('d M Y') ?? '-',
            'team_count' => (int) ($project->active_members_count ?? 0),
            'task_count' => $totalTasks,
            'completed_count' => $completedTasks,
            'completion_rate' => $this->percentage($completedTasks, $totalTasks),
            'detail_url' => route('project_management.projects.detail', $project),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function projectDetailValue(Project $project): array
    {
        return [
            'id' => (string) $project->id,
            'name' => trim((string) $project->name),
            'description' => trim((string) ($project->description ?? '-')),
            'subtitle' => trim((string) ($project->description ?? $project->client_name ?? '-')),
            'client_name' => trim((string) ($project->client_name ?? '-')),
            'status_label' => $this->projectStatusLabel((string) $project->status),
            'status_class' => $this->projectStatusClass((string) $project->status),
            'live_event_date_label' => $this->projectDateRangeLabel($project->live_event_start_date ?? $project->start_date, $project->live_event_end_date ?? $project->end_date),
            'live_event_duration_label' => $this->projectDurationLabel($project->live_event_start_date ?? $project->start_date, $project->live_event_end_date ?? $project->end_date),
            'date_label' => $this->projectDateRangeLabel($project->start_date, $project->end_date),
            'duration_label' => $this->projectDurationLabel($project->start_date, $project->end_date),
            'pic_label' => trim((string) ($project->creator?->username ?? '-')),
            'team_count' => (int) $project->memberships->count(),
            'team_members' => $project->memberships
                ->map(fn ($membership): array => $this->teamMemberValue($membership->employee))
                ->filter(fn (array $teamMember): bool => $teamMember !== [])
                ->values(),
            'team_names' => $project->memberships
                ->map(fn ($membership): string => trim((string) ($membership->employee?->profile?->name ?? $membership->employee?->user?->username ?? 'Staff')))
                ->filter()
                ->values(),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function teamMemberValue(?Employee $employee): array
    {
        if ($employee === null) {
            return [];
        }

        $displayName = trim((string) ($employee->profile?->name ?? $employee->user?->username ?? 'Staff'));

        return [
            'name' => $displayName !== '' ? $displayName : 'Staff',
            'avatar_url' => $this->profilePictureUrl($employee->profile?->profile_picture_path),
            'fallback_label' => $this->teamAvatarFallbackLabel($displayName),
        ];
    }

    private function profilePictureUrl(mixed $profilePicturePath): ?string
    {
        $profilePicturePath = trim((string) $profilePicturePath);
        if ($profilePicturePath === '') {
            return null;
        }

        if (str_starts_with($profilePicturePath, 'http://') || str_starts_with($profilePicturePath, 'https://')) {
            return $profilePicturePath;
        }

        return asset(ltrim($profilePicturePath, '/'));
    }

    private function teamAvatarFallbackLabel(string $displayName): string
    {
        if (preg_match('/\d+/', $displayName, $matches) === 1) {
            return substr($matches[0], 0, 2);
        }

        $words = preg_split('/\s+/', trim($displayName)) ?: [];
        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => strtoupper(substr($word, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'S';
    }

    /**
     * @param  Collection<int, ProjectTask>  $tasks
     * @return array<string, mixed>
     */
    private function projectSummaryValue(Project $project, Collection $tasks): array
    {
        $completedCount = $tasks->filter(fn (ProjectTask $task): bool => $this->isCompletedTask($task))->count();
        $overdueCount = $tasks->filter(fn (ProjectTask $task): bool => $this->isOverdueTask($task))->count();

        return [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $completedCount,
            'incomplete_tasks' => max(0, $tasks->count() - $completedCount),
            'overdue_tasks' => $overdueCount,
            'completion_rate' => $this->percentage($completedCount, $tasks->count()),
            'project_name' => trim((string) $project->name),
        ];
    }

    /**
     * @param  Collection<int, ProjectTask>  $tasks
     * @return array{month_label:string, labels:array<int, string>, completed:array<int, int>, incomplete:array<int, int>}
     */
    private function projectTaskTimelineValue(Project $project, Collection $tasks): array
    {
        $anchorDate = $project->start_date
            ?? $project->live_event_start_date
            ?? $tasks->first()?->due_date
            ?? CarbonImmutable::now('Asia/Jakarta');
        $monthStart = CarbonImmutable::instance($anchorDate)->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();
        $cursor = $monthStart;
        $labels = [];
        $completed = [];
        $incomplete = [];

        while ($cursor->lte($monthEnd)) {
            $weekStart = $cursor;
            $weekEnd = $cursor->addDays(6)->min($monthEnd);
            $weeklyTasks = $tasks->filter(function (ProjectTask $task) use ($weekStart, $weekEnd): bool {
                $taskDate = $task->due_date ?? $task->start_date ?? $task->created_at;
                if ($taskDate === null) {
                    return false;
                }

                return CarbonImmutable::instance($taskDate)
                    ->startOfDay()
                    ->betweenIncluded($weekStart, $weekEnd);
            });

            $labels[] = $weekStart->day.'-'.$weekEnd->day.' '.$weekEnd->format('M');
            $completed[] = $weeklyTasks->filter(fn (ProjectTask $task): bool => $this->isCompletedTask($task))->count();
            $incomplete[] = $weeklyTasks->reject(fn (ProjectTask $task): bool => $this->isCompletedTask($task))->count();
            $cursor = $weekEnd->addDay();
        }

        return [
            'month_label' => $monthStart->format('F Y'),
            'labels' => $labels,
            'completed' => $completed,
            'incomplete' => $incomplete,
        ];
    }

    /**
     * @param  Collection<int, ProjectTask>  $tasks
     * @return Collection<int, array<string, mixed>>
     */
    private function projectDepartmentGroups(Project $project, Collection $tasks, ?string $ownDepartmentId): Collection
    {
        $departments = collect();

        foreach ($project->memberships as $membership) {
            $department = $membership->employee?->deployment?->department;
            if ($department !== null) {
                $departments->put((string) $department->id, [
                    'id' => (string) $department->id,
                    'name' => trim((string) $department->name),
                ]);
            }
        }

        foreach ($tasks as $task) {
            $department = $task->employee?->deployment?->department;
            if ($department !== null) {
                $departments->put((string) $department->id, [
                    'id' => (string) $department->id,
                    'name' => trim((string) $department->name),
                ]);
            }
        }

        return $departments
            ->sortBy('name')
            ->values()
            ->map(function (array $department) use ($project, $tasks, $ownDepartmentId): array {
                $departmentTasks = $tasks
                    ->filter(fn (ProjectTask $task): bool => (string) ($task->employee?->deployment?->current_department_id ?? '') === $department['id'])
                    ->values();
                $completedCount = $departmentTasks->filter(fn (ProjectTask $task): bool => $this->isCompletedTask($task))->count();

                return [
                    'id' => $department['id'],
                    'name' => $department['name'],
                    'is_own_department' => $ownDepartmentId !== null && $department['id'] === $ownDepartmentId,
                    'store_url' => route('project_management.projects.tasks.store', $project),
                    'total_tasks' => $departmentTasks->count(),
                    'completed_tasks' => $completedCount,
                    'completion_rate' => $this->percentage($completedCount, $departmentTasks->count()),
                    'tasks' => $departmentTasks
                        ->map(fn (ProjectTask $task): array => $this->projectTaskValue($task, $ownDepartmentId))
                        ->values(),
                ];
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function projectTaskValue(?ProjectTask $projectTask, ?string $ownDepartmentId): array
    {
        if (! $projectTask instanceof ProjectTask) {
            return [];
        }

        $departmentId = trim((string) ($projectTask->employee?->deployment?->current_department_id ?? ''));
        $isCompleted = $this->isCompletedTask($projectTask);

        return [
            'id' => (string) $projectTask->id,
            'title' => trim((string) $projectTask->title),
            'description' => trim((string) ($projectTask->description ?? '')),
            'blockers' => trim((string) ($projectTask->blockers ?? '')),
            'attachment_path' => trim((string) ($projectTask->attachment_path ?? '')),
            'status' => trim((string) $projectTask->status),
            'priority' => trim((string) $projectTask->priority),
            'is_completed' => $isCompleted,
            'start_date' => $projectTask->start_date?->toDateString() ?? '',
            'due_date' => $projectTask->due_date?->toDateString() ?? '',
            'due_label' => $this->taskDueLabel($projectTask->due_date, $isCompleted),
            'employee_id' => trim((string) $projectTask->employee_id),
            'assignee_label' => trim((string) ($projectTask->employee?->profile?->name ?? $projectTask->employee?->user?->username ?? 'Staff')),
            'can_toggle' => $ownDepartmentId !== null && $departmentId === $ownDepartmentId,
            'toggle_url' => route('project_management.projects.tasks.toggle', [$projectTask->project_id, $projectTask]),
            'update_url' => route('project_management.projects.tasks.update', [$projectTask->project_id, $projectTask]),
            'delete_url' => route('project_management.projects.tasks.destroy', [$projectTask->project_id, $projectTask]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProjectTaskPayload(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'blockers' => ['nullable', 'string', 'max:5000'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);
    }

    private function canManageProjectTask(Project $project, ProjectTask $projectTask, string $departmentId): bool
    {
        if ((string) $projectTask->project_id !== (string) $project->id) {
            return false;
        }

        if ($projectTask->overtime_id !== null && trim((string) $projectTask->overtime_id) !== '') {
            return false;
        }

        $projectTask->loadMissing('employee.deployment:id,employee_id,current_department_id');

        return (string) ($projectTask->employee?->deployment?->current_department_id ?? '') === $departmentId;
    }

    private function projectStatusLabel(string $status): string
    {
        return match (strtolower(trim($status))) {
            'completed', 'done' => 'Completed',
            'cancelled' => 'Cancelled',
            'active' => 'In Progress',
            default => 'Pending',
        };
    }

    private function projectStatusClass(string $status): string
    {
        return match (strtolower(trim($status))) {
            'completed', 'done' => 'success',
            'cancelled' => 'danger',
            'active' => 'primary',
            default => 'warning',
        };
    }

    private function projectDateRangeLabel(?CarbonInterface $startDate, ?CarbonInterface $endDate): string
    {
        if ($startDate === null && $endDate === null) {
            return '-';
        }

        if ($startDate === null) {
            return $endDate?->format('d M Y') ?? '-';
        }

        if ($endDate === null) {
            return $startDate->format('d M Y');
        }

        if ($startDate->format('M Y') === $endDate->format('M Y')) {
            return $startDate->format('d').' - '.$endDate->format('d M Y');
        }

        return $startDate->format('d M Y').' - '.$endDate->format('d M Y');
    }

    private function projectDurationLabel(?CarbonInterface $startDate, ?CarbonInterface $endDate): string
    {
        if ($startDate === null || $endDate === null) {
            return '-';
        }

        return ((int) $startDate->diffInDays($endDate) + 1).' Days';
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
            return abs((int) $days).' day'.(abs((int) $days) === 1 ? '' : 's').' overdue';
        }

        if ((int) $days === 0) {
            return 'Due today';
        }

        return 'Due in '.(int) $days.' day'.((int) $days === 1 ? '' : 's');
    }

    private function isCompletedTask(ProjectTask $projectTask): bool
    {
        return $projectTask->status === 'completed' || $projectTask->completed_at !== null;
    }

    private function isOverdueTask(ProjectTask $projectTask): bool
    {
        if ($this->isCompletedTask($projectTask) || $projectTask->due_date === null) {
            return false;
        }

        return CarbonImmutable::instance($projectTask->due_date)->startOfDay()
            ->lt(CarbonImmutable::now('Asia/Jakarta')->startOfDay());
    }

    private function percentage(int $value, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }

    private function nullableStringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
