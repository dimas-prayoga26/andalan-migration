<?php

namespace Tests\Feature;

use App\Models\BusinessTrip;
use App\Models\BusinessTripCashAdvance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessTripCashAdvanceStoreTest extends TestCase
{
    public function test_staff_can_create_and_update_business_trip_cash_advances(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available for this database behavior test.');
        }

        Storage::fake('public');
        $this->createBusinessTripCashAdvanceTestSchema();

        [$user, $employee] = $this->createAuthenticatedUserWithEmployee('staff_cash_advance');
        $businessTrip = $this->createBusinessTrip($employee);

        foreach ([
            ['11111111-1111-4111-8111-111111111111', 'pre_trip_preparation', 'cash_advance_submitted', 3, 'Cash Advance Submitted'],
            ['22222222-2222-4222-8222-222222222222', 'post_trip_settlement', 'trip_report', 6, 'Trip Report & Task Submitted'],
            ['33333333-3333-4333-8333-333333333333', 'post_trip_settlement', 'reimbursement_submitted', 7, 'Reimbursement Submitted'],
        ] as [$id, $phase, $eventKey, $stepOrder, $title]) {
            DB::table('business_trip_lifecycle_logs')->insert([
                'id' => $id,
                'business_trip_id' => $businessTrip->id,
                'phase' => $phase,
                'event_key' => $eventKey,
                'step_order' => $stepOrder,
                'title' => $title,
                'status' => $eventKey === 'trip_report' ? 'pending' : 'waiting',
                'actor_id' => null,
                'happened_at' => null,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->post(route('attendance.business-trips.cash-advances.store', $businessTrip), [
                'cash_advance_ids' => ['', ''],
                'request_dates' => ['10/06/2026 - 12/06/2026', '11/06/2026 - 11/06/2026'],
                'request_amounts' => ['Rp. 1.000.000', 'Rp. 500.000'],
                'request_breakdowns' => ['local_transport', 'meals_entertainment'],
                'request_notes' => ['Taxi airport', 'Client meals'],
                'request_amount_realized' => ['Rp. 900.000', ''],
                'existing_attachment_paths' => ['', ''],
                'request_attachments' => [
                    UploadedFile::fake()->image('taxi.jpg'),
                    null,
                ],
            ]);

        $response->assertRedirect(route('attendance.business-trips.show', $businessTrip));

        $this->assertSame(2, BusinessTripCashAdvance::query()->where('business_trip_id', $businessTrip->id)->count());
        $this->assertDatabaseHas('business_trip_cash_advances', [
            'business_trip_id' => $businessTrip->id,
            'requested_by' => $employee->id,
            'date_needed' => '2026-06-10',
            'date_needed_until' => '2026-06-12',
            'category' => 'local_transport',
            'amount_requested' => 1000000,
            'amount_realized' => 900000,
            'notes' => 'Taxi airport',
            'status' => 'pending',
        ]);
        $cashAdvance = BusinessTripCashAdvance::query()
            ->where('business_trip_id', $businessTrip->id)
            ->where('category', 'local_transport')
            ->firstOrFail();
        Storage::disk('public')->assertExists($cashAdvance->attachment_path);
        $this->assertDatabaseHas('business_trip_lifecycle_logs', [
            'business_trip_id' => $businessTrip->id,
            'event_key' => 'cash_advance_submitted',
            'status' => 'waiting',
            'actor_id' => $user->id,
        ]);
        $this->assertNotNull(
            DB::table('business_trip_lifecycle_logs')
                ->where('business_trip_id', $businessTrip->id)
                ->where('event_key', 'cash_advance_submitted')
                ->value('happened_at')
        );

        $cashAdvance = BusinessTripCashAdvance::query()
            ->where('business_trip_id', $businessTrip->id)
            ->where('category', 'local_transport')
            ->firstOrFail();

        $this
            ->actingAs($user)
            ->post(route('attendance.business-trips.cash-advances.store', $businessTrip), [
                'cash_advance_ids' => [$cashAdvance->id],
                'request_dates' => ['12/06/2026 - 13/06/2026'],
                'request_amounts' => ['Rp. 1.500.000'],
                'request_breakdowns' => ['transportation'],
                'request_notes' => ['Train ticket'],
                'request_amount_realized' => ['Rp. 1.250.000'],
                'existing_attachment_paths' => [$cashAdvance->attachment_path],
                'request_attachments' => [
                    UploadedFile::fake()->create('train.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('attendance.business-trips.show', $businessTrip));

        $this->assertSame(1, BusinessTripCashAdvance::query()->where('business_trip_id', $businessTrip->id)->count());
        $this->assertDatabaseHas('business_trip_cash_advances', [
            'id' => $cashAdvance->id,
            'business_trip_id' => $businessTrip->id,
            'date_needed' => '2026-06-12',
            'date_needed_until' => '2026-06-13',
            'category' => 'transportation',
            'amount_requested' => 1500000,
            'amount_realized' => 1250000,
            'notes' => 'Train ticket',
        ]);
        $cashAdvance->refresh();
        Storage::disk('public')->assertExists($cashAdvance->attachment_path);
        $this->assertDatabaseHas('business_trip_lifecycle_logs', [
            'business_trip_id' => $businessTrip->id,
            'event_key' => 'trip_report',
            'status' => 'complete',
            'actor_id' => $user->id,
        ]);
        $this->assertDatabaseHas('business_trip_lifecycle_logs', [
            'business_trip_id' => $businessTrip->id,
            'event_key' => 'reimbursement_submitted',
            'status' => 'pending',
            'actor_id' => null,
        ]);
    }

    private function createBusinessTripCashAdvanceTestSchema(): void
    {
        foreach ([
            'business_trip_cash_advances',
            'business_trip_lifecycle_logs',
            'business_trips',
            'employees',
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

        Schema::create('employees', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users', 'id')->cascadeOnDelete();
            $table->string('status')->default('Active');
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

        Schema::create('business_trip_cash_advances', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_trip_id')->constrained('business_trips', 'id')->cascadeOnDelete();
            $table->foreignUuid('requested_by')->constrained('employees', 'id')->cascadeOnDelete();
            $table->date('date_needed');
            $table->date('date_needed_until')->nullable();
            $table->string('category', 100);
            $table->decimal('amount_requested', 12, 2)->default(0);
            $table->decimal('amount_realized', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status', 50)->default('pending');
            $table->decimal('amount_approved', 12, 2)->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('finance_notes')->nullable();
            $table->softDeletes();
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

    private function createBusinessTrip(Employee $employee): BusinessTrip
    {
        return BusinessTrip::query()->create([
            'employee_id' => $employee->id,
            'request_number' => 'BT-TEST-001',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
            'total_days' => 3,
            'destination_zone' => 'Intercity (Luar Kota)',
            'province_destination' => 'Jawa Barat',
            'city_destination' => 'Bandung',
            'purpose' => 'Client meeting',
            'trip_type' => 'intercity',
            'transportation_arrangement' => 'self_managed',
            'accommodation_arrangement' => 'self_managed',
            'transportation_mode' => 'train',
            'approval_status' => 'approved',
            'payment_status' => 'pending',
            'daily_rate' => 0,
            'total_allowance' => 0,
        ]);
    }
}
