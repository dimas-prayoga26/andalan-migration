<?php

namespace App\Http\Requests\EmployeeData;

use App\Support\Employee\EmployeeDataOptions;
use Illuminate\Validation\Rule;

/**
 * Step 1 – Personal & Physical Profile.
 */
class UpdatePersonalDataRequest extends EmployeeDataStepRequest
{
    public const ADDRESS_TYPES = ['ktp', 'domicile'];

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'domicile_same_as_ktp' => $this->boolean('domicile_same_as_ktp'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'gender' => ['nullable', Rule::in(array_keys(EmployeeDataOptions::GENDERS))],
            'religion' => ['nullable', Rule::in(array_keys(EmployeeDataOptions::RELIGIONS))],
            'marital_status' => ['nullable', Rule::in(array_keys(EmployeeDataOptions::MARITAL_STATUSES))],
            'nationality' => ['nullable', Rule::in(array_keys(EmployeeDataOptions::NATIONALITIES))],
            'height' => ['nullable', 'integer', 'between:50,250'],
            'weight' => ['nullable', 'numeric', 'between:20,300'],
            'blood_type' => ['nullable', Rule::in(array_keys(EmployeeDataOptions::BLOOD_TYPES))],
            'shirt_size' => ['nullable', 'string', 'max:20'],
            'pants_size' => ['nullable', 'string', 'max:20'],
            'shoe_size' => ['nullable', 'string', 'max:20'],
            'medical_history' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'regex:'.self::PHONE_PATTERN],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'addresses' => ['nullable', 'array:'.implode(',', self::ADDRESS_TYPES)],
            'addresses.*' => ['array'],
            'addresses.*.province' => ['nullable', 'string', 'max:255'],
            'addresses.*.regency' => ['nullable', 'string', 'max:255'],
            'addresses.*.subdistrict' => ['nullable', 'string', 'max:255'],
            'addresses.*.village' => ['nullable', 'string', 'max:255'],
            'addresses.*.address_line' => ['nullable', 'string', 'max:1000'],
            'domicile_same_as_ktp' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'nickname' => 'Nama Panggilan',
            'place_of_birth' => 'Tempat Lahir',
            'date_of_birth' => 'Tanggal Lahir',
            'gender' => 'Jenis Kelamin',
            'religion' => 'Agama',
            'marital_status' => 'Status Pernikahan',
            'nationality' => 'Kewarganegaraan',
            'height' => 'Tinggi Badan',
            'weight' => 'Berat Badan',
            'blood_type' => 'Golongan Darah',
            'shirt_size' => 'Ukuran Kemeja',
            'pants_size' => 'Ukuran Celana',
            'shoe_size' => 'Ukuran Sepatu',
            'medical_history' => 'Riwayat Penyakit Khusus/Alergi',
            'phone' => 'Nomor Handphone',
            'personal_email' => 'Email Pribadi',
            'linkedin_url' => 'LinkedIn Profile',
            'portfolio_url' => 'Website / Portfolio',
            'addresses.*.province' => 'Provinsi',
            'addresses.*.regency' => 'Kabupaten/Kota',
            'addresses.*.subdistrict' => 'Kecamatan',
            'addresses.*.village' => 'Kelurahan / Desa',
            'addresses.*.address_line' => 'Nama Jalan, No Rumah dan RT/RW',
        ];
    }
}
