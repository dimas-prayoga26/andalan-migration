<?php

namespace Database\Seeders;

use App\Models\AttendanceOvertime;
use App\Models\OvertimeLifecycleLog;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class OvertimeDeadlineTaskSeeder extends Seeder
{
    private const PROJECT_CODE = 'RNB-EVENT-2026';

    private const STAFF_USERNAMES = [
        'staff31',
        'staff32',
        'staff33',
        'staff34',
    ];

    private const TASK_TITLE = 'Overtime deadline follow-up';

    private const COMPLETED_TASK_TITLES = [
        'Finalize overtime preparation notes',
        'Submit overtime deliverable evidence',
        'Complete overtime handoff checklist',
    ];

    private const LIFECYCLE_STEPS = [
        [
            'phase' => 'assignment_request',
            'event_key' => 'assignment_submitted',
            'step_order' => 1,
            'title' => 'Overtime Assignment Submitted',
            'status' => 'complete',
        ],
        [
            'phase' => 'execution_time_tracking',
            'event_key' => 'session_started',
            'step_order' => 2,
            'title' => 'Overtime Session Started',
            'status' => 'waiting',
        ],
        [
            'phase' => 'execution_time_tracking',
            'event_key' => 'task_deliverables_submitted',
            'step_order' => 3,
            'title' => 'Task & Deliverables Submitted',
            'status' => 'waiting',
        ],
        [
            'phase' => 'execution_time_tracking',
            'event_key' => 'session_ended',
            'step_order' => 4,
            'title' => 'Overtime Session Ended',
            'status' => 'waiting',
        ],
        [
            'phase' => 'review_approval',
            'event_key' => 'task_hours_verification',
            'step_order' => 5,
            'title' => 'Task & Hours Verification',
            'status' => 'waiting',
        ],
        [
            'phase' => 'payroll_payment',
            'event_key' => 'payroll_processing',
            'step_order' => 6,
            'title' => 'HR / Payroll Processing',
            'status' => 'waiting',
        ],
        [
            'phase' => 'payroll_payment',
            'event_key' => 'director_approval',
            'step_order' => 7,
            'title' => 'Director Approval',
            'status' => 'waiting',
        ],
        [
            'phase' => 'payroll_payment',
            'event_key' => 'payment_disbursement',
            'step_order' => 8,
            'title' => 'Payment Disbursement',
            'status' => 'waiting',
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

            $project = Project::query()
                ->where('code', self::PROJECT_CODE)
                ->where('company_id', $rnbCompanyId)
                ->first();

            if (! $project instanceof Project) {
                throw new RuntimeException('Project RNB-EVENT-2026 tidak ditemukan. Jalankan ProjectTaskSeeder terlebih dahulu.');
            }

            $staffUsers = $this->resolveStaffUsers($rnbCompanyId);
            $missingUsernames = collect(self::STAFF_USERNAMES)
                ->reject(fn (string $username): bool => $staffUsers->has($username))
                ->values();

            if ($missingUsernames->isNotEmpty()) {
                throw new RuntimeException('Akun staff31 sampai staff34 pada company RNB belum lengkap: '.$missingUsernames->implode(', ').'. Jalankan UserSeeder terlebih dahulu.');
            }

            $today = Carbon::now('Asia/Jakarta')->startOfDay();
            $deadline = $today->copy()->addDay();

            DB::transaction(function () use ($project, $staffUsers, $today, $deadline): void {
                $this->resetSeededOvertimeTasks();

                foreach (self::STAFF_USERNAMES as $index => $username) {
                    /** @var User $staffUser */
                    $staffUser = $staffUsers->get($username);
                    $employeeId = trim((string) ($staffUser->employee?->id ?? ''));
                    if ($employeeId === '') {
                        throw new RuntimeException("Data employee untuk {$username} tidak ditemukan.");
                    }

                    $supervisorUserId = $this->resolveSupervisorUserId($employeeId);
                    if ($supervisorUserId === null) {
                        throw new RuntimeException("Supervisor aktif untuk {$username} tidak ditemukan. Jalankan EmployeePicAssignmentSeeder terlebih dahulu.");
                    }

                    $overtime = AttendanceOvertime::query()->create([
                        'employee_id' => $employeeId,
                        'assigned_by' => $supervisorUserId,
                        'overtime_date' => $deadline->toDateString(),
                        'planned_start_time' => '17:00:00',
                        'planned_end_time' => '20:00:00',
                        'instruction' => "Seed overtime deadline task for {$username}.",
                        'actual_start_time' => null,
                        'actual_end_time' => null,
                        'calculated_hours' => null,
                        'status' => 'assigned',
                    ]);

                    $this->seedProjectTasks($project, $username, $employeeId, $overtime, $today, $deadline, $index);

                    $this->seedLifecycleLogs($overtime, $staffUser, $supervisorUserId, $today);
                }
            });
        } catch (Throwable $throwable) {
            throw new RuntimeException('OvertimeDeadlineTaskSeeder gagal dijalankan.', 0, $throwable);
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

    private function resolveSupervisorUserId(string $staffEmployeeId): ?string
    {
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

    private function resetSeededOvertimeTasks(): void
    {
        $seededTaskTitles = $this->seededTaskTitles();
        $overtimeIds = ProjectTask::withTrashed()
            ->whereIn('title', $seededTaskTitles)
            ->pluck('overtime_id')
            ->filter(static fn (mixed $overtimeId): bool => is_string($overtimeId) && trim($overtimeId) !== '')
            ->values();

        $instructionOvertimeIds = AttendanceOvertime::query()
            ->where('instruction', 'like', 'Seed overtime deadline task for staff%')
            ->pluck('id')
            ->filter(static fn (mixed $overtimeId): bool => is_string($overtimeId) && trim($overtimeId) !== '')
            ->values();

        $overtimeIds = $overtimeIds
            ->merge($instructionOvertimeIds)
            ->unique()
            ->values();

        if ($overtimeIds->isEmpty()) {
            return;
        }

        ProjectTask::withTrashed()
            ->where(function ($query) use ($overtimeIds, $seededTaskTitles): void {
                $query
                    ->whereIn('title', $seededTaskTitles)
                    ->orWhereIn('overtime_id', $overtimeIds);
            })
            ->get()
            ->each(fn (ProjectTask $projectTask): bool => (bool) $projectTask->forceDelete());

        OvertimeLifecycleLog::query()
            ->whereIn('overtime_id', $overtimeIds)
            ->delete();

        AttendanceOvertime::query()
            ->whereIn('id', $overtimeIds)
            ->delete();
    }

    private function seedProjectTasks(Project $project, string $username, string $employeeId, AttendanceOvertime $overtime, Carbon $today, Carbon $deadline, int $staffIndex): void
    {
        ProjectTask::query()->create([
            'project_id' => $project->id,
            'employee_id' => $employeeId,
            'overtime_id' => $overtime->id,
            'title' => self::TASK_TITLE,
            'description' => 'Seed task untuk overtime dengan deadline 1 hari lagi.',
            'blockers' => 'Waiting for final overtime deliverable update.',
            'attachment_path' => null,
            'status' => 'pending',
            'priority' => $staffIndex === 0 ? 'high' : 'medium',
            'start_date' => $today->toDateString(),
            'due_date' => $deadline->toDateString(),
            'completed_at' => null,
        ]);

        foreach (self::COMPLETED_TASK_TITLES as $taskIndex => $taskTitle) {
            ProjectTask::query()->create([
                'project_id' => $project->id,
                'employee_id' => $employeeId,
                'overtime_id' => $overtime->id,
                'title' => $taskTitle,
                'description' => 'Seed completed task untuk histori overtime.',
                'blockers' => null,
                'attachment_path' => null,
                'status' => 'completed',
                'priority' => 'medium',
                'start_date' => $today->copy()->subDays($taskIndex + 1)->toDateString(),
                'due_date' => $today->toDateString(),
                'completed_at' => $today->copy()->setTime(16, 0)->addMinutes($taskIndex)->toDateTimeString(),
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function seededTaskTitles(): array
    {
        return [
            self::TASK_TITLE,
            ...self::COMPLETED_TASK_TITLES,
        ];
    }

    private function seedLifecycleLogs(AttendanceOvertime $overtime, User $staffUser, string $supervisorUserId, Carbon $submittedAt): void
    {
        foreach (self::LIFECYCLE_STEPS as $lifecycleStep) {
            $isAssignmentStep = $lifecycleStep['event_key'] === 'assignment_submitted';

            OvertimeLifecycleLog::query()->create([
                'overtime_id' => $overtime->id,
                'phase' => $lifecycleStep['phase'],
                'event_key' => $lifecycleStep['event_key'],
                'step_order' => $lifecycleStep['step_order'],
                'title' => $lifecycleStep['title'],
                'status' => $isAssignmentStep ? 'complete' : $lifecycleStep['status'],
                'actor_id' => $isAssignmentStep ? $supervisorUserId : null,
                'happened_at' => $isAssignmentStep ? $submittedAt->copy()->setTime(9, 0) : null,
                'metadata' => [
                    'overtime_status' => $overtime->status,
                    'planned_start_time' => '17:00:00',
                    'planned_end_time' => '20:00:00',
                    'seeded_for' => $staffUser->username,
                ],
            ]);
        }
    }
}
