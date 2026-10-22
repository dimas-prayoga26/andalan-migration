<?php

namespace App\Support\Employee;

use App\Models\CompanyAsset;
use App\Models\EducationLevel;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeCertification;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeEducation;
use App\Models\EmployeeFamily;
use App\Models\EmployeeLicense;
use App\Models\EmployeeMedicalCheckup;
use App\Models\EmployeeReference;
use App\Models\EmployeeWorkExperience;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Raw values that prefill the inputs of the "Data Karyawan" wizard, grouped by step.
 * Dates use Y-m-d (native date inputs) and repeatable rows keep their id so a save updates instead of duplicating.
 */
class EmployeeDataWizardValues
{
    private const ADDRESS_COLUMNS = ['province', 'regency', 'subdistrict', 'village', 'address_line'];

    /**
     * Education levels from the lowest to the highest; unknown levels are listed last.
     */
    private const EDUCATION_LEVEL_ORDER = ['SD', 'SMP', 'SMA', 'SMK', 'Diploma', 'D1', 'D2', 'D3', 'D4', 'Strata 1', 'S1', 'Strata 2', 'S2', 'Strata 3', 'S3'];

    /**
     * @return array<string, mixed>
     */
    public function forEmployee(?Employee $employee): array
    {
        $values = [
            'education_levels' => $this->educationLevelOptions(),
            'personal' => ['addresses' => ['ktp' => $this->addressValues(null), 'domicile' => $this->addressValues(null)], 'domicile_same_as_ktp' => false],
            'legal' => ['bank' => [], 'licenses' => []],
            'education' => ['educations' => [], 'certifications' => [], 'work_experiences' => [], 'references' => [], 'soft_skills' => []],
            'family' => ['families' => [], 'emergency_contacts' => []],
            'employment' => ['medical_checkups' => [], 'assets' => []],
        ];

        if (! $employee instanceof Employee || ! $employee->exists) {
            return $values;
        }

        $employee->loadMissing(['user:id,phone', 'profile', 'identity', 'deployment']);
        $profile = $employee->profile;
        $identity = $employee->identity;
        $addresses = EmployeeAddress::query()
            ->where('employee_id', $employee->id)
            ->latest('created_at')
            ->get()
            ->groupBy(fn (EmployeeAddress $address): string => strtolower(trim((string) $address->type)) ?: EmployeeDataCompletionCalculator::DOMICILE_ADDRESS_TYPE)
            ->map(fn (Collection $typedAddresses): ?EmployeeAddress => $typedAddresses->first());
        $ktpAddress = $this->addressValues($addresses->get(EmployeeDataCompletionCalculator::KTP_ADDRESS_TYPE));
        $domicileAddress = $this->addressValues($addresses->get(EmployeeDataCompletionCalculator::DOMICILE_ADDRESS_TYPE));
        /** @var EmployeeBankAccount|null $bankAccount */
        $bankAccount = $employee->bankAccounts()->orderByDesc('is_primary')->orderByDesc('created_at')->first();
        $families = $employee->families()->orderBy('created_at')->get();

        $values['personal'] = [
            'name' => $profile?->name,
            'nickname' => $profile?->nickname,
            'place_of_birth' => $profile?->place_of_birth,
            'date_of_birth' => $this->date($profile?->date_of_birth),
            'gender' => $profile?->gender,
            'religion' => $profile?->religion,
            'marital_status' => EmployeeDataOptions::normalizeMaritalStatus($profile?->marital_status),
            'nationality' => $profile?->nationality,
            'height' => $profile?->height,
            'weight' => $this->decimal($profile?->weight),
            'blood_type' => $profile?->blood_type,
            'shirt_size' => $profile?->shirt_size,
            'pants_size' => $profile?->pants_size,
            'shoe_size' => $profile?->shoe_size,
            'medical_history' => $profile?->medical_history,
            'phone' => $employee->user?->phone,
            'personal_email' => $profile?->personal_email,
            'linkedin_url' => $profile?->linkedin_url,
            'portfolio_url' => $profile?->portfolio_url,
            'addresses' => ['ktp' => $ktpAddress, 'domicile' => $domicileAddress],
            'domicile_same_as_ktp' => filled($ktpAddress['address_line']) && $ktpAddress === $domicileAddress,
        ];

        $values['legal'] = [
            'nik' => $identity?->nik,
            'kk' => $identity?->kk,
            'mother_name' => $identity?->mother_name,
            'npwp' => $identity?->npwp,
            'bpjs_ketenagakerjaan' => $identity?->bpjs_ketenagakerjaan,
            'bpjs_kesehatan' => $identity?->bpjs_kesehatan,
            'bank' => [
                'bank_name' => $bankAccount?->bank_name,
                'account_number' => $bankAccount?->account_number,
                'account_holder_name' => $bankAccount?->account_holder_name,
            ],
            'licenses' => $employee->licenses()->orderBy('created_at')->get()
                ->map(fn (EmployeeLicense $license): array => [
                    'id' => (string) $license->id,
                    'license_type' => $license->license_type,
                    'license_number' => $license->license_number,
                    'expired_at' => $this->date($license->expired_at),
                ])->all(),
        ];

        $values['education'] = [
            'educations' => $employee->educations()->orderBy('sequence')->get()
                ->map(fn (EmployeeEducation $education): array => [
                    'id' => (string) $education->id,
                    'education_level_id' => $education->education_level_id,
                    'institution_name' => $education->institution_name,
                    'major' => $education->major,
                    'city' => $education->city,
                    'start_year' => $education->start_year,
                    'graduation_year' => $education->graduation_year,
                    'gpa' => $education->gpa,
                ])->all(),
            'certifications' => $employee->certifications()->orderBy('sequence')->get()
                ->map(fn (EmployeeCertification $certification): array => [
                    'id' => (string) $certification->id,
                    'certificate_name' => $certification->certificate_name,
                    'issuer' => $certification->issuer,
                    'effective_date' => $this->date($certification->effective_date),
                    'expired_at' => $this->date($certification->expired_at),
                    'city' => $certification->city,
                ])->all(),
            'work_experiences' => $employee->workExperiences()->orderBy('sequence')->get()
                ->map(fn (EmployeeWorkExperience $workExperience): array => [
                    'id' => (string) $workExperience->id,
                    'company_name' => $workExperience->company_name,
                    'last_position' => $workExperience->last_position,
                    'duration_text' => $workExperience->duration_text,
                    'resignation_reason' => $workExperience->resignation_reason,
                ])->all(),
            'references' => $employee->references()->orderBy('sequence')->get()
                ->map(fn (EmployeeReference $reference): array => [
                    'id' => (string) $reference->id,
                    'name' => $reference->name,
                    'position' => $reference->position,
                    'company_name' => $reference->company_name,
                    'phone_number' => $reference->phone_number,
                ])->all(),
            'languages' => implode(', ', (array) ($profile?->languages ?? [])),
            'hard_skills' => implode(', ', (array) ($profile?->hard_skills ?? [])),
            'soft_skills' => array_values((array) ($profile?->soft_skills ?? [])),
        ];

        $values['family'] = [
            'families' => $families->reject(fn (EmployeeFamily $family): bool => $family->is_emergency_contact)
                ->map(fn (EmployeeFamily $family): array => [
                    'id' => (string) $family->id,
                    'name' => $family->name,
                    'date_of_birth' => $this->date($family->date_of_birth),
                    'occupation' => $family->occupation,
                    'phone_number' => $family->phone_number,
                    'relationship' => $family->relationship,
                ])->values()->all(),
            'emergency_contacts' => $families->filter(fn (EmployeeFamily $family): bool => $family->is_emergency_contact)
                ->map(fn (EmployeeFamily $family): array => [
                    'id' => (string) $family->id,
                    'name' => $family->name,
                    'phone_number' => $family->phone_number,
                    'relationship' => $family->relationship,
                ])->values()->all(),
        ];

        $values['employment'] = $this->employmentValues($employee);

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    private function employmentValues(Employee $employee): array
    {
        /** @var EmployeeDeployment|null $deployment */
        $deployment = $employee->deployment;
        /** @var EmployeeCompensation|null $compensation */
        $compensation = $employee->compensation()->first();
        $employmentStatus = null;

        if ($deployment !== null) {
            $employmentStatus = $deployment->is_contract ? 'PKWT' : ($deployment->isOnProbation() ? 'Probation' : 'PKWTT');
        }

        return [
            'employment_status' => $employmentStatus,
            'join_date' => $this->date($deployment?->join_date),
            'end_date' => $this->date(match ($employmentStatus) {
                'PKWT' => $deployment?->contract_end_date,
                'Probation' => $deployment?->probation_end_date,
                default => null,
            }),
            'base_salary' => $this->amount($compensation?->base_salary),
            'allowance' => $this->amount($compensation?->allowance),
            'medical_checkups' => $employee->medicalCheckups()->orderByDesc('checkup_date')->get()
                ->map(fn (EmployeeMedicalCheckup $medicalCheckup): array => [
                    'id' => (string) $medicalCheckup->id,
                    'checkup_date' => $this->date($medicalCheckup->checkup_date),
                    'clinic_name' => $medicalCheckup->clinic_name,
                    'result_status' => $medicalCheckup->result_status,
                ])->all(),
            'assets' => $employee->assets()->orderBy('handover_date')->orderBy('name')->get()
                ->map(fn (CompanyAsset $companyAsset): array => [
                    'id' => (string) $companyAsset->id,
                    'name' => $companyAsset->name,
                    'specification' => $companyAsset->specification,
                    'serial_number' => $companyAsset->serial_number,
                    'handover_date' => $this->date($companyAsset->handover_date),
                ])->all(),
        ];
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function educationLevelOptions(): array
    {
        $levelRanks = array_flip(self::EDUCATION_LEVEL_ORDER);

        return EducationLevel::query()
            ->get(['id', 'name'])
            ->sortBy(fn (EducationLevel $educationLevel): string => str_pad((string) ($levelRanks[$educationLevel->name] ?? 99), 2, '0', STR_PAD_LEFT).$educationLevel->name)
            ->map(fn (EducationLevel $educationLevel): array => ['id' => (string) $educationLevel->id, 'name' => (string) $educationLevel->name])
            ->values()
            ->all();
    }

    /**
     * @return array<string, ?string>
     */
    private function addressValues(?EmployeeAddress $address): array
    {
        return collect(self::ADDRESS_COLUMNS)
            ->mapWithKeys(fn (string $column): array => [$column => $address?->getAttribute($column)])
            ->all();
    }

    private function date(mixed $date): ?string
    {
        return filled($date) ? Carbon::parse($date)->toDateString() : null;
    }

    /**
     * Drops trailing decimal zeros so "65.00" is shown as "65".
     */
    private function decimal(mixed $value): ?string
    {
        $text = trim((string) $value);

        if ($text === '' || ! str_contains($text, '.')) {
            return $text !== '' ? $text : null;
        }

        return rtrim(rtrim($text, '0'), '.');
    }

    private function amount(?string $amount): ?string
    {
        return is_numeric($amount) ? number_format((float) $amount, 0, ',', '.') : $amount;
    }
}
