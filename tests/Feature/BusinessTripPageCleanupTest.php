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
        $this->assertStringContainsString("route('attendance.business-trips.cash-advances.create', \$businessTrip)", $businessTripDetailView);
        $this->assertStringContainsString("route('attendance.business-trips.reimbursements.create', \$businessTrip)", $businessTripDetailView);
        $this->assertStringContainsString("route('attendance.business-trips.show', \$businessTrip)", $businessTripCashAdvanceCreateView);
        $this->assertStringContainsString("route('attendance.business-trips.show', \$businessTrip)", $businessTripReimbursementCreateView);
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
