<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeProfile;
use Illuminate\Database\Seeder;
use RuntimeException;
use Throwable;

class EmployeeProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $employees = Employee::query()
                ->with('user')
                ->get();

            foreach ($employees as $employee) {
                $user = $employee->user;
                if ($user === null) {
                    continue;
                }

                $name = $user->username ?: explode('@', (string) $user->email)[0];
                $profile = EmployeeProfile::query()->firstOrNew(['employee_id' => $employee->id]);
                $profile->name = filled($profile->name) ? $profile->name : $name;
                $profile->nickname = filled($profile->nickname) ? $profile->nickname : $name;
                $profile->nationality = filled($profile->nationality) ? $profile->nationality : 'Indonesia';
                $profile->save();
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('EmployeeProfileSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
