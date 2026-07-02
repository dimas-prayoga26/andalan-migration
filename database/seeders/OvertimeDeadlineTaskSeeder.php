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
            'title' => 'Payment Distribution',
            'status' => 'waiting',
        ],
    ];

    private const OVERTIME_SCENARIOS = [
        [
            'key' => 'payment_distribution_complete',
            'date_offset_days' => -3,
            'planned_start_time' => '18:00:00',
            'planned_end_time' => '21:00:00',
            'actual_start_time' => '18:00:00',
            'actual_end_time' => '21:30:00',
            'calculated_hours' => 3.5,
            'status' => 'completed',
            'task_verification_pending' => false,
            'lifecycle_statuses' => [
                'assignment_submitted' => 'complete',
                'session_started' => 'clock_in',
                'task_deliverables_submitted' => 'complete',
                'session_ended' => 'clock_out',
                'task_hours_verification' => 'verified',
                'payroll_processing' => 'calculated_locked',
                'director_approval' => 'approved',
                'payment_disbursement' => 'complete',
            ],
        ],
        [
            'key' => 'task_hours_verification_pending',
            'date_offset_days' => -2,
            'planned_start_time' => '18:30:00',
            'planned_end_time' => '21:30:00',
            'actual_start_time' => '18:35:00',
            'actual_end_time' => '21:05:00',
            'calculated_hours' => 2.5,
            'status' => 'completed',
            'task_verification_pending' => true,
            'lifecycle_statuses' => [
                'assignment_submitted' => 'complete',
                'session_started' => 'clock_in',
                'task_deliverables_submitted' => 'complete',
                'session_ended' => 'clock_out',
                'task_hours_verification' => 'pending',
                'payroll_processing' => 'waiting',
                'director_approval' => 'waiting',
                'payment_disbursement' => 'waiting',
            ],
        ],
        [
            'key' => 'clock_in_in_progress',
            'date_offset_days' => -1,
            'planned_start_time' => '19:00:00',
            'planned_end_time' => '22:00:00',
            'actual_start_time' => '19:03:00',
            'actual_end_time' => null,
            'calculated_hours' => null,
            'status' => 'in_progress',
            'task_verification_pending' => true,
            'lifecycle_statuses' => [
                'assignment_submitted' => 'complete',
                'session_started' => 'clock_in',
                'task_deliverables_submitted' => 'pending',
                'session_ended' => 'pending',
                'task_hours_verification' => 'waiting',
                'payroll_processing' => 'waiting',
                'director_approval' => 'waiting',
                'payment_disbursement' => 'waiting',
            ],
        ],
        [
            'key' => 'payment_distribution_upcoming',
            'date_offset_days' => -4,
            'planned_start_time' => '18:00:00',
            'planned_end_time' => '20:00:00',
            'actual_start_time' => '18:05:00',
            'actual_end_time' => '20:10:00',
            'calculated_hours' => 2.08,
            'status' => 'completed',
            'task_verification_pending' => false,
            'lifecycle_statuses' => [
                'assignment_submitted' => 'complete',
                'session_started' => 'clock_in',
                'task_deliverables_submitted' => 'complete',
                'session_ended' => 'clock_out',
                'task_hours_verification' => 'verified',
                'payroll_processing' => 'calculated_locked',
                'director_approval' => 'approved',
                'payment_disbursement' => 'upcoming',
            ],
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

            $now = Carbon::now('Asia/Jakarta');
            $today = $now->copy()->startOfDay();

            DB::transaction(function () use ($project, $staffUsers, $today): void {
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

                    $scenario = self::OVERTIME_SCENARIOS[$index] ?? self::OVERTIME_SCENARIOS[0];
                    $overtimeDate = $today->copy()->addDays((int) $scenario['date_offset_days']);

                    $overtime = AttendanceOvertime::query()->create([
                        'employee_id' => $employeeId,
                        'assigned_by' => $supervisorUserId,
                        'overtime_date' => $overtimeDate->toDateString(),
                        'planned_start_time' => $scenario['planned_start_time'],
                        'planned_end_time' => $scenario['planned_end_time'],
                        'instruction' => "Seed overtime deadline task for {$username} ({$scenario['key']}).",
                        'actual_start_time' => $scenario['actual_start_time'],
                        'actual_end_time' => $scenario['actual_end_time'],
                        'calculated_hours' => $scenario['calculated_hours'],
                        'status' => $scenario['status'],
                    ]);

                    $this->seedProjectTasks($project, $employeeId, $overtime, $today, $overtimeDate, $index, $scenario);

                    $this->seedLifecycleLogs($overtime, $staffUser, $supervisorUserId, $overtimeDate, $scenario);
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

    /**
     * @param  array<string, mixed>  $scenario
     */
    private function seedProjectTasks(Project $project, string $employeeId, AttendanceOvertime $overtime, Carbon $today, Carbon $deadline, int $staffIndex, array $scenario): void
    {
        ProjectTask::query()->create([
            'project_id' => $project->id,
            'employee_id' => $employeeId,
            'assigned_by' => $overtime->assigned_by,
            'overtime_id' => $overtime->id,
            'title' => self::TASK_TITLE,
            'description' => 'Seed task untuk overtime dengan deadline hari ini.',
            'blockers' => 'Waiting for final overtime deliverable update.',
            'attachment_path' => null,
            'status' => (bool) ($scenario['task_verification_pending'] ?? true) ? 'pending' : 'completed',
            'priority' => $staffIndex === 0 ? 'high' : 'medium',
            'start_date' => $today->toDateString(),
            'due_date' => $deadline->toDateString(),
            'completed_at' => (bool) ($scenario['task_verification_pending'] ?? true)
                ? null
                : $deadline->copy()->setTime(20, 15)->toDateTimeString(),
        ]);

        foreach (self::COMPLETED_TASK_TITLES as $taskIndex => $taskTitle) {
            ProjectTask::query()->create([
                'project_id' => $project->id,
                'employee_id' => $employeeId,
                'assigned_by' => $overtime->assigned_by,
                'overtime_id' => $overtime->id,
                'title' => $taskTitle,
                'description' => 'Seed completed task untuk histori overtime.',
                'blockers' => null,
                'attachment_path' => null,
                'status' => 'completed',
                'priority' => 'medium',
                'start_date' => $deadline->copy()->subDays($taskIndex + 1)->toDateString(),
                'due_date' => $deadline->toDateString(),
                'completed_at' => $deadline->copy()->setTime(16, 0)->addMinutes($taskIndex)->toDateTimeString(),
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

    /**
     * @param  array<string, mixed>  $scenario
     */
    private function seedLifecycleLogs(AttendanceOvertime $overtime, User $staffUser, string $supervisorUserId, Carbon $overtimeDate, array $scenario): void
    {
        foreach (self::LIFECYCLE_STEPS as $lifecycleStep) {
            $eventKey = (string) $lifecycleStep['event_key'];
            $status = (string) (($scenario['lifecycle_statuses'][$eventKey] ?? null) ?: $lifecycleStep['status']);

            OvertimeLifecycleLog::query()->create([
                'overtime_id' => $overtime->id,
                'phase' => $lifecycleStep['phase'],
                'event_key' => $eventKey,
                'step_order' => $lifecycleStep['step_order'],
                'title' => $lifecycleStep['title'],
                'status' => $status,
                'actor_id' => $this->resolveLifecycleActorId($eventKey, $status, $staffUser, $supervisorUserId),
                'happened_at' => $this->resolveLifecycleHappenedAt($eventKey, $status, $overtimeDate, $scenario),
                'metadata' => [
                    'overtime_status' => $overtime->status,
                    'planned_start_time' => $scenario['planned_start_time'],
                    'planned_end_time' => $scenario['planned_end_time'],
                    'actual_start_time' => $scenario['actual_start_time'],
                    'actual_end_time' => $scenario['actual_end_time'],
                    'calculated_hours' => $scenario['calculated_hours'],
                    'scenario' => $scenario['key'],
                    'seeded_for' => $staffUser->username,
                ],
            ]);
        }
    }

    private function resolveLifecycleActorId(string $eventKey, string $status, User $staffUser, string $supervisorUserId): ?string
    {
        if (in_array($status, ['waiting', 'pending', 'upcoming'], true)) {
            return null;
        }

        return match ($eventKey) {
            'session_started', 'task_deliverables_submitted', 'session_ended' => (string) $staffUser->id,
            default => $supervisorUserId,
        };
    }

    /**
     * @param  array<string, mixed>  $scenario
     */
    private function resolveLifecycleHappenedAt(string $eventKey, string $status, Carbon $overtimeDate, array $scenario): ?Carbon
    {
        if (in_array($status, ['waiting', 'pending', 'upcoming'], true)) {
            return null;
        }

        return match ($eventKey) {
            'assignment_submitted' => $overtimeDate->copy()->subDay()->setTime(9, 0),
            'session_started' => $this->timeOnDate($overtimeDate, $scenario['actual_start_time'] ?: $scenario['planned_start_time']),
            'task_deliverables_submitted' => $this->timeOnDate($overtimeDate, $scenario['actual_start_time'] ?: $scenario['planned_start_time'])->addMinutes(45),
            'session_ended' => $this->timeOnDate($overtimeDate, $scenario['actual_end_time'] ?: $scenario['planned_end_time']),
            'task_hours_verification' => $this->timeOnDate($overtimeDate, $scenario['actual_end_time'] ?: $scenario['planned_end_time'])->addMinutes(30),
            'payroll_processing' => $overtimeDate->copy()->addDay()->setTime(10, 0),
            'director_approval' => $overtimeDate->copy()->addDay()->setTime(14, 0),
            'payment_disbursement' => $overtimeDate->copy()->addDays(2)->setTime(10, 0),
            default => null,
        };
    }

    private function timeOnDate(Carbon $date, mixed $time): Carbon
    {
        $timeParts = explode(':', (string) $time);

        return $date->copy()->setTime(
            (int) ($timeParts[0] ?? 0),
            (int) ($timeParts[1] ?? 0),
            (int) ($timeParts[2] ?? 0)
        );
    }
}
