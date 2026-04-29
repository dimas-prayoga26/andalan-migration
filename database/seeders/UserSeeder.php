<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
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

        $superuser = User::query()->updateOrCreate(
            ['email' => 'superuser@gmail.com'],
            [
                'company_id' => $companies->first()->id,
                'name' => 'System Superuser',
                'password' => Hash::make('password'),
            ],
        );
        $superuser->syncRoles(['superuser']);

        foreach ($companies as $index => $company) {
            $directorNumber = $index + 1;

            $director = User::query()->updateOrCreate(
                ['email' => "director{$directorNumber}@gmail.com"],
                [
                    'company_id' => $company->id,
                    'name' => "Director {$directorNumber} - {$company->name}",
                    'password' => Hash::make('password'),
                ],
            );
            $director->syncRoles(['Board of Directors']);

            foreach ([1, 2] as $staffIndex) {
                $staff = User::query()->updateOrCreate(
                    ['email' => "staff{$directorNumber}{$staffIndex}@gmail.com"],
                    [
                        'company_id' => $company->id,
                        'name' => "Staff {$staffIndex} - {$company->name}",
                        'password' => Hash::make('password'),
                    ],
                );
                $staff->syncRoles(['Staff']);
            }
        }
    }
}
