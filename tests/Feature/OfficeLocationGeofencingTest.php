<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class OfficeLocationGeofencingTest extends TestCase
{
    public function test_office_locations_store_coordinates_without_duplicate_company_name(): void
    {
        $companyMigration = File::get(database_path('migrations/0001_01_01_000000_create_companies_table.php'));
        $companyCoordinatesMigration = database_path('migrations/2026_05_04_094013_add_coordinates_to_companies_table.php');
        $migration = File::get(database_path('migrations/2026_06_30_071850_create_office_locations_and_link_attendance_rules.php'));
        $officeLocationModel = File::get(app_path('Models/OfficeLocation.php'));
        $employeeDeploymentModel = File::get(app_path('Models/EmployeeDeployment.php'));
        $rulesModel = File::get(app_path('Models/RulesOfAttendace.php'));

        $this->assertStringNotContainsString("\$table->text('address')", $companyMigration);
        $this->assertFileDoesNotExist($companyCoordinatesMigration);
        $this->assertStringContainsString("Schema::create('office_locations'", $migration);
        $this->assertStringContainsString("\$table->foreignUuid('company_id')->constrained('companies', 'id')", $migration);
        $this->assertStringContainsString("\$table->text('address')->nullable();", $migration);
        $this->assertStringContainsString("\$table->decimal('latitude', 10, 7);", $migration);
        $this->assertStringContainsString("\$table->decimal('longitude', 10, 7);", $migration);
        $this->assertStringNotContainsString("\$table->string('name'", $migration);
        $this->assertStringContainsString("current_office_location_id'", $migration);
        $this->assertStringContainsString("office_location_id'", $migration);
        $this->assertStringNotContainsString("->select(['id', 'address', 'latitude', 'longitude'", $migration);

        $this->assertStringContainsString('class OfficeLocation extends Model', $officeLocationModel);
        $this->assertStringContainsString('public function company(): BelongsTo', $officeLocationModel);
        $this->assertStringContainsString('public function activeAttendanceRule(): HasOne', $officeLocationModel);
        $this->assertStringContainsString('return $this->belongsTo(OfficeLocation::class', $employeeDeploymentModel);
        $this->assertStringContainsString('return $this->belongsTo(OfficeLocation::class', $rulesModel);
    }

    public function test_attendance_geofencing_prefers_office_location_coordinates_and_rules(): void
    {
        $contextService = File::get(app_path('Services/Attendance/AttendanceContextService.php'));
        $mutationService = File::get(app_path('Services/Attendance/AttendanceMutationService.php'));
        $rulesSeeder = File::get(database_path('seeders/RulesOfAttendacesSeeder.php'));
        $employeeAddressSeeder = File::get(database_path('seeders/EmployeeAddressSeeder.php'));
        $userSeeder = File::get(database_path('seeders/UserSeeder.php'));
        $adminRecapController = File::get(app_path('Http/Controllers/AdminAttendance/AttendanceRecapController.php'));
        $picAttendanceController = File::get(app_path('Http/Controllers/PicAttendance/PicAttendanceController.php'));

        foreach ([$contextService, $mutationService] as $service) {
            $this->assertStringContainsString('employee.deployment.officeLocation:id,company_id,address,latitude,longitude,is_active', $service);
            $this->assertStringContainsString('employee.deployment.officeLocation.activeAttendanceRule', $service);
            $this->assertStringContainsString('$officeLocation = $deployment?->officeLocation;', $service);
            $this->assertStringContainsString("'id' => \$officeLocation->id", $service);
            $this->assertStringContainsString("'latitude' => (float) \$officeLocation->latitude", $service);
            $this->assertStringContainsString("'longitude' => (float) \$officeLocation->longitude", $service);
            $this->assertStringContainsString("'radius_meters' => (int) (\$attendanceRule->radius ?? 10)", $service);
            $this->assertStringNotContainsString('$fallbackCompany = $deployment?->company;', $service);
            $this->assertStringNotContainsString('employee.deployment.company:id,name,address,latitude,longitude', $service);
        }

        $this->assertStringContainsString("DB::table('office_locations')->insert", $rulesSeeder);
        $this->assertStringContainsString('Company::query()', $rulesSeeder);
        $this->assertStringContainsString("->where('is_active', true)", $rulesSeeder);
        $this->assertStringContainsString('syncCompanyAttendanceRule($company)', $rulesSeeder);
        $this->assertStringContainsString('syncOfficeAttendanceRule($company, $officeLocationId, $now)', $rulesSeeder);
        $this->assertStringContainsString("->pluck('id')", $rulesSeeder);
        $this->assertStringNotContainsString('officeLocationDataForCompany', $rulesSeeder);
        $this->assertStringNotContainsString('$company->address', $rulesSeeder);
        $this->assertStringNotContainsString('$company->latitude', $rulesSeeder);
        $this->assertStringNotContainsString('$company->longitude', $rulesSeeder);
        $this->assertStringContainsString("\$ruleData['office_location_id'] = \$officeLocationId;", $rulesSeeder);
        $this->assertStringContainsString("'current_office_location_id' => \$primaryOfficeLocationId", $rulesSeeder);
        $this->assertStringContainsString("\$deploymentData['current_office_location_id'] = \$officeLocationId;", $userSeeder);
        $this->assertStringContainsString('deployment.officeLocation', $employeeAddressSeeder);
        $this->assertStringNotContainsString('deployment?->company?->address', $employeeAddressSeeder);

        foreach ([$adminRecapController, $picAttendanceController] as $controller) {
            $this->assertStringContainsString('deployment.officeLocation:id,address', $controller);
            $this->assertStringContainsString('deployment?->officeLocation?->address', $controller);
            $this->assertStringNotContainsString('deployment?->company?->address', $controller);
        }
    }
}
