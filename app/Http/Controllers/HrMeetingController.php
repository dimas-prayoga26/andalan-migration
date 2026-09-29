<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Employee;
use App\Models\HrMeeting;
use App\Models\HrMeetingTask;
use App\Models\ProjectTask;
use App\Services\HrMeetingNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HrMeetingController extends Controller
{
    public function staffIndex(Request $request): View
    {
        $completedMonth = min(12, max(1, (int) $request->query('completed_month', now('Asia/Jakarta')->month)));
        $completedYear = (int) $request->query('completed_year', now('Asia/Jakarta')->year);
        $employee = auth()->user()?->employee;
        $scheduledMeetings = collect();
        $completedMeetings = collect();

        if ($employee instanceof Employee) {
            $employee->loadMissing('deployment.position', 'deployment.positions');

            $baseQuery = HrMeeting::query()
                ->withCount([
                    'participants as joined_count' => fn ($query) => $query->where('attendance_status', 'joined'),
                    'tasks as staff_tasks_count' => fn ($query) => $query->whereHas(
                        'projectTask',
                        fn ($projectTaskQuery) => $projectTaskQuery->where('employee_id', $employee->id)
                    ),
                ])
                ->where(function ($query) use ($employee): void {
                    $query->whereHas('participants', function ($participantQuery) use ($employee): void {
                        $participantQuery
                            ->where('employee_id', $employee->id)
                            ->orWhere(function ($legacyAllStaffQuery): void {
                                $legacyAllStaffQuery
                                    ->whereNull('employee_id')
                                    ->where('participant_type', 'all_staff');
                            });

                        if ($this->employeeHasSupervisorPosition($employee)) {
                            $participantQuery->orWhere(function ($legacyBodQuery): void {
                                $legacyBodQuery
                                    ->whereNull('employee_id')
                                    ->where('participant_type', 'bod');
                            });
                        }
                    });
                });

            $scheduledMeetings = (clone $baseQuery)
                ->where('status', 'scheduled')
                ->orderBy('meeting_date')
                ->orderBy('meeting_time')
                ->get()
                ->map(fn (HrMeeting $meeting): array => $this->staffMeetingCardData($meeting));

            $completedMeetings = (clone $baseQuery)
                ->where('status', 'completed')
                ->whereYear('meeting_date', $completedYear)
                ->whereMonth('meeting_date', $completedMonth)
                ->orderByDesc('meeting_date')
                ->orderByDesc('meeting_time')
                ->get()
                ->map(fn (HrMeeting $meeting): array => $this->staffMeetingCardData($meeting, true));
        }

        return view('meetings.staff.index', [
            'scheduledMeetings' => $scheduledMeetings,
            'completedMeetings' => $completedMeetings,
            'completedMonth' => $completedMonth,
            'completedYear' => $completedYear,
            'completedMonthLabel' => $this->monthOptions()[$completedMonth] ?? now('Asia/Jakarta')->format('F'),
            'monthOptions' => $this->monthOptions(),
            'yearOptions' => range($completedYear + 1, $completedYear - 3),
        ]);
    }

    public function staffJoin(HrMeeting $hrMeeting): RedirectResponse
    {
        $employee = auth()->user()?->employee;

        if (! $employee instanceof Employee || ! $this->employeeCanAccessMeeting($employee, $hrMeeting)) {
            abort(403);
        }

        $participant = $hrMeeting->participants()
            ->firstOrNew([
                'employee_id' => $employee->id,
            ]);

        $participant->participant_type = $participant->participant_type ?: 'employee';

        if ($participant->attendance_status !== 'joined') {
            $participant->attendance_status = 'joined';
            $participant->joined_at = now('Asia/Jakarta');
        } elseif ($participant->joined_at === null) {
            $participant->joined_at = now('Asia/Jakarta');
        }

        $participant->save();

        if ($hrMeeting->meeting_link) {
            return redirect()->away($hrMeeting->meeting_link);
        }

        return redirect()
            ->route('zoom-meeting.index')
            ->with('error', 'Meeting link is not available.');
    }

    public function index(Request $request): View
    {
        $month = (int) $request->query('month', now('Asia/Jakarta')->month);
        $year = (int) $request->query('year', now('Asia/Jakarta')->year);

        return view('meetings.admin.index', [
            'month' => $month,
            'year' => $year,
            'monthOptions' => $this->monthOptions(),
            'yearOptions' => range($year + 1, $year - 3),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $month = (int) $request->query('month', now('Asia/Jakarta')->month);
        $year = (int) $request->query('year', now('Asia/Jakarta')->year);
        $meetingTypeLabels = $this->meetingTypeLabels();
        $statusBadgeClasses = $this->statusBadgeClasses();

        $meetings = HrMeeting::query()
            ->withCount([
                'participants as joined_count' => fn ($query) => $query->where('attendance_status', 'joined'),
                'tasks',
            ])
            ->whereYear('meeting_date', $year)
            ->whereMonth('meeting_date', $month)
            ->orderByDesc('meeting_date')
            ->orderByDesc('meeting_time')
            ->get()
            ->map(fn (HrMeeting $meeting): array => [
                'id' => (string) $meeting->id,
                'date' => $meeting->meeting_date?->format('l, d F Y') ?? '-',
                'type' => $meetingTypeLabels[$meeting->type] ?? $meeting->type,
                'time' => substr((string) $meeting->meeting_time, 0, 5).' WIB',
                'joined' => ((int) $meeting->joined_count).' People',
                'tasks' => ((int) $meeting->tasks_count).' Tasks',
                'status' => ucfirst((string) $meeting->status),
                'status_badge_class' => $statusBadgeClasses[$meeting->status] ?? 'badge-info',
                'details_url' => route('hr-meetings.details', $meeting),
                'update_url' => route('hr-meetings.update', $meeting),
                'destroy_url' => route('hr-meetings.destroy', $meeting),
            ])
            ->values();

        return response()->json([
            'meetings' => $meetings,
            'data' => $meetings,
        ]);
    }

    public function create(): View
    {
        return view('meetings.admin.create', [
            'meeting' => null,
            'employeeOptions' => $this->employeeOptions(),
            'meetingTypeLabels' => $this->meetingTypeLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $meeting = DB::transaction(function () use ($validated): HrMeeting {
            $meeting = HrMeeting::query()->create([
                'title' => $validated['title'],
                'type' => $validated['type'],
                'meeting_date' => $validated['meeting_date'],
                'meeting_time' => $validated['meeting_time'],
                'meeting_link' => $validated['meeting_link'] ?? null,
                'status' => $validated['status'],
                'attachment_link' => $validated['attachment_link'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->user()?->employee?->id,
            ]);

            $this->syncParticipants($meeting, $validated['participant_group'] ?? 'all_staff', $validated['participant_ids'] ?? []);

            if (! empty($validated['attachment_link'])) {
                $meeting->attachments()->create([
                    'type' => 'presentation',
                    'name' => 'Presentation Link',
                    'url' => $validated['attachment_link'],
                ]);
            }

            return $meeting;
        });

        app(HrMeetingNotificationService::class)->notifyScheduled($meeting);

        return redirect()
            ->route('hr-meetings.details', $meeting)
            ->with('status', 'Meeting has been created.');
    }

    public function show(?HrMeeting $hrMeeting = null): View|RedirectResponse
    {
        $meeting = $hrMeeting ?? $this->latestMeeting();

        if (! $meeting instanceof HrMeeting) {
            return redirect()
                ->route('hr-meetings.index')
                ->with('error', 'No meeting data available yet.');
        }

        $meeting->loadCount([
            'participants as joined_count' => fn ($query) => $query->where('attendance_status', 'joined'),
            'participants',
            'tasks',
        ]);

        $meeting->load([
            'tasks.projectTask.employee.profile',
            'tasks.projectTask.employee.user',
            'participants.employee.profile',
            'participants.employee.user',
        ]);

        $meetingTaskCards = $this->meetingTaskCards($meeting);

        return view('meetings.admin.details', [
            'meeting' => $meeting,
            'meetingTypeLabels' => $this->meetingTypeLabels(),
            'statusBadgeClasses' => $this->statusBadgeClasses(),
            'meetingTaskCards' => $meetingTaskCards,
            'meetingTaskSummary' => $this->meetingTaskSummary($meeting, $meetingTaskCards),
            'meetingTaskAssigneeOptions' => $this->meetingTaskAssigneeOptions($meeting),
            'meetingAttendanceRows' => $this->meetingAttendanceRows($meeting),
        ]);
    }

    public function edit(?HrMeeting $hrMeeting = null): View|RedirectResponse
    {
        $meeting = $hrMeeting ?? $this->latestMeeting();

        if (! $meeting instanceof HrMeeting) {
            return redirect()
                ->route('hr-meetings.create')
                ->with('error', 'Create a meeting first before updating it.');
        }

        $meeting->load('participants');

        return view('meetings.admin.update', [
            'meeting' => $meeting,
            'employeeOptions' => $this->employeeOptions(),
            'meetingTypeLabels' => $this->meetingTypeLabels(),
            'selectedParticipantGroup' => $this->selectedParticipantGroup($meeting),
            'selectedParticipantIds' => $this->selectedParticipantIds($meeting),
        ]);
    }

    public function update(Request $request, HrMeeting $hrMeeting): RedirectResponse
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($hrMeeting, $validated): void {
            $hrMeeting->update([
                'title' => $validated['title'],
                'type' => $validated['type'],
                'meeting_date' => $validated['meeting_date'],
                'meeting_time' => $validated['meeting_time'],
                'meeting_link' => $validated['meeting_link'] ?? null,
                'status' => $validated['status'],
                'attachment_link' => $validated['attachment_link'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncParticipants($hrMeeting, $validated['participant_group'] ?? 'all_staff', $validated['participant_ids'] ?? []);
        });

        return redirect()
            ->route('hr-meetings.details', $hrMeeting)
            ->with('status', 'Meeting has been updated.');
    }

    public function destroy(HrMeeting $hrMeeting): RedirectResponse
    {
        DB::transaction(function () use ($hrMeeting): void {
            $projectTaskIds = $hrMeeting->tasks()
                ->whereNotNull('project_task_id')
                ->pluck('project_task_id')
                ->filter()
                ->unique()
                ->values();

            AppNotification::query()
                ->where('type', 'hr_meeting_scheduled')
                ->where('url', 'like', '%'.$hrMeeting->id.'%')
                ->delete();

            $hrMeeting->participants()->delete();
            $hrMeeting->attachments()->delete();
            $hrMeeting->momExports()->delete();
            $hrMeeting->tasks()->delete();

            if ($projectTaskIds->isNotEmpty()) {
                ProjectTask::query()
                    ->whereIn('id', $projectTaskIds->all())
                    ->delete();
            }

            $hrMeeting->delete();
        });

        return redirect()
            ->route('hr-meetings.index')
            ->with('status', 'Meeting has been deleted.');
    }

    public function storeTask(Request $request, HrMeeting $hrMeeting): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys($this->meetingTaskCardDefinitions()))],
            'title' => ['required', 'string', 'max:255'],
            'assigned_to' => ['required', 'string', 'exists:employees,id'],
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
        ]);

        $assigneeIds = $this->meetingTaskAssigneeOptions($hrMeeting)
            ->pluck('id')
            ->map(fn (mixed $employeeId): string => (string) $employeeId);

        if (! $assigneeIds->contains((string) $validated['assigned_to'])) {
            throw ValidationException::withMessages([
                'assigned_to' => 'Staff task harus staff yang sudah joined di meeting ini.',
            ]);
        }

        DB::transaction(function () use ($hrMeeting, $validated): void {
            $projectTask = ProjectTask::query()->create([
                'employee_id' => $validated['assigned_to'],
                'assigned_by' => auth()->id(),
                'title' => trim((string) $validated['title']),
                'description' => trim((string) $validated['title']),
                'status' => 'pending',
                'priority' => 'medium',
                'start_date' => $validated['start_date'],
                'due_date' => $validated['due_date'],
            ]);

            $hrMeeting->tasks()->create([
                'category' => $validated['category'],
                'project_task_id' => $projectTask->id,
            ]);
        });

        return redirect()
            ->route('hr-meetings.details', $hrMeeting)
            ->with('status', 'Task meeting has been created.');
    }

    /**
     * @return array{
     *     title:string,
     *     type:string,
     *     meeting_date:string,
     *     meeting_time:string,
     *     meeting_link?:string|null,
     *     status:string,
     *     attachment_link?:string|null,
     *     notes?:string|null,
     *     participant_group?:string,
     *     participant_ids?:array<int,string>
     * }
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(array_keys($this->meetingTypeLabels()))],
            'meeting_date' => ['required', 'date'],
            'meeting_time' => ['required', 'date_format:H:i'],
            'meeting_link' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'string', Rule::in(['scheduled', 'completed', 'canceled'])],
            'attachment_link' => ['nullable', 'string', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'participant_group' => ['nullable', 'string', Rule::in(['all_staff', 'bod', 'custom'])],
            'participant_ids' => ['nullable', 'array'],
            'participant_ids.*' => ['string', 'max:100'],
        ]);
    }

    /**
     * @param  array<int,string>  $participantIds
     */
    private function syncParticipants(HrMeeting $meeting, string $participantGroup, array $participantIds): void
    {
        $participants = $this->participantEmployeesForGroup($participantGroup, $participantIds);

        $meeting->participants()->delete();

        $participants->each(function (Employee $employee) use ($meeting, $participantGroup): void {
            $meeting->participants()->create([
                'employee_id' => $employee->id,
                'participant_type' => $participantGroup,
                'attendance_status' => 'invited',
            ]);
        });
    }

    /**
     * @param  array<int,string>  $participantIds
     */
    private function participantEmployeesForGroup(string $participantGroup, array $participantIds)
    {
        if ($participantGroup === 'all_staff') {
            return $this->staffEmployeeQuery()
                ->orderBy('employee_code')
                ->get();
        }

        if ($participantGroup === 'bod') {
            return $this->staffEmployeeQuery()
                ->with(['deployment.position', 'deployment.positions'])
                ->orderBy('employee_code')
                ->get()
                ->filter(fn (Employee $employee): bool => $this->employeeHasSupervisorPosition($employee))
                ->values();
        }

        $participantIds = collect($participantIds)
            ->map(fn (mixed $participantId): string => trim((string) $participantId))
            ->filter()
            ->unique()
            ->values();

        return $this->staffEmployeeQuery()
            ->whereIn('id', $participantIds)
            ->orderBy('employee_code')
            ->get();
    }

    private function latestMeeting(): ?HrMeeting
    {
        return HrMeeting::query()
            ->orderByDesc('meeting_date')
            ->orderByDesc('meeting_time')
            ->first();
    }

    /**
     * @return array<string,string>
     */
    private function meetingTypeLabels(): array
    {
        return [
            'weekly_meeting' => 'Weekly Meeting',
            'bod_meeting' => 'BOD Meeting',
            'evaluation' => 'Evaluation',
            'division_meeting' => 'Division Meeting',
            'other_meeting' => 'Other Meeting',
        ];
    }

    /**
     * @return array<string,string>
     */
    private function statusBadgeClasses(): array
    {
        return [
            'scheduled' => 'badge-info',
            'completed' => 'badge-success',
            'canceled' => 'badge-danger',
        ];
    }

    /**
     * @return array<int,string>
     */
    private function monthOptions(): array
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    private function employeeOptions()
    {
        return $this->staffEmployeeQuery()
            ->with(['profile', 'user', 'deployment.position', 'deployment.positions'])
            ->orderBy('employee_code')
            ->get()
            ->map(fn (Employee $employee): array => [
                'id' => $employee->id,
                'name' => trim((string) ($employee->profile?->name ?? $employee->user?->name ?? $employee->employee_code)),
                'is_supervisor' => $this->employeeHasSupervisorPosition($employee),
            ])
            ->filter(fn (array $employee): bool => $employee['name'] !== '')
            ->values();
    }

    private function staffEmployeeQuery()
    {
        return Employee::query()
            ->where('status', 'Active')
            ->whereHas('user.roles', function ($query): void {
                $query->whereRaw('LOWER(name) = ?', ['staff']);
            })
            ->whereDoesntHave('user.roles', function ($query): void {
                $query->whereRaw('LOWER(name) = ?', ['superuser']);
            });
    }

    /**
     * @return array<string,array{title:string,subtitle:string,total:int}>
     */
    private function meetingTaskCardDefinitions(): array
    {
        return [
            'administration' => [
                'title' => 'Administration',
                'subtitle' => 'Administrative operational support',
                'total' => 8,
            ],
            'event_management_kma' => [
                'title' => 'Event Management (KMA)',
                'subtitle' => 'Event Planning & Operations',
                'total' => 8,
            ],
            'property_construction_trah' => [
                'title' => 'Property and Construction (TRAH)',
                'subtitle' => 'Property & Construction Operations',
                'total' => 8,
            ],
            'it_social_media_tms' => [
                'title' => 'IT and Social Media (TMS)',
                'subtitle' => 'Digital publication and IT operational support',
                'total' => 8,
            ],
            'others' => [
                'title' => 'Others',
                'subtitle' => 'General & Miscellaneous Support',
                'total' => 8,
            ],
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function meetingTaskCards(HrMeeting $meeting): array
    {
        $tasksByCategory = $meeting->tasks
            ->groupBy(fn (HrMeetingTask $task): string => (string) ($task->category ?: 'others'));

        return collect($this->meetingTaskCardDefinitions())
            ->map(function (array $card, string $category) use ($tasksByCategory): array {
                $tasks = $tasksByCategory->get($category, collect());
                $completed = $tasks->filter(fn (HrMeetingTask $task): bool => $task->projectTask?->status === 'completed')->count();
                $total = max((int) $card['total'], $tasks->count());
                $percentage = $total > 0 ? (int) floor(($completed / $total) * 100) : 0;

                return [
                    'key' => $category,
                    'title' => $card['title'],
                    'subtitle' => $card['subtitle'],
                    'completed' => $completed,
                    'total' => $total,
                    'percentage' => $percentage,
                    'tasks' => $tasks
                        ->sortByDesc('created_at')
                        ->map(function (HrMeetingTask $task): array {
                            $projectTask = $task->projectTask;
                            $status = (string) ($projectTask?->status ?? 'pending');

                            return [
                                'id' => $task->id,
                                'title' => $projectTask?->title ?? '-',
                                'assignee' => $this->employeeDisplayName($projectTask?->employee),
                                'due_label' => $this->projectTaskDateLabel($projectTask),
                                'status' => $status,
                                'is_completed' => $status === 'completed',
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function meetingTaskAssigneeOptions(HrMeeting $meeting)
    {
        return $meeting->participants
            ->filter(fn ($participant): bool => $participant->attendance_status === 'joined' && $participant->employee instanceof Employee)
            ->map(fn ($participant): array => [
                'id' => (string) $participant->employee->id,
                'name' => $this->employeeDisplayName($participant->employee),
            ])
            ->filter(fn (array $employee): bool => $employee['name'] !== '')
            ->unique('id')
            ->values();
    }

    private function projectTaskDateLabel(?ProjectTask $projectTask): string
    {
        if (! $projectTask instanceof ProjectTask) {
            return '-';
        }

        $startDate = $projectTask->start_date;
        $dueDate = $projectTask->due_date;

        if ($startDate && $dueDate) {
            if ($startDate->isSameDay($dueDate)) {
                return $dueDate->format('d M Y');
            }

            return $startDate->format('d M Y').' - '.$dueDate->format('d M Y');
        }

        return ($dueDate ?? $startDate)?->format('d M Y') ?? '-';
    }

    /**
     * @param  array<int,array<string,mixed>>  $meetingTaskCards
     * @return array{total:int, overdue:int, gradient:string, segments:array<int,array{label:string,count:int,color:string}>}
     */
    private function meetingTaskSummary(HrMeeting $meeting, array $meetingTaskCards): array
    {
        $colors = [
            'administration' => '#2444c3',
            'event_management_kma' => '#9b2cf3',
            'property_construction_trah' => '#22c55e',
            'it_social_media_tms' => '#f43f86',
            'others' => '#ffb000',
        ];

        $total = $meeting->tasks->count();
        $overdue = $meeting->tasks
            ->filter(function (HrMeetingTask $task): bool {
                $projectTask = $task->projectTask;

                return $projectTask instanceof ProjectTask
                    && $projectTask->status !== 'completed'
                    && $projectTask->due_date
                    && $projectTask->due_date->lt(now('Asia/Jakarta')->startOfDay());
            })
            ->count();

        $segments = collect($meetingTaskCards)
            ->map(fn (array $card): array => [
                'label' => $card['title'],
                'count' => count($card['tasks'] ?? []),
                'color' => $colors[$card['key']] ?? '#d8dde8',
            ])
            ->values()
            ->all();

        $gradient = '#edf0f5 0deg 360deg';
        if ($total > 0) {
            $cursor = 0;
            $parts = [];

            foreach ($segments as $segment) {
                $degrees = ((int) $segment['count'] / $total) * 360;
                $next = $cursor + $degrees;
                $parts[] = "{$segment['color']} {$cursor}deg {$next}deg";
                $cursor = $next;
            }

            $gradient = implode(', ', $parts);
        }

        return [
            'total' => $total,
            'overdue' => $overdue,
            'gradient' => $gradient,
            'segments' => $segments,
        ];
    }

    private function meetingAttendanceRows(HrMeeting $meeting)
    {
        return $meeting->participants
            ->filter(fn ($participant): bool => $participant->attendance_status === 'joined' && $participant->employee instanceof Employee)
            ->sortBy('joined_at')
            ->map(fn ($participant): array => [
                'name' => $this->employeeDisplayName($participant->employee),
                'time' => $participant->joined_at ? $participant->joined_at->timezone('Asia/Jakarta')->format('H:i').' WIB' : '-',
            ])
            ->values();
    }

    private function employeeDisplayName(?Employee $employee): string
    {
        if (! $employee instanceof Employee) {
            return '-';
        }

        return trim((string) ($employee->profile?->name ?? $employee->user?->name ?? $employee->employee_code)) ?: '-';
    }

    /**
     * @return array<string,mixed>
     */
    private function staffMeetingCardData(HrMeeting $meeting, bool $forceZeroTasks = false): array
    {
        return [
            'title' => $this->meetingTypeLabels()[$meeting->type] ?? $meeting->type,
            'subtitle' => $meeting->title,
            'date_time' => trim(($meeting->meeting_date?->format('d M') ?? '-').' '.substr((string) $meeting->meeting_time, 0, 5).' WIB'),
            'joined_count' => (int) ($meeting->joined_count ?? 0),
            'task_count' => $forceZeroTasks ? 0 : (int) ($meeting->staff_tasks_count ?? 0),
            'meeting_link' => $meeting->meeting_link,
            'join_url' => route('zoom-meeting.join', $meeting),
            'details_url' => route('zoom-meeting.details'),
        ];
    }

    private function employeeCanAccessMeeting(Employee $employee, HrMeeting $meeting): bool
    {
        $employee->loadMissing('deployment.position', 'deployment.positions');

        return $meeting->participants()
            ->where(function ($query) use ($employee): void {
                $query
                    ->where('employee_id', $employee->id)
                    ->orWhere(function ($legacyAllStaffQuery): void {
                        $legacyAllStaffQuery
                            ->whereNull('employee_id')
                            ->where('participant_type', 'all_staff');
                    });

                if ($this->employeeHasSupervisorPosition($employee)) {
                    $query->orWhere(function ($legacyBodQuery): void {
                        $legacyBodQuery
                            ->whereNull('employee_id')
                            ->where('participant_type', 'bod');
                    });
                }
            })
            ->exists();
    }

    /**
     * @return array<int,string>
     */
    private function selectedParticipantIds(HrMeeting $meeting): array
    {
        return $meeting->participants
            ->pluck('employee_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function selectedParticipantGroup(HrMeeting $meeting): string
    {
        if ($meeting->participants->contains('participant_type', 'all_staff')) {
            return 'all_staff';
        }

        if ($meeting->participants->contains('participant_type', 'bod')) {
            return 'bod';
        }

        return 'custom';
    }

    private function employeeHasSupervisorPosition(Employee $employee): bool
    {
        $positionNames = collect([$employee->deployment?->position?->name])
            ->merge($employee->deployment?->positions?->pluck('name') ?? collect());

        return $positionNames
            ->filter()
            ->contains(fn (mixed $positionName): bool => str_contains(strtolower((string) $positionName), 'supervisor'));
    }
}
