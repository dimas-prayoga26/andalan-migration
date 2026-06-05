<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BusinessTripSeederTest extends TestCase
{
    public function test_business_trip_seeder_is_registered_and_targets_rnb_staff_lifecycle_scenarios(): void
    {
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));
        $businessTripSeeder = File::get(database_path('seeders/BusinessTripSeeder.php'));

        $this->assertStringContainsString('BusinessTripSeeder::class', $databaseSeeder);

        foreach (['staff31', 'staff32', 'staff33', 'staff34'] as $username) {
            $this->assertStringContainsString("'{$username}' => [", $businessTripSeeder);
            $this->assertStringContainsString('TRP-RNB-'.strtoupper($username), $businessTripSeeder);
        }

        $this->assertStringContainsString("DB::table('companies')->where('name', 'RNB')->value('id')", $businessTripSeeder);
        $this->assertStringContainsString("where('current_company_id', \$rnbCompanyId)", $businessTripSeeder);

        foreach ([
            'submitted',
            'supervisor_review',
            'cash_advance_submitted',
            'finance_disbursement',
            'trip_execution',
            'trip_report',
            'reimbursement_submitted',
        ] as $eventKey) {
            $this->assertStringContainsString("'event_key' => '{$eventKey}'", $businessTripSeeder);
        }

        foreach (['transportation', 'accommodation', 'meals_entertainment', 'local_transport'] as $category) {
            $this->assertStringContainsString("'category' => '{$category}'", $businessTripSeeder);
        }

        $this->assertStringContainsString("'request_number' => 'TRP-RNB-STAFF31'", $businessTripSeeder);
        $this->assertStringContainsString("'supervisor_review' => [\n                        'status' => 'pending'", $businessTripSeeder);
        $this->assertStringContainsString("'request_number' => 'TRP-RNB-STAFF32'", $businessTripSeeder);
        $this->assertStringContainsString("'cash_advance_submitted' => [\n                        'status' => 'pending'", $businessTripSeeder);
    }
}
