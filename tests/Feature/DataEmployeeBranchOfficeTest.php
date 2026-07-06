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
        $legacySeeder = File::get(database_path('seeders/LegacySqlUserSeeder.php'));
        $rulesSeeder = File::get(database_path('seeders/RulesOfAttendacesSeeder.php'));

        $this->assertStringContainsString("string('name')->nullable()", $nameMigration);
        $this->assertStringContainsString("['company_id', 'name']", $nameMigration);
        $this->assertStringContainsString("=> 'Jakarta'", $backfillMigration);
        $this->assertStringContainsString("=> 'Yogyakarta'", $backfillMigration);
        $this->assertStringContainsString("'name' => 'Jakarta'", $legacySeeder);
        $this->assertStringContainsString("? 'Jakarta' : 'Yogyakarta'", $legacySeeder);
        $this->assertStringContainsString("'name' => 'Yogyakarta'", $rulesSeeder);
        $this->assertStringContainsString("['workplace' => \$locationName]", $normalizationMigration);
    }

    public function test_data_employee_form_filters_branch_options_by_company(): void
    {
        $form = File::get(resource_path('views/authorization/form.blade.php'));

        $this->assertStringContainsString('name="current_office_location_id"', $form);
        $this->assertStringContainsString('id="dataEmployeeCompany"', $form);
        $this->assertStringContainsString('function updateDataEmployeeOfficeOptions()', $form);
        $this->assertStringContainsString('officeLocation.company_id === companyId', $form);
        $this->assertStringNotContainsString('name="workplace"', $form);
        $this->assertIsString(Blade::compileString($form));
    }

    public function test_data_employee_update_synchronizes_branch_and_geofencing_office(): void
    {
        $controller = File::get(app_path('Http/Controllers/AuthorizationController.php'));

        $this->assertStringContainsString("'current_office_location_id' => [", $controller);
        $this->assertStringContainsString("Rule::exists('office_locations', 'id')", $controller);
        $this->assertStringContainsString("->where('company_id', \$request->input('current_company_id'))", $controller);
        $this->assertStringContainsString("'current_office_location_id' => \$officeLocation?->id", $controller);
        $this->assertStringContainsString("'workplace' => \$workplace", $controller);
    }
}
