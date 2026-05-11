<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = ['superuser', 'Board of Directors', 'Staff'];

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
        $directorDivisionId = $this->toNullableInt(DB::table('meta_data_divisions')->where('name', 'Board of Directors')->value('id'));
        $staffDivisionId = $this->toNullableInt(
            DB::table('meta_data_divisions')->where('name', 'Information and Communications Technology')->value('id')
                ?? DB::table('meta_data_divisions')->where('name', 'Operations')->value('id'),
        );
        $adminDivisionId = $this->toNullableInt(DB::table('meta_data_divisions')->where('name', 'Administrator')->value('id'));
        $directorPositionId = $this->toNullableInt(DB::table('meta_data_positions')->where('name', 'Director')->value('id'));
        $staffPositionId = $this->toNullableInt(
            DB::table('meta_data_positions')->where('name', 'Web Developer')->value('id')
                ?? DB::table('meta_data_positions')->orderBy('id')->value('id'),
        );
        $adminPositionId = $this->toNullableInt(
            DB::table('meta_data_positions')->where('name', 'System Administrator')->value('id')
                ?? DB::table('meta_data_positions')->orderBy('id')->value('id'),
        );
        $jakartaDomicileId = $this->toNullableInt(DB::table('meta_data_domicili')->where('name', 'Jakarta')->value('id'));
        $yogyakartaDomicileId = $this->toNullableInt(DB::table('meta_data_domicili')->where('name', 'Yogyakarta')->value('id'));
        $fallbackDomicileId = $this->toNullableInt(DB::table('meta_data_domicili')->orderBy('id')->value('id'));

        $superuser = User::query()->updateOrCreate(
            ['email' => 'superuser@gmail.com'],
            [
                'username' => 'superuser',
                'name' => 'System Superuser',
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
                    'name' => "Director {$directorNumber} - {$company->name}",
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

            foreach ([1, 2] as $staffIndex) {
                $staff = User::query()->updateOrCreate(
                    ['email' => "staff{$directorNumber}{$staffIndex}@gmail.com"],
                    [
                        'username' => "staff{$directorNumber}{$staffIndex}",
                        'name' => "Staff {$staffIndex} - {$company->name}",
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
    }

    private function seedUserRelations(
        User $user,
        int|string|null $companyId,
        ?int $divisionId,
        ?int $positionId,
        ?int $domicileId,
        ?int $genderId,
        ?int $maritalStatusId,
    ): void {
        $now = now();

        DB::table('user_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'nickname' => $user->name,
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

        EmployeeDeployment::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'current_company_id' => $companyId,
                'current_position_id' => $positionId,
                'current_department_id' => $divisionId,
                'join_date' => now()->toDateString(),
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

    private function resolvePhoneNumber(string $userId): string
    {
        return '08'.$this->resolveShortNumericToken($userId, '10');
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
}
