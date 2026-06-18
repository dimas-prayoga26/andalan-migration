<?php

namespace Tests\Feature;

use App\Http\Controllers\AttendanceLeaveRequestController;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;

class LeaveHistoryYearFilterTest extends TestCase
{
    public function test_leave_timeline_marker_color_marks_passed_and_current_steps(): void
    {
        $method = new ReflectionMethod(AttendanceLeaveRequestController::class, 'buildFixedLeaveTimelineRows');
        $method->setAccessible(true);
        $controller = app(AttendanceLeaveRequestController::class);
        $fallbackDate = Carbon::parse('2026-06-15', 'Asia/Jakarta');

        $submittedOnlyRows = $method->invoke(
            $controller,
            $this->leaveRequestWithHistories([
                new LeaveRequestHistory([
                    'event_type' => 'submitted',
                    'title' => 'Request Submitted',
                    'to_status' => 'pending',
                    'happened_at' => Carbon::parse('2026-06-15 08:00:00', 'Asia/Jakarta'),
                ]),
            ]),
            'pending',
            $fallbackDate
        );
        $this->assertSame('border-success', $submittedOnlyRows[0]['badge_class']);
        $this->assertSame('border-warning', $submittedOnlyRows[1]['badge_class']);
        $this->assertSame('border-dark', $submittedOnlyRows[2]['badge_class']);

        $supervisorPendingRows = $method->invoke(
            $controller,
            $this->leaveRequestWithHistories([
                new LeaveRequestHistory([
                    'event_type' => 'submitted',
                    'title' => 'Request Submitted',
                    'to_status' => 'pending',
                    'happened_at' => Carbon::parse('2026-06-15 08:00:00', 'Asia/Jakarta'),
                ]),
                new LeaveRequestHistory([
                    'event_type' => 'supervisor_review',
                    'title' => 'Supervisor Review',
                    'to_status' => 'pending',
                    'happened_at' => Carbon::parse('2026-06-15 09:00:00', 'Asia/Jakarta'),
                ]),
            ]),
            'pending',
            $fallbackDate
        );
        $this->assertSame('border-success', $supervisorPendingRows[0]['badge_class']);
        $this->assertSame('border-warning', $supervisorPendingRows[1]['badge_class']);
        $this->assertSame('border-dark', $supervisorPendingRows[2]['badge_class']);

        $hrWaitingRows = $method->invoke(
            $controller,
            $this->leaveRequestWithHistories([
                new LeaveRequestHistory([
                    'event_type' => 'submitted',
                    'title' => 'Request Submitted',
                    'to_status' => 'pending',
                    'happened_at' => Carbon::parse('2026-06-15 08:00:00', 'Asia/Jakarta'),
                ]),
                new LeaveRequestHistory([
                    'event_type' => 'supervisor_review',
                    'title' => 'Supervisor Review',
                    'to_status' => 'complete',
                    'happened_at' => Carbon::parse('2026-06-15 09:00:00', 'Asia/Jakarta'),
                ]),
            ]),
            'pending',
            $fallbackDate
        );
        $this->assertSame('border-success', $hrWaitingRows[0]['badge_class']);
        $this->assertSame('border-success', $hrWaitingRows[1]['badge_class']);
        $this->assertSame('border-warning', $hrWaitingRows[2]['badge_class']);

        $hrPendingRows = $method->invoke(
            $controller,
            $this->leaveRequestWithHistories([
                new LeaveRequestHistory([
                    'event_type' => 'submitted',
                    'title' => 'Request Submitted',
                    'to_status' => 'pending',
                    'happened_at' => Carbon::parse('2026-06-15 08:00:00', 'Asia/Jakarta'),
                ]),
                new LeaveRequestHistory([
                    'event_type' => 'supervisor_review',
                    'title' => 'Supervisor Review',
                    'to_status' => 'complete',
                    'happened_at' => Carbon::parse('2026-06-15 09:00:00', 'Asia/Jakarta'),
                ]),
                new LeaveRequestHistory([
                    'event_type' => 'hr_verification',
                    'title' => 'HR Verification (Pending)',
                    'to_status' => 'pending',
                    'happened_at' => Carbon::parse('2026-06-15 10:00:00', 'Asia/Jakarta'),
                ]),
            ]),
            'pending',
            $fallbackDate
        );

        $this->assertSame('border-success', $hrPendingRows[0]['badge_class']);
        $this->assertSame('border-success', $hrPendingRows[1]['badge_class']);
        $this->assertSame('border-warning', $hrPendingRows[2]['badge_class']);
        $this->assertSame('border-dark', $hrPendingRows[3]['badge_class']);
    }

    /**
     * @param  array<int, LeaveRequestHistory>  $histories
     */
    private function leaveRequestWithHistories(array $histories): LeaveRequest
    {
        $leaveRequest = new LeaveRequest;
        $leaveRequest->setRelation('histories', collect($histories));

        return $leaveRequest;
    }

    public function test_leave_request_is_locked_after_supervisor_review_is_complete(): void
    {
        $completedLeaveRequest = new LeaveRequest;
        $completedLeaveRequest->setRelation('histories', collect([
            new LeaveRequestHistory([
                'event_type' => 'supervisor_review',
                'title' => 'Supervisor Review',
                'to_status' => 'complete',
            ]),
        ]));

        $pendingLeaveRequest = new LeaveRequest;
        $pendingLeaveRequest->setRelation('histories', collect([
            new LeaveRequestHistory([
                'event_type' => 'supervisor_review',
                'title' => 'Supervisor Review',
                'to_status' => 'pending',
            ]),
        ]));

        $this->assertTrue($completedLeaveRequest->hasCompletedSupervisorReview());
        $this->assertFalse($pendingLeaveRequest->hasCompletedSupervisorReview());
    }

    public function test_leave_history_year_filter_is_removed_from_view_and_controller(): void
    {
        $leaveRequestView = File::get(resource_path('views/attendance/leave-requests/index.blade.php'));
        $leaveHistoryListCardsPartial = File::get(resource_path('views/attendance/leave-requests/partials/history-list-cards.blade.php'));
        $leaveRequestController = File::get(app_path('Http/Controllers/AttendanceLeaveRequestController.php'));
        $leaveRequestModel = File::get(app_path('Models/LeaveRequest.php'));
        $leaveSubTypeModel = File::get(app_path('Models/LeaveSubType.php'));
        $routes = File::get(base_path('routes/web.php'));
        $leaveRequestUpdateMigration = File::get(database_path('migrations/2026_06_09_080747_add_handover_notes_to_leave_requests_table.php'));
        $leaveRequestHistoryCompleteMigration = File::get(database_path('migrations/2026_06_15_081539_add_complete_status_to_leave_request_histories_table.php'));

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
        $this->assertStringContainsString('class="card leave-history-detail-trigger" role="button" tabindex="0"', $leaveRequestView);
        $this->assertStringContainsString('class="clearfix ms-auto leave-history-card-actions"', $leaveRequestView);
        $this->assertStringContainsString('class="dropdown-item leave-history-action-view">View</a>', $leaveRequestView);
        $this->assertStringContainsString('class="dropdown-item leave-history-action-update">Update</a>', $leaveRequestView);
        $this->assertStringContainsString('class="dropdown-item leave-history-action-delete">Delete</a>', $leaveRequestView);
        $this->assertStringContainsString("if ($(event.target).closest('.leave-history-card-actions').length) {", $leaveRequestView);
        $this->assertStringContainsString("$(document).on('click', '.leave-history-action-view', function (event) {", $leaveRequestView);
        $this->assertStringContainsString("$(document).on('click', '.leave-history-action-update', function (event) {", $leaveRequestView);
        $this->assertStringContainsString("$(document).on('click', '.leave-history-action-delete', function (event) {", $leaveRequestView);
        $this->assertStringContainsString("showLeaveHistoryDetailModal($(this).closest('.leave-history-detail-trigger'));", $leaveRequestView);
        $this->assertStringContainsString("showLeaveUpdateModal($(this).closest('.leave-history-detail-trigger'));", $leaveRequestView);
        $this->assertStringContainsString("showLeaveDeleteModal($(this).closest('.leave-history-detail-trigger'));", $leaveRequestView);
        $this->assertStringContainsString('function showLeaveHistoryDetailModal($card)', $leaveRequestView);
        $this->assertStringContainsString('function showLeaveUpdateModal($card)', $leaveRequestView);
        $this->assertStringContainsString('function showLeaveDeleteModal($card)', $leaveRequestView);
        $this->assertStringContainsString('function fillLeaveDeleteModal($card)', $leaveRequestView);
        $this->assertStringContainsString('function fillLeaveUpdateModal($card)', $leaveRequestView);
        $this->assertStringContainsString('function toggleUpdateConditionalFields()', $leaveRequestView);
        $this->assertStringContainsString('function initLeaveRequestUpdateSubmit()', $leaveRequestView);
        $this->assertStringContainsString('function initLeaveRequestDeleteSubmit()', $leaveRequestView);
        $this->assertStringContainsString('function showSwalAlert(iconType, titleText, messageText)', $leaveRequestView);
        $this->assertStringNotContainsString('class="card leave-history-detail-trigger" role="button" tabindex="0" data-bs-toggle="modal" data-bs-target="#leaveHistoryDetailModal"', $leaveRequestView);
        $this->assertStringNotContainsString('class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sick">View</a>', $leaveRequestView);
        $this->assertStringContainsString("data-leave-request-id=\"{{ \$leaveHistoryCard['id'] ?? '' }}\"", $leaveRequestView);
        $this->assertStringContainsString("@if (! empty(\$leaveHistoryCard['can_view']))", $leaveRequestView);
        $this->assertStringContainsString("@if (! empty(\$leaveHistoryCard['can_update']))", $leaveRequestView);
        $this->assertStringContainsString("@if (! empty(\$leaveHistoryCard['can_delete']))", $leaveRequestView);
        $this->assertStringContainsString("data-leave-type-id=\"{{ \$leaveHistoryCard['leave_type_id'] ?? '' }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-start-date=\"{{ \$leaveHistoryCard['start_date_value'] ?? '' }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-end-date=\"{{ \$leaveHistoryCard['end_date_value'] ?? '' }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-handover-notes=\"{{ \$leaveHistoryCard['handover_notes'] ?? '' }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-title=\"{{ \$leaveHistoryCard['title'] ?? 'Leave Request' }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-modal-title=\"{{ \$leaveHistoryCard['modal_title'] ?? (\$leaveHistoryCard['title'] ?? 'Leave Request') }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-leave-type=\"{{ \$leaveHistoryCard['detail_leave_type'] ?? (\$leaveHistoryCard['title'] ?? 'Leave Request') }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-is-sick=\"{{ ! empty(\$leaveHistoryCard['is_sick_leave']) ? 'true' : 'false' }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-attachment-url=\"{{ \$leaveHistoryCard['attachment_url'] ?? '' }}\"", $leaveRequestView);
        $this->assertStringContainsString("data-detail-timeline='@json(\$leaveHistoryCard['timeline'] ?? [])'", $leaveRequestView);
        $this->assertStringContainsString("asset('assets/'.(\$leaveHistoryCard['icon_file'] ?? 'annual_leave.svg'))", $leaveRequestView);
        $this->assertStringContainsString('data-detail-title="\' + escapeHtml(title) + \'"', $leaveRequestView);
        $this->assertStringContainsString('data-leave-request-id="\' + escapeHtml(leaveRequestId) + \'"', $leaveRequestView);
        $this->assertStringContainsString('var canView = card.can_view === true;', $leaveRequestView);
        $this->assertStringContainsString('var canUpdate = card.can_update === true;', $leaveRequestView);
        $this->assertStringContainsString('var canDelete = card.can_delete === true;', $leaveRequestView);
        $this->assertStringContainsString("(canView ? '<a href=\"#\" class=\"dropdown-item leave-history-action-view\">View</a>' : '')", $leaveRequestView);
        $this->assertStringContainsString("(canUpdate ? '<a href=\"#\" class=\"dropdown-item leave-history-action-update\">Update</a>' : '')", $leaveRequestView);
        $this->assertStringContainsString("(canDelete ? '<a href=\"#\" class=\"dropdown-item leave-history-action-delete\">Delete</a>' : '')", $leaveRequestView);
        $this->assertStringContainsString('data-detail-modal-title="\' + escapeHtml(modalTitle) + \'"', $leaveRequestView);
        $this->assertStringContainsString('data-detail-is-sick="\' + isSickLeave + \'"', $leaveRequestView);
        $this->assertStringContainsString('data-detail-attachment-url="\' + escapeHtml(attachmentUrl) + \'"', $leaveRequestView);
        $this->assertStringContainsString('data-detail-timeline="\' + timelineAttribute + \'"', $leaveRequestView);
        $this->assertStringContainsString("var iconFile = card.icon_file || 'annual_leave.svg';", $leaveRequestView);
        $this->assertStringContainsString("var leaveTypeIconBaseUrl = @json(asset('assets'));", $leaveRequestView);
        $this->assertStringContainsString("var leaveUpdateUrlTemplate = @json(route('attendance.leave-requests.update', ['leaveRequest' => '__LEAVE_REQUEST_ID__']));", $leaveRequestView);
        $this->assertStringContainsString("var leaveDeleteUrlTemplate = @json(route('attendance.leave-requests.destroy', ['leaveRequest' => '__LEAVE_REQUEST_ID__']));", $leaveRequestView);
        $this->assertStringContainsString('id="leaveRequestUpdateForm"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveRequestDeleteForm"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveDeleteRequestIdInput"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveDeleteAlert"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveDeleteSubmitButton"', $leaveRequestView);
        $this->assertStringContainsString('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>', $leaveRequestView);
        $this->assertStringContainsString("showSwalAlert('success', 'Berhasil', successMessage);", $leaveRequestView);
        $this->assertStringContainsString('id="leaveUpdateTypeSelect" name="permission_type_id"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveUpdateSpecialLeaveTypeWrapper"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveUpdateSpecialLeaveSubTypeSelect" name="special_leave_sub_type_id"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveUpdateDateRangeInput"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveUpdateReasonInput" name="reason"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveUpdateHandoverNotesInput" name="handover_notes"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveUpdateSickAttachmentWrapper"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveUpdateAttachmentFileInput" name="attachment_file"', $leaveRequestView);
        $this->assertStringContainsString('id="leaveUpdateSubmitButton"', $leaveRequestView);
        $this->assertStringContainsString("var medicalNotesUnavailableUrl = @json(asset('assets/not_available_images.png'));", $leaveRequestView);
        $this->assertStringContainsString("asset('assets/not_available_images.png')", $leaveRequestView);
        $this->assertStringContainsString('attachmentUrl || medicalNotesUnavailableUrl', $leaveRequestView);
        $this->assertStringContainsString('target="_blank" rel="noopener" id="leaveHistoryDetailMedicalNotesLink"', $leaveRequestView);
        $this->assertStringContainsString("$('#leaveHistoryDetailMedicalNotesLink').attr('href', attachmentUrl || medicalNotesUnavailableUrl);", $leaveRequestView);
        $this->assertStringNotContainsString('assets/images/logo/figma.avif', $leaveRequestView);

        $this->assertStringContainsString("Route::put('/attendance/leave-requests/{leaveRequest}', [AttendanceLeaveRequestController::class, 'update'])->name('attendance.leave-requests.update');", $routes);
        $this->assertStringContainsString("Route::delete('/attendance/leave-requests/{leaveRequest}', [AttendanceLeaveRequestController::class, 'destroy'])->name('attendance.leave-requests.destroy');", $routes);
        $this->assertStringContainsString('public function update(Request $request, LeaveRequest $leaveRequest): JsonResponse', $leaveRequestController);
        $this->assertStringContainsString('public function destroy(LeaveRequest $leaveRequest): JsonResponse', $leaveRequestController);
        $this->assertStringContainsString('private function canUpdatePermissionRequest(?User $authenticatedUser, LeaveRequest $leaveRequest): bool', $leaveRequestController);
        $this->assertStringContainsString('private function canDeletePermissionRequest(?User $authenticatedUser, LeaveRequest $leaveRequest): bool', $leaveRequestController);
        $this->assertStringContainsString('private function canStaffManageOwnLeaveRequest(?User $authenticatedUser, LeaveRequest $leaveRequest): bool', $leaveRequestController);
        $this->assertStringContainsString('if ($this->isAdminUser($authenticatedUser) || $this->isBoardOfDirectur($authenticatedUser)) {', $leaveRequestController);
        $this->assertStringContainsString("'can_view' => ! \$hasCompletedSupervisorReview,", $leaveRequestController);
        $this->assertStringContainsString("'can_update' => \$this->canUpdatePermissionRequest(\$authenticatedUser, \$leaveRequest),", $leaveRequestController);
        $this->assertStringContainsString("'can_delete' => \$this->canDeletePermissionRequest(\$authenticatedUser, \$leaveRequest),", $leaveRequestController);
        $this->assertStringContainsString('if ($leaveRequest->hasCompletedSupervisorReview()) {', $leaveRequestController);
        $this->assertStringContainsString('Supervisor Review sudah complete.', $leaveRequestController);
        $this->assertStringContainsString('public function hasCompletedSupervisorReview(): bool', $leaveRequestModel);
        $this->assertStringContainsString("\$normalizedEventType === 'supervisor_review'", $leaveRequestModel);
        $this->assertStringContainsString("\$normalizedStatus === 'complete'", $leaveRequestModel);
        $this->assertStringContainsString("->where('event_type', 'supervisor_review')", $leaveRequestModel);
        $this->assertStringContainsString("->where('to_status', 'complete')", $leaveRequestModel);
        $this->assertStringContainsString("'complete']", $leaveRequestHistoryCompleteMigration);
        $this->assertStringContainsString("'employee_id',", $leaveRequestController);
        $this->assertStringContainsString("'special_leave_sub_type_id' => ['nullable', 'exists:leave_sub_types,id']", $leaveRequestController);
        $this->assertStringContainsString("'handover_notes' => ['nullable', 'string', 'max:5000']", $leaveRequestController);
        $this->assertStringContainsString("'status' => 'pending',", $leaveRequestController);
        $this->assertStringContainsString("eventType: 'updated',", $leaveRequestController);
        $this->assertStringContainsString("'handover_notes',", $leaveRequestController);
        $this->assertStringContainsString("'start_date_value' => \$startDate->toDateString(),", $leaveRequestController);
        $this->assertStringContainsString("'handover_notes' => trim((string) (\$leaveRequest->handover_notes ?? '')),", $leaveRequestController);
        $this->assertStringNotContainsString("foreignUuid('special_leave_sub_type_id')", $leaveRequestUpdateMigration);
        $this->assertStringContainsString("\$table->text('handover_notes')->nullable()->after('reason');", $leaveRequestUpdateMigration);
        $this->assertStringContainsString("protected \$table = 'leave_sub_types';", $leaveSubTypeModel);
        $this->assertStringContainsString('public function leaveType(): BelongsTo', $leaveSubTypeModel);
    }

    public function test_leave_summary_is_split_into_eligibility_and_tracker_data(): void
    {
        $leaveRequestView = File::get(resource_path('views/attendance/leave-requests/index.blade.php'));
        $leaveHistoryListCardsPartial = File::get(resource_path('views/attendance/leave-requests/partials/history-list-cards.blade.php'));
        $leaveRequestController = File::get(app_path('Http/Controllers/AttendanceLeaveRequestController.php'));
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
            'function showLeaveHistoryDetailModal($card)',
            'Out of Office mode: ON',
            'Your health comes first',
            "$(document).on('click', '.leave-history-detail-trigger', function (event) {",
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
            'private function formatLeaveHistoryPeriodLabel(Carbon $startDate, Carbon $endDate, int $totalDays): string',
            "return \$startDate->format('d M').' - '.\$endDate->format('d M Y').' ('.\$dayLabel.')';",
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
            'Demam dan Flu',
            'Liburan keluarga dan istirahat sejenak',
            '18 May 2026 - 19 May 2026 (2 days)',
            'ecom-product-detail.html',
        ] as $removedFragment) {
            $this->assertStringNotContainsString($removedFragment, $leaveRequestView.$leaveRequestController);
        }
    }
}
