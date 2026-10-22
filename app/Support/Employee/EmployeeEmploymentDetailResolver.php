<?php

namespace App\Support\Employee;

use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeePicAssignment;

/**
 * Resolves the values shown in wizard step 5 (Employment & Contract Details), which only HR / admin may fill.
 * Work schedule, compensation, MCU history and company assets have no storage yet, so they stay empty.
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

    public const WORK_SCHEDULE_OPTIONS = ['Office Hours', 'Roster'];

    public const MCU_STATUS_OPTIONS = ['Fit', 'Fit with Note', 'Unfit'];

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
        ] + $details;
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

    private function filled(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }
}
