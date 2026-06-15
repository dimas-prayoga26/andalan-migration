<?php

namespace Tests\Feature;

use Database\Seeders\CompanySeeder;
use Database\Seeders\MetaDataDomiciliSeeder;
use Database\Seeders\MetaDataGenderSeeder;
use Database\Seeders\MetaDataMaritalStatusSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RnbStaffSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is not available.');
        }

        parent::setUp();
    }

    public function test_user_seeder_creates_four_active_rnb_staff_accounts(): void
    {
        $this->seed([
            CompanySeeder::class,
            MetaDataDomiciliSeeder::class,
            MetaDataGenderSeeder::class,
            MetaDataMaritalStatusSeeder::class,
            PositionSeeder::class,
            UserSeeder::class,
        ]);

        $rnbCompanyId = DB::table('companies')->where('name', 'RNB')->value('id');

        $staffCount = DB::table('users')
            ->join('model_has_roles', 'model_has_roles.model_uuid', '=', 'users.id')
            ->join('roles', 'roles.uuid', '=', 'model_has_roles.role_id')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->join('employee_deployments', 'employee_deployments.employee_id', '=', 'employees.id')
            ->where('roles.name', 'Staff')
            ->where('users.is_active', true)
            ->where('employee_deployments.current_company_id', $rnbCompanyId)
            ->distinct()
            ->count('users.id');

        $this->assertSame(4, $staffCount);

        $staffUsernames = DB::table('users')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->join('employee_deployments', 'employee_deployments.employee_id', '=', 'employees.id')
            ->where('employee_deployments.current_company_id', $rnbCompanyId)
            ->whereIn('users.username', ['staff31', 'staff32', 'staff33', 'staff34'])
            ->orderBy('users.username')
            ->pluck('users.username')
            ->all();

        $this->assertSame(['staff31', 'staff32', 'staff33', 'staff34'], $staffUsernames);

        $staff31JoinDate = DB::table('users')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->join('employee_deployments', 'employee_deployments.employee_id', '=', 'employees.id')
            ->where('users.username', 'staff31')
            ->where('employee_deployments.current_company_id', $rnbCompanyId)
            ->value('employee_deployments.join_date');

        $this->assertNotNull($staff31JoinDate);
        $this->assertTrue(Carbon::parse((string) $staff31JoinDate)->lessThan(now()->subYear()));

        $staffAssignments = DB::table('users')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->join('employee_deployments', 'employee_deployments.employee_id', '=', 'employees.id')
            ->join('departments', 'departments.id', '=', 'employee_deployments.current_department_id')
            ->join('positions', 'positions.id', '=', 'employee_deployments.current_position_id')
            ->where('employee_deployments.current_company_id', $rnbCompanyId)
            ->whereIn('users.username', ['staff31', 'staff32', 'staff33', 'staff34'])
            ->orderBy('users.username')
            ->get(['users.username', 'departments.name as department_name', 'positions.name as position_name'])
            ->mapWithKeys(fn (object $staffAssignment): array => [
                (string) $staffAssignment->username => [
                    'department' => (string) $staffAssignment->department_name,
                    'position' => (string) $staffAssignment->position_name,
                ],
            ])
            ->all();

        $this->assertSame([
            'staff31' => [
                'department' => 'Administration, Finance and Legal',
                'position' => 'Finance and Administration Coordinator',
            ],
            'staff32' => [
                'department' => 'Marketing and Promotion',
                'position' => 'Graphic Design',
            ],
            'staff33' => [
                'department' => 'Project Planning and Development',
                'position' => 'Architecture Design',
            ],
            'staff34' => [
                'department' => 'Operations',
                'position' => 'Documentation Event and Editor Video',
            ],
        ], $staffAssignments);
    }
}
