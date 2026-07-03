<?php

namespace App\Services\Leave;

use App\Models\AttendanceHoliday;
use App\Models\EmployeeDeployment;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Support\Carbon;

class AnnualLeaveBalanceService
{
    public const TOTAL_ANNUAL_ENTITLEMENT = 12;

    public const MINIMUM_TENURE_MONTHS = 12;

    public function syncAll(Carbon $asOf): void
    {
        $annualLeaveType = $this->annualLeaveType();
        if (! $annualLeaveType instanceof LeaveType) {
            return;
        }

        EmployeeDeployment::query()
            ->select(['id', 'employee_id', 'join_date'])
            ->whereNotNull('employee_id')
            ->chunkById(200, function ($deployments) use ($annualLeaveType, $asOf): void {
                foreach ($deployments as $deployment) {
                    $this->syncDeploymentBalance($deployment, $annualLeaveType, (int) $asOf->year, $asOf);
                }
            });
    }

    public function syncEmployeeBalance(string $employeeId, int $year, Carbon $asOf): ?LeaveBalance
    {
        $annualLeaveType = $this->annualLeaveType();
        if (! $annualLeaveType instanceof LeaveType) {
            return null;
        }

        $deployment = EmployeeDeployment::query()
            ->select(['id', 'employee_id', 'join_date'])
            ->where('employee_id', $employeeId)
            ->first();
        if (! $deployment instanceof EmployeeDeployment) {
            return null;
        }

        return $this->syncDeploymentBalance($deployment, $annualLeaveType, $year, $asOf);
    }

    public function isEligible(mixed $joinDate, Carbon $asOf): bool
    {
        $parsedJoinDate = $this->parseJoinDate($joinDate, $asOf);

        return $parsedJoinDate instanceof Carbon
            && $parsedJoinDate->copy()->addMonthsNoOverflow(self::MINIMUM_TENURE_MONTHS)->startOfDay()->lessThanOrEqualTo($asOf->copy()->startOfDay());
    }

    public function personalAnnualQuota(int $year): int
    {
        $jointHolidayDays = AttendanceHoliday::query()
            ->whereYear('date', $year)
            ->where('type', 2)
            ->distinct()
            ->count('date');

        return max(self::TOTAL_ANNUAL_ENTITLEMENT - $jointHolidayDays, 0);
    }

    public function calculateEarnedQuota(mixed $joinDate, int $year, Carbon $asOf, int $monthlyAccrualRate = 1): int
    {
        return $this->calculateEarnedQuotaForPersonalQuota(
            $joinDate,
            $year,
            $asOf,
            $this->personalAnnualQuota($year),
            $monthlyAccrualRate
        );
    }

    public function calculateEarnedQuotaForPersonalQuota(
        mixed $joinDate,
        int $year,
        Carbon $asOf,
        int $personalAnnualQuota,
        int $monthlyAccrualRate = 1
    ): int {
        if ($personalAnnualQuota === 0 || $monthlyAccrualRate <= 0) {
            return 0;
        }

        $parsedJoinDate = $this->parseJoinDate($joinDate, $asOf);
        if (! $parsedJoinDate instanceof Carbon) {
            return 0;
        }

        $yearStart = Carbon::create($year, 1, 1, 0, 0, 0, $asOf->getTimezone())->startOfMonth();
        $yearEnd = $yearStart->copy()->endOfYear()->startOfMonth();
        $lastCompletedMonth = $asOf->copy()->startOfMonth()->subMonth();
        if ($lastCompletedMonth->greaterThan($yearEnd)) {
            $lastCompletedMonth = $yearEnd;
        }

        $joinMonth = $parsedJoinDate->startOfMonth();
        $accrualStart = $joinMonth->greaterThan($yearStart) ? $joinMonth : $yearStart;
        if ($lastCompletedMonth->lessThan($accrualStart)) {
            return 0;
        }

        $completedAccrualMonths = $accrualStart->diffInMonths($lastCompletedMonth) + 1;

        return min($completedAccrualMonths * $monthlyAccrualRate, $personalAnnualQuota);
    }

    private function syncDeploymentBalance(
        EmployeeDeployment $deployment,
        LeaveType $annualLeaveType,
        int $year,
        Carbon $asOf
    ): LeaveBalance {
        $employeeId = trim((string) $deployment->employee_id);
        $monthlyAccrualRate = max((int) round((float) $annualLeaveType->monthly_accrual_rate), 0);
        $earnedQuota = $this->calculateEarnedQuota($deployment->join_date, $year, $asOf, $monthlyAccrualRate);
        $usedQuota = (float) LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $annualLeaveType->id)
            ->whereYear('start_date', $year)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->sum('total_days');

        return LeaveBalance::withTrashed()->updateOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type_id' => $annualLeaveType->id,
                'period_year' => $year,
            ],
            [
                'earned_quota' => $earnedQuota,
                'used_quota' => $usedQuota,
                'remaining_quota' => max($earnedQuota - $usedQuota, 0),
                'deleted_at' => null,
            ]
        );
    }

    private function annualLeaveType(): ?LeaveType
    {
        return LeaveType::query()
            ->where(function ($query): void {
                $query->whereRaw('LOWER(code) IN (?, ?)', ['annual', 'annual_leave'])
                    ->orWhereRaw('LOWER(name) IN (?, ?)', ['cuti tahunan', 'annual leave']);
            })
            ->first();
    }

    private function parseJoinDate(mixed $joinDate, Carbon $asOf): ?Carbon
    {
        if ($joinDate instanceof \DateTimeInterface) {
            return Carbon::createFromFormat('Y-m-d', $joinDate->format('Y-m-d'), $asOf->getTimezone())->startOfDay();
        }

        if (! is_string($joinDate) || trim($joinDate) === '') {
            return null;
        }

        return Carbon::parse($joinDate, $asOf->getTimezone())->startOfDay();
    }
}
