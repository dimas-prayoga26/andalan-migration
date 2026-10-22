<?php

namespace App\Http\Requests\EmployeeData;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base request of one "Data Karyawan" wizard step. Access is checked by the controller (own profile or HR).
 * Repeatable rows the user left completely empty are dropped before validation, so a blank template row never fails.
 */
abstract class EmployeeDataStepRequest extends FormRequest
{
    public const PHONE_PATTERN = '/^[0-9+\-\s()]{6,20}$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Repeatable row groups of the step and the fields of one row (the row "id" is not a field).
     *
     * @return array<string, list<string>>
     */
    protected function repeatableRowFields(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        $rows = [];

        foreach ($this->repeatableRowFields() as $rowGroup => $rowFields) {
            $rows[$rowGroup] = collect((array) $this->input($rowGroup, []))
                ->filter(fn (mixed $row): bool => is_array($row) && collect($rowFields)->contains(
                    fn (string $rowField): bool => filled(is_array($row[$rowField] ?? null) ? implode('', $row[$rowField]) : ($row[$rowField] ?? null))
                ))
                ->values()
                ->all();
        }

        $this->merge($rows);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'integer' => ':attribute harus berupa angka bulat.',
            'numeric' => ':attribute harus berupa angka.',
            'between' => ':attribute harus di antara :min dan :max.',
            'digits_between' => ':attribute harus berupa angka (maksimal :max digit).',
            'date_format' => ':attribute bukan tanggal yang valid.',
            'before' => ':attribute harus sebelum hari ini.',
            'after_or_equal' => ':attribute tidak boleh sebelum :date.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'url' => ':attribute harus berupa URL yang valid (diawali http:// atau https://).',
            'regex' => 'Format :attribute tidak valid.',
            'in' => ':attribute tidak valid.',
            'exists' => ':attribute tidak valid.',
            'array' => ':attribute tidak valid.',
            'boolean' => ':attribute tidak valid.',
        ];
    }
}
