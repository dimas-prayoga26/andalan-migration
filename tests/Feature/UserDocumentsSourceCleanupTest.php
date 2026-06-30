<?php

namespace Tests\Feature;

use Tests\TestCase;

class UserDocumentsSourceCleanupTest extends TestCase
{
    public function test_active_seeders_no_longer_write_to_user_documents(): void
    {
        $seeders = [
            database_path('seeders/UserSeeder.php'),
            database_path('seeders/LegacySqlUserSeeder.php'),
            database_path('seeders/NiskalaMultiPicLeaveSeeder.php'),
            database_path('seeders/EmployeeIdentitySeeder.php'),
        ];

        foreach ($seeders as $seeder) {
            $contents = file_get_contents($seeder);

            $this->assertIsString($contents);
            $this->assertStringNotContainsString("DB::table('user_documents')", $contents);
            $this->assertStringNotContainsString('DB::table("user_documents")', $contents);
        }
    }

    public function test_user_documents_migrations_are_removed_from_fresh_schema(): void
    {
        $migrationFiles = glob(database_path('migrations/*user_documents*.php')) ?: [];

        $this->assertSame([], $migrationFiles);
    }
}
