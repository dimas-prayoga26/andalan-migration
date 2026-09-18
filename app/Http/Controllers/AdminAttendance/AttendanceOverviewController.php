<?php

namespace App\Http\Controllers\AdminAttendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceOvertime;
use App\Models\BusinessTrip;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Support\Attendance\AttendanceExceptionPresenter;
use App\Support\Attendance\AttendanceLocationFormatter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceOverviewController extends Controller
{
    private const EXCLUDED_ATTENDANCE_DETAIL_EMAILS = [
        'lukman@rnbmanagement.com',
        'rully.priyatno@andalanbersama.com',
    ];

    public function __construct(private readonly AttendanceExceptionPresenter $attendanceExceptionPresenter, private readonly AttendanceLocationFormatter $attendanceLocationFormatter) {}

    public function index(): View
    {
        return view('admin_attendance.overview.index', $this->overviewViewData());
    }

    private function overviewViewData(): array
    {
        $dailyAttendanceDate = now('Asia/Jakarta')->startOfDay();
        $activeEmployeeIds = $this->activeEmployeeIdsFor($dailyAttendanceDate);

        return $this->dailyAttendanceSummary($dailyAttendanceDate, $activeEmployeeIds)
            + $this->dailyAttendanceLists($dailyAttendanceDate, $activeEmployeeIds)
            + $this->weeklyAttendanceCharts($dailyAttendanceDate->copy()->startOfWeek(Carbon::MONDAY), $activeEmployeeIds)
            + $this->monthlyAttendanceCharts($dailyAttendanceDate->copy()->startOfMonth(), $activeEmployeeIds)
            + $this->yearToDateAttendanceCharts($dailyAttendanceDate->copy()->startOfYear(), $activeEmployeeIds);
    }

    /**     * @return array{     *     recapAttendanceDateLabel: string,     *     recapAttendanceDayLabel: string,     *     recapAttendanceLogRows: Collection<int, array{     *         name: string,     *         clock_in: string,     *         clock_in_class: string,     *         clock_in_badge: string,     *         clock_out: string,     *         clock_out_badge: string,     *         note: string,     *         working_hours: string,     *         modal_id: string,     *         attachment_badge: string,     *         location_name: string,     *         location_address: string,     *         map_url: string,     *         attendance_status: string,     *         attendance_status_class: string,     *         deviation_title: string,     *         deviation_intro: string,     *         deviation_request_type: string,     *         deviation_reason: string,     *         deviation_time_variance: string,     *         deviation_status: string,     *         deviation_status_date: string,     *         leave_type: string,     *         leave_reason: string,     *         leave_duration: string,     *         leave_status: string,     *         leave_status_date: string,     *         leave_attachment_url: string     *     }>,     *     recapMonthlyPeriodLabel: string,     *     recapMonthlySelectedMonth: int,     *     recapMonthlySelectedYear: int,     *     recapMonthlyMonthOptions: array<int, array{value: int, label: string}>,     *     recapMonthlyYearOptions: array<int, int>,     *     recapMonthlyRows: Collection<int, array<string, string>>     * }     */
    private function dailyAttendanceSummary(Carbon $date, Collection $activeEmployeeIds): array
    {
        $todayDate = $date->toDateString();
        $totalStaffCount = $activeEmployeeIds->count();
        $attendanceTodayQuery = Attendance::query()->whereIn('employee_id', $activeEmployeeIds)->whereDate('date', $todayDate)->whereNull('deleted_at');
        $presentStaffCount = (clone $attendanceTodayQuery)->whereNotNull('clock_in')->distinct()->count('employee_id');
        $onTimeCount = (clone $attendanceTodayQuery)->whereNotNull('clock_in')->where('late_minutes', '<=', 0)->distinct()->count('employee_id');
        $lateCount = (clone $attendanceTodayQuery)->whereNotNull('clock_in')->where('late_minutes', '>', 0)->distinct()->count('employee_id');
        $leaveCount = LeaveRequest::query()->whereIn('employee_id', $activeEmployeeIds)->whereDate('start_date', '<=', $todayDate)->whereDate('end_date', '>=', $todayDate)->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])->where('is_active', true)->whereNull('deleted_at')->distinct()->count('employee_id');
        $deviationCount = AttendanceException::query()->whereIn('employee_id', $activeEmployeeIds)->whereDate('exception_date', $todayDate)->whereIn('type', ['late_arrival', 'early_departure'])->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])->distinct()->count('employee_id');
        $businessTripCount = BusinessTrip::query()->whereIn('employee_id', $activeEmployeeIds)->whereDate('start_date', '<=', $todayDate)->whereDate('end_date', '>=', $todayDate)->whereRaw('LOWER(COALESCE(approval_status, "")) = ?', ['approved'])->distinct()->count('employee_id');

        return ['dailyAttendanceDateLabel' => $date->format('d F Y'),            'dailyTotalStaffCount' => $totalStaffCount,            'dailyPresentStaffCount' => $presentStaffCount,            'dailyOnTimeCount' => $onTimeCount,            'dailyLateCount' => $lateCount,            'dailyLeaveCount' => $leaveCount,            'dailyDeviationCount' => $deviationCount,            'dailyBusinessTripCount' => $businessTripCount,            'dailyOnTimePercent' => $this->percentage($onTimeCount, $totalStaffCount),            'dailyLatePercent' => $this->percentage($lateCount, $totalStaffCount),            'dailyLeavePercent' => $this->percentage($leaveCount, $totalStaffCount),            'dailyDeviationPercent' => $this->percentage($deviationCount, $totalStaffCount),            'attendanceProgressPercent' => $this->percentage($presentStaffCount, $totalStaffCount),            'attendanceOverviewSeries' => [$onTimeCount,                $lateCount,                $leaveCount,                $deviationCount],            'dailyOnTimeDonutValue' => $this->donutValue($onTimeCount, $totalStaffCount),            'dailyLateDonutValue' => $this->donutValue($lateCount, $totalStaffCount),            'dailyLeaveDonutValue' => $this->donutValue($leaveCount, $totalStaffCount),            'dailyDeviationDonutValue' => $this->donutValue($deviationCount, $totalStaffCount)];
    }

    /**     * @param  Collection<int, string>  $activeEmployeeIds     * @return array{     *     dailyEarlyBirds: Collection<int, array{name: string, initials: string, time: string, rank: int}>,     *     dailyRunningLate: Collection<int, array{name: string, initials: string, time: string, rank: int}>,     *     dailyBusinessTrips: Collection<int, array{name: string, initials: string, destination: string, date_range: string}>,     *     dailyLeaves: Collection<int, array{     *         name: string,     *         initials: string,     *         leave_type: string,     *         modal_id: string,     *         modal_title: string,     *         reason: string,     *         duration: string,     *         status: string,     *         status_date: string,     *         attachment_url: string,     *         attachment_is_image: bool     *     }>     * }     */
    private function dailyAttendanceLists(Carbon $date, Collection $activeEmployeeIds): array
    {
        $todayDate = $date->toDateString();
        $attendanceTodayQuery = Attendance::query()->with(['employee:id,user_id',                'employee.profile:id,employee_id,name,profile_picture_path',                'employee.user:id,username,email'])->whereIn('employee_id', $activeEmployeeIds)->whereDate('date', $todayDate)->whereNotNull('clock_in')->whereNull('deleted_at');
        $dailyEarlyBirds = (clone $attendanceTodayQuery)->where('late_minutes', '<=', 0)->orderBy('clock_in')->limit(5)->get(['id', 'employee_id', 'date', 'clock_in', 'status', 'late_minutes'])->values()->map(fn (Attendance $attendance, int $index): array => $this->presentAttendancePerson($attendance, $index + 1));
        $dailyRunningLate = (clone $attendanceTodayQuery)->where('late_minutes', '>', 0)->orderByDesc('clock_in')->limit(5)->get(['id', 'employee_id', 'date', 'clock_in', 'status', 'late_minutes'])->values()->map(fn (Attendance $attendance, int $index): array => $this->presentAttendancePerson($attendance, $index + 1));
        $dailyBusinessTrips = BusinessTrip::query()->with(['employee:id,user_id',                'employee.profile:id,employee_id,name',                'employee.user:id,username,email'])->whereIn('employee_id', $activeEmployeeIds)->whereDate('start_date', '<=', $todayDate)->whereDate('end_date', '>=', $todayDate)->whereRaw('LOWER(COALESCE(approval_status, "")) = ?', ['approved'])->orderBy('start_date')->limit(5)->get(['id', 'employee_id', 'start_date', 'end_date', 'city_destination', 'province_destination'])->map(fn (BusinessTrip $businessTrip): array => ['name' => $this->employeeDisplayName($businessTrip->employee),                'initials' => $this->initials($this->employeeDisplayName($businessTrip->employee)),                'destination' => $this->businessTripDestination($businessTrip),                'date_range' => $this->dateRangeLabel($businessTrip->start_date, $businessTrip->end_date)]);
        $dailyLeaves = LeaveRequest::query()->with(['employee:id,user_id',                'employee.profile:id,employee_id,name',                'employee.user:id,username,email',                'leaveType:id,name,code'])->whereIn('employee_id', $activeEmployeeIds)->whereDate('start_date', '<=', $todayDate)->whereDate('end_date', '>=', $todayDate)->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])->where('is_active', true)->whereNull('deleted_at')->orderBy('start_date')->limit(5)->get(['id',                'employee_id',                'leave_type_id',                'start_date',                'end_date',                'total_days',                'reason',                'status',                'approved_at',                'attachment_path'])->map(function (LeaveRequest $leaveRequest): array {
            $name = $this->employeeDisplayName($leaveRequest->employee);
            $isSickLeave = $this->isSickLeave($leaveRequest);
            $attachmentUrl = $this->leaveAttachmentUrl($leaveRequest);

            return ['name' => $name,                    'initials' => $this->initials($name),                    'leave_type' => $this->leaveTypeName($leaveRequest),                    'modal_id' => $isSickLeave ? 'sick' : 'annualLeave',                    'modal_title' => $isSickLeave ? 'Attendance Sick' : $this->leaveTypeName($leaveRequest),                    'reason' => $this->leaveReason($leaveRequest),                    'duration' => $this->leaveDurationLabel($leaveRequest),                    'status' => 'Approved',                    'status_date' => $this->leaveApprovedDateLabel($leaveRequest),                    'attachment_url' => $attachmentUrl,                    'attachment_is_image' => $this->isImageAttachment($attachmentUrl)];
        });

        return ['dailyEarlyBirds' => $dailyEarlyBirds,            'dailyRunningLate' => $dailyRunningLate,            'dailyBusinessTrips' => $dailyBusinessTrips,            'dailyLeaves' => $dailyLeaves];
    }

    /**     * @param  Collection<int, string>  $activeEmployeeIds     * @return array{     *     weeklyAttendanceRangeLabel: string,     *     weeklyDayLabels: array<int, string>,     *     weeklyAttendanceSeries: array<int, array{name: string, data: array<int, int>}>,     *     weeklyOutOfOfficeSeries: array<int, array{name: string, data: array<int, int>}>,     *     weeklyOvertimeHoursSeries: array<int, float|int>     * }     */
    private function weeklyAttendanceCharts(Carbon $weekStart, Collection $activeEmployeeIds): array
    {
        $weekStart = $weekStart->copy()->startOfDay();
        $weekEnd = $weekStart->copy()->addDays(4)->endOfDay();
        $weekDays = collect(range(0, 4))->map(fn (int $offset): Carbon => $weekStart->copy()->addDays($offset));
        $weekDayKeys = $weekDays->map(fn (Carbon $date): string => $date->toDateString());
        $attendanceRows = Attendance::query()->whereIn('employee_id', $activeEmployeeIds)->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])->whereNotNull('clock_in')->whereNull('deleted_at')->get(['employee_id', 'date', 'clock_in', 'status', 'late_minutes']);
        $onTimeByDay = $attendanceRows->reject(fn (Attendance $attendance): bool => $this->isLateAttendance($attendance))->groupBy(fn (Attendance $attendance): string => $this->dateKey($attendance->date))->map(fn (Collection $rows): int => $rows->pluck('employee_id')->unique()->count());
        $lateByDay = $attendanceRows->filter(fn (Attendance $attendance): bool => $this->isLateAttendance($attendance))->groupBy(fn (Attendance $attendance): string => $this->dateKey($attendance->date))->map(fn (Collection $rows): int => $rows->pluck('employee_id')->unique()->count());
        $leaveRows = LeaveRequest::query()->whereIn('employee_id', $activeEmployeeIds)->whereDate('start_date', '<=', $weekEnd->toDateString())->whereDate('end_date', '>=', $weekStart->toDateString())->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])->where('is_active', true)->whereNull('deleted_at')->get(['employee_id', 'start_date', 'end_date']);
        $businessTripRows = BusinessTrip::query()->whereIn('employee_id', $activeEmployeeIds)->whereDate('start_date', '<=', $weekEnd->toDateString())->whereDate('end_date', '>=', $weekStart->toDateString())->whereRaw('LOWER(COALESCE(approval_status, "")) = ?', ['approved'])->whereNull('deleted_at')->get(['employee_id', 'start_date', 'end_date']);
        $deviationByDay = AttendanceException::query()->whereIn('employee_id', $activeEmployeeIds)->whereBetween('exception_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->whereIn('type', ['late_arrival', 'early_departure'])->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])->whereNull('deleted_at')->get(['employee_id', 'exception_date'])->groupBy(fn (AttendanceException $attendanceException): string => $this->dateKey($attendanceException->exception_date))->map(fn (Collection $rows): int => $rows->pluck('employee_id')->unique()->count());
        $overtimeHoursByDay = AttendanceOvertime::query()->whereIn('employee_id', $activeEmployeeIds)->whereBetween('overtime_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled'])->whereNull('deleted_at')->get(['overtime_date', 'calculated_hours'])->groupBy(fn (AttendanceOvertime $overtime): string => $this->dateKey($overtime->overtime_date))->map(fn (Collection $rows): float => round((float) $rows->sum('calculated_hours'), 2));

        return ['weeklyAttendanceRangeLabel' => $this->dateRangeLabel($weekStart, $weekEnd),            'weeklyDayLabels' => $weekDays->map(fn (Carbon $date): string => $date->format('d-D'))->all(),            'weeklyAttendanceSeries' => [['name' => 'On Time',                    'data' => $weekDayKeys->map(fn (string $date): int => (int) ($onTimeByDay[$date] ?? 0))->all()],                ['name' => 'Late',                    'data' => $weekDayKeys->map(fn (string $date): int => (int) ($lateByDay[$date] ?? 0))->all()]],            'weeklyOutOfOfficeSeries' => [['name' => 'Leave',                    'data' => $weekDays->map(fn (Carbon $date): int => $this->overlappingEmployeeCount($leaveRows, $date))->all()],                ['name' => 'Business Trip',                    'data' => $weekDays->map(fn (Carbon $date): int => $this->overlappingEmployeeCount($businessTripRows, $date))->all()],                ['name' => 'Deviation',                    'data' => $weekDayKeys->map(fn (string $date): int => (int) ($deviationByDay[$date] ?? 0))->all()]],            'weeklyOvertimeHoursSeries' => $weekDayKeys->map(fn (string $date): float|int => $overtimeHoursByDay[$date] ?? 0)->all()];
    }

    /**     * @param  Collection<int, string>  $activeEmployeeIds     * @return array{     *     monthlyAttendanceRangeLabel: string,     *     monthlyDayLabels: array<int, string>,     *     monthlyAttendanceSeries: array<int, array{name: string, data: array<int, int>}>,     *     monthlyOutOfOfficeSeries: array<int, array{name: string, data: array<int, int>}>     * }     */
    private function monthlyAttendanceCharts(Carbon $monthStart, Collection $activeEmployeeIds): array
    {
        $monthStart = $monthStart->copy()->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();
        $monthDays = $this->workDaysBetween($monthStart, $monthEnd);
        $monthDayKeys = $monthDays->map(fn (Carbon $date): string => $date->toDateString());
        $attendanceRows = Attendance::query()->whereIn('employee_id', $activeEmployeeIds)->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])->whereNotNull('clock_in')->whereNull('deleted_at')->get(['employee_id', 'date', 'clock_in', 'status', 'late_minutes']);
        $onTimeByDay = $attendanceRows->reject(fn (Attendance $attendance): bool => $this->isLateAttendance($attendance))->groupBy(fn (Attendance $attendance): string => $this->dateKey($attendance->date))->map(fn (Collection $rows): int => $rows->pluck('employee_id')->unique()->count());
        $lateByDay = $attendanceRows->filter(fn (Attendance $attendance): bool => $this->isLateAttendance($attendance))->groupBy(fn (Attendance $attendance): string => $this->dateKey($attendance->date))->map(fn (Collection $rows): int => $rows->pluck('employee_id')->unique()->count());
        $leaveRows = LeaveRequest::query()->whereIn('employee_id', $activeEmployeeIds)->whereDate('start_date', '<=', $monthEnd->toDateString())->whereDate('end_date', '>=', $monthStart->toDateString())->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])->where('is_active', true)->whereNull('deleted_at')->get(['employee_id', 'start_date', 'end_date']);
        $businessTripRows = BusinessTrip::query()->whereIn('employee_id', $activeEmployeeIds)->whereDate('start_date', '<=', $monthEnd->toDateString())->whereDate('end_date', '>=', $monthStart->toDateString())->whereRaw('LOWER(COALESCE(approval_status, "")) = ?', ['approved'])->whereNull('deleted_at')->get(['employee_id', 'start_date', 'end_date']);
        $deviationByDay = AttendanceException::query()->whereIn('employee_id', $activeEmployeeIds)->whereBetween('exception_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->whereIn('type', ['late_arrival', 'early_departure'])->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])->whereNull('deleted_at')->get(['employee_id', 'exception_date'])->groupBy(fn (AttendanceException $attendanceException): string => $this->dateKey($attendanceException->exception_date))->map(fn (Collection $rows): int => $rows->pluck('employee_id')->unique()->count());

        return ['monthlyAttendanceRangeLabel' => $this->dateRangeLabel($monthStart, $monthEnd),            'monthlyDayLabels' => $monthDays->map(fn (Carbon $date): string => $date->format('d-D'))->all(),            'monthlyAttendanceSeries' => [['name' => 'On Time',                    'data' => $monthDayKeys->map(fn (string $date): int => (int) ($onTimeByDay[$date] ?? 0))->all()],                ['name' => 'Late',                    'data' => $monthDayKeys->map(fn (string $date): int => (int) ($lateByDay[$date] ?? 0))->all()]],            'monthlyOutOfOfficeSeries' => [['name' => 'Leave',                    'data' => $monthDays->map(fn (Carbon $date): int => $this->overlappingEmployeeCount($leaveRows, $date))->all()],                ['name' => 'Business Trip',                    'data' => $monthDays->map(fn (Carbon $date): int => $this->overlappingEmployeeCount($businessTripRows, $date))->all()],                ['name' => 'Deviation',                    'data' => $monthDayKeys->map(fn (string $date): int => (int) ($deviationByDay[$date] ?? 0))->all()]]];
    }

    /**     * @param  Collection<int, string>  $activeEmployeeIds     * @return array{     *     yearToDateYearLabel: string,     *     yearToDateMonthLabels: array<int, string>,     *     yearToDateLeaveSeries: array<int, array{name: string, data: array<int, int>}>,     *     yearToDateOvertimeHoursSeries: array<int, float|int>     * }     */
    private function yearToDateAttendanceCharts(Carbon $yearStart, Collection $activeEmployeeIds): array
    {
        $yearStart = $yearStart->copy()->startOfYear()->startOfDay();
        $yearEnd = $yearStart->copy()->endOfYear()->endOfDay();
        $monthNumbers = collect(range(1, 12));
        $leaveTypes = LeaveType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        if ($leaveTypes->isEmpty()) {
            $leaveTypes = collect([(object) ['id' => null, 'name' => 'Leave']]);
        }        $leaveRows = LeaveRequest::query()->with('leaveType:id,name')->whereIn('employee_id', $activeEmployeeIds)->whereDate('start_date', '<=', $yearEnd->toDateString())->whereDate('end_date', '>=', $yearStart->toDateString())->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])->where('is_active', true)->whereNull('deleted_at')->get(['id', 'employee_id', 'leave_type_id', 'start_date', 'end_date']);
        $overtimeHoursByMonth = AttendanceOvertime::query()->whereIn('employee_id', $activeEmployeeIds)->whereBetween('overtime_date', [$yearStart->toDateString(), $yearEnd->toDateString()])->whereRaw('LOWER(COALESCE(status, "")) <> ?', ['cancelled'])->whereNull('deleted_at')->get(['overtime_date', 'calculated_hours'])->groupBy(fn (AttendanceOvertime $overtime): int => (int) Carbon::parse($overtime->overtime_date)->format('n'))->map(fn (Collection $rows): float => round((float) $rows->sum('calculated_hours'), 2));

        return ['yearToDateYearLabel' => $yearStart->format('Y'),            'yearToDateMonthLabels' => $monthNumbers->map(fn (int $month): string => Carbon::create((int) $yearStart->format('Y'), $month, 1)->format('M'))->all(),            'yearToDateLeaveSeries' => $leaveTypes->map(fn (object $leaveType): array => ['name' => (string) $leaveType->name,                    'data' => $monthNumbers->map(fn (int $month): int => $this->leaveDaysForMonth($leaveRows, $leaveType->id, (int) $yearStart->format('Y'), $month))->all()])->values()->all(),            'yearToDateOvertimeHoursSeries' => $monthNumbers->map(fn (int $month): float|int => $overtimeHoursByMonth[$month] ?? 0)->all()];
    }

    /**     * @return Collection<int, string>     */
    private function activeEmployeeIdsFor(Carbon $date): Collection
    {
        $todayDate = $date->toDateString();

        return Employee::query()->whereNull('deleted_at')->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])->whereHas('user', function ($query): void {
            $query
                ->where('is_active', true)
                ->whereNotIn('email', self::EXCLUDED_ATTENDANCE_DETAIL_EMAILS)
                ->whereDoesntHave('roles', function ($roleQuery): void {
                    $roleQuery->where('name', 'superuser');
                });
        })->whereHas('deployment', function ($query) use ($todayDate): void {
            $query->whereNull('deleted_at')->whereRaw('LOWER(COALESCE(status, "")) = ?', ['active'])->whereRaw('LOWER(COALESCE(workplace, "")) <> ?', ['rnb jakarta'])->where(function ($query) use ($todayDate): void {
                $query->whereNull('join_date')->orWhereDate('join_date', '<=', $todayDate);
            })->where(function ($query) use ($todayDate): void {
                $query->whereNull('resignation_date')->orWhereDate('resignation_date', '>=', $todayDate);
            });
        })->pluck('id')->filter(static fn (mixed $employeeId): bool => is_string($employeeId) && trim($employeeId) !== '')->values();
    }

    private function percentage(int $count, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return max(0, min((int) round(($count / $total) * 100), 100));
    }

    private function donutValue(int $count, int $total): string
    {
        return $count.'/'.max($total, 1);
    }

    /**     * @return array{name: string, initials: string, avatar_url: string, time: string, rank: int}     */
    private function presentAttendancePerson(Attendance $attendance, int $rank): array
    {
        $name = $this->employeeDisplayName($attendance->employee);

        return ['name' => $name,            'initials' => $this->initials($name),            'avatar_url' => $this->employeeAvatarUrl($attendance->employee?->profile?->profile_picture_path),            'time' => $attendance->clock_in instanceof \DateTimeInterface ? $attendance->clock_in->format('H:i:s').' WIB' : '-',            'rank' => $rank];
    }

    private function isLateAttendance(Attendance $attendance): bool
    {
        return (int) ($attendance->late_minutes ?? 0) > 0;
    }

    private function employeeDisplayName(?Employee $employee): string
    {
        $profileName = $employee?->profile?->name;
        if (is_string($profileName) && trim($profileName) !== '') {
            return trim($profileName);
        }        $username = $employee?->user?->username;
        if (is_string($username) && trim($username) !== '') {
            return trim($username);
        }        $email = $employee?->user?->email;
        if (is_string($email) && trim($email) !== '') {
            return trim(explode('@', $email)[0]);
        }

        return 'Unknown Staff';
    }

    private function businessTripDestination(BusinessTrip $businessTrip): string
    {
        $city = is_string($businessTrip->city_destination) ? trim($businessTrip->city_destination) : '';
        $province = is_string($businessTrip->province_destination) ? trim($businessTrip->province_destination) : '';

        return collect([$city, $province])->filter()->implode(', ') ?: '-';
    }

    private function dateRangeLabel(mixed $startDate, mixed $endDate): string
    {
        if (! $startDate instanceof \DateTimeInterface || ! $endDate instanceof \DateTimeInterface) {
            return '-';
        }        $start = Carbon::instance($startDate);
        $end = Carbon::instance($endDate);
        if ($start->isSameDay($end)) {
            return $start->format('d F Y');
        }

        return $start->format('d').' - '.$end->format('d F Y');
    }

    /**     * @return Collection<int, Carbon>     */
    private function workDaysBetween(Carbon $startDate, Carbon $endDate): Collection
    {
        $days = collect();
        $currentDate = $startDate->copy()->startOfDay();
        $lastDate = $endDate->copy()->startOfDay();
        while ($currentDate->lte($lastDate)) {
            if (! $currentDate->isWeekend()) {
                $days->push($currentDate->copy());
            }            $currentDate->addDay();
        }

        return $days;
    }

    private function dateKey(mixed $date): string
    {
        return $date instanceof \DateTimeInterface ? Carbon::instance($date)->toDateString() : Carbon::parse($date)->toDateString();
    }

    private function overlapsDate(mixed $startDate, mixed $endDate, Carbon $date): bool
    {
        if ($startDate === null || $endDate === null) {
            return false;
        }

        return $date->between(Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay(), true);
    }

    private function overlappingEmployeeCount(Collection $rows, Carbon $date): int
    {
        return $rows->filter(fn (mixed $row): bool => $this->overlapsDate($row->start_date ?? null, $row->end_date ?? null, $date))->pluck('employee_id')->unique()->count();
    }

    private function leaveDaysForMonth(Collection $leaveRows, mixed $leaveTypeId, int $year, int $month): int
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();

        return $leaveRows->filter(fn (LeaveRequest $leaveRequest): bool => $leaveRequest->leave_type_id === $leaveTypeId)->sum(function (LeaveRequest $leaveRequest) use ($monthStart, $monthEnd): int {
            $startDate = Carbon::parse($leaveRequest->start_date)->max($monthStart);
            $endDate = Carbon::parse($leaveRequest->end_date)->min($monthEnd);
            if ($startDate->gt($endDate)) {
                return 0;
            }

            return $startDate->diffInDays($endDate) + 1;
        });
    }

    private function leaveTypeName(LeaveRequest $leaveRequest): string
    {
        $leaveTypeName = $leaveRequest->leaveType?->name;

        return is_string($leaveTypeName) && trim($leaveTypeName) !== '' ? trim($leaveTypeName) : 'Leave';
    }

    private function isSickLeave(LeaveRequest $leaveRequest): bool
    {
        $leaveTypeCode = strtolower(trim((string) ($leaveRequest->leaveType?->code ?? '')));
        $leaveTypeName = strtolower($this->leaveTypeName($leaveRequest));

        return str_contains($leaveTypeCode, 'sick') || str_contains($leaveTypeName, 'sick') || str_contains($leaveTypeName, 'sakit');
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

    private function isImageAttachment(string $attachmentUrl): bool
    {
        $extension = strtolower(pathinfo(parse_url($attachmentUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        return in_array($extension, ['avif', 'gif', 'jpeg', 'jpg', 'png', 'webp'], true);
    }

    private function initials(string $name): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])->filter()->map(static fn (string $part): string => mb_substr($part, 0, 1))->take(2)->implode('');

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
        $storagePath = Str::startsWith($publicPath, 'storage/')
            ? Str::after($publicPath, 'storage/')
            : $publicPath;

        if (Storage::disk('public')->exists($storagePath)) {
            return asset('storage/'.$storagePath);
        }

        return File::exists(public_path($publicPath)) ? asset($publicPath) : $defaultAvatarUrl;
    }
}
