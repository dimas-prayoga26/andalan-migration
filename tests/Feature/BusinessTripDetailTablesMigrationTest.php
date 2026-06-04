<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BusinessTripDetailTablesMigrationTest extends TestCase
{
    public function test_business_trip_detail_tables_and_columns_are_defined(): void
    {
        $businessTripDetailsMigration = File::get(database_path('migrations/2026_06_03_080202_add_business_trip_details_to_business_trips_table.php'));
        $expenseItemsMigration = File::get(database_path('migrations/2026_06_03_080203_create_business_trip_expense_items_table.php'));
        $cashAdvancesMigration = File::get(database_path('migrations/2026_06_03_080204_create_business_trip_cash_advances_table.php'));
        $reimbursementsMigration = File::get(database_path('migrations/2026_06_03_080204_create_business_trip_reimbursements_table.php'));
        $lifecycleLogsMigration = File::get(database_path('migrations/2026_06_03_080205_create_business_trip_lifecycle_logs_table.php'));

        $this->assertFileDoesNotExist(database_path('migrations/2026_06_04_044036_add_display_values_to_business_trip_lifecycle_logs_table.php'));

        foreach ([
            "Schema::table('business_trips'",
            "request_number', 50",
            'supervisor_employee_id',
            'province_destination',
            "trip_type', 50",
            "transportation_arrangement', 50",
            "accommodation_arrangement', 50",
            "transportation_mode', 50",
            'departure_date',
            "departure_time_window', 50",
            'check_in_date',
            'check_out_date',
            'submitted_at',
            'approved_at',
            'rejected_at',
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $businessTripDetailsMigration);
        }

        $this->assertStringContainsString("Schema::create('business_trip_expense_items'", $expenseItemsMigration);
        $this->assertStringContainsString("Schema::create('business_trip_cash_advances'", $cashAdvancesMigration);
        $this->assertStringContainsString("Schema::create('business_trip_reimbursements'", $reimbursementsMigration);

        foreach ([
            "Schema::create('business_trip_lifecycle_logs'",
            'event_key',
            'step_order',
            "status', 50",
            'actor_id',
            'happened_at',
            'metadata',
            'business_trip_lifecycle_logs_trip_event_unique',
            'business_trip_lifecycle_logs_trip_step_unique',
            'business_trip_lifecycle_logs_trip_status_index',
            'business_trip_lifecycle_logs_trip_happened_index',
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $lifecycleLogsMigration);
        }

        foreach ([
            'description',
            'is_completed',
            'completed_at',
            'actor_label',
            'status_label',
            'status_state',
            'business_trip_lifecycle_logs_trip_state_index',
        ] as $expectedFragment) {
            $this->assertStringNotContainsString($expectedFragment, $lifecycleLogsMigration);
        }

        foreach ([
            'progress_status',
            'progress_percentage',
        ] as $removedColumn) {
            $this->assertStringNotContainsString($removedColumn, $businessTripDetailsMigration.$lifecycleLogsMigration);
        }
    }
}
