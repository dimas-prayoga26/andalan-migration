<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LeaveHistoryYearFilterTest extends TestCase
{
    public function test_leave_history_year_filter_is_removed_from_view_and_controller(): void
    {
        $leaveRequestView = File::get(resource_path('views/attendance/leave-requests/index.blade.php'));
        $leaveHistoryListCardsPartial = File::get(resource_path('views/attendance/leave-requests/partials/history-list-cards.blade.php'));
        $leaveRequestController = File::get(app_path('Http/Controllers/LeaveRequestController.php'));

        $this->assertFileDoesNotExist(resource_path('views/attendance/leave-requests/partials/history-cards.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/attendance/leave-requests/partials/request-cards.blade.php'));
        $this->assertStringNotContainsString('action="{{ route(\'attendance.leave-requests\') }}"', $leaveRequestView);
        $this->assertStringNotContainsString('name="history_year"', $leaveRequestView);
        $this->assertStringNotContainsString('leaveHistoryYearFilter', $leaveRequestView);

        $this->assertStringNotContainsString('history_year', $leaveRequestController);
        $this->assertStringNotContainsString('selectedLeaveHistoryYear', $leaveRequestController);
        $this->assertStringNotContainsString('buildLeaveHistoryYearOptions', $leaveRequestController);
        $this->assertStringNotContainsString('resolveLeaveHistoryYearFilter', $leaveRequestController);
        $this->assertStringContainsString('id="leaveHistoryCardsContainer"', $leaveRequestView);
        $this->assertStringContainsString('leaveHistoryCardsUrl = @json(route(\'attendance.leave-requests.cards\'))', $leaveRequestView);
        $this->assertStringContainsString('id="leaveHistoryFilterForm"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveHistoryStatusFilter" name="status"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveHistoryTypeFilter" name="leave_type"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveHistoryTimeframeFilter" name="timeframe"', $leaveRequestView);
        $this->assertStringContainsString('function refreshLeaveHistoryCards()', $leaveRequestView);
        $this->assertStringContainsString('refreshLeaveHistoryCards();', $leaveRequestView);
        $this->assertStringContainsString('function hideLeaveHistoryFilterModal()', $leaveRequestView);
        $this->assertStringContainsString('public function cards(Request $request): JsonResponse', $leaveRequestController);
        $this->assertStringContainsString('private function resolveLeaveHistoryFilters(Request $request): array', $leaveRequestController);
        $this->assertStringContainsString('private function applyLeaveHistoryStatusFilter(Builder $query, string $status): void', $leaveRequestController);
        $this->assertStringContainsString('private function applyLeaveHistoryTypeFilter(Builder $query, string $leaveType): void', $leaveRequestController);
        $this->assertStringContainsString('private function applyLeaveHistoryTimeframeFilter(Builder $query, string $timeframe): void', $leaveRequestController);
        $this->assertStringContainsString('\'cards\' => $leaveHistoryCards->values(),', $leaveRequestController);
        $this->assertStringContainsString('function renderLeaveHistoryCards(cards)', $leaveRequestView);
        $this->assertStringContainsString('function leaveHistoryCardHtml(card)', $leaveRequestView);
        $this->assertStringContainsString('Array.isArray(response.cards)', $leaveRequestView);
        $this->assertStringNotContainsString('attendance.leave-requests.partials.request-cards', $leaveRequestController);
        $this->assertStringContainsString("@include('attendance.leave-requests.partials.history-list-cards'", $leaveRequestView);
        $this->assertStringNotContainsString("@include('attendance.leave-requests.partials.history-cards'", $leaveRequestView);
        $this->assertStringNotContainsString("@include('attendance.leave-requests.partials.request-cards'", $leaveRequestView);
        $this->assertStringNotContainsString("@include('attendance.leave-requests.partials.balance-cards'", $leaveRequestView);
        $this->assertStringContainsString('row leave-balance-mobile-slider', $leaveHistoryListCardsPartial);
        $this->assertStringNotContainsString('id="leaveHistoryCardsSlider"', $leaveHistoryListCardsPartial);
        $this->assertStringContainsString('id="leaveHistoryCardsSlider"', $leaveRequestView);
        $this->assertStringContainsString('class="card leave-history-detail-trigger" role="button" tabindex="0" data-bs-toggle="modal" data-bs-target="#leaveHistoryDetailModal"', $leaveRequestView);
        $this->assertStringContainsString("data-detail-title=\"{{ \$leaveHistoryCard['title'] ?? 'Leave Request' }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-modal-title=\"{{ \$leaveHistoryCard['modal_title'] ?? (\$leaveHistoryCard['title'] ?? 'Leave Request') }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-leave-type=\"{{ \$leaveHistoryCard['detail_leave_type'] ?? (\$leaveHistoryCard['title'] ?? 'Leave Request') }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-is-sick=\"{{ ! empty(\$leaveHistoryCard['is_sick_leave']) ? 'true' : 'false' }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-attachment-url=\"{{ \$leaveHistoryCard['attachment_url'] ?? '' }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-timeline='@json(\$leaveHistoryCard['timeline'] ?? [])'", $leaveRequestView);
        $this->assertStringContainsString("asset('assets/'.(\$leaveHistoryCard['icon_file'] ?? 'annual_leave.svg'))", $leaveRequestView);
        $this->assertStringContainsString('data-detail-title="\' + escapeHtml(title) + \'"', $leaveRequestView);
        $this->assertStringContainsString('data-detail-modal-title="\' + escapeHtml(modalTitle) + \'"', $leaveRequestView);
        $this->assertStringContainsString('data-detail-is-sick="\' + isSickLeave + \'"', $leaveRequestView);
        $this->assertStringContainsString('data-detail-attachment-url="\' + escapeHtml(attachmentUrl) + \'"', $leaveRequestView);
        $this->assertStringContainsString('data-detail-timeline="\' + timelineAttribute + \'"', $leaveRequestView);
        $this->assertStringContainsString("var iconFile = card.icon_file || 'annual_leave.svg';", $leaveRequestView);
        $this->assertStringContainsString("var leaveTypeIconBaseUrl = @json(asset('assets'));", $leaveRequestView);
        $this->assertStringContainsString("var medicalNotesUnavailableUrl = @json(asset('assets/not_available_images.png'));", $leaveRequestView);
        $this->assertStringContainsString("asset('assets/not_available_images.png')", $leaveRequestView);
        $this->assertStringContainsString('attachmentUrl || medicalNotesUnavailableUrl', $leaveRequestView);
        $this->assertStringContainsString('target="_blank" rel="noopener" id="leaveHistoryDetailMedicalNotesLink"', $leaveRequestView);
        $this->assertStringContainsString("$('#leaveHistoryDetailMedicalNotesLink').attr('href', attachmentUrl || medicalNotesUnavailableUrl);", $leaveRequestView);
        $this->assertStringNotContainsString('assets/images/logo/figma.avif', $leaveRequestView);
    }

    public function test_leave_summary_is_split_into_eligibility_and_tracker_data(): void
    {
        $leaveRequestView = File::get(resource_path('views/attendance/leave-requests/index.blade.php'));
        $leaveHistoryListCardsPartial = File::get(resource_path('views/attendance/leave-requests/partials/history-list-cards.blade.php'));
        $leaveRequestController = File::get(app_path('Http/Controllers/LeaveRequestController.php'));
        $leaveTypeSeeder = File::get(database_path('seeders/LeaveTypeSeeder.php'));

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
            'id="leaveHistoryDetailModal"',
            'id="leaveHistoryDetailIntroTitle"',
            'id="leaveHistoryDetailIntroText"',
            'id="leaveHistoryDetailType"',
            'id="leaveHistoryDetailPeriod"',
            'id="leaveHistoryDetailReason"',
            'id="leaveHistoryDetailStatusText"',
            'id="leaveHistoryDetailStatusDate"',
            'id="leaveHistoryDetailMedicalNotesRow"',
            'id="leaveHistoryDetailMedicalNotesLink"',
            'id="leaveHistoryDetailMedicalNotesImage"',
            'function fillLeaveHistoryDetailModal($card)',
            'Out of Office mode: ON',
            'Your health comes first',
            "$(document).on('click', '.leave-history-detail-trigger', function () {",
            '$leaveTracker[\'annual_leave_taken_breakdown\']',
            '$leaveTracker[\'annual_leave_taken_month_label\']',
            '$leaveTracker[\'annual_leave_taken_month_breakdown\']',
            '$leaveTracker[\'annual_leave_monthly_limit_label\']',
            '$leaveTracker[\'sick_leave_taken_breakdown\']',
            '$leaveTracker[\'sick_leave_taken_month_label\']',
            '$leaveTracker[\'sick_leave_taken_month_breakdown\']',
            '$leaveTracker[\'pending_requests_label\']',
            '$leaveTracker[\'approved_requests_label\']',
            '$leaveTracker[\'rejected_requests_label\']',
        ] as $expectedViewFragment) {
            $this->assertStringContainsString($expectedViewFragment, $leaveRequestView.$leaveHistoryListCardsPartial);
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
            'status_date_label',
            'attachment_url',
            'icon_file',
            'private function resolveLeaveHistoryIconFile(string $leaveTypeCode, string $leaveTypeName): string',
            'annual_leave.svg',
            'sick_leave.svg',
            'special_leave.svg',
            'unpaid_leave.svg',
            'private function resolveLeaveStatusDateLabel(LeaveRequest $leaveRequest, string $status, Carbon $fallbackDate): string',
        ] as $expectedControllerFragment) {
            $this->assertStringContainsString($expectedControllerFragment, $leaveRequestController);
        }

        $this->assertStringContainsString("'code' => 'UNPAID'", $leaveTypeSeeder);
        $this->assertStringContainsString("'name' => 'Unpaid Leave'", $leaveTypeSeeder);

        $this->assertSame(6, substr_count($leaveHistoryListCardsPartial, 'col-md-2 col-sm-6 leave-balance-mobile-slide'));

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
            'leave-summary-detail-trigger',
            'data-bs-target="#annualLeave"',
            'data-bs-target="#sick"',
            'Demam dan Flu',
            'Liburan keluarga dan istirahat sejenak',
            '18 May 2026 - 19 May 2026 (2 days)',
            'ecom-product-detail.html',
        ] as $removedFragment) {
            $this->assertStringNotContainsString($removedFragment, $leaveRequestView.$leaveRequestController);
        }
    }
}
