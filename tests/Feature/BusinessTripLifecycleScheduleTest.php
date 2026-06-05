<?php

namespace Tests\Feature;

use App\Console\Commands\SyncBusinessTripLifecycleStatus;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BusinessTripLifecycleScheduleTest extends TestCase
{
    public function test_business_trip_lifecycle_sync_command_is_registered(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('business-trips:lifecycle:sync', $commands);
        $this->assertInstanceOf(SyncBusinessTripLifecycleStatus::class, $commands['business-trips:lifecycle:sync']);
    }

    public function test_business_trip_lifecycle_sync_is_scheduled_daily(): void
    {
        $consoleRoutes = File::get(base_path('routes/console.php'));

        $this->assertStringContainsString("Schedule::command('business-trips:lifecycle:sync')", $consoleRoutes);
        $this->assertStringContainsString("->dailyAt('00:05')", $consoleRoutes);
        $this->assertStringContainsString("->timezone('Asia/Jakarta')", $consoleRoutes);
        $this->assertStringContainsString('->withoutOverlapping()', $consoleRoutes);
    }

    public function test_business_trip_lifecycle_sync_script_runners_exist(): void
    {
        $windowsScript = File::get(base_path('cronJob/Schedule/run-business-trip-lifecycle-sync.bat'));
        $linuxScript = File::get(base_path('cronJob/Schedule/run-business-trip-lifecycle-sync.sh'));

        $this->assertStringContainsString('php artisan business-trips:lifecycle:sync %*', $windowsScript);
        $this->assertStringContainsString('php artisan business-trips:lifecycle:sync "$@"', $linuxScript);
    }

    public function test_business_trip_lifecycle_sync_uses_trip_dates_for_phase_three_and_four(): void
    {
        $command = File::get(app_path('Console/Commands/SyncBusinessTripLifecycleStatus.php'));

        $this->assertStringContainsString('markTripExecutionPending', $command);
        $this->assertStringContainsString('markTripExecutionCompleted', $command);
        $this->assertStringContainsString('markTripReportPending($businessTrip, $endDate)', $command);
        $this->assertStringContainsString("'event_key' => \$eventKey", $command);
        $this->assertStringContainsString("'status' => 'pending'", $command);
        $this->assertStringContainsString("'status' => 'complete'", $command);
        $this->assertStringContainsString("'actor_id' => \$this->businessTripStaffActorId(\$businessTrip)", $command);
        $this->assertStringContainsString("'actor_id' => null", $command);
        $this->assertStringContainsString("'metadata' => \$this->tripExecutionMetadata(\$businessTrip)", $command);
        $this->assertStringNotContainsString('scheduleMetadata', $command);
        $this->assertStringNotContainsString('businessTripStaffActorLabel', $command);
        $this->assertStringNotContainsString("'actor_label'", $command);
        $this->assertStringContainsString("'trip_start_date' => \$businessTrip->start_date?->toDateString()", $command);
        $this->assertStringContainsString("'trip_end_date' => \$businessTrip->end_date?->toDateString()", $command);
        $this->assertStringContainsString("'happened_at' => \$endDate->copy()->endOfDay()->timezone('UTC')", $command);
        $this->assertStringContainsString('private function businessTripStaffActorId(BusinessTrip $businessTrip): ?string', $command);
        $this->assertStringContainsString('&& ! $this->isPendingStatus((string) $tripReport->status)', $command);
        $this->assertStringContainsString('private function isPendingStatus(string $status): bool', $command);
        $this->assertStringContainsString('$this->businessTripHasSupervisorApproval($businessTrip)', $command);
        $this->assertStringContainsString('$this->tripExecutionCanBeSynced($businessTrip)', $command);
    }
}
