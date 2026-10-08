<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Employee;
use App\Models\HrMeeting;
use App\Models\HrMeetingTask;
use App\Models\Position;
use App\Models\ProjectTask;
use App\Services\HrMeetingNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class HrMeetingController extends Controller
{
    public function staffIndex(Request $request): View
    {
        $completedMonth = min(12, max(1, (int) $request->query('completed_month', now('Asia/Jakarta')->month)));
        $completedYear = (int) $request->query('completed_year', now('Asia/Jakarta')->year);
        $employee = auth()->user()?->employee;
        $scheduledMeetings = collect();
        $completedMeetings = collect();

        if ($employee instanceof Employee && (bool) $employee->is_core_staff) {
            $employee->loadMissing('deployment.position', 'deployment.positions');

            $baseQuery = HrMeeting::query()
                ->with([
                    'tasks:id,hr_meeting_id,project_task_id',
                    'tasks.projectTask:id,attachment_path',
                ])
                ->withCount([
                    'participants as joined_count' => fn ($query) => $query->where('attendance_status', 'joined'),
                    'tasks',
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
                ->map(fn (HrMeeting $meeting): array => $this->staffMeetingCardData($meeting));
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

    public function staffJoin(Request $request, HrMeeting $hrMeeting): RedirectResponse|JsonResponse
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

        if ($request->expectsJson()) {
            $joinedCount = $hrMeeting->participants()
                ->where('attendance_status', 'joined')
                ->count();

            if (! $hrMeeting->meeting_link) {
                return response()->json([
                    'message' => 'Meeting link is not available.',
                    'joined_count' => $joinedCount,
                    'joined_label' => $joinedCount.' Staff Joined',
                ], 422);
            }

            return response()->json([
                'meeting_id' => (string) $hrMeeting->id,
                'meeting_link' => $hrMeeting->meeting_link,
                'joined_count' => $joinedCount,
                'joined_label' => $joinedCount.' Staff Joined',
            ]);
        }

        if ($hrMeeting->meeting_link) {
            return redirect()->away($hrMeeting->meeting_link);
        }

        return redirect()
            ->route('zoom-meeting.index')
            ->with('error', 'Meeting link is not available.');
    }

    public function staffDetails(HrMeeting $hrMeeting): View
    {
        $employee = auth()->user()?->employee;

        if (! $employee instanceof Employee || ! $this->employeeCanAccessMeeting($employee, $hrMeeting)) {
            abort(403);
        }

        $hrMeeting->loadCount([
            'participants as joined_count' => fn ($query) => $query->where('attendance_status', 'joined'),
            'tasks',
        ]);

        $hrMeeting->load([
            'tasks.projectTask.employee.profile',
            'tasks.projectTask.employee.user',
            'tasks.projectTask.assignedBy',
            'tasks.projectTask.project',
            'participants.employee.profile',
            'participants.employee.user',
        ]);

        $meetingTaskCards = $this->meetingTaskCards($hrMeeting, $employee, 'zoom-meeting.tasks.update', true);

        return view('meetings.staff.details', [
            'meeting' => $hrMeeting,
            'meetingTypeLabels' => $this->meetingTypeLabels(),
            'meetingTaskCards' => $meetingTaskCards,
            'meetingTaskSummary' => $this->meetingTaskSummary($hrMeeting, $meetingTaskCards),
            'meetingAttendanceRows' => $this->meetingAttendanceRows($hrMeeting),
            'meetingStaffAvatars' => $this->meetingStaffAvatars($hrMeeting),
        ]);
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

        return DataTables::collection($meetings)
            ->with('meetings', $meetings)
            ->toJson();
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
            'tasks.projectTask.assignedBy',
            'tasks.projectTask.project',
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
            'meetingStaffAvatars' => $this->meetingStaffAvatars($meeting),
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

        $meeting->load([
            'participants.employee.profile',
            'participants.employee.user',
            'tasks.projectTask.employee.profile',
            'tasks.projectTask.employee.user',
            'tasks.projectTask.assignedBy',
            'tasks.projectTask.project',
        ]);

        $meetingTaskCards = $this->meetingTaskCards($meeting);

        return view('meetings.admin.update', [
            'meeting' => $meeting,
            'employeeOptions' => $this->employeeOptions(),
            'meetingTypeLabels' => $this->meetingTypeLabels(),
            'selectedParticipantGroup' => $this->selectedParticipantGroup($meeting),
            'selectedParticipantIds' => $this->selectedParticipantIds($meeting),
            'meetingAttendanceRows' => $this->meetingAttendanceRows($meeting),
            'meetingTaskCards' => $meetingTaskCards,
            'meetingTaskSummary' => $this->meetingTaskSummary($meeting, $meetingTaskCards),
            'meetingTaskAssigneeOptions' => $this->meetingTaskAssigneeOptions($meeting),
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
            AppNotification::query()
                ->where('type', 'hr_meeting_scheduled')
                ->where('url', 'like', '%'.$hrMeeting->id.'%')
                ->delete();

            $hrMeeting->participants()->delete();
            $hrMeeting->attachments()->delete();
            $hrMeeting->momExports()->update(['hr_meeting_id' => null]);
            $hrMeeting->tasks()->delete();

            $hrMeeting->forceDelete();
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

    public function updateTask(Request $request, HrMeeting $hrMeeting, HrMeetingTask $hrMeetingTask): RedirectResponse
    {
        $this->updateMeetingTaskFromRequest($request, $hrMeeting, $hrMeetingTask, true);

        return back()->with('status', 'Task meeting has been updated.');
    }

    public function updateStaffTask(Request $request, HrMeeting $hrMeeting, HrMeetingTask $hrMeetingTask): RedirectResponse
    {
        $employee = auth()->user()?->employee;

        if (! $employee instanceof Employee || ! $this->employeeCanAccessMeeting($employee, $hrMeeting)) {
            abort(403);
        }

        $this->ensureTaskBelongsToMeeting($hrMeeting, $hrMeetingTask);

        $projectTask = $hrMeetingTask->projectTask;

        if (! $projectTask instanceof ProjectTask || (string) $projectTask->employee_id !== (string) $employee->id) {
            abort(403);
        }

        $this->updateMeetingTaskFromRequest($request, $hrMeeting, $hrMeetingTask, false);

        return back()->with('status', 'Task meeting has been updated.');
    }

    private function updateMeetingTaskFromRequest(Request $request, HrMeeting $hrMeeting, HrMeetingTask $hrMeetingTask, bool $allowAssigneeUpdate): void
    {
        $this->ensureTaskBelongsToMeeting($hrMeeting, $hrMeetingTask);

        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys($this->meetingTaskCardDefinitions()))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => [$allowAssigneeUpdate ? 'required' : 'nullable', 'string', 'exists:employees,id'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high'])],
            'status' => ['required', 'string', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'blockers' => ['nullable', 'string', 'max:5000'],
        ]);

        $projectTask = $hrMeetingTask->projectTask;
        $assigneeId = (string) ($projectTask?->employee_id ?? $validated['assigned_to'] ?? '');

        if ($allowAssigneeUpdate) {
            $assigneeId = (string) $validated['assigned_to'];

            $assigneeIds = $this->meetingTaskAssigneeOptions($hrMeeting->loadMissing('participants.employee'))
                ->pluck('id')
                ->map(fn (mixed $employeeId): string => (string) $employeeId);

            if (! $assigneeIds->contains($assigneeId)) {
                throw ValidationException::withMessages([
                    'assigned_to' => 'Staff task harus staff yang sudah joined di meeting ini.',
                ]);
            }
        }

        $hasDescription = $request->has('description');
        $hasPriority = $request->has('priority');
        $hasBlockers = $request->has('blockers');
        $hasAttachmentPath = $request->has('attachment_path');

        DB::transaction(function () use ($hrMeetingTask, $projectTask, $validated, $assigneeId, $hasDescription, $hasPriority, $hasBlockers, $hasAttachmentPath): void {
            $status = strtolower(trim((string) $validated['status']));
            $title = trim((string) $validated['title']);
            $completedAt = $status === 'completed' ? now('Asia/Jakarta') : null;
            $projectTaskPayload = [
                'employee_id' => $assigneeId,
                'title' => $title,
                'description' => $hasDescription
                    ? $this->nullableStringValue($validated['description'] ?? null)
                    : ($projectTask?->description ?? null),
                'status' => $status,
                'priority' => $hasPriority
                    ? strtolower(trim((string) ($validated['priority'] ?? 'medium')))
                    : (strtolower(trim((string) ($projectTask?->priority ?? 'medium'))) ?: 'medium'),
                'start_date' => $validated['start_date'],
                'due_date' => $validated['due_date'],
                'blockers' => $hasBlockers
                    ? $this->nullableStringValue($validated['blockers'] ?? null)
                    : ($projectTask?->blockers ?? null),
                'attachment_path' => $hasAttachmentPath
                    ? $this->nullableStringValue($validated['attachment_path'] ?? null)
                    : ($projectTask?->attachment_path ?? null),
                'completed_at' => $status === 'completed'
                    ? ($projectTask?->completed_at ?? $completedAt)
                    : null,
            ];

            if (! $projectTask instanceof ProjectTask) {
                $projectTask = ProjectTask::query()->create($projectTaskPayload + [
                    'assigned_by' => auth()->id(),
                ]);

                $hrMeetingTask->project_task_id = $projectTask->id;
            } else {
                $projectTask->update($projectTaskPayload);
            }

            $hrMeetingTask->category = $validated['category'];
            $hrMeetingTask->save();
        });
    }

    public function destroyTask(HrMeeting $hrMeeting, HrMeetingTask $hrMeetingTask): RedirectResponse
    {
        $this->ensureTaskBelongsToMeeting($hrMeeting, $hrMeetingTask);

        DB::transaction(function () use ($hrMeetingTask): void {
            $projectTask = $hrMeetingTask->projectTask;

            $hrMeetingTask->delete();

            if ($projectTask instanceof ProjectTask) {
                $projectTask->delete();
            }
        });

        return back()->with('status', 'Task meeting and related project task have been deleted.');
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
        $selectedEmployeeIds = $participants
            ->pluck('id')
            ->map(fn (mixed $employeeId): string => (string) $employeeId)
            ->values()
            ->all();

        $meeting->participants()
            ->whereNull('employee_id')
            ->delete();

        $obsoleteParticipantsQuery = $meeting->participants()->whereNotNull('employee_id');

        if ($selectedEmployeeIds === []) {
            $obsoleteParticipantsQuery->delete();
        } else {
            $obsoleteParticipantsQuery
                ->whereNotIn('employee_id', $selectedEmployeeIds)
                ->delete();
        }

        $participants->each(function (Employee $employee) use ($meeting, $participantGroup): void {
            $participant = $meeting->participants()
                ->where('employee_id', $employee->id)
                ->first();

            if (! $participant) {
                $participant = $meeting->participants()->make([
                    'employee_id' => $employee->id,
                    'attendance_status' => 'invited',
                ]);
            }

            $participant->participant_type = $participantGroup;
            $participant->save();
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
            ->where('is_core_staff', true)
            ->whereHas('user', function ($query): void {
                $query->where('is_active', true);
            })
            ->whereHas('user.roles', function ($query): void {
                $query->whereRaw('LOWER(name) = ?', ['staff']);
            })
            ->whereDoesntHave('deployment.position', function ($query): void {
                $query->whereSystemKey(Position::KEY_SUPER_ADMINISTRATOR);
            })
            ->whereDoesntHave('deployment.positions', function ($query): void {
                $query->whereSystemKey(Position::KEY_SUPER_ADMINISTRATOR);
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
    private function meetingTaskCards(HrMeeting $meeting, ?Employee $prioritizedEmployee = null, string $updateRouteName = 'hr-meetings.tasks.update', bool $restrictActionsToAssigned = false): array
    {
        $prioritizedEmployeeId = (string) ($prioritizedEmployee?->id ?? '');
        $tasksByCategory = $meeting->tasks
            ->groupBy(fn (HrMeetingTask $task): string => (string) ($task->category ?: 'others'));

        return collect($this->meetingTaskCardDefinitions())
            ->map(function (array $card, string $category) use ($tasksByCategory, $prioritizedEmployeeId, $updateRouteName, $restrictActionsToAssigned): array {
                $tasks = $tasksByCategory->get($category, collect());
                $completed = $tasks->filter(fn (HrMeetingTask $task): bool => $task->projectTask?->status === 'completed')->count();
                $total = $tasks->count();
                $percentage = $total > 0 ? (int) floor(($completed / $total) * 100) : 0;

                return [
                    'key' => $category,
                    'title' => $card['title'],
                    'subtitle' => $card['subtitle'],
                    'completed' => $completed,
                    'total' => $total,
                    'percentage' => $percentage,
                    'tasks' => $tasks
                        ->sort(function (HrMeetingTask $firstTask, HrMeetingTask $secondTask) use ($prioritizedEmployeeId): int {
                            $firstIsPrioritized = $prioritizedEmployeeId !== ''
                                && (string) $firstTask->projectTask?->employee_id === $prioritizedEmployeeId;
                            $secondIsPrioritized = $prioritizedEmployeeId !== ''
                                && (string) $secondTask->projectTask?->employee_id === $prioritizedEmployeeId;
                            $priorityComparison = (int) $secondIsPrioritized <=> (int) $firstIsPrioritized;

                            if ($priorityComparison !== 0) {
                                return $priorityComparison;
                            }

                            return ($secondTask->created_at?->getTimestamp() ?? 0) <=> ($firstTask->created_at?->getTimestamp() ?? 0);
                        })
                        ->map(function (HrMeetingTask $task) use ($card, $updateRouteName, $restrictActionsToAssigned, $prioritizedEmployeeId): array {
                            $projectTask = $task->projectTask;
                            $status = (string) ($projectTask?->status ?? 'pending');
                            $startDate = $projectTask?->start_date;
                            $dueDate = $projectTask?->due_date;
                            $isAssignedToPrioritizedEmployee = $prioritizedEmployeeId !== ''
                                && (string) $projectTask?->employee_id === $prioritizedEmployeeId;
                            $canUpdate = ! $restrictActionsToAssigned || $isAssignedToPrioritizedEmployee;
                            $canDelete = ! $restrictActionsToAssigned;

                            return [
                                'id' => $task->id,
                                'category' => (string) ($task->category ?: 'others'),
                                'project_task_id' => $projectTask?->id,
                                'title' => $projectTask?->title ?? '-',
                                'description' => $projectTask?->description ?: ($projectTask?->title ?? '-'),
                                'attachment_path' => trim((string) ($projectTask?->attachment_path ?? '')),
                                'blockers' => trim((string) ($projectTask?->blockers ?? '')),
                                'priority' => strtolower(trim((string) ($projectTask?->priority ?? 'medium'))) ?: 'medium',
                                'project_id' => $projectTask?->project_id,
                                'project_name' => trim((string) ($projectTask?->project?->name ?? '')) ?: 'Pilih Nama Project',
                                'assignee_id' => $projectTask?->employee_id,
                                'assignee' => $this->employeeDisplayName($projectTask?->employee),
                                'assigned_by' => trim((string) ($projectTask?->assignedBy?->username ?? 'self')) ?: 'self',
                                'task_category_label' => $card['title'],
                                'task_category_description' => $card['subtitle'],
                                'start_date' => $startDate?->format('Y-m-d'),
                                'start_date_label' => $startDate?->format('d M Y') ?? '-',
                                'due_date' => $dueDate?->format('Y-m-d'),
                                'due_date_label' => $dueDate?->format('d M Y') ?? '-',
                                'date_range_label' => $this->projectTaskDateLabel($projectTask),
                                'due_label' => $this->projectTaskDateLabel($projectTask),
                                'status' => $status,
                                'status_label' => $this->meetingTaskStatusLabel($status),
                                'status_class' => $this->meetingTaskStatusClass($status),
                                'is_completed' => $status === 'completed',
                                'can_update' => $canUpdate,
                                'can_delete' => $canDelete,
                                'update_url' => route($updateRouteName, [$task->hr_meeting_id, $task->id]),
                                'destroy_url' => route('hr-meetings.tasks.destroy', [$task->hr_meeting_id, $task->id]),
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

    private function ensureTaskBelongsToMeeting(HrMeeting $meeting, HrMeetingTask $task): void
    {
        abort_unless((string) $task->hr_meeting_id === (string) $meeting->id, 404);
    }

    private function meetingTaskStatusLabel(string $status): string
    {
        return match (strtolower(trim($status))) {
            'completed' => 'Completed',
            'in_progress' => 'On Progress',
            'cancelled' => 'Cancelled',
            default => 'To Do',
        };
    }

    private function meetingTaskStatusClass(string $status): string
    {
        return match (strtolower(trim($status))) {
            'completed' => 'text-success',
            'in_progress' => 'text-primary',
            'cancelled' => 'text-danger',
            default => 'text-warning',
        };
    }

    private function nullableStringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
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

    private function meetingStaffAvatars(HrMeeting $meeting)
    {
        return $meeting->participants
            ->filter(fn ($participant): bool => $participant->employee instanceof Employee)
            ->map(function ($participant): array {
                $name = $this->employeeDisplayName($participant->employee);

                return [
                    'id' => (string) $participant->employee->id,
                    'name' => $name,
                    'initials' => $this->initials($name),
                    'avatar_url' => $this->employeeAvatarUrl($participant->employee->profile?->profile_picture_path),
                ];
            })
            ->unique('id')
            ->values();
    }

    private function employeeDisplayName(?Employee $employee): string
    {
        if (! $employee instanceof Employee) {
            return '-';
        }

        return trim((string) ($employee->profile?->name ?? $employee->user?->name ?? $employee->employee_code)) ?: '-';
    }

    private function initials(string $name): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->map(fn (string $part): string => Str::substr($part, 0, 1))
            ->take(2)
            ->implode('');

        return Str::upper($initials !== '' ? $initials : 'S');
    }

    private function employeeAvatarUrl(mixed $profilePicturePath): string
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

    /**
     * @return array<string,mixed>
     */
    private function staffMeetingCardData(HrMeeting $meeting, bool $forceZeroTasks = false): array
    {
        $taskAttachmentUrl = $meeting->tasks
            ->map(fn (HrMeetingTask $task): string => trim((string) ($task->projectTask?->attachment_path ?? '')))
            ->first(static fn (string $attachmentPath): bool => $attachmentPath !== '') ?? '';
        $meetingAttachmentUrl = trim((string) ($meeting->attachment_link ?? ''));

        return [
            'id' => (string) $meeting->id,
            'title' => $this->meetingTypeLabels()[$meeting->type] ?? $meeting->type,
            'subtitle' => $meeting->title,
            'date_time' => trim(($meeting->meeting_date?->format('d M') ?? '-').' '.substr((string) $meeting->meeting_time, 0, 5).' WIB'),
            'joined_count' => (int) ($meeting->joined_count ?? 0),
            'task_count' => $forceZeroTasks ? 0 : (int) ($meeting->tasks_count ?? 0),
            'task_attachment_url' => $taskAttachmentUrl !== '' ? $taskAttachmentUrl : $meetingAttachmentUrl,
            'meeting_link' => $meeting->meeting_link,
            'join_url' => route('zoom-meeting.join', $meeting),
            'details_url' => route('zoom-meeting.details', $meeting),
        ];
    }

    private function employeeCanAccessMeeting(Employee $employee, HrMeeting $meeting): bool
    {
        if (! (bool) $employee->is_core_staff) {
            return false;
        }

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
        $positionSystemKeys = collect([$employee->deployment?->position?->system_key])
            ->merge($employee->deployment?->positions?->pluck('system_key') ?? collect());

        return $positionSystemKeys
            ->filter()
            ->contains(Position::KEY_SUPERVISOR);
    }
}
