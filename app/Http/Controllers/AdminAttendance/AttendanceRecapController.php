<?php

namespace App\Http\Controllers\AdminAttendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceHoliday;
use App\Models\AttendanceLog;
use App\Models\AttendanceOvertime;
use App\Models\BusinessTrip;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Support\Attendance\AttendanceDurationFormatter;
use App\Support\Attendance\AttendanceExceptionPresenter;
use App\Support\Attendance\AttendanceLocationFormatter;
use App\Support\Attendance\AttendanceWorkDurationCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AttendanceRecapController extends Controller
{
    private const EXCLUDED_ATTENDANCE_DETAIL_EMAILS = [
        'lukman@rnbmanagement.com',
        'rully.priyatno@andalanbersama.com',
        'hilmi.ulwan@andalanbersama.com',
    ];

    public function __construct(
        private readonly AttendanceDurationFormatter $attendanceDurationFormatter,
        private readonly AttendanceExceptionPresenter $attendanceExceptionPresenter,
        private readonly AttendanceLocationFormatter $attendanceLocationFormatter,
        private readonly AttendanceWorkDurationCalculator $attendanceWorkDurationCalculator,
    ) {}

    public function index(Request $request): View
    {
        return view($this->attendanceIndexView(), $this->recapViewData($request));
    }

    public function monthlyDatatable(Request $request): JsonResponse
    {
        $monthlyData = $this->recapMonthlyData($request);

        return response()->json([
            'data' => $monthlyData['recapMonthlyRows'],
            'period_label' => $monthlyData['recapMonthlyPeriodLabel'],
        ]);
    }

    public function employeeDetails(Request $request, string $employee): View
    {
        $detailContext = $this->recapEmployeeDetailContext($request, $employee);

        return view($this->attendanceDetailView(), [
            'recapDetailMonth' => $detailContext['month'],
            'recapDetailYear' => $detailContext['year'],
        ] + $this->recapEmployeeDetailData(
            $detailContext['employee'],
            $detailContext['period_start'],
            $detailContext['period_end'],
        ));
    }

    public function employeeDetailsDatatable(Request $request, string $employee): JsonResponse
    {
        $detailContext = $this->recapEmployeeDetailContext($request, $employee);
        $detailData = $this->recapEmployeeDetailData(
            $detailContext['employee'],
            $detailContext['period_start'],
            $detailContext['period_end'],
        );

        return response()->json([
            'data' => $detailData['recapDetailAttendanceRows'],
            'period_label' => $detailData['recapDetailPeriodLabel'],
        ]);
    }

    protected function attendanceIndexView(): string
    {
        return 'admin_attendance.recap_attendance.index';
    }

    protected function attendanceDetailView(): string
    {
        return 'admin_attendance.recap_attendance.detail-employees';
    }

    /**
     * @return array{employee: Employee, month: int, year: int, period_start: Carbon, period_end: Carbon}
     */
    private function recapEmployeeDetailContext(Request $request, string $employeeId): array
    {
        $now = now('Asia/Jakarta')->startOfDay();
        $selectedYear = $request->integer('year', (int) $now->year);
        $selectedYear = $selectedYear >= 2000 && $selectedYear <= (int) $now->year ? $selectedYear : (int) $now->year;
        $selectedMonth = $request->integer('month', (int) $now->month);
        $selectedMonth = $selectedMonth >= 1 && $selectedMonth <= 12 ? $selectedMonth : (int) $now->month;

        $periodStart = Carbon::create($selectedYear, $selectedMonth, 1, 0, 0, 0, 'Asia/Jakarta')->startOfDay();
        $periodEnd = $periodStart->copy()->endOfMonth()->startOfDay();
        $employeeId = trim($employeeId);
        $activeEmployeeIds = $this->activeEmployeeIdsFor($now);

        abort_unless($employeeId !== '' && $activeEmployeeIds->contains($employeeId), 404);

        $employee = Employee::query()
            ->with([
                'profile:id,employee_id,name,profile_picture_path',
                'user:id,username,email,phone',
                'deployment:id,employee_id,current_company_id,current_position_id,current_department_id,current_office_location_id',
                'deployment.company:id,name',
                'deployment.officeLocation:id,address',
                'deployment.position:id,name',
                'deployment.department:id,name',
            ])
            ->findOrFail($employeeId, ['id', 'user_id', 'employee_code']);

        return [
            'employee' => $employee,
            'month' => $selectedMonth,
            'year' => $selectedYear,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ];
    }

    private function recapViewData(Request $request): array
    {
        $attendanceDate = now('Asia/Jakarta')->startOfDay();
        $activeEmployeeIds = $this->activeEmployeeIdsFor($attendanceDate);

        $recapMonthlyData = $this->recapMonthlyData($request, false);

        return [
            'recapAttendanceDateLabel' => $attendanceDate->format('d F Y'),
            'recapAttendanceDayLabel' => $attendanceDate->translatedFormat('l, d F Y'),
            'recapAttendanceLogRows' => $this->recapAttendanceLogRows($attendanceDate, $activeEmployeeIds),
        ] + $recapMonthlyData;
    }

    private function recapMonthlyData(Request $request, bool $includeRows = true): array
    {
        $now = now('Asia/Jakarta')->startOfDay();
        $selectedYear = $request->integer('year', (int) $now->year);
        $selectedYear = $selectedYear >= 2000 && $selectedYear <= (int) $now->year
            ? $selectedYear
            : (int) $now->year;
        $selectedMonth = $request->integer('month', (int) $now->month);
        $selectedMonth = $selectedMonth >= 1 && $selectedMonth <= 12 ? $selectedMonth : (int) $now->month;
        $periodStart = Carbon::create($selectedYear, $selectedMonth, 1, 0, 0, 0, 'Asia/Jakarta')->startOfDay();
        $periodEnd = $selectedYear === (int) $now->year && $selectedMonth === (int) $now->month
            ? $now->copy()
            : $periodStart->copy()->endOfMonth()->startOfDay();
        $activeEmployeeIds = $this->activeEmployeeIdsFor($periodEnd);

        return [
            'recapMonthlyPeriodLabel' => $periodStart->format('F Y'),
            'recapMonthlySelectedMonth' => $selectedMonth,
            'recapMonthlySelectedYear' => $selectedYear,
            'recapMonthlyMonthOptions' => collect(range(1, 12))
                ->map(fn (int $month): array => [
                    'value' => $month,
                    'label' => Carbon::create($selectedYear, $month, 1)->format('F'),
                ])
                ->all(),
            'recapMonthlyYearOptions' => collect(range(min($selectedYear, (int) $now->year - 3), (int) $now->year))
                ->sortDesc()
                ->values()
                ->all(),
            'recapMonthlyRows' => $includeRows
                ? $this->recapMonthlyRows($periodStart, $periodEnd, $activeEmployeeIds)
                : collect(),
        ];
    }

    private function recapMonthlyRows(Carbon $periodStart, Carbon $periodEnd, Collection $activeEmployeeIds): Collection
    {
        $workDays = $this->recapWorkDaysBetween($periodStart, $periodEnd);
        $monthlyWorkingDaysCount = $this->recapWorkDaysBetween(
            $periodStart,
            $periodStart->copy()->endOfMonth()->startOfDay(),
        )->count();
        $monthlyExpectedWorkMinutes = $monthlyWorkingDaysCount * 8 * 60;
        $employeeRelations = [
            'profile:id,employee_id,name',
            'user:id,username,email',
            'deployment:id,employee_id,join_date',
        ];
        $employees = Employee::query()
            ->with($employeeRelations)
            ->whereIn('id', $activeEmployeeIds)
            ->orderBy('id')
            ->get(['id', 'user_id']);
        $dateRange = [$periodStart->toDateString(), $periodEnd->toDateString()];

        $attendancesByEmployeeId = Attendance::query()
            ->whereIn('employee_id', $activeEmployeeIds)
            ->whereBetween('date', $dateRange)
            ->whereNotNull('clock_in')
            ->whereNull('deleted_at')
            ->get(['employee_id', 'date', 'clock_in', 'clock_out', 'late_minutes', 'work_hours', 'status'])
            ->groupBy('employee_id');
        $leaveRequestsByEmployeeId = LeaveRequest::query()
            ->whereIn('employee_id', $activeEmployeeIds)
            ->whereDate('start_date', '<=', $periodEnd->toDateString())
            ->whereDate('end_date', '>=', $periodStart->toDateString())
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get(['employee_id', 'start_date', 'end_date'])
            ->groupBy('employee_id');
        $businessTripsByEmployeeId = BusinessTrip::query()
            ->whereIn('employee_id', $activeEmployeeIds)
            ->whereDate('start_date', '<=', $periodEnd->toDateString())
            ->whereDate('end_date', '>=', $periodStart->toDateString())
            ->whereRaw('LOWER(COALESCE(approval_status, "")) = ?', ['approved'])
            ->whereNull('deleted_at')
            ->get(['employee_id', 'start_date', 'end_date'])
            ->groupBy('employee_id');
        $exceptionsByEmployeeId = AttendanceException::query()
            ->whereIn('employee_id', $activeEmployeeIds)
            ->whereBetween('exception_date', $dateRange)
            ->whereIn('type', ['late_arrival', 'early_departure'])
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
            ->whereNull('deleted_at')
            ->get(['employee_id', 'exception_date'])
            ->groupBy('employee_id');
        $overtimesByEmployeeId = AttendanceOvertime::query()
            ->whereIn('employee_id', $activeEmployeeIds)
            ->whereBetween('overtime_date', $dateRange)
            ->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled'])
            ->whereNull('deleted_at')
            ->get(['employee_id', 'overtime_date', 'calculated_hours'])
            ->groupBy('employee_id');

        $yearStart = $periodStart->copy()->startOfYear();
        $yearEnd = min($periodStart->copy()->endOfYear()->startOfDay(), now('Asia/Jakarta')->startOfDay());
        $yearLeaveRequestsByEmployeeId = LeaveRequest::query()
            ->whereIn('employee_id', $activeEmployeeIds)
            ->whereDate('start_date', '<=', $yearEnd->toDateString())
            ->whereDate('end_date', '>=', $yearStart->toDateString())
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get(['employee_id', 'start_date', 'end_date'])
            ->groupBy('employee_id');
        $yearWorkDays = $this->recapWorkDaysBetween($yearStart, $yearEnd);

        return $employees
            ->map(function (Employee $employee) use (
                $attendancesByEmployeeId,
                $leaveRequestsByEmployeeId,
                $businessTripsByEmployeeId,
                $exceptionsByEmployeeId,
                $overtimesByEmployeeId,
                $yearLeaveRequestsByEmployeeId,
                $workDays,
                $monthlyExpectedWorkMinutes,
                $monthlyWorkingDaysCount,
                $yearWorkDays
            ): array {
                $employeeId = $employee->id;
                $employeeWorkDays = $this->recapEmployeeWorkDays($employee, $workDays);
                $employeeWorkDayKeys = $employeeWorkDays
                    ->map(fn (Carbon $date): string => $date->toDateString());
                $attendances = $attendancesByEmployeeId
                    ->get($employeeId, collect())
                    ->filter(fn (Attendance $attendance): bool => $employeeWorkDayKeys->contains($this->dateKey($attendance->date)));
                $leaveRequests = $leaveRequestsByEmployeeId->get($employeeId, collect());
                $businessTrips = $businessTripsByEmployeeId->get($employeeId, collect());
                $attendedDateKeys = $attendances
                    ->map(fn (Attendance $attendance): string => $this->dateKey($attendance->date))
                    ->unique();
                $leaveDays = $this->recapOverlappingWorkDayCount($leaveRequests, $employeeWorkDays);
                $businessTripDays = $this->recapOverlappingWorkDayCount($businessTrips, $employeeWorkDays);
                $onTimeCount = $attendances
                    ->reject(fn (Attendance $attendance): bool => $this->isLateAttendance($attendance))
                    ->count();
                $lateAttendances = $attendances
                    ->filter(fn (Attendance $attendance): bool => $this->isLateAttendance($attendance));
                $lateMinutes = $lateAttendances->sum(fn (Attendance $attendance): int => (int) $attendance->late_minutes);
                $deviationCount = $exceptionsByEmployeeId
                    ->get($employeeId, collect())
                    ->map(fn (AttendanceException $attendanceException): string => $this->dateKey($attendanceException->exception_date))
                    ->filter(fn (string $dateKey): bool => $employeeWorkDayKeys->contains($dateKey))
                    ->unique()
                    ->count();
                $workedMinutes = $attendances
                    ->sum(fn (Attendance $attendance): int => $this->recapAttendanceWorkMinutes($attendance));
                $overtimeMinutes = $overtimesByEmployeeId
                    ->get($employeeId, collect())
                    ->filter(fn (AttendanceOvertime $overtime): bool => $employeeWorkDayKeys->contains($this->dateKey($overtime->overtime_date)))
                    ->sum(fn (AttendanceOvertime $overtime): int => max(0, (int) round((float) $overtime->calculated_hours * 60)));
                $alphaDays = $employeeWorkDayKeys
                    ->filter(function (string $dateKey) use ($attendedDateKeys, $leaveRequests, $businessTrips): bool {
                        $date = Carbon::parse($dateKey, 'Asia/Jakarta');

                        return ! $attendedDateKeys->contains($dateKey)
                            && ! $leaveRequests->contains(fn (LeaveRequest $leaveRequest): bool => $this->overlapsDate($leaveRequest->start_date, $leaveRequest->end_date, $date))
                            && ! $businessTrips->contains(fn (BusinessTrip $businessTrip): bool => $this->overlapsDate($businessTrip->start_date, $businessTrip->end_date, $date));
                    })
                    ->count();

                return [
                    'employee_id' => $employeeId,
                    'name' => $this->employeeDisplayName($employee),
                    'working_days' => $attendedDateKeys->count().' / '.$monthlyWorkingDaysCount.' days',
                    'working_hours' => $this->recapCompactMinutesLabel($workedMinutes).' / '.$this->recapCompactMinutesLabel($monthlyExpectedWorkMinutes),
                    'on_time' => $this->recapDaysLabel($onTimeCount),
                    'late' => $this->recapLateLabel($lateAttendances->count(), $lateMinutes),
                    'leave' => $this->recapDaysLabel($leaveDays),
                    'deviation' => $this->recapDaysLabel($deviationCount),
                    'alpha' => $this->recapDaysLabel($alphaDays),
                    'trip' => $this->recapDaysLabel($businessTripDays),
                    'overtimes' => $this->recapCompactMinutesLabel($overtimeMinutes),
                    'year_leave' => $this->recapDaysLabel($this->recapOverlappingWorkDayCount(
                        $yearLeaveRequestsByEmployeeId->get($employeeId, collect()),
                        $this->recapEmployeeWorkDays($employee, $yearWorkDays),
                    )),
                ];
            })
            ->values();
    }

    private function recapWorkDaysBetween(Carbon $startDate, Carbon $endDate): Collection
    {
        $holidayKeys = AttendanceHoliday::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('date')
            ->map(fn (mixed $date): string => $this->dateKey($date))
            ->flip();

        return $this->workDaysBetween($startDate, $endDate)
            ->reject(fn (Carbon $date): bool => $holidayKeys->has($date->toDateString()))
            ->values();
    }

    private function recapEmployeeWorkDays(Employee $employee, Collection $workDays): Collection
    {
        $joinDate = $employee->deployment?->join_date;
        if (! $joinDate instanceof \DateTimeInterface) {
            return $workDays;
        }

        $employmentStart = Carbon::instance($joinDate)->startOfDay();

        return $workDays
            ->filter(fn (Carbon $date): bool => $date->greaterThanOrEqualTo($employmentStart))
            ->values();
    }

    private function recapOverlappingWorkDayCount(Collection $records, Collection $workDays): int
    {
        return $workDays
            ->filter(fn (Carbon $date): bool => $records->contains(
                fn (mixed $record): bool => $this->overlapsDate($record->start_date ?? null, $record->end_date ?? null, $date)
            ))
            ->count();
    }

    private function recapAttendanceWorkMinutes(Attendance $attendance): int
    {
        if ($attendance->clock_in instanceof \DateTimeInterface && $attendance->clock_out instanceof \DateTimeInterface) {
            return $this->attendanceWorkDurationCalculator->netMinutesBetween(
                Carbon::instance($attendance->clock_in),
                Carbon::instance($attendance->clock_out)
            );
        }

        if (is_numeric($attendance->work_hours)) {
            return max(0, (int) round((float) $attendance->work_hours * 60));
        }

        return 0;
    }

    private function recapCompactMinutesLabel(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0h';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0 ? $hours.'h '.$remainingMinutes.'m' : $hours.'h';
    }

    private function recapLateLabel(int $lateDays, int $lateMinutes): string
    {
        $daysLabel = $this->recapDaysLabel($lateDays);

        if ($lateDays <= 0) {
            return $daysLabel;
        }

        return $daysLabel.' ('.$this->recapCompactMinutesLabel($lateMinutes).')';
    }

    private function recapDaysLabel(int $days): string
    {
        return $days.' '.($days === 1 ? 'day' : 'days');
    }

    private function recapEmployeeDetailData(Employee $employee, Carbon $periodStart, Carbon $periodEnd): array
    {
        $workDays = $this->recapWorkDaysBetween($periodStart, $periodEnd);
        $workDayKeys = $workDays->map(fn (Carbon $date): string => $date->toDateString());
        $asOfDate = now('Asia/Jakarta')->startOfDay();
        $elapsedPeriodEnd = $periodEnd->lessThan($asOfDate) ? $periodEnd->copy() : $asOfDate;
        $elapsedWorkDayKeys = $this->recapWorkDaysBetween($periodStart, $elapsedPeriodEnd)
            ->map(fn (Carbon $date): string => $date->toDateString());
        $dateRange = [$periodStart->toDateString(), $periodEnd->toDateString()];
        $attendances = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('date', $dateRange)
            ->whereNotNull('clock_in')
            ->whereNull('deleted_at')
            ->orderBy('date')
            ->get(['id', 'date', 'clock_in', 'clock_out', 'late_minutes', 'work_hours', 'status']);
        $attendanceExceptionsByAttendanceId = AttendanceException::query()
            ->whereIn('attendance_id', $attendances->pluck('id'))
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
            ->get(['attendance_id', 'exception_date', 'type', 'note', 'from_time', 'to_time', 'status'])
            ->keyBy('attendance_id');
        $leaveRequests = LeaveRequest::query()
            ->with('leaveType:id,name,code')
            ->where('employee_id', $employee->id)
            ->whereDate('start_date', '<=', $periodEnd->toDateString())
            ->whereDate('end_date', '>=', $periodStart->toDateString())
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get(['id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'reason', 'status']);
        $businessTrips = BusinessTrip::query()
            ->where('employee_id', $employee->id)
            ->whereDate('start_date', '<=', $periodEnd->toDateString())
            ->whereDate('end_date', '>=', $periodStart->toDateString())
            ->whereRaw('LOWER(COALESCE(approval_status, "")) = ?', ['approved'])
            ->whereNull('deleted_at')
            ->get(['start_date', 'end_date']);
        $overtimeMinutes = AttendanceOvertime::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('overtime_date', $dateRange)
            ->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled'])
            ->whereNull('deleted_at')
            ->get(['calculated_hours'])
            ->sum(fn (AttendanceOvertime $overtime): int => max(0, (int) round((float) $overtime->calculated_hours * 60)));

        $attendedDateKeys = $attendances
            ->map(fn (Attendance $attendance): string => $this->dateKey($attendance->date))
            ->unique();
        $onTimeCount = $attendances
            ->reject(fn (Attendance $attendance): bool => $this->isLateAttendance($attendance))
            ->count();
        $lateAttendances = $attendances
            ->filter(fn (Attendance $attendance): bool => $this->isLateAttendance($attendance));
        $lateMinutes = $lateAttendances->sum(fn (Attendance $attendance): int => (int) $attendance->late_minutes);
        $deviationCount = $attendanceExceptionsByAttendanceId->count();
        $leaveDays = $this->recapOverlappingWorkDayCount($leaveRequests, $workDays);
        $businessTripDays = $this->recapOverlappingWorkDayCount($businessTrips, $workDays);
        $workedMinutes = $attendances->sum(fn (Attendance $attendance): int => $this->recapAttendanceWorkMinutes($attendance));
        $expectedWorkMinutes = $workDays->count() * 8 * 60;
        $alphaDays = $elapsedWorkDayKeys
            ->filter(function (string $dateKey) use ($attendedDateKeys, $leaveRequests, $businessTrips): bool {
                $date = Carbon::parse($dateKey, 'Asia/Jakarta');

                return ! $attendedDateKeys->contains($dateKey)
                    && ! $leaveRequests->contains(fn (LeaveRequest $leaveRequest): bool => $this->overlapsDate($leaveRequest->start_date, $leaveRequest->end_date, $date))
                    && ! $businessTrips->contains(fn (BusinessTrip $businessTrip): bool => $this->overlapsDate($businessTrip->start_date, $businessTrip->end_date, $date));
            })
            ->count();
        $leaveTypeDays = $this->recapLeaveTypeDays($leaveRequests, $workDays);
        $attendanceRows = $attendances->map(function (Attendance $attendance) use ($attendanceExceptionsByAttendanceId): array {
            $attendanceException = $attendanceExceptionsByAttendanceId->get($attendance->id);
            $isLate = $this->isLateAttendance($attendance);
            $isException = $attendanceException instanceof AttendanceException;

            return [
                'date' => $attendance->date?->format('d M Y') ?? '-',
                'date_sort' => $this->dateKey($attendance->date),
                'clock_in' => $attendance->clock_in?->format('H:i') ?? '-',
                'clock_in_badge' => $isException && $attendanceException->type === 'late_arrival' ? 'secondary' : ($isLate ? 'danger' : 'success'),
                'clock_out' => $attendance->clock_out?->format('H:i') ?? '-',
                'clock_out_badge' => $isException && $attendanceException->type === 'early_departure' ? 'secondary' : 'light',
                'note' => $isException ? $this->attendanceExceptionPresenter->requestTypeLabel($attendanceException) : ($isLate ? $this->attendanceDurationFormatter->lateLabel((int) $attendance->late_minutes) : 'On Time'),
                'working_hours' => $this->recapMinutesLabel($this->recapAttendanceWorkMinutes($attendance)),
            ];
        });
        $leaveRows = $leaveRequests
            ->reject(fn (LeaveRequest $leaveRequest): bool => $attendedDateKeys->contains($this->dateKey($leaveRequest->start_date)))
            ->map(fn (LeaveRequest $leaveRequest): array => [
                'date' => $this->leaveDurationLabel($leaveRequest),
                'date_sort' => $this->dateKey($leaveRequest->start_date),
                'clock_in' => $this->leaveTypeName($leaveRequest),
                'clock_in_badge' => 'info',
                'clock_out' => '-',
                'clock_out_badge' => 'light',
                'note' => $this->leaveTypeName($leaveRequest),
                'working_hours' => '0 hours',
            ]);
        $holidayRows = $this->recapVirtualHolidayRows($periodStart, $periodEnd, $attendedDateKeys);

        return [
            'recapDetailPeriodLabel' => $periodStart->format('F Y'),
            'recapDetailEmployee' => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code ?: '-',
                'name' => $this->employeeDisplayName($employee),
                'initials' => $this->initials($this->employeeDisplayName($employee)),
                'position' => $employee->deployment?->position?->name ?: '-',
                'department' => $employee->deployment?->department?->name ?: '-',
                'company' => $employee->deployment?->company?->name ?: '-',
                'base' => $employee->deployment?->officeLocation?->address ?: '-',
                'phone' => $employee->user?->phone ?: '-',
                'email' => $employee->user?->email ?: '-',
                'avatar_url' => $this->employeeAvatarUrl($employee->profile?->profile_picture_path),
            ],
            'recapDetailMetrics' => [
                'on_time' => $this->recapDaysLabel($onTimeCount),
                'late' => $this->recapLateLabel($lateAttendances->count(), $lateMinutes),
                'leave' => $this->recapDaysLabel($leaveDays),
                'deviation' => $this->recapDaysLabel($deviationCount),
                'alpha' => $this->recapDaysLabel($alphaDays),
                'trip' => $this->recapDaysLabel($businessTripDays),
                'annual_leave' => $this->recapDaysLabel($leaveTypeDays['annual']),
                'sick_leave' => $this->recapDaysLabel($leaveTypeDays['sick']),
                'special_leave' => $this->recapDaysLabel($leaveTypeDays['special']),
                'unpaid_leave' => $this->recapDaysLabel($leaveTypeDays['unpaid']),
            ],
            'recapDetailCharts' => [
                'days_worked_percent' => $this->percentage($attendedDateKeys->count(), $workDays->count()),
                'on_time_percent' => $this->percentage($onTimeCount, $attendedDateKeys->count()),
                'late_percent' => $this->percentage($lateAttendances->count(), $attendedDateKeys->count()),
                'monthly_hours_percent' => $this->percentage($workedMinutes, $expectedWorkMinutes),
                'overtime_percent' => $this->percentage($overtimeMinutes, $expectedWorkMinutes),
                'days_worked_label' => $attendedDateKeys->count().'/'.$workDays->count().' days',
            ],
            'recapDetailAttendanceRows' => $attendanceRows
                ->concat($leaveRows)
                ->concat($holidayRows)
                ->sortBy('date_sort')
                ->values(),
        ];
    }

    private function recapVirtualHolidayRows(Carbon $periodStart, Carbon $periodEnd, Collection $attendedDateKeys): Collection
    {
        return AttendanceHoliday::query()
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->orderBy('date')
            ->get(['date', 'name', 'type'])
            ->reject(fn (AttendanceHoliday $holiday): bool => $attendedDateKeys->contains($this->dateKey($holiday->date)))
            ->map(function (AttendanceHoliday $holiday): array {
                $isNationalHoliday = (int) $holiday->type === 1;

                return [
                    'date' => $holiday->date?->format('d M Y') ?? '-',
                    'date_sort' => $this->dateKey($holiday->date),
                    'clock_in' => $holiday->name ?: '-',
                    'clock_in_badge' => 'secondary',
                    'clock_out' => '-',
                    'clock_out_badge' => 'light',
                    'note' => $isNationalHoliday ? 'Libur Nasional' : 'Cuti Bersama',
                    'working_hours' => '0 hours',
                ];
            });
    }

    private function recapLeaveTypeDays(Collection $leaveRequests, Collection $workDays): array
    {
        $leaveTypes = ['annual' => 0, 'sick' => 0, 'special' => 0, 'unpaid' => 0];

        foreach ($leaveRequests as $leaveRequest) {
            if (! $leaveRequest instanceof LeaveRequest) {
                continue;
            }

            $leaveTypeName = strtolower($this->leaveTypeName($leaveRequest));
            $leaveTypeCode = strtolower(trim((string) ($leaveRequest->leaveType?->code ?? '')));
            $typeKey = str_contains($leaveTypeCode, 'sick') || str_contains($leaveTypeName, 'sick') || str_contains($leaveTypeName, 'sakit')
                ? 'sick'
                : (str_contains($leaveTypeCode, 'annual') || str_contains($leaveTypeName, 'annual') || str_contains($leaveTypeName, 'tahunan')
                    ? 'annual'
                    : (str_contains($leaveTypeCode, 'unpaid') || str_contains($leaveTypeName, 'unpaid') || str_contains($leaveTypeName, 'tanpa bayar')
                        ? 'unpaid'
                        : 'special'));
            $leaveTypes[$typeKey] += $this->recapOverlappingWorkDayCount(collect([$leaveRequest]), $workDays);
        }

        return $leaveTypes;
    }

    private function recapAttendanceLogRows(Carbon $date, Collection $activeEmployeeIds): Collection
    {
        $dateKey = $date->toDateString();
        $employees = Employee::query()
            ->with([
                'profile:id,employee_id,name',
                'user:id,username,email',
            ])
            ->whereIn('id', $activeEmployeeIds)
            ->get(['id', 'user_id']);

        $attendances = Attendance::query()
            ->whereIn('employee_id', $activeEmployeeIds)
            ->whereDate('date', $dateKey)
            ->whereNull('deleted_at')
            ->orderBy('clock_in')
            ->get([
                'id',
                'employee_id',
                'leave_request_id',
                'date',
                'clock_in',
                'clock_out',
                'late_minutes',
                'work_hours',
                'status',
            ]);

        $attendanceIds = $attendances->pluck('id')->filter()->values();
        $attendanceLogsByAttendanceId = AttendanceLog::query()
            ->whereIn('attendance_id', $attendanceIds)
            ->where('type', true)
            ->orderByDesc('created_at')
            ->get([
                'attendance_id',
                'location',
                'latitude',
                'longitude',
                'distance',
                'radius_result',
                'address_village',
                'address_district',
                'address_regency',
                'address_city',
                'address_province',
                'address_postal_code',
            ])
            ->groupBy('attendance_id')
            ->map(static fn (Collection $attendanceLogs): ?AttendanceLog => $attendanceLogs->first());

        $attendanceExceptionsByAttendanceId = AttendanceException::query()
            ->whereIn('attendance_id', $attendanceIds)
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
            ->orderByDesc('created_at')
            ->get(['id', 'attendance_id', 'employee_id', 'exception_date', 'type', 'note', 'from_time', 'to_time', 'status'])
            ->groupBy('attendance_id')
            ->map(static fn (Collection $attendanceExceptions): ?AttendanceException => $attendanceExceptions->first());

        $leaveRequestsByEmployeeId = LeaveRequest::query()
            ->with([
                'leaveType:id,name,code',
            ])
            ->whereIn('employee_id', $activeEmployeeIds)
            ->whereDate('start_date', '<=', $dateKey)
            ->whereDate('end_date', '>=', $dateKey)
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('start_date')
            ->get([
                'id',
                'employee_id',
                'leave_type_id',
                'start_date',
                'end_date',
                'total_days',
                'reason',
                'status',
                'approved_at',
                'attachment_path',
            ])
            ->keyBy('employee_id');

        $attendanceByEmployeeId = $attendances->keyBy('employee_id');

        return $employees
            ->map(function (Employee $employee) use ($attendanceByEmployeeId, $attendanceLogsByAttendanceId, $attendanceExceptionsByAttendanceId, $leaveRequestsByEmployeeId): array {
                $attendance = $attendanceByEmployeeId->get($employee->id);
                $attendanceException = $attendance instanceof Attendance
                    ? $attendanceExceptionsByAttendanceId->get($attendance->id)
                    : null;

                if ($attendance instanceof Attendance && ($attendance->clock_in !== null || $attendanceException instanceof AttendanceException)) {
                    $attendance->setRelation('employee', $employee);

                    return $this->recapAttendanceRow(
                        $attendance,
                        $attendanceLogsByAttendanceId->get($attendance->id),
                        $attendanceException,
                    );
                }

                $leaveRequest = $leaveRequestsByEmployeeId->get($employee->id);
                if ($leaveRequest instanceof LeaveRequest) {
                    $leaveRequest->setRelation('employee', $employee);

                    return $this->recapLeaveRow($leaveRequest);
                }

                return $this->recapEmptyAttendanceRow($employee);
            })
            ->sortBy([
                ['clock_in_sort', 'asc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    private function recapAttendanceRow(
        Attendance $attendance,
        ?AttendanceLog $attendanceLog,
        ?AttendanceException $attendanceException
    ): array {
        $lateMinutes = (int) ($attendance->late_minutes ?? 0);
        $isLate = $this->isLateAttendance($attendance);
        $hasDeviation = $attendanceException instanceof AttendanceException;
        $exceptionType = $hasDeviation ? strtolower(trim((string) $attendanceException->type)) : '';
        $isLateArrival = $exceptionType === 'late_arrival';
        $isEarlyDeparture = $exceptionType === 'early_departure';
        $clockIn = $attendance->clock_in?->format('H:i') ?? '-';
        $clockOut = $attendance->clock_out?->format('H:i') ?? '-';
        $attendanceStatus = $hasDeviation
            ? $this->attendanceExceptionPresenter->requestTypeLabel($attendanceException)
            : ($isLate ? $this->attendanceDurationFormatter->lateLabel($lateMinutes) : 'On Time');

        return [
            'name' => $this->employeeDisplayName($attendance->employee),
            'has_detail' => true,
            'clock_in' => $clockIn,
            'clock_in_sort' => $clockIn === '-' ? '99:99' : $clockIn,
            'clock_in_class' => $isLate ? 'text-danger' : 'text-success',
            'clock_in_badge' => $isLateArrival ? 'secondary' : ($hasDeviation ? '' : ($isLate ? 'danger' : 'success')),
            'clock_out' => $clockOut,
            'clock_out_badge' => $isEarlyDeparture ? 'secondary' : ($hasDeviation ? '' : 'light'),
            'note' => $attendanceStatus,
            'working_hours' => $this->recapWorkingHoursLabel($attendance),
            'modal_id' => $hasDeviation ? 'attendanceDeviationModal' : 'attendanceLogDetailModal',
            'attachment_badge' => $hasDeviation ? 'secondary' : ($isLate ? 'danger' : 'success'),
            'location_name' => $this->attendanceLocationFormatter->name($attendanceLog),
            'location_address' => $this->attendanceLocationFormatter->address($attendanceLog),
            'map_url' => $this->recapMapUrl($attendanceLog),
            'attendance_status' => $attendanceStatus,
            'attendance_status_class' => $isLate ? 'text-danger' : 'text-success',
            'deviation_title' => $hasDeviation ? $this->attendanceExceptionPresenter->modalTitle($attendanceException) : '-',
            'deviation_intro' => $hasDeviation ? $this->attendanceExceptionPresenter->introPrimary($attendanceException) : '-',
            'deviation_request_type' => $hasDeviation ? $this->attendanceExceptionPresenter->requestTypeLabel($attendanceException) : '-',
            'deviation_reason' => $hasDeviation ? $this->recapExceptionReason($attendanceException) : '-',
            'deviation_time_variance' => $hasDeviation ? $this->attendanceExceptionPresenter->timeVarianceLabel($attendanceException) : '-',
            'deviation_status' => $hasDeviation ? $this->attendanceExceptionPresenter->statusLabel($attendanceException) : '-',
            'deviation_status_date' => $hasDeviation ? $this->attendanceExceptionPresenter->statusDateLabel($attendanceException) : '-',
            'leave_type' => '-',
            'leave_reason' => '-',
            'leave_duration' => '-',
            'leave_status' => '-',
            'leave_status_date' => '-',
            'leave_attachment_url' => '',
        ];
    }

    private function recapLeaveRow(LeaveRequest $leaveRequest): array
    {
        $isSickLeave = $this->isSickLeave($leaveRequest);
        $leaveType = $this->leaveTypeName($leaveRequest);

        return [
            'name' => $this->employeeDisplayName($leaveRequest->employee),
            'has_detail' => true,
            'clock_in' => $leaveType,
            'clock_in_sort' => '99:99',
            'clock_in_class' => 'text-success',
            'clock_in_badge' => 'info',
            'clock_out' => $leaveType,
            'clock_out_badge' => 'light',
            'note' => $leaveType,
            'working_hours' => '0 hours',
            'modal_id' => $isSickLeave ? 'attendanceSickLeaveDetailModal' : 'attendanceLeaveDetailModal',
            'attachment_badge' => 'info',
            'location_name' => '-',
            'location_address' => '-',
            'map_url' => '',
            'attendance_status' => '-',
            'attendance_status_class' => 'text-success',
            'deviation_title' => '-',
            'deviation_intro' => '-',
            'deviation_request_type' => '-',
            'deviation_reason' => '-',
            'deviation_time_variance' => '-',
            'deviation_status' => '-',
            'deviation_status_date' => '-',
            'leave_type' => $leaveType,
            'leave_reason' => $this->leaveReason($leaveRequest),
            'leave_duration' => $this->leaveDurationLabel($leaveRequest),
            'leave_status' => str($leaveRequest->status)->replace('_', ' ')->title()->toString(),
            'leave_status_date' => $this->leaveApprovedDateLabel($leaveRequest),
            'leave_attachment_url' => $this->leaveAttachmentUrl($leaveRequest),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function recapEmptyAttendanceRow(Employee $employee): array
    {
        return [
            'name' => $this->employeeDisplayName($employee),
            'has_detail' => false,
            'clock_in' => '-',
            'clock_in_sort' => '99:99',
            'clock_in_class' => '',
            'clock_in_badge' => '',
            'clock_out' => '-',
            'clock_out_badge' => '',
            'note' => '-',
            'working_hours' => '-',
            'modal_id' => '',
            'attachment_badge' => '',
            'location_name' => '-',
            'location_address' => '-',
            'map_url' => '',
            'attendance_status' => '-',
            'attendance_status_class' => '',
            'deviation_title' => '-',
            'deviation_intro' => '-',
            'deviation_request_type' => '-',
            'deviation_reason' => '-',
            'deviation_time_variance' => '-',
            'deviation_status' => '-',
            'deviation_status_date' => '-',
            'leave_type' => '-',
            'leave_reason' => '-',
            'leave_duration' => '-',
            'leave_status' => '-',
            'leave_status_date' => '-',
            'leave_attachment_url' => '',
        ];
    }

    private function recapWorkingHoursLabel(Attendance $attendance): string
    {
        if ($attendance->clock_in instanceof \DateTimeInterface && $attendance->clock_out instanceof \DateTimeInterface) {
            $minutes = $this->attendanceWorkDurationCalculator->netMinutesBetween(
                Carbon::instance($attendance->clock_in),
                Carbon::instance($attendance->clock_out)
            );

            return $this->recapMinutesLabel($minutes);
        }

        if (is_numeric($attendance->work_hours)) {
            $minutes = max(0, (int) round((float) $attendance->work_hours * 60));

            return $this->recapMinutesLabel($minutes);
        }

        return '0 hours';
    }

    private function isLateAttendance(Attendance $attendance): bool
    {
        return (int) ($attendance->late_minutes ?? 0) > 0;
    }

    private function recapMinutesLabel(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 hours';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0
            ? $hours.' hours '.$remainingMinutes.' minutes'
            : $hours.' hours';
    }

    private function recapExceptionReason(AttendanceException $attendanceException): string
    {
        $reason = is_string($attendanceException->note) ? trim($attendanceException->note) : '';

        return $reason !== '' ? $reason : '-';
    }

    private function recapMapUrl(?AttendanceLog $attendanceLog): string
    {
        if (! isset($attendanceLog?->latitude, $attendanceLog?->longitude)) {
            return '';
        }

        $latitude = (float) $attendanceLog->latitude;
        $longitude = (float) $attendanceLog->longitude;
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return '';
        }

        return 'https://maps.google.com/maps?q='.rawurlencode($latitude.','.$longitude).'&t=&z=13&ie=UTF8&iwloc=&output=embed';
    }

    private function activeEmployeeIdsFor(Carbon $date): Collection
    {
        $todayDate = $date->toDateString();

        return Employee::query()
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])
            ->whereHas('user', function ($query): void {
                $query
                    ->where('is_active', true)
                    ->whereNotIn('email', self::EXCLUDED_ATTENDANCE_DETAIL_EMAILS)
                    ->whereDoesntHave('roles', function ($roleQuery): void {
                        $roleQuery->where('name', 'superuser');
                    });
            })
            ->whereHas('deployment', function ($query) use ($todayDate): void {
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
            })
            ->pluck('id')
            ->filter(static fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')
            ->values();
    }

    private function percentage(int $count, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return max(0, min((int) round(($count / $total) * 100), 100));
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
            return trim(explode('@', $email)[0]);
        }

        return 'Unknown Staff';
    }

    private function workDaysBetween(Carbon $startDate, Carbon $endDate): Collection
    {
        $days = collect();
        $currentDate = $startDate->copy()->startOfDay();
        $lastDate = $endDate->copy()->startOfDay();

        while ($currentDate->lte($lastDate)) {
            if (! $currentDate->isWeekend()) {
                $days->push($currentDate->copy());
            }

            $currentDate->addDay();
        }

        return $days;
    }

    private function dateKey(mixed $date): string
    {
        return $date instanceof \DateTimeInterface
            ? Carbon::instance($date)->toDateString()
            : Carbon::parse($date)->toDateString();
    }

    private function overlapsDate(mixed $startDate, mixed $endDate, Carbon $date): bool
    {
        if ($startDate === null || $endDate === null) {
            return false;
        }

        return $date->between(
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
            true
        );
    }

    private function leaveTypeName(LeaveRequest $leaveRequest): string
    {
        $leaveTypeName = $leaveRequest->leaveType?->name;

        return is_string($leaveTypeName) && trim($leaveTypeName) !== ''
            ? trim($leaveTypeName)
            : 'Leave';
    }

    private function isSickLeave(LeaveRequest $leaveRequest): bool
    {
        $leaveTypeCode = strtolower(trim((string) ($leaveRequest->leaveType?->code ?? '')));
        $leaveTypeName = strtolower($this->leaveTypeName($leaveRequest));

        return str_contains($leaveTypeCode, 'sick')
            || str_contains($leaveTypeName, 'sick')
            || str_contains($leaveTypeName, 'sakit');
    }

    private function leaveReason(LeaveRequest $leaveRequest): string
    {
        $reason = is_string($leaveRequest->reason) ? trim($leaveRequest->reason) : '';

        return $reason !== '' ? $reason : '-';
    }

    private function leaveDurationLabel(LeaveRequest $leaveRequest): string
    {
        $startDate = Carbon::parse($leaveRequest->start_date);
        $endDate = Carbon::parse($leaveRequest->end_date);
        $totalDays = max((int) ($leaveRequest->total_days ?? 0), $startDate->diffInDays($endDate) + 1, 1);
        $dayLabel = $totalDays === 1 ? 'day' : 'days';

        return $startDate->format('d F Y').' - '.$endDate->format('d F Y')." ({$totalDays} {$dayLabel})";
    }

    private function leaveApprovedDateLabel(LeaveRequest $leaveRequest): string
    {
        if ($leaveRequest->approved_at === null) {
            return '';
        }

        return Carbon::parse($leaveRequest->approved_at)->format('d F Y');
    }

    private function leaveAttachmentUrl(LeaveRequest $leaveRequest): string
    {
        $attachmentPath = is_string($leaveRequest->attachment_path) ? trim($leaveRequest->attachment_path) : '';

        return $attachmentPath !== '' ? asset('storage/'.ltrim($attachmentPath, '/')) : '';
    }

    private function initials(string $name): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->map(static fn (string $part): string => mb_substr($part, 0, 1))
            ->take(2)
            ->implode('');

        return mb_strtoupper($initials !== '' ? $initials : 'U');
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

        return File::exists(public_path($publicPath)) ? asset($publicPath) : $defaultAvatarUrl;
    }
}
