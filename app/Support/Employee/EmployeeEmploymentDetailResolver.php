<?php

namespace App\Support\Employee;

use App\Models\CompanyAsset;
use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeMedicalCheckup;
use App\Models\EmployeePicAssignment;
use App\Services\Attendance\AttendanceContextService;

/**
 * Resolves the values shown in wizard step 5 (Employment & Contract Details), which only HR / admin may fill.
 * The work schedule follows the active Rules of Attendance of the employee's current office location.
 *
 * @phpstan-type EmploymentDetails array{
 *     company: ?string,
 *     employee_code: ?string,
 *     department: ?string,
 *     position: ?string,
 *     supervisor: ?string,
 *     work_location: ?string,
 *     employment_status: ?string,
 *     join_date: ?string,
 *     contract_end_date: ?string,
 *     work_schedule: ?string,
 *     base_salary: ?string,
 *     allowance: ?string,
 *     mcu_records: list<array{date: string, clinic: string, status: string}>,
 *     assets: list<array{name: string, specification: string, serial_number: string, handover_date: string}>
 * }
 */
class EmployeeEmploymentDetailResolver
{
    public const EMPLOYMENT_STATUS_OPTIONS = ['Probation', 'PKWT', 'PKWTT'];

    public function __construct(private readonly AttendanceContextService $attendanceContextService) {}

    /**
     * @return EmploymentDetails
     */
    public function forEmployee(?Employee $employee): array
    {
        $details = [
            'company' => null,
            'employee_code' => null,
            'department' => null,
            'position' => null,
            'supervisor' => null,
            'work_location' => null,
            'employment_status' => null,
            'join_date' => null,
            'contract_end_date' => null,
            'work_schedule' => null,
            'base_salary' => null,
            'allowance' => null,
            'mcu_records' => [],
            'assets' => [],
        ];

        if (! $employee instanceof Employee || ! $employee->exists) {
            return $details;
        }

        /** @var EmployeeDeployment|null $deployment */
        $deployment = $employee->deployment()
            ->with([
                'company:id,name',
                'department:id,name',
                'officeLocation:id,name',
                'position:id,name',
                'positions:id,name',
            ])
            ->first();
        /** @var EmployeePicAssignment|null $picAssignment */
        $picAssignment = $employee->picAssignment()->with('supervisor.profile:id,employee_id,name,nickname')->first();
        $supervisorProfile = $picAssignment?->supervisor?->profile;
        /** @var EmployeeCompensation|null $compensation */
        $compensation = $employee->compensation()->first();

        return [
            'company' => $this->filled($deployment?->company?->name),
            'employee_code' => $this->filled($employee->employee_code),
            'department' => $this->filled($deployment?->department?->name),
            'position' => $this->filled(collect([$deployment?->position?->name])
                ->merge($deployment?->positions?->pluck('name') ?? [])
                ->map(fn (mixed $positionName): string => trim((string) $positionName))
                ->filter()
                ->unique()
                ->implode(', ')),
            'supervisor' => $this->filled($supervisorProfile?->name ?: $supervisorProfile?->nickname),
            'work_location' => $this->filled($deployment?->officeLocation?->name ?? $deployment?->workplace),
            'employment_status' => $this->employmentStatus($deployment),
            'join_date' => $deployment?->join_date?->format('d/m/Y'),
            'contract_end_date' => $deployment?->is_contract ? $deployment->contract_end_date?->format('d/m/Y') : null,
            'work_schedule' => $this->workSchedule($employee),
            'base_salary' => $this->amount($compensation?->base_salary),
            'allowance' => $this->amount($compensation?->allowance),
            'mcu_records' => $employee->medicalCheckups()
                ->orderByDesc('checkup_date')
                ->get()
                ->map(fn (EmployeeMedicalCheckup $medicalCheckup): array => [
                    'date' => $medicalCheckup->checkup_date?->format('d/m/Y') ?? '-',
                    'clinic' => $this->filled($medicalCheckup->clinic_name) ?? '-',
                    'status' => EmployeeMedicalCheckup::resultOptions()[$medicalCheckup->result_status] ?? $this->filled($medicalCheckup->result_status) ?? '-',
                ])
                ->all(),
            'assets' => $employee->assets()
                ->orderBy('handover_date')
                ->orderBy('name')
                ->get()
                ->map(fn (CompanyAsset $companyAsset): array => [
                    'name' => $this->filled($companyAsset->name) ?? '-',
                    'specification' => $this->filled($companyAsset->specification) ?? '-',
                    'serial_number' => $this->filled($companyAsset->serial_number) ?? '-',
                    'handover_date' => $companyAsset->handover_date?->format('d/m/Y') ?? '-',
                ])
                ->all(),
        ];
    }

    private function employmentStatus(?EmployeeDeployment $deployment): ?string
    {
        if ($deployment === null) {
            return null;
        }

        if ($deployment->is_contract) {
            return 'PKWT';
        }

        return $deployment->isOnProbation() ? 'Probation' : 'PKWTT';
    }

    /**
     * Describes the active attendance rule of the employee's office location, e.g. "Fixed (08:00 – 17:00)".
     * Returns null when the location has no active rule, so the attendance fallback hours are not shown as real data.
     */
    private function workSchedule(Employee $employee): ?string
    {
        $officeContext = $this->attendanceContextService->resolveOfficeContext($employee->user_id);

        if ($officeContext === null || ($officeContext['has_attendance_rule'] ?? false) !== true) {
            return null;
        }

        if ($this->attendanceContextService->isFlexibleAttendance($officeContext)) {
            return 'Flexible';
        }

        return sprintf(
            'Fixed (%s – %s)',
            substr((string) $officeContext['office_start_time'], 0, 5),
            substr((string) $officeContext['office_end_time'], 0, 5),
        );
    }

    private function amount(mixed $value): ?string
    {
        $text = $this->filled($value);

        if ($text === null || ! is_numeric($text)) {
            return $text;
        }

        return number_format((float) $text, 0, ',', '.');
    }

    private function filled(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }
}
