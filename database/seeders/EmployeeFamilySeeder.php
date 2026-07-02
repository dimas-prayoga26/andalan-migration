<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmployeeFamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            if (! Schema::hasTable('employees') || ! Schema::hasTable('employee_families')) {
                return;
            }

            $now = now();

            $employees = Employee::query()
                ->with('user')
                ->get();

            foreach ($employees as $employee) {
                $employeeId = (string) $employee->id;
                $displayName = $this->resolveDisplayName($employee);

                $familyRows = [
                    [
                        'name' => "Ayah {$displayName}",
                        'sibling_index' => 1,
                        'relationship' => 'Ayah',
                        'gender' => 'Laki-laki',
                        'place_of_birth' => 'Yogyakarta',
                        'date_of_birth' => now()->subYears(55)->toDateString(),
                        'occupation' => 'Karyawan Swasta',
                        'bpjs_kesehatan_number' => 'BPJS-'.$this->resolveNumericToken($employeeId, '11'),
                        'is_dependents' => true,
                        'is_emergency_contact' => true,
                        'phone_number' => '08'.$this->resolveNumericToken($employeeId, '21'),
                    ],
                    [
                        'name' => "Ibu {$displayName}",
                        'sibling_index' => 2,
                        'relationship' => 'Ibu',
                        'gender' => 'Perempuan',
                        'place_of_birth' => 'Sleman',
                        'date_of_birth' => now()->subYears(52)->toDateString(),
                        'occupation' => 'Ibu Rumah Tangga',
                        'bpjs_kesehatan_number' => 'BPJS-'.$this->resolveNumericToken($employeeId, '12'),
                        'is_dependents' => true,
                        'is_emergency_contact' => false,
                        'phone_number' => '08'.$this->resolveNumericToken($employeeId, '22'),
                    ],
                ];

                foreach ($familyRows as $familyRow) {
                    $existingFamily = DB::table('employee_families')
                        ->where('employee_id', $employeeId)
                        ->where('relationship', $familyRow['relationship'])
                        ->first();

                    $payload = $familyRow + [
                        'updated_at' => $now,
                    ];

                    if ($existingFamily) {
                        DB::table('employee_families')
                            ->where('id', $existingFamily->id)
                            ->update($payload);
                    } else {
                        DB::table('employee_families')->insert($payload + [
                            'id' => (string) Str::uuid(),
                            'employee_id' => $employeeId,
                            'created_at' => $now,
                        ]);
                    }
                }
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('EmployeeFamilySeeder gagal dijalankan.', 0, $throwable);
        }
    }

    private function resolveDisplayName(Employee $employee): string
    {
        $username = is_string($employee->user?->username) ? trim($employee->user->username) : '';
        if ($username !== '') {
            return $username;
        }

        $email = is_string($employee->user?->email) ? trim($employee->user->email) : '';
        if ($email !== '') {
            return (string) explode('@', $email)[0];
        }

        $employeeCode = is_string($employee->employee_code) ? trim($employee->employee_code) : '';

        return $employeeCode !== '' ? $employeeCode : 'Employee';
    }

    private function resolveNumericToken(string $employeeId, string $salt): string
    {
        $hexHash = substr(hash('sha256', $salt.'|'.$employeeId), 0, 16);
        $decimalValue = (string) hexdec(substr($hexHash, 0, 8));

        return str_pad(substr($decimalValue, 0, 10), 10, '0', STR_PAD_LEFT);
    }
}
