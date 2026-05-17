<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\MetaDataLeaveCompany;
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
                $annualLeaveTypeId = LeaveType::query()
                    ->whereRaw('LOWER(name) = ?', ['cuti khusus'])
                    ->value('id');
            }
            if (! is_string($annualLeaveTypeId) || trim($annualLeaveTypeId) === '') {
                throw new RuntimeException('Leave type cuti tahunan/cuti khusus tidak ditemukan.');
            }

            /** @var array<string, int> $annualQuotaByCompany */
            $annualQuotaByCompany = [];

            $users = User::query()
                ->select(['id'])
                ->with([
                    'employee:id,user_id',
                    'employee.deployment:id,employee_id,join_date,current_company_id',
                ])
                ->get();

            foreach ($users as $user) {
                $employeeId = is_string($user->employee?->id) ? trim($user->employee->id) : '';
                if ($employeeId === '') {
                    continue;
                }

                $employment = $user->employee?->deployment;
                $employmentStartDateRaw = $employment?->join_date?->toDateString();
                $companyId = is_string($employment?->current_company_id) ? trim($employment->current_company_id) : null;
                if ($companyId === '') {
                    $companyId = null;
                }

                $companyKey = $companyId === null ? 'none' : (string) $companyId;

                if (! array_key_exists($companyKey, $annualQuotaByCompany)) {
                    $annualQuotaByCompany[$companyKey] = $companyId !== null
                        ? (int) (MetaDataLeaveCompany::query()
                            ->where('company_id', $companyId)
                            ->where('is_active', true)
                            ->orderByDesc('id')
                            ->value('annual_quota') ?? $defaultAnnualQuota)
                        : $defaultAnnualQuota;
                }

                $annualQuota = max($annualQuotaByCompany[$companyKey], 0);
                $annualBalance = $this->resolveAnnualBalance(
                    $employeeId,
                    $employmentStartDateRaw,
                    $currentYear,
                    $annualQuota,
                    $now
                );

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
                        'used_quota' => 0,
                        'remaining_quota' => $annualBalance,
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
        string $employeeId,
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

        $usedLeaveMonths = $this->resolveUsedLeaveMonths(
            $employeeId,
            $currentYear,
            $accrualPeriodStart,
            $lastCompletedMonthStart
        );

        $accruedBalance = 0;
        $cursor = $accrualPeriodStart->copy();

        while ($cursor->lessThanOrEqualTo($lastCompletedMonthStart)) {
            $monthNumber = (int) $cursor->month;
            if (! isset($usedLeaveMonths[$monthNumber])) {
                $accruedBalance++;
            }

            $cursor->addMonth();
        }

        return min($accruedBalance, $annualQuota);
    }

    /**
     * @return array<int, true>
     */
    private function resolveUsedLeaveMonths(
        string $employeeId,
        int $currentYear,
        Carbon $accrualPeriodStart,
        Carbon $lastCompletedMonthStart
    ): array {
        /** @var array<int, int|string> $months */
        $months = LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereYear('start_date', $currentYear)
            ->whereBetween('start_date', [
                $accrualPeriodStart->toDateString(),
                $lastCompletedMonthStart->copy()->endOfMonth()->toDateString(),
            ])
            ->whereHas('leaveType', function ($query): void {
                $query->whereRaw('LOWER(name) IN (?, ?)', ['cuti tahunan', 'cuti khusus']);
            })
            ->whereRaw('LOWER(status) NOT IN (?, ?)', ['rejected', 'refused'])
            ->selectRaw('DISTINCT MONTH(start_date) as month_number')
            ->pluck('month_number')
            ->all();

        $usedLeaveMonths = [];
        foreach ($months as $month) {
            $monthNumber = (int) $month;
            if ($monthNumber > 0) {
                $usedLeaveMonths[$monthNumber] = true;
            }
        }

        return $usedLeaveMonths;
    }
}
