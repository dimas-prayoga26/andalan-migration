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

    private const STAFF_USERNAMES = [
        'staff31',
        'staff32',
        'staff33',
        'staff34',
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

            $staffUsers = $this->resolveStaffUsers($rnbCompanyId);
            $missingUsernames = collect(self::STAFF_USERNAMES)
                ->reject(fn (string $username): bool => $staffUsers->has($username))
                ->values();

            if ($missingUsernames->isNotEmpty()) {
                throw new RuntimeException('Akun staff31 sampai staff34 pada company RNB belum lengkap: '.$missingUsernames->implode(', ').'. Jalankan UserSeeder terlebih dahulu.');
            }

            $supervisorUserId = $this->resolveSupervisorUserId($staffUsers);
            if (! is_string($supervisorUserId) || trim($supervisorUserId) === '') {
                throw new RuntimeException('PIC project dari supervisor staff31 sampai staff34 tidak ditemukan. Jalankan EmployeePicAssignmentSeeder terlebih dahulu.');
            }

            DB::transaction(function () use ($rnbCompanyId, $staffUsers, $supervisorUserId): void {
                $project = $this->seedProject($rnbCompanyId, $supervisorUserId);
                $this->resetProjectDetails($project, $staffUsers);
                $this->seedProjectMembers($project, $staffUsers);
                $this->seedProjectTasks($project, $staffUsers);
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
            'start_date' => Carbon::create(2026, 6, 12, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
            'end_date' => Carbon::create(2026, 6, 30, 0, 0, 0, 'Asia/Jakarta')->toDateString(),
            'status' => 'active',
            'created_by' => $supervisorUserId,
        ]);
        $project->save();

        return $project;
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
    private function seedProjectTasks(Project $project, Collection $staffUsers): void
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
                'overtime_id' => null,
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
     * @return array<int, string>
     */
    private function dailyTaskTitles(): array
    {
        return collect(self::PROJECT_TASKS)
            ->filter(static fn (array $taskData): bool => ($taskData['is_daily'] ?? false) === true)
            ->pluck('title')
            ->all();
    }
}
