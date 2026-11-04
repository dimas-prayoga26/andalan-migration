<?php

namespace Tests\Feature;

use App\Models\BusinessTrip;
use App\Models\BusinessTripReimbursement;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessTripReimbursementStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_and_update_business_trip_reimbursements(): void
    {
        Storage::fake('public');
        [$user, $employee] = $this->createAuthenticatedUserWithEmployee('staff_reimbursement');
        $businessTrip = $this->createBusinessTrip($employee);

        foreach ([
            ['trip_report', 6, 'Trip Report & Task Submitted'],
            ['reimbursement_submitted', 7, 'Reimbursement Submitted'],
        ] as [$eventKey, $stepOrder, $title]) {
            DB::table('business_trip_lifecycle_logs')->insert([
                'id' => $eventKey === 'trip_report'
                    ? '11111111-1111-4111-8111-111111111111'
                    : '22222222-2222-4222-8222-222222222222',
                'business_trip_id' => $businessTrip->id,
                'phase' => 'post_trip_settlement',
                'event_key' => $eventKey,
                'step_order' => $stepOrder,
                'title' => $title,
                'status' => $eventKey === 'trip_report' ? 'complete' : 'pending',
                'actor_id' => $eventKey === 'trip_report' ? $user->id : null,
                'happened_at' => $eventKey === 'trip_report' ? now() : null,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->post(route('attendance.business-trips.reimbursements.store', $businessTrip), [
                'reimbursement_ids' => ['', ''],
                'reimbursement_dates' => ['12/06/2026', '13/06/2026'],
                'reimbursement_amounts' => ['Rp. 1.000.000', 'Rp. 500.000'],
                'reimbursement_categories' => ['accommodation', 'local_transport'],
                'reimbursement_notes' => ['Hotel receipt', 'Taxi receipt'],
                'existing_receipt_paths' => ['', ''],
                'reimbursement_receipts' => [
                    UploadedFile::fake()->create('hotel.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->image('taxi.jpg'),
                ],
            ]);

        $response->assertRedirect(route('attendance.business-trips.show', $businessTrip));

        $this->assertSame(2, BusinessTripReimbursement::query()->where('business_trip_id', $businessTrip->id)->count());
        $this->assertDatabaseHas('business_trip_reimbursements', [
            'business_trip_id' => $businessTrip->id,
            'requested_by' => $employee->id,
            'expense_date' => '2026-06-12',
            'category' => 'accommodation',
            'amount' => 1000000,
            'notes' => 'Hotel receipt',
            'status' => 'pending',
        ]);

        $reimbursement = BusinessTripReimbursement::query()
            ->where('business_trip_id', $businessTrip->id)
            ->where('category', 'accommodation')
            ->firstOrFail();

        Storage::disk('public')->assertExists($reimbursement->receipt_path);

        $this->assertDatabaseHas('business_trip_lifecycle_logs', [
            'business_trip_id' => $businessTrip->id,
            'event_key' => 'trip_report',
            'status' => 'complete',
            'actor_id' => $user->id,
        ]);
        $this->assertDatabaseHas('business_trip_lifecycle_logs', [
            'business_trip_id' => $businessTrip->id,
            'event_key' => 'reimbursement_submitted',
            'status' => 'complete',
            'actor_id' => $user->id,
        ]);

        $this
            ->actingAs($user)
            ->post(route('attendance.business-trips.reimbursements.store', $businessTrip), [
                'reimbursement_ids' => [$reimbursement->id],
                'reimbursement_dates' => ['14/06/2026'],
                'reimbursement_amounts' => ['Rp. 1.500.000'],
                'reimbursement_categories' => ['transportation'],
                'reimbursement_notes' => ['Train ticket'],
                'existing_receipt_paths' => [$reimbursement->receipt_path],
                'reimbursement_receipts' => [],
            ])
            ->assertRedirect(route('attendance.business-trips.show', $businessTrip));

        $this->assertSame(1, BusinessTripReimbursement::query()->where('business_trip_id', $businessTrip->id)->count());
        $this->assertDatabaseHas('business_trip_reimbursements', [
            'id' => $reimbursement->id,
            'business_trip_id' => $businessTrip->id,
            'expense_date' => '2026-06-14',
            'category' => 'transportation',
            'amount' => 1500000,
            'notes' => 'Train ticket',
            'receipt_path' => $reimbursement->receipt_path,
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

    private function createBusinessTrip(Employee $employee): BusinessTrip
    {
        return BusinessTrip::query()->create([
            'employee_id' => $employee->id,
            'request_number' => 'BT-REIMBURSEMENT-001',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
            'total_days' => 3,
            'destination_zone' => 'Intercity (Luar Kota)',
            'province_destination' => 'Jawa Barat',
            'city_destination' => 'Bandung',
            'purpose' => 'Client visit',
            'daily_rate' => 100000,
            'total_allowance' => 300000,
            'approval_status' => 'approved',
            'payment_status' => 'pending',
        ]);
    }
}
