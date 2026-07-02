<?php

namespace App\Console\Commands;

use App\Models\BusinessTrip;
use App\Models\BusinessTripLifecycleLog;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('business-trips:lifecycle:sync')]
#[Description('Synchronize business trip lifecycle status from trip dates')]
class SyncBusinessTripLifecycleStatus extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now('Asia/Jakarta')->startOfDay();
        $pendingCount = 0;
        $completedCount = 0;

        BusinessTrip::query()
            ->with([
                'employee:id,user_id',
                'lifecycleLogs',
            ])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->orderBy('id')
            ->chunk(100, function ($businessTrips) use ($today, &$pendingCount, &$completedCount): void {
                foreach ($businessTrips as $businessTrip) {
                    if (! $this->businessTripHasSupervisorApproval($businessTrip)) {
                        continue;
                    }

                    if (! $this->tripExecutionCanBeSynced($businessTrip)) {
                        continue;
                    }

                    $startDate = Carbon::parse($businessTrip->start_date)->timezone('Asia/Jakarta')->startOfDay();
                    $endDate = Carbon::parse($businessTrip->end_date)->timezone('Asia/Jakarta')->startOfDay();

                    if ($today->lt($startDate)) {
                        continue;
                    }

                    if ($today->gt($endDate)) {
                        $this->markTripExecutionCompleted($businessTrip, $endDate);
                        $this->markTripReportPending($businessTrip, $endDate);
                        $completedCount++;

                        continue;
                    }

                    $this->markTripExecutionPending($businessTrip);
                    $pendingCount++;
                }
            });

        $this->info(sprintf(
            'Business trip lifecycle synchronized. Trip execution pending: %d, completed: %d.',
            $pendingCount,
            $completedCount
        ));

        return self::SUCCESS;
    }

    private function businessTripHasSupervisorApproval(BusinessTrip $businessTrip): bool
    {
        if ($this->isApprovedStatus((string) $businessTrip->approval_status)) {
            return true;
        }

        $supervisorReview = $businessTrip->lifecycleLogs
            ->first(fn (BusinessTripLifecycleLog $lifecycleLog): bool => $lifecycleLog->event_key === 'supervisor_review');

        return $supervisorReview instanceof BusinessTripLifecycleLog
            && $this->isApprovedStatus((string) $supervisorReview->status);
    }

    private function markTripExecutionPending(BusinessTrip $businessTrip): void
    {
        $this->updateLifecycleLog($businessTrip, 'trip_execution', [
            'phase' => 'trip_execution',
            'step_order' => 5,
            'title' => 'Business Trip in Progress',
            'status' => 'pending',
            'actor_id' => $this->businessTripStaffActorId($businessTrip),
            'happened_at' => null,
            'metadata' => $this->tripExecutionMetadata($businessTrip),
        ]);
    }

    private function tripExecutionCanBeSynced(BusinessTrip $businessTrip): bool
    {
        $tripExecution = $businessTrip->lifecycleLogs
            ->first(fn (BusinessTripLifecycleLog $lifecycleLog): bool => $lifecycleLog->event_key === 'trip_execution');

        return ! $tripExecution instanceof BusinessTripLifecycleLog
            || ! $this->isRejectedStatus((string) $tripExecution->status);
    }

    private function markTripExecutionCompleted(BusinessTrip $businessTrip, Carbon $endDate): void
    {
        $this->updateLifecycleLog($businessTrip, 'trip_execution', [
            'phase' => 'trip_execution',
            'step_order' => 5,
            'title' => 'Business Trip in Progress',
            'status' => 'complete',
            'actor_id' => $this->businessTripStaffActorId($businessTrip),
            'happened_at' => $endDate->copy()->endOfDay()->timezone('UTC'),
            'metadata' => $this->tripExecutionMetadata($businessTrip),
        ]);
    }

    private function markTripReportPending(BusinessTrip $businessTrip, Carbon $endDate): void
    {
        $tripReport = $businessTrip->lifecycleLogs
            ->first(fn (BusinessTripLifecycleLog $lifecycleLog): bool => $lifecycleLog->event_key === 'trip_report');

        if ($tripReport instanceof BusinessTripLifecycleLog
            && ! $this->isWaitingStatus((string) $tripReport->status)
            && ! $this->isPendingStatus((string) $tripReport->status)) {
            return;
        }

        $this->updateLifecycleLog($businessTrip, 'trip_report', [
            'phase' => 'post_trip_settlement',
            'step_order' => 6,
            'title' => 'Trip Report & Task Submitted',
            'status' => 'pending',
            'actor_id' => null,
            'happened_at' => $endDate->copy()->endOfDay()->timezone('UTC'),
        ]);
    }

    /**
     * @param  array{phase: string, step_order: int, title: string, status: string, happened_at: ?Carbon, actor_id?: ?string, metadata?: array<string, string|null>}  $values
     */
    private function updateLifecycleLog(BusinessTrip $businessTrip, string $eventKey, array $values): void
    {
        BusinessTripLifecycleLog::query()->updateOrCreate(
            [
                'business_trip_id' => $businessTrip->id,
                'event_key' => $eventKey,
            ],
            $values
        );
    }

    /**
     * @return array{trip_start_date: string|null, trip_end_date: string|null}
     */
    private function tripExecutionMetadata(BusinessTrip $businessTrip): array
    {
        return [
            'trip_start_date' => $businessTrip->start_date?->toDateString(),
            'trip_end_date' => $businessTrip->end_date?->toDateString(),
        ];
    }

    private function businessTripStaffActorId(BusinessTrip $businessTrip): ?string
    {
        $userId = trim((string) ($businessTrip->employee?->user_id ?? ''));

        return $userId !== '' ? $userId : null;
    }

    private function isApprovedStatus(string $status): bool
    {
        return in_array(strtolower(trim($status)), ['approved', 'complete', 'completed', 'paid', 'done', 'success'], true);
    }

    private function isWaitingStatus(string $status): bool
    {
        return in_array(strtolower(trim($status)), ['', 'waiting'], true);
    }

    private function isPendingStatus(string $status): bool
    {
        return strtolower(trim($status)) === 'pending';
    }

    private function isRejectedStatus(string $status): bool
    {
        return in_array(strtolower(trim($status)), ['rejected', 'cancelled', 'canceled', 'failed'], true);
    }
}
