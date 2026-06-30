<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
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

            DB::transaction(function () use ($picUsers, $staffUsers): void {
                $this->syncCompanySupervisorAssignments($picUsers, $staffUsers);
                $this->syncExplicitPicAssignments();
            });
        } catch (Throwable $throwable) {
            throw new RuntimeException('EmployeePicAssignmentSeeder gagal dijalankan.', 0, $throwable);
        }
    }

    /**
     * @param  Collection<int, User>  $picUsers
     * @param  Collection<int, User>  $staffUsers
     */
    private function syncCompanySupervisorAssignments(Collection $picUsers, Collection $staffUsers): void
    {
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
            $this->syncPicAssignment($supervisorEmployeeId, $staffEmployeeId);
        }
    }

    private function syncExplicitPicAssignments(): void
    {
        $assignments = [
            'leonieputri7@gmail.com' => [
                'leonieputri7@gmail.com',
                'diktanamira@gmail.com',
                'halloerlin@gmail.com',
            ],
            'msyafiq.dev@gmail.com' => [
                'syarifhidayatullah.040203@gmail.com',
                'rifkafebriza456@gmail.com',
                'dimas.prayoga260403@gmail.com',
            ],
            'rexy@andalanbersama.com' => [
                'arumkusumawati98@gmail.com',
                'dedystwn.interior@gmail.com',
            ],
            'fahmil@andalanbersama.com' => [
                'aryapardomuan@gmail.com',
                'abasyamanyusuf1999@gmail.com',
                'aarissubakti@gmail.com',
                'airarizqi22@gmail.com',
            ],
        ];

        foreach ($assignments as $supervisorEmail => $staffEmails) {
            $supervisorEmployeeId = $this->employeeIdByEmail($supervisorEmail);

            foreach ($staffEmails as $staffEmail) {
                $this->syncPicAssignment($supervisorEmployeeId, $this->employeeIdByEmail($staffEmail));
            }
        }
    }

    private function employeeIdByEmail(string $email): ?string
    {
        $employeeId = User::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->with('employee:id,user_id')
            ->first()
            ?->employee
            ?->id;

        return is_string($employeeId) && trim($employeeId) !== '' ? $employeeId : null;
    }

    private function syncPicAssignment(?string $supervisorEmployeeId, ?string $staffEmployeeId): void
    {
        if (! is_string($supervisorEmployeeId) || trim($supervisorEmployeeId) === '') {
            return;
        }

        if (! is_string($staffEmployeeId) || trim($staffEmployeeId) === '') {
            return;
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
}
