<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmployeeIdentitySeeder extends Seeder
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
                $maritalStatusName = $userProfile?->marital_status_id
                    ? $maritalStatusNamesById->get($userProfile->marital_status_id)
                    : null;

                $existingIdentity = DB::table('employee_identities')
                    ->where('employee_id', $employee->id)
                    ->first();

                $payload = [
                    'nik' => $existingIdentity?->nik,
                    'kk' => $existingIdentity?->kk,
                    'npwp' => $existingIdentity?->npwp,
                    'bpjs_ketenagakerjaan' => $existingIdentity?->bpjs_ketenagakerjaan,
                    'bpjs_kesehatan' => $existingIdentity?->bpjs_kesehatan,
                    'ptkp_status' => $this->resolvePtkpStatus($maritalStatusName),
                    'updated_at' => $now,
                ];

                if ($existingIdentity) {
                    DB::table('employee_identities')
                        ->where('employee_id', $employee->id)
                        ->update($payload);
                } else {
                    DB::table('employee_identities')->insert($payload + [
                        'id' => (string) Str::uuid(),
                        'employee_id' => $employee->id,
                        'created_at' => $now,
                    ]);
                }
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('EmployeeIdentitySeeder gagal dijalankan.', 0, $throwable);
        }
    }

    private function resolvePtkpStatus(?string $maritalStatus): string
    {
        $normalizedStatus = strtolower(trim((string) $maritalStatus));

        if ($normalizedStatus === 'married' || $normalizedStatus === 'menikah') {
            return 'K/0';
        }

        return 'TK/0';
    }
}
