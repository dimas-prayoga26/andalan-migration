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
                ->with(['user.userProfile', 'deployment.company'])
                ->get();

            foreach ($employees as $employee) {
                $employeeId = (string) $employee->id;
                $userProfile = $employee->user?->userProfile;
                $companyAddress = is_string($employee->deployment?->company?->address)
                    ? trim((string) $employee->deployment?->company?->address)
                    : '';
                $companyCity = is_string($employee->deployment?->company?->city)
                    ? trim((string) $employee->deployment?->company?->city)
                    : '';
                $addressLine = is_string($userProfile?->address) && trim((string) $userProfile?->address) !== ''
                    ? trim((string) $userProfile?->address)
                    : ($companyAddress !== '' ? $companyAddress : 'Alamat belum diisi');
                $regency = $companyCity !== '' ? $companyCity : 'Sleman';
                $subdistrict = $regency === 'Jakarta' ? 'Tanah Abang' : 'Depok';
                $village = $subdistrict === 'Tanah Abang' ? 'Kebon Kacang' : 'Condongcatur';

                $existingAddress = DB::table('employee_addresses')
                    ->where('employee_id', $employeeId)
                    ->where('type', 'Domisili')
                    ->first();

                $payload = [
                    'address_line' => $addressLine,
                    'village' => $village,
                    'subdistrict' => $subdistrict,
                    'regency' => $regency,
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
