<?php

namespace Tests\Feature;

use Database\Seeders\LegacySqlUserSeeder;
use Database\Seeders\MailAccessAccountSeeder;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;

class MailAccessAccountSeederTest extends TestCase
{
    public function test_mail_access_accounts_are_seeded_without_backend_inbox_accounts(): void
    {
        $seeder = File::get(database_path('seeders/MailAccessAccountSeeder.php'));
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));

        $this->assertStringContainsString('MailAccessAccount::query()->updateOrCreate', $seeder);
        $this->assertStringNotContainsString("config('mail_inboxes.accounts', [])", $seeder);
        $this->assertStringNotContainsString('$configuredInboxEmails = MailAccessAccount::configuredInboxEmails();', $seeder);
        $this->assertStringNotContainsString('$configuredInboxEmails->contains($email)', $seeder);
        $this->assertStringContainsString('deleteCatchAllAccessAccounts', $seeder);
        $this->assertStringContainsString('MailAccessAccount::catchAllInboxEmails()', $seeder);
        $this->assertStringContainsString('deleteHrAccessAccounts', $seeder);
        $this->assertStringNotContainsString('seedHrAccessAccounts', $seeder);
        $this->assertStringContainsString("\$hrEmailPattern = 'hr'.'@%';", $seeder);
        $this->assertStringContainsString("->where('email', 'like', \$hrEmailPattern)", $seeder);
        $this->assertStringContainsString("'type' => MailAccessAccount::TYPE_APPLICANT_NOTIFICATION", $seeder);
        $this->assertStringContainsString('$legacyAccessAccounts = $this->legacyAccessAccounts();', $seeder);
        $this->assertStringContainsString("base_path('users.sql')", $seeder);
        $this->assertStringContainsString("base_path('employees.sql')", $seeder);
        $this->assertStringContainsString('$this->legacyUserIsActive($user, $employeesByUserId)', $seeder);
        $this->assertStringContainsString("Hash::make(\$this->pinFor('default'))", $seeder);
        $this->assertStringContainsString("'type' => MailAccessAccount::TYPE_PERSONAL", $seeder);
        $this->assertStringContainsString("env('MAIL_ACCESS_DEFAULT_PIN', '0000')", $seeder);
        $this->assertStringContainsString('/^[0-9]{4}$/', $seeder);
        $this->assertStringContainsString('MailAccessAccountSeeder::class', $databaseSeeder);
    }

    public function test_legacy_sql_accounts_include_active_and_inactive_statuses(): void
    {
        $method = new ReflectionMethod(MailAccessAccountSeeder::class, 'legacyAccessAccounts');
        $accounts = $method->invoke(new MailAccessAccountSeeder);

        $this->assertTrue($accounts['dimas.prayoga260403@gmail.com']);
        $this->assertTrue($accounts['mnshiddiq.01@gmail.com']);
        $this->assertFalse($accounts['tikoramadhan47@gmail.com']);
        $this->assertFalse($accounts['fadil@andalanbersama.com']);
    }

    public function test_legacy_user_seeder_syncs_statuses_from_current_sql_exports(): void
    {
        $seeder = File::get(database_path('seeders/LegacySqlUserSeeder.php'));
        $method = new ReflectionMethod(LegacySqlUserSeeder::class, 'currentSqlUserStatuses');
        $statuses = $method->invoke(new LegacySqlUserSeeder);

        $this->assertStringContainsString('$this->syncCurrentSqlUserStatuses();', $seeder);
        $this->assertStringContainsString("base_path('users.sql')", $seeder);
        $this->assertStringContainsString("base_path('employees.sql')", $seeder);
        $this->assertStringContainsString('currentSqlUserIsActive', $seeder);
        $this->assertTrue($statuses['dimas.prayoga260403@gmail.com']);
        $this->assertFalse($statuses['tikoramadhan47@gmail.com']);
    }
}
