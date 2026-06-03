<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LeaveHistoryYearFilterTest extends TestCase
{
    public function test_leave_history_year_filter_is_removed_from_view_and_controller(): void
    {
        $leaveRequestView = File::get(resource_path('views/attendance/leave-requests/index.blade.php'));
        $leaveRequestController = File::get(app_path('Http/Controllers/LeaveRequestController.php'));

        $this->assertStringNotContainsString('action="{{ route(\'attendance.leave-requests\') }}"', $leaveRequestView);
        $this->assertStringNotContainsString('name="history_year"', $leaveRequestView);
        $this->assertStringNotContainsString('leaveHistoryYearFilter', $leaveRequestView);

        $this->assertStringNotContainsString('history_year', $leaveRequestController);
        $this->assertStringNotContainsString('selectedLeaveHistoryYear', $leaveRequestController);
        $this->assertStringNotContainsString('buildLeaveHistoryYearOptions', $leaveRequestController);
        $this->assertStringNotContainsString('resolveLeaveHistoryYearFilter', $leaveRequestController);
    }

    public function test_leave_summary_is_split_into_eligibility_and_tracker_data(): void
    {
        $leaveRequestView = File::get(resource_path('views/attendance/leave-requests/index.blade.php'));
        $leaveRequestController = File::get(app_path('Http/Controllers/LeaveRequestController.php'));

        foreach ([
            'href="#Eligibility"',
            'href="#Tracker"',
            'id="Eligibility"',
            'id="Tracker"',
            'row leave-balance-mobile-slider',
            'leave-balance-mobile-slide',
            'class="col-12 col-md-12 mb-3" id="leaveTypeWrapper"',
            'var $leaveTypeWrapper = $(\'#leaveTypeWrapper\');',
            '$leaveTypeWrapper.removeClass(\'col-md-12\').addClass(\'col-md-6\');',
            '$leaveTypeWrapper.removeClass(\'col-md-6\').addClass(\'col-md-12\');',
            '$leaveSummaryYear = (int) ($leaveTracker[\'year\'] ?? now(\'Asia/Jakarta\')->year);',
            'Leave Balance ({{ $leaveSummaryYear }})',
            'Joint Holiday ({{ $leaveSummaryYear }})',
            'Annual Leave ({{ $leaveSummaryYear }})',
            'Sick Leave ({{ $leaveSummaryYear }})',
            'Special Leave ({{ $leaveSummaryYear }})',
            'Unpaid Leave ({{ $leaveSummaryYear }})',
            '$leaveEligibility[\'available_balance_label\']',
            'leave-summary-card-header',
            'nav nav-underline leave-summary-tabs',
            '$leaveEligibility[\'joint_holiday_label\']',
            '$leaveEligibility[\'joint_holiday_items\']',
            '@forelse (($leaveEligibility[\'joint_holiday_items\'] ?? []) as $jointHolidayItem)',
            '$leaveTracker[\'annual_leave_taken_label\']',
            '$leaveTracker[\'sick_leave_taken_label\']',
            '$leaveTracker[\'special_leave_taken_label\']',
            '$leaveTracker[\'unpaid_leave_taken_label\']',
            '$leaveTracker[\'pending_requests_label\']',
            '$leaveTracker[\'approved_requests_label\']',
            '$leaveTracker[\'rejected_requests_label\']',
        ] as $expectedViewFragment) {
            $this->assertStringContainsString($expectedViewFragment, $leaveRequestView);
        }

        foreach ([
            'use App\Models\AttendanceHoliday;',
            '\'leaveTracker\' => $this->buildLeaveTrackerData($authenticatedUser),',
            'private function buildLeaveTrackerData(?User $authenticatedUser): array',
            'private function buildJointHolidaySummary(int $year, Carbon $today): array',
            '->where(\'type\', 2)',
            '$remainingDays = max($totalDays - $passedDays, 0);',
            '\'label\' => $remainingDays.\' / \'.$totalDays.\' \'.Str::plural(\'Day\', $totalDays),',
            'private function buildLeaveTypeUsageSummary(',
            'private function countStaffLeaveRequestsByStatus(',
        ] as $expectedControllerFragment) {
            $this->assertStringContainsString($expectedControllerFragment, $leaveRequestController);
        }

        $this->assertSame(6, substr_count($leaveRequestView, 'col-md-2 col-sm-6 leave-balance-mobile-slide'));

        foreach ([
            'Leave Balance (2026)',
            'Joint Holiday (2026)',
            'Annual Leave (2026)',
            'Sick Leave (2026)',
            'Special Leave (2026)',
            'Unpaid Leave (2026)',
            '6 / 8 days',
            'Days Passed',
            'Thomas Jefferson',
            'Michael Scott',
            '15 March 2025',
            '1 Year, 2 Months',
            '16 May',
            'Annual Leave Taken This Month (May)',
            'Unpaid Leave Taken This Month(May)',
            'leave_used_label',
            'leave_taken_month_value_label',
            'joint_holiday_breakdown',
        ] as $removedFragment) {
            $this->assertStringNotContainsString($removedFragment, $leaveRequestView.$leaveRequestController);
        }
    }
}
