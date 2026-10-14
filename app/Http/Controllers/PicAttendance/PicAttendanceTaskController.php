<?php

namespace App\Http\Controllers\PicAttendance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePicAssignment;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PicAttendanceTaskController extends Controller
{
    public function index(Request $request): View
    {
        $staffOptions = $this->staffOptionsFor($request->user());
        // dd($request->user());
        $selectedPeriod = $this->selectedPeriodFor($request);
        $taskRows = $this->tasksForStaffEmployeeIds(
            $staffOptions->pluck('id'),
            $selectedPeriod['year'],
            $selectedPeriod['month']
        );

        return view('pic_attendance.task.index', [
            'picTaskStaffOptions' => $staffOptions,
            'picTaskRows' => $taskRows,
            'picTaskSummary' => $this->taskSummaryFor($taskRows),
            'picTaskMonthLabel' => $this->monthYearLabel($selectedPeriod['year'], $selectedPeriod['month']),
            'picTaskSelectedMonth' => $selectedPeriod['month'],
            'picTaskSelectedYear' => $selectedPeriod['year'],
            'picTaskMonthOptions' => $this->monthOptions(),
            'picTaskYearOptions' => $this->yearOptions($selectedPeriod['year']),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $authenticatedUser = $request->user();
        $supervisorEmployeeId = $this->authenticatedEmployeeId($authenticatedUser);
        if ($supervisorEmployeeId === null) {
            return DataTables::collection(collect())->toJson();
        }

        $staffEmployeeIds = $this->supervisedStaffEmployeeIds($supervisorEmployeeId);
        $selectedStaffId = $this->selectedStaffEmployeeId($request, $staffEmployeeIds);
        if ($selectedStaffId === false || $selectedStaffId === null) {
            return DataTables::collection(collect())->toJson();
        }

        $visibleEmployeeIds = collect([$selectedStaffId]);

        if ($visibleEmployeeIds->isEmpty()) {
            return DataTables::collection(collect())->toJson();
        }

        $selectedPeriod = $this->selectedPeriodFor($request);
        $tasks = $this->tasksForStaffEmployeeIds(
            $visibleEmployeeIds,
            $selectedPeriod['year'],
            $selectedPeriod['month']
        );

        return DataTables::collection($tasks)->toJson();
    }

    /**
     * @param  Collection<int, string>  $employeeIds
     * @return Collection<int, array<string, string>>
     */
    private function tasksForStaffEmployeeIds(Collection $employeeIds, int $year, int $month): Collection
    {
        $visibleEmployeeIds = $employeeIds
            ->filter(fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->map(fn (string $employeeId): string => trim($employeeId))
            ->unique()
            ->values();

        if ($visibleEmployeeIds->isEmpty()) {
            return collect();
        }

        $selectedMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
        $currentMonthStart = $selectedMonth->copy()->startOfMonth()->toDateString();
        $currentMonthEnd = $selectedMonth->copy()->endOfMonth()->toDateString();

        return ProjectTask::query()
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name,profile_picture_path',
                'employee.user:id,username,email',
                'project:id,name',
                'overtime:id,record_number',
                'assignedBy:id,username,email',
            ])
            ->whereIn('employee_id', $visibleEmployeeIds->all())
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
    }

    /**
     * @return array{month:int,year:int}
     */
    private function selectedPeriodFor(Request $request): array
    {
        $today = now('Asia/Jakarta');

        return [
            'month' => $this->normalizeMonth($request->query('month'), (int) $today->month),
            'year' => $this->normalizeYear($request->query('year'), (int) $today->year),
        ];
    }

    private function normalizeMonth(mixed $month, int $fallbackMonth): int
    {
        $normalizedMonth = filter_var($month, FILTER_VALIDATE_INT);

        return is_int($normalizedMonth) && $normalizedMonth >= 1 && $normalizedMonth <= 12
            ? $normalizedMonth
            : $fallbackMonth;
    }

    private function normalizeYear(mixed $year, int $fallbackYear): int
    {
        $normalizedYear = filter_var($year, FILTER_VALIDATE_INT);

        return is_int($normalizedYear) && $normalizedYear >= 2000 && $normalizedYear <= 2100
            ? $normalizedYear
            : $fallbackYear;
    }

    private function monthYearLabel(int $year, int $month): string
    {
        return Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->format('F Y');
    }

    /**
     * @return array<int, string>
     */
    private function monthOptions(): array
    {
        $monthOptions = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthOptions[$month] = Carbon::create(2026, $month, 1, 0, 0, 0, 'Asia/Jakarta')->format('F');
        }

        return $monthOptions;
    }

    /**
     * @return array<int, int>
     */
    private function yearOptions(int $selectedYear): array
    {
        $currentYear = (int) now('Asia/Jakarta')->year;
        $years = range($currentYear - 2, $currentYear + 1);
        $years[] = $selectedYear;
        $years = array_values(array_unique($years));
        sort($years);

        return $years;
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    private function staffOptionsFor(?User $authenticatedUser): Collection
    {
        $supervisorEmployeeId = $this->authenticatedEmployeeId($authenticatedUser);
        if ($supervisorEmployeeId === null) {
            return collect();
        }

        $staffEmployeeIds = $this->supervisedStaffEmployeeIds($supervisorEmployeeId);
        if ($staffEmployeeIds->isEmpty()) {
            return collect();
        }

        return Employee::query()
            ->with(['profile:id,employee_id,name', 'user:id,username,email'])
            ->whereIn('id', $staffEmployeeIds->all())
            ->get(['id', 'user_id'])
            ->sortBy(fn (Employee $employee): int|false => $staffEmployeeIds->search((string) $employee->id))
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'name' => $this->employeeDisplayName($employee),
            ])
            ->values();

        // dd($employee);
    }

    /**
     * @param  Collection<int, array<string, string>>  $taskRows
     * @return array{completion_rate:int,total_tasks:int,in_progress_tasks:int,completed_tasks:int,total_progress:int,in_progress_progress:int,completed_progress:int}
     */
    private function taskSummaryFor(Collection $taskRows): array
    {
        $totalTasks = $taskRows->count();
        $completedTasks = $taskRows
            ->filter(fn (array $taskRow): bool => ($taskRow['status_class'] ?? '') === 'success')
            ->count();
        $inProgressTasks = $taskRows
            ->filter(fn (array $taskRow): bool => ($taskRow['status_key'] ?? '') === 'in_progress')
            ->count();
        $completionRate = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        return [
            'completion_rate' => $completionRate,
            'total_tasks' => $totalTasks,
            'in_progress_tasks' => $inProgressTasks,
            'completed_tasks' => $completedTasks,
            'total_progress' => $totalTasks > 0 ? 100 : 0,
            'in_progress_progress' => $totalTasks > 0 ? (int) round(($inProgressTasks / $totalTasks) * 100) : 0,
            'completed_progress' => $completionRate,
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function supervisedStaffEmployeeIds(string $supervisorEmployeeId): Collection
    {
        return EmployeePicAssignment::query()
            ->where('supervisor_employee_id', $supervisorEmployeeId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->pluck('staff_employee_id')
            ->filter(fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->map(fn (string $employeeId): string => trim($employeeId))
            ->unique()
            ->values();
    }

    private function selectedStaffEmployeeId(Request $request, Collection $staffEmployeeIds): string|false|null
    {
        if ($staffEmployeeIds->isEmpty()) {
            return null;
        }

        $selectedStaffId = $request->string('staff')->trim()->toString();
        if ($selectedStaffId === '') {
            return null;
        }

        return $staffEmployeeIds->contains($selectedStaffId) ? $selectedStaffId : false;
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

    /**
     * @return array<string, string>
     */
    private function taskRow(ProjectTask $projectTask): array
    {
        $isCompleted = $projectTask->status === 'completed' || $projectTask->completed_at !== null;
        $projectName = trim((string) ($projectTask->project?->name ?? 'Daily Task'));
        $isOvertimeTask = $projectTask->overtime_id !== null;
        $taskContext = $isOvertimeTask
            ? $this->overtimeRecordNumberLabel($projectTask)
            : ($projectTask->project_id !== null ? 'Task ('.$projectName.')' : 'Daily Task');

        return [
            'id' => (string) $projectTask->id,
            'staff_id' => (string) ($projectTask->employee?->id ?? ''),
            'staff' => $this->employeeDisplayName($projectTask->employee),
            'avatar_url' => $this->employeeAvatarUrl($projectTask->employee),
            'task' => trim((string) $projectTask->title),
            'description' => trim((string) ($projectTask->description ?? '')),
            'blockers' => trim((string) ($projectTask->blockers ?? '')),
            'attachment_path' => trim((string) ($projectTask->attachment_path ?? '')),
            'project' => $projectName,
            'task_category' => $isOvertimeTask ? 'Overtime Task' : ($projectTask->project_id !== null ? $taskContext : 'Daily Task'),
            'task_context' => $taskContext,
            'task_context_type' => $isOvertimeTask ? 'overtime' : ($projectTask->project_id !== null ? 'project' : 'daily'),
            'assigned_by' => $this->assignedByLabel($projectTask),
            'due_date' => $this->dateRangeLabel($projectTask->start_date, $projectTask->due_date),
            'short_due_date' => $this->shortDateRangeLabel($projectTask->start_date, $projectTask->due_date),
            'priority' => $this->priorityLabel((string) $projectTask->priority),
            'status' => $this->statusLabel((string) $projectTask->status, $isCompleted),
            'status_key' => $this->statusKey((string) $projectTask->status, $isCompleted),
            'status_class' => $isCompleted ? 'success' : 'warning',
        ];
    }

    private function statusLabel(string $status, bool $isCompleted): string
    {
        if ($isCompleted) {
            return 'Completed';
        }

        return match (strtolower(trim($status))) {
            'in_progress' => 'In Progress',
            'cancelled' => 'Cancelled',
            default => 'Pending',
        };
    }

    private function statusKey(string $status, bool $isCompleted): string
    {
        if ($isCompleted) {
            return 'completed';
        }

        return match (strtolower(trim($status))) {
            'in_progress' => 'in_progress',
            'cancelled' => 'cancelled',
            default => 'pending',
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
        $username = $employee?->user?->username;
        if (is_string($username) && trim($username) !== '') {
            return trim($username);
        }

        return 'Unknown Staff';
    }

    private function employeeAvatarUrl(?Employee $employee): string
    {
        $profilePicturePath = $employee?->profile?->profile_picture_path;
        if (! is_string($profilePicturePath)) {
            return asset('assets/default_user.jpg');
        }

        $storagePath = trim($profilePicturePath);
        if ($storagePath === '') {
            return asset('assets/default_user.jpg');
        }

        if (Storage::disk('public')->exists($storagePath)) {
            return asset('storage/'.$storagePath);
        }

        return asset('assets/default_user.jpg');
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

    private function overtimeRecordNumberLabel(ProjectTask $projectTask): string
    {
        $recordNumber = trim((string) ($projectTask->overtime?->record_number ?? ''));

        return $recordNumber !== '' ? $recordNumber : '-';
    }

    private function dateRangeLabel(?CarbonInterface $startDate, ?CarbonInterface $dueDate): string
    {
        if ($startDate === null && $dueDate === null) {
            return '-';
        }

        if ($startDate === null) {
            return $dueDate !== null ? $this->dateLabelWithDay($dueDate) : '-';
        }

        if ($dueDate === null || $startDate->isSameDay($dueDate)) {
            return $this->dateLabelWithDay($startDate);
        }

        if ($startDate->format('M Y') === $dueDate->format('M Y')) {
            return $this->dateDayLabel($startDate).' - '.$this->dateLabelWithDay($dueDate);
        }

        return $this->dateLabelWithDay($startDate).' - '.$this->dateLabelWithDay($dueDate);
    }

    private function shortDateRangeLabel(?CarbonInterface $startDate, ?CarbonInterface $dueDate): string
    {
        if ($startDate === null && $dueDate === null) {
            return '-';
        }

        if ($startDate === null) {
            return $dueDate !== null ? $dueDate->format('j M') : '-';
        }

        if ($dueDate === null || $startDate->isSameDay($dueDate)) {
            return $startDate->format('j M');
        }

        if ($startDate->format('M Y') === $dueDate->format('M Y')) {
            return $startDate->format('j').'-'.$dueDate->format('j M');
        }

        return $startDate->format('j M').' - '.$dueDate->format('j M');
    }

    private function dateLabelWithDay(CarbonInterface $date): string
    {
        return $this->dateDayLabel($date).' '.$date->format('M Y');
    }

    private function dateDayLabel(CarbonInterface $date): string
    {
        return $this->indonesianWeekdayName($date).', '.$date->format('d');
    }

    private function indonesianWeekdayName(CarbonInterface $date): string
    {
        return match ((int) $date->dayOfWeek) {
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => "Jum'at",
            default => 'Sabtu',
        };
    }
}
