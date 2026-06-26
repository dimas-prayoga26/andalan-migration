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

    public function test_user_seeder_creates_expected_active_users_for_each_company(): void
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

        $companyStaffCounts = DB::table('companies')
            ->join('employee_deployments', 'employee_deployments.current_company_id', '=', 'companies.id')
            ->join('employees', 'employees.id', '=', 'employee_deployments.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('positions', 'positions.id', '=', 'employee_deployments.current_position_id')
            ->where('users.is_active', true)
            ->where('positions.name', '!=', 'System Administrator')
            ->where('positions.name', '!=', 'Director')
            ->where('positions.name', '!=', 'Supervisor')
            ->where('users.username', 'like', 'staff%')
            ->groupBy('companies.name')
            ->orderBy('companies.name')
            ->get([
                'companies.name as company_name',
                DB::raw('COUNT(DISTINCT users.id) as user_count'),
            ])
            ->mapWithKeys(static fn (object $row): array => [
                (string) $row->company_name => (int) $row->user_count,
            ])
            ->all();

        $this->assertSame([
            'AndalanKu' => 5,
            'KMA' => 5,
            'Niskala' => 5,
            'RNB' => 5,
            'RNE' => 5,
            'TMS' => 5,
            'Trah' => 5,
        ], $companyStaffCounts);

        $positionCountsByCompany = DB::table('companies')
            ->join('employee_deployments', 'employee_deployments.current_company_id', '=', 'companies.id')
            ->join('employees', 'employees.id', '=', 'employee_deployments.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('positions', 'positions.id', '=', 'employee_deployments.current_position_id')
            ->where('users.is_active', true)
            ->whereIn('positions.name', ['System Administrator', 'Director', 'Supervisor'])
            ->groupBy('companies.name', 'positions.name')
            ->orderBy('companies.name')
            ->get([
                'companies.name as company_name',
                'positions.name as position_name',
                DB::raw('COUNT(DISTINCT users.id) as user_count'),
            ]);

        foreach (['AndalanKu', 'KMA', 'Niskala', 'RNB', 'RNE', 'TMS', 'Trah'] as $companyName) {
            foreach (['System Administrator', 'Director', 'Supervisor'] as $positionName) {
                $positionCount = $positionCountsByCompany
                    ->first(
                        static fn (object $row): bool => (string) $row->company_name === $companyName
                            && (string) $row->position_name === $positionName,
                    )?->user_count;

                $this->assertSame(1, (int) $positionCount, "{$companyName} harus punya 1 {$positionName}.");
            }
        }

        $rnbStaffCount = DB::table('users')
            ->join('model_has_roles', 'model_has_roles.model_uuid', '=', 'users.id')
            ->join('roles', 'roles.uuid', '=', 'model_has_roles.role_id')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->join('employee_deployments', 'employee_deployments.employee_id', '=', 'employees.id')
            ->where('roles.name', 'Staff')
            ->where('users.is_active', true)
            ->where('employee_deployments.current_company_id', $rnbCompanyId)
            ->whereIn('users.username', ['staff31', 'staff32', 'staff33', 'staff34', 'staff35'])
            ->distinct()
            ->count('users.id');

        $this->assertSame(5, $rnbStaffCount);

        $staffUsernames = DB::table('users')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->join('employee_deployments', 'employee_deployments.employee_id', '=', 'employees.id')
            ->where('employee_deployments.current_company_id', $rnbCompanyId)
            ->whereIn('users.username', ['staff31', 'staff32', 'staff33', 'staff34', 'staff35'])
            ->orderBy('users.username')
            ->pluck('users.username')
            ->all();

        $this->assertSame(['staff31', 'staff32', 'staff33', 'staff34', 'staff35'], $staffUsernames);

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
            ->whereIn('users.username', ['staff31', 'staff32', 'staff33', 'staff34', 'staff35'])
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
            'staff35' => [
                'department' => 'Information and Communications Technology',
                'position' => 'Web Developer',
            ],
        ], $staffAssignments);
    }

    public function test_user_seeder_creates_one_administrator_employee_for_each_company(): void
    {
        $this->seed([
            CompanySeeder::class,
            MetaDataDomiciliSeeder::class,
            MetaDataGenderSeeder::class,
            MetaDataMaritalStatusSeeder::class,
            PositionSeeder::class,
            UserSeeder::class,
        ]);

        $administratorRows = DB::table('companies')
            ->join('employee_deployments', 'employee_deployments.current_company_id', '=', 'companies.id')
            ->join('employees', 'employees.id', '=', 'employee_deployments.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employee_deployments.current_department_id')
            ->join('positions', 'positions.id', '=', 'employee_deployments.current_position_id')
            ->where('departments.name', 'Administrator')
            ->where('positions.name', 'System Administrator')
            ->where('users.username', 'like', 'admin%')
            ->orderBy('companies.name')
            ->get([
                'companies.name as company_name',
                'users.username',
                'users.business_email',
            ]);

        $this->assertCount(7, $administratorRows);

        $administratorAssignments = $administratorRows
            ->mapWithKeys(fn (object $assignment): array => [
                (string) $assignment->company_name => [
                    'username' => (string) $assignment->username,
                    'business_email' => (string) $assignment->business_email,
                ],
            ])
            ->all();

        $this->assertSame([
            'AndalanKu' => [
                'username' => 'admin1',
                'business_email' => 'admin1@andalanku.local',
            ],
            'KMA' => [
                'username' => 'admin2',
                'business_email' => 'admin2@kma.local',
            ],
            'Niskala' => [
                'username' => 'admin4',
                'business_email' => 'admin4@niskala.local',
            ],
            'RNB' => [
                'username' => 'admin3',
                'business_email' => 'admin3@rnb.local',
            ],
            'RNE' => [
                'username' => 'admin5',
                'business_email' => 'admin5@rne.local',
            ],
            'TMS' => [
                'username' => 'admin6',
                'business_email' => 'admin6@tms.local',
            ],
            'Trah' => [
                'username' => 'admin7',
                'business_email' => 'admin7@trah.local',
            ],
        ], $administratorAssignments);
    }
}
