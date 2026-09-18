<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\OvertimeLifecycleLog;
use App\Models\User;
use Illuminate\Support\Carbon;

class TwelveHourAutoOvertimeService
{
    private const THRESHOLD_MINUTES = 720;

    private const OVERTIME_STATUS_COMPLETED = 'completed';

    private const OVERTIME_INSTRUCTION = 'Auto overtime from attendance after 12 working hours.';

    private const OVERTIME_LIFECYCLE_STEPS = [
        [
            'phase' => 'assignment_request',
            'event_key' => 'assignment_submitted',
            'step_order' => 1,
            'title' => 'Overtime Assignment Submitted',
            'status' => 'complete',
        ],
        [
            'phase' => 'execution_time_tracking',
            'event_key' => 'session_started',
            'step_order' => 2,
            'title' => 'Overtime Session Started',
            'status' => 'clock_in',
        ],
        [
            'phase' => 'execution_time_tracking',
            'event_key' => 'task_deliverables_submitted',
            'step_order' => 3,
            'title' => 'Task & Deliverables Submitted',
            'status' => 'complete',
        ],
        [
            'phase' => 'execution_time_tracking',
            'event_key' => 'session_ended',
            'step_order' => 4,
            'title' => 'Overtime Session Ended',
            'status' => 'clock_out',
        ],
        [
            'phase' => 'review_approval',
            'event_key' => 'task_hours_verification',
            'step_order' => 5,
            'title' => 'Task & Hours Verification',
            'status' => 'pending',
        ],
        [
            'phase' => 'payroll_payment',
            'event_key' => 'payroll_processing',
            'step_order' => 6,
            'title' => 'HR / Payroll Processing',
            'status' => 'waiting',
        ],
        [
            'phase' => 'payroll_payment',
            'event_key' => 'director_approval',
            'step_order' => 7,
            'title' => 'Director Approval',
            'status' => 'waiting',
        ],
        [
            'phase' => 'payroll_payment',
            'event_key' => 'payment_disbursement',
            'step_order' => 8,
            'title' => 'Payment Disbursement',
            'status' => 'waiting',
        ],
    ];

    public function createFromClockOut(Attendance $attendance, Carbon $clockInTime, Carbon $clockOutTime, ?User $actor): ?AttendanceOvertime
    {
        $attendance->loadMissing(
            'employee.user',
            'employee.picAssignment.supervisor.user',
            'employee.deployment.position',
            'employee.deployment.positions'
        );
        $employee = $attendance->employee;

        if (! $employee instanceof Employee || ! $employee->isEligibleForTwelveHourAutoOvertime()) {
            return null;
        }

        $overtimeWindow = $this->overtimeWindow($clockInTime, $clockOutTime);
        if ($overtimeWindow === null) {
            return null;
        }

        $actualStartTime = $overtimeWindow['actual_start_at']->format('H:i:s');
        $actualEndTime = $overtimeWindow['actual_end_at']->format('H:i:s');
        $overtimeDate = $overtimeWindow['actual_start_at']->toDateString();
        $actorUser = $this->resolveSupervisorUser($employee) ?? ($actor instanceof User ? $actor : $employee->user);
        $executionActorUser = $employee->user instanceof User ? $employee->user : ($actor instanceof User ? $actor : $actorUser);

        $existingOvertime = AttendanceOvertime::query()
            ->where('employee_id', $employee->id)
            ->whereDate('overtime_date', $overtimeDate)
            ->where('actual_start_time', $actualStartTime)
            ->where('actual_end_time', $actualEndTime)
            ->where('instruction', self::OVERTIME_INSTRUCTION)
            ->first();

        if ($existingOvertime instanceof AttendanceOvertime) {
            return $existingOvertime;
        }

        $overtime = AttendanceOvertime::query()->create([
            'employee_id' => $employee->id,
            'assigned_by' => $actorUser?->id,
            'overtime_date' => $overtimeDate,
            'instruction' => self::OVERTIME_INSTRUCTION,
            'actual_start_time' => $actualStartTime,
            'actual_end_time' => $actualEndTime,
            'calculated_hours' => round($overtimeWindow['overtime_minutes'] / 60, 2),
            'status' => self::OVERTIME_STATUS_COMPLETED,
        ]);

        $this->createLifecycleLogs($overtime, $overtimeWindow['actual_start_at'], $overtimeWindow['actual_end_at'], $actorUser, $executionActorUser);

        return $overtime;
    }

    private function resolveSupervisorUser(Employee $employee): ?User
    {
        $employee->loadMissing('picAssignment.supervisor.user');

        $supervisorUser = $employee->picAssignment?->supervisor?->user;
        if (! $supervisorUser instanceof User) {
            return null;
        }

        return is_string($supervisorUser->id) && trim($supervisorUser->id) !== ''
            ? $supervisorUser
            : null;
    }

    /**
     * @return array{actual_start_at: Carbon, actual_end_at: Carbon, overtime_minutes: int}|null
     */
    public function overtimeWindow(Carbon $clockInTime, Carbon $clockOutTime): ?array
    {
        $workedMinutes = (int) $clockInTime->diffInMinutes($clockOutTime, false);
        if ($workedMinutes <= self::THRESHOLD_MINUTES) {
            return null;
        }

        $actualStartAt = $clockInTime->copy()->addMinutes(self::THRESHOLD_MINUTES);
        $actualEndAt = $clockOutTime->copy();

        return [
            'actual_start_at' => $actualStartAt,
            'actual_end_at' => $actualEndAt,
            'overtime_minutes' => (int) $actualStartAt->diffInMinutes($actualEndAt, false),
        ];
    }

    private function createLifecycleLogs(AttendanceOvertime $overtime, Carbon $actualStartAt, Carbon $actualEndAt, ?User $assignmentActor, ?User $executionActor): void
    {
        foreach (self::OVERTIME_LIFECYCLE_STEPS as $lifecycleStep) {
            $eventKey = $lifecycleStep['event_key'];
            $happenedAt = match ($eventKey) {
                'assignment_submitted' => $actualEndAt,
                'session_started' => $actualStartAt,
                'task_deliverables_submitted', 'session_ended' => $actualEndAt,
                default => null,
            };
            $actor = $eventKey === 'assignment_submitted' ? $assignmentActor : $executionActor;

            OvertimeLifecycleLog::query()->updateOrCreate(
                [
                    'overtime_id' => $overtime->id,
                    'event_key' => $eventKey,
                ],
                [
                    'phase' => $lifecycleStep['phase'],
                    'step_order' => $lifecycleStep['step_order'],
                    'title' => $lifecycleStep['title'],
                    'status' => $lifecycleStep['status'],
                    'actor_id' => $happenedAt instanceof Carbon ? $actor?->id : null,
                    'happened_at' => $happenedAt,
                    'metadata' => [
                        'overtime_status' => $overtime->status,
                        'actual_start_time' => $actualStartAt->format('H:i:s'),
                        'actual_end_time' => $actualEndAt->format('H:i:s'),
                        'source' => 'attendance_12_hour_auto_overtime',
                    ],
                ]
            );
        }
    }
}
