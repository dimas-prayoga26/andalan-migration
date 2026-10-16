<?php

namespace App\Http\Controllers\DirectorAttendance;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HrMeetingTask;
use App\Models\Position;
use App\Models\ProjectTask;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DirectorAttendanceTaskController extends Controller
{
    private const EXCLUDED_TASK_EMPLOYEE_EMAILS = [
        'lukman@rnbmanagement.com',
        'rully.priyatno@andalanbersama.com',
        'hilmi.ulwan@andalanbersama.com',
    ];

    public function index(Request $request): View
    {
        $directorTaskCategoryCards = $this->directorTaskCategoryCards();
        $selectedDirectorTaskCategory = $this->selectedDirectorTaskCategory($request, $directorTaskCategoryCards);
        $directorTaskStaffOptions = $this->hrMeetingTaskStaffOptions();
        $selectedDirectorTaskStaff = $this->selectedDirectorTaskStaff($request, $directorTaskStaffOptions);
        $selectedDirectorTaskMonth = $this->selectedDirectorTaskMonth($request);
        $selectedDirectorTaskYear = $this->selectedDirectorTaskYear($request);
        $directorTaskBaseFilters = [
            'month' => $selectedDirectorTaskMonth,
            'year' => $selectedDirectorTaskYear,
        ];

        if ($selectedDirectorTaskStaff !== null) {
            $directorTaskBaseFilters['staff'] = $selectedDirectorTaskStaff;
        }

        return view('director_attendance.task.index', [
            'directorTaskCategoryCards' => $directorTaskCategoryCards,
            'selectedDirectorTaskCategory' => $selectedDirectorTaskCategory,
            'directorTaskStaffOptions' => $directorTaskStaffOptions,
            'selectedDirectorTaskStaff' => $selectedDirectorTaskStaff,
            'selectedDirectorTaskMonth' => $selectedDirectorTaskMonth,
            'selectedDirectorTaskYear' => $selectedDirectorTaskYear,
            'directorTaskMonthOptions' => $this->directorTaskMonthOptions(),
            'directorTaskYearOptions' => $this->directorTaskYearOptions($selectedDirectorTaskYear),
            'directorTaskBaseFilters' => $directorTaskBaseFilters,
            'directorTaskFilterLabel' => $this->directorTaskFilterLabel($selectedDirectorTaskStaff, $directorTaskStaffOptions, $selectedDirectorTaskMonth, $selectedDirectorTaskYear),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $categoryCards = $this->directorTaskCategoryCards();
        $staffOptions = $this->hrMeetingTaskStaffOptions();
        $selectedCategory = $this->selectedDirectorTaskCategory($request, $categoryCards);
        $selectedStaff = $this->selectedDirectorTaskStaff($request, $staffOptions);
        $selectedMonth = $this->selectedDirectorTaskMonth($request);
        $selectedYear = $this->selectedDirectorTaskYear($request);

        return DataTables::collection(
            $this->hrMeetingTaskRows($selectedCategory, $selectedStaff, $selectedMonth, $selectedYear, $categoryCards)
        )->escapeColumns([])->toJson();
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    private function companyOptions(): Collection
    {
        $companyIds = Employee::query()
            ->whereIn('id', $this->activeStaffEmployeeIds(now('Asia/Jakarta')->startOfDay())->all())
            ->whereHas('deployment', function ($query): void {
                $query->whereNotNull('current_company_id');
            })
            ->with('deployment:id,employee_id,current_company_id')
            ->get(['id'])
            ->pluck('deployment.current_company_id')
            ->filter(fn (mixed $companyId): bool => is_string($companyId) && trim($companyId) !== '')
            ->map(fn (string $companyId): string => trim($companyId))
            ->unique()
            ->values();

        if ($companyIds->isEmpty()) {
            return collect();
        }

        return Company::query()
            ->whereIn('id', $companyIds->all())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Company $company): array => [
                'id' => (string) $company->id,
                'name' => trim((string) $company->name),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{id:string,name:string,company_id:string,company_name:string}>
     */
    private function staffOptions(): Collection
    {
        $staffEmployeeIds = $this->activeStaffEmployeeIds(now('Asia/Jakarta')->startOfDay());
        if ($staffEmployeeIds->isEmpty()) {
            return collect();
        }

        return Employee::query()
            ->with([
                'profile:id,employee_id,name',
                'user:id,username,email',
                'deployment:id,employee_id,current_company_id',
                'deployment.company:id,name',
            ])
            ->whereIn('id', $staffEmployeeIds->all())
            ->get(['id', 'user_id'])
            ->sortBy(fn (Employee $employee): int|false => $staffEmployeeIds->search((string) $employee->id))
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'name' => $this->employeeDisplayName($employee),
                'company_id' => trim((string) ($employee->deployment?->current_company_id ?? '')),
                'company_name' => trim((string) ($employee->deployment?->company?->name ?? '-')),
            ])
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function activeStaffEmployeeIds(Carbon $date, ?string $companyId = null): Collection
    {
        $todayDate = $date->toDateString();
        $companyId = is_string($companyId) ? trim($companyId) : '';

        return Employee::query()
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
            ->whereHas('user', function ($query): void {
                $query
                    ->where('is_active', true)
                    ->whereNotIn('email', self::EXCLUDED_TASK_EMPLOYEE_EMAILS);
            })
            ->whereDoesntHave('deployment.position', function ($query): void {
                $query->whereSystemKey(Position::KEY_SUPER_ADMINISTRATOR);
            })
            ->whereDoesntHave('deployment.positions', function ($query): void {
                $query->whereSystemKey(Position::KEY_SUPER_ADMINISTRATOR);
            })
            ->whereHas('deployment', function ($query) use ($todayDate, $companyId): void {
                $query
                    ->whereNull('deleted_at')
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->whereRaw('LOWER(COALESCE(workplace, "")) <> ?', ['rnb jakarta'])
                    ->where(function ($query) use ($todayDate): void {
                        $query
                            ->whereNull('join_date')
                            ->orWhereDate('join_date', '<=', $todayDate);
                    })
                    ->where(function ($query) use ($todayDate): void {
                        $query
                            ->whereNull('resignation_date')
                            ->orWhereDate('resignation_date', '>=', $todayDate);
                    });

                if ($companyId !== '') {
                    $query->where('current_company_id', $companyId);
                }
            })
            ->pluck('id')
            ->filter(fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->map(fn (string $employeeId): string => trim($employeeId))
            ->values();
    }

    private function selectedStaffEmployeeIds(Request $request, Collection $staffEmployeeIds): Collection|false
    {
        if ($staffEmployeeIds->isEmpty()) {
            return collect();
        }

        $selectedStaffId = $request->string('staff')->trim()->toString();
        if ($selectedStaffId === '') {
            return $staffEmployeeIds;
        }

        return $staffEmployeeIds->contains($selectedStaffId)
            ? collect([$selectedStaffId])
            : false;
    }

    /**
     * @return array<string,array{title:string,subtitle:string,color:string,icon:string}>
     */
    private function directorTaskCategoryCards(): array
    {
        return [
            'administration' => [
                'title' => 'Administration',
                'subtitle' => 'Administrative operational support',
                'color' => 'secondary',
                'icon' => 'fa-clipboard-list',
            ],
            'event_management_kma' => [
                'title' => 'Event Management (KMA)',
                'subtitle' => 'Event Planning & Operations',
                'color' => 'danger',
                'icon' => 'fa-calendar-check',
            ],
            'property_construction_trah' => [
                'title' => 'Property and Construction (TRAH)',
                'subtitle' => 'Property & Construction Operations',
                'color' => 'info',
                'icon' => 'fa-building',
            ],
            'it_social_media_tms' => [
                'title' => 'IT and Social Media (TMS)',
                'subtitle' => 'Digital publication and IT operational support',
                'color' => 'success',
                'icon' => 'fa-laptop-code',
            ],
            'others' => [
                'title' => 'Others',
                'subtitle' => 'General & Miscellaneous Support',
                'color' => 'warning',
                'icon' => 'fa-layer-group',
            ],
        ];
    }

    /**
     * @param  array<string,array{title:string,subtitle:string,color:string,icon:string}>  $categoryCards
     */
    private function selectedDirectorTaskCategory(Request $request, array $categoryCards): ?string
    {
        $category = $request->string('category')->trim()->toString();

        return array_key_exists($category, $categoryCards) ? $category : null;
    }

    /**
     * @return Collection<int,array{id:string,name:string}>
     */
    private function hrMeetingTaskStaffOptions(): Collection
    {
        return Employee::query()
            ->with([
                'profile:id,employee_id,name',
                'user:id,username,email',
            ])
            ->whereHas('projectTasks.hrMeetingTasks')
            ->get(['id', 'user_id'])
            ->map(fn (Employee $employee): array => [
                'id' => (string) $employee->id,
                'name' => $this->employeeDisplayName($employee),
            ])
            ->sortBy('name')
            ->values();
    }

    /**
     * @param  Collection<int,array{id:string,name:string}>  $staffOptions
     */
    private function selectedDirectorTaskStaff(Request $request, Collection $staffOptions): ?string
    {
        $staffId = $request->string('staff')->trim()->toString();

        if ($staffId === '') {
            return null;
        }

        return $staffOptions->contains(fn (array $staffOption): bool => $staffOption['id'] === $staffId)
            ? $staffId
            : null;
    }

    private function selectedDirectorTaskMonth(Request $request): int
    {
        $month = $request->integer('month', now('Asia/Jakarta')->month);

        return $month >= 1 && $month <= 12 ? $month : now('Asia/Jakarta')->month;
    }

    private function selectedDirectorTaskYear(Request $request): int
    {
        $year = $request->integer('year', now('Asia/Jakarta')->year);

        return $year >= 2000 && $year <= 2100 ? $year : now('Asia/Jakarta')->year;
    }

    /**
     * @return array<int,string>
     */
    private function directorTaskMonthOptions(): array
    {
        return collect(range(1, 12))
            ->mapWithKeys(fn (int $month): array => [
                $month => Carbon::create(2026, $month, 1, 0, 0, 0, 'Asia/Jakarta')->format('F'),
            ])
            ->all();
    }

    /**
     * @return array<int,int>
     */
    private function directorTaskYearOptions(int $selectedYear): array
    {
        $currentYear = now('Asia/Jakarta')->year;

        return range(min($selectedYear, $currentYear) - 2, max($selectedYear, $currentYear) + 2);
    }

    /**
     * @param  Collection<int,array{id:string,name:string}>  $staffOptions
     */
    private function directorTaskFilterLabel(?string $selectedStaff, Collection $staffOptions, int $month, int $year): string
    {
        $staffLabel = $staffOptions
            ->first(fn (array $staffOption): bool => $staffOption['id'] === $selectedStaff)['name']
            ?? 'All Staff';

        return $staffLabel.' | '.Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->format('F Y');
    }

    /**
     * @param  array<string,array{title:string,subtitle:string,color:string,icon:string}>  $categoryCards
     * @return Collection<int,array<string,string>>
     */
    private function hrMeetingTaskRows(?string $selectedCategory, ?string $selectedStaff, int $selectedMonth, int $selectedYear, array $categoryCards): Collection
    {
        $periodStart = Carbon::create($selectedYear, $selectedMonth, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth()->toDateString();
        $periodEnd = Carbon::create($selectedYear, $selectedMonth, 1, 0, 0, 0, 'Asia/Jakarta')->endOfMonth()->toDateString();

        return HrMeetingTask::query()
            ->with([
                'meeting:id,title,meeting_date',
                'projectTask:id,project_id,employee_id,assigned_by,title,description,blockers,attachment_path,status,priority,start_date,due_date,completed_at,created_at',
                'projectTask.project:id,name',
                'projectTask.employee:id,user_id',
                'projectTask.employee.profile:id,employee_id,name,profile_picture_path',
                'projectTask.employee.user:id,username,email',
                'projectTask.assignedBy:id,username,email',
            ])
            ->when($selectedCategory !== null, fn ($query) => $query->where('category', $selectedCategory))
            ->whereHas('projectTask', function ($query) use ($selectedStaff, $periodStart, $periodEnd): void {
                if ($selectedStaff !== null) {
                    $query->where('employee_id', $selectedStaff);
                }

                $query->whereRaw('DATE(COALESCE(due_date, start_date, created_at)) BETWEEN ? AND ?', [$periodStart, $periodEnd]);
            })
            ->latest()
            ->get(['id', 'hr_meeting_id', 'category', 'project_task_id', 'created_at'])
            ->map(fn (HrMeetingTask $hrMeetingTask): array => $this->hrMeetingTaskRow($hrMeetingTask, $categoryCards))
            ->values();
    }

    /**
     * @param  array<string,array{title:string,subtitle:string,color:string,icon:string}>  $categoryCards
     * @return array<string,string|null>
     */
    private function hrMeetingTaskRow(HrMeetingTask $hrMeetingTask, array $categoryCards): array
    {
        $projectTask = $hrMeetingTask->projectTask;
        $category = trim((string) ($hrMeetingTask->category ?: 'others'));
        $categoryCard = $categoryCards[$category] ?? $categoryCards['others'];
        $assignee = $this->employeeDisplayName($projectTask?->employee);
        $attachmentPath = trim((string) ($projectTask?->attachment_path ?? ''));

        return [
            'id' => (string) $hrMeetingTask->id,
            'category' => $category,
            'category_label' => $categoryCard['title'],
            'category_subtitle' => $categoryCard['subtitle'],
            'task' => trim((string) ($projectTask?->title ?? '-')) ?: '-',
            'description' => trim((string) ($projectTask?->description ?? '')),
            'blockers' => trim((string) ($projectTask?->blockers ?? '')),
            'attachment_path' => $attachmentPath,
            'attachment_url' => Str::startsWith(strtolower($attachmentPath), ['http://', 'https://']) ? $attachmentPath : null,
            'project' => trim((string) ($projectTask?->project?->name ?? '')) ?: 'Daily Task',
            'assignee' => $assignee,
            'assignee_initial' => str($assignee)->trim()->substr(0, 1)->upper()->toString() ?: '-',
            'assignee_avatar_url' => $this->profilePictureUrl($projectTask?->employee?->profile?->profile_picture_path),
            'assigned_by' => $projectTask instanceof ProjectTask ? $this->assignedByLabel($projectTask) : 'Self',
            'date_range' => $this->dateRangeLabel($projectTask?->start_date, $projectTask?->due_date),
            'priority' => $this->priorityLabel((string) ($projectTask?->priority ?? 'medium')),
            'priority_class' => $this->priorityBadgeClass((string) ($projectTask?->priority ?? 'medium')),
            'status' => $this->hrMeetingTaskStatusLabel((string) ($projectTask?->status ?? 'pending')),
            'status_class' => $this->hrMeetingTaskStatusBadgeClass((string) ($projectTask?->status ?? 'pending')),
            'due_date' => $this->dueDateLabel($projectTask?->due_date),
        ];
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
            'staff' => $this->employeeDisplayName($projectTask->employee),
            'company' => trim((string) ($projectTask->employee?->deployment?->company?->name ?? '-')),
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
            'priority' => $this->priorityLabel((string) $projectTask->priority),
            'status' => $this->statusLabel((string) $projectTask->status, $isCompleted),
            'status_class' => $isCompleted ? 'success' : 'warning',
        ];
    }

    private function statusLabel(string $status, bool $isCompleted): string
    {
        if ($isCompleted) {
            return 'Finished';
        }

        return match (strtolower(trim($status))) {
            'in_progress' => 'On Progress',
            'cancelled' => 'Cancelled',
            default => 'Pending',
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

    private function priorityBadgeClass(string $priority): string
    {
        return match (strtolower(trim($priority))) {
            'high' => 'danger',
            'low' => 'success',
            default => 'warning',
        };
    }

    private function hrMeetingTaskStatusLabel(string $status): string
    {
        return match (strtolower(trim($status))) {
            'completed' => 'Completed',
            'in_progress' => 'On Progress',
            'cancelled' => 'Cancelled',
            default => 'To Do',
        };
    }

    private function hrMeetingTaskStatusBadgeClass(string $status): string
    {
        return match (strtolower(trim($status))) {
            'completed' => 'success',
            'in_progress' => 'primary',
            'cancelled' => 'danger',
            default => 'warning',
        };
    }

    private function employeeDisplayName(?Employee $employee): string
    {
        $profileName = $employee?->profile?->name;
        if (is_string($profileName) && trim($profileName) !== '') {
            return trim($profileName);
        }

        $username = $employee?->user?->username;
        if (is_string($username) && trim($username) !== '') {
            return trim($username);
        }

        $email = $employee?->user?->email;
        if (is_string($email) && trim($email) !== '') {
            return str($email)->before('@')->toString();
        }

        return 'Unknown Staff';
    }

    /**
     * Resolve a stored profile picture to a public URL, or null so the view can fall back to the initial.
     */
    private function profilePictureUrl(mixed $profilePicturePath): ?string
    {
        $profilePicturePath = trim((string) $profilePicturePath);

        if ($profilePicturePath === '') {
            return null;
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

        return File::exists(public_path($publicPath)) ? asset($publicPath) : null;
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

    private function dueDateLabel(?CarbonInterface $dueDate): string
    {
        return $dueDate !== null ? $this->dateLabelWithDay($dueDate) : '-';
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
