<?php

namespace Tests\Feature;

use App\Http\Controllers\BusinessTripController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BusinessTripPageCleanupTest extends TestCase
{
    public function test_business_trip_page_only_keeps_index_route_and_placeholder_button(): void
    {
        $businessTripIndexRoute = Route::getRoutes()->getByName('attendance.business-trips');
        $businessTripView = File::get(resource_path('views/attendance/business-trips/index.blade.php'));

        $this->assertNotNull($businessTripIndexRoute);
        $this->assertSame(BusinessTripController::class.'@index', $businessTripIndexRoute->getActionName());
        $this->assertNull(Route::getRoutes()->getByName('attendance.business-trips.datatable'));
        $this->assertFalse(method_exists(BusinessTripController::class, 'datatable'));
        $this->assertFalse(method_exists(BusinessTripController::class, 'store'));
        $this->assertStringContainsString('<h5 class="mb-0">Business Trip</h5>', $businessTripView);
        $this->assertStringContainsString('data-bs-target="#create"', $businessTripView);
        $this->assertStringContainsString('>+ Business Trip</a>', $businessTripView);
        $this->assertStringNotContainsString('Data Perjalanan Dinas', $businessTripView);
        $this->assertStringNotContainsString('id="myTable"', $businessTripView);
        $this->assertStringNotContainsString('Submit Dinas', $businessTripView);
    }
}
