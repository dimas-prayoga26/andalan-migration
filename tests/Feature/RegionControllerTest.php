<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_proxies_and_caches_each_region_level(): void
    {
        Cache::flush();
        Http::fake([
            'wilayah.id/api/provinces.json' => Http::response(['data' => [['code' => '34', 'name' => 'Daerah Istimewa Yogyakarta'], ['code' => '', 'name' => 'Invalid']]]),
            'wilayah.id/api/regencies/34.json' => Http::response(['data' => [['code' => '34.04', 'name' => 'Kabupaten Sleman']]]),
            'wilayah.id/api/districts/34.04.json' => Http::response(['data' => [['code' => '34.04.07', 'name' => 'Depok']]]),
            'wilayah.id/api/villages/34.04.07.json' => Http::response(['data' => [['code' => '34.04.07.2002', 'name' => 'Condongcatur']]]),
        ]);
        $user = $this->createActiveUser();

        $this->actingAs($user)->getJson(route('regions.provinces'))
            ->assertOk()
            ->assertExactJson(['data' => [['code' => '34', 'name' => 'Daerah Istimewa Yogyakarta']]]);
        $this->actingAs($user)->getJson(route('regions.regencies', ['provinceCode' => '34']))
            ->assertOk()->assertJsonPath('data.0.name', 'Kabupaten Sleman');
        $this->actingAs($user)->getJson(route('regions.districts', ['regencyCode' => '34.04']))
            ->assertOk()->assertJsonPath('data.0.name', 'Depok');
        $this->actingAs($user)->getJson(route('regions.villages', ['districtCode' => '34.04.07']))
            ->assertOk()->assertJsonPath('data.0.name', 'Condongcatur');
        $this->actingAs($user)->getJson(route('regions.provinces'))->assertOk();

        Http::assertSentCount(4);
    }

    public function test_it_reports_upstream_failures_and_rejects_invalid_codes(): void
    {
        Cache::flush();
        Http::fake(['wilayah.id/*' => Http::response([], 500)]);
        $user = $this->createActiveUser();

        $this->actingAs($user)->getJson(route('regions.provinces'))
            ->assertStatus(502)
            ->assertJsonPath('data', []);
        $this->actingAs($user)->getJson('/regions/regencies/abc')->assertNotFound();
        $this->actingAs($user)->getJson('/regions/districts/3404')->assertNotFound();
    }

    private function createActiveUser(): User
    {
        $user = User::query()->create([
            'email' => 'region.user@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $employee = Employee::query()->create(['user_id' => $user->id, 'employee_code' => 'EMP-REGION', 'status' => 'Active']);
        EmployeeDeployment::query()->create(['employee_id' => $employee->id, 'status' => 'Active']);

        return $user;
    }
}
