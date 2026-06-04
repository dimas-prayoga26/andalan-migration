<?php

namespace Tests\Feature;

use App\Http\Controllers\BusinessTripCashAdvanceController;
use App\Http\Controllers\BusinessTripController;
use App\Http\Controllers\BusinessTripReimbursementController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
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
        $businessTripReimbursementCreateRoute = Route::getRoutes()->getByName('attendance.business-trips.reimbursements.create');
        $businessTripProvincesRoute = Route::getRoutes()->getByName('attendance.business-trips.provinces');
        $businessTripRegenciesRoute = Route::getRoutes()->getByName('attendance.business-trips.regencies');
        $businessTripView = File::get(resource_path('views/attendance/business-trips/index.blade.php'));
        $businessTripCreateView = File::get(resource_path('views/attendance/business-trips/create.blade.php'));
        $businessTripDetailView = File::get(resource_path('views/attendance/business-trips/detail.blade.php'));
        $businessTripCashAdvanceCreateView = File::get(resource_path('views/attendance/business-trips/create-cash-advance.blade.php'));
        $businessTripReimbursementCreateView = File::get(resource_path('views/attendance/business-trips/create-reimbursement.blade.php'));
        $profileNavbarView = File::get(resource_path('views/attendance/layouts/profile-navbar.blade.php'));
        $businessTripController = File::get(app_path('Http/Controllers/BusinessTripController.php'));

        $this->assertNotNull($businessTripIndexRoute);
        $this->assertNotNull($businessTripCreateRoute);
        $this->assertNotNull($businessTripStoreRoute);
        $this->assertNotNull($businessTripShowRoute);
        $this->assertNotNull($businessTripCashAdvanceCreateRoute);
        $this->assertNotNull($businessTripReimbursementCreateRoute);
        $this->assertNotNull($businessTripProvincesRoute);
        $this->assertNotNull($businessTripRegenciesRoute);
        $this->assertSame(BusinessTripController::class.'@index', $businessTripIndexRoute->getActionName());
        $this->assertSame(BusinessTripController::class.'@create', $businessTripCreateRoute->getActionName());
        $this->assertSame(BusinessTripController::class.'@store', $businessTripStoreRoute->getActionName());
        $this->assertSame(BusinessTripController::class.'@show', $businessTripShowRoute->getActionName());
        $this->assertSame(BusinessTripCashAdvanceController::class.'@create', $businessTripCashAdvanceCreateRoute->getActionName());
        $this->assertSame(BusinessTripReimbursementController::class.'@create', $businessTripReimbursementCreateRoute->getActionName());
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
        $this->assertStringContainsString("'businessTripRequestDetails' => \$businessTripRequestDetails", $businessTripController);
        $this->assertStringContainsString("'businessTripDetailPermissions' => \$this->buildBusinessTripDetailPermissions(\$businessTrip)", $businessTripController);
        $this->assertStringContainsString("'businessTripLifecycleTracker' => \$this->buildBusinessTripLifecycleTracker(\$businessTrip)", $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripRequestDetails(BusinessTrip $businessTrip): array', $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripDetailPermissions(BusinessTrip $businessTrip): array', $businessTripController);
        $this->assertStringContainsString("'can_view_trip_expense_values' => \$cashAdvanceApproved", $businessTripController);
        $this->assertStringContainsString("'can_use_action_buttons' => \$supervisorReviewApproved", $businessTripController);
        $this->assertStringContainsString("businessTripLifecycleEventIsApproved(\$businessTrip, 'supervisor_review')", $businessTripController);
        $this->assertStringContainsString("businessTripLifecycleEventIsApproved(\$businessTrip, 'cash_advance_submitted')", $businessTripController);
        $this->assertStringContainsString('private function buildBusinessTripLifecycleTracker(BusinessTrip $businessTrip): Collection', $businessTripController);
        $this->assertStringContainsString('private function lifecycleValueFromLog(BusinessTripLifecycleLog $lifecycleLog): array', $businessTripController);
        $this->assertStringContainsString('private function normalizeLifecycleState(string $state): string', $businessTripController);
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
        $this->assertStringContainsString("\$canViewBusinessTripExpenseValues = (bool) (\$businessTripDetailPermissions['can_view_trip_expense_values'] ?? false);", $businessTripDetailView);
        $this->assertStringContainsString("\$canUseBusinessTripActionButtons = (bool) (\$businessTripDetailPermissions['can_use_action_buttons'] ?? false);", $businessTripDetailView);
        $this->assertStringContainsString('@if ($canViewBusinessTripExpenseValues)', $businessTripDetailView);
        $this->assertStringContainsString('<span class="text-gray">-</span>', $businessTripDetailView);
        $this->assertStringNotContainsString('Waiting Cash Advance Approval', $businessTripDetailView);
        $this->assertStringContainsString('<span>Requested Cash Advance</span>', $businessTripDetailView);
        $this->assertStringContainsString('<span>Business Trip Incentive</span>', $businessTripDetailView);
        $this->assertStringContainsString("'Status Incentive'", $businessTripDetailView);
        $this->assertStringContainsString('<span class="badge badge-sm badge-warning light">Pending</span>', $businessTripDetailView);
        $this->assertStringContainsString('<div class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center" style="min-height: 220px;">', $businessTripDetailView);
        $this->assertStringContainsString('Expense details will appear after cash advance approval.', $businessTripDetailView);
        $this->assertStringContainsString("['Total Expenses', 'Cash Advance', 'Balance Due', 'Trip Incentive', 'Total Payment']", $businessTripDetailView);
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
        $this->assertStringContainsString("route('attendance.business-trips.show', \$businessTrip)", $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('id="businessTripCashAdvanceRequestRows"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('id="businessTripCashAdvanceFinanceRows"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-request-row', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-finance-row', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-add', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-remove', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('function addCashAdvanceRow()', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('function removeCashAdvanceRow($requestRow)', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-date-picker', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('business-trip-cash-advance-currency-input', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('singleDatePicker: true', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString("return 'Rp. ' + numericValue", $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('initializeCashAdvanceDatePickers($requestRow)', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('initializeCashAdvanceCurrencyInputs($requestRow)', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString('placeholder="Rp. 0"', $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString("route('attendance.business-trips.show', \$businessTrip)", $businessTripReimbursementCreateView);
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
}
