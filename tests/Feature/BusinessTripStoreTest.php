<?php

namespace Tests\Feature;

use App\Http\Controllers\StaffAttendance\AttendanceBusinessTripController;
use App\Models\BusinessTrip;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BusinessTripStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_trip_store_route_and_create_form_are_wired(): void
    {
        $storeRoute = Route::getRoutes()->getByName('attendance.business-trips.store');
        $createView = File::get(resource_path('views/staff_attendance/business-trips/create.blade.php'));
        $controller = File::get(app_path('Http/Controllers/StaffAttendance/AttendanceBusinessTripController.php'));

        $this->assertNotNull($storeRoute);
        $this->assertSame('POST', implode('|', $storeRoute->methods()));
        $this->assertSame(AttendanceBusinessTripController::class.'@store', $storeRoute->getActionName());
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
        $this->assertStringContainsString("Rule::requiredIf(\$request->input('transportation_arrangement') === 'booked_by_ga')", $controller);
        $this->assertStringContainsString("Rule::requiredIf(\$request->input('accommodation_arrangement') === 'booked_by_ga')", $controller);
        $this->assertStringNotContainsString("Rule::requiredIf(\$request->input('transportation_arrangement') === 'self_managed')", $controller);
        $this->assertStringNotContainsString("Rule::requiredIf(\$request->input('accommodation_arrangement') === 'self_managed')", $controller);
        $this->assertStringContainsString("'transportation_mode' => \$validated['transportation_mode'] ?? null", $controller);
        $this->assertStringContainsString('BusinessTrip::query()->create', $controller);
        $this->assertStringContainsString("'total_days' => \$totalDays", $controller);
        $this->assertStringContainsString("'approval_status' => 'pending'", $controller);
        $this->assertStringContainsString("'payment_status' => 'pending'", $controller);
        $this->assertStringContainsString('DB::transaction(function ()', $controller);
        $this->assertStringContainsString('$submittedAt = now();', $controller);
        $this->assertStringNotContainsString('$submittedAt = now(\'Asia/Jakarta\');', $controller);
        $this->assertStringContainsString('$this->createInitialLifecycleLogs($businessTrip, $actorUser, $submittedAt)', $controller);
        $this->assertStringContainsString('private function createInitialLifecycleLogs(BusinessTrip $businessTrip, ?User $actor, Carbon $submittedAt): void', $controller);
        $this->assertStringContainsString('private const BUSINESS_TRIP_LIFECYCLE_STEPS = [', $controller);
        $this->assertStringContainsString('BusinessTripLifecycleLog::query()->create', $controller);
        $this->assertStringContainsString("'title' => 'Trip Request Submitted'", $controller);
        $this->assertStringContainsString("'title' => 'Reimbursement & Incentive Distributed'", $controller);
        $this->assertStringContainsString("'phase' => 'trip_approval'", $controller);
        $this->assertStringContainsString("'event_key' => 'submitted'", $controller);
        $this->assertStringContainsString("'step_order' => 1", $controller);
        $this->assertStringContainsString("'status' => 'complete'", $controller);
        $this->assertStringContainsString("'actor_id' => \$isSubmittedStep ? \$actor?->id : null", $controller);
        $this->assertStringContainsString("'happened_at' => \$isSubmittedStep ? \$submittedAt : null", $controller);
        $this->assertStringNotContainsString("'is_completed' => true", $controller);
        $this->assertStringNotContainsString("'completed_at' => \$submittedAt", $controller);
        $this->assertStringNotContainsString("'status_label' => 'Complete'", $controller);
        $this->assertStringNotContainsString("'status_state' => 'completed'", $controller);
        $this->assertStringNotContainsString("'actor_label' => \$this->actorDisplayName(\$actor)", $controller);
    }

    public function test_staff_business_trip_create_also_stores_submitted_lifecycle_log(): void
    {
        [$user, $employee] = $this->createAuthenticatedUserWithEmployee('staff_business_trip');

        $response = $this
            ->actingAs($user)
            ->post(route('attendance.business-trips.store'), [
                'purpose' => 'Client meeting and site survey',
                'start_date' => '2026-06-10',
                'end_date' => '2026-06-12',
                'trip_type' => 'intercity',
                'province_destination' => 'Jawa Timur',
                'province_code' => '35',
                'city_destination' => 'Surabaya',
                'city_regency_code' => '35.78',
                'transportation_arrangement' => 'self_managed',
                'accommodation_arrangement' => 'self_managed',
                'transportation_mode' => 'train',
                'departure_date' => '2026-06-10',
                'departure_time_window' => 'morning',
                'check_in_date' => '2026-06-10',
                'check_out_date' => '2026-06-12',
            ]);

        $response->assertRedirect(route('attendance.business-trips'));

        $businessTrip = BusinessTrip::query()
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        $this->assertDatabaseHas('business_trips', [
            'id' => $businessTrip->id,
            'employee_id' => $employee->id,
            'purpose' => 'Client meeting and site survey',
            'approval_status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseHas('business_trip_lifecycle_logs', [
            'business_trip_id' => $businessTrip->id,
            'phase' => 'trip_approval',
            'event_key' => 'submitted',
            'step_order' => 1,
            'title' => 'Trip Request Submitted',
            'status' => 'complete',
            'actor_id' => $user->id,
        ]);

        $this->assertSame(
            9,
            DB::table('business_trip_lifecycle_logs')
                ->where('business_trip_id', $businessTrip->id)
                ->count()
        );

        $this->assertDatabaseHas('business_trip_lifecycle_logs', [
            'business_trip_id' => $businessTrip->id,
            'phase' => 'post_trip_settlement',
            'event_key' => 'payment_distribution',
            'step_order' => 9,
            'title' => 'Reimbursement & Incentive Distributed',
            'status' => 'waiting',
            'actor_id' => null,
        ]);
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function createAuthenticatedUserWithEmployee(string $username): array
    {
        $user = User::factory()->activeEmployee(['view-attendance'])->create([
        ]);

        return [$user, $user->employee];
    }
}
