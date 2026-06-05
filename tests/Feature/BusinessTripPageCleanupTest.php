<?php

namespace Tests\Feature;

use App\Http\Controllers\BusinessTripCashAdvanceController;
use App\Http\Controllers\BusinessTripController;
use App\Http\Controllers\BusinessTripReimbursementController;
use App\Models\BusinessTrip;
use App\Models\BusinessTripCashAdvance;
use App\Models\BusinessTripLifecycleLog;
use App\Models\BusinessTripReimbursement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

class BusinessTripPageCleanupTest extends TestCase
{
    public function test_business_trip_page_only_keeps_index_route_and_placeholder_button(): void
    {
        $businessTripIndexRoute = Route::getRoutes()->getByName('attendance.business-trips');
        $businessTripCreateRoute = Route::getRoutes()->getByName('attendance.business-trips.create');
        $businessTripStoreRoute = Route::getRoutes()->getByName('attendance.business-trips.store');
        $businessTripShowRoute = Route::getRoutes()->getByName('attendance.business-trips.show');
        $businessTripCashAdvanceCreateRoute = Route::getRoutes()->getByName('attendance.business-trips.cash-advances.create');
        $businessTripCashAdvanceStoreRoute = Route::getRoutes()->getByName('attendance.business-trips.cash-advances.store');
        $businessTripReimbursementCreateRoute = Route::getRoutes()->getByName('attendance.business-trips.reimbursements.create');
        $businessTripReimbursementStoreRoute = Route::getRoutes()->getByName('attendance.business-trips.reimbursements.store');
        $businessTripProvincesRoute = Route::getRoutes()->getByName('attendance.business-trips.provinces');
        $businessTripRegenciesRoute = Route::getRoutes()->getByName('attendance.business-trips.regencies');
        $businessTripView = File::get(resource_path('views/attendance/business-trips/index.blade.php'));
        $businessTripCreateView = File::get(resource_path('views/attendance/business-trips/create.blade.php'));
        $businessTripDetailView = File::get(resource_path('views/attendance/business-trips/detail.blade.php'));
        $businessTripCashAdvanceCreateView = File::get(resource_path('views/attendance/business-trips/create-cash-advance.blade.php'));
        $businessTripReimbursementCreateView = File::get(resource_path('views/attendance/business-trips/create-reimbursement.blade.php'));
        $profileNavbarView = File::get(resource_path('views/attendance/layouts/profile-navbar.blade.php'));
        $businessTripController = File::get(app_path('Http/Controllers/BusinessTripController.php'));
        $businessTripCashAdvanceController = File::get(app_path('Http/Controllers/BusinessTripCashAdvanceController.php'));

        $this->assertNotNull($businessTripIndexRoute);
        $this->assertNotNull($businessTripCreateRoute);
        $this->assertNotNull($businessTripStoreRoute);
        $this->assertNotNull($businessTripShowRoute);
        $this->assertNotNull($businessTripCashAdvanceCreateRoute);
        $this->assertNotNull($businessTripCashAdvanceStoreRoute);
        $this->assertNotNull($businessTripReimbursementCreateRoute);
        $this->assertNotNull($businessTripReimbursementStoreRoute);
        $this->assertNotNull($businessTripProvincesRoute);
        $this->assertNotNull($businessTripRegenciesRoute);
        $this->assertSame(BusinessTripController::class.'@index', $businessTripIndexRoute->getActionName());
        $this->assertSame(BusinessTripController::class.'@create', $businessTripCreateRoute->getActionName());
        $this->assertSame(BusinessTripController::class.'@store', $businessTripStoreRoute->getActionName());
        $this->assertSame(BusinessTripController::class.'@show', $businessTripShowRoute->getActionName());
        $this->assertSame(BusinessTripCashAdvanceController::class.'@create', $businessTripCashAdvanceCreateRoute->getActionName());
        $this->assertSame(BusinessTripCashAdvanceController::class.'@store', $businessTripCashAdvanceStoreRoute->getActionName());
        $this->assertSame(BusinessTripReimbursementController::class.'@create', $businessTripReimbursementCreateRoute->getActionName());
        $this->assertSame(BusinessTripReimbursementController::class.'@store', $businessTripReimbursementStoreRoute->getActionName());
        $this->assertSame(BusinessTripController::class.'@provinces', $businessTripProvincesRoute->getActionName());
        $this->assertSame(BusinessTripController::class.'@regencies', $businessTripRegenciesRoute->getActionName());
        $this->assertNull(Route::getRoutes()->getByName('attendance.business-trips.datatable'));
        $this->assertFalse(method_exists(BusinessTripController::class, 'datatable'));
        $this->assertTrue(method_exists(BusinessTripController::class, 'store'));
        $this->assertTrue(method_exists(BusinessTripController::class, 'create'));
        $this->assertTrue(method_exists(BusinessTripController::class, 'show'));
        $this->assertStringContainsString('<h5 class="mb-0">Business Trip</h5>', $businessTripView);
        $this->assertStringContainsString('href="{{ route(\'attendance.business-trips.create\') }}"', $businessTripView);
        $this->assertStringContainsString('>+ Business Trip</a>', $businessTripView);
        $this->assertStringContainsString('businessTripSummary', $businessTripController);
        $this->assertStringContainsString('businessTripCards', $businessTripController);
        $this->assertStringContainsString("'progress_percentage' => 0", $businessTripController);
        $this->assertStringContainsString("'detail_url' => route('attendance.business-trips.show', \$businessTrip)", $businessTripController);
        $this->assertStringContainsString("return view('attendance.business-trips.detail', [", $businessTripController);
        $this->assertStringContainsString("'employee.profile'", $businessTripController);
        $this->assertStringContainsString("'supervisor.profile'", $businessTripController);
        $this->assertStringContainsString("'cashAdvances'", $businessTripController);
        $this->assertStringContainsString("'reimbursements'", $businessTripController);
        $this->assertStringContainsString("'lifecycleLogs.actor'", $businessTripController);
        $this->assertStringContainsString("'lifecycleLogs.actor.employee.profile'", $businessTripController);
        $this->assertStringContainsString("'lifecycleLogs.actor.userProfile'", $businessTripController);
        $this->assertStringContainsString('$businessTripRequestDetails = $this->buildBusinessTripRequestDetails($businessTrip);', $businessTripController);
        $this->assertStringContainsString('$businessTripCashAdvanceRows = $this->buildBusinessTripCashAdvanceRows($businessTrip);', $businessTripController);
        $this->assertStringContainsString("'businessTripRequestDetails' => \$businessTripRequestDetails", $businessTripController);
        $this->assertStringContainsString("'businessTripApprovedExpenseBreakdownRows' => \$this->buildBusinessTripApprovedExpenseBreakdownRows(\$businessTripCashAdvanceRows)", $businessTripController);
        $this->assertStringContainsString("'businessTripRequestFinancialRows' => \$this->buildBusinessTripRequestFinancialRows(\$businessTrip, \$businessTripCashAdvanceRows)", $businessTripController);
        $this->assertStringContainsString("'businessTripRequestStatusRows' => \$this->buildBusinessTripRequestStatusRows(\$businessTrip, \$businessTripCashAdvanceRows)", $businessTripController);
        $this->assertStringContainsString("'businessTripExpenseRows' => \$this->buildBusinessTripApprovedExpenseRows(\$businessTripCashAdvanceRows)", $businessTripController);
        $this->assertStringContainsString("'businessTripExpenseSummaryRows' => \$this->buildBusinessTripExpenseSummaryRows(\$businessTrip, \$businessTripCashAdvanceRows)", $businessTripController);
        $this->assertStringContainsString("'businessTripCashAdvanceRows' => \$businessTripCashAdvanceRows", $businessTripController);
        $this->assertStringContainsString("'businessTripCashAdvanceSummary' => \$this->buildBusinessTripCashAdvanceSummary(\$businessTripCashAdvanceRows)", $businessTripController);
        $this->assertStringContainsString("'businessTripReimbursementRows' => \$this->buildBusinessTripReimbursementRows(\$businessTrip)", $businessTripController);
        $this->assertStringContainsString("'businessTripReimbursementSummary' => \$this->buildBusinessTripReimbursementSummary(\$businessTrip)", $businessTripController);
        $this->assertStringContainsString("'businessTripDetailPermissions' => \$this->buildBusinessTripDetailPermissions(\$businessTrip)", $businessTripController);
        $this->assertStringContainsString("'businessTripLifecycleTracker' => \$this->buildBusinessTripLifecycleTracker(\$businessTrip)", $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripRequestDetails(BusinessTrip $businessTrip): array', $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripCashAdvanceRows(BusinessTrip $businessTrip): Collection', $businessTripController);
        $this->assertStringContainsString('private function formatBusinessTripCashAdvanceDateRange(BusinessTripCashAdvance $cashAdvance): string', $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripApprovedExpenseBreakdownRows(Collection $cashAdvanceRows): Collection', $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripRequestFinancialRows(BusinessTrip $businessTrip, Collection $cashAdvanceRows): Collection', $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripRequestStatusRows(BusinessTrip $businessTrip, Collection $cashAdvanceRows): Collection', $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripApprovedExpenseRows(Collection $cashAdvanceRows): Collection', $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripExpenseSummaryRows(BusinessTrip $businessTrip, Collection $cashAdvanceRows): Collection', $businessTripController);
        $this->assertStringContainsString('private const BUSINESS_TRIP_EXPENSE_BREAKDOWN_CATEGORIES = [', $businessTripController);
        $this->assertStringNotContainsString('$businessTrip->reimbursements
            ->filter(fn ($reimbursement): bool => $this->isApprovedLifecycleStatus((string) $reimbursement->status))', $businessTripController);
        $this->assertStringContainsString("->filter(fn (array \$cashAdvanceRow): bool => (bool) (\$cashAdvanceRow['is_approved'] ?? false))", $businessTripController);
        $this->assertStringContainsString("'approved_date_label' => \$cashAdvance->approved_at?->timezone('Asia/Jakarta')->format('d M Y') ?? '-'", $businessTripController);
        $this->assertStringContainsString("'date_label' => \$cashAdvanceRow['approved_date_label'] ?? '-'", $businessTripController);
        $this->assertStringContainsString("'attachment_url' => \$attachmentPath !== '' ? Storage::url(\$attachmentPath) : null", $businessTripController);
        $this->assertStringContainsString("'attachment_modal_id' => 'businessTripExpenseAttachmentModal'", $businessTripController);
        $this->assertStringContainsString('private const BUSINESS_TRIP_INCENTIVE_DAILY_RATE = 100000;', $businessTripController);
        $this->assertStringContainsString('$totalExpenses = $cashAdvanceTotal + $reimbursementTotal;', $businessTripController);
        $this->assertStringContainsString('$balanceDue = max($totalExpenses - $cashAdvanceTotal, 0);', $businessTripController);
        $this->assertStringContainsString('$totalPayment = $balanceDue + $tripIncentive;', $businessTripController);
        $this->assertStringContainsString("'amount_class' => \$cashAdvanceAmountClass", $businessTripController);
        $this->assertStringContainsString("'has_bottom_divider' => true", $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripCashAdvanceSummary(Collection $cashAdvanceRows): array', $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripReimbursementRows(BusinessTrip $businessTrip): Collection', $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripReimbursementSummary(BusinessTrip $businessTrip): array', $businessTripController);
        $this->assertStringContainsString('$finalAmount = $approvedAmount ?? $requestedAmount;', $businessTripController);
        $this->assertStringContainsString("'payment_amount' => \$finalAmount", $businessTripController);
        $this->assertStringContainsString("'has_approved_amount' => \$approvedAmount !== null", $businessTripController);
        $this->assertStringNotContainsString('$finalAmount = $approvedAmount ?? $realizedAmount ?? $requestedAmount;', $businessTripController);
        $this->assertStringContainsString("'status_label' => \$allCashAdvancesApproved ? 'Approved' : 'Pending'", $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripDetailPermissions(BusinessTrip $businessTrip): array', $businessTripController);
        $this->assertStringContainsString("'can_view_trip_expense_values' => \$cashAdvanceApproved", $businessTripController);
        $this->assertStringContainsString("'can_view_cash_advance_values' => \$cashAdvanceDetailsReady", $businessTripController);
        $this->assertStringContainsString("'can_view_reimbursement_values' => \$reimbursementDetailsReady", $businessTripController);
        $this->assertStringContainsString("'can_use_action_buttons' => \$supervisorReviewApproved", $businessTripController);
        $this->assertStringContainsString("'can_use_reimbursement_button' => \$reimbursementButtonReady", $businessTripController);
        $this->assertStringContainsString('private function businessTripHasCompletedReimbursementReport(BusinessTrip $businessTrip): bool', $businessTripController);
        $this->assertStringContainsString('private function businessTripLifecycleEventIsPending(BusinessTrip $businessTrip, string $eventKey): bool', $businessTripController);
        $this->assertStringContainsString('private function businessTripLifecycleEventHasStarted(BusinessTrip $businessTrip, string $eventKey): bool', $businessTripController);
        $this->assertStringContainsString("businessTripLifecycleEventIsApproved(\$businessTrip, 'supervisor_review')", $businessTripController);
        $this->assertStringContainsString("businessTripLifecycleEventIsApproved(\$businessTrip, 'cash_advance_submitted')", $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripLifecycleTracker(BusinessTrip $businessTrip): Collection', $businessTripController);
        $this->assertStringContainsString('private function lifecycleValueFromLog(BusinessTripLifecycleLog $lifecycleLog, BusinessTrip $businessTrip): array', $businessTripController);
        $this->assertStringContainsString("if ((string) \$lifecycleLog->event_key === 'trip_execution' && in_array(\$state, ['pending', 'completed'], true))", $businessTripController);
        $this->assertStringContainsString('$lifecycleValue[\'date_label\'] = $this->formatTripExecutionLifecycleDateLabel($businessTrip, $state);', $businessTripController);
        $this->assertStringContainsString('$lifecycleValue[\'datetime_label\'] = $this->formatTripExecutionLifecycleDateTimeLabel($businessTrip, $state);', $businessTripController);
        $this->assertStringContainsString('private function normalizeLifecycleState(string $state): string', $businessTripController);
        $this->assertStringContainsString('private function formatTripExecutionLifecycleDateLabel(BusinessTrip $businessTrip, string $state): string', $businessTripController);
        $this->assertStringContainsString('private function formatTripExecutionLifecycleDateTimeLabel(BusinessTrip $businessTrip, string $state): string', $businessTripController);
        $this->assertStringNotContainsString('private function isScheduledLifecycleEvent(BusinessTripLifecycleLog $lifecycleLog, string $state): bool', $businessTripController);
        $this->assertStringNotContainsString('private function businessTripStaffActorLabel(BusinessTrip $businessTrip): string', $businessTripController);
        $this->assertStringNotContainsString('$actorLabel = $this->businessTripStaffActorLabel($businessTrip);', $businessTripController);
        $this->assertStringContainsString('return $businessTrip->lifecycleLogs', $businessTripController);
        $this->assertStringContainsString("->groupBy('phase')", $businessTripController);
        $this->assertStringContainsString("'pending' => 'border-warning'", $businessTripController);
        $this->assertStringContainsString("return \$state === 'pending' ? 'Now' : 'Next';", $businessTripController);
        $this->assertStringNotContainsString('buildBusinessTripLifecycleValues', $businessTripController);
        $this->assertStringContainsString('@forelse (($businessTripCards ?? collect()) as $businessTripCard)', $businessTripView);
        $this->assertStringContainsString('<a href="{{ $businessTripCard[\'detail_url\'] ?? \'#\' }}"', $businessTripView);
        $this->assertStringContainsString("{{ \$businessTripSummary['total_trips'] ?? 0 }} Trips", $businessTripView);
        $this->assertStringContainsString("<span>{{ \$businessTripCard['progress_percentage'] ?? 0 }}%</span>", $businessTripView);
        $this->assertStringContainsString("style=\"width: {{ \$businessTripCard['progress_percentage'] ?? 0 }}%;\"", $businessTripView);
        $this->assertStringNotContainsString('#TRP-2026-054', $businessTripView);
        $this->assertStringNotContainsString('Surabaya, Jawa Timur', $businessTripView);
        $this->assertStringContainsString("request()->routeIs('attendance.business-trips*')", $profileNavbarView);
        $this->assertStringContainsString('Business Trip - Create', $businessTripCreateView);
        $this->assertStringContainsString('id="businessTripDateRangeInput"', $businessTripCreateView);
        $this->assertStringContainsString('name="start_date" id="businessTripStartDateInput"', $businessTripCreateView);
        $this->assertStringContainsString('name="end_date" id="businessTripEndDateInput"', $businessTripCreateView);
        $this->assertStringContainsString('$businessTripDateRangeInput.daterangepicker', $businessTripCreateView);
        $this->assertStringContainsString('name="province_destination" id="businessTripProvinceDestinationInput"', $businessTripCreateView);
        $this->assertStringContainsString('id="businessTripProvinceSelect" name="province_code"', $businessTripCreateView);
        $this->assertStringContainsString('name="city_destination" id="businessTripCityDestinationInput"', $businessTripCreateView);
        $this->assertStringContainsString('id="businessTripCityRegencySelect" name="city_regency_code"', $businessTripCreateView);
        $this->assertStringContainsString("route('attendance.business-trips.provinces')", $businessTripCreateView);
        $this->assertStringContainsString("route('attendance.business-trips.regencies'", $businessTripCreateView);
        $this->assertStringContainsString('fetch(businessTripProvinceUrl)', $businessTripCreateView);
        $this->assertStringContainsString("fetch(businessTripRegencyUrlTemplate.replace('__PROVINCE_CODE__', provinceCode))", $businessTripCreateView);
        $this->assertStringContainsString('method="POST" action="{{ route(\'attendance.business-trips.store\') }}"', $businessTripCreateView);
        $this->assertStringContainsString('name="purpose"', $businessTripCreateView);
        $this->assertStringContainsString('name="trip_type"', $businessTripCreateView);
        $this->assertStringContainsString('name="transportation_mode"', $businessTripCreateView);
        $this->assertStringContainsString('name="departure_date" id="businessTripDepartureDateValueInput"', $businessTripCreateView);
        $this->assertStringContainsString('name="check_in_date" id="businessTripCheckInDateValueInput"', $businessTripCreateView);
        $this->assertStringContainsString('name="check_out_date" id="businessTripCheckOutDateValueInput"', $businessTripCreateView);
        $this->assertStringContainsString('id="businessTripTransportationSelect" name="transportation_arrangement"', $businessTripCreateView);
        $this->assertStringContainsString('<option value="booked_by_ga">Booked by GA</option>', $businessTripCreateView);
        $this->assertStringContainsString('id="businessTripDepartureDateWrapper"', $businessTripCreateView);
        $this->assertStringContainsString('id="businessTripDepartureTimeWrapper"', $businessTripCreateView);
        $this->assertStringContainsString("var isBookedByGa = \$businessTripTransportationSelect.val() === 'booked_by_ga';", $businessTripCreateView);
        $this->assertStringContainsString("\$businessTripDepartureDateWrapper.toggleClass('d-none', isBookedByGa);", $businessTripCreateView);
        $this->assertStringContainsString("\$businessTripDepartureTimeWrapper.toggleClass('d-none', isBookedByGa);", $businessTripCreateView);
        $this->assertStringContainsString('id="businessTripAccommodationSelect" name="accommodation_arrangement"', $businessTripCreateView);
        $this->assertStringContainsString('id="businessTripCheckInDateWrapper"', $businessTripCreateView);
        $this->assertStringContainsString('id="businessTripCheckOutDateWrapper"', $businessTripCreateView);
        $this->assertStringContainsString("var isBookedByGa = \$businessTripAccommodationSelect.val() === 'booked_by_ga';", $businessTripCreateView);
        $this->assertStringContainsString("\$businessTripCheckInDateWrapper.toggleClass('d-none', isBookedByGa);", $businessTripCreateView);
        $this->assertStringContainsString("\$businessTripCheckOutDateWrapper.toggleClass('d-none', isBookedByGa);", $businessTripCreateView);
        $this->assertStringContainsString('class="form-control business-trip-single-date-picker"', $businessTripCreateView);
        $this->assertStringContainsString('singleDatePicker: true', $businessTripCreateView);
        $this->assertStringContainsString('href="{{ route(\'attendance.business-trips\') }}"', $businessTripCreateView);
        $this->assertStringContainsString('Business Trip Details', $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripRequestDetails['full_name'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripRequestDetails['supervisor_name'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripRequestDetails['purpose'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripRequestDetails['trip_type'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripRequestDetails['destination'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripRequestDetails['date_range'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripRequestDetails['duration'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString('@foreach (($businessTripApprovedExpenseBreakdownRows ?? collect()) as $businessTripExpenseValue)', $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripExpenseValue['label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripExpenseValue['amount_label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("\$businessTripExpenseValue['description_lines'] ?? []", $businessTripDetailView);
        $this->assertStringNotContainsString('Kereta Api Taksaka', $businessTripDetailView);
        $this->assertStringContainsString("\$canViewBusinessTripExpenseValues = (bool) (\$businessTripDetailPermissions['can_view_trip_expense_values'] ?? false);", $businessTripDetailView);
        $this->assertStringContainsString("\$canViewBusinessTripCashAdvanceValues = (bool) (\$businessTripDetailPermissions['can_view_cash_advance_values'] ?? false);", $businessTripDetailView);
        $this->assertStringContainsString("\$canViewBusinessTripReimbursementValues = (bool) (\$businessTripDetailPermissions['can_view_reimbursement_values'] ?? false);", $businessTripDetailView);
        $this->assertStringContainsString("\$canUseBusinessTripActionButtons = (bool) (\$businessTripDetailPermissions['can_use_action_buttons'] ?? false);", $businessTripDetailView);
        $this->assertStringContainsString("\$canUseBusinessTripReimbursementButton = (bool) (\$businessTripDetailPermissions['can_use_reimbursement_button'] ?? false);", $businessTripDetailView);
        $this->assertStringContainsString('@if ($canViewBusinessTripExpenseValues)', $businessTripDetailView);
        $this->assertStringContainsString('@forelse (($businessTripExpenseRows ?? collect()) as $expenseItem)', $businessTripDetailView);
        $this->assertStringContainsString("{{ \$expenseItem['category_label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$expenseItem['amount_label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString('class="js-business-trip-attachment-preview"', $businessTripDetailView);
        $this->assertStringContainsString('data-bs-target="#businessTripAttachmentPreviewModal"', $businessTripDetailView);
        $this->assertStringContainsString('id="businessTripAttachmentPreviewFrame"', $businessTripDetailView);
        $this->assertStringContainsString('Expense Attachment', $businessTripDetailView);
        $this->assertStringContainsString('<span class="text-danger fw-semibold">Belum mengupload attachment.</span>', $businessTripDetailView);
        $this->assertStringNotContainsString("'title' => 'Meals & Entertaintment'", $businessTripDetailView);
        $this->assertStringContainsString('@if ($canViewBusinessTripCashAdvanceValues)', $businessTripDetailView);
        $this->assertStringContainsString('@if ($canViewBusinessTripReimbursementValues)', $businessTripDetailView);
        $this->assertStringContainsString('@forelse (($businessTripReimbursementRows ?? collect()) as $reimbursementItem)', $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripReimbursementSummary['total_label'] ?? 'Rp 0' }}", $businessTripDetailView);
        $this->assertStringNotContainsString("['date' => '10 Jun 2026', 'title' => 'Transportation'", $businessTripDetailView);
        $this->assertStringContainsString('@forelse (($businessTripCashAdvanceRows ?? collect()) as $cashAdvanceRow)', $businessTripDetailView);
        $this->assertStringContainsString("{{ \$cashAdvanceRow['category_label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripCashAdvanceSummary['total_payment_label'] ?? 'Rp 0' }}", $businessTripDetailView);
        $this->assertStringContainsString("@if (! empty(\$cashAdvanceRow['has_approved_amount']))", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$cashAdvanceRow['amount_approved_label'] ?? (\$cashAdvanceRow['payment_amount_label'] ?? '-') }}", $businessTripDetailView);
        $this->assertStringNotContainsString("@elseif (! empty(\$cashAdvanceRow['amount_realized_label']))", $businessTripDetailView);
        $this->assertStringContainsString('<span class="text-gray">-</span>', $businessTripDetailView);
        $this->assertStringNotContainsString('Waiting Cash Advance Approval', $businessTripDetailView);
        $this->assertStringContainsString('@foreach (($businessTripRequestFinancialRows ?? collect()) as $businessTripRequestFinancialRow)', $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripRequestFinancialRow['label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripRequestFinancialRow['amount_label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("\$businessTripRequestFinancialRow['description_lines'] ?? []", $businessTripDetailView);
        $this->assertStringContainsString('@foreach (($businessTripRequestStatusRows ?? collect()) as $businessTripStatusRow)', $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripStatusRow['label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$businessTripStatusRow['status_label'] ?? 'Pending' }}", $businessTripDetailView);
        $this->assertStringNotContainsString('For local transport, meals, and client entertainment', $businessTripDetailView);
        $this->assertStringNotContainsString("@foreach (['Status Cash Advance', 'Status Reimbursement', 'Status Incentive']", $businessTripDetailView);
        $this->assertStringContainsString('<span class="badge badge-sm badge-warning light">Pending</span>', $businessTripDetailView);
        $this->assertStringContainsString('<div class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center" style="min-height: 220px;">', $businessTripDetailView);
        $this->assertStringContainsString('Expense details will appear after cash advance approval.', $businessTripDetailView);
        $this->assertStringContainsString('Cash advance details will appear after staff submits the cash advance request in Phase 2.', $businessTripDetailView);
        $this->assertStringContainsString('Reimbursement details will appear after staff submits the required report and attachments.', $businessTripDetailView);
        $this->assertStringNotContainsString('Cash advance details will appear after cash advance approval.', $businessTripDetailView);
        $this->assertStringNotContainsString('Reimbursement details will appear after cash advance approval.', $businessTripDetailView);
        $this->assertStringContainsString('@foreach (($businessTripExpenseSummaryRows ?? collect()) as $expenseSummaryRow)', $businessTripDetailView);
        $this->assertStringContainsString("{{ \$expenseSummaryRow['label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$expenseSummaryRow['amount_label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$expenseSummaryRow['amount_class'] ?? 'text-gray' }} fw-semibold", $businessTripDetailView);
        $this->assertStringContainsString("@if (! empty(\$expenseSummaryRow['has_bottom_divider']))", $businessTripDetailView);
        $this->assertStringContainsString('<hr class="my-2">', $businessTripDetailView);
        $this->assertStringNotContainsString("['Total Expenses', 'Cash Advance', 'Balance Due', 'Trip Incentive', 'Total Payment']", $businessTripDetailView);
        $this->assertStringContainsString('<div class="col-md-4 col-12"><span>Total Payment</span></div>', $businessTripDetailView);
        $this->assertStringContainsString('<div class="col-md-4 col-12"><span>Total</span></div>', $businessTripDetailView);
        $this->assertStringNotContainsString('<span class="text-gray fw-semibold">-</span>', $businessTripDetailView);
        $this->assertStringContainsString('@if ($canUseBusinessTripActionButtons)', $businessTripDetailView);
        $this->assertStringContainsString('aria-disabled="true" tabindex="-1">Update Details</a>', $businessTripDetailView);
        $this->assertStringContainsString('aria-disabled="true" tabindex="-1">Cash Advance</a>', $businessTripDetailView);
        $this->assertStringContainsString('aria-disabled="true" tabindex="-1">Reimbursement</a>', $businessTripDetailView);
        $this->assertStringContainsString('@forelse (($businessTripLifecycleTracker ?? collect()) as $lifecyclePhase)', $businessTripDetailView);
        $this->assertStringContainsString("@foreach ((\$lifecyclePhase['items'] ?? collect()) as \$lifecycleItem)", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$lifecyclePhase['title'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$lifecycleItem['step_order'] ?? '-' }}. {{ \$lifecycleItem['title'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$lifecycleItem['datetime_label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$lifecycleItem['actor_label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringContainsString("{{ \$lifecycleItem['status_label'] ?? '-' }}", $businessTripDetailView);
        $this->assertStringNotContainsString('businessTripLifecycleValues', $businessTripDetailView);
        $this->assertStringNotContainsString('$businessTripLifecycleTracker = collect([', $businessTripDetailView);
        $this->assertStringNotContainsString('$businessTripLifecycleEmployeeName', $businessTripDetailView);
        $this->assertStringNotContainsString('Description :', $businessTripDetailView);
        $this->assertStringNotContainsString('Thomas Jefferson', $businessTripDetailView);
        $this->assertStringNotContainsString('Michael Scott', $businessTripDetailView);
        $this->assertStringNotContainsString('Surabaya, Jawa Timur', $businessTripDetailView);
        $this->assertStringContainsString("route('attendance.business-trips.cash-advances.create', \$businessTrip)", $businessTripDetailView);
        $this->assertStringContainsString("route('attendance.business-trips.reimbursements.create', \$businessTrip)", $businessTripDetailView);
        $this->assertStringContainsString("route('attendance.business-trips.cash-advances.store', \$businessTrip)", $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('method="POST"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('@csrf', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('name="cash_advance_ids[]"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('enctype="multipart/form-data"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('name="existing_attachment_paths[]"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('name="request_attachments[]"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('Current attachment', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString("route('attendance.business-trips.show', \$businessTrip)", $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('id="businessTripCashAdvanceRequestRows"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('id="businessTripCashAdvanceFinanceRows"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-request-row', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-finance-row', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('$cashAdvanceFinanceApproved = (bool) ($cashAdvanceRow[\'is_approved\'] ?? false);', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString("value=\"{{ \$cashAdvanceFinanceApproved ? (\$cashAdvanceRow['finance_date'] ?? '') : '' }}\"", $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString("value=\"{{ \$cashAdvanceFinanceApproved ? (\$cashAdvanceRow['amount_approved'] ?? '') : '' }}\"", $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('<option value="" @selected(! $cashAdvanceFinanceApproved)>Breakdown</option>', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-add', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-remove', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('function addCashAdvanceRow()', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('function removeCashAdvanceRow($requestRow)', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('Date Range Needed', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-date-picker', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-currency-input', $businessTripCashAdvanceCreateView);
        $this->assertStringNotContainsString('singleDatePicker: true', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString("picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY')", $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString("return 'Rp. ' + numericValue", $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('initializeCashAdvanceDatePickers($requestRow)', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('initializeCashAdvanceCurrencyInputs($requestRow)', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('function resetClonedCashAdvanceSelectPickers($scope)', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('function initializeCashAdvanceSelectPickers($scope)', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('$select.insertBefore($wrapper);', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('resetClonedCashAdvanceSelectPickers($requestRow)', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('initializeCashAdvanceSelectPickers($requestRow)', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('placeholder="Rp. 0"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('<button type="submit" class="btn light btn-success mb-2 btn-lg">Submit</button>', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('return new BusinessTripCashAdvance', $businessTripCashAdvanceController);
        $this->assertStringContainsString("'date_needed_until' => \$cashAdvanceRow['date_needed_until']", $businessTripCashAdvanceController);
        $this->assertStringContainsString('private function parseCashAdvanceDateRange(string $dateRange): array', $businessTripCashAdvanceController);
        $this->assertStringContainsString('private function formatCashAdvanceDateRange(BusinessTripCashAdvance $cashAdvance): string', $businessTripCashAdvanceController);
        $this->assertStringContainsString('BusinessTripLifecycleLog::query()', $businessTripCashAdvanceController);
        $this->assertStringContainsString('private function markCashAdvanceSubmitted(BusinessTrip $businessTrip, ?User $actor, Collection $cashAdvanceRows): void', $businessTripCashAdvanceController);
        $this->assertStringContainsString('private function syncTripReportLifecycleFromCashAdvances(BusinessTrip $businessTrip, ?User $actor): void', $businessTripCashAdvanceController);
        $this->assertStringContainsString("'event_key' => 'trip_report'", $businessTripCashAdvanceController);
        $this->assertStringContainsString("'event_key' => 'reimbursement_submitted'", $businessTripCashAdvanceController);
        $this->assertStringContainsString("'status' => 'waiting'", $businessTripCashAdvanceController);
        $this->assertStringContainsString("'status' => 'complete'", $businessTripCashAdvanceController);
        $this->assertStringContainsString("'status' => 'pending'", $businessTripCashAdvanceController);
        $this->assertStringContainsString("'actor_id' => \$actor?->id", $businessTripCashAdvanceController);
        $this->assertStringContainsString("'happened_at' => now()", $businessTripCashAdvanceController);
        $this->assertStringContainsString("route('attendance.business-trips.show', \$businessTrip)", $businessTripReimbursementCreateView);
        $this->assertStringContainsString("route('attendance.business-trips.reimbursements.store', \$businessTrip)", $businessTripReimbursementCreateView);
        $this->assertStringContainsString('enctype="multipart/form-data"', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('method="POST"', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('@csrf', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('name="reimbursement_ids[]"', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('name="existing_receipt_paths[]"', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('<button type="submit" class="btn light btn-success mb-2 btn-lg">Submit</button>', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('id="businessTripReimbursementRows"', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('business-trip-reimbursement-row', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('business-trip-reimbursement-add', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('business-trip-reimbursement-remove', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('function addReimbursementRow()', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('function removeReimbursementRow($row)', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('business-trip-reimbursement-date-picker', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('business-trip-reimbursement-currency-input', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('function initializeReimbursementDatePickers($scope)', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('function initializeReimbursementCurrencyInputs($scope)', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('initializeReimbursementDatePickers($row)', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('initializeReimbursementCurrencyInputs($row)', $businessTripReimbursementCreateView);
        $this->assertStringContainsString('Current receipt', $businessTripReimbursementCreateView);
        $this->assertStringNotContainsString('attendance-business-trip.html', $businessTripCreateView);
        $this->assertStringNotContainsString('data-bs-target="#create"', $businessTripView);
        $this->assertStringNotContainsString('data-bs-target="#reimbursement"', $businessTripCreateView);
        $this->assertStringNotContainsString('Data Perjalanan Dinas', $businessTripView);
        $this->assertStringNotContainsString('id="myTable"', $businessTripView);
        $this->assertStringNotContainsString('Submit Dinas', $businessTripView);
    }

    public function test_business_trip_location_proxy_uses_wilayah_api(): void
    {
        Http::fake([
            'https://wilayah.id/api/provinces.json' => Http::response([
                'data' => [
                    ['code' => '31', 'name' => 'DKI Jakarta'],
                ],
            ]),
            'https://wilayah.id/api/regencies/31.json' => Http::response([
                'data' => [
                    ['code' => '31.71', 'name' => 'Kota Administrasi Jakarta Pusat'],
                ],
            ]),
        ]);

        $this->assertSame(
            [['code' => '31', 'name' => 'DKI Jakarta']],
            app(BusinessTripController::class)->provinces()->getData(true)['data']
        );
        $this->assertSame(
            [['code' => '31.71', 'name' => 'Kota Administrasi Jakarta Pusat']],
            app(BusinessTripController::class)->regencies('31')->getData(true)['data']
        );

        Http::assertSentCount(2);
    }

    public function test_business_trip_summary_uses_lifecycle_dates_and_financial_status(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-05 09:00:00', 'Asia/Jakarta'));

        try {
            $pendingTrip = new BusinessTrip([
                'start_date' => '2026-06-10',
                'end_date' => '2026-06-15',
                'total_days' => 6,
                'approval_status' => 'approved',
                'payment_status' => 'pending',
            ]);
            $pendingTrip->setRelation('cashAdvances', collect([
                new BusinessTripCashAdvance([
                    'status' => 'approved',
                    'amount_requested' => 1450000,
                    'amount_approved' => null,
                ]),
                new BusinessTripCashAdvance([
                    'status' => 'rejected',
                    'amount_requested' => 999999,
                    'amount_approved' => null,
                ]),
            ]));
            $pendingTrip->setRelation('reimbursements', collect([
                new BusinessTripReimbursement([
                    'status' => 'pending',
                    'amount' => 1150000,
                    'amount_approved' => null,
                ]),
                new BusinessTripReimbursement([
                    'status' => 'approved',
                    'amount' => 200000,
                    'amount_approved' => null,
                ]),
            ]));
            $pendingTrip->setRelation('lifecycleLogs', collect([
                new BusinessTripLifecycleLog([
                    'event_key' => 'supervisor_review',
                    'status' => 'pending',
                ]),
                new BusinessTripLifecycleLog([
                    'event_key' => 'trip_report',
                    'status' => 'waiting',
                ]),
                new BusinessTripLifecycleLog([
                    'event_key' => 'payment_distribution',
                    'status' => 'waiting',
                ]),
            ]));

            $overdueTrip = new BusinessTrip([
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-03',
                'total_days' => 3,
                'approval_status' => 'approved',
                'payment_status' => 'pending',
            ]);
            $overdueTrip->setRelation('cashAdvances', collect());
            $overdueTrip->setRelation('reimbursements', collect());
            $overdueTrip->setRelation('lifecycleLogs', collect([
                new BusinessTripLifecycleLog([
                    'event_key' => 'trip_report',
                    'status' => 'waiting',
                ]),
                new BusinessTripLifecycleLog([
                    'event_key' => 'payment_distribution',
                    'status' => 'waiting',
                ]),
            ]));

            $settledTrip = new BusinessTrip([
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-02',
                'total_days' => 2,
                'approval_status' => 'approved',
                'payment_status' => 'pending',
            ]);
            $settledTrip->setRelation('cashAdvances', collect());
            $settledTrip->setRelation('reimbursements', collect());
            $settledTrip->setRelation('lifecycleLogs', collect([
                new BusinessTripLifecycleLog([
                    'event_key' => 'trip_report',
                    'status' => 'complete',
                ]),
                new BusinessTripLifecycleLog([
                    'event_key' => 'payment_distribution',
                    'status' => 'complete',
                ]),
            ]));

            $summaryMethod = new ReflectionMethod(BusinessTripController::class, 'buildBusinessTripSummary');
            $summary = $summaryMethod->invoke(new BusinessTripController, collect([
                $pendingTrip,
                $overdueTrip,
                $settledTrip,
            ]));

            $this->assertSame(3, $summary['total_trips']);
            $this->assertSame(11, $summary['total_days_away']);
            $this->assertSame(1, $summary['pending_approvals']);
            $this->assertSame(1, $summary['upcoming_scheduled']);
            $this->assertSame('Rp 1.450.000', $summary['active_cash_advance']);
            $this->assertSame('Rp 1.150.000', $summary['pending_reimbursement']);
            $this->assertSame(1, $summary['overdue_reports']);
            $this->assertSame(1, $summary['successfully_settled']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_business_trip_expense_summary_uses_cash_advance_reimbursement_and_incentive(): void
    {
        $businessTrip = new BusinessTrip([
            'total_days' => 3,
        ]);
        $businessTrip->setRelation('reimbursements', collect([
            new BusinessTripReimbursement([
                'status' => 'pending',
                'amount' => 2500000,
                'amount_approved' => null,
            ]),
            new BusinessTripReimbursement([
                'status' => 'rejected',
                'amount' => 500000,
                'amount_approved' => null,
            ]),
        ]));
        $cashAdvanceRows = collect([
            [
                'is_approved' => true,
                'amount_approved' => 2500000,
            ],
            [
                'is_approved' => false,
                'amount_approved' => 1000000,
            ],
        ]);
        $summaryMethod = new ReflectionMethod(BusinessTripController::class, 'buildBusinessTripExpenseSummaryRows');
        $summaryRows = $summaryMethod->invoke(new BusinessTripController, $businessTrip, $cashAdvanceRows);

        $this->assertSame([
            'Total Expenses' => 'Rp 5.000.000',
            'Cash Advance' => 'Rp 2.500.000',
            'Balance Due' => 'Rp 2.500.000',
            'Trip Incentive' => 'Rp 300.000',
            'Total Payment' => 'Rp 2.800.000',
        ], $summaryRows->pluck('amount_label', 'label')->all());
        $this->assertSame('Reimbursement to Employee', $summaryRows->firstWhere('label', 'Balance Due')['description']);
        $this->assertSame('Rp 100.000 x 3 days', $summaryRows->firstWhere('label', 'Trip Incentive')['description']);
        $this->assertSame('Company to Employee', $summaryRows->firstWhere('label', 'Total Payment')['description']);
        $this->assertSame('text-danger', $summaryRows->firstWhere('label', 'Cash Advance')['amount_class']);
        $this->assertSame('text-success', $summaryRows->firstWhere('label', 'Trip Incentive')['amount_class']);
        $this->assertSame('text-success', $summaryRows->firstWhere('label', 'Total Payment')['amount_class']);
        $this->assertTrue($summaryRows->firstWhere('label', 'Cash Advance')['has_bottom_divider']);
    }

    public function test_business_trip_approved_expense_breakdown_uses_only_approved_cash_advance(): void
    {
        $cashAdvanceRows = collect([
            [
                'is_approved' => true,
                'category' => 'transportation',
                'amount_approved' => 1000000,
                'notes' => 'Flight ticket',
            ],
            [
                'is_approved' => false,
                'category' => 'accommodation',
                'amount_approved' => 900000,
                'notes' => 'Pending hotel',
            ],
        ]);
        $breakdownMethod = new ReflectionMethod(BusinessTripController::class, 'buildBusinessTripApprovedExpenseBreakdownRows');
        $breakdownRows = $breakdownMethod->invoke(new BusinessTripController, $cashAdvanceRows)->keyBy('label');

        $this->assertSame('Rp 1.000.000', $breakdownRows->get('Transportation')['amount_label']);
        $this->assertSame(['Flight ticket'], $breakdownRows->get('Transportation')['description_lines']);
        $this->assertSame('-', $breakdownRows->get('Accommodation')['amount_label']);
        $this->assertFalse($breakdownRows->get('Accommodation')['has_value']);
        $this->assertFalse($breakdownRows->has('Parking'));
    }

    public function test_business_trip_request_financial_and_status_rows_use_real_values(): void
    {
        $businessTrip = new BusinessTrip([
            'total_days' => 4,
            'payment_status' => 'paid',
        ]);
        $businessTrip->setRelation('reimbursements', collect([
            new BusinessTripReimbursement([
                'status' => 'approved',
            ]),
        ]));
        $cashAdvanceRows = collect([
            [
                'is_approved' => true,
                'amount_approved' => 1500000,
                'notes' => 'Flight ticket',
            ],
            [
                'is_approved' => true,
                'amount_approved' => 500000,
                'notes' => 'Airport taxi',
            ],
        ]);
        $financialMethod = new ReflectionMethod(BusinessTripController::class, 'buildBusinessTripRequestFinancialRows');
        $statusMethod = new ReflectionMethod(BusinessTripController::class, 'buildBusinessTripRequestStatusRows');
        $financialRows = $financialMethod->invoke(new BusinessTripController, $businessTrip, $cashAdvanceRows)->keyBy('label');
        $statusRows = $statusMethod->invoke(new BusinessTripController, $businessTrip, $cashAdvanceRows)->keyBy('label');

        $this->assertSame('Rp 2.000.000', $financialRows->get('Requested Cash Advance')['amount_label']);
        $this->assertSame(['Flight ticket', 'Airport taxi'], $financialRows->get('Requested Cash Advance')['description_lines']);
        $this->assertSame('Rp 400.000', $financialRows->get('Business Trip Incentive')['amount_label']);
        $this->assertSame(['Rp 100.000 x 4 days'], $financialRows->get('Business Trip Incentive')['description_lines']);
        $this->assertSame('Approved', $statusRows->get('Status Cash Advance')['status_label']);
        $this->assertSame('Approved', $statusRows->get('Status Reimbursement')['status_label']);
        $this->assertSame('Paid', $statusRows->get('Status Incentive')['status_label']);
        $this->assertSame('badge-success light', $statusRows->get('Status Incentive')['badge_class']);
    }

    public function test_business_trip_cash_advance_summary_is_success_when_expenses_equal_cash_advance(): void
    {
        $businessTrip = new BusinessTrip([
            'total_days' => 1,
        ]);
        $businessTrip->setRelation('reimbursements', collect());
        $cashAdvanceRows = collect([
            [
                'is_approved' => true,
                'amount_approved' => 2500000,
            ],
        ]);
        $summaryMethod = new ReflectionMethod(BusinessTripController::class, 'buildBusinessTripExpenseSummaryRows');
        $summaryRows = $summaryMethod->invoke(new BusinessTripController, $businessTrip, $cashAdvanceRows);

        $this->assertSame('text-success', $summaryRows->firstWhere('label', 'Cash Advance')['amount_class']);
    }

    public function test_business_trip_reimbursement_button_depends_on_reimbursement_submitted_pending_status(): void
    {
        $businessTrip = new BusinessTrip([
            'approval_status' => 'approved',
        ]);
        $businessTrip->setRelation('cashAdvances', collect());
        $businessTrip->setRelation('reimbursements', collect());
        $businessTrip->setRelation('lifecycleLogs', collect([
            new BusinessTripLifecycleLog([
                'event_key' => 'reimbursement_submitted',
                'status' => 'waiting',
            ]),
        ]));

        $permissionsMethod = new ReflectionMethod(BusinessTripController::class, 'buildBusinessTripDetailPermissions');
        $permissions = $permissionsMethod->invoke(new BusinessTripController, $businessTrip);

        $this->assertFalse($permissions['can_use_reimbursement_button']);

        $businessTrip->setRelation('lifecycleLogs', collect([
            new BusinessTripLifecycleLog([
                'event_key' => 'reimbursement_submitted',
                'status' => 'pending',
            ]),
        ]));
        $permissions = $permissionsMethod->invoke(new BusinessTripController, $businessTrip);

        $this->assertTrue($permissions['can_use_reimbursement_button']);
    }

    public function test_business_trip_trip_execution_pending_lifecycle_uses_now_with_trip_date_range(): void
    {
        $businessTrip = new BusinessTrip([
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
        ]);
        $lifecycleLog = new BusinessTripLifecycleLog([
            'event_key' => 'trip_execution',
            'step_order' => 5,
            'title' => 'Business Trip in Progress',
            'status' => 'pending',
            'happened_at' => null,
            'metadata' => [
                'actor_label' => 'System',
            ],
        ]);
        $lifecycleLog->setRelation('actor', $this->businessTripStaffActor());

        $lifecycleMethod = new ReflectionMethod(BusinessTripController::class, 'lifecycleValueFromLog');
        $lifecycleValue = $lifecycleMethod->invoke(new BusinessTripController, $lifecycleLog, $businessTrip);

        $this->assertSame('Now', $lifecycleValue['date_label']);
        $this->assertSame('10 June 2026 - 12 June 2026', $lifecycleValue['datetime_label']);
        $this->assertSame('staff72', $lifecycleValue['actor_label']);
        $this->assertSame('Pending', $lifecycleValue['status_label']);
    }

    public function test_business_trip_trip_execution_completed_lifecycle_uses_trip_end_date(): void
    {
        $businessTrip = new BusinessTrip([
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-15',
        ]);
        $lifecycleLog = new BusinessTripLifecycleLog([
            'event_key' => 'trip_execution',
            'step_order' => 5,
            'title' => 'Business Trip in Progress',
            'status' => 'complete',
            'happened_at' => null,
            'metadata' => [
                'actor_label' => 'System',
            ],
        ]);
        $lifecycleLog->setRelation('actor', $this->businessTripStaffActor());

        $lifecycleMethod = new ReflectionMethod(BusinessTripController::class, 'lifecycleValueFromLog');
        $lifecycleValue = $lifecycleMethod->invoke(new BusinessTripController, $lifecycleLog, $businessTrip);

        $this->assertSame('15 Jun', $lifecycleValue['date_label']);
        $this->assertSame('10 June 2026 - 15 June 2026', $lifecycleValue['datetime_label']);
        $this->assertSame('staff72', $lifecycleValue['actor_label']);
        $this->assertSame('Complete', $lifecycleValue['status_label']);
    }

    public function test_business_trip_trip_execution_without_actor_stays_empty_until_cron_updates_table(): void
    {
        $businessTrip = new BusinessTrip([
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-15',
        ]);
        $lifecycleLog = new BusinessTripLifecycleLog([
            'event_key' => 'trip_execution',
            'step_order' => 5,
            'title' => 'Business Trip in Progress',
            'status' => 'pending',
            'happened_at' => null,
        ]);
        $lifecycleLog->setRelation('actor', null);

        $lifecycleMethod = new ReflectionMethod(BusinessTripController::class, 'lifecycleValueFromLog');
        $lifecycleValue = $lifecycleMethod->invoke(new BusinessTripController, $lifecycleLog, $businessTrip);

        $this->assertSame('-', $lifecycleValue['actor_label']);
    }

    public function test_business_trip_trip_report_pending_without_actor_stays_empty_until_cron_updates_table(): void
    {
        $businessTrip = new BusinessTrip([
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-15',
        ]);
        $lifecycleLog = new BusinessTripLifecycleLog([
            'event_key' => 'trip_report',
            'step_order' => 6,
            'title' => 'Trip Report & Task Submitted',
            'status' => 'pending',
            'happened_at' => null,
        ]);
        $lifecycleLog->setRelation('actor', null);

        $lifecycleMethod = new ReflectionMethod(BusinessTripController::class, 'lifecycleValueFromLog');
        $lifecycleValue = $lifecycleMethod->invoke(new BusinessTripController, $lifecycleLog, $businessTrip);

        $this->assertSame('-', $lifecycleValue['actor_label']);
        $this->assertSame('Pending', $lifecycleValue['datetime_label']);
    }

    private function businessTripStaffActor(): User
    {
        $user = new User([
            'id' => 'USER-1',
            'username' => 'staff72',
            'email' => 'staff72@example.test',
        ]);
        $user->setRelation('employee', null);
        $user->setRelation('userProfile', null);

        return $user;
    }
}
