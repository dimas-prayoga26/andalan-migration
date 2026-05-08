<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $currentYear = (int) $now->year;
        $defaultAnnualQuota = 12;
        $annualQuotaByCompany = [];

        $users = User::query()
            ->select(['id'])
            ->get();

        foreach ($users as $user) {
            $employment = DB::table('user_employments')
                ->select(['start_date', 'company_id'])
                ->where('user_id', $user->id)
                ->first();

            $employmentStartDateRaw = $employment?->start_date;
            $companyId = (int) ($employment?->company_id ?? 0);

            if (! array_key_exists($companyId, $annualQuotaByCompany)) {
                $annualQuotaByCompany[$companyId] = $companyId > 0
                    ? (int) (DB::table('meta_data_leave_companies')
                        ->where('company_id', $companyId)
                        ->value('annual_quota') ?? $defaultAnnualQuota)
                    : $defaultAnnualQuota;
            }

            $annualQuota = max($annualQuotaByCompany[$companyId], 0);
            $annualBalance = $this->resolveAnnualBalance(
                (int) $user->id,
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
    }

    private function resolveAnnualBalance(
        int $userId,
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
        int $userId,
        int $currentYear,
        Carbon $accrualPeriodStart,
        Carbon $lastCompletedMonthStart
    ): array {
        /** @var array<int, int|string> $months */
        $months = DB::table('attendance_permissions')
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
