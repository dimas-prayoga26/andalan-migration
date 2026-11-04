<?php

namespace App\Services\Employee;

use App\Http\Requests\EmployeeData\EmployeeDataStepRequest;
use App\Http\Requests\EmployeeData\UpdateEducationDataRequest;
use App\Http\Requests\EmployeeData\UpdateEmploymentDataRequest;
use App\Http\Requests\EmployeeData\UpdateFamilyDataRequest;
use App\Http\Requests\EmployeeData\UpdateLegalDataRequest;
use App\Http\Requests\EmployeeData\UpdatePersonalDataRequest;
use App\Models\CompanyAsset;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeIdentity;
use App\Models\EmployeeProfile;
use App\Support\Employee\EmployeeDataCompletionCalculator;
use App\Support\Employee\EmployeeDataOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Saves one step of the "Data Karyawan" wizard. Steps 1–4 are filled by the employee or HR, step 5 by HR only.
 * Each step form is always submitted whole, so a repeatable row missing from the submission was removed by the user.
 * Rows are matched by id, which keeps columns the wizard does not show (e.g. uploaded certificate files).
 */
class EmployeeDataWizardService
{
    public const STEP_PERSONAL = 'personal';

    public const STEP_LEGAL = 'legal';

    public const STEP_EDUCATION = 'education';

    public const STEP_FAMILY = 'family';

    public const STEP_EMPLOYMENT = 'employment';

    public const SELF_SERVICE_STEPS = [self::STEP_PERSONAL, self::STEP_LEGAL, self::STEP_EDUCATION, self::STEP_FAMILY];

    public const HR_STEPS = [...self::SELF_SERVICE_STEPS, self::STEP_EMPLOYMENT];

    private const PROFILE_COLUMNS = [
        'name', 'nickname', 'place_of_birth', 'date_of_birth', 'gender', 'religion', 'marital_status', 'nationality',
        'height', 'weight', 'blood_type', 'shirt_size', 'pants_size', 'shoe_size', 'medical_history',
        'personal_email', 'linkedin_url', 'portfolio_url',
    ];

    private const IDENTITY_COLUMNS = ['nik', 'kk', 'mother_name', 'npwp', 'bpjs_ketenagakerjaan', 'bpjs_kesehatan'];

    private const ADDRESS_COLUMNS = ['province', 'regency', 'subdistrict', 'village', 'address_line'];

    /**
     * @return class-string<EmployeeDataStepRequest>
     */
    public static function requestClassFor(string $step): string
    {
        return match ($step) {
            self::STEP_PERSONAL => UpdatePersonalDataRequest::class,
            self::STEP_LEGAL => UpdateLegalDataRequest::class,
            self::STEP_EDUCATION => UpdateEducationDataRequest::class,
            self::STEP_FAMILY => UpdateFamilyDataRequest::class,
            self::STEP_EMPLOYMENT => UpdateEmploymentDataRequest::class,
        };
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, list<string>> saved row ids per repeatable group, in submitted order
     */
    public function save(Employee $employee, string $step, array $validated): array
    {
        return DB::transaction(fn (): array => match ($step) {
            self::STEP_PERSONAL => $this->savePersonal($employee, $validated),
            self::STEP_LEGAL => $this->saveLegal($employee, $validated),
            self::STEP_EDUCATION => $this->saveEducation($employee, $validated),
            self::STEP_FAMILY => $this->saveFamily($employee, $validated),
            self::STEP_EMPLOYMENT => $this->saveEmployment($employee, $validated),
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, list<string>>
     */
    private function savePersonal(Employee $employee, array $validated): array
    {
        EmployeeProfile::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            Arr::only($validated, self::PROFILE_COLUMNS),
        );

        if (array_key_exists('phone', $validated) && $employee->user !== null) {
            $employee->user->forceFill(['phone' => $validated['phone']])->save();
        }

        $ktpAddress = $this->addressAttributes($validated['addresses']['ktp'] ?? []);
        $domicileAddress = $validated['domicile_same_as_ktp']
            ? $ktpAddress
            : $this->addressAttributes($validated['addresses']['domicile'] ?? []);

        $this->saveAddress($employee, EmployeeDataCompletionCalculator::KTP_ADDRESS_TYPE, $ktpAddress);
        $this->saveAddress($employee, EmployeeDataCompletionCalculator::DOMICILE_ADDRESS_TYPE, $domicileAddress);

        return [];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, list<string>>
     */
    private function saveLegal(Employee $employee, array $validated): array
    {
        EmployeeIdentity::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            Arr::only($validated, self::IDENTITY_COLUMNS),
        );

        $bankAttributes = Arr::only($validated['bank'] ?? [], ['bank_name', 'account_number', 'account_holder_name']);
        /** @var EmployeeBankAccount|null $bankAccount */
        $bankAccount = $employee->bankAccounts()->orderByDesc('is_primary')->orderByDesc('created_at')->first();

        if ($bankAccount !== null) {
            $bankAccount->update($bankAttributes + ['is_primary' => true]);
        } elseif (collect($bankAttributes)->filter(fn (mixed $value): bool => filled($value))->isNotEmpty()) {
            $employee->bankAccounts()->create($bankAttributes + ['is_primary' => true]);
        }

        return [
            'licenses' => $this->syncRows($employee->licenses(), $validated['licenses'] ?? [], fn (array $license): array => [
                'license_type' => $license['license_type'],
                'license_number' => $license['license_number'] ?? null,
                'expired_at' => $license['expired_at'] ?? null,
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, list<string>>
     */
    private function saveEducation(Employee $employee, array $validated): array
    {
        EmployeeProfile::query()->updateOrCreate(['employee_id' => $employee->id], [
            'languages' => $this->listFromText($validated['languages'] ?? null),
            'hard_skills' => $this->listFromText($validated['hard_skills'] ?? null),
            'soft_skills' => $this->uniqueList($validated['soft_skills'] ?? []),
        ]);

        return [
            'educations' => $this->syncRows($employee->educations(), $validated['educations'] ?? [], fn (array $education, int $index): array => [
                'education_level_id' => $education['education_level_id'] ?? null,
                'institution_name' => $education['institution_name'],
                'major' => $education['major'] ?? null,
                'city' => $education['city'] ?? null,
                'start_year' => $education['start_year'] ?? null,
                'graduation_year' => $education['graduation_year'] ?? null,
                'gpa' => $education['gpa'] ?? null,
                'sequence' => $index + 1,
            ]),
            'certifications' => $this->syncRows($employee->certifications(), $validated['certifications'] ?? [], fn (array $certification, int $index): array => [
                'certificate_name' => $certification['certificate_name'],
                'issuer' => $certification['issuer'] ?? null,
                'effective_date' => $certification['effective_date'] ?? null,
                'expired_at' => $certification['expired_at'] ?? null,
                'city' => $certification['city'] ?? null,
                'sequence' => $index + 1,
            ]),
            'work_experiences' => $this->syncRows($employee->workExperiences(), $validated['work_experiences'] ?? [], fn (array $workExperience, int $index): array => [
                'company_name' => $workExperience['company_name'],
                'last_position' => $workExperience['last_position'] ?? null,
                'duration_text' => $workExperience['duration_text'] ?? null,
                'resignation_reason' => $workExperience['resignation_reason'] ?? null,
                'sequence' => $index + 1,
            ]),
            'references' => $this->syncRows($employee->references(), $validated['references'] ?? [], fn (array $reference, int $index): array => [
                'name' => $reference['name'],
                'position' => $reference['position'] ?? null,
                'company_name' => $reference['company_name'] ?? null,
                'phone_number' => $reference['phone_number'] ?? null,
                'sequence' => $index + 1,
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, list<string>>
     */
    private function saveFamily(Employee $employee, array $validated): array
    {
        return [
            'families' => $this->syncRows(
                $employee->families()->where('is_emergency_contact', false),
                $validated['families'] ?? [],
                fn (array $family): array => [
                    'name' => $family['name'],
                    'date_of_birth' => $family['date_of_birth'] ?? null,
                    'occupation' => $family['occupation'] ?? null,
                    'phone_number' => $family['phone_number'] ?? null,
                    'relationship' => $family['relationship'],
                    'is_dependents' => in_array($family['relationship'], EmployeeDataOptions::DEPENDENT_RELATIONSHIPS, true),
                    'is_emergency_contact' => false,
                ] + array_filter(['gender' => EmployeeDataOptions::RELATIONSHIP_GENDERS[$family['relationship']] ?? null]),
            ),
            'emergency_contacts' => $this->syncRows(
                $employee->families()->where('is_emergency_contact', true),
                $validated['emergency_contacts'] ?? [],
                fn (array $emergencyContact): array => [
                    'name' => $emergencyContact['name'],
                    'phone_number' => $emergencyContact['phone_number'],
                    'relationship' => $emergencyContact['relationship'],
                    'is_dependents' => false,
                    'is_emergency_contact' => true,
                ],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, list<string>>
     */
    private function saveEmployment(Employee $employee, array $validated): array
    {
        /** @var EmployeeDeployment $deployment */
        $deployment = EmployeeDeployment::query()->firstOrNew(['employee_id' => $employee->id]);

        if (array_key_exists('join_date', $validated)) {
            $deployment->join_date = $validated['join_date'];
        }

        $this->applyEmploymentStatus($deployment, $validated['employment_status'] ?? null, $validated['end_date'] ?? null);
        $deployment->save();

        $this->saveCompensation($employee, $validated);

        $companyId = $deployment->current_company_id;

        return [
            'medical_checkups' => $this->syncRows($employee->medicalCheckups(), $validated['medical_checkups'] ?? [], fn (array $medicalCheckup): array => [
                'checkup_date' => $medicalCheckup['checkup_date'],
                'clinic_name' => $medicalCheckup['clinic_name'] ?? null,
                'result_status' => $medicalCheckup['result_status'],
            ]),
            'assets' => $this->syncRows(
                $employee->assets(),
                $validated['assets'] ?? [],
                function (array $asset, int $index, ?Model $existingAsset) use ($companyId): array {
                    if ($existingAsset === null && blank($companyId)) {
                        throw ValidationException::withMessages([
                            "assets.{$index}.name" => 'Atur Company karyawan pada tab 1 sebelum menambahkan aset.',
                        ]);
                    }

                    return [
                        'name' => $asset['name'],
                        'specification' => $asset['specification'] ?? null,
                        'serial_number' => $asset['serial_number'] ?? null,
                        'handover_date' => $asset['handover_date'] ?? null,
                        'status' => CompanyAsset::STATUS_ASSIGNED,
                    ] + ($existingAsset === null ? ['company_id' => $companyId] : []);
                },
                fn (CompanyAsset $returnedAsset): bool => $returnedAsset->forceFill([
                    'employee_id' => null,
                    'handover_date' => null,
                    'status' => CompanyAsset::STATUS_AVAILABLE,
                ])->save(),
            ),
        ];
    }

    /**
     * PKWT keeps a contract end date, Probation a probation end date, PKWTT neither (a past probation end stays as history).
     */
    private function applyEmploymentStatus(EmployeeDeployment $deployment, ?string $employmentStatus, ?string $endDate): void
    {
        if ($employmentStatus === null) {
            return;
        }

        $deployment->is_contract = $employmentStatus === 'PKWT';
        $deployment->contract_end_date = $employmentStatus === 'PKWT' ? $endDate : null;
        $deployment->probation_end_date = match ($employmentStatus) {
            'Probation' => $endDate,
            'PKWTT' => $deployment->probation_end_date?->isPast() ? $deployment->probation_end_date : null,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function saveCompensation(Employee $employee, array $validated): void
    {
        $compensationAttributes = Arr::only($validated, ['base_salary', 'allowance']);
        /** @var EmployeeCompensation|null $compensation */
        $compensation = $employee->compensation()->first();

        if ($compensation !== null) {
            $compensation->update($compensationAttributes);

            return;
        }

        if (collect($compensationAttributes)->filter(fn (mixed $value): bool => filled($value))->isNotEmpty()) {
            $employee->compensation()->create($compensationAttributes + ['effective_date' => now('Asia/Jakarta')->toDateString()]);
        }
    }

    /**
     * Updates or creates the address of the given type. A legacy address without a type is the domicile address.
     *
     * @param  array<string, ?string>  $attributes
     */
    private function saveAddress(Employee $employee, string $type, array $attributes): void
    {
        $address = EmployeeAddress::query()
            ->where('employee_id', $employee->id)
            ->where('type', $type)
            ->latest('created_at')
            ->first();

        if ($address === null && $type === EmployeeDataCompletionCalculator::DOMICILE_ADDRESS_TYPE) {
            $address = EmployeeAddress::query()
                ->where('employee_id', $employee->id)
                ->where(fn ($query) => $query->whereNull('type')->orWhere('type', ''))
                ->latest('created_at')
                ->first();
        }

        if ($address !== null) {
            $address->forceFill(['type' => $type] + $attributes)->save();

            return;
        }

        if (collect($attributes)->filter(fn (?string $value): bool => filled($value))->isNotEmpty()) {
            EmployeeAddress::query()->create(['employee_id' => $employee->id, 'type' => $type] + $attributes);
        }
    }

    /**
     * @param  array<string, mixed>  $address
     * @return array<string, ?string>
     */
    private function addressAttributes(array $address): array
    {
        return collect(self::ADDRESS_COLUMNS)
            ->mapWithKeys(fn (string $column): array => [$column => $address[$column] ?? null])
            ->all();
    }

    /**
     * Updates submitted rows that already exist, creates the new ones and removes the rest.
     *
     * @param  list<array<string, mixed>>  $rows
     * @param  callable(array<string, mixed>, int, ?Model): array<string, mixed>  $attributesFor
     * @param  (callable(Model): mixed)|null  $removeRow
     * @return list<string>
     */
    private function syncRows(HasMany $relation, array $rows, callable $attributesFor, ?callable $removeRow = null): array
    {
        $existingRows = $relation->get()->keyBy(fn (Model $row): string => (string) $row->getKey());
        $savedRowIds = [];

        foreach (array_values($rows) as $index => $row) {
            $existingRow = $existingRows->get((string) ($row['id'] ?? ''));
            $attributes = $attributesFor($row, $index, $existingRow);

            if ($existingRow !== null) {
                $existingRow->fill($attributes)->save();
                $savedRowIds[] = (string) $existingRow->getKey();

                continue;
            }

            $savedRowIds[] = (string) $relation->create($attributes)->getKey();
        }

        $existingRows
            ->except($savedRowIds)
            ->each(fn (Model $removedRow): mixed => $removeRow !== null ? $removeRow($removedRow) : $removedRow->delete());

        return $savedRowIds;
    }

    /**
     * @return list<string>|null
     */
    private function listFromText(?string $text): ?array
    {
        return $this->uniqueList(explode(',', (string) $text));
    }

    /**
     * @param  array<int, mixed>  $values
     * @return list<string>|null
     */
    private function uniqueList(array $values): ?array
    {
        $list = collect($values)
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $list !== [] ? $list : null;
    }
}
