<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EventDivision;
use App\Models\Project;
use App\Models\ProjectDivisionEvent;
use App\Models\ProjectMember;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Throwable;

class ProjectController extends Controller
{
    public function index(): View
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $authenticatedUserId = $this->authenticatedUserId($authenticatedUser);
        $canManageEventProjects = $this->authenticatedEmployeeIsEventProjectAdmin($authenticatedUser);
        $canCreateProjects = $this->authenticatedEmployeeIsSupervisor($authenticatedUser);

        return view('project_management.projects.index', array_merge($this->profileMetricData($employeeId), [
            'canManageProjects' => $canCreateProjects,
            'projectCompanyOptions' => $canCreateProjects ? $this->projectCompanyOptions() : collect(),
            'projectCityOptions' => $canCreateProjects ? $this->projectCityOptions() : collect(),
            'projectCards' => $employeeId !== null
                ? $this->projectCards($employeeId, $authenticatedUserId, $canManageEventProjects)
                : collect(),
            'projectProvinceOptions' => $canCreateProjects ? $this->projectProvinceOptions() : collect(),
            'projectStaffOptions' => $canCreateProjects ? $this->projectStaffOptions() : collect(),
            'projectStoreUrl' => route('project_management.projects.store'),
        ]));
    }

    public function storeProject(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $authenticatedUserId = $this->authenticatedUserId($authenticatedUser);

        if ($employeeId === null || ! $this->authenticatedEmployeeIsSupervisor($authenticatedUser)) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya supervisor yang dapat menambahkan project.',
            ], 403);
        }

        $validated = $this->validateProjectPayload($request);
        $staffEmployeeIds = $this->projectStaffEmployeeIdsWithPic(
            collect($validated['staff_employee_ids']),
            $employeeId,
        );
        $projectImagePath = $this->storeProjectImageFile($request);

        try {
            $project = DB::transaction(function () use ($authenticatedUserId, $projectImagePath, $staffEmployeeIds, $validated): Project {
                $project = Project::query()->create([
                    'company_id' => $validated['company_id'],
                    'code' => $this->generateProjectCodeFromName((string) $validated['name'], (string) $validated['company_id']),
                    'name' => trim((string) $validated['name']),
                    'description' => $this->nullableStringValue($validated['description'] ?? null),
                    'image_path' => $projectImagePath,
                    'client_name' => $this->nullableStringValue($validated['client_name'] ?? null),
                    'province_code' => $this->nullableStringValue($validated['province_code'] ?? null),
                    'city_code' => $this->nullableStringValue($validated['city_code'] ?? null),
                    'address' => $this->nullableStringValue($validated['address'] ?? null),
                    'live_event_start_date' => $validated['live_event_start_date'] ?? null,
                    'live_event_end_date' => $validated['live_event_end_date'] ?? null,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'status' => strtolower(trim((string) ($validated['status'] ?? 'active'))),
                    'created_by' => $authenticatedUserId,
                ]);

                $this->syncProjectMembers($project, $staffEmployeeIds, (string) $validated['start_date']);
                $this->syncProjectDivisionEvents($project, $staffEmployeeIds);

                return $project;
            });
        } catch (Throwable $exception) {
            if ($projectImagePath !== null) {
                Storage::disk('public')->delete($projectImagePath);
            }

            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Project berhasil ditambahkan.',
            'redirect_url' => route('project_management.projects.detail', $project),
        ]);
    }

    public function updateProject(Request $request, Project $project): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $authenticatedUserId = $this->authenticatedUserId($authenticatedUser);
        $canManageEventProjects = $this->authenticatedEmployeeIsEventProjectAdmin($authenticatedUser);

        if (
            $employeeId === null
            || ! $this->authenticatedEmployeeIsSupervisor($authenticatedUser)
            || ! $this->employeeCanViewProject($project, $employeeId, $authenticatedUserId, $canManageEventProjects)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya supervisor yang dapat memperbarui project ini.',
            ], 403);
        }

        $validated = $this->validateProjectPayload($request);
        $staffEmployeeIds = $this->projectStaffEmployeeIdsWithPic(
            collect($validated['staff_employee_ids']),
            $this->projectCreatorEmployeeId($project),
        );
        $projectImagePath = $this->storeProjectImageFile($request);
        $previousProjectImagePath = $this->nullableStringValue($project->image_path);

        try {
            DB::transaction(function () use ($project, $projectImagePath, $staffEmployeeIds, $validated): void {
                $project->update([
                    'company_id' => $validated['company_id'],
                    'code' => $this->generateProjectCodeFromName((string) $validated['name'], (string) $validated['company_id'], (string) $project->id),
                    'name' => trim((string) $validated['name']),
                    'description' => $this->nullableStringValue($validated['description'] ?? null),
                    'image_path' => $projectImagePath ?? $project->image_path,
                    'client_name' => $this->nullableStringValue($validated['client_name'] ?? null),
                    'province_code' => $this->nullableStringValue($validated['province_code'] ?? null),
                    'city_code' => $this->nullableStringValue($validated['city_code'] ?? null),
                    'address' => $this->nullableStringValue($validated['address'] ?? null),
                    'live_event_start_date' => $validated['live_event_start_date'] ?? null,
                    'live_event_end_date' => $validated['live_event_end_date'] ?? null,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'status' => strtolower(trim((string) ($validated['status'] ?? $project->status ?? 'active'))),
                ]);

                $this->syncProjectMembers($project, $staffEmployeeIds, (string) $validated['start_date']);
                $this->syncProjectDivisionEvents($project, $staffEmployeeIds);
            });
        } catch (Throwable $exception) {
            if ($projectImagePath !== null) {
                Storage::disk('public')->delete($projectImagePath);
            }

            throw $exception;
        }

        if ($projectImagePath !== null && $previousProjectImagePath !== null && $previousProjectImagePath !== $projectImagePath) {
            Storage::disk('public')->delete($previousProjectImagePath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Project berhasil diperbarui.',
            'redirect_url' => route('project_management.projects.detail', $project),
        ]);
    }

    public function destroyProject(Project $project): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $authenticatedUserId = $this->authenticatedUserId($authenticatedUser);
        $canManageEventProjects = $this->authenticatedEmployeeIsEventProjectAdmin($authenticatedUser);

        if (
            $employeeId === null
            || ! $this->authenticatedEmployeeIsSupervisor($authenticatedUser)
            || ! $this->employeeCanViewProject($project, $employeeId, $authenticatedUserId, $canManageEventProjects)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya supervisor yang dapat menghapus project ini.',
            ], 403);
        }

        DB::transaction(function () use ($project): void {
            ProjectTask::query()
                ->where('project_id', $project->id)
                ->delete();

            ProjectMember::query()
                ->where('project_id', $project->id)
                ->update([
                    'left_at' => CarbonImmutable::now('Asia/Jakarta')->toDateString(),
                    'status' => 'inactive',
                ]);

            $project->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Project berhasil dihapus.',
            'redirect_url' => route('project_management.projects'),
        ]);
    }

    public function detailFallback(): RedirectResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $authenticatedUserId = $this->authenticatedUserId($authenticatedUser);
        $canManageEventProjects = $this->authenticatedEmployeeIsEventProjectAdmin($authenticatedUser);
        $project = $employeeId !== null
            ? $this->projectsForEmployee($employeeId, $authenticatedUserId, $canManageEventProjects)->first()
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
        $authenticatedUserId = $this->authenticatedUserId($authenticatedUser);
        $canManageEventProjects = $this->authenticatedEmployeeIsEventProjectAdmin($authenticatedUser);
        $canManageProject = $canManageEventProjects || $this->userIsProjectCreator($project, $authenticatedUserId);
        abort_if($employeeId === null || ! $this->employeeCanViewProject($project, $employeeId, $authenticatedUserId, $canManageEventProjects), 403);

        $project->loadMissing([
            'creator:id,username',
            'memberships.employee:id,user_id',
            'memberships.employee.user:id,username',
            'memberships.employee.profile:id,employee_id,name,profile_picture_path',
            'memberships.employee.deployment:id,employee_id,current_event_division_id',
            'projectDivisionEvents:id,project_id,event_division_id,google_drive_url,folder_id,status',
            'projectDivisionEvents.eventDivision:id,title,sub_title',
        ]);

        $tasks = ProjectTask::query()
            ->where('project_id', $project->id)
            ->with([
                'assignedBy:id,username',
                'employee:id,user_id',
                'employee.user:id,username',
                'employee.profile:id,employee_id,name,profile_picture_path',
            ])
            ->orderByRaw('COALESCE(due_date, start_date, created_at) ASC')
            ->get();

        $projectDivisionGroups = $this->projectDivisionGroups($project, $tasks, $employeeId, $canManageProject, $canManageEventProjects);

        return view('project_management.projects.detail', array_merge($this->profileMetricData($employeeId), [
            'projectDetail' => $this->projectDetailValue($project),
            'projectDivisionGroups' => $projectDivisionGroups,
            'projectSummary' => $this->projectSummaryValue($project, $tasks),
            'projectTaskTimeline' => $this->projectTaskTimelineValue($project, $tasks),
        ]));
    }

    public function updateEventDivisionGoogleDrive(Request $request, Project $project, EventDivision $eventDivision): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $canManageEventProjects = $this->authenticatedEmployeeIsEventProjectAdmin($authenticatedUser);

        if (
            $employeeId === null
            || ! $canManageEventProjects
            || ! $this->employeeCanViewProject($project, $employeeId, $this->authenticatedUserId($authenticatedUser), true)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk memperbarui Google Drive division project ini.',
            ], 403);
        }

        $validated = $request->validate([
            'google_drive_url' => ['nullable', 'url', 'max:2048'],
            'folder_id' => ['nullable', 'string', 'max:255'],
        ]);

        $googleDriveUrl = $this->nullableStringValue($validated['google_drive_url'] ?? null);
        $folderId = $this->nullableStringValue($validated['folder_id'] ?? null);

        ProjectDivisionEvent::query()->updateOrCreate(
            [
                'project_id' => $project->id,
                'event_division_id' => $eventDivision->id,
            ],
            [
                'google_drive_url' => $googleDriveUrl,
                'folder_id' => $folderId,
                'status' => 'active',
            ],
        );

        return response()->json([
            'success' => true,
            'message' => 'Google Drive division berhasil diperbarui.',
            'google_drive_url' => $googleDriveUrl ?? '',
            'folder_id' => $folderId ?? '',
        ]);
    }

    public function toggleTask(Request $request, Project $project, ProjectTask $projectTask): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $authenticatedUserId = $this->authenticatedUserId($authenticatedUser);
        $canManageProject = $this->authenticatedEmployeeIsEventProjectAdmin($authenticatedUser) || $this->userIsProjectCreator($project, $authenticatedUserId);

        if (
            $employeeId === null
            || ! $this->employeeCanViewProject($project, $employeeId, $authenticatedUserId, $canManageProject)
            || ! $this->canManageProjectTask($project, $projectTask, $employeeId, $canManageProject)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengubah task project ini.',
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
            'message' => $isCompleted ? 'Task division berhasil diselesaikan.' : 'Task division dikembalikan ke pending.',
            'task' => $this->projectTaskValue($projectTask->fresh(['assignedBy:id,username', 'employee:id,user_id', 'employee.user:id,username', 'employee.profile:id,employee_id,name']), $employeeId, $canManageProject),
        ]);
    }

    public function storeTask(Request $request, Project $project): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $authenticatedUserId = $this->authenticatedUserId($authenticatedUser);
        $canManageEventProjects = $this->authenticatedEmployeeIsEventProjectAdmin($authenticatedUser);

        if ($employeeId === null || ! $this->employeeCanViewProject($project, $employeeId, $authenticatedUserId, $canManageEventProjects)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menambahkan task project ini.',
            ], 403);
        }

        $validated = $this->validateProjectTaskPayload($request, true);
        $taskEmployeeId = $this->validatedProjectDetailTaskEmployeeId($project, $employeeId, $canManageEventProjects, $validated);
        if ($taskEmployeeId === false) {
            return response()->json([
                'success' => false,
                'message' => 'Staff task harus merupakan member aktif project ini pada event division yang dipilih.',
            ], 422);
        }

        $status = strtolower(trim((string) $validated['status']));

        ProjectTask::query()->create([
            'project_id' => $project->id,
            'event_division_id' => $validated['event_division_id'],
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

        return response()->json([
            'success' => true,
            'message' => 'Task project berhasil ditambahkan.',
        ]);
    }

    public function updateTask(Request $request, Project $project, ProjectTask $projectTask): JsonResponse
    {
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $authenticatedUserId = $this->authenticatedUserId($authenticatedUser);
        $canManageProject = $this->authenticatedEmployeeIsEventProjectAdmin($authenticatedUser) || $this->userIsProjectCreator($project, $authenticatedUserId);

        if (
            $employeeId === null
            || ! $this->employeeCanViewProject($project, $employeeId, $authenticatedUserId, $canManageProject)
            || ! $this->canManageProjectTask($project, $projectTask, $employeeId, $canManageProject)
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
        $authenticatedUser = Auth::user();
        $employeeId = $this->authenticatedEmployeeId($authenticatedUser);
        $authenticatedUserId = $this->authenticatedUserId($authenticatedUser);
        $canManageProject = $this->authenticatedEmployeeIsEventProjectAdmin($authenticatedUser) || $this->userIsProjectCreator($project, $authenticatedUserId);

        if (
            $employeeId === null
            || ! $this->employeeCanViewProject($project, $employeeId, $authenticatedUserId, $canManageProject)
            || ! $this->canManageProjectTask($project, $projectTask, $employeeId, $canManageProject)
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

        $authenticatedUser->loadMissing('employee:id,user_id,is_event_project_admin');
        $employeeId = trim((string) ($authenticatedUser->employee?->id ?? ''));

        return $employeeId !== '' ? $employeeId : null;
    }

    private function authenticatedUserId(?User $authenticatedUser): ?string
    {
        if (! $authenticatedUser instanceof User) {
            return null;
        }

        $userId = trim((string) $authenticatedUser->id);

        return $userId !== '' ? $userId : null;
    }

    private function authenticatedEmployeeIsEventProjectAdmin(?User $authenticatedUser): bool
    {
        if (! $authenticatedUser instanceof User) {
            return false;
        }

        return (bool) $authenticatedUser->employee()->value('is_event_project_admin');
    }

    private function authenticatedEmployeeIsSupervisor(?User $authenticatedUser): bool
    {
        if (! $authenticatedUser instanceof User) {
            return false;
        }

        $authenticatedUser->loadMissing('employee.deployment.position', 'employee.deployment.positions');

        return $authenticatedUser->employee?->hasPositionName('Supervisor') ?? false;
    }

    private function employeeCanViewProject(Project $project, string $employeeId, ?string $userId = null, bool $canManageEventProjects = false): bool
    {
        if ($canManageEventProjects || $this->userIsProjectCreator($project, $userId)) {
            return true;
        }

        return $project->memberships()
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->whereNull('left_at')
            ->exists();
    }

    private function userIsProjectCreator(Project $project, ?string $userId): bool
    {
        return $userId !== null && $userId !== '' && (string) $project->created_by === $userId;
    }

    private function projectsForEmployee(string $employeeId, ?string $userId = null, bool $canManageEventProjects = false): Builder
    {
        $query = Project::query();

        if (! $canManageEventProjects) {
            $query->where(function (Builder $query) use ($employeeId, $userId): void {
                $query->whereHas('memberships', function (Builder $membershipQuery) use ($employeeId): void {
                    $membershipQuery
                        ->where('employee_id', $employeeId)
                        ->where('status', 'active')
                        ->whereNull('left_at');
                });

                if ($userId !== null) {
                    $query->orWhere('created_by', $userId);
                }
            });
        }

        return $query
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
            ->with([
                'memberships' => function (HasMany $query): void {
                    $query
                        ->where('status', 'active')
                        ->whereNull('left_at')
                        ->with([
                            'employee:id,user_id',
                            'employee.user:id,username',
                            'employee.profile:id,employee_id,name,profile_picture_path',
                            'employee.deployment:id,employee_id,current_position_id',
                            'employee.deployment.position:id,name',
                            'employee.deployment.positions:id,name',
                        ]);
                },
            ])
            ->orderByDesc('start_date')
            ->orderBy('name');
    }

    /**
     * @param  Collection<int, string>  $staffEmployeeIds
     */
    private function syncProjectMembers(Project $project, Collection $staffEmployeeIds, string $joinedAt): void
    {
        $staffEmployees = Employee::query()
            ->whereIn('id', $staffEmployeeIds)
            ->get(['id'])
            ->keyBy('id');

        ProjectMember::query()
            ->where('project_id', $project->id)
            ->whereNotIn('employee_id', $staffEmployeeIds)
            ->update([
                'left_at' => CarbonImmutable::now('Asia/Jakarta')->toDateString(),
                'status' => 'inactive',
            ]);

        foreach ($staffEmployeeIds as $staffEmployeeId) {
            if (! $staffEmployees->has($staffEmployeeId)) {
                continue;
            }

            $projectMember = ProjectMember::query()
                ->where('project_id', $project->id)
                ->where('employee_id', $staffEmployeeId)
                ->first();

            if ($projectMember instanceof ProjectMember) {
                $projectMember->update([
                    'left_at' => null,
                    'status' => 'active',
                ]);

                continue;
            }

            ProjectMember::query()->create([
                'project_id' => $project->id,
                'employee_id' => $staffEmployeeId,
                'joined_at' => $joinedAt,
                'status' => 'active',
            ]);
        }
    }

    /**
     * @param  Collection<int, string>  $staffEmployeeIds
     */
    private function syncProjectDivisionEvents(Project $project, Collection $staffEmployeeIds): void
    {
        $eventDivisionIds = Employee::query()
            ->whereIn('id', $staffEmployeeIds)
            ->with('deployment:id,employee_id,current_event_division_id')
            ->get(['id'])
            ->map(fn (Employee $employee): ?string => $this->nullableStringValue($employee->deployment?->current_event_division_id))
            ->filter()
            ->unique()
            ->values();

        foreach ($eventDivisionIds as $eventDivisionId) {
            ProjectDivisionEvent::query()->firstOrCreate(
                [
                    'project_id' => $project->id,
                    'event_division_id' => $eventDivisionId,
                ],
                [
                    'status' => 'active',
                ],
            );
        }
    }

    /**
     * @param  Collection<int, mixed>  $staffEmployeeIds
     * @return Collection<int, string>
     */
    private function projectStaffEmployeeIdsWithPic(Collection $staffEmployeeIds, ?string $picEmployeeId): Collection
    {
        $normalizedPicEmployeeId = $this->nullableStringValue($picEmployeeId);

        return $staffEmployeeIds
            ->when($normalizedPicEmployeeId !== null, fn (Collection $employeeIds): Collection => $employeeIds->push($normalizedPicEmployeeId))
            ->map(fn (mixed $employeeId): string => trim((string) $employeeId))
            ->filter()
            ->unique()
            ->values();
    }

    private function projectCreatorEmployeeId(Project $project): ?string
    {
        $creatorUserId = $this->nullableStringValue($project->created_by);

        if ($creatorUserId === null) {
            return null;
        }

        $creatorEmployeeId = Employee::query()
            ->where('user_id', $creatorUserId)
            ->value('id');

        return $this->nullableStringValue($creatorEmployeeId);
    }

    /**
     * @return Collection<int, array{id:string, name:string}>
     */
    private function projectCompanyOptions(): Collection
    {
        return Company::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Company $company): array => [
                'id' => (string) $company->id,
                'name' => trim((string) $company->name),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{code:string, name:string}>
     */
    private function projectProvinceOptions(): Collection
    {
        return Province::query()
            ->orderBy('name')
            ->get(['code', 'name'])
            ->map(fn (Province $province): array => [
                'code' => (string) $province->code,
                'name' => trim((string) $province->name),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{code:string, province_code:string, name:string}>
     */
    private function projectCityOptions(): Collection
    {
        return City::query()
            ->orderBy('name')
            ->get(['code', 'province_code', 'name'])
            ->map(fn (City $city): array => [
                'code' => (string) $city->code,
                'province_code' => (string) $city->province_code,
                'name' => trim((string) $city->name),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{id:string, label:string}>
     */
    private function projectStaffOptions(): Collection
    {
        return Employee::query()
            ->with([
                'profile:id,employee_id,name',
                'user:id,username,email',
                'deployment:id,employee_id,current_event_division_id',
                'deployment.eventDivision:id,title',
            ])
            ->where('status', 'Active')
            ->whereHas('deployment', function (Builder $query): void {
                $query->whereNotNull('current_event_division_id');
            })
            ->orderBy('employee_code')
            ->get(['id', 'user_id', 'employee_code', 'status'])
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'label' => $this->employeeDisplayName($employee).' - '.trim((string) ($employee->deployment?->eventDivision?->title ?? '')),
                'division_id' => (string) $employee->deployment?->current_event_division_id,
                'division_name' => trim((string) ($employee->deployment?->eventDivision?->title ?? '')),
            ])
            ->sortBy('label')
            ->values();
    }

    private function employeeProjectOptionLabel(Employee $employee): string
    {
        $employeeName = $this->employeeDisplayName($employee);
        $positionLabel = $this->employeePositionLabel($employee);

        return $positionLabel !== '' ? $employeeName.' - '.$positionLabel : $employeeName.' - Staff';
    }

    private function employeePositionLabel(Employee $employee): string
    {
        return collect([$employee->deployment?->position?->name])
            ->merge($employee->deployment?->positions?->pluck('name') ?? [])
            ->map(fn (mixed $positionName): string => trim((string) $positionName))
            ->filter()
            ->unique()
            ->implode(', ');
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
    private function projectCards(string $employeeId, ?string $userId = null, bool $canManageEventProjects = false): Collection
    {
        return $this->projectsForEmployee($employeeId, $userId, $canManageEventProjects)
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
            'image_url' => $this->projectImageUrl($project->image_path ?? null),
            'client_name' => trim((string) ($project->client_name ?? '-')),
            'status_label' => $this->projectStatusLabel((string) $project->status),
            'status_class' => $this->projectStatusClass((string) $project->status),
            'date_label' => $this->projectDateRangeLabel($project->start_date, $project->end_date),
            'due_label' => $project->end_date?->format('d M Y') ?? '-',
            'team_count' => (int) ($project->active_members_count ?? 0),
            'team_members' => $project->memberships
                ->map(fn ($membership): array => $this->teamMemberValue($membership->employee))
                ->filter(fn (array $teamMember): bool => $teamMember !== [])
                ->values(),
            'task_count' => $totalTasks,
            'completed_count' => $completedTasks,
            'completion_rate' => $this->percentage($completedTasks, $totalTasks),
            'detail_url' => route('project_management.projects.detail', $project),
            'update_url' => route('project_management.projects.update', $project),
            'delete_url' => route('project_management.projects.destroy', $project),
            'form_value' => [
                'id' => (string) $project->id,
                'company_id' => (string) ($project->company_id ?? ''),
                'staff_employee_ids' => $project->memberships
                    ->pluck('employee_id')
                    ->map(fn (mixed $employeeId): string => (string) $employeeId)
                    ->filter()
                    ->values(),
                'staff_members' => $project->memberships
                    ->filter(fn ($membership): bool => $membership->employee instanceof Employee)
                    ->map(fn ($membership): array => [
                        'id' => (string) $membership->employee->id,
                        'label' => $this->employeeProjectOptionLabel($membership->employee),
                    ])
                    ->values(),
                'name' => trim((string) $project->name),
                'description' => trim((string) ($project->description ?? '')),
                'client_name' => trim((string) ($project->client_name ?? '')),
                'province_code' => trim((string) ($project->province_code ?? '')),
                'city_code' => trim((string) ($project->city_code ?? '')),
                'address' => trim((string) ($project->address ?? '')),
                'live_event_start_date' => $project->live_event_start_date?->toDateString() ?? '',
                'live_event_end_date' => $project->live_event_end_date?->toDateString() ?? '',
                'start_date' => $project->start_date?->toDateString() ?? '',
                'end_date' => $project->end_date?->toDateString() ?? '',
                'update_url' => route('project_management.projects.update', $project),
                'delete_url' => route('project_management.projects.destroy', $project),
            ],
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
            'image_url' => $this->projectImageUrl($project->image_path ?? null),
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

    private function profilePictureUrl(mixed $profilePicturePath): string
    {
        $defaultAvatarUrl = asset('assets/default_user.jpg');
        $profilePicturePath = trim((string) $profilePicturePath);

        if ($profilePicturePath === '') {
            return $defaultAvatarUrl;
        }

        if (Str::startsWith($profilePicturePath, ['http://', 'https://'])) {
            return $profilePicturePath;
        }

        $publicPath = ltrim($profilePicturePath, '/');
        $storagePath = Str::startsWith($publicPath, 'storage/')
            ? Str::after($publicPath, 'storage/')
            : $publicPath;

        if (Storage::disk('public')->exists($storagePath)) {
            return asset('storage/'.$storagePath);
        }

        return File::exists(public_path($publicPath)) ? asset($publicPath) : $defaultAvatarUrl;
    }

    private function projectImageUrl(mixed $imagePath): string
    {
        $defaultProjectImageUrl = asset('assets/images/files/folder.avif');
        $imagePath = trim((string) $imagePath);

        if ($imagePath === '') {
            return $defaultProjectImageUrl;
        }

        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
            return $imagePath;
        }

        $publicPath = ltrim($imagePath, '/');
        $storagePath = Str::startsWith($publicPath, 'storage/')
            ? Str::after($publicPath, 'storage/')
            : $publicPath;

        if (Storage::disk('public')->exists($storagePath)) {
            return asset('storage/'.$storagePath);
        }

        return File::exists(public_path($publicPath)) ? asset($publicPath) : $defaultProjectImageUrl;
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
    private function projectDivisionGroups(Project $project, Collection $tasks, ?string $employeeId, bool $canManageProject, bool $canManageGoogleDrive): Collection
    {
        $projectMemberEmployees = $project->memberships
            ->filter(fn ($membership): bool => $membership->status === 'active' && $membership->left_at === null && $membership->employee instanceof Employee)
            ->map(fn ($membership): Employee => $membership->employee)
            ->values();

        $activeProjectDivisionEvents = $project->projectDivisionEvents
            ->filter(fn (ProjectDivisionEvent $projectDivisionEvent): bool => $projectDivisionEvent->status === 'active');

        $divisionDriveUrls = $activeProjectDivisionEvents
            ->keyBy(fn (ProjectDivisionEvent $projectDivisionEvent): string => (string) $projectDivisionEvent->event_division_id)
            ->map(fn (ProjectDivisionEvent $projectDivisionEvent): string => trim((string) ($projectDivisionEvent->google_drive_url ?? '')));
        $divisionFolderIds = $activeProjectDivisionEvents
            ->keyBy(fn (ProjectDivisionEvent $projectDivisionEvent): string => (string) $projectDivisionEvent->event_division_id)
            ->map(fn (ProjectDivisionEvent $projectDivisionEvent): string => trim((string) ($projectDivisionEvent->folder_id ?? '')));

        $relevantDivisionIds = $activeProjectDivisionEvents
            ->pluck('event_division_id')
            ->merge($tasks->pluck('event_division_id'))
            ->map(fn (mixed $eventDivisionId): ?string => $this->nullableStringValue($eventDivisionId))
            ->filter()
            ->unique()
            ->values();

        return EventDivision::query()
            ->where('status', 'active')
            ->whereIn('id', $relevantDivisionIds)
            ->orderBy('title')
            ->get(['id', 'title', 'sub_title'])
            ->map(function (EventDivision $eventDivision) use ($project, $tasks, $employeeId, $canManageProject, $canManageGoogleDrive, $projectMemberEmployees, $divisionDriveUrls, $divisionFolderIds): array {
                $divisionId = (string) $eventDivision->id;
                $divisionTasks = $tasks
                    ->filter(fn (ProjectTask $task): bool => (string) $task->event_division_id === $divisionId)
                    ->values();
                $completedCount = $divisionTasks->filter(fn (ProjectTask $task): bool => $this->isCompletedTask($task))->count();
                $taskAssigneeOptions = $projectMemberEmployees
                    ->filter(fn (Employee $employee): bool => (string) ($employee->deployment?->current_event_division_id ?? '') === $divisionId)
                    ->map(fn (Employee $employee): array => [
                        'id' => (string) $employee->id,
                        'name' => $this->employeeDisplayName($employee),
                    ])
                    ->values();
                $employeeCanCreateOwnTask = $employeeId !== null
                    && $taskAssigneeOptions->contains(fn (array $taskAssigneeOption): bool => (string) $taskAssigneeOption['id'] === $employeeId);
                $visibleTaskAssigneeOptions = $canManageGoogleDrive
                    ? $taskAssigneeOptions
                    : $taskAssigneeOptions
                        ->filter(fn (array $taskAssigneeOption): bool => (string) $taskAssigneeOption['id'] === (string) $employeeId)
                        ->values();

                return [
                    'id' => $divisionId,
                    'name' => trim((string) $eventDivision->title),
                    'sub_title' => trim((string) ($eventDivision->sub_title ?? '')),
                    'google_drive_url' => $divisionDriveUrls->get($divisionId, ''),
                    'folder_id' => $divisionFolderIds->get($divisionId, ''),
                    'can_create_task' => ($canManageGoogleDrive || $employeeCanCreateOwnTask) && $visibleTaskAssigneeOptions->isNotEmpty(),
                    'can_manage_drive' => $canManageGoogleDrive,
                    'task_assignee_options' => $visibleTaskAssigneeOptions,
                    'store_url' => route('project_management.projects.tasks.store', $project),
                    'drive_update_url' => route('project_management.projects.event-divisions.google-drive.update', [$project, $divisionId]),
                    'total_tasks' => $divisionTasks->count(),
                    'completed_tasks' => $completedCount,
                    'completion_rate' => $this->percentage($completedCount, $divisionTasks->count()),
                    'tasks' => $divisionTasks
                        ->map(fn (ProjectTask $task): array => $this->projectTaskValue($task, $employeeId, $canManageProject))
                        ->values(),
                ];
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function projectTaskValue(?ProjectTask $projectTask, ?string $employeeId, bool $canManageProject = false): array
    {
        if (! $projectTask instanceof ProjectTask) {
            return [];
        }

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
            'can_toggle' => $canManageProject || ($employeeId !== null && (string) $projectTask->employee_id === $employeeId),
            'toggle_url' => route('project_management.projects.tasks.toggle', [$projectTask->project_id, $projectTask]),
            'update_url' => route('project_management.projects.tasks.update', [$projectTask->project_id, $projectTask]),
            'delete_url' => route('project_management.projects.tasks.destroy', [$projectTask->project_id, $projectTask]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProjectPayload(Request $request): array
    {
        $validated = $request->validate([
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'staff_employee_ids' => ['required', 'array', 'min:1'],
            'staff_employee_ids.*' => ['required', 'uuid', 'exists:employees,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'project_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'province_code' => ['nullable', 'required_with:city_code', 'string', 'size:2', 'exists:indonesia_provinces,code'],
            'city_code' => ['nullable', 'required_with:province_code', 'string', 'size:4', 'exists:indonesia_cities,code'],
            'address' => ['nullable', 'string', 'max:2000'],
            'live_event_start_date' => ['nullable', 'date_format:Y-m-d'],
            'live_event_end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:live_event_start_date'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:active,pending,completed,cancelled'],
        ]);

        $provinceCode = $this->nullableStringValue($validated['province_code'] ?? null);
        $cityCode = $this->nullableStringValue($validated['city_code'] ?? null);

        if (
            $provinceCode !== null
            && $cityCode !== null
            && ! City::query()->where('code', $cityCode)->where('province_code', $provinceCode)->exists()
        ) {
            throw ValidationException::withMessages([
                'city_code' => 'Kabupaten/kota harus sesuai dengan provinsi yang dipilih.',
            ]);
        }

        return $validated;
    }

    private function generateProjectCodeFromName(string $projectName, string $companyId, ?string $exceptProjectId = null): string
    {
        $baseCode = $this->projectCodeBaseFromName($projectName);
        $code = $baseCode;
        $sequence = 2;

        while (
            Project::query()
                ->where('company_id', $companyId)
                ->where('code', $code)
                ->when($exceptProjectId !== null, function (Builder $query) use ($exceptProjectId): void {
                    $query->whereKeyNot($exceptProjectId);
                })
                ->whereNull('deleted_at')
                ->exists()
        ) {
            $suffix = '-'.$sequence;
            $code = Str::of($baseCode)
                ->limit(255 - strlen($suffix), '')
                ->append($suffix)
                ->toString();
            $sequence++;
        }

        return $code;
    }

    private function projectCodeBaseFromName(string $projectName): string
    {
        $tokens = Str::of($projectName)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', ' ')
            ->squish()
            ->explode(' ')
            ->map(fn (string $token): string => trim($token))
            ->filter()
            ->values();

        if ($tokens->isEmpty()) {
            return 'PROJECT';
        }

        $yearToken = $tokens
            ->first(fn (string $token): bool => preg_match('/^(?:19|20)\d{2}$/', $token) === 1);
        $wordTokens = $tokens
            ->reject(fn (string $token): bool => $token === $yearToken)
            ->values();

        if ($wordTokens->isEmpty()) {
            return $yearToken !== null ? 'PROJECT-'.$yearToken : 'PROJECT';
        }

        $prefix = (string) $wordTokens->first();
        $remainingWords = $wordTokens->slice(1)->values();

        if (strlen($prefix) > 4 || $remainingWords->isEmpty()) {
            $prefix = $wordTokens
                ->take(4)
                ->map(fn (string $token): string => substr($token, 0, 1))
                ->implode('');
            $remainingWords = collect();
        }

        $codeParts = [$prefix];
        $initials = $remainingWords
            ->take(4)
            ->map(fn (string $token): string => substr($token, 0, 1))
            ->implode('');

        if ($initials !== '') {
            $codeParts[] = $initials;
        }

        if (is_string($yearToken) && $yearToken !== '') {
            $codeParts[] = $yearToken;
        }

        return Str::of(implode('-', $codeParts))
            ->limit(240, '')
            ->toString();
    }

    private function storeProjectImageFile(Request $request): ?string
    {
        $projectImageFile = $request->file('project_image');

        if (! $projectImageFile instanceof UploadedFile) {
            return null;
        }

        $originalName = $projectImageFile->getClientOriginalName();
        $sanitizedName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = strtolower((string) ($projectImageFile->getClientOriginalExtension() ?: $projectImageFile->extension()));
        $extension = $extension !== '' ? $extension : 'jpg';
        $storedFileName = now()->format('YmdHis').'_'.Str::random(8).'_'.$sanitizedName.'.'.$extension;
        $storedPath = $projectImageFile->storeAs('project-images', $storedFileName, 'public');

        if ($storedPath === false) {
            throw ValidationException::withMessages([
                'project_image' => 'Gagal menyimpan image project.',
            ]);
        }

        return $storedPath;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProjectTaskPayload(Request $request, bool $requiresEventDivision = false): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'blockers' => ['nullable', 'string', 'max:5000'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'event_division_id' => [$requiresEventDivision ? 'required' : 'nullable', 'uuid', 'exists:event_divisions,id'],
            'assigned_employee_id' => ['nullable', 'uuid', 'exists:employees,id'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);
    }

    private function validatedProjectDetailTaskEmployeeId(Project $project, string $employeeId, bool $canManageProject, array $validated): string|false
    {
        $eventDivisionId = trim((string) ($validated['event_division_id'] ?? ''));
        if ($eventDivisionId === '') {
            return false;
        }

        $requestedEmployeeId = is_string($validated['assigned_employee_id'] ?? null)
            ? trim($validated['assigned_employee_id'])
            : '';
        $taskEmployeeId = $canManageProject && $requestedEmployeeId !== ''
            ? $requestedEmployeeId
            : $employeeId;

        if (! $canManageProject && $taskEmployeeId !== $employeeId) {
            return false;
        }

        return $this->employeeIsActiveProjectMemberInEventDivision($project, $taskEmployeeId, $eventDivisionId)
            ? $taskEmployeeId
            : false;
    }

    private function employeeIsActiveProjectMemberInEventDivision(Project $project, string $employeeId, string $eventDivisionId): bool
    {
        return Employee::query()
            ->whereKey($employeeId)
            ->whereHas('deployment', function (Builder $query) use ($eventDivisionId): void {
                $query->where('current_event_division_id', $eventDivisionId);
            })
            ->whereHas('projectMemberships', function (Builder $query) use ($project): void {
                $query
                    ->where('project_id', $project->id)
                    ->where('status', 'active')
                    ->whereNull('left_at');
            })
            ->exists();
    }

    private function canManageProjectTask(Project $project, ProjectTask $projectTask, ?string $employeeId, bool $canManageProject = false): bool
    {
        if ((string) $projectTask->project_id !== (string) $project->id) {
            return false;
        }

        if ($projectTask->overtime_id !== null && trim((string) $projectTask->overtime_id) !== '') {
            return false;
        }

        if ($canManageProject) {
            return true;
        }

        return $employeeId !== null && (string) $projectTask->employee_id === $employeeId;
    }

    private function employeeDisplayName(Employee $employee): string
    {
        $name = trim((string) ($employee->profile?->name ?? $employee->user?->username ?? $employee->user?->email ?? ''));

        return $name !== '' ? $name : (string) $employee->id;
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

        $durationInDays = (int) $startDate->diffInDays($endDate) + 1;

        return $durationInDays.'-Day Duration';
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
