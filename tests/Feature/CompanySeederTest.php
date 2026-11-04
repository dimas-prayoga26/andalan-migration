<?php

namespace Tests\Feature;

use App\Models\Company;
use Database\Seeders\CompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_seeder_adds_inticoal_once(): void
    {
        $this->seed(CompanySeeder::class);
        $this->seed(CompanySeeder::class);

        $inticoal = Company::query()->where('name', 'Inticoal')->sole();

        $this->assertSame('https://inticoal.co.id/', $inticoal->website);
        $this->assertSame('Indonesia', $inticoal->country);
        $this->assertTrue((bool) $inticoal->is_active);
        $this->assertSame(8, Company::query()->count());
    }
}
