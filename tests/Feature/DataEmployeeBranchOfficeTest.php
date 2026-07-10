<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DataEmployeeBranchOfficeTest extends TestCase
{
    public function test_office_location_schema_and_seeders_define_branch_names(): void
    {
        $nameMigration = File::get(database_path('migrations/2026_07_06_034507_add_name_to_office_locations_table.php'));
        $backfillMigration = File::get(database_path('migrations/2026_07_06_034524_backfill_office_location_names.php'));
        $normalizationMigration = File::get(database_path('migrations/2026_07_06_035307_normalize_office_location_names_to_cities.php'));
        $globalLocationMigration = File::get(database_path('migrations/2026_07_06_093540_detach_office_locations_and_attendance_rules_from_companies.php'));
        $legacySeeder = File::get(database_path('seeders/LegacySqlUserSeeder.php'));
        $rulesSeeder = File::get(database_path('seeders/RulesOfAttendacesSeeder.php'));

        $this->assertStringContainsString("string('name')->nullable()", $nameMigration);
        $this->assertStringContainsString("\$table->unique('name', 'office_locations_name_unique')", $globalLocationMigration);
        $this->assertStringContainsString("=> 'Jakarta'", $backfillMigration);
        $this->assertStringContainsString("=> 'Yogyakarta'", $backfillMigration);
        $this->assertStringContainsString("'name' => 'Jakarta'", $legacySeeder);
        $this->assertStringContainsString("? 'Jakarta' : 'Yogyakarta'", $legacySeeder);
        $this->assertStringContainsString("'Yogyakarta' => [", $rulesSeeder);
        $this->assertStringContainsString("['workplace' => \$locationName]", $normalizationMigration);
    }

    public function test_data_employee_form_lists_global_branch_options_independently(): void
    {
        $form = File::get(resource_path('views/authorization/form.blade.php'));

        $this->assertStringContainsString('name="current_office_location_id"', $form);
        $this->assertStringContainsString('id="dataEmployeeCompany"', $form);
        $this->assertStringContainsString('@foreach ($officeLocationOptions as $officeLocation)', $form);
        $this->assertStringNotContainsString('function updateDataEmployeeOfficeOptions()', $form);
        $this->assertStringNotContainsString('officeLocation.company_id === companyId', $form);
        $this->assertStringNotContainsString('name="workplace"', $form);
        $this->assertIsString(Blade::compileString($form));
    }

    public function test_data_employee_update_synchronizes_branch_and_geofencing_office(): void
    {
        $controller = File::get(app_path('Http/Controllers/AuthorizationController.php'));

        $this->assertStringContainsString("'current_office_location_id' => [", $controller);
        $this->assertStringContainsString("Rule::exists('office_locations', 'id')", $controller);
        $this->assertStringNotContainsString("->where('company_id', \$request->input('current_company_id'))", $controller);
        $this->assertStringContainsString("\$query->where('is_active', true)", $controller);
        $this->assertStringContainsString("'current_office_location_id' => \$officeLocation?->id", $controller);
        $this->assertStringContainsString("'workplace' => \$workplace", $controller);
    }
}
