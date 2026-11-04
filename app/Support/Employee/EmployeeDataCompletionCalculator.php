<?php

namespace App\Support\Employee;

use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeEducation;
use App\Models\EmployeeIdentity;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Measures how complete an employee's self-service data is (steps 1–4 of the "Data Karyawan" wizard).
 * Only required items are counted; optional data such as certifications or physical sizes is ignored.
 * Addresses saved before KTP/domicile types existed have no type; they hold the current residence, so they count as domicile.
 */
class EmployeeDataCompletionCalculator
{
    public const KTP_ADDRESS_TYPE = 'ktp';

    public const DOMICILE_ADDRESS_TYPE = 'domicile';

    /**
     * @return array{
     *     percent: int,
     *     completed_items: int,
     *     total_items: int,
     *     is_complete: bool,
     *     steps: list<array{
     *         key: string,
     *         title: string,
     *         completed_items: int,
     *         total_items: int,
     *         is_complete: bool,
     *         items: list<array{label: string, is_filled: bool}>,
     *         missing_items: list<string>
     *     }>
     * }
     */
    public function forEmployee(Employee $employee): array
    {
        $steps = collect($this->requiredItemsFor($employee))
            ->map(function (array $step, string $stepKey): array {
                $items = collect($step['items'])
                    ->map(fn (bool $isFilled, string $label): array => ['label' => $label, 'is_filled' => $isFilled])
                    ->values();
                $missingItems = $items
                    ->reject(fn (array $item): bool => $item['is_filled'])
                    ->pluck('label')
                    ->values()
                    ->all();

                return [
                    'key' => $stepKey,
                    'title' => $step['title'],
                    'completed_items' => $items->count() - count($missingItems),
                    'total_items' => $items->count(),
                    'is_complete' => $missingItems === [],
                    'items' => $items->all(),
                    'missing_items' => $missingItems,
                ];
            })
            ->values();

        $completedItems = (int) $steps->sum('completed_items');
        $totalItems = (int) $steps->sum('total_items');

        return [
            'percent' => $totalItems > 0 ? (int) floor(($completedItems / $totalItems) * 100) : 0,
            'completed_items' => $completedItems,
            'total_items' => $totalItems,
            'is_complete' => $totalItems > 0 && $completedItems === $totalItems,
            'steps' => $steps->all(),
        ];
    }

    /**
     * @return array<string, array{title: string, items: array<string, bool>}>
     */
    private function requiredItemsFor(Employee $employee): array
    {
        $employeeId = (string) $employee->id;
        $profile = EmployeeProfile::query()
            ->where('employee_id', $employeeId)
            ->first(['id', 'employee_id', 'name', 'place_of_birth', 'date_of_birth', 'gender', 'religion', 'marital_status']);
        $identity = EmployeeIdentity::query()
            ->where('employee_id', $employeeId)
            ->first(['id', 'employee_id', 'nik', 'kk', 'mother_name']);
        $phone = $employee->user_id !== null
            ? User::query()->whereKey($employee->user_id)->value('phone')
            : null;
        $addressTypes = EmployeeAddress::query()
            ->where('employee_id', $employeeId)
            ->whereNotNull('address_line')
            ->where('address_line', '<>', '')
            ->pluck('type')
            ->map(fn (mixed $type): string => strtolower(trim((string) $type)) ?: self::DOMICILE_ADDRESS_TYPE);

        return [
            'personal' => [
                'title' => 'Data Diri',
                'items' => [
                    'Nama Lengkap' => filled($profile?->name),
                    'Tempat Lahir' => filled($profile?->place_of_birth),
                    'Tanggal Lahir' => filled($profile?->date_of_birth),
                    'Jenis Kelamin' => filled($profile?->gender),
                    'Agama' => filled($profile?->religion),
                    'Status Pernikahan' => filled($profile?->marital_status),
                    'Nomor Handphone' => filled($phone),
                    'Alamat KTP' => $addressTypes->contains(self::KTP_ADDRESS_TYPE),
                    'Alamat Domisili' => $addressTypes->contains(self::DOMICILE_ADDRESS_TYPE),
                ],
            ],
            'identity' => [
                'title' => 'Identitas & Pajak',
                'items' => [
                    'NIK (No KTP)' => filled($identity?->nik),
                    'Nomor Kartu Keluarga' => filled($identity?->kk),
                    'Nama Ibu Kandung' => filled($identity?->mother_name),
                    'Rekening Bank' => DB::table('employee_bank_accounts')
                        ->where('employee_id', $employeeId)
                        ->whereNotNull('account_number')
                        ->where('account_number', '<>', '')
                        ->exists(),
                ],
            ],
            'education' => [
                'title' => 'Pendidikan',
                'items' => [
                    'Riwayat Pendidikan' => EmployeeEducation::query()->where('employee_id', $employeeId)->exists(),
                ],
            ],
            'family' => [
                'title' => 'Keluarga',
                'items' => [
                    'Kontak Darurat' => DB::table('employee_families')
                        ->where('employee_id', $employeeId)
                        ->where('is_emergency_contact', true)
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '<>', '')
                        ->exists(),
                ],
            ],
        ];
    }
}
