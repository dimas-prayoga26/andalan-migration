<?php

namespace App\View\Composers;

use App\Models\Attendance;
use App\Models\AttendanceHoliday;
use App\Models\AttendanceOvertime;
use App\Models\BusinessTrip;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\Attendance\AttendanceWorkDurationCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttendanceProfileComposer
{
    /**
     * @var array<int, string>
     */
    private const STAFF_STYLE_GLOBAL_MANAGEMENT_POSITIONS = [
        'chief operating officer',
        'super administrator',
    ];

    private const MAX_DAILY_WORK_MINUTES = 480;

    private const DRIVER_MAX_DAILY_WORK_MINUTES = 540;

    private const TASK_HOURS_VERIFICATION = 'task_hours_verification';

    private const VERIFIED_STATUS = 'verified';

    public function __construct(
        private readonly AttendanceWorkDurationCalculator $attendanceWorkDurationCalculator
    ) {}

    public function compose(View $view): void
    {
        $nowJakarta = now('Asia/Jakarta');
        $profileData = [
            'profilePicturePath' => null,
            'profilePositionName' => '-',
            'profileAddressSummary' => '-',
            'profileBusinessEmail' => '-',
            'profileDisplayName' => '-',
            'profileAttendanceDaysCount' => 0,
            'profileElapsedWorkingDaysCount' => 0,
            'profileWorkingDaysCount' => 0,
            'profileWorkingMonthLabel' => $nowJakarta->format('F'),
            'profileLateInCount' => 0,
            'profileLeavesAndSickCount' => 0,
            'profileWeeklyAttendancePercent' => 0,
            'profileWeeklyOnTimePercent' => 0,
            'profileAttendanceRatePercent' => 100.0,
            'profileOnTimeRatePercent' => 100.0,
            'profileLatenessRatePercent' => 0.0,
            'profileOvertimeRatePercent' => 0,
            'profileAttendanceOverviewMonthLabel' => $nowJakarta->format('F'),
            'profileAttendanceOverviewSeries' => [0, 0, 0, 0],
            'profileAttendanceOverviewOnTimeCount' => 0,
            'profileAttendanceOverviewLateCount' => 0,
            'profileAttendanceOverviewLeaveCount' => 0,
            'profileAttendanceOverviewDeviationCount' => 0,
            'profileAttendanceProgressPercent' => 100.0,
            'profileDaysWorkedProgressPercent' => 0,
            'profileProgressOnTimePercent' => 0,
            'profileProgressOnTimeCount' => 0,
            'profileProgressOnTimeTotal' => 0,
            'profileProgressLatePercent' => 0,
            'profileProgressLateCount' => 0,
            'profileProgressLateTotal' => 0,
            'profileWeeklyRequiredHours' => 0.0,
            'profileWeeklyRequiredHoursTarget' => 40,
            'profileWeeklyRequiredHoursPercent' => 0,
            'profileWeeklyOvertimeHours' => 0.0,
            'profileWeeklyOvertimeHoursTarget' => 18,
            'profileWeeklyOvertimeHoursPercent' => 0,
            'profileYearChartYear' => (int) $nowJakarta->year,
            'profileYearMonthLabels' => [],
            'profileYearAttendanceOnTimeSeries' => [],
            'profileYearAttendanceLateSeries' => [],
            'profileYearAttendanceLeaveSeries' => [],
            'profileYearLeaveSeries' => [],
            'profileYearSickSeries' => [],
            'profileYearBusinessTripSeries' => [],
            'profileYearOvertimeHoursSeries' => [],
            'profileMonthlyAttendanceLabels' => [],
            'profileMonthlyAttendanceSeries' => [],
            'profileMonthlyAttendanceDelta' => 0.0,
            'profileStatsMode' => 'staff',
            'managementTotalEmployeesCount' => 0,
            'managementPresentTodayCount' => 0,
            'managementLateTodayCount' => 0,
            'managementLeaveTodayCount' => 0,
        ];

        $authenticatedUserId = Auth::id();
        $authenticatedUser = (is_string($authenticatedUserId) || is_int($authenticatedUserId))
            ? User::query()
                ->with([
                    'employee:id,user_id',
                    'employee.profile:id,employee_id,name,profile_picture_path',
                    'employee.deployment:id,employee_id,current_company_id,current_position_id',
                    'employee.deployment.position:id,name',
                    'employee.deployment.positions:id,name',
                    'employee.latestAddress' => static function ($query): void {
                        $query->select([
                            'employee_addresses.id',
                            'employee_addresses.employee_id',
                            'employee_addresses.village',
                            'employee_addresses.subdistrict',
                            'employee_addresses.created_at',
                        ]);
                    },
                ])
                ->find($authenticatedUserId)
            : null;
        if (! $authenticatedUser instanceof User) {
            $view->with($profileData);

            return;
        }
        $employeeId = is_string($authenticatedUser->employee?->id) ? trim($authenticatedUser->employee->id) : '';
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $usesGlobalManagementScope = $this->hasAnyPositionName($authenticatedUser, self::STAFF_STYLE_GLOBAL_MANAGEMENT_POSITIONS);

        $profileData['profileStatsMode'] = ($isStaffUser || $usesGlobalManagementScope) ? 'staff' : 'management';

        $profilePicturePath = $this->availableProfilePicturePath($authenticatedUser->employee?->profile?->profile_picture_path);
        if ($profilePicturePath !== null) {
            $profileData['profilePicturePath'] = $profilePicturePath;
        }

        if (is_string($authenticatedUser->business_email) && trim($authenticatedUser->business_email) !== '') {
            $profileData['profileBusinessEmail'] = trim($authenticatedUser->business_email);
        } elseif (is_string($authenticatedUser->email) && trim($authenticatedUser->email) !== '') {
            $profileData['profileBusinessEmail'] = trim($authenticatedUser->email);
        }

        if (is_string($authenticatedUser->username) && trim($authenticatedUser->username) !== '') {
            $profileData['profileDisplayName'] = trim($authenticatedUser->username);
        } elseif (is_string($authenticatedUser->email) && trim($authenticatedUser->email) !== '') {
            $profileData['profileDisplayName'] = (string) explode('@', trim($authenticatedUser->email))[0];
        }

        if ($employeeId !== '') {
            $employeeProfileName = $authenticatedUser->employee?->profile?->name;
            if (is_string($employeeProfileName) && trim($employeeProfileName) !== '') {
                $profileData['profileDisplayName'] = trim($employeeProfileName);
            }

            $positionName = collect([$authenticatedUser->employee?->deployment?->position?->name])
                ->merge($authenticatedUser->employee?->deployment?->positions?->pluck('name') ?? [])
                ->map(fn (mixed $positionName): string => trim((string) $positionName))
                ->filter()
                ->unique()
                ->implode(', ');
            if ($positionName !== '') {
                $profileData['profilePositionName'] = $positionName;
            }

            $latestAddress = $authenticatedUser->employee?->latestAddress;
            $villageName = is_string($latestAddress?->village) ? trim($latestAddress->village) : '';
            $subdistrictName = is_string($latestAddress?->subdistrict) ? trim($latestAddress->subdistrict) : '';
            if ($villageName !== '' && $subdistrictName !== '') {
                $profileData['profileAddressSummary'] = $villageName.', '.$subdistrictName;
            } elseif ($villageName !== '') {
                $profileData['profileAddressSummary'] = $villageName;
            } elseif ($subdistrictName !== '') {
                $profileData['profileAddressSummary'] = $subdistrictName;
            }

            $monthStart = $nowJakarta->copy()->startOfMonth();
            $monthEnd = $nowJakarta->copy()->endOfMonth();
            $effectiveMonthStart = $this->attendanceTrackingPeriodStart($monthStart, $monthEnd);
            $workingDaysInCurrentMonth = $this->calculateWorkingDaysInPeriod($monthStart, $monthEnd);

            $profileData['profileAttendanceDaysCount'] = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$effectiveMonthStart->toDateString(), $monthEnd->toDateString()])
                ->whereNotNull('clock_in')
                ->count();

            $monthlyAttendanceQuery = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$effectiveMonthStart->toDateString(), $monthEnd->toDateString()]);
            $monthlyOnTimeCount = (clone $monthlyAttendanceQuery)
                ->whereNotNull('clock_in')
                ->where('late_minutes', '<=', 0)
                ->count();
            $monthlyLateCount = (clone $monthlyAttendanceQuery)
                ->whereNotNull('clock_in')
                ->where('late_minutes', '>', 0)
                ->count();

            $profileData['profileLateInCount'] = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$effectiveMonthStart->toDateString(), $monthEnd->toDateString()])
                ->whereNotNull('clock_in')
                ->where('late_minutes', '>', 0)
                ->count();

            $profileData['profileLeavesAndSickCount'] = LeaveRequest::query()
                ->where('employee_id', $employeeId)
                ->whereDate('start_date', '<=', $monthEnd->toDateString())
                ->whereDate('end_date', '>=', $effectiveMonthStart->toDateString())
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->count();

            $monthlyLeaveDaysCount = $this->countApprovedLeaveDaysForPeriod($employeeId, $effectiveMonthStart, $monthEnd);
            $elapsedMonthEnd = $nowJakarta->lessThan($monthEnd)
                ? $nowJakarta->copy()->startOfDay()
                : $monthEnd->copy();
            $profileData['profileElapsedWorkingDaysCount'] = $this->calculateWorkingDaysInPeriod($monthStart, $elapsedMonthEnd);
            $monthlyAlphaDaysCount = $this->countAlphaWorkDaysForPeriod($employeeId, $monthStart, $elapsedMonthEnd);
            $monthlyOvertimeMinutes = $this->approvedOvertimeQueryForPeriod($employeeId, $effectiveMonthStart, $monthEnd)
                ->get(['approved_start_time', 'approved_end_time'])
                ->sum(fn (AttendanceOvertime $overtime): int => $this->calculateOvertimeMinutes($overtime->approved_start_time, $overtime->approved_end_time));

            $profileData['profileAttendanceOverviewSeries'] = [
                (int) $monthlyOnTimeCount,
                (int) $monthlyLateCount,
                (int) $monthlyLeaveDaysCount,
                (int) $monthlyAlphaDaysCount,
            ];
            $profileData['profileAttendanceOverviewOnTimeCount'] = (int) $monthlyOnTimeCount;
            $profileData['profileAttendanceOverviewLateCount'] = (int) $monthlyLateCount;
            $profileData['profileAttendanceOverviewLeaveCount'] = (int) $monthlyLeaveDaysCount;
            $profileData['profileAttendanceOverviewDeviationCount'] = (int) $monthlyAlphaDaysCount;
            $alphaRateImpactPercent = $this->rateImpactPercent((int) $monthlyAlphaDaysCount, $workingDaysInCurrentMonth);
            $lateRateImpactPercent = $this->rateImpactPercent((int) $monthlyLateCount, $workingDaysInCurrentMonth);
            $profileData['profileAttendanceRatePercent'] = max(0.0, round(100.0 - $alphaRateImpactPercent));
            $profileData['profileOnTimeRatePercent'] = max(0.0, round(100.0 - $lateRateImpactPercent));
            $profileData['profileLatenessRatePercent'] = $lateRateImpactPercent;
            $profileData['profileOvertimeRatePercent'] = max(0, min((int) round((($monthlyOvertimeMinutes / 60) / 72) * 100), 100));
            $profileData['profileAttendanceProgressPercent'] = $profileData['profileAttendanceRatePercent'];
            $profileData['profileDaysWorkedProgressPercent'] = $workingDaysInCurrentMonth > 0
                ? max(0.0, min(round(($profileData['profileElapsedWorkingDaysCount'] / $workingDaysInCurrentMonth) * 100), 100.0))
                : 0.0;
            $profileData['profileProgressOnTimeCount'] = (int) $monthlyOnTimeCount;
            $profileData['profileProgressOnTimeTotal'] = (int) $workingDaysInCurrentMonth;
            $profileData['profileProgressOnTimePercent'] = $profileData['profileOnTimeRatePercent'];
            $profileData['profileProgressLateCount'] = (int) $monthlyLateCount;
            $profileData['profileProgressLateTotal'] = (int) $workingDaysInCurrentMonth;
            $profileData['profileProgressLatePercent'] = $profileData['profileLatenessRatePercent'];

            $weekStart = $nowJakarta->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $nowJakarta->copy()->endOfWeek(Carbon::SUNDAY);
            $weeklyWorkingDaysCount = $this->calculateWorkingDaysInPeriod($weekStart, $weekEnd);

            $weeklyAttendanceQuery = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()]);

            $weeklyCheckedInCount = (clone $weeklyAttendanceQuery)
                ->whereNotNull('clock_in')
                ->count();
            $weeklyOnTimeCount = (clone $weeklyAttendanceQuery)
                ->whereNotNull('clock_in')
                ->where('late_minutes', '<=', 0)
                ->count();
            $weeklyWorkedMinutes = (clone $weeklyAttendanceQuery)
                ->whereNotNull('clock_in')
                ->get(['clock_in', 'clock_out', 'work_hours'])
                ->sum(fn (Attendance $attendance): int => $this->calculateAttendanceWorkMinutes($attendance, $authenticatedUser->employee));
            $weeklyOvertimeMinutes = $this->approvedOvertimeQueryForPeriod($employeeId, $weekStart, $weekEnd)
                ->get(['approved_start_time', 'approved_end_time'])
                ->sum(fn (AttendanceOvertime $overtime): int => $this->calculateOvertimeMinutes($overtime->approved_start_time, $overtime->approved_end_time));

            $profileData['profileWeeklyAttendancePercent'] = $weeklyWorkingDaysCount > 0
                ? max(0, min((int) round(($weeklyCheckedInCount / $weeklyWorkingDaysCount) * 100), 100))
                : 0;
            $profileData['profileWeeklyOnTimePercent'] = $weeklyCheckedInCount > 0
                ? max(0, min((int) round(($weeklyOnTimeCount / $weeklyCheckedInCount) * 100), 100))
                : 0;
            $profileData['profileWeeklyRequiredHours'] = round($weeklyWorkedMinutes / 60, 2);
            $profileData['profileWeeklyRequiredHoursPercent'] = max(0, min((int) round(($profileData['profileWeeklyRequiredHours'] / $profileData['profileWeeklyRequiredHoursTarget']) * 100), 100));
            $profileData['profileWeeklyOvertimeHours'] = round($weeklyOvertimeMinutes / 60, 2);
            $profileData['profileWeeklyOvertimeHoursPercent'] = max(0, min((int) round(($profileData['profileWeeklyOvertimeHours'] / $profileData['profileWeeklyOvertimeHoursTarget']) * 100), 100));

            $monthlyCheckedInCountsByMonth = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereYear('date', (int) $nowJakarta->year)
                ->whereNotNull('clock_in')
                ->selectRaw('MONTH(date) as month_number, COUNT(*) as total_count')
                ->groupBy('month_number')
                ->pluck('total_count', 'month_number');
            $yearOnTimeCountsByMonth = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereYear('date', (int) $nowJakarta->year)
                ->whereNotNull('clock_in')
                ->where('late_minutes', '<=', 0)
                ->selectRaw('MONTH(date) as month_number, COUNT(*) as total_count')
                ->groupBy('month_number')
                ->pluck('total_count', 'month_number');
            $yearLateCountsByMonth = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereYear('date', (int) $nowJakarta->year)
                ->whereNotNull('clock_in')
                ->where('late_minutes', '>', 0)
                ->selectRaw('MONTH(date) as month_number, COUNT(*) as total_count')
                ->groupBy('month_number')
                ->pluck('total_count', 'month_number');
            $yearBusinessTripCountsByMonth = BusinessTrip::query()
                ->where('employee_id', $employeeId)
                ->whereYear('start_date', (int) $nowJakarta->year)
                ->where('approval_status', 'approved')
                ->selectRaw('MONTH(start_date) as month_number, COUNT(*) as total_count')
                ->groupBy('month_number')
                ->pluck('total_count', 'month_number');
            $yearOvertimeHoursByMonth = $this->approvedOvertimeQueryForYear($employeeId, (int) $nowJakarta->year)
                ->get(['overtime_date', 'approved_start_time', 'approved_end_time'])
                ->groupBy(static fn (AttendanceOvertime $overtime): int => (int) Carbon::parse($overtime->overtime_date)->month)
                ->map(fn ($monthOvertimes): float => round($monthOvertimes->sum(fn (AttendanceOvertime $overtime): int => $this->calculateOvertimeMinutes($overtime->approved_start_time, $overtime->approved_end_time)) / 60, 2));

            $monthlyAttendanceLabels = [];
            $monthlyAttendanceSeries = [];
            $yearAttendanceOnTimeSeries = [];
            $yearAttendanceLateSeries = [];
            $yearAttendanceLeaveSeries = [];
            $yearLeaveSeries = [];
            $yearSickSeries = [];
            $yearBusinessTripSeries = [];
            $yearOvertimeHoursSeries = [];

            for ($month = 1; $month <= 12; $month++) {
                $monthStart = Carbon::create((int) $nowJakarta->year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();
                $workingDaysInMonth = $this->calculateWorkingDaysInPeriod($monthStart, $monthEnd);
                $checkedInCount = (int) ($monthlyCheckedInCountsByMonth[$month] ?? 0);
                $leaveDaysInMonth = $this->countApprovedLeaveDaysForPeriod($employeeId, $monthStart, $monthEnd);
                $sickDaysInMonth = $this->countApprovedLeaveDaysForPeriod($employeeId, $monthStart, $monthEnd, true);
                $attendancePercent = $workingDaysInMonth > 0
                    ? max(0, min((int) round(($checkedInCount / $workingDaysInMonth) * 100), 100))
                    : 0;

                $monthlyAttendanceLabels[] = $monthStart->format('M');
                $monthlyAttendanceSeries[] = $attendancePercent;
                $yearAttendanceOnTimeSeries[] = (int) ($yearOnTimeCountsByMonth[$month] ?? 0);
                $yearAttendanceLateSeries[] = (int) ($yearLateCountsByMonth[$month] ?? 0);
                $yearAttendanceLeaveSeries[] = (int) $leaveDaysInMonth;
                $yearLeaveSeries[] = max(0, (int) $leaveDaysInMonth - (int) $sickDaysInMonth);
                $yearSickSeries[] = (int) $sickDaysInMonth;
                $yearBusinessTripSeries[] = (int) ($yearBusinessTripCountsByMonth[$month] ?? 0);
                $yearOvertimeHoursSeries[] = (float) ($yearOvertimeHoursByMonth[$month] ?? 0.0);
            }

            $currentMonthIndex = max((int) $nowJakarta->month - 1, 0);
            $currentMonthPercent = (float) ($monthlyAttendanceSeries[$currentMonthIndex] ?? 0);
            $previousMonthPercent = $currentMonthIndex > 0
                ? (float) ($monthlyAttendanceSeries[$currentMonthIndex - 1] ?? 0)
                : 0.0;

            $profileData['profileMonthlyAttendanceLabels'] = $monthlyAttendanceLabels;
            $profileData['profileMonthlyAttendanceSeries'] = $monthlyAttendanceSeries;
            $profileData['profileMonthlyAttendanceDelta'] = round($currentMonthPercent - $previousMonthPercent, 2);
            $profileData['profileYearMonthLabels'] = $monthlyAttendanceLabels;
            $profileData['profileYearAttendanceOnTimeSeries'] = $yearAttendanceOnTimeSeries;
            $profileData['profileYearAttendanceLateSeries'] = $yearAttendanceLateSeries;
            $profileData['profileYearAttendanceLeaveSeries'] = $yearAttendanceLeaveSeries;
            $profileData['profileYearLeaveSeries'] = $yearLeaveSeries;
            $profileData['profileYearSickSeries'] = $yearSickSeries;
            $profileData['profileYearBusinessTripSeries'] = $yearBusinessTripSeries;
            $profileData['profileYearOvertimeHoursSeries'] = $yearOvertimeHoursSeries;
        }

        $profileData['profileWorkingDaysCount'] = $this->calculateWorkingDaysInMonth($nowJakarta);

        if (! $isStaffUser) {
            $employeeScopeQuery = Employee::query()->select('id');

            if ($isBoardOfDirectur && ! $usesGlobalManagementScope) {
                $currentCompanyId = $authenticatedUser->employee?->deployment?->current_company_id;
                if (! is_string($currentCompanyId) || trim($currentCompanyId) === '') {
                    $employeeScopeQuery->whereRaw('1 = 0');
                } else {
                    $employeeScopeQuery->whereHas('deployment', function ($query) use ($currentCompanyId): void {
                        $query->where('current_company_id', $currentCompanyId);
                    });
                }
            }

            $scopedEmployeeIds = $employeeScopeQuery
                ->pluck('id')
                ->filter(static fn (mixed $value): bool => is_string($value) && trim($value) !== '')
                ->values();

            $profileData['managementTotalEmployeesCount'] = $scopedEmployeeIds->count();

            if ($profileData['managementTotalEmployeesCount'] > 0) {
                $todayDate = $nowJakarta->toDateString();
                $attendanceTodayQuery = Attendance::query()
                    ->whereIn('employee_id', $scopedEmployeeIds)
                    ->whereDate('date', $todayDate);

                $profileData['managementPresentTodayCount'] = (clone $attendanceTodayQuery)
                    ->whereNotNull('clock_in')
                    ->count();

                $profileData['managementLateTodayCount'] = (clone $attendanceTodayQuery)
                    ->whereNotNull('clock_in')
                    ->where('late_minutes', '>', 0)
                    ->count();

                $profileData['managementLeaveTodayCount'] = LeaveRequest::query()
                    ->whereIn('employee_id', $scopedEmployeeIds)
                    ->whereDate('start_date', '<=', $todayDate)
                    ->whereDate('end_date', '>=', $todayDate)
                    ->where('status', 'approved')
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->count();
            }
        }

        $view->with($profileData);
    }

    private function calculateWorkingDaysInMonth(Carbon $referenceDate): int
    {
        $monthStart = $referenceDate->copy()->startOfMonth();
        $monthEnd = $referenceDate->copy()->endOfMonth();

        return $this->calculateWorkingDaysInPeriod($monthStart, $monthEnd);
    }

    private function calculateWorkingDaysInPeriod(Carbon $periodStart, Carbon $periodEnd): int
    {
        return count($this->workingDayDateKeysForPeriod($periodStart, $periodEnd));
    }

    private function attendanceTrackingPeriodStart(Carbon $periodStart, Carbon $periodEnd): Carbon
    {
        $configuredStartDate = config('attendance.tracking_start_date');
        if (! is_string($configuredStartDate) || trim($configuredStartDate) === '') {
            return $periodStart->copy()->startOfDay();
        }

        try {
            $trackingStart = Carbon::parse(trim($configuredStartDate), 'Asia/Jakarta')->startOfDay();
        } catch (\Throwable) {
            return $periodStart->copy()->startOfDay();
        }

        if ($trackingStart->lessThanOrEqualTo($periodStart) || $trackingStart->greaterThan($periodEnd)) {
            return $periodStart->copy()->startOfDay();
        }

        return $trackingStart;
    }

    private function rateImpactPercent(int $eventCount, int $workingDaysCount): float
    {
        if ($workingDaysCount <= 0) {
            return 0.0;
        }

        return max(0.0, min(round(($eventCount / $workingDaysCount) * 100), 100.0));
    }

    /**
     * @return list<string>
     */
    private function workingDayDateKeysForPeriod(Carbon $periodStart, Carbon $periodEnd): array
    {
        $normalizedStart = $periodStart->copy()->startOfDay();
        $normalizedEnd = $periodEnd->copy()->startOfDay();
        if ($normalizedStart->greaterThan($normalizedEnd)) {
            return [];
        }

        $holidayDates = AttendanceHoliday::query()
            ->whereBetween('date', [$normalizedStart->toDateString(), $normalizedEnd->toDateString()])
            ->pluck('date')
            ->map(static function (mixed $holidayDate): ?string {
                if ($holidayDate instanceof \DateTimeInterface) {
                    return Carbon::instance($holidayDate)->toDateString();
                }

                if (is_string($holidayDate) && trim($holidayDate) !== '') {
                    return trim($holidayDate);
                }

                return null;
            })
            ->filter(static fn (mixed $holidayDate): bool => is_string($holidayDate) && $holidayDate !== '')
            ->values()
            ->all();
        $holidayMap = array_fill_keys($holidayDates, true);
        $workingDayKeys = [];

        for ($day = $normalizedStart->copy(); $day->lte($normalizedEnd); $day->addDay()) {
            if ($day->isWeekend()) {
                continue;
            }

            if (isset($holidayMap[$day->format('Y-m-d')])) {
                continue;
            }

            $workingDayKeys[] = $day->format('Y-m-d');
        }

        return $workingDayKeys;
    }

    private function countAlphaWorkDaysForPeriod(string $employeeId, Carbon $periodStart, Carbon $periodEnd): int
    {
        $workingDayKeys = $this->workingDayDateKeysForPeriod($periodStart, $periodEnd);
        if ($workingDayKeys === []) {
            return 0;
        }

        $attendedDateMap = array_fill_keys(
            Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->whereNotNull('clock_in')
                ->pluck('date')
                ->map(fn (mixed $date): ?string => $this->dateKey($date))
                ->filter()
                ->values()
                ->all(),
            true
        );
        $leaveDateMap = array_fill_keys($this->approvedLeaveDateKeysForPeriod($employeeId, $periodStart, $periodEnd), true);
        $businessTripDateMap = array_fill_keys($this->approvedBusinessTripDateKeysForPeriod($employeeId, $periodStart, $periodEnd), true);
        $alphaDays = 0;

        foreach ($workingDayKeys as $workingDayKey) {
            if (isset($attendedDateMap[$workingDayKey]) || isset($leaveDateMap[$workingDayKey]) || isset($businessTripDateMap[$workingDayKey])) {
                continue;
            }

            $alphaDays++;
        }

        return $alphaDays;
    }

    /**
     * @return list<string>
     */
    private function approvedLeaveDateKeysForPeriod(string $employeeId, Carbon $periodStart, Carbon $periodEnd): array
    {
        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $periodEnd->toDateString())
            ->whereDate('end_date', '>=', $periodStart->toDateString())
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get(['start_date', 'end_date'])
            ->flatMap(fn (LeaveRequest $leaveRequest): array => $this->dateKeysBetween(
                $leaveRequest->start_date,
                $leaveRequest->end_date,
                $periodStart,
                $periodEnd
            ))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function approvedBusinessTripDateKeysForPeriod(string $employeeId, Carbon $periodStart, Carbon $periodEnd): array
    {
        return BusinessTrip::query()
            ->where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $periodEnd->toDateString())
            ->whereDate('end_date', '>=', $periodStart->toDateString())
            ->whereRaw('LOWER(COALESCE(approval_status, "")) = ?', ['approved'])
            ->whereNull('deleted_at')
            ->get(['start_date', 'end_date'])
            ->flatMap(fn (BusinessTrip $businessTrip): array => $this->dateKeysBetween(
                $businessTrip->start_date,
                $businessTrip->end_date,
                $periodStart,
                $periodEnd
            ))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function dateKeysBetween(mixed $startDate, mixed $endDate, Carbon $periodStart, Carbon $periodEnd): array
    {
        try {
            $start = Carbon::parse($startDate, 'Asia/Jakarta')->startOfDay();
            $end = Carbon::parse($endDate, 'Asia/Jakarta')->startOfDay();
        } catch (\Throwable) {
            return [];
        }

        $effectiveStart = $start->greaterThan($periodStart) ? $start : $periodStart->copy()->startOfDay();
        $effectiveEnd = $end->lessThan($periodEnd) ? $end : $periodEnd->copy()->startOfDay();
        if ($effectiveStart->greaterThan($effectiveEnd)) {
            return [];
        }

        $dateKeys = [];
        for ($date = $effectiveStart->copy(); $date->lte($effectiveEnd); $date->addDay()) {
            $dateKeys[] = $date->toDateString();
        }

        return $dateKeys;
    }

    private function dateKey(mixed $date): ?string
    {
        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance($date)->toDateString();
        }

        if (is_string($date) && trim($date) !== '') {
            try {
                return Carbon::parse($date, 'Asia/Jakarta')->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function calculateAttendanceWorkMinutes(Attendance $attendance, ?Employee $employee = null): int
    {
        if ($attendance->clock_in instanceof \DateTimeInterface && $attendance->clock_out instanceof \DateTimeInterface) {
            $minutes = $this->attendanceWorkDurationCalculator->netMinutesBetween(
                Carbon::instance($attendance->clock_in),
                Carbon::instance($attendance->clock_out),
                $this->shouldDeductRestTime($employee)
            );

            return min($minutes, $this->maxDailyWorkMinutes($employee));
        }

        $workHours = $attendance->work_hours;
        if (is_numeric($workHours)) {
            return min(max(0, (int) round(((float) $workHours) * 60)), $this->maxDailyWorkMinutes($employee));
        }

        return 0;
    }

    private function maxDailyWorkMinutes(?Employee $employee): int
    {
        return $this->shouldDeductRestTime($employee)
            ? self::MAX_DAILY_WORK_MINUTES
            : self::DRIVER_MAX_DAILY_WORK_MINUTES;
    }

    private function shouldDeductRestTime(?Employee $employee): bool
    {
        return ! ($employee instanceof Employee && $employee->hasPositionName('Driver'));
    }

    private function approvedOvertimeQueryForPeriod(string $employeeId, Carbon $periodStart, Carbon $periodEnd): Builder
    {
        return $this->approvedOvertimeQuery($employeeId)
            ->whereBetween('overtime_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
    }

    private function approvedOvertimeQueryForYear(string $employeeId, int $year): Builder
    {
        return $this->approvedOvertimeQuery($employeeId)
            ->whereYear('overtime_date', $year);
    }

    private function approvedOvertimeQuery(string $employeeId): Builder
    {
        return AttendanceOvertime::query()
            ->where('employee_id', $employeeId)
            ->whereNotNull('approved_start_time')
            ->whereNotNull('approved_end_time')
            ->whereHas('lifecycleLogs', function (Builder $query): void {
                $query
                    ->where('event_key', self::TASK_HOURS_VERIFICATION)
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', [self::VERIFIED_STATUS]);
            });
    }

    private function calculateOvertimeMinutes(mixed $startTimeValue, mixed $endTimeValue): int
    {
        if (! is_string($startTimeValue) || ! is_string($endTimeValue)) {
            return 0;
        }

        try {
            $startTime = Carbon::createFromFormat('H:i:s', $startTimeValue);
            $endTime = Carbon::createFromFormat('H:i:s', $endTimeValue);

            if ($endTime->lessThan($startTime)) {
                $endTime->addDay();
            }

            return $startTime->diffInMinutes($endTime);
        } catch (\Throwable) {
            return 0;
        }
    }

    private function countApprovedLeaveDaysForPeriod(string $employeeId, Carbon $periodStart, Carbon $periodEnd, ?bool $sickOnly = null): int
    {
        $leaveQuery = LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $periodEnd->toDateString())
            ->whereDate('end_date', '>=', $periodStart->toDateString())
            ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
            ->where('is_active', true)
            ->whereNull('deleted_at');

        if ($sickOnly === true) {
            $leaveQuery->whereHas('leaveType', function ($query): void {
                $query
                    ->whereRaw('LOWER(COALESCE(code, "")) in (?, ?)', ['sick', 'sick_leave'])
                    ->orWhereRaw('LOWER(COALESCE(name, "")) in (?, ?)', ['sakit', 'sick leave']);
            });
        }

        return $leaveQuery
            ->get(['start_date', 'end_date'])
            ->sum(function (LeaveRequest $leaveRequest) use ($periodStart, $periodEnd): int {
                try {
                    $leaveStart = Carbon::parse($leaveRequest->start_date, 'Asia/Jakarta')->startOfDay();
                    $leaveEnd = Carbon::parse($leaveRequest->end_date, 'Asia/Jakarta')->startOfDay();
                } catch (\Throwable) {
                    return 0;
                }

                $effectiveStart = $leaveStart->greaterThan($periodStart) ? $leaveStart : $periodStart->copy();
                $effectiveEnd = $leaveEnd->lessThan($periodEnd) ? $leaveEnd : $periodEnd->copy();

                return $this->calculateWorkingDaysInPeriod($effectiveStart, $effectiveEnd);
            });
    }

    private function isBoardOfDirectur(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('board of directur')
            || $normalizedRoleNames->contains('board of directors');
    }

    /**
     * @param  array<int, string>  $positionNames
     */
    private function hasAnyPositionName(?User $user, array $positionNames): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedTargetNames = collect($positionNames)
            ->map(static fn (string $positionName): string => strtolower(trim($positionName)))
            ->filter()
            ->values();

        if ($normalizedTargetNames->isEmpty()) {
            return false;
        }

        $deployment = $user->employee?->deployment;
        if (! $deployment) {
            return false;
        }

        $normalizedUserPositionNames = collect();

        $primaryPositionName = $deployment->position?->name;
        if (is_string($primaryPositionName) && trim($primaryPositionName) !== '') {
            $normalizedUserPositionNames->push(strtolower(trim($primaryPositionName)));
        }

        $deployment->positions
            ->pluck('name')
            ->filter(static fn (mixed $positionName): bool => is_string($positionName) && trim($positionName) !== '')
            ->map(static fn (string $positionName): string => strtolower(trim($positionName)))
            ->each(static function (string $positionName) use ($normalizedUserPositionNames): void {
                $normalizedUserPositionNames->push($positionName);
            });

        return $normalizedUserPositionNames
            ->unique()
            ->intersect($normalizedTargetNames)
            ->isNotEmpty();
    }

    private function availableProfilePicturePath(mixed $profilePicturePath): ?string
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
            return 'storage/'.$storagePath;
        }

        return File::exists(public_path($publicPath)) ? $publicPath : null;
    }

    private function isStaffUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(static fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('staff');
    }
}
