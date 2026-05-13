<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            $now = now();

            $employees = Employee::query()
                ->with('user')
                ->get();

            $userIds = $employees
                ->pluck('user_id')
                ->filter()
                ->values()
                ->all();

            $userProfiles = DB::table('user_profiles')
                ->whereIn('user_id', $userIds)
                ->get()
                ->keyBy('user_id');

            $genderNamesById = DB::table('meta_data_gender')
                ->select(['id', 'name'])
                ->get()
                ->pluck('name', 'id');

            $maritalStatusNamesById = DB::table('meta_data_marital_statuses')
                ->select(['id', 'name'])
                ->get()
                ->pluck('name', 'id');

            foreach ($employees as $employee) {
                $user = $employee->user;
                if ($user === null) {
                    continue;
                }

                $userProfile = $userProfiles->get($user->id);
                $name = $user->username ?: explode('@', (string) $user->email)[0];

                $existingProfile = DB::table('employee_profiles')
                    ->where('employee_id', $employee->id)
                    ->first();

                $payload = [
                    'name' => $name,
                    'nickname' => $userProfile?->nickname ?: $name,
                    'gender' => $userProfile?->gender_id ? $genderNamesById->get($userProfile->gender_id) : null,
                    'place_of_birth' => $userProfile?->pob,
                    'date_of_birth' => $userProfile?->dob,
                    'nationality' => 'Indonesia',
                    'ethnicity' => null,
                    'marital_status' => $userProfile?->marital_status_id ? $maritalStatusNamesById->get($userProfile->marital_status_id) : null,
                    'religion' => null,
                    'blood_type' => null,
                    'height' => null,
                    'weight' => null,
                    'sibling_count' => null,
                    'sibling_index' => null,
                    'hobbies' => null,
                    'updated_at' => $now,
                ];

                if ($existingProfile) {
                    DB::table('employee_profiles')
                        ->where('employee_id', $employee->id)
                        ->update($payload);
                } else {
                    DB::table('employee_profiles')->insert($payload + [
                        'id' => (string) Str::uuid(),
                        'employee_id' => $employee->id,
                        'created_at' => $now,
                    ]);
                }
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('EmployeeProfileSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
