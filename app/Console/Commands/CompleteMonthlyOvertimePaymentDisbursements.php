<?php

namespace App\Console\Commands;

use App\Models\AttendanceOvertime;
use App\Models\OvertimeLifecycleLog;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

#[Signature('overtimes:complete-monthly-payment-disbursements {--month= : Month number to process} {--year= : Year to process} {--dry-run : Show eligible payment disbursements without updating them}')]
#[Description('Complete pending overtime payment disbursements after director approval for the previous month')]
class CompleteMonthlyOvertimePaymentDisbursements extends Command
{
    private const DIRECTOR_APPROVAL = 'director_approval';

    private const PAYMENT_DISBURSEMENT = 'payment_disbursement';

    private const ACTOR_PROFILE_NAME = 'Leonie Putri Andhari';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $period = $this->period();
        if ($period === null) {
            return self::FAILURE;
        }

        $actor = $this->paymentDisbursementActor();
        if (! $actor instanceof User) {
            $this->error('Payment disbursement actor Leonie Putri Andhari tidak ditemukan.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $eligibleCount = 0;
        $completedCount = 0;

        AttendanceOvertime::query()
            ->select(['id'])
            ->whereBetween('overtime_date', [
                $period['start']->toDateString(),
                $period['end']->toDateString(),
            ])
            ->whereHas('lifecycleLogs', function ($query): void {
                $query
                    ->where('event_key', self::DIRECTOR_APPROVAL)
                    ->whereRaw('LOWER(status) = ?', ['approved']);
            })
            ->whereHas('lifecycleLogs', function ($query): void {
                $query
                    ->where('event_key', self::PAYMENT_DISBURSEMENT)
                    ->whereRaw('LOWER(status) = ?', ['pending']);
            })
            ->orderBy('id')
            ->chunkById(100, function ($overtimes) use ($actor, $dryRun, &$completedCount, &$eligibleCount): void {
                foreach ($overtimes as $overtime) {
                    $eligibleCount++;

                    if ($dryRun) {
                        continue;
                    }

                    if ($this->completePaymentDisbursement((string) $overtime->id, $actor)) {
                        $completedCount++;
                    }
                }
            });

        $this->info(sprintf(
            'Overtime payment disbursements processed for %s. Eligible: %d, %s: %d.',
            $period['start']->format('F Y'),
            $eligibleCount,
            $dryRun ? 'would complete' : 'completed',
            $dryRun ? $eligibleCount : $completedCount
        ));

        return self::SUCCESS;
    }

    /**
     * @return array{start: Carbon, end: Carbon}|null
     */
    private function period(): ?array
    {
        $now = Carbon::now('Asia/Jakarta');
        $monthOption = $this->option('month');
        $yearOption = $this->option('year');

        if ($monthOption === null || trim((string) $monthOption) === '') {
            $periodStart = $now->copy()->subMonthNoOverflow()->startOfMonth();

            return [
                'start' => $periodStart,
                'end' => $periodStart->copy()->endOfMonth(),
            ];
        }

        $month = (int) $monthOption;
        $year = $yearOption === null || trim((string) $yearOption) === ''
            ? (int) $now->format('Y')
            : (int) $yearOption;

        if ($month < 1 || $month > 12 || $year < 2020 || $year > 2100) {
            $this->error('Month atau year tidak valid.');

            return null;
        }

        $periodStart = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();

        return [
            'start' => $periodStart,
            'end' => $periodStart->copy()->endOfMonth(),
        ];
    }

    private function paymentDisbursementActor(): ?User
    {
        return User::query()
            ->role('staff')
            ->whereHas('employee.profile', function ($query): void {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(self::ACTOR_PROFILE_NAME)]);
            })
            ->first(['id']);
    }

    private function completePaymentDisbursement(string $overtimeId, User $actor): bool
    {
        return DB::transaction(function () use ($actor, $overtimeId): bool {
            $hasDirectorApproval = OvertimeLifecycleLog::query()
                ->where('overtime_id', $overtimeId)
                ->where('event_key', self::DIRECTOR_APPROVAL)
                ->whereRaw('LOWER(status) = ?', ['approved'])
                ->lockForUpdate()
                ->exists();

            if (! $hasDirectorApproval) {
                return false;
            }

            $paymentDisbursementLog = OvertimeLifecycleLog::query()
                ->where('overtime_id', $overtimeId)
                ->where('event_key', self::PAYMENT_DISBURSEMENT)
                ->whereRaw('LOWER(status) = ?', ['pending'])
                ->lockForUpdate()
                ->first();

            if (! $paymentDisbursementLog instanceof OvertimeLifecycleLog) {
                return false;
            }

            $metadata = is_array($paymentDisbursementLog->metadata) ? $paymentDisbursementLog->metadata : [];
            $metadata['completed_by_monthly_scheduler'] = true;

            $paymentDisbursementLog->forceFill([
                'status' => 'completed',
                'actor_id' => $actor->id,
                'happened_at' => Carbon::now('Asia/Jakarta'),
                'metadata' => $metadata,
            ])->save();

            return true;
        });
    }
}
