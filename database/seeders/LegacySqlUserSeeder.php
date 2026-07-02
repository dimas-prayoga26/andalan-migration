<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class LegacySqlUserSeeder extends Seeder
{
    private const DUMP_FILE = 'rnbmanag_andalanbersamacom.sql';

    /**
     * @var array<int, string>
     */
    private const LEGACY_PLACEHOLDER_ADMIN_EMAILS = [
        'admin@andalanbersama.com',
    ];

    /**
     * @var array<int, string>
     */
    private const EXPLICIT_RNB_USER_EMAILS = [
        'rully.priyatno@andalanbersama.com',
        'hilmi.ulwan@andalanbersama.com',
    ];

    /**
     * @var array{address: string, latitude: float, longitude: float}
     */
    private const RNB_JAKARTA_OFFICE = [
        'address' => '4, Jl. Bhineka Blok Bhineka No.26, RT.4/RW.2, Cipedak, Kec. Jagakarsa, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12630',
        'latitude' => -6.3636699,
        'longitude' => 106.8016359,
    ];

    /**
     * @var array<int, string>
     */
    private array $companyIdsByLegacyId = [];

    /**
     * @var array<int, string>
     */
    private array $departmentIdsByLegacyId = [];

    /**
     * @var array<int, string>
     */
    private array $positionIdsByLegacyId = [];

    /**
     * @var array<int, string>
     */
    private array $roleNamesByLegacyId = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $dump = $this->readDump();

            DB::transaction(function () use ($dump): void {
                app(PermissionRegistrar::class)->forgetCachedPermissions();

                $this->seedCompanies($this->parseInsertRows($dump, 'opt_companies'));
                $this->seedDepartments($this->parseInsertRows($dump, 'opt_departments'));
                $this->seedPositions($this->parseInsertRows($dump, 'opt_positions'));
                $this->seedRoles($this->parseInsertRows($dump, 'opt_roles'));

                $legacyUsers = $this->legacyUsersForImport($this->parseInsertRows($dump, 'users'));
                $this->removeNonLegacyUsers($legacyUsers);
                $this->seedUsers($legacyUsers);
                $this->seedExplicitRnbUsers();
                $this->syncRnbJakartaOfficeAssignments();
                $this->seedPositionPermissions($this->parseInsertRows($dump, 'users_authorization'));
                $this->seedSuperAdministratorAccount();

                app(PermissionRegistrar::class)->forgetCachedPermissions();
            });
        } catch (Throwable $throwable) {
            throw new RuntimeException('LegacySqlUserSeeder gagal dijalankan.', 0, $throwable);
        }
    }

    private function readDump(): string
    {
        $path = database_path('../'.self::DUMP_FILE);

        if (! is_file($path)) {
            throw new RuntimeException("File dump legacy tidak ditemukan: {$path}");
        }

        $dump = file_get_contents($path);

        if (! is_string($dump) || trim($dump) === '') {
            throw new RuntimeException("File dump legacy kosong atau tidak bisa dibaca: {$path}");
        }

        return $dump;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $legacyCompanies
     */
    private function seedCompanies(Collection $legacyCompanies): void
    {
        $legacyCompanies->each(function (array $legacyCompany): void {
            $legacyId = (int) $legacyCompany['id'];
            $company = Company::query()->updateOrCreate(
                ['name' => $this->normalizeCompanyName((string) $legacyCompany['abbreviation'], (string) $legacyCompany['name'])],
                [
                    'legal_name' => $this->nullIfEmpty($legacyCompany['name']),
                    'website' => $this->nullIfEmpty($legacyCompany['website']),
                    'country' => 'Indonesia',
                    'is_active' => true,
                ],
            );

            $this->syncOfficeLocation($company);
            $this->companyIdsByLegacyId[$legacyId] = (string) $company->id;
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $legacyDepartments
     */
    private function seedDepartments(Collection $legacyDepartments): void
    {
        $legacyDepartments->each(function (array $legacyDepartment): void {
            $legacyId = (int) $legacyDepartment['id'];
            $name = $this->normalizeDepartmentName((string) $legacyDepartment['name']);

            $departmentId = DB::table('departments')
                ->where('name', $name)
                ->value('id');

            if (! is_string($departmentId) || trim($departmentId) === '') {
                $departmentId = (string) Str::uuid();

                DB::table('departments')->insert([
                    'id' => $departmentId,
                    'name' => $name,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('departments')
                    ->where('id', $departmentId)
                    ->update([
                        'status' => 'active',
                        'updated_at' => now(),
                    ]);
            }

            $this->departmentIdsByLegacyId[$legacyId] = $departmentId;
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $legacyPositions
     */
    private function seedPositions(Collection $legacyPositions): void
    {
        $legacyPositions->each(function (array $legacyPosition): void {
            $legacyId = (int) $legacyPosition['id'];
            $position = Position::query()->updateOrCreate(
                ['name' => $this->normalizePositionName((string) $legacyPosition['name'])],
                ['status' => 'active'],
            );

            $this->positionIdsByLegacyId[$legacyId] = (string) $position->id;
        });

        foreach (['Administrator', 'Chief Operating Officer', 'Director', 'Supervisor'] as $positionName) {
            Position::query()->updateOrCreate(
                ['name' => $positionName],
                ['status' => 'active'],
            );
        }
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $legacyRoles
     */
    private function seedRoles(Collection $legacyRoles): void
    {
        $legacyRoles->each(function (array $legacyRole): void {
            $legacyId = (int) $legacyRole['id'];
            $roleName = $this->normalizeRoleName((string) $legacyRole['name']);

            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $this->roleNamesByLegacyId[$legacyId] = $roleName;
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $legacyUsers
     */
    private function legacyUsersForImport(Collection $legacyUsers): Collection
    {
        return $legacyUsers
            ->reject(fn (array $legacyUser): bool => $this->isExcludedLegacyUser($legacyUser))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $legacyUser
     */
    private function isExcludedLegacyUser(array $legacyUser): bool
    {
        $email = strtolower(trim((string) ($legacyUser['email'] ?? '')));
        $name = strtolower(trim((string) ($legacyUser['name'] ?? '')));

        return in_array($email, self::LEGACY_PLACEHOLDER_ADMIN_EMAILS, true)
            || $name === 'admin andalan'
            || $email === 'adik@andalanbersama.com'
            || in_array($name, ['adik wiriyanto', 'adik wiryanto'], true);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $legacyUsers
     */
    private function removeNonLegacyUsers(Collection $legacyUsers): void
    {
        $legacyEmails = $legacyUsers
            ->pluck('email')
            ->filter(static fn (mixed $email): bool => is_string($email) && trim($email) !== '')
            ->map(static fn (string $email): string => trim(strtolower($email)))
            ->merge(self::EXPLICIT_RNB_USER_EMAILS)
            ->values()
            ->all();

        if ($legacyEmails === []) {
            return;
        }

        User::query()
            ->whereNotIn(DB::raw('LOWER(email)'), $legacyEmails)
            ->delete();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $legacyUsers
     */
    private function seedUsers(Collection $legacyUsers): void
    {
        $legacyUsers->each(function (array $legacyUser): void {
            $email = $this->legacyEmail($legacyUser);
            $companyId = $this->companyIdsByLegacyId[(int) $legacyUser['company']] ?? null;
            $positionId = $this->overridePositionId($legacyUser)
                ?? $this->positionIdsByLegacyId[(int) $legacyUser['position']]
                ?? null;
            $departmentId = $this->departmentIdsByLegacyId[(int) $legacyUser['department']] ?? null;
            $roleName = $this->roleNamesByLegacyId[(int) $legacyUser['role']] ?? 'Staff';
            $isActive = (int) $legacyUser['status'] === 1;
            $now = now();

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'company_id' => $companyId,
                    'username' => $this->uniqueUsername($legacyUser),
                    'phone' => $this->nullIfEmpty($legacyUser['phone']),
                    'business_email' => $email,
                    'password' => Hash::make('password'),
                    'is_active' => $isActive,
                    'created_at' => $this->normalizeTimestamp($legacyUser['created_at']) ?? $now,
                    'updated_at' => $this->normalizeTimestamp($legacyUser['updated_at']) ?? $now,
                ],
            );

            $user->syncRoles([$roleName]);

            $employee = Employee::withTrashed()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => $this->legacyEmployeeCode($legacyUser),
                    'status' => $isActive ? 'Active' : 'Inactive',
                    'created_at' => $this->normalizeTimestamp($legacyUser['created_at']) ?? $now,
                    'updated_at' => $this->normalizeTimestamp($legacyUser['updated_at']) ?? $now,
                ],
            );

            if ($employee->trashed()) {
                $employee->restore();
            }

            $this->seedEmployeeProfile($employee, $legacyUser);
            $this->seedEmployeeIdentity($employee, $legacyUser);
            $this->seedEmployeeDeployment($employee, $legacyUser, $companyId, $positionId, $departmentId, $isActive);
            $this->seedEmployeeAddress($employee, $legacyUser);
        });
    }

    private function seedExplicitRnbUsers(): void
    {
        $companyId = Company::query()
            ->whereRaw('LOWER(name) = ?', ['rnb'])
            ->value('id');
        $departmentId = DB::table('departments')
            ->where('name', 'Operations')
            ->value('id');
        $positionId = DB::table('positions')
            ->where('name', 'Operations Coordinator')
            ->value('id');

        if (! is_string($companyId) || ! is_string($positionId)) {
            throw new RuntimeException('Company RNB atau position Operations Coordinator tidak ditemukan.');
        }

        Role::query()->firstOrCreate([
            'name' => 'Staff',
            'guard_name' => 'web',
        ]);

        $users = [
            [
                'name' => 'Rully Priyatno',
                'nickname' => 'Rully',
                'email' => 'rully.priyatno@andalanbersama.com',
                'username' => 'rully.priyatno',
                'employee_code' => 'EMP-RULLY-PRIYATNO',
            ],
            [
                'name' => 'Hilmi Ulwan',
                'nickname' => 'Hilmi',
                'email' => 'hilmi.ulwan@andalanbersama.com',
                'username' => 'hilmi.ulwan',
                'employee_code' => 'EMP-HILMI-ULWAN',
            ],
        ];

        foreach ($users as $userData) {
            $now = now();
            $user = User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'company_id' => $companyId,
                    'username' => $userData['username'],
                    'business_email' => $userData['email'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
            $user->syncRoles(['Staff']);

            $employee = Employee::withTrashed()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => $userData['employee_code'],
                    'status' => 'Active',
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            if ($employee->trashed()) {
                $employee->restore();
            }

            DB::table('employee_profiles')->updateOrInsert(
                ['employee_id' => $employee->id],
                [
                    'id' => (string) (DB::table('employee_profiles')->where('employee_id', $employee->id)->value('id') ?? Str::uuid()),
                    'name' => $userData['name'],
                    'nickname' => $userData['nickname'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            $existingJoinDate = EmployeeDeployment::query()
                ->where('employee_id', $employee->id)
                ->value('join_date');
            $joinDate = is_string($existingJoinDate) && trim($existingJoinDate) !== ''
                ? $existingJoinDate
                : $now->toDateString();
            $deployment = EmployeeDeployment::query()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'current_company_id' => $companyId,
                    'current_position_id' => $positionId,
                    'current_department_id' => is_string($departmentId) ? $departmentId : null,
                    ...$this->optionalOfficeLocationPayload($this->officeLocationIdForCompany($companyId)),
                    'join_date' => $joinDate,
                    'resignation_date' => null,
                    'workplace' => 'RNB Jakarta',
                    'status' => 'Active',
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            $this->syncDeploymentPosition($deployment, $positionId, $joinDate, null);
        }
    }

    private function syncRnbJakartaOfficeAssignments(): void
    {
        if (! Schema::hasTable('office_locations') || ! Schema::hasColumn('employee_deployments', 'current_office_location_id')) {
            return;
        }

        $companyId = Company::query()
            ->whereRaw('LOWER(name) = ?', ['rnb'])
            ->value('id');

        if (! is_string($companyId) || trim($companyId) === '') {
            throw new RuntimeException('Company RNB tidak ditemukan untuk assignment office Jakarta.');
        }

        $officeLocationId = DB::table('office_locations')
            ->where('company_id', $companyId)
            ->where('address', self::RNB_JAKARTA_OFFICE['address'])
            ->value('id');
        $officeLocationId = is_string($officeLocationId) && trim($officeLocationId) !== ''
            ? $officeLocationId
            : (string) Str::uuid();
        $now = now();

        DB::table('office_locations')->updateOrInsert(
            ['id' => $officeLocationId],
            [
                'company_id' => $companyId,
                'address' => self::RNB_JAKARTA_OFFICE['address'],
                'latitude' => self::RNB_JAKARTA_OFFICE['latitude'],
                'longitude' => self::RNB_JAKARTA_OFFICE['longitude'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        $employeeIds = Employee::query()
            ->whereHas('user', function ($query): void {
                $query->whereIn('email', [
                    'lukman@rnbmanagement.com',
                    'rully.priyatno@andalanbersama.com',
                    'hilmi.ulwan@andalanbersama.com',
                ]);
            })
            ->pluck('id');

        if ($employeeIds->count() !== 3) {
            throw new RuntimeException('Employee Lukman, Rully, atau Hilmi tidak lengkap untuk assignment office Jakarta.');
        }

        EmployeeDeployment::query()
            ->whereIn('employee_id', $employeeIds)
            ->update([
                'current_company_id' => $companyId,
                'current_office_location_id' => $officeLocationId,
                'workplace' => 'RNB Jakarta',
                'status' => 'Active',
                'resignation_date' => null,
                'deleted_at' => null,
                'updated_at' => $now,
            ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $legacyAuthorizations
     */
    private function seedPositionPermissions(Collection $legacyAuthorizations): void
    {
        $permissionsByName = collect($this->activePermissionNames())
            ->mapWithKeys(static function (string $permissionName): array {
                $permission = Permission::query()->firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);

                return [$permissionName => (string) $permission->uuid];
            });

        $positionPermissionNames = [];

        $legacyAuthorizations->each(function (array $legacyAuthorization) use (&$positionPermissionNames): void {
            $permissionNames = $this->permissionNamesForLegacyMenu((string) $legacyAuthorization['menu']);

            if ($permissionNames === []) {
                return;
            }

            foreach ($this->legacyPositionIdsFromAuthorization((string) $legacyAuthorization['id_position']) as $legacyPositionId) {
                $positionId = $this->positionIdsByLegacyId[$legacyPositionId] ?? null;

                if (! is_string($positionId) || trim($positionId) === '') {
                    continue;
                }

                $positionPermissionNames[$positionId] = array_values(array_unique(array_merge(
                    $positionPermissionNames[$positionId] ?? [],
                    $permissionNames,
                )));
            }
        });

        Position::query()->get()->each(function (Position $position) use ($positionPermissionNames, $permissionsByName): void {
            $permissionIds = collect($positionPermissionNames[(string) $position->id] ?? [])
                ->map(static fn (string $permissionName): ?string => $permissionsByName->get($permissionName))
                ->filter()
                ->values()
                ->all();

            $position->permissions()->sync($permissionIds);
        });

        $this->syncRolePermissions();
    }

    private function seedEmployeeProfile(Employee $employee, array $legacyUser): void
    {
        DB::table('employee_profiles')->updateOrInsert(
            ['employee_id' => $employee->id],
            [
                'id' => (string) (DB::table('employee_profiles')->where('employee_id', $employee->id)->value('id') ?? Str::uuid()),
                'name' => $this->nullIfEmpty($legacyUser['name']),
                'nickname' => $this->nullIfEmpty($legacyUser['nickname']),
                'gender' => $this->legacyGenderName((int) $legacyUser['gender']),
                'place_of_birth' => $this->nullIfEmpty($legacyUser['pob']),
                'date_of_birth' => $this->normalizeDate($legacyUser['dob']),
                'marital_status' => $this->legacyMaritalStatusName((int) $legacyUser['marital_status']),
                'profile_picture_path' => $this->nullIfEmpty($legacyUser['profile_picture']),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    private function overridePositionId(array $legacyUser): ?string
    {
        $email = strtolower(trim((string) ($legacyUser['email'] ?? '')));

        $positionName = match ($email) {
            'diktanamira@gmail.com' => 'Administrator',
            default => null,
        };

        if ($positionName === null) {
            return null;
        }

        $positionId = DB::table('positions')
            ->where('name', $positionName)
            ->value('id');

        return is_string($positionId) && trim($positionId) !== ''
            ? $positionId
            : null;
    }

    private function seedEmployeeIdentity(Employee $employee, array $legacyUser): void
    {
        DB::table('employee_identities')->updateOrInsert(
            ['employee_id' => $employee->id],
            [
                'id' => (string) (DB::table('employee_identities')->where('employee_id', $employee->id)->value('id') ?? Str::uuid()),
                'nik' => $this->nullIfZeroValue($legacyUser['nik']),
                'kk' => $this->nullIfZeroValue($legacyUser['kk']),
                'npwp' => $this->nullIfZeroValue($legacyUser['npwp']),
                'bpjs_ketenagakerjaan' => $this->nullIfZeroValue($legacyUser['bpjstk']),
                'bpjs_kesehatan' => $this->nullIfZeroValue($legacyUser['bpjs']),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    private function seedEmployeeDeployment(
        Employee $employee,
        array $legacyUser,
        ?string $companyId,
        ?string $positionId,
        ?string $departmentId,
        bool $isActive,
    ): void {
        $officeLocationId = $this->officeLocationIdForCompany($companyId);

        $joinDate = $this->normalizeJoinDate($legacyUser);
        $resignationDate = $this->legacyResignationDate($legacyUser);

        $deployment = EmployeeDeployment::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'current_company_id' => $companyId,
                'current_position_id' => $positionId,
                'current_department_id' => $departmentId,
                ...$this->optionalOfficeLocationPayload($officeLocationId),
                'join_date' => $joinDate,
                'resignation_date' => $resignationDate,
                'workplace' => $this->nullIfEmpty($legacyUser['address']),
                'status' => $isActive ? 'Active' : 'Inactive',
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        $this->syncDeploymentPosition($deployment, $positionId, $joinDate, $resignationDate);
        $this->syncAdditionalLegacyPositions($deployment, $legacyUser, $joinDate, $resignationDate);
    }

    private function syncDeploymentPosition(EmployeeDeployment $deployment, ?string $positionId, ?string $joinDate, ?string $resignationDate): void
    {
        if (! Schema::hasTable('employee_deployment_positions') || ! is_string($positionId) || trim($positionId) === '') {
            return;
        }

        DB::table('employee_deployment_positions')->updateOrInsert(
            [
                'employee_deployment_id' => $deployment->id,
                'position_id' => $positionId,
            ],
            [
                'is_primary' => true,
                'status' => 'active',
                'started_at' => $joinDate,
                'ended_at' => $resignationDate,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    private function syncAdditionalLegacyPositions(EmployeeDeployment $deployment, array $legacyUser, ?string $joinDate, ?string $resignationDate): void
    {
        $email = strtolower(trim((string) ($legacyUser['email'] ?? '')));

        if (! in_array($email, $this->managedAdditionalPositionEmails(), true)) {
            return;
        }

        $positionNames = $this->additionalPositionNamesForLegacyUser($legacyUser);
        $positionIds = DB::table('positions')
            ->whereIn('name', $positionNames)
            ->pluck('id')
            ->filter(static fn (mixed $positionId): bool => is_string($positionId) && trim($positionId) !== '')
            ->values();
        $managedPositionIds = DB::table('positions')
            ->whereIn('name', ['Administrator', 'Accounting and Taxation', 'Director', 'Supervisor'])
            ->pluck('id');

        DB::table('employee_deployment_positions')
            ->where('employee_deployment_id', $deployment->id)
            ->where('is_primary', false)
            ->whereIn('position_id', $managedPositionIds->all())
            ->when(
                $positionIds->isNotEmpty(),
                fn ($query) => $query->whereNotIn('position_id', $positionIds->all()),
            )
            ->delete();

        foreach ($positionIds as $positionId) {
            if (! is_string($positionId) || trim($positionId) === '') {
                continue;
            }

            DB::table('employee_deployment_positions')->updateOrInsert(
                [
                    'employee_deployment_id' => $deployment->id,
                    'position_id' => $positionId,
                ],
                [
                    'is_primary' => false,
                    'status' => 'active',
                    'started_at' => $joinDate,
                    'ended_at' => $resignationDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function additionalPositionNamesForLegacyUser(array $legacyUser): array
    {
        $email = strtolower(trim((string) ($legacyUser['email'] ?? '')));

        return match ($email) {
            'halloerlin@gmail.com' => ['Administrator', 'Accounting and Taxation'],
            'diktanamira@gmail.com' => ['Administrator', 'Accounting and Taxation'],
            'msyafiq.dev@gmail.com' => ['Supervisor'],
            'rexy@andalanbersama.com' => ['Director', 'Supervisor'],
            'fuadmfahrudin@gmail.com' => ['Director', 'Supervisor'],
            'fahmil@andalanbersama.com' => ['Director', 'Supervisor'],
            'lukman@rnbmanagement.com' => ['Supervisor'],
            'leonieputri7@gmail.com' => ['Supervisor'],
            default => [],
        };
    }

    /**
     * @return array<int, string>
     */
    private function managedAdditionalPositionEmails(): array
    {
        return [
            'halloerlin@gmail.com',
            'diktanamira@gmail.com',
            'msyafiq.dev@gmail.com',
            'rexy@andalanbersama.com',
            'fuadmfahrudin@gmail.com',
            'fahmil@andalanbersama.com',
            'lukman@rnbmanagement.com',
            'leonieputri7@gmail.com',
        ];
    }

    /**
     * @return array{address: string, latitude: float, longitude: float}
     */
    private function defaultOfficeLocationData(): array
    {
        return [
            'address' => 'Bulurejo, RT.04/RW.02, Gantalan, Minomartani, Kec. Ngaglik, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55581',
            'latitude' => -7.7299965,
            'longitude' => 110.4040011,
        ];
    }

    private function syncOfficeLocation(Company $company): void
    {
        if (! Schema::hasTable('office_locations')) {
            return;
        }

        $existingOfficeLocationId = DB::table('office_locations')
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('created_at')
            ->value('id');

        if (! is_string($existingOfficeLocationId) || trim($existingOfficeLocationId) === '') {
            $officeData = $this->defaultOfficeLocationData();

            DB::table('office_locations')->insert([
                'id' => (string) Str::uuid(),
                'company_id' => $company->id,
                'address' => $officeData['address'],
                'latitude' => $officeData['latitude'],
                'longitude' => $officeData['longitude'],
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }

    private function officeLocationIdForCompany(?string $companyId): ?string
    {
        if (! Schema::hasTable('office_locations') || ! Schema::hasColumn('employee_deployments', 'current_office_location_id')) {
            return null;
        }

        if (! is_string($companyId) || trim($companyId) === '') {
            return null;
        }

        $officeLocationId = DB::table('office_locations')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('created_at')
            ->value('id');

        return is_string($officeLocationId) && trim($officeLocationId) !== ''
            ? $officeLocationId
            : null;
    }

    /**
     * @return array<string, string|null>
     */
    private function optionalOfficeLocationPayload(?string $officeLocationId): array
    {
        if (! Schema::hasColumn('employee_deployments', 'current_office_location_id')) {
            return [];
        }

        return ['current_office_location_id' => $officeLocationId];
    }

    private function seedEmployeeAddress(Employee $employee, array $legacyUser): void
    {
        if (! Schema::hasTable('employee_addresses')) {
            return;
        }

        DB::table('employee_addresses')->updateOrInsert(
            [
                'employee_id' => $employee->id,
                'type' => 'domicile',
            ],
            [
                'id' => (string) (DB::table('employee_addresses')
                    ->where('employee_id', $employee->id)
                    ->where('type', 'domicile')
                    ->value('id') ?? Str::uuid()),
                'address_line' => $this->nullIfEmpty($legacyUser['address']),
                'province' => $this->legacyDomicileName((int) $legacyUser['domicile']),
                'country' => 'Indonesia',
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function parseInsertRows(string $dump, string $table): Collection
    {
        $pattern = '/INSERT INTO `'.preg_quote($table, '/').'` \((.*?)\) VALUES\s*(.*?);/s';

        if (preg_match($pattern, $dump, $matches) !== 1) {
            return collect();
        }

        preg_match_all('/`([^`]+)`/', $matches[1], $columnMatches);
        $columns = $columnMatches[1] ?? [];

        return collect($this->splitSqlTuples($matches[2]))
            ->map(fn (string $tuple): array => array_combine($columns, $this->parseSqlTuple($tuple)) ?: [])
            ->filter(static fn (array $row): bool => $row !== [])
            ->values();
    }

    /**
     * @return array<int, string>
     */
    private function splitSqlTuples(string $valuesSql): array
    {
        $tuples = [];
        $buffer = '';
        $depth = 0;
        $inString = false;
        $length = strlen($valuesSql);

        for ($index = 0; $index < $length; $index++) {
            $char = $valuesSql[$index];
            $previous = $index > 0 ? $valuesSql[$index - 1] : '';

            if ($char === "'" && $previous !== '\\') {
                $inString = ! $inString;
            }

            if (! $inString && $char === '(') {
                $depth++;

                if ($depth === 1) {
                    $buffer = '';

                    continue;
                }
            }

            if (! $inString && $char === ')') {
                $depth--;

                if ($depth === 0) {
                    $tuples[] = $buffer;
                    $buffer = '';

                    continue;
                }
            }

            if ($depth > 0) {
                $buffer .= $char;
            }
        }

        return $tuples;
    }

    /**
     * @return array<int, mixed>
     */
    private function parseSqlTuple(string $tuple): array
    {
        $values = [];
        $buffer = '';
        $inString = false;
        $length = strlen($tuple);

        for ($index = 0; $index < $length; $index++) {
            $char = $tuple[$index];
            $previous = $index > 0 ? $tuple[$index - 1] : '';

            if ($char === "'" && $previous !== '\\') {
                $inString = ! $inString;
                $buffer .= $char;

                continue;
            }

            if ($char === ',' && ! $inString) {
                $values[] = $this->normalizeSqlValue($buffer);
                $buffer = '';

                continue;
            }

            $buffer .= $char;
        }

        $values[] = $this->normalizeSqlValue($buffer);

        return $values;
    }

    private function normalizeSqlValue(string $value): mixed
    {
        $value = trim($value);

        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }

        if (str_starts_with($value, "'") && str_ends_with($value, "'")) {
            $value = substr($value, 1, -1);
            $value = str_replace(["\\'", '\\\\', '\r', '\n'], ["'", '\\', "\r", "\n"], $value);
        }

        return $value;
    }

    private function normalizeCompanyName(string $abbreviation, string $name): string
    {
        $abbreviation = trim($abbreviation);

        if ($abbreviation !== '') {
            return match (strtoupper($abbreviation)) {
                'TRH' => 'Trah',
                'ABG' => 'AndalanKu',
                default => $abbreviation,
            };
        }

        return trim($name);
    }

    private function normalizeDepartmentName(string $name): string
    {
        return match (trim($name)) {
            'Administrastion, Finance and Legal' => 'Administration, Finance and Legal',
            default => trim($name),
        };
    }

    private function normalizePositionName(string $name): string
    {
        return match (trim($name)) {
            'System Administrator' => 'Administrator',
            default => trim($name),
        };
    }

    private function normalizeRoleName(string $name): string
    {
        return trim($name) === 'Superuser' ? 'superuser' : trim($name);
    }

    private function legacyEmail(array $legacyUser): string
    {
        $email = trim((string) $legacyUser['email']);

        if ($email !== '') {
            return strtolower($email);
        }

        return 'legacy-user-'.$legacyUser['id'].'@legacy.local';
    }

    private function uniqueUsername(array $legacyUser): string
    {
        $username = trim((string) $legacyUser['username']);

        if ($username === '') {
            $username = Str::slug((string) $legacyUser['name']);
        }

        if ($username === '') {
            $username = 'legacy-user-'.$legacyUser['id'];
        }

        return $username;
    }

    private function legacyEmployeeCode(array $legacyUser): string
    {
        $nik = $this->nullIfZeroValue($legacyUser['nik']);

        if (is_string($nik) && trim($nik) !== '') {
            return 'EMP-'.$nik;
        }

        return 'EMP-OLD-'.str_pad((string) $legacyUser['id'], 4, '0', STR_PAD_LEFT);
    }

    private function normalizeJoinDate(array $legacyUser): ?string
    {
        return $this->normalizeDate($legacyUser['start_date'])
            ?? $this->normalizeDate($legacyUser['created_at']);
    }

    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function normalizeResignationDate(mixed $value): ?string
    {
        $normalizedDate = $this->normalizeDate($value);

        return $normalizedDate === '1970-01-01' ? null : $normalizedDate;
    }

    /**
     * @param  array<string, mixed>  $legacyUser
     */
    private function legacyResignationDate(array $legacyUser): ?string
    {
        if ((int) ($legacyUser['status'] ?? 0) === 1) {
            return null;
        }

        return $this->normalizeResignationDate($legacyUser['end_date'] ?? null);
    }

    private function seedSuperAdministratorAccount(): void
    {
        $now = now();
        $companyId = Company::query()
            ->whereRaw('LOWER(name) = ?', ['rnb'])
            ->value('id')
            ?? Company::query()->orderBy('name')->value('id');
        $departmentId = DB::table('departments')->where('name', 'Administrator')->value('id');
        $positionId = DB::table('positions')->where('name', 'Super Administrator')->value('id');

        if (! is_string($positionId) || trim($positionId) === '') {
            throw new RuntimeException('Position Super Administrator tidak ditemukan.');
        }

        Role::query()->firstOrCreate([
            'name' => 'superuser',
            'guard_name' => 'web',
        ]);

        $user = User::query()->updateOrCreate(
            ['email' => 'superadmin@andalanbersama.com'],
            [
                'company_id' => $companyId,
                'username' => 'superadmin',
                'business_email' => 'superadmin@andalanbersama.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
        $user->syncRoles(['superuser']);

        $employee = Employee::withTrashed()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code' => 'EMP-SUPERADMIN',
                'status' => 'Active',
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        if ($employee->trashed()) {
            $employee->restore();
        }

        DB::table('employee_profiles')->updateOrInsert(
            ['employee_id' => $employee->id],
            [
                'id' => (string) (DB::table('employee_profiles')->where('employee_id', $employee->id)->value('id') ?? Str::uuid()),
                'name' => 'superadmin',
                'nickname' => 'superadmin',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        $deployment = EmployeeDeployment::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'current_company_id' => $companyId,
                'current_position_id' => $positionId,
                'current_department_id' => is_string($departmentId) ? $departmentId : null,
                ...$this->optionalOfficeLocationPayload($this->officeLocationIdForCompany(is_string($companyId) ? $companyId : null)),
                'join_date' => $now->toDateString(),
                'resignation_date' => null,
                'workplace' => 'RNB',
                'status' => 'Active',
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        $this->syncDeploymentPosition($deployment, $positionId, $now->toDateString(), null);
    }

    private function normalizeTimestamp(mixed $value): ?Carbon
    {
        $date = $this->normalizeDate($value);

        if ($date === null) {
            return null;
        }

        return Carbon::parse($value);
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullIfZeroValue(mixed $value): ?string
    {
        $value = $this->nullIfEmpty($value);

        if ($value === null) {
            return null;
        }

        $normalized = str_replace(['_', '-', ' '], '', $value);

        if ($normalized === '' || preg_match('/^0+$/', $normalized) === 1) {
            return null;
        }

        return $value;
    }

    private function legacyGenderName(int $legacyGenderId): ?string
    {
        return match ($legacyGenderId) {
            1 => 'Male',
            2 => 'Female',
            default => null,
        };
    }

    private function legacyMaritalStatusName(int $legacyMaritalStatusId): ?string
    {
        return match ($legacyMaritalStatusId) {
            1 => 'Single',
            2 => 'Married',
            default => null,
        };
    }

    private function legacyDomicileName(int $legacyDomicileId): ?string
    {
        return match ($legacyDomicileId) {
            1 => 'Daerah Istimewa Yogyakarta',
            2 => 'Daerah Khusus Ibukota Jakarta',
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    private function activePermissionNames(): array
    {
        return [
            'view-dashboard',
            'view-calendar',
            'view-attendance',
            'view-timesheet-reporting',
            'view-admin-attendance',
            'view-pic-attendance',
            'view-director-attendance',
            'view-authorization',
            'view-employee-database',
            'view-talent-acquisition',
            'view-meeting',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function permissionNamesForLegacyMenu(string $menu): array
    {
        return match (trim($menu)) {
            'dahsboard' => ['view-dashboard'],
            'calendar' => ['view-calendar'],
            'attendances' => ['view-attendance'],
            'attendances-data' => ['view-admin-attendance', 'view-pic-attendance', 'view-director-attendance'],
            'reports' => ['view-timesheet-reporting'],
            'users' => ['view-authorization', 'view-employee-database'],
            'applicants' => ['view-talent-acquisition'],
            'meeting' => ['view-meeting'],
            default => [],
        };
    }

    /**
     * @return array<int, int>
     */
    private function legacyPositionIdsFromAuthorization(string $legacyPositionIds): array
    {
        return collect(explode(',', $legacyPositionIds))
            ->map(static fn (string $legacyPositionId): int => (int) trim($legacyPositionId))
            ->filter(static fn (int $legacyPositionId): bool => $legacyPositionId > 0 && $legacyPositionId < 100)
            ->values()
            ->all();
    }

    private function syncRolePermissions(): void
    {
        Role::query()
            ->where('name', 'superuser')
            ->first()
            ?->syncPermissions([
                'view-dashboard',
                'view-calendar',
                'view-attendance',
                'view-timesheet-reporting',
                'view-admin-attendance',
                'view-pic-attendance',
                'view-director-attendance',
                'view-authorization',
                'view-employee-database',
                'view-talent-acquisition',
                'view-meeting',
            ]);

        Role::query()
            ->where('name', 'Board of Directors')
            ->first()
            ?->syncPermissions([
                'view-dashboard',
                'view-calendar',
                'view-attendance',
                'view-timesheet-reporting',
                'view-admin-attendance',
                'view-authorization',
                'view-employee-database',
                'view-talent-acquisition',
                'view-meeting',
            ]);

        Role::query()
            ->where('name', 'Staff')
            ->first()
            ?->syncPermissions([
                'view-dashboard',
                'view-calendar',
                'view-attendance',
                'view-timesheet-reporting',
                'view-meeting',
            ]);
    }
}
