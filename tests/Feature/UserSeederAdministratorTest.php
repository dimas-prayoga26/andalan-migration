<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UserSeederAdministratorTest extends TestCase
{
    public function test_user_seeder_creates_company_administrator_accounts(): void
    {
        $userSeeder = File::get(database_path('seeders/UserSeeder.php'));

        $this->assertStringContainsString("DB::table('departments')->where('name', 'Administrator')->value('id')", $userSeeder);
        $this->assertStringContainsString("DB::table('positions')->where('name', 'System Administrator')->value('id')", $userSeeder);
        $this->assertStringContainsString("['email' => \"admin{\$directorNumber}@gmail.com\"]", $userSeeder);
        $this->assertStringContainsString("'username' => \"admin{\$directorNumber}\"", $userSeeder);
        $this->assertStringContainsString("'business_email' => \"admin{\$directorNumber}@{\$this->resolveCompanyEmailDomain((string) \$company->name)}\"", $userSeeder);
        $this->assertStringContainsString('$administrator->syncRoles([\'Staff\']);', $userSeeder);
        $this->assertStringContainsString('divisionId: $adminDivisionId', $userSeeder);
        $this->assertStringContainsString('positionId: $adminPositionId', $userSeeder);
    }

    public function test_user_seeder_limits_staff_accounts_to_five_per_company(): void
    {
        $userSeeder = File::get(database_path('seeders/UserSeeder.php'));

        $this->assertStringContainsString('private const STAFF_PER_COMPANY = 5;', $userSeeder);
        $this->assertStringContainsString('$staffIndexes = range(1, self::STAFF_PER_COMPANY);', $userSeeder);
        $this->assertStringContainsString('private function deleteExtraSeededStaffAccounts(int $companySeedNumber): void', $userSeeder);
        $this->assertStringContainsString("'department' => 'Information and Communications Technology'", $userSeeder);
        $this->assertStringContainsString("'position' => 'Web Developer'", $userSeeder);
        $this->assertStringNotContainsString('$staffIndexes = (string) $company->name === \'RNB\'', $userSeeder);
    }
}
