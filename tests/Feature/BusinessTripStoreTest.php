<?php

namespace Tests\Feature;

use App\Http\Controllers\BusinessTripController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BusinessTripStoreTest extends TestCase
{
    public function test_business_trip_store_route_and_create_form_are_wired(): void
    {
        $storeRoute = Route::getRoutes()->getByName('attendance.business-trips.store');
        $createView = File::get(resource_path('views/attendance/business-trips/create.blade.php'));
        $controller = File::get(app_path('Http/Controllers/BusinessTripController.php'));

        $this->assertNotNull($storeRoute);
        $this->assertSame('POST', implode('|', $storeRoute->methods()));
        $this->assertSame(BusinessTripController::class.'@store', $storeRoute->getActionName());
        $this->assertStringContainsString('method="POST" action="{{ route(\'attendance.business-trips.store\') }}"', $createView);
        $this->assertStringContainsString('@csrf', $createView);
        $this->assertStringContainsString('name="purpose"', $createView);
        $this->assertStringContainsString('name="trip_type"', $createView);
        $this->assertStringContainsString('name="province_destination"', $createView);
        $this->assertStringContainsString('name="city_destination"', $createView);
        $this->assertStringContainsString('name="transportation_arrangement"', $createView);
        $this->assertStringContainsString('name="accommodation_arrangement"', $createView);
        $this->assertStringContainsString('name="transportation_mode"', $createView);
        $this->assertStringContainsString('name="departure_time_window"', $createView);
        $this->assertStringContainsString('BusinessTrip::query()->create', $controller);
        $this->assertStringContainsString("'total_days' => \$totalDays", $controller);
        $this->assertStringContainsString("'approval_status' => 'pending'", $controller);
        $this->assertStringContainsString("'payment_status' => 'pending'", $controller);
    }
}
