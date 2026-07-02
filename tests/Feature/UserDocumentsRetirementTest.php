<?php

namespace Tests\Feature;

use Database\Seeders\CompanySeeder;
use Database\Seeders\MetaDataDomiciliSeeder;
use Database\Seeders\MetaDataGenderSeeder;
use Database\Seeders\MetaDataMaritalStatusSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserDocumentsRetirementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is not available.');
        }

        parent::setUp();
    }

    public function test_schema_uses_employee_identities_instead_of_user_documents(): void
    {
        $this->assertFalse(Schema::hasTable('user_documents'));
        $this->assertTrue(Schema::hasTable('employee_identities'));
    }

    public function test_user_seeder_writes_identity_data_to_employee_identities(): void
    {
        $this->seed([
            CompanySeeder::class,
            MetaDataDomiciliSeeder::class,
            MetaDataGenderSeeder::class,
            MetaDataMaritalStatusSeeder::class,
            PositionSeeder::class,
            UserSeeder::class,
        ]);

        $this->assertFalse(Schema::hasTable('user_documents'));
        $this->assertGreaterThan(0, DB::table('employee_identities')->count());

        $this->assertSame(
            DB::table('employees')->count(),
            DB::table('employee_identities')->count(),
        );
    }
}
