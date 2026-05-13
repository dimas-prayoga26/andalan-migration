<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmployeeBankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            if (! Schema::hasTable('employees') || ! Schema::hasTable('employee_bank_accounts')) {
                return;
            }

            $now = now();

            $employees = Employee::query()
                ->with('user')
                ->get();

            foreach ($employees as $employee) {
                $employeeId = (string) $employee->id;
                $displayName = $this->resolveDisplayName($employee);

                $existingPrimaryBankAccount = DB::table('employee_bank_accounts')
                    ->where('employee_id', $employeeId)
                    ->where('is_primary', true)
                    ->first();

                $payload = [
                    'bank_name' => 'BCA',
                    'branch' => 'Yogyakarta',
                    'account_number' => $this->resolveAccountNumber($employeeId),
                    'account_holder_name' => strtoupper($displayName),
                    'is_primary' => true,
                    'updated_at' => $now,
                ];

                if ($existingPrimaryBankAccount) {
                    DB::table('employee_bank_accounts')
                        ->where('id', $existingPrimaryBankAccount->id)
                        ->update($payload);
                } else {
                    DB::table('employee_bank_accounts')->insert($payload + [
                        'id' => (string) Str::uuid(),
                        'employee_id' => $employeeId,
                        'created_at' => $now,
                    ]);
                }
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('EmployeeBankAccountSeeder gagal dijalankan.', 0, $throwable);
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

    private function resolveAccountNumber(string $employeeId): string
    {
        $hexHash = substr(hash('sha256', 'ACCT|'.$employeeId), 0, 16);
        $decimalValue = (string) hexdec(substr($hexHash, 0, 8));

        return str_pad(substr($decimalValue, 0, 12), 12, '0', STR_PAD_LEFT);
    }
}
