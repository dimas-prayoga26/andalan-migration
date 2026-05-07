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
        $currentYear = (int) now()->year;

        $users = User::query()
            ->select(['id'])
            ->get();

        foreach ($users as $user) {
            $employmentStartDateRaw = DB::table('user_employments')
                ->where('user_id', $user->id)
                ->value('start_date');

            $annualBalance = $this->resolveAnnualBalance($employmentStartDateRaw, $currentYear);
            $timestamp = now();
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
                    'updated_at' => now(),
                    'created_at' => $timestamp,
                ]
            );
        }
    }

    private function resolveAnnualBalance(mixed $employmentStartDateRaw, int $currentYear): int
    {
        if (! is_string($employmentStartDateRaw) || trim($employmentStartDateRaw) === '') {
            return 1;
        }

        $employmentStartDate = Carbon::parse($employmentStartDateRaw);
        if ((int) $employmentStartDate->year > $currentYear) {
            return 0;
        }

        return 1;
    }
}
