<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeIdentity;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class NiskalaMultiPicLeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            if (! $this->requiredTablesExist()) {
                return;
            }

            $now = now('Asia/Jakarta');
            $rnbCompany = $this->companyByName('RNB');
            $niskalaCompany = $this->companyByName('Niskala');

            DB::transaction(function () use ($niskalaCompany, $now, $rnbCompany): void {
                $this->seedRnbSecondAdministrator($rnbCompany, $now);

                $mevia = $this->seedEmployeeAccount([
                    'username' => 'staff-rnb-mevia',
                    'email' => 'pic-rnb-mevia@gmail.com',
                    'business_email' => 'mevia.dikta@rnb.local',
                    'name' => 'Mevia Dikta Namira',
                    'employee_code' => 'EMP-RNB-MEVIA',
                    'company_id' => (string) $rnbCompany->id,
                    'department_name' => 'Administration, Finance and Legal',
                    'position_name' => 'Finance and Administration Coordinator',
                    'workplace' => 'RNB Jogja',
                    'phone' => '081300000031',
                ], $now);

                $erlin = $this->seedEmployeeAccount([
                    'username' => 'staff-rnb-erlin',
                    'email' => 'pic-rnb-tsabita@gmail.com',
                    'business_email' => 'erlin.tsabita@rnb.local',
                    'name' => 'Tsabita Anisa Eriliana',
                    'employee_code' => 'EMP-RNB-ERLIN',
                    'company_id' => (string) $rnbCompany->id,
                    'department_name' => 'Administration, Finance and Legal',
                    'position_name' => 'Finance and Administration Coordinator',
                    'workplace' => 'RNB Jogja',
                    'phone' => '081300000032',
                ], $now);

                $leonie = $this->seedEmployeeAccount([
                    'username' => 'staff-niskala-leonie',
                    'email' => 'staff-niskala-leonie@gmail.com',
                    'business_email' => 'leonie.putri@niskala.local',
                    'name' => 'Leonie Putri Andhari',
                    'employee_code' => 'EMP-NISKALA-LEONIE',
                    'company_id' => (string) $niskalaCompany->id,
                    'department_name' => 'Operations',
                    'position_name' => 'Supervisor',
                    'workplace' => 'Niskala',
                    'phone' => '081300000041',
                ], $now);

                $this->deactivatePicAssignmentsForStaff($leonie, $now);
                $this->syncActivePicAssignments($mevia, [$leonie], $now);
                $this->syncActivePicAssignments($erlin, [$leonie], $now);
                $this->seedPendingSupervisorReviewLeaveRequest($mevia, $leonie->user, $now);
            });
        } catch (Throwable $throwable) {
            throw new RuntimeException('NiskalaMultiPicLeaveSeeder gagal dijalankan.', 0, $throwable);
        }
    }

    private function requiredTablesExist(): bool
    {
        return collect([
            'companies',
            'users',
            'user_profiles',
            'user_documents',
            'employees',
            'employee_profiles',
            'employee_deployments',
            'employee_identities',
            'employee_pic_assignments',
            'leave_types',
            'leave_requests',
            'leave_request_histories',
        ])->every(static fn (string $table): bool => Schema::hasTable($table));
    }

    private function companyByName(string $name): Company
    {
        $company = Company::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if (! $company instanceof Company) {
            throw new RuntimeException("Company {$name} tidak ditemukan.");
        }

        return $company;
    }

    private function seedRnbSecondAdministrator(Company $company, Carbon $now): Employee
    {
        return $this->seedEmployeeAccount([
            'username' => 'admin3b',
            'email' => 'admin3b@gmail.com',
            'business_email' => 'admin3b@rnb.local',
            'name' => 'Admin RNB 2',
            'employee_code' => 'EMP-RNB-ADMIN-2',
            'company_id' => (string) $company->id,
            'department_name' => 'Administrator',
            'position_name' => 'System Administrator',
            'workplace' => 'RNB Jogja',
            'phone' => '081300000033',
        ], $now);
    }

    /**
     * @param  array{
     *     username:string,
     *     email:string,
     *     business_email:string,
     *     name:string,
     *     employee_code:string,
     *     company_id:string,
     *     department_name:string,
     *     position_name:string,
     *     workplace:string,
     *     phone:string
     * }  $data
     */
    private function seedEmployeeAccount(array $data, Carbon $now): Employee
    {
        $user = User::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'username' => $data['username'],
                'business_email' => $data['business_email'],
                'company_id' => $data['company_id'],
                'phone' => $data['phone'],
                'is_active' => true,
                'password' => Hash::make('password'),
                'updated_at' => $now,
            ],
        );

        $user->syncRoles(['Staff']);

        DB::table('user_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'nickname' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['workplace'],
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        DB::table('user_documents')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'ktp' => $this->documentNumber($data['employee_code'], '11'),
                'kk' => $this->documentNumber($data['employee_code'], '22'),
                'npwp' => 'NPWP-'.$this->shortNumericToken($data['employee_code'], '33'),
                'bpjs' => 'BPJS-'.$this->shortNumericToken($data['employee_code'], '44'),
                'bpjstk' => 'BPJSTK-'.$this->shortNumericToken($data['employee_code'], '55'),
                'nik' => $this->documentNumber($data['employee_code'], '66'),
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        $employee = Employee::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code' => $data['employee_code'],
                'status' => 'Active',
                'updated_at' => $now,
            ],
        );

        EmployeeProfile::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'name' => $data['name'],
                'nickname' => $data['name'],
                'gender' => 'Female',
                'nationality' => 'Indonesia',
                'marital_status' => 'Single',
            ],
        );

        $identityPayload = [
            'nik' => $this->documentNumber($data['employee_code'], '66'),
            'npwp' => 'NPWP-'.$this->shortNumericToken($data['employee_code'], '33'),
            'bpjs_ketenagakerjaan' => 'BPJSTK-'.$this->shortNumericToken($data['employee_code'], '55'),
            'bpjs_kesehatan' => 'BPJS-'.$this->shortNumericToken($data['employee_code'], '44'),
        ];

        if (Schema::hasColumn('employee_identities', 'ptkp_status')) {
            $identityPayload['ptkp_status'] = 'TK/0';
        }

        EmployeeIdentity::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            $identityPayload,
        );

        EmployeeDeployment::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'current_company_id' => $data['company_id'],
                'current_department_id' => $this->tableIdByName('departments', $data['department_name']),
                'current_position_id' => $this->tableIdByName('positions', $data['position_name']),
                'join_date' => $now->copy()->subYear()->toDateString(),
                'resignation_date' => null,
                'workplace' => $data['workplace'],
                'status' => 'Active',
            ],
        );

        return $employee->fresh(['user']) ?? $employee;
    }

    /**
     * @param  array<int, Employee>  $picEmployees
     */
    private function syncActivePicAssignments(Employee $staffEmployee, array $picEmployees, Carbon $now): void
    {
        $picEmployeeIds = collect($picEmployees)
            ->pluck('id')
            ->filter()
            ->map(static fn (mixed $employeeId): string => (string) $employeeId)
            ->values()
            ->all();

        DB::table('employee_pic_assignments')
            ->where('staff_employee_id', $staffEmployee->id)
            ->whereNotIn('supervisor_employee_id', $picEmployeeIds)
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);

        foreach ($picEmployeeIds as $picEmployeeId) {
            $existingAssignment = DB::table('employee_pic_assignments')
                ->where('supervisor_employee_id', $picEmployeeId)
                ->where('staff_employee_id', $staffEmployee->id)
                ->first(['id']);

            if ($existingAssignment) {
                DB::table('employee_pic_assignments')
                    ->where('id', $existingAssignment->id)
                    ->update([
                        'is_active' => true,
                        'deleted_at' => null,
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('employee_pic_assignments')->insert([
                'id' => (string) Str::uuid(),
                'supervisor_employee_id' => $picEmployeeId,
                'staff_employee_id' => $staffEmployee->id,
                'is_active' => true,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function deactivatePicAssignmentsForStaff(Employee $staffEmployee, Carbon $now): void
    {
        DB::table('employee_pic_assignments')
            ->where('staff_employee_id', $staffEmployee->id)
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);
    }

    private function seedPendingSupervisorReviewLeaveRequest(Employee $staffEmployee, ?User $supervisorUser, Carbon $now): void
    {
        $leaveTypeId = LeaveType::query()
            ->whereRaw('LOWER(code) = ?', ['annual'])
            ->value('id')
            ?? LeaveType::query()->orderBy('name')->value('id');

        if (! is_string($leaveTypeId) || trim($leaveTypeId) === '') {
            throw new RuntimeException('Leave type untuk request Niskala tidak ditemukan.');
        }

        $reason = '[Seeder] RNB staff leave pending Leonie supervisor review';
        $legacyReason = '[Seeder] Niskala multi PIC leave pending supervisor review';
        $existingLeaveRequestIds = LeaveRequest::query()
            ->whereIn('reason', [$reason, $legacyReason])
            ->pluck('id')
            ->all();

        if ($existingLeaveRequestIds !== []) {
            LeaveRequestHistory::query()
                ->whereIn('leave_request_id', $existingLeaveRequestIds)
                ->delete();
            LeaveRequest::query()
                ->whereIn('id', $existingLeaveRequestIds)
                ->delete();
        }

        $leaveDate = $this->currentOrPreviousWorkingDate($now);
        $payload = [
            'employee_id' => $staffEmployee->id,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $leaveDate->toDateString(),
            'end_date' => $leaveDate->toDateString(),
            'total_days' => 1,
            'reason' => $reason,
            'status' => 'pending',
            'is_active' => true,
            'approved_by' => null,
            'approved_at' => null,
            'attachment_path' => null,
            'deleted_at' => null,
        ];

        if (Schema::hasColumn('leave_requests', 'handover_notes')) {
            $payload['handover_notes'] = 'Seed handover notes for RNB staff leave with Leonie as PIC.';
        }

        $leaveRequest = LeaveRequest::query()->create($payload);

        LeaveRequestHistory::query()->create([
            'leave_request_id' => $leaveRequest->id,
            'actor_user_id' => $staffEmployee->user_id,
            'event_type' => 'submitted',
            'title' => 'Request Submitted',
            'from_status' => null,
            'to_status' => 'pending',
            'notes' => null,
            'metadata' => ['seed' => 'niskala_multi_pic'],
            'happened_at' => $leaveDate->copy()->setTime(8, 0),
        ]);

        LeaveRequestHistory::query()->create([
            'leave_request_id' => $leaveRequest->id,
            'actor_user_id' => $supervisorUser?->id,
            'event_type' => 'supervisor_review',
            'title' => 'Supervisor Review',
            'from_status' => 'pending',
            'to_status' => 'pending',
            'notes' => null,
            'metadata' => ['seed' => 'niskala_multi_pic'],
            'happened_at' => $leaveDate->copy()->setTime(9, 0),
        ]);
    }

    private function tableIdByName(string $table, string $name): ?string
    {
        $id = DB::table($table)
            ->where('name', $name)
            ->value('id');

        return is_string($id) && trim($id) !== '' ? trim($id) : null;
    }

    private function currentOrPreviousWorkingDate(Carbon $date): Carbon
    {
        $workingDate = $date->copy()->startOfDay();

        while ($workingDate->isWeekend()) {
            $workingDate->subDay();
        }

        return $workingDate;
    }

    private function documentNumber(string $value, string $prefix): string
    {
        return substr($prefix.$this->shortNumericToken($value, $prefix).$this->shortNumericToken(strrev($value), $prefix), 0, 16);
    }

    private function shortNumericToken(string $value, string $salt): string
    {
        $hexHash = substr(hash('sha256', $salt.'|'.$value), 0, 16);
        $decimalValue = (string) hexdec(substr($hexHash, 0, 8));

        return str_pad(substr($decimalValue, 0, 8), 8, '0', STR_PAD_LEFT);
    }
}
