<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

class LeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $now = now();
            $currentYear = (int) $now->year;
            $defaultAnnualQuota = 12;

            $annualLeaveTypeId = LeaveType::query()
                ->whereRaw('LOWER(name) = ?', ['cuti tahunan'])
                ->value('id');
            if (! is_string($annualLeaveTypeId) || trim($annualLeaveTypeId) === '') {
                throw new RuntimeException('Leave type cuti tahunan tidak ditemukan.');
            }

            $users = User::query()
                ->select(['id'])
                ->with([
                    'employee:id,user_id',
                    'employee.deployment:id,employee_id,join_date',
                ])
                ->get();

            foreach ($users as $user) {
                $employeeId = is_string($user->employee?->id) ? trim($user->employee->id) : '';
                if ($employeeId === '') {
                    continue;
                }

                $employment = $user->employee?->deployment;
                $employmentStartDateRaw = $employment?->join_date?->toDateString();
                $annualQuota = max($defaultAnnualQuota, 0);
                $annualBalance = $this->resolveAnnualBalance(
                    $employmentStartDateRaw,
                    $currentYear,
                    $annualQuota,
                    $now
                );
                $usedQuota = $this->approvedAnnualLeaveUsage($employeeId, $annualLeaveTypeId, $currentYear);

                $timestamp = $now->copy();
                if (is_string($employmentStartDateRaw) && trim($employmentStartDateRaw) !== '') {
                    $timestamp = Carbon::parse($employmentStartDateRaw)->startOfDay();
                }

                LeaveBalance::withTrashed()->updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'leave_type_id' => $annualLeaveTypeId,
                        'period_year' => $currentYear,
                    ],
                    [
                        'earned_quota' => $annualBalance,
                        'used_quota' => $usedQuota,
                        'remaining_quota' => max($annualBalance - $usedQuota, 0),
                        'deleted_at' => null,
                        'updated_at' => $now,
                        'created_at' => $timestamp,
                    ]
                );
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('LeaveBalanceSeeder gagal dijalankan.', 0, $throwable);
        }
    }

    private function resolveAnnualBalance(
        mixed $employmentStartDateRaw,
        int $currentYear,
        int $annualQuota,
        Carbon $now
    ): int {
        if ($annualQuota <= 0) {
            return 0;
        }

        $yearStart = Carbon::create($currentYear, 1, 1)->startOfMonth();
        $lastCompletedMonthStart = $now->copy()->startOfMonth()->subMonth();

        if ($lastCompletedMonthStart->lessThan($yearStart)) {
            return 0;
        }

        $accrualPeriodStart = $yearStart;
        if (is_string($employmentStartDateRaw) && trim($employmentStartDateRaw) !== '') {
            $employmentStartDate = Carbon::parse($employmentStartDateRaw)->startOfMonth();
            if ($employmentStartDate->greaterThan($accrualPeriodStart)) {
                $accrualPeriodStart = $employmentStartDate;
            }
        }

        if ($accrualPeriodStart->greaterThan($lastCompletedMonthStart)) {
            return 0;
        }

        $accruedBalance = $accrualPeriodStart->diffInMonths($lastCompletedMonthStart) + 1;

        return min($accruedBalance, $annualQuota);
    }

    private function approvedAnnualLeaveUsage(string $employeeId, string $annualLeaveTypeId, int $currentYear): float
    {
        return (float) LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $annualLeaveTypeId)
            ->whereYear('start_date', $currentYear)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->sum('total_days');
    }
}
