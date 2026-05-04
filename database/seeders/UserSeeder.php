<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
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
                'name' => 'System Superuser',
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
                    'name' => "Director {$directorNumber} - {$company->name}",
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
                        'name' => "Staff {$staffIndex} - {$company->name}",
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
        ?int $companyId,
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
                'phone' => '08'.str_pad((string) $user->id, 10, '0', STR_PAD_LEFT),
                'address' => 'Alamat belum diisi',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        DB::table('user_documents')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'ktp' => str_pad((string) ($user->id + 1000000000000000), 16, '0', STR_PAD_LEFT),
                'kk' => str_pad((string) ($user->id + 1000000000000000), 16, '0', STR_PAD_LEFT),
                'npwp' => 'NPWP-'.str_pad((string) $user->id, 8, '0', STR_PAD_LEFT),
                'bpjs' => 'BPJS-'.str_pad((string) $user->id, 8, '0', STR_PAD_LEFT),
                'bpjstk' => 'BPJSTK-'.str_pad((string) $user->id, 8, '0', STR_PAD_LEFT),
                'nik' => str_pad((string) ($user->id + 2000000000000000), 16, '0', STR_PAD_LEFT),
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        DB::table('user_employments')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'company_id' => $companyId,
                'position_id' => $positionId,
                'division_id' => $divisionId,
                'domicile_id' => $domicileId,
                'start_date' => now()->toDateString(),
                'status' => 'Active',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );
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
}
