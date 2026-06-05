<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $roles = ['superuser', 'admin', 'Board of Directors', 'Supervisor', 'Staff'];

            foreach ($roles as $roleName) {
                Role::query()->firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);
            }

            $companies = Company::query()->orderBy('id')->take(7)->get();

            if ($companies->isEmpty()) {
                return;
            }

            $genderId = $this->toNullableInt(
                DB::table('meta_data_gender')->where('name', 'Male')->value('id')
                    ?? DB::table('meta_data_gender')->orderBy('id')->value('id'),
            );
            $maritalStatusId = $this->toNullableInt(
                DB::table('meta_data_marital_statuses')->where('name', 'Single')->value('id')
                    ?? DB::table('meta_data_marital_statuses')->orderBy('id')->value('id'),
            );
            $directorDivisionId = $this->toNullableString(DB::table('departments')->where('name', 'Board of Directors')->value('id'));
            $staffDivisionId = $this->toNullableString(
                DB::table('departments')->where('name', 'Information and Communications Technology')->value('id')
                    ?? DB::table('departments')->where('name', 'Operations')->value('id'),
            );
            $supervisorDivisionId = $this->toNullableString(
                DB::table('departments')->where('name', 'Operations')->value('id')
                    ?? DB::table('departments')->where('name', 'Information and Communications Technology')->value('id'),
            );
            $adminDivisionId = $this->toNullableString(DB::table('departments')->where('name', 'Administrator')->value('id'));
            $directorPositionId = $this->toNullableString(DB::table('positions')->where('name', 'Director')->value('id'));
            $staffPositionId = $this->toNullableString(
                DB::table('positions')->where('name', 'Web Developer')->value('id')
                    ?? DB::table('positions')->orderBy('name')->value('id'),
            );
            $supervisorPositionId = $this->toNullableString(
                DB::table('positions')->where('name', 'Supervisor')->value('id')
                    ?? DB::table('positions')->where('name', 'Team Lead')->value('id')
                    ?? DB::table('positions')->where('name', 'Manager')->value('id')
                    ?? DB::table('positions')->orderBy('name')->value('id'),
            );
            $adminPositionId = $this->toNullableString(
                DB::table('positions')->where('name', 'System Administrator')->value('id')
                    ?? DB::table('positions')->orderBy('name')->value('id'),
            );
            $jakartaDomicileId = $this->toNullableInt(DB::table('meta_data_domicili')->where('name', 'Jakarta')->value('id'));
            $yogyakartaDomicileId = $this->toNullableInt(DB::table('meta_data_domicili')->where('name', 'Yogyakarta')->value('id'));
            $fallbackDomicileId = $this->toNullableInt(DB::table('meta_data_domicili')->orderBy('id')->value('id'));

            $superuser = User::query()->updateOrCreate(
                ['email' => 'superuser@gmail.com'],
                [
                    'username' => 'superuser',
                    'is_active' => true,
                    'password' => Hash::make('password'),
                ],
            );
            $superuser->syncRoles(['superuser']);
            $this->seedUserRelations(
                $superuser,
                companyId: $companies->first()->id,
                divisionId: $adminDivisionId,
                positionId: $adminPositionId,
                domicileId: $jakartaDomicileId ?? $fallbackDomicileId,
                genderId: $genderId,
                maritalStatusId: $maritalStatusId,
            );

            foreach ($companies as $index => $company) {
                $directorNumber = $index + 1;
                $domicileId = $this->resolveDomicileId(
                    companyCity: (string) $company->city,
                    jakartaDomicileId: $jakartaDomicileId,
                    yogyakartaDomicileId: $yogyakartaDomicileId,
                    fallbackDomicileId: $fallbackDomicileId,
                );

                $director = User::query()->updateOrCreate(
                    ['email' => "director{$directorNumber}@gmail.com"],
                    [
                        'username' => "director{$directorNumber}",
                        'business_email' => "director{$directorNumber}@{$this->resolveCompanyEmailDomain((string) $company->name)}",
                        'is_active' => true,
                        'password' => Hash::make('password'),
                    ],
                );
                $director->syncRoles(['Board of Directors']);
                $this->seedUserRelations(
                    $director,
                    companyId: $company->id,
                    divisionId: $directorDivisionId,
                    positionId: $directorPositionId,
                    domicileId: $domicileId,
                    genderId: $genderId,
                    maritalStatusId: $maritalStatusId,
                );

                $supervisor = User::query()->updateOrCreate(
                    ['email' => "supervisor{$directorNumber}@gmail.com"],
                    [
                        'username' => "supervisor{$directorNumber}",
                        'business_email' => "supervisor{$directorNumber}@{$this->resolveCompanyEmailDomain((string) $company->name)}",
                        'is_active' => true,
                        'password' => Hash::make('password'),
                    ],
                );
                $supervisor->syncRoles(['Supervisor']);
                $this->seedUserRelations(
                    $supervisor,
                    companyId: $company->id,
                    divisionId: $supervisorDivisionId,
                    positionId: $supervisorPositionId,
                    domicileId: $domicileId,
                    genderId: $genderId,
                    maritalStatusId: $maritalStatusId,
                );

                $staffIndexes = (string) $company->name === 'RNB'
                    ? [1, 2, 3, 4]
                    : [1, 2];

                foreach ($staffIndexes as $staffIndex) {
                    $staff = User::query()->updateOrCreate(
                        ['email' => "staff{$directorNumber}{$staffIndex}@gmail.com"],
                        [
                            'username' => "staff{$directorNumber}{$staffIndex}",
                            'business_email' => "staff{$directorNumber}{$staffIndex}@{$this->resolveCompanyEmailDomain((string) $company->name)}",
                            'is_active' => true,
                            'password' => Hash::make('password'),
                        ],
                    );
                    $staff->syncRoles(['Staff']);
                    $this->seedUserRelations(
                        $staff,
                        companyId: $company->id,
                        divisionId: $staffDivisionId,
                        positionId: $staffPositionId,
                        domicileId: $domicileId,
                        genderId: $genderId,
                        maritalStatusId: $maritalStatusId,
                    );
                }
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('UserSeeder gagal dijalankan.', 0, $throwable);
        }
    }

    private function seedUserRelations(
        User $user,
        int|string|null $companyId,
        ?string $divisionId,
        ?string $positionId,
        ?int $domicileId,
        ?int $genderId,
        ?int $maritalStatusId,
    ): void {
        $now = now();

        DB::table('user_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'nickname' => $this->resolveDisplayName($user),
                'gender_id' => $genderId,
                'marital_status_id' => $maritalStatusId,
                'phone' => $this->resolvePhoneNumber((string) $user->id),
                'address' => 'Alamat belum diisi',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        DB::table('user_documents')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'ktp' => $this->resolveDocumentNumber((string) $user->id, '11'),
                'kk' => $this->resolveDocumentNumber((string) $user->id, '22'),
                'npwp' => 'NPWP-'.$this->resolveShortNumericToken((string) $user->id, '33'),
                'bpjs' => 'BPJS-'.$this->resolveShortNumericToken((string) $user->id, '44'),
                'bpjstk' => 'BPJSTK-'.$this->resolveShortNumericToken((string) $user->id, '55'),
                'nik' => $this->resolveDocumentNumber((string) $user->id, '66'),
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        $employee = Employee::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code' => 'EMP-'.$this->resolveShortNumericToken((string) $user->id, 'EMP'),
                'status' => 'Active',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        $joinDate = $this->resolveRandomJoinDateFromYearStart((string) $employee->id, $now);

        EmployeeDeployment::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'current_company_id' => $companyId,
                'current_position_id' => $positionId,
                'current_department_id' => $divisionId,
                'join_date' => $joinDate,
                'resignation_date' => null,
                'workplace' => 'Onsite',
                'status' => 'Active',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        $user->forceFill([
            'company_id' => $companyId,
            'phone' => $this->resolvePhoneNumber((string) $user->id),
            'is_active' => true,
        ])->save();
    }

    private function resolveDomicileId(
        string $companyCity,
        ?int $jakartaDomicileId,
        ?int $yogyakartaDomicileId,
        ?int $fallbackDomicileId,
    ): ?int {
        $normalizedCity = strtolower($companyCity);

        if (str_contains($normalizedCity, 'jakarta')) {
            return $jakartaDomicileId ?? $fallbackDomicileId;
        }

        if (str_contains($normalizedCity, 'yogyakarta') || str_contains($normalizedCity, 'jogja')) {
            return $yogyakartaDomicileId ?? $fallbackDomicileId;
        }

        return $fallbackDomicileId;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return (int) $value;
    }

    private function toNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }

    private function resolvePhoneNumber(string $userId): string
    {
        return '08'.$this->resolveShortNumericToken($userId, '10');
    }

    private function resolveDisplayName(User $user): string
    {
        if (is_string($user->username) && trim($user->username) !== '') {
            return (string) $user->username;
        }

        return (string) explode('@', (string) $user->email)[0];
    }

    private function resolveCompanyEmailDomain(string $companyName): string
    {
        $slug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', trim($companyName)));
        $slug = trim($slug, '-');

        if ($slug === '') {
            return 'company.local';
        }

        return "{$slug}.local";
    }

    private function resolveDocumentNumber(string $userId, string $prefix): string
    {
        return substr($prefix.$this->resolveShortNumericToken($userId, $prefix).$this->resolveShortNumericToken(strrev($userId), $prefix), 0, 16);
    }

    private function resolveShortNumericToken(string $value, string $salt): string
    {
        $hexHash = substr(hash('sha256', $salt.'|'.$value), 0, 16);
        $decimalValue = (string) hexdec(substr($hexHash, 0, 8));

        return str_pad(substr($decimalValue, 0, 8), 8, '0', STR_PAD_LEFT);
    }

    private function resolveRandomJoinDateFromYearStart(string $employeeId, Carbon $now): string
    {
        $startOfYear = $now->copy()->startOfYear();
        $today = $now->copy()->startOfDay();
        $dayRange = $startOfYear->diffInDays($today);
        $slotCount = $dayRange + 1;
        $slotHash = substr(hash('sha256', 'join-date|'.$employeeId.'|'.$now->format('Y')), 0, 8);
        $slot = hexdec($slotHash) % max(1, $slotCount);

        return $startOfYear->addDays($slot)->toDateString();
    }
}
