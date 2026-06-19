<?php

namespace Tests\Feature;

use App\Http\Controllers\AttendanceBusinessTripController;
use App\Models\BusinessTrip;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BusinessTripStoreTest extends TestCase
{
    public function test_business_trip_store_route_and_create_form_are_wired(): void
    {
        $storeRoute = Route::getRoutes()->getByName('attendance.business-trips.store');
        $createView = File::get(resource_path('views/staff_attendance/business-trips/create.blade.php'));
        $controller = File::get(app_path('Http/Controllers/AttendanceBusinessTripController.php'));

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
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available for this database behavior test.');
        }

        $this->createBusinessTripStoreTestSchema();

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

    private function createBusinessTripStoreTestSchema(): void
    {
        foreach ([
            'business_trip_lifecycle_logs',
            'business_trips',
            'employee_pic_assignments',
            'employee_profiles',
            'employees',
            'user_profiles',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('username')->nullable()->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users', 'id')->cascadeOnDelete();
            $table->string('profile_picture')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users', 'id')->cascadeOnDelete();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('employee_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->unique()->constrained('employees', 'id')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_pic_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('supervisor_employee_id')->nullable()->constrained('employees', 'id')->cascadeOnDelete();
            $table->foreignUuid('staff_employee_id')->nullable()->constrained('employees', 'id')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('business_trips', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees', 'id')->cascadeOnDelete();
            $table->string('request_number', 50)->nullable()->unique();
            $table->foreignUuid('supervisor_employee_id')->nullable()->constrained('employees', 'id')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_days');
            $table->string('destination_zone');
            $table->string('province_destination')->nullable();
            $table->string('city_destination');
            $table->text('purpose');
            $table->string('trip_type', 50)->nullable();
            $table->string('transportation_arrangement', 50)->nullable();
            $table->string('accommodation_arrangement', 50)->nullable();
            $table->string('transportation_mode', 50)->nullable();
            $table->date('departure_date')->nullable();
            $table->string('departure_time_window', 50)->nullable();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->decimal('total_allowance', 12, 2)->default(0);
            $table->string('approval_status')->default('pending');
            $table->foreignUuid('approved_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->string('payment_status')->default('pending');
            $table->string('payment_reference')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('business_trip_lifecycle_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_trip_id')->constrained('business_trips', 'id')->cascadeOnDelete();
            $table->string('phase', 100);
            $table->string('event_key', 100);
            $table->unsignedInteger('step_order');
            $table->string('title');
            $table->string('status', 50)->default('waiting');
            $table->foreignUuid('actor_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('happened_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function createAuthenticatedUserWithEmployee(string $username): array
    {
        $user = User::query()->create([
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'status' => 'Active',
        ]);

        return [$user, $employee];
    }
}
