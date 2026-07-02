<?php

namespace App\Http\Controllers\AdminAttendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceHoliday;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceLeaveController extends Controller
{
    public function index(Request $request): View
    {
        $selectedPeriod = $this->selectedPeriodFor($request);

        return view('admin_attendance.leave.index', [
            'leaveOverviewStats' => $this->leaveOverviewStatsFor($request),
            'leavePendingCards' => $this->pendingLeaveCardsFor($request, $selectedPeriod),
            'leaveGridPositionGroups' => $this->leaveGridPositionGroupsFor($request, $selectedPeriod),
            'leaveSelectedMonth' => $selectedPeriod['month'],
            'leaveSelectedYear' => $selectedPeriod['year'],
            'leaveMonthOptions' => $this->leaveMonthOptions($selectedPeriod['year']),
            'leaveYearOptions' => $this->leaveYearOptions($selectedPeriod['year']),
        ]);
    }

    public function detail(Request $request, string $uid): View
    {
        $now = now('Asia/Jakarta')->startOfDay();
        $authenticatedUser = $request->user();
        $companyId = $authenticatedUser instanceof User
            ? $this->currentCompanyIdFor($authenticatedUser)
            : null;
        $activeEmployeeIds = $this->activeEmployeeIdsFor($now, $companyId);
        $year = (int) $now->year;

        $leaveRequest = $this->baseLeaveRequestQuery($activeEmployeeIds)
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.user:id,username,email',
                'employee.deployment:id,employee_id,join_date',
                'leaveType:id,code,name',
                'leaveType.subTypes:id,leave_type_id,name',
                'histories' => function ($query): void {
                    $query
                        ->select(['id', 'leave_request_id', 'event_type', 'title', 'from_status', 'to_status', 'metadata', 'happened_at'])
                        ->oldest('happened_at');
                },
            ])
            ->findOrFail($uid);
        $employeeId = trim((string) $leaveRequest->employee_id);

        return view('admin_attendance.leave.detail', [
            'leaveSummaryYear' => $year,
            'leaveEligibility' => $this->leaveBalanceCardsFor($leaveRequest, $year, $now),
            'leaveTracker' => $this->leaveTrackerFor($employeeId, $year, (int) $now->month),
            'leaveApproval' => $this->leaveApprovalFor($leaveRequest),
        ]);
    }

    public function updateApproval(Request $request, string $uid): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);
        $now = now('Asia/Jakarta');
        $authenticatedUser = $request->user();
        $companyId = $authenticatedUser instanceof User
            ? $this->currentCompanyIdFor($authenticatedUser)
            : null;
        $activeEmployeeIds = $this->activeEmployeeIdsFor($now->copy()->startOfDay(), $companyId);
        $targetStatus = (string) $validated['status'];

        DB::transaction(function () use ($activeEmployeeIds, $authenticatedUser, $now, $targetStatus, $uid): void {
            $leaveRequest = $this->baseLeaveRequestQuery($activeEmployeeIds)
                ->whereKey($uid)
                ->lockForUpdate()
                ->firstOrFail();
            $currentStatus = strtolower(trim((string) $leaveRequest->status));
            if ($currentStatus !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Leave request sudah memiliki keputusan akhir.',
                ]);
            }

            $hrVerificationHistory = LeaveRequestHistory::query()
                ->where('leave_request_id', $leaveRequest->id)
                ->where('event_type', 'hr_verification')
                ->where('from_status', 'pending')
                ->where('to_status', 'pending')
                ->latest('happened_at')
                ->lockForUpdate()
                ->first();
            if (! $hrVerificationHistory instanceof LeaveRequestHistory) {
                throw ValidationException::withMessages([
                    'status' => 'Leave request belum berada pada tahap HR Verification yang menunggu keputusan.',
                ]);
            }

            $hrVerificationHistory->update([
                'to_status' => $targetStatus,
            ]);

            $actorUserId = $authenticatedUser instanceof User ? (string) $authenticatedUser->id : null;
            $leaveRequest->update([
                'status' => $targetStatus,
                'approved_by' => $targetStatus === 'approved' ? $actorUserId : null,
                'approved_at' => $targetStatus === 'approved' ? $now : null,
            ]);

            if ($targetStatus === 'approved') {
                $this->syncAnnualLeaveBalance($leaveRequest);
            }

            LeaveRequestHistory::query()->create([
                'leave_request_id' => $leaveRequest->id,
                'actor_user_id' => $actorUserId,
                'event_type' => 'status_updated',
                'title' => 'Final Decision',
                'from_status' => 'pending',
                'to_status' => $targetStatus,
                'notes' => null,
                'metadata' => null,
                'happened_at' => $now,
            ]);
        });

        return redirect()
            ->route('admin-attendance.leave.detail', ['uid' => $uid])
            ->with('success', 'Leave request berhasil '.($targetStatus === 'approved' ? 'disetujui.' : 'ditolak.'));
    }

    public function pendingDatatable(Request $request): JsonResponse
    {
        return $this->leaveRequestsDatatable($request, 'pending');
    }

    public function approvedDatatable(Request $request): JsonResponse
    {
        return $this->leaveRequestsDatatable($request, 'approved');
    }

    /**
     * @return array{
     *     pending: int,
     *     rejected: int,
     *     approved: int,
     *     this_week: int,
     *     next_week: int,
     *     annual: int,
     *     sick: int,
     *     special: int,
     *     unpaid: int
     * }
     */
    private function leaveOverviewStatsFor(Request $request): array
    {
        $now = now('Asia/Jakarta')->startOfDay();
        $authenticatedUser = $request->user();
        $companyId = $authenticatedUser instanceof User
            ? $this->currentCompanyIdFor($authenticatedUser)
            : null;
        $activeEmployeeIds = $this->activeEmployeeIdsFor($now, $companyId);

        if ($activeEmployeeIds->isEmpty()) {
            return $this->emptyLeaveOverviewStats();
        }

        $statusCounts = $this->baseLeaveRequestQuery($activeEmployeeIds)
            ->selectRaw('LOWER(COALESCE(status, "")) as normalized_status, COUNT(*) as aggregate')
            ->groupBy('normalized_status')
            ->pluck('aggregate', 'normalized_status');

        $thisWeekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $thisWeekEnd = $thisWeekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $nextWeekStart = $thisWeekStart->copy()->addWeek();
        $nextWeekEnd = $thisWeekEnd->copy()->addWeek();

        return [
            'pending' => $this->countSupervisorApprovedPendingLeaveRequests($activeEmployeeIds),
            'rejected' => (int) ($statusCounts['rejected'] ?? 0) + (int) ($statusCounts['refused'] ?? 0),
            'approved' => (int) ($statusCounts['approved'] ?? 0),
            'this_week' => $this->countLeaveRequestsBetween($activeEmployeeIds, $thisWeekStart, $thisWeekEnd),
            'next_week' => $this->countLeaveRequestsBetween($activeEmployeeIds, $nextWeekStart, $nextWeekEnd),
            'annual' => $this->countLeaveRequestsByType($activeEmployeeIds, ['ANNUAL', 'ANNUAL_LEAVE'], ['annual leave', 'cuti tahunan']),
            'sick' => $this->countLeaveRequestsByType($activeEmployeeIds, ['SICK', 'SICK_LEAVE'], ['sick leave', 'sakit']),
            'special' => $this->countLeaveRequestsByType($activeEmployeeIds, ['SPECIAL', 'SPECIAL_LEAVE'], ['special leave', 'cuti khusus']),
            'unpaid' => $this->countLeaveRequestsByType($activeEmployeeIds, ['UNPAID', 'UNPAID_LEAVE'], ['unpaid leave', 'cuti tidak dibayar']),
        ];
    }

    /**
     * @return array{
     *     pending: int,
     *     rejected: int,
     *     approved: int,
     *     this_week: int,
     *     next_week: int,
     *     annual: int,
     *     sick: int,
     *     special: int,
     *     unpaid: int
     * }
     */
    private function emptyLeaveOverviewStats(): array
    {
        return [
            'pending' => 0,
            'rejected' => 0,
            'approved' => 0,
            'this_week' => 0,
            'next_week' => 0,
            'annual' => 0,
            'sick' => 0,
            'special' => 0,
            'unpaid' => 0,
        ];
    }

    private function currentCompanyIdFor(User $user): ?string
    {
        $user->loadMissing('employee.deployment:id,employee_id,current_company_id');

        $companyId = $user->employee?->deployment?->current_company_id;

        return is_string($companyId) && trim($companyId) !== '' ? trim($companyId) : null;
    }

    /**
     * @return Collection<int, string>
     */
    private function activeEmployeeIdsFor(Carbon $date, ?string $companyId): Collection
    {
        if (! is_string($companyId) || trim($companyId) === '') {
            return collect();
        }

        $todayDate = $date->toDateString();

        return Employee::query()
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
            ->whereHas('user', function (Builder $query): void {
                $query->where('is_active', true);
            })
            ->whereHas('deployment', function (Builder $query) use ($todayDate, $companyId): void {
                $query
                    ->whereNull('deleted_at')
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
                    ->where('current_company_id', $companyId)
                    ->where(function (Builder $query) use ($todayDate): void {
                        $query
                            ->whereNull('join_date')
                            ->orWhereDate('join_date', '<=', $todayDate);
                    })
                    ->where(function (Builder $query) use ($todayDate): void {
                        $query
                            ->whereNull('resignation_date')
                            ->orWhereDate('resignation_date', '>=', $todayDate);
                    });
            })
            ->pluck('id')
            ->filter(static fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->values();
    }

    private function baseLeaveRequestQuery(Collection $activeEmployeeIds): Builder
    {
        return LeaveRequest::query()
            ->whereIn('employee_id', $activeEmployeeIds)
            ->where('is_active', true)
            ->whereNull('deleted_at');
    }

    private function countLeaveRequestsBetween(Collection $activeEmployeeIds, Carbon $startDate, Carbon $endDate): int
    {
        return (int) $this->supervisorApprovedLeaveRequestQuery($activeEmployeeIds)
            ->whereDate('start_date', '<=', $endDate->toDateString())
            ->whereDate('end_date', '>=', $startDate->toDateString())
            ->count();
    }

    private function supervisorApprovedLeaveRequestQuery(Collection $activeEmployeeIds): Builder
    {
        return $this->applySupervisorApprovedReviewFilter(
            $this->baseLeaveRequestQuery($activeEmployeeIds)
        );
    }

    private function countSupervisorApprovedPendingLeaveRequests(Collection $activeEmployeeIds): int
    {
        return (int) $this->applySupervisorApprovedReviewFilter(
            $this->baseLeaveRequestQuery($activeEmployeeIds)
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['pending'])
        )->count();
    }

    /**
     * @param  array{month: int, year: int}  $selectedPeriod
     * @return Collection<int, array{
     *     name:string,
     *     initial:string,
     *     leave_type:string,
     *     reason:string,
     *     date_label:string,
     *     supervisor_name:string,
     *     detail_url:string
     * }>
     */
    private function pendingLeaveCardsFor(Request $request, array $selectedPeriod): Collection
    {
        $now = now('Asia/Jakarta')->startOfDay();
        $authenticatedUser = $request->user();
        $companyId = $authenticatedUser instanceof User
            ? $this->currentCompanyIdFor($authenticatedUser)
            : null;
        $activeEmployeeIds = $this->activeEmployeeIdsFor($now, $companyId);
        $periodStart = Carbon::create($selectedPeriod['year'], $selectedPeriod['month'], 1, 0, 0, 0, 'Asia/Jakarta')->startOfDay();
        $periodEnd = $periodStart->copy()->endOfMonth()->startOfDay();

        if ($activeEmployeeIds->isEmpty()) {
            return collect();
        }

        return $this->applySupervisorApprovedReviewFilter(
            $this->baseLeaveRequestQuery($activeEmployeeIds)
                ->with([
                    'employee:id,user_id',
                    'employee.profile:id,employee_id,name',
                    'employee.user:id,username,email',
                    'leaveType:id,code,name',
                    'histories' => function ($query): void {
                        $query
                            ->select(['id', 'leave_request_id', 'event_type', 'to_status'])
                            ->where('event_type', 'supervisor_review')
                            ->where('to_status', 'approved')
                            ->oldest('happened_at');
                    },
                ])
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['pending'])
                ->whereDate('start_date', '<=', $periodEnd->toDateString())
                ->whereDate('end_date', '>=', $periodStart->toDateString())
        )
            ->orderBy('start_date')
            ->orderBy('created_at')
            ->limit(6)
            ->get(['id', 'employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'reason', 'status'])
            ->map(function (LeaveRequest $leaveRequest): array {
                $employeeName = $this->employeeDisplayName($leaveRequest->employee);

                return [
                    'name' => $employeeName,
                    'initial' => Str::upper(Str::substr($employeeName !== '-' ? $employeeName : 'L', 0, 1)),
                    'leave_type' => $this->leaveTypeCardLabel($leaveRequest),
                    'reason' => trim((string) $leaveRequest->reason) !== '' ? trim((string) $leaveRequest->reason) : '-',
                    'date_label' => $this->leaveRequestPeriodLabel($leaveRequest),
                    'supervisor_name' => $this->supervisorDisplayName($leaveRequest),
                    'detail_url' => route('admin-attendance.leave.detail', ['uid' => $leaveRequest->id]),
                ];
            })
            ->values();
    }

    /**
     * @param  array{month: int, year: int}  $selectedPeriod
     * @return Collection<int, array{position_name: string, employees: Collection<int, array{name: string, initial: string, department_name: string, annual_days_label: string, annual_start_label: ?string, sick_days_label: string, special_days_label: string, unpaid_days_label: string}>}>
     */
    private function leaveGridPositionGroupsFor(Request $request, array $selectedPeriod): Collection
    {
        $now = now('Asia/Jakarta')->startOfDay();
        $authenticatedUser = $request->user();
        $companyId = $authenticatedUser instanceof User
            ? $this->currentCompanyIdFor($authenticatedUser)
            : null;
        $activeEmployeeIds = $this->activeEmployeeIdsFor($now, $companyId);

        if ($activeEmployeeIds->isEmpty()) {
            return collect();
        }

        $periodStart = Carbon::create($selectedPeriod['year'], $selectedPeriod['month'], 1, 0, 0, 0, 'Asia/Jakarta')->startOfDay();
        $periodEnd = $periodStart->copy()->endOfMonth()->startOfDay();
        $leaveRequestsByEmployee = $this->baseLeaveRequestQuery($activeEmployeeIds)
            ->with('leaveType:id,code,name')
            ->whereDate('start_date', '<=', $periodEnd->toDateString())
            ->whereDate('end_date', '>=', $periodStart->toDateString())
            ->orderBy('start_date')
            ->get(['id', 'employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days'])
            ->groupBy('employee_id');

        return Employee::query()
            ->whereIn('id', $activeEmployeeIds)
            ->with([
                'profile:id,employee_id,name',
                'user:id,username,email',
                'deployment:id,employee_id,current_position_id,current_company_id,current_department_id',
                'deployment.position:id,name',
                'deployment.positions:id,name',
                'deployment.department:id,name',
            ])
            ->get(['id', 'user_id'])
            ->flatMap(function (Employee $employee): Collection {
                $positions = collect([$employee->deployment?->position])
                    ->merge($employee->deployment?->positions ?? [])
                    ->filter()
                    ->unique('id')
                    ->values();

                return $positions->map(fn ($position): array => [
                    'position_id' => (string) $position->id,
                    'position_name' => (string) $position->name,
                    'employee' => $employee,
                ]);
            })
            ->groupBy('position_id')
            ->map(function (Collection $positionRows) use ($leaveRequestsByEmployee): array {
                $positionName = $positionRows->first()['position_name'] ?? null;
                $employees = $positionRows->pluck('employee');

                return [
                    'position_name' => is_string($positionName) && trim($positionName) !== '' ? trim($positionName) : '-',
                    'employees' => $employees
                        ->sortBy(fn (Employee $employee): string => $this->employeeDisplayName($employee))
                        ->map(fn (Employee $employee): array => $this->leaveGridEmployeeData(
                            $employee,
                            $leaveRequestsByEmployee->get($employee->id, collect())
                        ))
                        ->values(),
                ];
            })
            ->sortBy('position_name')
            ->values();
    }

    /**
     * @param  Collection<int, LeaveRequest>  $leaveRequests
     * @return array{name: string, initial: string, department_name: string, annual_days_label: string, annual_start_label: ?string, sick_days_label: string, special_days_label: string, unpaid_days_label: string}
     */
    private function leaveGridEmployeeData(Employee $employee, Collection $leaveRequests): array
    {
        $leaveDays = [
            'Annual' => 0,
            'Sick' => 0,
            'Special' => 0,
            'Unpaid' => 0,
        ];
        $annualStartDate = null;

        foreach ($leaveRequests as $leaveRequest) {
            $leaveType = $this->leaveTypeShortLabel($leaveRequest);
            if (! array_key_exists($leaveType, $leaveDays)) {
                continue;
            }

            $leaveDays[$leaveType] += max((int) $leaveRequest->total_days, 0);

            if ($leaveType === 'Annual') {
                $startDate = Carbon::parse($leaveRequest->start_date)->timezone('Asia/Jakarta')->startOfDay();
                if ($annualStartDate === null || $startDate->lessThan($annualStartDate)) {
                    $annualStartDate = $startDate;
                }
            }
        }

        $employeeName = $this->employeeDisplayName($employee);
        $departmentName = $employee->deployment?->department?->name;

        return [
            'name' => $employeeName,
            'initial' => Str::upper(Str::substr($employeeName !== '-' ? $employeeName : 'E', 0, 1)),
            'department_name' => is_string($departmentName) && trim($departmentName) !== '' ? trim($departmentName) : '-',
            'annual_days_label' => $this->leaveDaysLabel($leaveDays['Annual']),
            'annual_start_label' => $annualStartDate?->format('d M'),
            'sick_days_label' => $this->leaveDaysLabel($leaveDays['Sick']),
            'special_days_label' => $this->leaveDaysLabel($leaveDays['Special']),
            'unpaid_days_label' => $this->leaveDaysLabel($leaveDays['Unpaid']),
        ];
    }

    private function leaveDaysLabel(int $days): string
    {
        return $days.' '.($days === 1 ? 'day' : 'days');
    }

    /**
     * @param  array<int, string>  $codes
     * @param  array<int, string>  $names
     */
    private function countLeaveRequestsByType(Collection $activeEmployeeIds, array $codes, array $names): int
    {
        return (int) $this->supervisorApprovedLeaveRequestQuery($activeEmployeeIds)
            ->whereHas('leaveType', function (Builder $query) use ($codes, $names): void {
                $query->where(function (Builder $query) use ($codes, $names): void {
                    $query
                        ->whereIn('code', $codes)
                        ->orWhereIn('name', $names)
                        ->orWhereIn('name', array_map('ucwords', $names));
                });
            })
            ->count();
    }

    private function leaveRequestsDatatable(Request $request, string $status): JsonResponse
    {
        $now = now('Asia/Jakarta')->startOfDay();
        $authenticatedUser = $request->user();
        $companyId = $authenticatedUser instanceof User
            ? $this->currentCompanyIdFor($authenticatedUser)
            : null;
        $activeEmployeeIds = $this->activeEmployeeIdsFor($now, $companyId);
        $selectedPeriod = $this->selectedPeriodFor($request);
        $periodStart = Carbon::create($selectedPeriod['year'], $selectedPeriod['month'], 1, 0, 0, 0, 'Asia/Jakarta')->startOfDay();
        $periodEnd = $periodStart->copy()->endOfMonth()->startOfDay();

        if ($activeEmployeeIds->isEmpty()) {
            return response()->json([
                'data' => [],
            ]);
        }

        $query = $this->baseLeaveRequestQuery($activeEmployeeIds)
            ->with([
                'employee:id,user_id',
                'employee.profile:id,employee_id,name',
                'employee.user:id,username,email',
                'leaveType:id,code,name',
            ])
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', [strtolower($status)])
            ->whereDate('start_date', '<=', $periodEnd->toDateString())
            ->whereDate('end_date', '>=', $periodStart->toDateString());

        if (strtolower($status) === 'pending') {
            $query = $this->applySupervisorApprovedReviewFilter($query);
        }

        $rows = $query
            ->orderBy('start_date')
            ->orderBy('created_at')
            ->get(['id', 'employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'status'])
            ->values()
            ->map(fn (LeaveRequest $leaveRequest, int $index): array => [
                'no' => $index + 1,
                'date' => $this->leaveRequestPeriodLabel($leaveRequest),
                'name' => $this->employeeDisplayName($leaveRequest->employee),
                'type' => $this->leaveTypeShortLabel($leaveRequest),
                'type_badge' => $this->leaveTypeBadge($leaveRequest),
                'detail_url' => route('admin-attendance.leave.detail', ['uid' => $leaveRequest->id]),
            ]);

        return response()->json([
            'data' => $rows,
        ]);
    }

    private function applySupervisorApprovedReviewFilter(Builder $query): Builder
    {
        return $query->whereHas('histories', function (Builder $query): void {
            $query
                ->where('event_type', 'supervisor_review')
                ->where('to_status', 'approved');
        });
    }

    /**
     * @return array{month: int, year: int}
     */
    private function selectedPeriodFor(Request $request): array
    {
        $now = now('Asia/Jakarta')->startOfDay();
        $selectedYear = $request->integer('year', (int) $now->year);
        $selectedYear = $selectedYear >= 2000 && $selectedYear <= (int) $now->year
            ? $selectedYear
            : (int) $now->year;
        $selectedMonth = $request->integer('month', (int) $now->month);
        $selectedMonth = $selectedMonth >= 1 && $selectedMonth <= 12 ? $selectedMonth : (int) $now->month;

        return [
            'month' => $selectedMonth,
            'year' => $selectedYear,
        ];
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    private function leaveMonthOptions(int $year): array
    {
        return collect(range(1, 12))
            ->map(fn (int $month): array => [
                'value' => $month,
                'label' => Carbon::create($year, $month, 1)->format('F'),
            ])
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function leaveYearOptions(int $selectedYear): array
    {
        $currentYear = (int) now('Asia/Jakarta')->year;

        return collect(range(min($selectedYear, $currentYear - 3), $currentYear))
            ->sortDesc()
            ->values()
            ->all();
    }

    private function leaveRequestPeriodLabel(LeaveRequest $leaveRequest): string
    {
        $startDate = Carbon::parse($leaveRequest->start_date)->timezone('Asia/Jakarta');
        $endDate = Carbon::parse($leaveRequest->end_date)->timezone('Asia/Jakarta');
        $totalDays = max((int) $leaveRequest->total_days, 1);
        $dayLabel = $totalDays === 1 ? 'day' : 'days';

        if ($startDate->isSameDay($endDate)) {
            return $startDate->format('d M Y').' ('.$totalDays.' '.$dayLabel.')';
        }

        return $startDate->format('d').' - '.$endDate->format('d M Y').' ('.$totalDays.' '.$dayLabel.')';
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
            return trim(Str::before($email, '@'));
        }

        return '-';
    }

    private function leaveTypeShortLabel(LeaveRequest $leaveRequest): string
    {
        $typeCode = strtoupper(trim((string) $leaveRequest->leaveType?->code));
        $typeName = strtolower(trim((string) $leaveRequest->leaveType?->name));

        if (in_array($typeCode, ['ANNUAL', 'ANNUAL_LEAVE'], true) || in_array($typeName, ['annual leave', 'cuti tahunan'], true)) {
            return 'Annual';
        }

        if (in_array($typeCode, ['SICK', 'SICK_LEAVE'], true) || in_array($typeName, ['sick leave', 'sakit'], true)) {
            return 'Sick';
        }

        if (in_array($typeCode, ['SPECIAL', 'SPECIAL_LEAVE'], true) || in_array($typeName, ['special leave', 'cuti khusus'], true)) {
            return 'Special';
        }

        if (in_array($typeCode, ['UNPAID', 'UNPAID_LEAVE'], true) || in_array($typeName, ['unpaid leave', 'cuti tidak dibayar'], true)) {
            return 'Unpaid';
        }

        $leaveTypeName = $leaveRequest->leaveType?->name;

        return is_string($leaveTypeName) && trim($leaveTypeName) !== '' ? trim($leaveTypeName) : 'Leave';
    }

    private function leaveTypeCardLabel(LeaveRequest $leaveRequest): string
    {
        return match ($this->leaveTypeShortLabel($leaveRequest)) {
            'Annual' => 'Annual Leave',
            'Sick' => 'Sick Leave',
            'Special' => 'Special Leave',
            'Unpaid' => 'Unpaid Leave',
            default => $this->leaveTypeShortLabel($leaveRequest),
        };
    }

    private function leaveTypeBadge(LeaveRequest $leaveRequest): string
    {
        return match ($this->leaveTypeShortLabel($leaveRequest)) {
            'Sick' => 'warning',
            'Special' => 'secondary',
            'Unpaid' => 'dark',
            default => 'primary',
        };
    }

    /**
     * @return array{
     *     status:string,
     *     status_label:string,
     *     status_text_class:string,
     *     can_finalize:bool,
     *     leave_type_label:string,
     *     special_leave_type_label:string,
     *     period_label:string,
     *     reason:string,
     *     handover_notes:string,
     *     attachment_url:?string,
     *     attachment_label:string
     * }
     */
    private function leaveApprovalFor(LeaveRequest $leaveRequest): array
    {
        $requestStatus = strtolower(trim((string) $leaveRequest->status));
        $hrVerificationHistory = $leaveRequest->histories
            ->filter(static fn (LeaveRequestHistory $history): bool => strtolower(trim((string) $history->event_type)) === 'hr_verification')
            ->sortByDesc('happened_at')
            ->first();
        $isPendingHrVerification = $hrVerificationHistory instanceof LeaveRequestHistory
            && strtolower(trim((string) $hrVerificationHistory->from_status)) === 'pending'
            && strtolower(trim((string) $hrVerificationHistory->to_status)) === 'pending';
        $isPending = $requestStatus === 'pending' && $isPendingHrVerification;
        $isSpecialLeave = $this->leaveTypeShortLabel($leaveRequest) === 'Special';
        $isSickLeave = $this->leaveTypeShortLabel($leaveRequest) === 'Sick';
        $specialLeaveMetadata = $leaveRequest->histories
            ->sortByDesc('happened_at')
            ->map(static function (LeaveRequestHistory $history): array {
                return is_array($history->metadata) ? $history->metadata : [];
            })
            ->first(static function (array $metadata): bool {
                return trim((string) ($metadata['special_leave_sub_type_id'] ?? '')) !== '';
            }, []);
        $specialLeaveSubTypeId = trim((string) ($specialLeaveMetadata['special_leave_sub_type_id'] ?? ''));
        $specialLeaveSubType = $specialLeaveSubTypeId !== ''
            ? $leaveRequest->leaveType?->subTypes->firstWhere('id', $specialLeaveSubTypeId)
            : null;
        $specialLeaveTypeLabel = $isSpecialLeave
            ? (is_string($specialLeaveSubType?->name) && trim($specialLeaveSubType->name) !== ''
                ? trim($specialLeaveSubType->name)
                : trim((string) ($specialLeaveMetadata['special_leave_sub_type_name'] ?? '-')))
            : '-';
        $attachmentPath = trim((string) $leaveRequest->attachment_path);
        $attachmentUrl = $isSickLeave && $attachmentPath !== ''
            ? asset('storage/'.ltrim($attachmentPath, '/'))
            : null;
        $status = $isPending ? 'pending' : $requestStatus;

        return [
            'status' => $status,
            'status_label' => match ($status) {
                'approved' => 'Approved',
                'rejected', 'refused' => 'Rejected',
                default => 'Pending',
            },
            'status_text_class' => match ($status) {
                'approved' => 'text-success',
                'rejected', 'refused' => 'text-danger',
                default => 'text-warning',
            },
            'can_finalize' => $isPending,
            'leave_type_label' => $this->leaveTypeCardLabel($leaveRequest),
            'special_leave_type_label' => $specialLeaveTypeLabel,
            'period_label' => $this->leaveRequestPeriodLabel($leaveRequest),
            'reason' => trim((string) $leaveRequest->reason) !== '' ? trim((string) $leaveRequest->reason) : '-',
            'handover_notes' => trim((string) $leaveRequest->handover_notes) !== '' ? trim((string) $leaveRequest->handover_notes) : '-',
            'attachment_url' => $attachmentUrl,
            'attachment_label' => $attachmentUrl !== null ? basename($attachmentPath) : '-',
        ];
    }

    /**
     * @return array{
     *     full_name:string,
     *     supervisor_name:string,
     *     join_date_label:string,
     *     tenure_label:string,
     *     is_eligible:bool,
     *     available_balance_label:string,
     *     available_balance_note:string,
     *     next_accrual_label:string,
     *     next_accrual_note:string,
     *     joint_holiday_label:string,
     *     joint_holiday_items:list<string>
     * }
     */
    private function leaveBalanceCardsFor(LeaveRequest $leaveRequest, int $year, Carbon $today): array
    {
        $employeeId = trim((string) $leaveRequest->employee_id);
        $joinDate = $leaveRequest->employee?->deployment?->join_date;
        $joinDate = $joinDate instanceof Carbon
            ? $joinDate->copy()->startOfDay()
            : (is_string($joinDate) && trim($joinDate) !== '' ? Carbon::parse($joinDate)->startOfDay() : null);
        $tenureMonths = $joinDate instanceof Carbon
            ? max((int) floor($joinDate->diffInMonths($today, true)), 0)
            : 0;
        $jointHolidaySummary = $this->jointHolidaySummaryFor($year, $today);
        $annualBalance = $this->annualLeaveBalanceFor($employeeId, $year);
        $monthlyAccrual = (int) round((float) DB::table('leave_types')
            ->whereIn(DB::raw('LOWER(code)'), ['annual', 'annual_leave'])
            ->orWhereIn(DB::raw('LOWER(name)'), ['cuti tahunan', 'annual leave'])
            ->value('monthly_accrual_rate'));

        return [
            'full_name' => $this->employeeDisplayName($leaveRequest->employee),
            'supervisor_name' => $this->supervisorDisplayName($leaveRequest),
            'join_date_label' => $joinDate?->translatedFormat('d F Y') ?? '-',
            'tenure_label' => $joinDate instanceof Carbon ? $this->tenureLabel($tenureMonths) : '-',
            'is_eligible' => $tenureMonths >= 12,
            'available_balance_label' => $annualBalance.' '.Str::plural('Day', $annualBalance),
            'available_balance_note' => $annualBalance > 0 ? 'Rolled over from previous months' : 'No available annual leave balance.',
            'next_accrual_label' => '+'.$monthlyAccrual.' '.Str::plural('Day', $monthlyAccrual),
            'next_accrual_note' => $monthlyAccrual > 0 ? 'Will be automatically added next month' : 'No automatic accrual configured.',
            'joint_holiday_label' => $jointHolidaySummary['label'],
            'joint_holiday_items' => $jointHolidaySummary['items'],
        ];
    }

    /**
     * @return array{
     *     month_label:string,
     *     annual_leave_taken_label:string,
     *     annual_leave_taken_breakdown:string,
     *     annual_leave_taken_month_label:string,
     *     annual_leave_taken_month_breakdown:string,
     *     sick_leave_taken_label:string,
     *     sick_leave_taken_breakdown:string,
     *     sick_leave_taken_month_label:string,
     *     sick_leave_taken_month_breakdown:string,
     *     special_leave_taken_label:string,
     *     special_leave_taken_breakdown:string,
     *     special_leave_taken_month_label:string,
     *     special_leave_taken_month_breakdown:string,
     *     unpaid_leave_taken_label:string
     *     unpaid_leave_taken_breakdown:string,
     *     unpaid_leave_taken_month_label:string,
     *     unpaid_leave_taken_month_breakdown:string,
     *     pending_requests_label:string,
     *     approved_requests_label:string,
     *     rejected_requests_label:string
     * }
     */
    private function leaveTrackerFor(string $employeeId, int $year, int $month): array
    {
        $annualUsage = $this->leaveTypeUsageFor($employeeId, $year, $month, ['annual', 'annual_leave'], ['cuti tahunan', 'annual leave']);
        $sickUsage = $this->leaveTypeUsageFor($employeeId, $year, $month, ['sick', 'sick_leave'], ['sakit', 'sick leave']);
        $specialUsage = $this->leaveTypeUsageFor($employeeId, $year, $month, ['special', 'special_leave'], ['cuti khusus', 'special leave']);
        $unpaidUsage = $this->leaveTypeUsageFor($employeeId, $year, $month, ['unpaid', 'unpaid_leave'], ['cuti tidak dibayar', 'unpaid leave']);

        return [
            'month_label' => Carbon::create($year, $month, 1)->format('F'),
            'annual_leave_taken_label' => $annualUsage['year_label'],
            'annual_leave_taken_breakdown' => $annualUsage['year_breakdown'],
            'annual_leave_taken_month_label' => $annualUsage['month_label'],
            'annual_leave_taken_month_breakdown' => $annualUsage['month_breakdown'],
            'sick_leave_taken_label' => $sickUsage['year_label'],
            'sick_leave_taken_breakdown' => $sickUsage['year_breakdown'],
            'sick_leave_taken_month_label' => $sickUsage['month_label'],
            'sick_leave_taken_month_breakdown' => $sickUsage['month_breakdown'],
            'special_leave_taken_label' => $specialUsage['year_label'],
            'special_leave_taken_breakdown' => $specialUsage['year_breakdown'],
            'special_leave_taken_month_label' => $specialUsage['month_label'],
            'special_leave_taken_month_breakdown' => $specialUsage['month_breakdown'],
            'unpaid_leave_taken_label' => $unpaidUsage['year_label'],
            'unpaid_leave_taken_breakdown' => $unpaidUsage['year_breakdown'],
            'unpaid_leave_taken_month_label' => $unpaidUsage['month_label'],
            'unpaid_leave_taken_month_breakdown' => $unpaidUsage['month_breakdown'],
            'pending_requests_label' => $this->leaveRequestCountLabel($employeeId, $year, ['pending']),
            'approved_requests_label' => $this->leaveRequestCountLabel($employeeId, $year, ['approved']),
            'rejected_requests_label' => $this->leaveRequestCountLabel($employeeId, $year, ['rejected', 'refused']),
        ];
    }

    /**
     * @return Collection<int, array{type:string, period:string, total_days:string, reason:string, status:string, status_badge:string}>
     */
    private function leaveHistoryCardsFor(string $employeeId, int $year): Collection
    {
        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereYear('start_date', $year)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->with('leaveType:id,code,name')
            ->latest('start_date')
            ->get(['id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'reason', 'status'])
            ->map(function (LeaveRequest $leaveRequest): array {
                $status = strtolower(trim((string) $leaveRequest->status));

                return [
                    'type' => $this->leaveTypeCardLabel($leaveRequest),
                    'period' => $this->leaveRequestPeriodLabel($leaveRequest),
                    'total_days' => max((int) $leaveRequest->total_days, 1).' '.Str::plural('day', max((int) $leaveRequest->total_days, 1)),
                    'reason' => is_string($leaveRequest->reason) && trim($leaveRequest->reason) !== '' ? trim($leaveRequest->reason) : '-',
                    'status' => Str::headline($status !== '' ? $status : 'pending'),
                    'status_badge' => match ($status) {
                        'approved' => 'success',
                        'rejected', 'refused' => 'danger',
                        default => 'warning',
                    },
                ];
            })
            ->values();
    }

    private function annualLeaveBalanceFor(string $employeeId, int $year): int
    {
        if ($employeeId === '') {
            return 0;
        }

        return max((int) round((float) LeaveBalance::query()
            ->where('employee_id', $employeeId)
            ->where('period_year', $year)
            ->whereHas('leaveType', fn (Builder $query): Builder => $this->applyLeaveTypeMatch($query, ['annual', 'annual_leave'], ['cuti tahunan', 'annual leave']))
            ->sum('remaining_quota')), 0);
    }

    private function syncAnnualLeaveBalance(LeaveRequest $leaveRequest): void
    {
        $employeeId = trim((string) $leaveRequest->employee_id);
        $leaveTypeId = trim((string) $leaveRequest->leave_type_id);
        $leaveYear = (int) Carbon::parse($leaveRequest->start_date)->year;

        if ($employeeId === '' || $leaveTypeId === '' || $leaveYear <= 0) {
            return;
        }

        $annualLeaveTypeId = LeaveType::query()
            ->where(function (Builder $query): void {
                $this->applyLeaveTypeMatch($query, ['annual', 'annual_leave'], ['cuti tahunan', 'annual leave']);
            })
            ->value('id');

        if (! is_string($annualLeaveTypeId) || trim($annualLeaveTypeId) === '' || $leaveTypeId !== trim($annualLeaveTypeId)) {
            return;
        }

        $leaveBalance = LeaveBalance::withTrashed()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $annualLeaveTypeId)
            ->where('period_year', $leaveYear)
            ->lockForUpdate()
            ->first();
        $earnedQuota = (float) ($leaveBalance?->earned_quota ?? 0);
        $usedQuota = (float) LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $annualLeaveTypeId)
            ->whereYear('start_date', $leaveYear)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->sum('total_days');

        LeaveBalance::withTrashed()->updateOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type_id' => $annualLeaveTypeId,
                'period_year' => $leaveYear,
            ],
            [
                'earned_quota' => $earnedQuota,
                'used_quota' => $usedQuota,
                'remaining_quota' => max($earnedQuota - $usedQuota, 0),
                'deleted_at' => null,
            ]
        );
    }

    /**
     * @return array{year_label:string, year_breakdown:string, month_label:string, month_breakdown:string}
     */
    private function leaveTypeUsageFor(string $employeeId, int $year, int $month, array $codes, array $names): array
    {
        if ($employeeId === '') {
            return [
                'year_label' => '0 Days',
                'year_breakdown' => 'No leave taken yet.',
                'month_label' => '0 Days',
                'month_breakdown' => 'No leave taken this month.',
            ];
        }

        $leaveRequests = LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereYear('start_date', $year)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->whereHas('leaveType', fn (Builder $query): Builder => $this->applyLeaveTypeMatch($query, $codes, $names))
            ->orderBy('start_date')
            ->get(['id', 'start_date', 'end_date', 'total_days']);
        $monthRequests = $leaveRequests->filter(function (LeaveRequest $leaveRequest) use ($month): bool {
            return Carbon::parse((string) $leaveRequest->start_date)->month === $month;
        });
        $yearTotalDays = (int) $leaveRequests->sum('total_days');
        $monthTotalDays = (int) $monthRequests->sum('total_days');

        return [
            'year_label' => $yearTotalDays.' '.Str::plural('Day', $yearTotalDays),
            'year_breakdown' => $this->leaveUsageDateList($leaveRequests, 'No leave taken yet.'),
            'month_label' => $monthTotalDays.' '.Str::plural('Day', $monthTotalDays),
            'month_breakdown' => $this->leaveUsageDateList($monthRequests, 'No leave taken this month.'),
        ];
    }

    /**
     * @param  Collection<int, LeaveRequest>  $leaveRequests
     */
    private function leaveUsageDateList(Collection $leaveRequests, string $emptyLabel): string
    {
        if ($leaveRequests->isEmpty()) {
            return $emptyLabel;
        }

        return $leaveRequests
            ->map(function (LeaveRequest $leaveRequest): string {
                $startDate = Carbon::parse((string) $leaveRequest->start_date);
                $endDate = Carbon::parse((string) ($leaveRequest->end_date ?? $leaveRequest->start_date));

                if ($startDate->isSameDay($endDate)) {
                    return $startDate->format('d M');
                }

                return $startDate->isSameMonth($endDate)
                    ? $startDate->format('d').'-'.$endDate->format('d M')
                    : $startDate->format('d M').'-'.$endDate->format('d M');
            })
            ->implode(', ');
    }

    private function leaveRequestCountLabel(string $employeeId, int $year, array $statuses): string
    {
        $count = (int) LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereYear('start_date', $year)
            ->whereIn(DB::raw('LOWER(status)'), array_map('strtolower', $statuses))
            ->count();

        return $count.' '.Str::plural('Request', $count);
    }

    /**
     * @return array{label:string, items:list<string>}
     */
    private function jointHolidaySummaryFor(int $year, Carbon $today): array
    {
        $jointHolidays = AttendanceHoliday::query()
            ->whereYear('date', $year)
            ->where('type', 2)
            ->orderBy('date')
            ->get(['date', 'name']);
        $totalDays = $jointHolidays->count();
        $passedDays = $jointHolidays
            ->filter(fn (AttendanceHoliday $holiday): bool => $holiday->date->lessThanOrEqualTo($today))
            ->count();

        return [
            'label' => max($totalDays - $passedDays, 0).' / '.$totalDays.' '.Str::plural('Day', $totalDays),
            'items' => $jointHolidays
                ->map(fn (AttendanceHoliday $holiday): string => trim((string) $holiday->name).' ('.$holiday->date->format('d M').')')
                ->values()
                ->all(),
        ];
    }

    private function tenureLabel(int $tenureMonths): string
    {
        $years = intdiv($tenureMonths, 12);
        $months = $tenureMonths % 12;
        $parts = [];

        if ($years > 0) {
            $parts[] = $years.' '.Str::plural('Year', $years);
        }

        if ($months > 0 || $parts === []) {
            $parts[] = $months.' '.Str::plural('Month', $months);
        }

        return implode(', ', $parts);
    }

    private function applyLeaveTypeMatch(Builder $query, array $codes, array $names): Builder
    {
        return $query->where(function (Builder $query) use ($codes, $names): void {
            $query
                ->whereIn(DB::raw('LOWER(code)'), array_map('strtolower', $codes))
                ->orWhereIn(DB::raw('LOWER(name)'), array_map('strtolower', $names));
        });
    }

    /**
     * @return array{
     *     employee_name: string,
     *     employee_initial: string,
     *     employee_code: string,
     *     position: string,
     *     department: string,
     *     company: string,
     *     phone: string,
     *     email: string,
     *     address: string,
     *     supervisor_name: string,
     *     join_date: string,
     *     leave_type: string,
     *     status: string,
     *     status_badge: string,
     *     date_label: string,
     *     total_days_label: string,
     *     reason: string,
     *     handover_notes: string,
     *     attachment_url: ?string,
     *     submitted_at: string
     * }
     */
    private function leaveRequestDetailData(LeaveRequest $leaveRequest): array
    {
        $employee = $leaveRequest->employee;
        $employeeName = $this->employeeDisplayName($employee);
        $address = $employee?->latestAddress;
        $addressLabel = collect([
            $address?->address_line,
            $address?->village,
            $address?->subdistrict,
            $address?->regency,
            $address?->province,
            $address?->country,
            $address?->postal_code,
        ])
            ->filter(static fn (mixed $value): bool => is_string($value) && trim($value) !== '')
            ->map(static fn (string $value): string => trim($value))
            ->implode(', ');
        $normalizedStatus = strtolower(trim((string) $leaveRequest->status));
        $status = match ($normalizedStatus) {
            'approved' => 'Approved',
            'rejected', 'refused' => 'Rejected',
            default => 'Pending',
        };
        $totalDays = max((int) $leaveRequest->total_days, 1);

        return [
            'employee_name' => $employeeName,
            'employee_initial' => Str::upper(Str::substr($employeeName !== '-' ? $employeeName : 'L', 0, 1)),
            'employee_code' => is_string($employee?->employee_code) && trim($employee->employee_code) !== ''
                ? trim($employee->employee_code)
                : '-',
            'position' => is_string($employee?->deployment?->position?->name) && trim($employee->deployment->position->name) !== ''
                ? trim($employee->deployment->position->name)
                : '-',
            'department' => is_string($employee?->deployment?->department?->name) && trim($employee->deployment->department->name) !== ''
                ? trim($employee->deployment->department->name)
                : '-',
            'company' => is_string($employee?->deployment?->company?->name) && trim($employee->deployment->company->name) !== ''
                ? trim($employee->deployment->company->name)
                : '-',
            'phone' => is_string($employee?->user?->phone) && trim($employee->user->phone) !== ''
                ? trim($employee->user->phone)
                : '-',
            'email' => is_string($employee?->user?->email) && trim($employee->user->email) !== ''
                ? trim($employee->user->email)
                : '-',
            'address' => $addressLabel !== '' ? $addressLabel : '-',
            'supervisor_name' => $this->supervisorDisplayName($leaveRequest),
            'join_date' => $employee?->deployment?->join_date?->translatedFormat('d F Y') ?? '-',
            'leave_type' => $this->leaveTypeCardLabel($leaveRequest),
            'status' => $status,
            'status_badge' => match ($normalizedStatus) {
                'approved' => 'success',
                'rejected', 'refused' => 'danger',
                default => 'warning',
            },
            'date_label' => $this->leaveRequestPeriodLabel($leaveRequest),
            'total_days_label' => $totalDays.' '.($totalDays === 1 ? 'day' : 'days'),
            'reason' => is_string($leaveRequest->reason) && trim($leaveRequest->reason) !== ''
                ? trim($leaveRequest->reason)
                : '-',
            'handover_notes' => is_string($leaveRequest->handover_notes) && trim($leaveRequest->handover_notes) !== ''
                ? trim($leaveRequest->handover_notes)
                : '-',
            'attachment_url' => $this->leaveRequestAttachmentUrl($leaveRequest),
            'submitted_at' => $leaveRequest->created_at?->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i').' WIB' ?? '-',
        ];
    }

    /**
     * @return Collection<int, array{title: string, actor: string, status: string, notes: string, happened_at: string}>
     */
    private function leaveRequestHistoryData(LeaveRequest $leaveRequest): Collection
    {
        return $leaveRequest->histories
            ->map(function ($history): array {
                $actorName = $history->actor?->username;
                if (! is_string($actorName) || trim($actorName) === '') {
                    $actorEmail = $history->actor?->email;
                    $actorName = is_string($actorEmail) && trim($actorEmail) !== ''
                        ? Str::before(trim($actorEmail), '@')
                        : '-';
                }

                $status = is_string($history->to_status) && trim($history->to_status) !== ''
                    ? Str::headline(trim($history->to_status))
                    : '-';

                return [
                    'title' => is_string($history->title) && trim($history->title) !== ''
                        ? trim($history->title)
                        : Str::headline((string) $history->event_type),
                    'actor' => trim($actorName),
                    'status' => $status,
                    'notes' => is_string($history->notes) && trim($history->notes) !== ''
                        ? trim($history->notes)
                        : '-',
                    'happened_at' => $history->happened_at?->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i').' WIB' ?? '-',
                ];
            })
            ->values();
    }

    private function leaveRequestAttachmentUrl(LeaveRequest $leaveRequest): ?string
    {
        $attachmentPath = is_string($leaveRequest->attachment_path) ? trim($leaveRequest->attachment_path) : '';

        return $attachmentPath !== '' ? asset('storage/'.ltrim($attachmentPath, '/')) : null;
    }

    private function supervisorDisplayName(LeaveRequest $leaveRequest): string
    {
        $staffEmployeeId = is_string($leaveRequest->employee_id)
            ? trim((string) $leaveRequest->employee_id)
            : '';

        if ($staffEmployeeId === '') {
            return '-';
        }

        $supervisorEmployeeId = DB::table('employee_pic_assignments')
            ->where('staff_employee_id', $staffEmployeeId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->value('supervisor_employee_id');

        if (! is_string($supervisorEmployeeId) || trim($supervisorEmployeeId) === '') {
            return '-';
        }

        $profileName = DB::table('employee_profiles')
            ->where('employee_id', trim($supervisorEmployeeId))
            ->value('name');

        if (is_string($profileName) && trim($profileName) !== '') {
            return trim($profileName);
        }

        $username = DB::table('employees')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('employees.id', trim($supervisorEmployeeId))
            ->value('users.username');

        if (is_string($username) && trim($username) !== '') {
            return trim($username);
        }

        $email = DB::table('employees')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('employees.id', trim($supervisorEmployeeId))
            ->value('users.email');

        if (is_string($email) && trim($email) !== '') {
            return trim(Str::before($email, '@'));
        }

        return '-';
    }
}
