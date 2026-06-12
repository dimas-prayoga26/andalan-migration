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

    private const PROJECT_DEPARTMENTS = [
        'administration' => 'Administration, Finance and Legal',
        'graphic_design' => 'Marketing and Promotion',
        'event_3d_design' => 'Project Planning and Development',
        'documentation' => 'Operations',
        'publication_technology' => 'Information and Communications Technology',
    ];

    private const PROJECT_TASKS = [
        [
            'department' => 'administration',
            'username' => 'staff31',
            'title' => 'Prepare project administration checklist',
            'description' => 'Compile project scope, manpower list, and overtime coordination notes.',
            'priority' => 'high',
            'status' => 'in_progress',
        ],
        [
            'department' => 'graphic_design',
            'username' => 'staff31',
            'title' => 'Collect visual asset inventory',
            'description' => 'List all graphic assets required for event production and publication.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'event_3d_design',
            'username' => 'staff31',
            'title' => 'Document 3D layout requirements',
            'description' => 'Prepare requirement notes for booth dimension, flow, and installation constraints.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'documentation',
            'username' => 'staff31',
            'title' => 'Prepare project document archive',
            'description' => 'Create archive structure for project briefs, approvals, and field references.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'publication_technology',
            'username' => 'staff31',
            'title' => 'Publish technical coordination brief',
            'description' => 'Coordinate publication schedule and technology support requirements.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'administration',
            'username' => 'staff32',
            'title' => 'Prepare procurement request draft',
            'description' => 'Draft procurement request items for event production support.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'graphic_design',
            'username' => 'staff32',
            'title' => 'Create event key visual adaptation',
            'description' => 'Prepare visual assets for venue branding and social media publication.',
            'priority' => 'high',
            'status' => 'pending',
        ],
        [
            'department' => 'event_3d_design',
            'username' => 'staff32',
            'title' => 'Review 3D color and material reference',
            'description' => 'Validate material references against graphic identity and event theme.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'documentation',
            'username' => 'staff32',
            'title' => 'Prepare design review documentation',
            'description' => 'Document design review notes, revisions, and approval references.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'publication_technology',
            'username' => 'staff32',
            'title' => 'Prepare social media asset handoff',
            'description' => 'Package approved social media assets and publish-ready files.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'administration',
            'username' => 'staff33',
            'title' => 'Update production timeline tracker',
            'description' => 'Maintain timeline tracker for production milestones and supervisor checkpoints.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'graphic_design',
            'username' => 'staff33',
            'title' => 'Validate graphic placement on booth layout',
            'description' => 'Check graphic placement compatibility with the 3D booth construction plan.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'event_3d_design',
            'username' => 'staff33',
            'title' => 'Draft 3D event booth layout',
            'description' => 'Build initial booth layout and spatial reference for supervisor review.',
            'priority' => 'high',
            'status' => 'pending',
        ],
        [
            'department' => 'documentation',
            'username' => 'staff33',
            'title' => 'Export 3D preview documentation',
            'description' => 'Export rendered previews and annotate design assumptions for review.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'publication_technology',
            'username' => 'staff33',
            'title' => 'Coordinate technical display specification',
            'description' => 'Confirm screen, lighting, and technical display requirements for the booth.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'administration',
            'username' => 'staff34',
            'title' => 'Compile field execution checklist',
            'description' => 'Prepare operational checklist for setup, execution, and teardown needs.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'graphic_design',
            'username' => 'staff34',
            'title' => 'Capture graphic installation references',
            'description' => 'Prepare reference notes for documenting graphic installation progress.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'event_3d_design',
            'username' => 'staff34',
            'title' => 'Document booth setup progress',
            'description' => 'Record booth setup status and highlight production risks for supervisor review.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'documentation',
            'username' => 'staff34',
            'title' => 'Prepare documentation shot list',
            'description' => 'Define photo and video coverage plan for event preparation and execution.',
            'priority' => 'medium',
            'status' => 'pending',
        ],
        [
            'department' => 'publication_technology',
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
                $this->resetProjectDetails($project);
                $this->seedProjectMembers($project, $staffUsers);
                $departments = $this->resolveDepartments();
                $this->seedProjectTasks($project, $departments, $staffUsers);
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

    private function resetProjectDetails(Project $project): void
    {
        ProjectTask::withTrashed()
            ->where('project_id', $project->id)
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
     * @return Collection<string, string>
     */
    private function resolveDepartments(): Collection
    {
        $departments = collect(self::PROJECT_DEPARTMENTS)
            ->mapWithKeys(function (string $departmentName, string $departmentKey): array {
                $departmentId = DB::table('departments')
                    ->where('name', $departmentName)
                    ->value('id');

                if (! is_string($departmentId) || trim($departmentId) === '') {
                    throw new RuntimeException("Department {$departmentName} tidak ditemukan. Jalankan migration departments terlebih dahulu.");
                }

                return [$departmentKey => trim($departmentId)];
            });

        return $departments;
    }

    /**
     * @param  Collection<string, string>  $departments
     * @param  Collection<string, User>  $staffUsers
     */
    private function seedProjectTasks(Project $project, Collection $departments, Collection $staffUsers): void
    {
        foreach (self::PROJECT_TASKS as $index => $taskData) {
            $departmentId = $departments->get($taskData['department']);
            if (! is_string($departmentId) || trim($departmentId) === '') {
                throw new RuntimeException("Department untuk task {$taskData['title']} tidak ditemukan.");
            }

            /** @var User $staffUser */
            $staffUser = $staffUsers->get($taskData['username']);
            $employeeId = trim((string) ($staffUser->employee?->id ?? ''));
            if ($employeeId === '') {
                throw new RuntimeException("Data employee untuk {$taskData['username']} tidak ditemukan.");
            }

            ProjectTask::query()->create([
                'project_id' => $project->id,
                'department_id' => trim($departmentId),
                'employee_id' => $employeeId,
                'overtime_id' => null,
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'status' => $taskData['status'],
                'priority' => $taskData['priority'],
                'start_date' => Carbon::create(2026, 6, 12, 0, 0, 0, 'Asia/Jakarta')->addDays($index)->toDateString(),
                'due_date' => Carbon::create(2026, 6, 18, 0, 0, 0, 'Asia/Jakarta')->addDays($index)->toDateString(),
                'completed_at' => null,
            ]);
        }
    }
}
