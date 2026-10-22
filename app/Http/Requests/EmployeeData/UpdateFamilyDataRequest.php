<?php

namespace App\Http\Requests\EmployeeData;

use App\Support\Employee\EmployeeDataOptions;
use Illuminate\Validation\Rule;

/**
 * Step 4 – Family & Emergency Contacts.
 */
class UpdateFamilyDataRequest extends EmployeeDataStepRequest
{
    /**
     * @return array<string, list<string>>
     */
    protected function repeatableRowFields(): array
    {
        return [
            'families' => ['name', 'date_of_birth', 'occupation', 'phone_number', 'relationship'],
            'emergency_contacts' => ['name', 'phone_number', 'relationship'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'families' => ['array'],
            'families.*.id' => ['nullable', 'string'],
            'families.*.name' => ['required', 'string', 'max:255'],
            'families.*.date_of_birth' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'families.*.occupation' => ['nullable', 'string', 'max:255'],
            'families.*.phone_number' => ['nullable', 'string', 'regex:'.self::PHONE_PATTERN],
            'families.*.relationship' => ['required', Rule::in(array_keys(EmployeeDataOptions::FAMILY_RELATIONSHIPS))],
            'emergency_contacts' => ['array'],
            'emergency_contacts.*.id' => ['nullable', 'string'],
            'emergency_contacts.*.name' => ['required', 'string', 'max:255'],
            'emergency_contacts.*.phone_number' => ['required', 'string', 'regex:'.self::PHONE_PATTERN],
            'emergency_contacts.*.relationship' => ['required', Rule::in(array_keys(EmployeeDataOptions::EMERGENCY_CONTACT_RELATIONSHIPS))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'families.*.name' => 'Nama Lengkap',
            'families.*.date_of_birth' => 'Tanggal Lahir',
            'families.*.occupation' => 'Pekerjaan',
            'families.*.phone_number' => 'No Handphone',
            'families.*.relationship' => 'Status',
            'emergency_contacts.*.name' => 'Nama Lengkap',
            'emergency_contacts.*.phone_number' => 'No Handphone',
            'emergency_contacts.*.relationship' => 'Hubungan',
        ];
    }
}
