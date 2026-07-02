<?php

namespace Tests\Unit;

use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\EmployeeProfile;
use App\Models\OvertimeLifecycleLog;
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
                employeeName: 'Rico',
                overtimeDate: '2026-06-22',
                actualStartTime: '18:00:00',
                actualEndTime: '20:00:00',
                lifecycleStatuses: [
                    'session_ended' => 'clock_out',
                    'task_hours_verification' => 'verified',
                    'payroll_processing' => 'waiting',
                ],
            ),
            $this->overtime(
                employeeId: 'employee-a',
                employeeName: 'Rico',
                overtimeDate: '2026-06-21',
                actualStartTime: '10:00:00',
                actualEndTime: '14:00:00',
                lifecycleStatuses: [
                    'session_ended' => 'clock_out',
                    'task_hours_verification' => 'verified',
                    'payroll_processing' => 'calculated_locked',
                ],
            ),
            $this->overtime(
                employeeId: 'employee-b',
                employeeName: 'Syafiq',
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
        $this->assertSame('Rico', $summary['top_overtime_label']);
        $this->assertSame('4h | 2h', $summary['weekend_weekday_label']);
    }

    /**
     * @param  array<string, string>  $lifecycleStatuses
     */
    private function overtime(
        string $employeeId,
        string $employeeName,
        string $overtimeDate,
        string $actualStartTime,
        string $actualEndTime,
        array $lifecycleStatuses
    ): AttendanceOvertime {
        $employee = new Employee([
            'id' => $employeeId,
        ]);
        $employee->setRelation('profile', new EmployeeProfile([
            'employee_id' => $employeeId,
            'name' => $employeeName,
        ]));

        $overtime = new AttendanceOvertime([
            'id' => 'overtime-'.$employeeId.'-'.$overtimeDate,
            'employee_id' => $employeeId,
            'overtime_date' => $overtimeDate,
            'actual_start_time' => $actualStartTime,
            'actual_end_time' => $actualEndTime,
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
