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
        $userSeeder = File::get(database_path('seeders/UserSeeder.php'));

        $this->assertStringContainsString('BusinessTripSeeder::class', $databaseSeeder);

        foreach (['staff31', 'staff32', 'staff33', 'staff34'] as $username) {
            $this->assertStringContainsString("'{$username}' => [", $businessTripSeeder);
            $this->assertStringContainsString('TRP-RNB-'.strtoupper($username), $businessTripSeeder);
        }

        $this->assertStringContainsString("DB::table('companies')->where('name', 'RNB')->value('id')", $businessTripSeeder);
        $this->assertStringContainsString("where('current_company_id', \$rnbCompanyId)", $businessTripSeeder);
        $this->assertStringContainsString("'RNB' => 3", $userSeeder);
        $this->assertStringContainsString('resolveCompanySeedNumber($company, $index + 1)', $userSeeder);
        $this->assertStringContainsString('private const RNB_STAFF_ASSIGNMENTS = [', $userSeeder);
        $this->assertStringContainsString('private function resolveStaffAssignment', $userSeeder);
        $this->assertStringContainsString("'department' => 'Administration, Finance and Legal'", $userSeeder);
        $this->assertStringContainsString("'position' => 'Finance and Administration Coordinator'", $userSeeder);
        $this->assertStringContainsString("'department' => 'Marketing and Promotion'", $userSeeder);
        $this->assertStringContainsString("'position' => 'Graphic Design'", $userSeeder);
        $this->assertStringContainsString("'department' => 'Project Planning and Development'", $userSeeder);
        $this->assertStringContainsString("'position' => 'Architecture Design'", $userSeeder);
        $this->assertStringContainsString("'department' => 'Operations'", $userSeeder);
        $this->assertStringContainsString("'position' => 'Documentation Event and Editor Video'", $userSeeder);

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
        $this->assertStringContainsString("'status' => 'pending',\n                            'actor' => null,\n                            'happened_at' => null,", $businessTripSeeder);
        $this->assertStringContainsString('private function staffScenarioItems(array $staffScenario): array', $businessTripSeeder);
        $this->assertStringContainsString("'request_number' => 'TRP-RNB-STAFF31-APPROVED'", $businessTripSeeder);
        $this->assertStringContainsString("'purpose' => 'Follow up meeting project RNB yang sudah disetujui supervisor.'", $businessTripSeeder);
        $this->assertStringContainsString("'approval_status' => 'approved'", $businessTripSeeder);
        $this->assertStringContainsString("'happened_at' => '2026-06-05 10:05:00'", $businessTripSeeder);
        $this->assertStringContainsString("'request_number' => 'TRP-RNB-STAFF32'", $businessTripSeeder);
        $this->assertStringContainsString("'cash_advance_submitted' => [\n                        'status' => 'pending'", $businessTripSeeder);
    }
}
