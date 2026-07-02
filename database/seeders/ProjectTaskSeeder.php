<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ProjectTaskSeeder extends Seeder
{
    private const PROJECT_CODE = 'RNB-EVENT-2026';

    private const CROSS_COMPANY_PROJECT_CODE = 'GROUP-COLLAB-2026';

    private const STAFF_USERNAMES = [
        'staff31',
        'staff32',
        'staff33',
        'staff34',
    ];

    private const CROSS_COMPANY_STAFF_USERNAMES = [
        'staff11',
        'staff12',
        'staff13',
        'staff21',
        'staff22',
        'staff23',
        'staff31',
        'staff33',
        'staff14',
        'staff45',
        'staff44',
    ];

    private const CROSS_COMPANY_DEPARTMENT_ASSIGNMENTS = [
        'staff11' => 'Marketing and Promotion',
        'staff12' => 'Marketing and Promotion',
        'staff13' => 'Marketing and Promotion',
        'staff21' => 'Information and Communications Technology',
        'staff22' => 'Information and Communications Technology',
        'staff23' => 'Information and Communications Technology',
        'staff31' => 'Administration, Finance and Legal',
        'staff33' => 'Project Planning and Development',
        'staff14' => 'Project Planning and Development',
        'staff45' => 'Project Planning and Development',
        'staff44' => 'Operations',
    ];

    private const PROJECT_TASKS = [
        [
            'username' => 'staff31',
            'title' => 'Prepare project administration checklist',
            'description' => 'Compile project scope, manpower list, and overtime coordination notes.',
            'blockers' => 'Waiting for updated manpower confirmation from supervisor.',
            'priority' => 'high',
            'status' => 'in_progress',
        ],
        [
            'username' => 'staff31',
            'title' => 'Collect visual asset inventory',
            'description' => 'List all graphic assets required for event production and publication.',
            'blockers' => 'Waiting for latest asset folder link from creative team.',
            'priority' => 'medium',
            'status' => 'pending',
            'is_daily' => true,
        ],
        [
            'username' => 'staff31',
            'title' => 'Document 3D layout requirements',
            'description' => 'Prepare requirement notes for booth dimension, flow, and installation constraints.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff31',
            'title' => 'Prepare project document archive',
            'description' => 'Create archive structure for project briefs, approvals, and field references.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff31',
            'title' => 'Publish technical coordination brief',
            'description' => 'Coordinate publication schedule and technology support requirements.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff32',
            'title' => 'Prepare procurement request draft',
            'description' => 'Draft procurement request items for event production support.',
            'blockers' => 'Waiting for approved vendor item list.',
            'priority' => 'medium',
            'status' => 'pending',
            'is_daily' => true,
        ],
        [
            'username' => 'staff32',
            'title' => 'Create event key visual adaptation',
            'description' => 'Prepare visual assets for venue branding and social media publication.',
            'priority' => 'high',
            'status' => 'pending',
        ],
        [
            'username' => 'staff32',
            'title' => 'Review 3D color and material reference',
            'description' => 'Validate material references against graphic identity and event theme.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff32',
            'title' => 'Prepare design review documentation',
            'description' => 'Document design review notes, revisions, and approval references.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff32',
            'title' => 'Prepare social media asset handoff',
            'description' => 'Package approved social media assets and publish-ready files.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff33',
            'title' => 'Update production timeline tracker',
            'description' => 'Maintain timeline tracker for production milestones and supervisor checkpoints.',
            'priority' => 'medium',
            'status' => 'pending',
            'is_daily' => true,
        ],
        [
            'username' => 'staff33',
            'title' => 'Validate graphic placement on booth layout',
            'description' => 'Check graphic placement compatibility with the 3D booth construction plan.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff33',
            'title' => 'Draft 3D event booth layout',
            'description' => 'Build initial booth layout and spatial reference for supervisor review.',
            'priority' => 'high',
            'status' => 'pending',
        ],
        [
            'username' => 'staff33',
            'title' => 'Export 3D preview documentation',
            'description' => 'Export rendered previews and annotate design assumptions for review.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff33',
            'title' => 'Coordinate technical display specification',
            'description' => 'Confirm screen, lighting, and technical display requirements for the booth.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff34',
            'title' => 'Compile field execution checklist',
            'description' => 'Prepare operational checklist for setup, execution, and teardown needs.',
            'blockers' => 'Waiting for venue access confirmation.',
            'priority' => 'medium',
            'status' => 'pending',
            'is_daily' => true,
        ],
        [
            'username' => 'staff34',
            'title' => 'Capture graphic installation references',
            'description' => 'Prepare reference notes for documenting graphic installation progress.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff34',
            'title' => 'Document booth setup progress',
            'description' => 'Record booth setup status and highlight production risks for supervisor review.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff34',
            'title' => 'Prepare documentation shot list',
            'description' => 'Define photo and video coverage plan for event preparation and execution.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff34',
            'title' => 'Prepare publication archive delivery',
            'description' => 'Organize final publication files, field documentation, and delivery notes.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
    ];

    private const CROSS_COMPANY_PROJECT_TASKS = [
        [
            'username' => 'staff11',
            'title' => 'Prepare cross-company marketing plan',
            'description' => 'Summarize shared campaign objectives, channel plan, and promotion checkpoints.',
            'priority' => 'high',
            'status' => 'completed',
            'completed_offset' => 1,
        ],
        [
            'username' => 'staff11',
            'title' => 'Finalize campaign channel checklist',
            'description' => 'Validate promotion channels and handoff notes for participating companies.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff12',
            'title' => 'Confirm partner campaign materials',
            'description' => 'Check campaign copy, partner logo placement, and publication readiness.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff13',
            'title' => 'Review launch audience segments',
            'description' => 'Validate target audience groups and promotion timing for the launch.',
            'priority' => 'medium',
            'status' => 'completed',
            'completed_offset' => 5,
        ],
        [
            'username' => 'staff21',
            'title' => 'Prepare shared technology support matrix',
            'description' => 'Map application, access, and communication support needed by each company.',
            'priority' => 'high',
            'status' => 'in_progress',
        ],
        [
            'username' => 'staff21',
            'title' => 'Validate access and collaboration tools',
            'description' => 'Check shared drive, meeting room, and dashboard access for all project members.',
            'priority' => 'medium',
            'status' => 'completed',
            'completed_offset' => 8,
        ],
        [
            'username' => 'staff22',
            'title' => 'Configure cross-company access checklist',
            'description' => 'Prepare account access, folder permissions, and support escalation notes.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff23',
            'title' => 'Test dashboard notification routing',
            'description' => 'Validate notification routes for project updates across participating teams.',
            'priority' => 'medium',
            'status' => 'completed',
            'completed_offset' => 11,
        ],
        [
            'username' => 'staff31',
            'title' => 'Prepare group administration checklist',
            'description' => 'Compile project documents, approvals, and budget references from all companies.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff31',
            'title' => 'Reconcile shared project budget notes',
            'description' => 'Review shared cost notes and mark finance owners for follow-up.',
            'priority' => 'high',
            'status' => 'completed',
            'completed_offset' => 15,
        ],
        [
            'username' => 'staff33',
            'title' => 'Map cross-company planning milestones',
            'description' => 'Align project phases, dependency dates, and owner checkpoints across departments.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff33',
            'title' => 'Review project dependency timeline',
            'description' => 'Confirm milestone risks and timeline dependencies before execution week.',
            'priority' => 'medium',
            'status' => 'completed',
            'completed_offset' => 20,
        ],
        [
            'username' => 'staff14',
            'title' => 'Draft milestone dependency board',
            'description' => 'Document dependency owners and milestone checkpoints for the project plan.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff45',
            'title' => 'Validate project planning risk notes',
            'description' => 'Review planning risks and confirm mitigation owners before execution.',
            'priority' => 'medium',
            'status' => 'completed',
            'completed_offset' => 24,
        ],
        [
            'username' => 'staff44',
            'title' => 'Coordinate operational readiness checklist',
            'description' => 'Prepare venue, manpower, and execution readiness items with the operations team.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'username' => 'staff44',
            'title' => 'Prepare event execution handoff',
            'description' => 'Draft final operational handoff and execution notes for the launch period.',
            'priority' => 'medium',
            'status' => 'completed',
            'completed_offset' => 22,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $rnbCompanyId = DB::table('companies')->where('name', 'RNB')->value('id');
            if (! is_string($rnbCompanyId) || trim($rnbCompanyId) === '') {
                throw new RuntimeException('Company RNB tidak ditemukan. Jalankan CompanySeeder terlebih dahulu.');
            }
            $groupOwnerCompanyId = DB::table('companies')->where('name', 'AndalanKu')->value('id');
            if (! is_string($groupOwnerCompanyId) || trim($groupOwnerCompanyId) === '') {
                throw new RuntimeException('Company AndalanKu tidak ditemukan. Jalankan CompanySeeder terlebih dahulu.');
            }

            $staffUsers = $this->resolveStaffUsers($rnbCompanyId);
            $crossCompanyStaffUsers = $this->resolveUsersByUsernames(self::CROSS_COMPANY_STAFF_USERNAMES);
            $missingUsernames = collect(self::STAFF_USERNAMES)
                ->reject(fn (string $username): bool => $staffUsers->has($username))
                ->values();
            $missingCrossCompanyUsernames = collect(self::CROSS_COMPANY_STAFF_USERNAMES)
                ->reject(fn (string $username): bool => $crossCompanyStaffUsers->has($username))
                ->values();

            if ($missingUsernames->isNotEmpty()) {
                throw new RuntimeException('Akun staff31 sampai staff34 pada company RNB belum lengkap: '.$missingUsernames->implode(', ').'. Jalankan UserSeeder terlebih dahulu.');
            }
            if ($missingCrossCompanyUsernames->isNotEmpty()) {
                throw new RuntimeException('Akun staff lintas company belum lengkap: '.$missingCrossCompanyUsernames->implode(', ').'. Jalankan UserSeeder terlebih dahulu.');
            }

            $supervisorUserId = $this->resolveSupervisorUserId($staffUsers);
            if (! is_string($supervisorUserId) || trim($supervisorUserId) === '') {
                throw new RuntimeException('PIC project dari supervisor staff31 sampai staff34 tidak ditemukan. Jalankan EmployeePicAssignmentSeeder terlebih dahulu.');
            }
            $crossCompanyCreatorUserId = $this->resolveUserIdByUsername('supervisor1') ?? $supervisorUserId;

            DB::transaction(function () use ($groupOwnerCompanyId, $rnbCompanyId, $staffUsers, $crossCompanyStaffUsers, $supervisorUserId, $crossCompanyCreatorUserId): void {
                $project = $this->seedProject($rnbCompanyId, $supervisorUserId);
                $this->resetProjectDetails($project, $staffUsers);
                $this->seedProjectMembers($project, $staffUsers);
                $this->seedProjectTasks($project, $staffUsers, $supervisorUserId);

                $this->ensureCrossCompanyDepartmentAssignments($crossCompanyStaffUsers);
                $crossCompanyProject = $this->seedCrossCompanyProject($groupOwnerCompanyId, $crossCompanyCreatorUserId);
                $this->resetProjectDetails($crossCompanyProject, $crossCompanyStaffUsers);
                $this->seedCrossCompanyProjectMembers($crossCompanyProject, $crossCompanyStaffUsers);
                $this->seedCrossCompanyProjectTasks($crossCompanyProject, $crossCompanyStaffUsers, $crossCompanyCreatorUserId);
            });
        } catch (Throwable $throwable) {
            throw new RuntimeException('ProjectTaskSeeder gagal dijalankan.', 0, $throwable);
        }
    }

    /**
     * @return Collection<string, User>
     */
    private function resolveStaffUsers(string $rnbCompanyId): Collection
    {
        return User::query()
            ->whereIn('username', self::STAFF_USERNAMES)
            ->whereHas('roles', function ($query): void {
                $query->whereRaw('LOWER(name) = ?', ['staff']);
            })
            ->whereHas('employee.deployment', function ($query) use ($rnbCompanyId): void {
                $query->where('current_company_id', $rnbCompanyId);
            })
            ->with(['employee.deployment'])
            ->get()
            ->keyBy('username');
    }

    /**
     * @param  array<int, string>  $usernames
     * @return Collection<string, User>
     */
    private function resolveUsersByUsernames(array $usernames): Collection
    {
        return User::query()
            ->whereIn('username', $usernames)
            ->whereHas('roles', function ($query): void {
                $query->whereRaw('LOWER(name) = ?', ['staff']);
            })
            ->with(['employee.deployment'])
            ->get()
            ->keyBy('username');
    }

    private function resolveUserIdByUsername(string $username): ?string
    {
        $userId = User::query()
            ->where('username', $username)
            ->value('id');

        return is_string($userId) && trim($userId) !== '' ? trim($userId) : null;
    }

    /**
     * @param  Collection<string, User>  $staffUsers
     */
    private function resolveSupervisorUserId(Collection $staffUsers): ?string
    {
        /** @var User|null $firstStaffUser */
        $firstStaffUser = $staffUsers->get(self::STAFF_USERNAMES[0]);
        $staffEmployeeId = trim((string) ($firstStaffUser?->employee?->id ?? ''));
        if ($staffEmployeeId === '') {
            return null;
        }

        $supervisorEmployeeId = DB::table('employee_pic_assignments')
            ->where('staff_employee_id', $staffEmployeeId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->value('supervisor_employee_id');

        if (! is_string($supervisorEmployeeId) || trim($supervisorEmployeeId) === '') {
            return null;
        }

        $supervisorUserId = DB::table('employees')
            ->where('id', trim($supervisorEmployeeId))
            ->value('user_id');

        return is_string($supervisorUserId) && trim($supervisorUserId) !== ''
            ? trim($supervisorUserId)
            : null;
    }

    private function seedProject(string $rnbCompanyId, string $supervisorUserId): Project
    {
        $project = Project::withTrashed()
            ->where('company_id', $rnbCompanyId)
            ->where('code', self::PROJECT_CODE)
            ->first() ?? new Project([
                'company_id' => $rnbCompanyId,
                'code' => self::PROJECT_CODE,
            ]);

        if ($project->exists && $project->trashed()) {
            $project->restore();
        }

        $project->fill([
            'company_id' => $rnbCompanyId,
            'code' => self::PROJECT_CODE,
            'name' => 'RNB Event Activation 2026',
            'description' => 'Seed project for overtime task planning across project departments.',
            'client_name' => 'RNB',
            'live_event_start_date' => Carbon::create(2026, 6, 18, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
            'live_event_end_date' => Carbon::create(2026, 6, 20, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
            'start_date' => Carbon::create(2026, 6, 12, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
            'end_date' => Carbon::create(2026, 6, 30, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
            'status' => 'active',
            'created_by' => $supervisorUserId,
        ]);
        $project->save();

        return $project;
    }

    private function seedCrossCompanyProject(string $groupOwnerCompanyId, string $creatorUserId): Project
    {
        $project = Project::withTrashed()
            ->where('company_id', $groupOwnerCompanyId)
            ->where('code', self::CROSS_COMPANY_PROJECT_CODE)
            ->first() ?? new Project([
                'company_id' => $groupOwnerCompanyId,
                'code' => self::CROSS_COMPANY_PROJECT_CODE,
            ]);

        if ($project->exists && $project->trashed()) {
            $project->restore();
        }

        $project->fill([
            'company_id' => $groupOwnerCompanyId,
            'code' => self::CROSS_COMPANY_PROJECT_CODE,
            'name' => 'Muktamar ke VI PKB 2024',
            'description' => 'Bali Nusa Dua Convention Center, Badung, Bali',
            'client_name' => 'Partai Kebangkitan Bangsa',
            'live_event_start_date' => Carbon::create(2026, 6, 24, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
            'live_event_end_date' => Carbon::create(2026, 6, 26, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
            'start_date' => Carbon::create(2026, 6, 1, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
            'end_date' => Carbon::create(2026, 6, 30, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
            'status' => 'active',
            'created_by' => $creatorUserId,
        ]);
        $project->save();

        return $project;
    }

    /**
     * @param  Collection<string, User>  $staffUsers
     */
    private function ensureCrossCompanyDepartmentAssignments(Collection $staffUsers): void
    {
        foreach (self::CROSS_COMPANY_DEPARTMENT_ASSIGNMENTS as $username => $departmentName) {
            /** @var User|null $staffUser */
            $staffUser = $staffUsers->get($username);
            $employeeId = trim((string) ($staffUser?->employee?->id ?? ''));
            $departmentId = $this->resolveDepartmentIdByName($departmentName);

            if ($employeeId === '') {
                throw new RuntimeException("Data employee untuk {$username} tidak ditemukan.");
            }

            if ($departmentId === null) {
                throw new RuntimeException("Department {$departmentName} tidak ditemukan. Jalankan DepartmentSeeder terlebih dahulu.");
            }

            $updatedDeployments = DB::table('employee_deployments')
                ->where('employee_id', $employeeId)
                ->update([
                    'current_department_id' => $departmentId,
                    'updated_at' => now(),
                ]);

            if ($updatedDeployments < 1) {
                throw new RuntimeException("Deployment employee untuk {$username} tidak ditemukan. Jalankan UserSeeder terlebih dahulu.");
            }
        }
    }

    /**
     * @param  Collection<string, User>  $staffUsers
     */
    private function resetProjectDetails(Project $project, Collection $staffUsers): void
    {
        ProjectTask::withTrashed()
            ->where('project_id', $project->id)
            ->get()
            ->each(fn (ProjectTask $projectTask): bool => (bool) $projectTask->forceDelete());

        $employeeIds = $staffUsers
            ->map(fn (User $staffUser): string => trim((string) ($staffUser->employee?->id ?? '')))
            ->filter(static fn (string $employeeId): bool => $employeeId !== '')
            ->values();

        ProjectTask::withTrashed()
            ->whereNull('project_id')
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('title', $this->dailyTaskTitles())
            ->get()
            ->each(fn (ProjectTask $projectTask): bool => (bool) $projectTask->forceDelete());

        ProjectMember::query()
            ->where('project_id', $project->id)
            ->delete();
    }

    /**
     * @param  Collection<string, User>  $staffUsers
     */
    private function seedProjectMembers(Project $project, Collection $staffUsers): void
    {
        foreach (self::STAFF_USERNAMES as $username) {
            /** @var User $staffUser */
            $staffUser = $staffUsers->get($username);
            $employeeId = trim((string) ($staffUser->employee?->id ?? ''));
            if ($employeeId === '') {
                throw new RuntimeException("Data employee untuk {$username} tidak ditemukan.");
            }

            ProjectMember::query()->create([
                'project_id' => $project->id,
                'employee_id' => $employeeId,
                'joined_at' => Carbon::create(2026, 6, 12, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
                'left_at' => null,
                'status' => 'active',
            ]);
        }
    }

    /**
     * @param  Collection<string, User>  $staffUsers
     */
    private function seedCrossCompanyProjectMembers(Project $project, Collection $staffUsers): void
    {
        foreach (self::CROSS_COMPANY_STAFF_USERNAMES as $username) {
            /** @var User $staffUser */
            $staffUser = $staffUsers->get($username);
            $employeeId = trim((string) ($staffUser->employee?->id ?? ''));
            if ($employeeId === '') {
                throw new RuntimeException("Data employee untuk {$username} tidak ditemukan.");
            }

            ProjectMember::query()->create([
                'project_id' => $project->id,
                'employee_id' => $employeeId,
                'joined_at' => Carbon::create(2026, 6, 1, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
                'left_at' => null,
                'status' => 'active',
            ]);
        }
    }

    /**
     * @param  Collection<string, User>  $staffUsers
     */
    private function seedProjectTasks(Project $project, Collection $staffUsers, string $supervisorUserId): void
    {
        foreach (self::PROJECT_TASKS as $index => $taskData) {
            /** @var User $staffUser */
            $staffUser = $staffUsers->get($taskData['username']);
            $employeeId = trim((string) ($staffUser->employee?->id ?? ''));
            if ($employeeId === '') {
                throw new RuntimeException("Data employee untuk {$taskData['username']} tidak ditemukan.");
            }

            ProjectTask::query()->create([
                'project_id' => ($taskData['is_daily'] ?? false) === true ? null : $project->id,
                'employee_id' => $employeeId,
                'assigned_by' => $supervisorUserId,
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'blockers' => $taskData['blockers'] ?? null,
                'attachment_path' => $taskData['attachment_path'] ?? null,
                'status' => $taskData['status'],
                'priority' => $taskData['priority'],
                'start_date' => Carbon::create(2026, 6, 12, 0, 0, 0, 'Asia/Jakarta')->addDays($index)->toDateString(),
                'due_date' => Carbon::create(2026, 6, 18, 0, 0, 0, 'Asia/Jakarta')->addDays($index)->toDateString(),
                'completed_at' => null,
            ]);
        }
    }

    /**
     * @param  Collection<string, User>  $staffUsers
     */
    private function seedCrossCompanyProjectTasks(Project $project, Collection $staffUsers, string $creatorUserId): void
    {
        foreach (self::CROSS_COMPANY_PROJECT_TASKS as $index => $taskData) {
            /** @var User $staffUser */
            $staffUser = $staffUsers->get($taskData['username']);
            $employeeId = trim((string) ($staffUser->employee?->id ?? ''));
            if ($employeeId === '') {
                throw new RuntimeException("Data employee untuk {$taskData['username']} tidak ditemukan.");
            }

            $status = (string) $taskData['status'];
            $startDate = Carbon::create(2026, 6, 1, 0, 0, 0, 'Asia/Jakarta')->addDays($index * 3);
            $dueDate = $startDate->copy()->addDays(3);

            ProjectTask::query()->create([
                'project_id' => $project->id,
                'employee_id' => $employeeId,
                'assigned_by' => $creatorUserId,
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'blockers' => $taskData['blockers'] ?? null,
                'attachment_path' => $taskData['attachment_path'] ?? null,
                'status' => $status,
                'priority' => $taskData['priority'],
                'start_date' => $startDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'completed_at' => $status === 'completed'
                    ? Carbon::create(2026, 6, 1, 12, 0, 0, 'Asia/Jakarta')->addDays((int) ($taskData['completed_offset'] ?? $index))->toDateTimeString()
                    : null,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function dailyTaskTitles(): array
    {
        return collect(self::PROJECT_TASKS)
            ->filter(static fn (array $taskData): bool => ($taskData['is_daily'] ?? false) === true)
            ->pluck('title')
            ->all();
    }

    private function resolveDepartmentIdByName(string $name): ?string
    {
        $departmentId = DB::table('departments')
            ->where('name', $name)
            ->value('id');

        return is_string($departmentId) && trim($departmentId) !== ''
            ? trim($departmentId)
            : null;
    }
}
