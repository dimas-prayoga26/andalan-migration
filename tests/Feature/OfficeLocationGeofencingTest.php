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
        $globalLocationMigration = File::get(database_path('migrations/2026_07_06_093540_detach_office_locations_and_attendance_rules_from_companies.php'));
        $officeLocationModel = File::get(app_path('Models/OfficeLocation.php'));
        $companyModel = File::get(app_path('Models/Company.php'));
        $employeeDeploymentModel = File::get(app_path('Models/EmployeeDeployment.php'));
        $rulesModel = File::get(app_path('Models/RulesOfAttendace.php'));

        $this->assertStringNotContainsString("\$table->text('address')", $companyMigration);
        $this->assertFileDoesNotExist($companyCoordinatesMigration);
        $this->assertStringContainsString("Schema::create('office_locations'", $migration);
        $this->assertStringContainsString("\$table->text('address')->nullable();", $migration);
        $this->assertStringContainsString("\$table->decimal('latitude', 10, 7);", $migration);
        $this->assertStringContainsString("\$table->decimal('longitude', 10, 7);", $migration);
        $this->assertStringNotContainsString("\$table->string('name'", $migration);
        $this->assertStringContainsString("current_office_location_id'", $migration);
        $this->assertStringContainsString("office_location_id'", $migration);
        $this->assertStringNotContainsString("->select(['id', 'address', 'latitude', 'longitude'", $migration);
        $this->assertStringContainsString("\$table->dropForeign(['company_id'])", $globalLocationMigration);
        $this->assertStringContainsString("\$table->dropColumn('company_id')", $globalLocationMigration);
        $this->assertStringContainsString("\$table->dropColumn('companies_id')", $globalLocationMigration);
        $this->assertStringContainsString("\$table->unique('name', 'office_locations_name_unique')", $globalLocationMigration);

        $this->assertStringContainsString('class OfficeLocation extends Model', $officeLocationModel);
        $this->assertStringNotContainsString('public function company(): BelongsTo', $officeLocationModel);
        $this->assertStringNotContainsString('public function officeLocations(): HasMany', $companyModel);
        $this->assertStringContainsString('public function activeAttendanceRule(): HasOne', $officeLocationModel);
        $this->assertStringContainsString('return $this->belongsTo(OfficeLocation::class', $employeeDeploymentModel);
        $this->assertStringContainsString('return $this->belongsTo(OfficeLocation::class', $rulesModel);
        $this->assertStringNotContainsString('public function company(): BelongsTo', $rulesModel);
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
            $this->assertStringContainsString('employee.deployment.officeLocation:id,name,address,latitude,longitude,is_active', $service);
            $this->assertStringContainsString('employee.deployment.officeLocation.activeAttendanceRule', $service);
            $this->assertStringContainsString('$officeLocation = $deployment?->officeLocation;', $service);
            $this->assertStringContainsString("'id' => \$officeLocation->id", $service);
            $this->assertStringContainsString("'name' => \$officeLocation->name", $service);
            $this->assertStringContainsString("'latitude' => (float) \$officeLocation->latitude", $service);
            $this->assertStringContainsString("'longitude' => (float) \$officeLocation->longitude", $service);
            $this->assertStringContainsString("'radius_meters' => (int) (\$attendanceRule->radius ?? 10)", $service);
            $this->assertStringContainsString('rules_of_attendaces.office_reset_time', $service);
            $this->assertStringContainsString("'office_reset_time' => isset(\$attendanceRule?->office_reset_time)", $service);
            $this->assertStringNotContainsString('$fallbackCompany = $deployment?->company;', $service);
            $this->assertStringNotContainsString('employee.deployment.company:id,name,address,latitude,longitude', $service);
        }

        $this->assertStringContainsString('private const OFFICE_LOCATIONS', $rulesSeeder);
        $this->assertStringContainsString("'Jakarta' => [", $rulesSeeder);
        $this->assertStringContainsString("'Yogyakarta' => [", $rulesSeeder);
        $this->assertStringContainsString("['office_location_id' => \$officeLocation->id]", $rulesSeeder);
        $this->assertStringNotContainsString('companies_id', $rulesSeeder);
        $this->assertStringNotContainsString('Company::query()', $rulesSeeder);
        $this->assertStringContainsString("\$deploymentData['current_office_location_id'] = \$officeLocationId;", $userSeeder);
        $this->assertStringContainsString('deployment.officeLocation', $employeeAddressSeeder);
        $this->assertStringNotContainsString('deployment?->company?->address', $employeeAddressSeeder);

        foreach ([$adminRecapController, $picAttendanceController] as $controller) {
            $this->assertStringContainsString('deployment.officeLocation:id,name,address', $controller);
            $this->assertStringContainsString('deployment?->officeLocation?->name', $controller);
            $this->assertStringNotContainsString('deployment?->company?->address', $controller);
        }
    }
}
