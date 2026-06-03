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
            'step_order',
            'is_completed',
            'completed_at',
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $lifecycleLogsMigration);
        }

        foreach ([
            'progress_status',
            'progress_percentage',
        ] as $removedColumn) {
            $this->assertStringNotContainsString($removedColumn, $businessTripDetailsMigration.$lifecycleLogsMigration);
        }
    }
}
