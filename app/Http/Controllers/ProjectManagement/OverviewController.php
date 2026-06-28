<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\AttendanceHoliday;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OverviewController extends Controller
{
    public function __invoke(): View
    {
        return view('project_management.overview.index', $this->overviewData());
    }

    /**
     * @return array<string, mixed>
     */
    private function overviewData(): array
    {
        $currentDate = now('Asia/Jakarta');
        $overviewData = $this->defaultOverviewData($currentDate);
        $authenticatedUserId = Auth::id();

        $authenticatedUser = is_string($authenticatedUserId) || is_int($authenticatedUserId)
            ? User::query()
                ->select(['id'])
                ->with('employee:id,user_id')
                ->find($authenticatedUserId)
            : null;

        $employeeId = trim((string) ($authenticatedUser?->employee?->id ?? ''));
        if ($employeeId === '') {
            return $overviewData;
        }

        $taskQuery = $this->projectTaskQueryForEmployee($employeeId);
        $monthlyTaskQuery = $this->projectTaskQueryForMonth(
            $taskQuery,
            (int) $currentDate->year,
            (int) $currentDate->month,
        );
        $weeklyTaskQuery = $this->projectTaskQueryForDateRange(
            $taskQuery,
            $currentDate->copy()->startOfWeek(),
            $currentDate->copy()->endOfWeek(),
        );

        $totalTasksCount = (clone $monthlyTaskQuery)->count();
        $completedTasksCount = $this->completedTaskQuery($monthlyTaskQuery)->count();
        $inProgressTasksCount = (clone $monthlyTaskQuery)
            ->where('status', 'in_progress')
            ->whereNull('completed_at')
            ->count();
        $dailyTasksCount = (clone $monthlyTaskQuery)
            ->whereNull('project_id')
            ->count();
        $dailyTasksCompletedCount = $this->completedTaskQuery(
            (clone $monthlyTaskQuery)->whereNull('project_id'),
        )->count();
        $projectTasksCount = (clone $monthlyTaskQuery)
            ->whereNotNull('project_id')
            ->count();
        $projectTasksCompletedCount = $this->completedTaskQuery(
            (clone $monthlyTaskQuery)->whereNotNull('project_id'),
        )->count();
        $incompleteTasksCount = max(0, $totalTasksCount - $completedTasksCount);
        $weeklyTotalTasksCount = (clone $weeklyTaskQuery)->count();
        $weeklyCompletedTasksCount = $this->completedTaskQuery($weeklyTaskQuery)->count();
        $weeklyIncompleteTasksCount = max(0, $weeklyTotalTasksCount - $weeklyCompletedTasksCount);
        $taskCompletionRate = $this->percentage($completedTasksCount, $totalTasksCount);
        $monthlyOverviewChartsByMonth = $this->monthlyOverviewChartsByMonth($taskQuery, (int) $currentDate->year);
        $monthlyOverviewChartData = $monthlyOverviewChartsByMonth[$currentDate->format('Y-m')]
            ?? $this->monthlyOverviewChartData($taskQuery, $currentDate);
        $yearlyOverviewChartData = $this->yearlyOverviewChartData($taskQuery, (int) $currentDate->year);

        return array_merge($overviewData, [
            'profileMonthlyAttendanceLabels' => $this->monthlyProgressLabels((int) $currentDate->year),
            'profileMonthlyAttendanceSeries' => $this->monthlyCompletedTaskSeries($taskQuery, (int) $currentDate->year),
            'profileMonthlyAttendanceDelta' => $taskCompletionRate,
            'projectTaskCompletionRate' => $taskCompletionRate,
            'projectTasksCompletedCount' => $completedTasksCount,
            'projectTasksInProgressCount' => $inProgressTasksCount,
            'projectTasksIncompleteCount' => $incompleteTasksCount,
            'projectTotalTasksCount' => $totalTasksCount,
            'projectDailyTasksCount' => $dailyTasksCount,
            'projectDailyTasksCompletedCount' => $dailyTasksCompletedCount,
            'projectDailyTasksRate' => $this->percentage($dailyTasksCompletedCount, $dailyTasksCount),
            'projectProjectTasksCount' => $projectTasksCount,
            'projectProjectTasksCompletedCount' => $projectTasksCompletedCount,
            'projectProjectTasksRate' => $this->percentage($projectTasksCompletedCount, $projectTasksCount),
            'projectWeeklyTasksCompletedCount' => $weeklyCompletedTasksCount,
            'projectWeeklyTasksIncompleteCount' => $weeklyIncompleteTasksCount,
            'projectWeeklyTasksTotalCount' => $weeklyTotalTasksCount,
            'projectWeeklyTasksCompletedRate' => $this->percentage($weeklyCompletedTasksCount, $weeklyTotalTasksCount),
            'projectWeeklyTasksIncompleteRate' => $this->percentage($weeklyIncompleteTasksCount, $weeklyTotalTasksCount),
            'projectWorkloadPercent' => $this->percentage($completedTasksCount + $inProgressTasksCount, $totalTasksCount),
            'projectMonthlyOverviewSelectedMonth' => $currentDate->format('Y-m'),
            'projectMonthlyOverviewFilterOptions' => $this->monthlyOverviewFilterOptions((int) $currentDate->year),
            'projectMonthlyOverviewChartsByMonth' => $monthlyOverviewChartsByMonth,
            'projectMonthlyOverviewChartLabels' => $monthlyOverviewChartData['labels'],
            'projectMonthlyOverviewCompletedSeries' => $monthlyOverviewChartData['completed'],
            'projectMonthlyOverviewIncompleteSeries' => $monthlyOverviewChartData['incomplete'],
            'projectYearlyOverviewChartLabels' => $yearlyOverviewChartData['labels'],
            'projectYearlyOverviewDailySeries' => $yearlyOverviewChartData['daily'],
            'projectYearlyOverviewProjectSeries' => $yearlyOverviewChartData['project'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultOverviewData(CarbonInterface $currentDate): array
    {
        return [
            'profileMonthlyAttendanceLabels' => $this->monthlyProgressLabels((int) $currentDate->year),
            'profileMonthlyAttendanceSeries' => array_fill(0, 12, 0),
            'profileMonthlyAttendanceDelta' => 0,
            'projectCurrentMonthLabel' => $currentDate->format('F'),
            'projectCurrentMonthYearLabel' => $currentDate->format('F Y'),
            'projectCurrentYearLabel' => $currentDate->format('Y'),
            'projectTaskCompletionRate' => 0,
            'projectTasksCompletedCount' => 0,
            'projectTasksInProgressCount' => 0,
            'projectTasksIncompleteCount' => 0,
            'projectTotalTasksCount' => 0,
            'projectDailyTasksCount' => 0,
            'projectDailyTasksCompletedCount' => 0,
            'projectDailyTasksRate' => 0,
            'projectProjectTasksCount' => 0,
            'projectProjectTasksCompletedCount' => 0,
            'projectProjectTasksRate' => 0,
            'projectWeeklyTasksCompletedCount' => 0,
            'projectWeeklyTasksIncompleteCount' => 0,
            'projectWeeklyTasksTotalCount' => 0,
            'projectWeeklyTasksCompletedRate' => 0,
            'projectWeeklyTasksIncompleteRate' => 0,
            'projectWorkloadPercent' => 0,
            'projectMonthlyOverviewSelectedMonth' => $currentDate->format('Y-m'),
            'projectMonthlyOverviewFilterOptions' => $this->monthlyOverviewFilterOptions((int) $currentDate->year),
            'projectMonthlyOverviewChartsByMonth' => [],
            'projectMonthlyOverviewChartLabels' => [],
            'projectMonthlyOverviewCompletedSeries' => [],
            'projectMonthlyOverviewIncompleteSeries' => [],
            'projectYearlyOverviewChartLabels' => $this->monthlyProgressLabels((int) $currentDate->year),
            'projectYearlyOverviewDailySeries' => array_fill(0, 12, 0),
            'projectYearlyOverviewProjectSeries' => array_fill(0, 12, 0),
        ];
    }

    private function projectTaskQueryForEmployee(string $employeeId): Builder
    {
        return ProjectTask::query()
            ->where('employee_id', $employeeId)
            ->where(function (Builder $query) use ($employeeId): void {
                $query->whereNull('project_id')
                    ->orWhereHas('project.memberships', function (Builder $membershipQuery) use ($employeeId): void {
                        $membershipQuery
                            ->where('employee_id', $employeeId)
                            ->where('status', 'active')
                            ->whereNull('left_at');
                    });
            });
    }

    private function projectTaskQueryForMonth(Builder $taskQuery, int $year, int $month): Builder
    {
        return (clone $taskQuery)
            ->whereRaw('YEAR(COALESCE(due_date, start_date, created_at)) = ?', [$year])
            ->whereRaw('MONTH(COALESCE(due_date, start_date, created_at)) = ?', [$month]);
    }

    private function projectTaskQueryForDateRange(Builder $taskQuery, CarbonInterface $startDate, CarbonInterface $endDate): Builder
    {
        return (clone $taskQuery)
            ->whereRaw('DATE(COALESCE(due_date, start_date, created_at)) >= ?', [$startDate->toDateString()])
            ->whereRaw('DATE(COALESCE(due_date, start_date, created_at)) <= ?', [$endDate->toDateString()]);
    }

    private function completedTaskQuery(Builder $taskQuery): Builder
    {
        return (clone $taskQuery)
            ->where(function (Builder $query): void {
                $query->where('status', 'completed')
                    ->orWhereNotNull('completed_at');
            });
    }

    private function percentage(int $value, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }

    /**
     * @return list<string>
     */
    private function monthlyProgressLabels(int $year): array
    {
        return array_map(
            static fn (int $month): string => CarbonImmutable::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->format('F'),
            range(1, 12),
        );
    }

    /**
     * @return list<int>
     */
    private function monthlyCompletedTaskSeries(Builder $taskQuery, int $year): array
    {
        $series = array_fill(0, 12, 0);

        $this->completedTaskQuery($taskQuery)
            ->get(['due_date', 'start_date', 'created_at', 'updated_at'])
            ->each(function (ProjectTask $projectTask) use (&$series, $year): void {
                $taskDate = $this->taskOverviewDate($projectTask);

                if ($taskDate === null || (int) $taskDate->format('Y') !== $year) {
                    return;
                }

                $monthIndex = (int) $taskDate->format('n') - 1;
                if ($monthIndex >= 0 && $monthIndex <= 11) {
                    $series[$monthIndex]++;
                }
            });

        return $series;
    }

    /**
     * @return array{labels: list<string>, completed: list<int>, incomplete: list<int>}
     */
    private function monthlyOverviewChartData(Builder $taskQuery, CarbonInterface $currentDate): array
    {
        return $this->monthlyOverviewChartDataForMonth(
            $this->tasksForYear($taskQuery, (int) $currentDate->year),
            $currentDate,
        );
    }

    /**
     * @return array<string, array{labels: list<string>, completed: list<int>, incomplete: list<int>}>
     */
    private function monthlyOverviewChartsByMonth(Builder $taskQuery, int $year): array
    {
        $tasks = $this->tasksForYear($taskQuery, $year);
        $chartsByMonth = [];

        foreach (range(1, 12) as $month) {
            $monthDate = CarbonImmutable::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');
            $chartsByMonth[$monthDate->format('Y-m')] = $this->monthlyOverviewChartDataForMonth($tasks, $monthDate);
        }

        return $chartsByMonth;
    }

    /**
     * @return list<array{value:string,label:string}>
     */
    private function monthlyOverviewFilterOptions(int $year): array
    {
        return array_map(
            static function (int $month) use ($year): array {
                $monthDate = CarbonImmutable::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta');

                return [
                    'value' => $monthDate->format('Y-m'),
                    'label' => $monthDate->format('F Y'),
                ];
            },
            range(1, 12),
        );
    }

    /**
     * @param  Collection<int, ProjectTask>  $tasks
     * @return array{labels: list<string>, completed: list<int>, incomplete: list<int>}
     */
    private function monthlyOverviewChartDataForMonth(Collection $tasks, CarbonInterface $currentDate): array
    {
        $labels = [];
        $completed = [];
        $incomplete = [];
        $date = CarbonImmutable::instance($currentDate)->startOfMonth();
        $endDate = CarbonImmutable::instance($currentDate)->endOfMonth();
        $holidayDateMap = $this->holidayDateMap($date, $endDate);

        while ($date->lte($endDate)) {
            if (! $date->isWeekend() && ! isset($holidayDateMap[$date->toDateString()])) {
                $dailyTasks = $tasks->filter(function (ProjectTask $projectTask) use ($date): bool {
                    return $this->taskOverviewDate($projectTask)?->isSameDay($date) === true;
                });
                $dailyCompleted = $dailyTasks->filter(fn (ProjectTask $projectTask): bool => $this->isCompletedTask($projectTask))->count();

                $labels[] = $date->format('d-l');
                $completed[] = $dailyCompleted;
                $incomplete[] = max(0, $dailyTasks->count() - $dailyCompleted);
            }

            $date = $date->addDay();
        }

        return [
            'labels' => $labels,
            'completed' => $completed,
            'incomplete' => $incomplete,
        ];
    }

    /**
     * @return array{labels: list<string>, daily: list<int>, project: list<int>}
     */
    private function yearlyOverviewChartData(Builder $taskQuery, int $year): array
    {
        $dailySeries = array_fill(0, 12, 0);
        $projectSeries = array_fill(0, 12, 0);

        $this->tasksForYear($taskQuery, $year)
            ->each(function (ProjectTask $projectTask) use (&$dailySeries, &$projectSeries): void {
                $taskDate = $this->taskOverviewDate($projectTask);

                if ($taskDate === null) {
                    return;
                }

                $monthIndex = (int) $taskDate->format('n') - 1;

                if ($projectTask->project_id === null) {
                    $dailySeries[$monthIndex]++;

                    return;
                }

                $projectSeries[$monthIndex]++;
            });

        return [
            'labels' => $this->monthlyProgressLabels($year),
            'daily' => $dailySeries,
            'project' => $projectSeries,
        ];
    }

    /**
     * @return Collection<int, ProjectTask>
     */
    private function tasksForYear(Builder $taskQuery, int $year): Collection
    {
        return (clone $taskQuery)
            ->whereRaw('YEAR(COALESCE(due_date, start_date, created_at)) = ?', [$year])
            ->get(['id', 'project_id', 'status', 'due_date', 'start_date', 'completed_at', 'created_at', 'updated_at']);
    }

    /**
     * @return array<string, bool>
     */
    private function holidayDateMap(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $holidayDates = AttendanceHoliday::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('date')
            ->map(static function (mixed $holidayDate): ?string {
                if ($holidayDate instanceof \DateTimeInterface) {
                    return CarbonImmutable::instance($holidayDate)->toDateString();
                }

                if (is_string($holidayDate) && trim($holidayDate) !== '') {
                    return trim($holidayDate);
                }

                return null;
            })
            ->filter(static fn (mixed $holidayDate): bool => is_string($holidayDate) && $holidayDate !== '')
            ->values()
            ->all();

        return array_fill_keys($holidayDates, true);
    }

    private function taskOverviewDate(ProjectTask $projectTask): ?CarbonInterface
    {
        return $projectTask->due_date
            ?? $projectTask->start_date
            ?? $projectTask->created_at
            ?? $projectTask->updated_at;
    }

    private function isCompletedTask(ProjectTask $projectTask): bool
    {
        return $projectTask->status === 'completed' || $projectTask->completed_at !== null;
    }
}
