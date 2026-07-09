<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AnnualLeaveAccrualConfigurationTest extends TestCase
{
    public function test_fresh_seed_and_scheduler_include_monthly_leave_balance_sync(): void
    {
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));
        $consoleRoutes = File::get(base_path('routes/console.php'));
        $appConfig = File::get(config_path('app.php'));

        $this->assertStringContainsString('AttendanceHolidaySeeder::class', $databaseSeeder);
        $this->assertStringContainsString('LeaveBalanceSeeder::class', $databaseSeeder);
        $this->assertGreaterThan(
            strpos($databaseSeeder, 'AttendanceHolidaySeeder::class'),
            strpos($databaseSeeder, 'LeaveBalanceSeeder::class')
        );
        $this->assertStringContainsString("Schedule::command('leave-balances:sync')", $consoleRoutes);
        $this->assertStringContainsString("->monthlyOn(1, '00:10')", $consoleRoutes);
        $this->assertStringContainsString("->timezone('Asia/Jakarta')", $consoleRoutes);
        $this->assertStringContainsString('->withoutOverlapping()', $consoleRoutes);
        $this->assertStringContainsString("'timezone' => 'Asia/Jakarta'", $appConfig);
    }

    public function test_annual_leave_rules_keep_joint_holiday_cap_and_request_guards(): void
    {
        $service = File::get(app_path('Services/Leave/AnnualLeaveBalanceService.php'));
        $controller = File::get(app_path('Http/Controllers/StaffAttendance/AttendanceLeaveRequestController.php'));

        $this->assertStringContainsString('TOTAL_ANNUAL_ENTITLEMENT = 12', $service);
        $this->assertStringContainsString("->where('type', 2)", $service);
        $this->assertStringContainsString('TOTAL_ANNUAL_ENTITLEMENT - $jointHolidayDays', $service);
        $this->assertStringContainsString('MINIMUM_TENURE_MONTHS = 12', $service);
        $this->assertStringContainsString('masa kerja belum tercapai', $controller);
        $this->assertStringContainsString('Saldo Cuti Tahunan sudah habis', $controller);
        $this->assertStringContainsString('$remainingAnnualBalance < $durationDays', $controller);
    }
}
