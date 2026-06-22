<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmployeePicAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            if (! Schema::hasTable('employee_pic_assignments')) {
                return;
            }

            $picUsers = User::query()
                ->where('username', 'like', 'supervisor%')
                ->whereHas('employee.deployment')
                ->with(['employee.deployment'])
                ->get();

            $staffUsers = User::query()
                ->whereHas('roles', function ($query): void {
                    $query->whereRaw('LOWER(name) = ?', ['staff']);
                })
                ->whereHas('employee.deployment')
                ->with(['employee.deployment'])
                ->get();

            if ($picUsers->isEmpty() || $staffUsers->isEmpty()) {
                return;
            }

            $supervisorMapByCompany = [];
            foreach ($picUsers as $supervisorUser) {
                $supervisorEmployeeId = $supervisorUser->employee?->id;
                $companyId = $supervisorUser->employee?->deployment?->current_company_id;
                if (! is_string($supervisorEmployeeId) || trim($supervisorEmployeeId) === '') {
                    continue;
                }
                if (! is_int($companyId) && ! is_string($companyId)) {
                    continue;
                }

                $supervisorMapByCompany[(string) $companyId] = $supervisorEmployeeId;
            }

            if ($supervisorMapByCompany === []) {
                return;
            }

            DB::transaction(function () use ($staffUsers, $supervisorMapByCompany): void {
                foreach ($staffUsers as $staffUser) {
                    $staffEmployeeId = $staffUser->employee?->id;
                    $companyId = $staffUser->employee?->deployment?->current_company_id;
                    if (! is_string($staffEmployeeId) || trim($staffEmployeeId) === '') {
                        continue;
                    }
                    if (! is_int($companyId) && ! is_string($companyId)) {
                        continue;
                    }

                    $supervisorEmployeeId = $supervisorMapByCompany[(string) $companyId] ?? null;
                    if (! is_string($supervisorEmployeeId) || trim($supervisorEmployeeId) === '') {
                        continue;
                    }

                    if ($supervisorEmployeeId === $staffEmployeeId) {
                        continue;
                    }

                    DB::table('employee_pic_assignments')
                        ->where('staff_employee_id', $staffEmployeeId)
                        ->where('supervisor_employee_id', '!=', $supervisorEmployeeId)
                        ->update([
                            'is_active' => false,
                            'updated_at' => now(),
                        ]);

                    DB::table('employee_pic_assignments')->updateOrInsert(
                        [
                            'supervisor_employee_id' => $supervisorEmployeeId,
                            'staff_employee_id' => $staffEmployeeId,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'is_active' => true,
                            'deleted_at' => null,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ],
                    );
                }
            });
        } catch (Throwable $throwable) {
            throw new RuntimeException('EmployeePicAssignmentSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
