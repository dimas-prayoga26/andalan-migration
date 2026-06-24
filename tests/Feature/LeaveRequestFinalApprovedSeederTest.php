<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LeaveRequestFinalApprovedSeederTest extends TestCase
{
    public function test_final_approved_leave_seeder_records_each_approval_stage(): void
    {
        $seeder = File::get(database_path('seeders/LeaveRequestFinalApprovedSeeder.php'));
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));

        foreach ([
            "'reason' => \$seedReason,",
            "'status' => 'approved',",
            "'approved_by' => \$finalDecisionActorUserId,",
            "'event_type' => 'submitted',",
            "'event_type' => 'supervisor_review',",
            "'event_type' => 'status_updated',",
            "'title' => 'Final Decision',",
            "'to_status' => 'approved',",
            "'reason' => \$seedReason",
            "->where('event_type', 'hr_verification')",
        ] as $expectedSeederFragment) {
            $this->assertStringContainsString($expectedSeederFragment, $seeder);
        }

        $this->assertStringNotContainsString("'to_status' => 'complete',", $seeder);
        $this->assertStringContainsString('LeaveRequestFinalApprovedSeeder::class,', $databaseSeeder);
    }
}
