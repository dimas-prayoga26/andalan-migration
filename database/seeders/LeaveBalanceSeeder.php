<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            /** @var array<string, int> $annualQuotaByCompany */
            $annualQuotaByCompany = [];

            $users = User::query()
                ->select(['id'])
                ->get();

            foreach ($users as $user) {
                $employment = DB::table('employee_deployments')
                    ->select(['join_date', 'current_company_id'])
                    ->join('employees', 'employee_deployments.employee_id', '=', 'employees.id')
                    ->where('employees.user_id', $user->id)
                    ->first();

                $employmentStartDateRaw = $employment?->join_date;
                $companyId = $employment?->current_company_id;
                $companyKey = $companyId === null ? 'none' : (string) $companyId;

                if (! array_key_exists($companyKey, $annualQuotaByCompany)) {
                    $annualQuotaByCompany[$companyKey] = $companyId !== null
                        ? (int) (DB::table('meta_data_leave_companies')
                            ->where('company_id', $companyId)
                            ->value('annual_quota') ?? $defaultAnnualQuota)
                        : $defaultAnnualQuota;
                }

                $annualQuota = max($annualQuotaByCompany[$companyKey], 0);
                $annualBalance = $this->resolveAnnualBalance(
                    (string) $user->id,
                    $employmentStartDateRaw,
                    $currentYear,
                    $annualQuota,
                    $now
                );

                $timestamp = $now->copy();
                if (is_string($employmentStartDateRaw) && trim($employmentStartDateRaw) !== '') {
                    $timestamp = Carbon::parse($employmentStartDateRaw)->startOfDay();
                }

                DB::table('leave_balances')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'year' => $currentYear,
                    ],
                    [
                        'annual_balance' => $annualBalance,
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
        string $userId,
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
            $userId,
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
        string $userId,
        int $currentYear,
        Carbon $accrualPeriodStart,
        Carbon $lastCompletedMonthStart
    ): array {
        /** @var array<int, int|string> $months */
        $months = DB::table('leave_requests')
            ->where('user_id', $userId)
            ->whereYear('start_date', $currentYear)
            ->whereBetween('start_date', [
                $accrualPeriodStart->toDateString(),
                $lastCompletedMonthStart->copy()->endOfMonth()->toDateString(),
            ])
            ->whereRaw('LOWER(permission_types) IN (?, ?)', ['cuti tahunan', 'cuti khusus'])
            ->whereRaw('LOWER(approval_status) NOT IN (?, ?)', ['rejected', 'refused'])
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
