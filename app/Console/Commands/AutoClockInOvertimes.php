<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceOvertime;
use App\Models\OvertimeLifecycleLog;
use DateTimeInterface;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

#[Signature('overtimes:auto-clock-in {--dry-run : Show eligible overtime sessions without updating them}')]
#[Description('Automatically clock in eligible overtime sessions at the scheduled start time')]
class AutoClockInOvertimes extends Command
{
    private const STATUS_IN_PROGRESS = 'in_progress';

    private const STATUS_COMPLETED = 'completed';

    private const STATUS_CANCELLED = 'cancelled';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now('Asia/Jakarta');
        $dryRun = (bool) $this->option('dry-run');
        $candidateCount = 0;
        $autoClockedInCount = 0;
        $skippedWithoutAttendanceCount = 0;

        AttendanceOvertime::query()
            ->whereNull('actual_start_time')
            ->where(function ($query): void {
                $query
                    ->whereNull('status')
                    ->orWhereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
            })
            ->whereBetween('overtime_date', [
                $now->copy()->subDay()->toDateString(),
                $now->toDateString(),
            ])
            ->orderBy('overtime_date')
            ->orderBy('planned_start_time')
            ->get()
            ->each(function (AttendanceOvertime $overtime) use ($now, $dryRun, &$candidateCount, &$autoClockedInCount, &$skippedWithoutAttendanceCount): void {
                $scheduledStartAt = $this->scheduledStartAt($overtime);

                if (! $scheduledStartAt instanceof Carbon || $now->lt($scheduledStartAt)) {
                    return;
                }

                $candidateCount++;

                if (! $this->hasAttendanceCheckIn($overtime)) {
                    $skippedWithoutAttendanceCount++;

                    return;
                }

                if ($this->autoClockIn($overtime, $scheduledStartAt, $dryRun)) {
                    $autoClockedInCount++;
                }
            });

        $this->info(sprintf(
            'Overtime auto clock-in finished. Candidates: %d, %s: %d, skipped without attendance: %d.',
            $candidateCount,
            $dryRun ? 'eligible' : 'auto clocked-in',
            $autoClockedInCount,
            $skippedWithoutAttendanceCount
        ));

        return self::SUCCESS;
    }

    private function scheduledStartAt(AttendanceOvertime $overtime): ?Carbon
    {
        $plannedStartTime = $this->normalizeTimeValue($overtime->planned_start_time);

        if ($plannedStartTime === null) {
            return null;
        }

        try {
            $overtimeDate = $overtime->overtime_date instanceof DateTimeInterface
                ? Carbon::instance($overtime->overtime_date)->timezone('Asia/Jakarta')->toDateString()
                : Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->toDateString();

            return Carbon::createFromFormat('Y-m-d H:i:s', $overtimeDate.' '.$plannedStartTime, 'Asia/Jakarta');
        } catch (\Throwable) {
            return null;
        }
    }

    private function hasAttendanceCheckIn(AttendanceOvertime $overtime): bool
    {
        if (! is_string($overtime->employee_id) || trim($overtime->employee_id) === '') {
            return false;
        }

        $overtimeDate = $this->overtimeDate($overtime);

        if ($overtimeDate === null) {
            return false;
        }

        return Attendance::query()
            ->where('employee_id', $overtime->employee_id)
            ->whereDate('date', $overtimeDate)
            ->whereNotNull('clock_in')
            ->exists();
    }

    private function overtimeDate(AttendanceOvertime $overtime): ?string
    {
        try {
            return $overtime->overtime_date instanceof DateTimeInterface
                ? Carbon::instance($overtime->overtime_date)->timezone('Asia/Jakarta')->toDateString()
                : Carbon::parse($overtime->overtime_date, 'Asia/Jakarta')->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function autoClockIn(AttendanceOvertime $overtime, Carbon $scheduledStartAt, bool $dryRun): bool
    {
        if ($dryRun) {
            return true;
        }

        return DB::transaction(function () use ($overtime, $scheduledStartAt): bool {
            $lockedOvertime = AttendanceOvertime::query()
                ->whereKey($overtime->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOvertime instanceof AttendanceOvertime
                || $lockedOvertime->actual_start_time !== null
                || in_array((string) $lockedOvertime->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true)) {
                return false;
            }

            $actualStartTime = $this->normalizeTimeValue($lockedOvertime->planned_start_time);

            if ($actualStartTime === null) {
                return false;
            }

            $lockedOvertime->update([
                'actual_start_time' => $actualStartTime,
                'status' => self::STATUS_IN_PROGRESS,
            ]);

            $this->updateLifecycleLog($lockedOvertime, 'session_started', [
                'phase' => 'execution_time_tracking',
                'step_order' => 2,
                'title' => 'Overtime Session Started',
                'status' => 'clock_in',
                'actor_id' => null,
                'happened_at' => $scheduledStartAt,
                'metadata' => $this->lifecycleMetadata($lockedOvertime, true),
            ]);

            $this->updateLifecycleLog($lockedOvertime, 'task_deliverables_submitted', [
                'phase' => 'execution_time_tracking',
                'step_order' => 3,
                'title' => 'Task & Deliverables Submitted',
                'status' => 'pending',
                'actor_id' => null,
                'happened_at' => null,
                'metadata' => $this->lifecycleMetadata($lockedOvertime, true),
            ], ['waiting', 'pending']);

            $this->updateLifecycleLog($lockedOvertime, 'session_ended', [
                'phase' => 'execution_time_tracking',
                'step_order' => 4,
                'title' => 'Overtime Session Ended',
                'status' => 'waiting',
                'actor_id' => null,
                'happened_at' => null,
                'metadata' => $this->lifecycleMetadata($lockedOvertime, true),
            ], ['waiting']);

            return true;
        });
    }

    /**
     * @param  array{phase:string,step_order:int,title:string,status:string,actor_id:?string,happened_at:?Carbon,metadata:array<string, mixed>}  $values
     * @param  list<string>|null  $onlyWhenCurrentStatusIn
     */
    private function updateLifecycleLog(AttendanceOvertime $overtime, string $eventKey, array $values, ?array $onlyWhenCurrentStatusIn = null): void
    {
        $existingLifecycleLog = OvertimeLifecycleLog::query()
            ->where('overtime_id', $overtime->id)
            ->where('event_key', $eventKey)
            ->first();

        if ($existingLifecycleLog instanceof OvertimeLifecycleLog && is_array($onlyWhenCurrentStatusIn)) {
            $currentStatus = strtolower(trim((string) $existingLifecycleLog->status));

            if (! in_array($currentStatus, $onlyWhenCurrentStatusIn, true)) {
                return;
            }
        }

        OvertimeLifecycleLog::query()->updateOrCreate(
            [
                'overtime_id' => $overtime->id,
                'event_key' => $eventKey,
            ],
            $values
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function lifecycleMetadata(AttendanceOvertime $overtime, bool $autoClockIn): array
    {
        return [
            'overtime_status' => $overtime->status,
            'actual_start_time' => $this->normalizeTimeValue($overtime->actual_start_time),
            'actual_end_time' => $this->normalizeTimeValue($overtime->actual_end_time),
            'auto_clock_in' => $autoClockIn,
        ];
    }

    private function normalizeTimeValue(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->format('H:i:s');
        }

        if (! is_string($value)) {
            return null;
        }

        $trimmedValue = trim($value);

        if ($trimmedValue === '') {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $trimmedValue) === 1) {
            return $trimmedValue.':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $trimmedValue) === 1) {
            return $trimmedValue;
        }

        return null;
    }
}
