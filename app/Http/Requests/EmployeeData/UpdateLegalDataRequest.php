<?php

namespace App\Http\Requests\EmployeeData;

/**
 * Step 2 – Legal, Tax & Compliance.
 */
class UpdateLegalDataRequest extends EmployeeDataStepRequest
{
    /**
     * @return array<string, list<string>>
     */
    protected function repeatableRowFields(): array
    {
        return [
            'licenses' => ['license_type', 'license_number', 'expired_at'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nik' => ['nullable', 'regex:/^\d{16}$/'],
            'kk' => ['nullable', 'regex:/^\d{16}$/'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9.\-\s]{15,25}$/'],
            'bpjs_ketenagakerjaan' => ['nullable', 'regex:/^\d{8,20}$/'],
            'bpjs_kesehatan' => ['nullable', 'regex:/^\d{8,20}$/'],
            'bank' => ['nullable', 'array'],
            'bank.bank_name' => ['nullable', 'string', 'max:100'],
            'bank.account_number' => ['nullable', 'regex:/^\d{5,30}$/'],
            'bank.account_holder_name' => ['nullable', 'string', 'max:255'],
            'licenses' => ['array'],
            'licenses.*.id' => ['nullable', 'string'],
            'licenses.*.license_type' => ['required', 'string', 'max:50'],
            'licenses.*.license_number' => ['nullable', 'string', 'max:100'],
            'licenses.*.expired_at' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nik.regex' => 'NIK harus 16 digit angka.',
            'kk.regex' => 'Nomor Kartu Keluarga harus 16 digit angka.',
            'bpjs_ketenagakerjaan.regex' => 'Nomor BPJS Ketenagakerjaan hanya boleh berisi angka.',
            'bpjs_kesehatan.regex' => 'Nomor BPJS Kesehatan hanya boleh berisi angka.',
            'bank.account_number.regex' => 'Nomor Rekening hanya boleh berisi angka.',
        ] + parent::messages();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nik' => 'NIK',
            'kk' => 'Nomor Kartu Keluarga',
            'mother_name' => 'Nama Ibu Kandung',
            'npwp' => 'Nomor NPWP',
            'bpjs_ketenagakerjaan' => 'Nomor BPJS Ketenagakerjaan',
            'bpjs_kesehatan' => 'Nomor BPJS Kesehatan',
            'bank.bank_name' => 'Nama Bank',
            'bank.account_number' => 'Nomor Rekening',
            'bank.account_holder_name' => 'Nama Pemilik Rekening',
            'licenses.*.license_type' => 'Jenis Lisensi',
            'licenses.*.license_number' => 'Nomor Lisensi',
            'licenses.*.expired_at' => 'Masa Berlaku',
        ];
    }
}
