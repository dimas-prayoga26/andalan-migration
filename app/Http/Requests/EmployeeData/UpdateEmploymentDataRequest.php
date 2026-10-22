<?php

namespace App\Http\Requests\EmployeeData;

use App\Models\EmployeeMedicalCheckup;
use App\Support\Employee\EmployeeEmploymentDetailResolver;
use Illuminate\Validation\Rule;

/**
 * Step 5 – Employment & Contract Details, filled by HR only.
 * Contract end date applies to PKWT, probation end date to Probation; PKWTT has neither.
 */
class UpdateEmploymentDataRequest extends EmployeeDataStepRequest
{
    /**
     * @return array<string, list<string>>
     */
    protected function repeatableRowFields(): array
    {
        return [
            'medical_checkups' => ['checkup_date', 'clinic_name', 'result_status'],
            'assets' => ['name', 'specification', 'serial_number', 'handover_date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'base_salary' => $this->digitsOnly($this->input('base_salary')),
            'allowance' => $this->digitsOnly($this->input('allowance')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $endDateRules = [
            'nullable',
            'date_format:Y-m-d',
            Rule::requiredIf(fn (): bool => in_array($this->input('employment_status'), ['PKWT', 'Probation'], true)),
        ];

        if (filled($this->input('join_date'))) {
            $endDateRules[] = 'after_or_equal:join_date';
        }

        return [
            'employment_status' => ['nullable', Rule::in(EmployeeEmploymentDetailResolver::EMPLOYMENT_STATUS_OPTIONS)],
            'join_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => $endDateRules,
            'base_salary' => ['nullable', 'digits_between:1,15'],
            'allowance' => ['nullable', 'digits_between:1,15'],
            'medical_checkups' => ['array'],
            'medical_checkups.*.id' => ['nullable', 'string'],
            'medical_checkups.*.checkup_date' => ['required', 'date_format:Y-m-d'],
            'medical_checkups.*.clinic_name' => ['nullable', 'string', 'max:255'],
            'medical_checkups.*.result_status' => ['required', Rule::in(array_keys(EmployeeMedicalCheckup::resultOptions()))],
            'assets' => ['array'],
            'assets.*.id' => ['nullable', 'string'],
            'assets.*.name' => ['required', 'string', 'max:255'],
            'assets.*.specification' => ['nullable', 'string', 'max:255'],
            'assets.*.serial_number' => ['nullable', 'string', 'max:100'],
            'assets.*.handover_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'employment_status' => 'Status Kepegawaian',
            'join_date' => 'Tanggal Bergabung',
            'end_date' => 'Tanggal Berakhir Kontrak / Probation',
            'base_salary' => 'Gaji Pokok',
            'allowance' => 'Tunjangan',
            'medical_checkups.*.checkup_date' => 'Tanggal MCU',
            'medical_checkups.*.clinic_name' => 'Rumah Sakit / Klinik',
            'medical_checkups.*.result_status' => 'Status MCU',
            'assets.*.name' => 'Nama Aset',
            'assets.*.specification' => 'Merk / Spesifikasi',
            'assets.*.serial_number' => 'Nomor Seri',
            'assets.*.handover_date' => 'Tanggal Serah Terima',
        ];
    }

    private function digitsOnly(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }
}
