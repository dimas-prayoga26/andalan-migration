<?php

namespace App\View\Composers;

use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceHoliday;
use App\Models\AttendanceOvertime;
use App\Models\BusinessTrip;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceProfileComposer
{
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
            'profileWorkingDaysCount' => 0,
            'profileWorkingMonthLabel' => $nowJakarta->format('F'),
            'profileLateInCount' => 0,
            'profileLeavesAndSickCount' => 0,
            'profileWeeklyAttendancePercent' => 0,
            'profileWeeklyOnTimePercent' => 0,
            'profileAttendanceRatePercent' => 0,
            'profileOnTimeRatePercent' => 0,
            'profileLatenessRatePercent' => 0,
            'profileOvertimeRatePercent' => 0,
            'profileAttendanceOverviewMonthLabel' => $nowJakarta->format('F'),
            'profileAttendanceOverviewSeries' => [0, 0, 0, 0],
            'profileAttendanceOverviewOnTimeCount' => 0,
            'profileAttendanceOverviewLateCount' => 0,
            'profileAttendanceOverviewLeaveCount' => 0,
            'profileAttendanceOverviewDeviationCount' => 0,
            'profileAttendanceProgressPercent' => 0,
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
                    'userProfile:id,user_id,profile_picture',
                    'employee:id,user_id',
                    'employee.profile:id,employee_id,name',
                    'employee.deployment:id,employee_id,current_company_id,current_position_id',
                    'employee.deployment.position:id,name',
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

        $profileData['profileStatsMode'] = $isStaffUser ? 'staff' : 'management';

        if (is_string($authenticatedUser->userProfile?->profile_picture) && trim($authenticatedUser->userProfile->profile_picture) !== '') {
            $profileData['profilePicturePath'] = trim($authenticatedUser->userProfile->profile_picture);
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

            $positionName = $authenticatedUser->employee?->deployment?->position?->name;
            if (is_string($positionName) && trim($positionName) !== '') {
                $profileData['profilePositionName'] = trim($positionName);
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

            $profileData['profileAttendanceDaysCount'] = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereYear('date', (int) $nowJakarta->year)
                ->whereMonth('date', (int) $nowJakarta->month)
                ->whereNotNull('clock_in')
                ->count();

            $monthlyAttendanceQuery = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereYear('date', (int) $nowJakarta->year)
                ->whereMonth('date', (int) $nowJakarta->month);
            $monthlyOnTimeCount = (clone $monthlyAttendanceQuery)
                ->whereNotNull('clock_in')
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['masuk'])
                ->count();
            $monthlyLateCount = (clone $monthlyAttendanceQuery)
                ->whereNotNull('clock_in')
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['terlambat'])
                ->count();

            $profileData['profileLateInCount'] = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereYear('date', (int) $nowJakarta->year)
                ->whereMonth('date', (int) $nowJakarta->month)
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['terlambat'])
                ->count();

            $profileData['profileLeavesAndSickCount'] = LeaveRequest::query()
                ->where('employee_id', $employeeId)
                ->whereYear('start_date', (int) $nowJakarta->year)
                ->whereMonth('start_date', (int) $nowJakarta->month)
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->count();

            $monthStart = $nowJakarta->copy()->startOfMonth();
            $monthEnd = $nowJakarta->copy()->endOfMonth();
            $workingDaysInCurrentMonth = $this->calculateWorkingDaysInMonth($nowJakarta);
            $monthlyLeaveDaysCount = $this->countApprovedLeaveDaysForPeriod($employeeId, $monthStart, $monthEnd);
            $monthlyDeviationCount = AttendanceException::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('exception_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->whereIn('type', ['late_arrival', 'early_departure'])
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['approved'])
                ->count();
            $monthlyOvertimeMinutes = AttendanceOvertime::query()
                ->where('employee_id', $employeeId)
                ->whereYear('overtime_date', (int) $nowJakarta->year)
                ->whereMonth('overtime_date', (int) $nowJakarta->month)
                ->whereNotNull('actual_start_time')
                ->whereNotNull('actual_end_time')
                ->get(['actual_start_time', 'actual_end_time'])
                ->sum(fn (AttendanceOvertime $overtime): int => $this->calculateOvertimeMinutes($overtime->actual_start_time, $overtime->actual_end_time));

            $profileData['profileAttendanceOverviewSeries'] = [
                (int) $monthlyOnTimeCount,
                (int) $monthlyLateCount,
                (int) $monthlyLeaveDaysCount,
                (int) $monthlyDeviationCount,
            ];
            $profileData['profileAttendanceOverviewOnTimeCount'] = (int) $monthlyOnTimeCount;
            $profileData['profileAttendanceOverviewLateCount'] = (int) $monthlyLateCount;
            $profileData['profileAttendanceOverviewLeaveCount'] = (int) $monthlyLeaveDaysCount;
            $profileData['profileAttendanceOverviewDeviationCount'] = (int) $monthlyDeviationCount;
            $profileData['profileAttendanceRatePercent'] = $workingDaysInCurrentMonth > 0
                ? max(0, min((int) round(($profileData['profileAttendanceDaysCount'] / $workingDaysInCurrentMonth) * 100), 100))
                : 0;
            $profileData['profileOnTimeRatePercent'] = $workingDaysInCurrentMonth > 0
                ? max(0, min((int) round(($monthlyOnTimeCount / $workingDaysInCurrentMonth) * 100), 100))
                : 0;
            $profileData['profileLatenessRatePercent'] = $workingDaysInCurrentMonth > 0
                ? max(0, min((int) round(($monthlyLateCount / $workingDaysInCurrentMonth) * 100), 100))
                : 0;
            $profileData['profileOvertimeRatePercent'] = max(0, min((int) round((($monthlyOvertimeMinutes / 60) / 72) * 100), 100));
            $profileData['profileAttendanceProgressPercent'] = $profileData['profileAttendanceRatePercent'];
            $profileData['profileProgressOnTimeCount'] = (int) $monthlyOnTimeCount;
            $profileData['profileProgressOnTimeTotal'] = (int) $profileData['profileAttendanceDaysCount'];
            $profileData['profileProgressOnTimePercent'] = $profileData['profileAttendanceDaysCount'] > 0
                ? max(0, min((int) round(($monthlyOnTimeCount / $profileData['profileAttendanceDaysCount']) * 100), 100))
                : 0;
            $profileData['profileProgressLateCount'] = (int) $monthlyLateCount;
            $profileData['profileProgressLateTotal'] = (int) $profileData['profileAttendanceDaysCount'];
            $profileData['profileProgressLatePercent'] = $profileData['profileAttendanceDaysCount'] > 0
                ? max(0, min((int) round(($monthlyLateCount / $profileData['profileAttendanceDaysCount']) * 100), 100))
                : 0;

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
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['masuk'])
                ->count();
            $weeklyWorkedMinutes = (clone $weeklyAttendanceQuery)
                ->whereNotNull('clock_in')
                ->get(['clock_in', 'clock_out', 'work_hours'])
                ->sum(fn (Attendance $attendance): int => $this->calculateAttendanceWorkMinutes($attendance));
            $weeklyOvertimeMinutes = AttendanceOvertime::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('overtime_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->whereNotNull('actual_start_time')
                ->whereNotNull('actual_end_time')
                ->get(['actual_start_time', 'actual_end_time'])
                ->sum(fn (AttendanceOvertime $overtime): int => $this->calculateOvertimeMinutes($overtime->actual_start_time, $overtime->actual_end_time));

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
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['masuk'])
                ->selectRaw('MONTH(date) as month_number, COUNT(*) as total_count')
                ->groupBy('month_number')
                ->pluck('total_count', 'month_number');
            $yearLateCountsByMonth = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereYear('date', (int) $nowJakarta->year)
                ->whereNotNull('clock_in')
                ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['terlambat'])
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
            $yearOvertimeHoursByMonth = AttendanceOvertime::query()
                ->where('employee_id', $employeeId)
                ->whereYear('overtime_date', (int) $nowJakarta->year)
                ->whereNotNull('actual_start_time')
                ->whereNotNull('actual_end_time')
                ->get(['overtime_date', 'actual_start_time', 'actual_end_time'])
                ->groupBy(static fn (AttendanceOvertime $overtime): int => (int) Carbon::parse($overtime->overtime_date)->month)
                ->map(fn ($monthOvertimes): float => round($monthOvertimes->sum(fn (AttendanceOvertime $overtime): int => $this->calculateOvertimeMinutes($overtime->actual_start_time, $overtime->actual_end_time)) / 60, 2));

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

            if ($isBoardOfDirectur) {
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
                    ->whereRaw('LOWER(COALESCE(status, "")) = ?', ['terlambat'])
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
        $normalizedStart = $periodStart->copy()->startOfDay();
        $normalizedEnd = $periodEnd->copy()->startOfDay();
        if ($normalizedStart->greaterThan($normalizedEnd)) {
            return 0;
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
        $workingDays = 0;

        for ($day = $normalizedStart->copy(); $day->lte($normalizedEnd); $day->addDay()) {
            if ($day->isWeekend()) {
                continue;
            }

            if (isset($holidayMap[$day->format('Y-m-d')])) {
                continue;
            }

            $workingDays++;
        }

        return $workingDays;
    }

    private function calculateAttendanceWorkMinutes(Attendance $attendance): int
    {
        $workHours = $attendance->work_hours;
        if (is_numeric($workHours)) {
            return max(0, (int) round(((float) $workHours) * 60));
        }

        if (! $attendance->clock_in instanceof \DateTimeInterface || ! $attendance->clock_out instanceof \DateTimeInterface) {
            return 0;
        }

        return $this->calculateOvertimeMinutes(
            $attendance->clock_in->format('H:i:s'),
            $attendance->clock_out->format('H:i:s')
        );
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
