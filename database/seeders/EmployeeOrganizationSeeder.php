<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmployeeOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            if (! Schema::hasTable('employees') || ! Schema::hasTable('employee_organization')) {
                return;
            }

            $now = now();

            $employees = Employee::query()
                ->with('deployment.company')
                ->get();

            foreach ($employees as $employee) {
                $employeeId = (string) $employee->id;
                $companyName = is_string($employee->deployment?->company?->name)
                    ? trim((string) $employee->deployment?->company?->name)
                    : '';

                $organizationName = $companyName !== '' ? $companyName : 'Andalan Bersama Group';
                $location = is_string($employee->deployment?->company?->city)
                    ? trim((string) $employee->deployment?->company?->city)
                    : 'Yogyakarta';

                $existingOrganization = DB::table('employee_organization')
                    ->where('employee_id', $employeeId)
                    ->where('organization_name', $organizationName)
                    ->first();

                $payload = [
                    'location' => $location !== '' ? $location : 'Yogyakarta',
                    'start_date' => $employee->deployment?->join_date?->format('Y-m-d') ?? now()->subYears(2)->toDateString(),
                    'end_date' => $employee->deployment?->resignation_date?->format('Y-m-d'),
                    'position' => 'Member',
                    'description' => 'Aktif dalam organisasi internal perusahaan.',
                    'certificate_path' => null,
                    'updated_at' => $now,
                ];

                if ($existingOrganization) {
                    DB::table('employee_organization')
                        ->where('id', $existingOrganization->id)
                        ->update($payload);
                } else {
                    DB::table('employee_organization')->insert($payload + [
                        'id' => (string) Str::uuid(),
                        'employee_id' => $employeeId,
                        'organization_name' => $organizationName,
                        'created_at' => $now,
                    ]);
                }
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('EmployeeOrganizationSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
