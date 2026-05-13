<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmployeeAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            if (! Schema::hasTable('employees') || ! Schema::hasTable('employee_addresses')) {
                return;
            }

            $now = now();

            $employees = Employee::query()
                ->with('user')
                ->get();

            $userProfiles = DB::table('user_profiles')
                ->whereIn('user_id', $employees->pluck('user_id')->filter()->values())
                ->get()
                ->keyBy('user_id');

            foreach ($employees as $employee) {
                $employeeId = (string) $employee->id;
                $userProfile = $userProfiles->get($employee->user_id);

                $existingAddress = DB::table('employee_addresses')
                    ->where('employee_id', $employeeId)
                    ->where('type', 'Domisili')
                    ->first();

                $payload = [
                    'address_line' => $userProfile?->address ?: 'Alamat belum diisi',
                    'village' => null,
                    'subdistrict' => null,
                    'regency' => null,
                    'province' => 'DI Yogyakarta',
                    'country' => 'Indonesia',
                    'postal_code' => null,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];

                if ($existingAddress) {
                    DB::table('employee_addresses')
                        ->where('id', $existingAddress->id)
                        ->update($payload);
                } else {
                    DB::table('employee_addresses')->insert($payload + [
                        'id' => (string) Str::uuid(),
                        'employee_id' => $employeeId,
                        'type' => 'Domisili',
                        'created_at' => $now,
                    ]);
                }
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('EmployeeAddressSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
