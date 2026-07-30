<?php

namespace Tests\Unit;

use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\OvertimeLifecycleLog;
use App\Models\User;
use App\Support\Attendance\OvertimeSummaryMetricBuilder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class OvertimeSummaryMetricBuilderTest extends TestCase
{
    public function test_it_calculates_overtime_summary_from_lifecycle_statuses(): void
    {
        $summary = (new OvertimeSummaryMetricBuilder)->summarizeCollection(collect([
            $this->overtime(
                employeeId: 'employee-a',
                employeeUsername: 'rico.username',
                overtimeDate: '2026-06-22',
                actualStartTime: '18:00:00',
                actualEndTime: '20:00:00',
                lifecycleStatuses: [
                    'session_ended' => 'clock_out',
                    'task_hours_verification' => 'verified',
                    'payroll_processing' => 'waiting',
                ],
                approvedStartTime: '18:00:00',
                approvedEndTime: '20:00:00',
            ),
            $this->overtime(
                employeeId: 'employee-a',
                employeeUsername: 'rico.username',
                overtimeDate: '2026-06-21',
                actualStartTime: '10:00:00',
                actualEndTime: '14:00:00',
                lifecycleStatuses: [
                    'session_ended' => 'clock_out',
                    'task_hours_verification' => 'verified',
                    'payroll_processing' => 'calculated_locked',
                ],
                approvedStartTime: '10:00:00',
                approvedEndTime: '14:00:00',
            ),
            $this->overtime(
                employeeId: 'employee-b',
                employeeUsername: 'syafiq.username',
                overtimeDate: '2026-06-23',
                actualStartTime: '18:30:00',
                actualEndTime: '21:30:00',
                lifecycleStatuses: [
                    'session_ended' => 'clock_out',
                    'task_hours_verification' => 'pending',
                    'payroll_processing' => 'waiting',
                ],
            ),
        ]));

        $this->assertSame('1 request', $summary['pending_label']);
        $this->assertSame('2 request', $summary['supervisor_approved_label']);
        $this->assertSame('1 request', $summary['director_approved_label']);
        $this->assertSame('6 hours', $summary['total_hours_label']);
        $this->assertSame('', $summary['estimated_cost_label']);
        $this->assertSame('3 hours', $summary['median_hours_label']);
        $this->assertSame('3 hours', $summary['average_hours_label']);
        $this->assertSame('rico.username', $summary['top_overtime_label']);
        $this->assertSame('4h | 2h', $summary['weekend_weekday_label']);
    }

    public function test_it_builds_conditional_metric_card_styles(): void
    {
        $metricCards = (new OvertimeSummaryMetricBuilder)->metricCards([
            'pending_label' => '0 request',
            'supervisor_approved_label' => '3 request',
            'director_approved_label' => '2 request',
            'total_hours_label' => '0h 21m',
            'estimated_cost_label' => '',
            'median_hours_label' => '0h 21m',
            'average_hours_label' => '0h 21m',
            'top_overtime_label' => 'Dimas',
            'weekend_weekday_label' => '0h | 0h 21m',
        ]);

        $this->assertCount(9, $metricCards);
        $this->assertSame([
            'label' => 'Pending',
            'value' => '0 request',
            'background_class' => 'bg-dark-subtle',
            'text_class' => 'text-black',
        ], $metricCards[0]);
        $this->assertSame('bg-success-subtle', $metricCards[1]['background_class']);
        $this->assertSame('bg-danger-subtle', $metricCards[3]['background_class']);
        $this->assertSame('Rp. 12 Jt', $metricCards[4]['value']);
        $this->assertSame('bg-secondary-subtle', $metricCards[7]['background_class']);
        $this->assertSame('bg-light-subtle', $metricCards[8]['background_class']);
    }

    public function test_it_uses_only_approved_times_for_summary_duration_metrics(): void
    {
        $summary = (new OvertimeSummaryMetricBuilder)->summarizeCollection(collect([
            $this->overtime(
                employeeId: 'employee-a',
                employeeUsername: 'dimas',
                overtimeDate: '2026-07-30',
                actualStartTime: '11:00:00',
                actualEndTime: '13:00:00',
                lifecycleStatuses: [
                    'session_ended' => 'clock_out',
                    'task_hours_verification' => 'verified',
                    'payroll_processing' => 'waiting',
                ],
                approvedStartTime: '11:30:00',
                approvedEndTime: '12:00:00',
            ),
            $this->overtime(
                employeeId: 'employee-b',
                employeeUsername: 'syafiq',
                overtimeDate: '2026-07-31',
                actualStartTime: '18:00:00',
                actualEndTime: '19:00:00',
                lifecycleStatuses: [
                    'session_ended' => 'clock_out',
                    'task_hours_verification' => 'verified',
                    'payroll_processing' => 'waiting',
                ],
                approvedStartTime: '18:00:00',
                approvedEndTime: '19:00:00',
            ),
        ]));

        $this->assertSame('1h 30m', $summary['total_hours_label']);
        $this->assertSame('0h 45m', $summary['median_hours_label']);
        $this->assertSame('0h 45m', $summary['average_hours_label']);
        $this->assertSame('syafiq', $summary['top_overtime_label']);
    }

    public function test_it_does_not_fallback_to_actual_times_for_summary_duration_metrics(): void
    {
        $summary = (new OvertimeSummaryMetricBuilder)->summarizeCollection(collect([
            $this->overtime(
                employeeId: 'employee-a',
                employeeUsername: 'dimas',
                overtimeDate: '2026-07-30',
                actualStartTime: '11:00:00',
                actualEndTime: '13:00:00',
                lifecycleStatuses: [
                    'session_ended' => 'clock_out',
                    'task_hours_verification' => 'verified',
                    'payroll_processing' => 'waiting',
                ],
            ),
        ]));

        $this->assertSame('0 hours', $summary['total_hours_label']);
        $this->assertSame('0 hours', $summary['median_hours_label']);
        $this->assertSame('0 hours', $summary['average_hours_label']);
        $this->assertSame('-', $summary['top_overtime_label']);
        $this->assertSame('0h | 0h', $summary['weekend_weekday_label']);
    }

    /**
     * @param  array<string, string>  $lifecycleStatuses
     */
    private function overtime(
        string $employeeId,
        string $employeeUsername,
        string $overtimeDate,
        string $actualStartTime,
        string $actualEndTime,
        array $lifecycleStatuses,
        ?string $approvedStartTime = null,
        ?string $approvedEndTime = null,
    ): AttendanceOvertime {
        $employee = new Employee([
            'id' => $employeeId,
        ]);
        $employee->setRelation('user', new User([
            'username' => $employeeUsername,
        ]));

        $overtime = new AttendanceOvertime([
            'id' => 'overtime-'.$employeeId.'-'.$overtimeDate,
            'employee_id' => $employeeId,
            'overtime_date' => $overtimeDate,
            'actual_start_time' => $actualStartTime,
            'actual_end_time' => $actualEndTime,
            'approved_start_time' => $approvedStartTime,
            'approved_end_time' => $approvedEndTime,
            'status' => 'completed',
        ]);
        $overtime->setRelation('employee', $employee);
        $overtime->setRelation('lifecycleLogs', $this->lifecycleLogs($lifecycleStatuses));

        return $overtime;
    }

    /**
     * @param  array<string, string>  $lifecycleStatuses
     * @return Collection<int, OvertimeLifecycleLog>
     */
    private function lifecycleLogs(array $lifecycleStatuses): Collection
    {
        return collect($lifecycleStatuses)
            ->map(fn (string $status, string $eventKey): OvertimeLifecycleLog => new OvertimeLifecycleLog([
                'event_key' => $eventKey,
                'status' => $status,
            ]))
            ->values();
    }
}
