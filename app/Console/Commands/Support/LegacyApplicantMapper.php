<?php

namespace App\Console\Commands\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

/**
 * Maps a row from the legacy careers database's flat `applicants` table
 * (single-value education/work-experience columns, "||" separated for
 * applicants with multiple entries) into this app's normalized schema.
 */
class LegacyApplicantMapper
{
    /**
     * @var array<int, string>
     */
    private const GENDER_NAMES_BY_LEGACY_VALUE = [
        1 => 'Male',
        2 => 'Female',
    ];

    /**
     * @var array<int, string>
     */
    private const MARITAL_STATUS_NAMES_BY_LEGACY_VALUE = [
        1 => 'Single',
        2 => 'Married',
        3 => 'Divorced',
        4 => 'Widowed',
    ];

    /**
     * @var array<int, string>
     */
    private const EDUCATION_LEVEL_NAMES_BY_LEGACY_VALUE = [
        1 => 'SMA',
        2 => 'Diploma',
        3 => 'Strata 1',
        4 => 'Strata 2',
        5 => 'Strata 3',
    ];

    /**
     * @param  Collection<string, string>  $genderIdsByName  lowercased gender name => meta_data_gender.id
     * @param  Collection<string, string>  $maritalStatusIdsByName  lowercased name => meta_data_marital_statuses.id
     * @param  Collection<string, string>  $educationLevelIdsByName  lowercased name => education_levels.id
     * @param  Collection<int, string>  $applicantStatusIdsByValue  applicant_statuses.value => applicant_statuses.id
     * @param  Collection<string, string>  $jobVacancyIdsByName  lowercased trimmed name => job_vacancies.id
     * @param  Collection<int, string>  $legacyVacancyNamesByValue  legacy opt_applicants_vacancies.value => name
     */
    public function __construct(
        private readonly Collection $genderIdsByName,
        private readonly Collection $maritalStatusIdsByName,
        private readonly Collection $educationLevelIdsByName,
        private readonly Collection $applicantStatusIdsByValue,
        private readonly Collection $jobVacancyIdsByName,
        private readonly Collection $legacyVacancyNamesByValue,
    ) {}

    /**
     * @param  array<string, mixed>  $legacyRow
     * @return array<string, mixed>
     */
    public function mapApplicantAttributes(array $legacyRow, ?string $brandKey): array
    {
        $createdAt = $this->parseLegacyTimestamp(self::firstValue($legacyRow, ['created_at'])) ?? Carbon::now();

        return [
            'job_vacancy_id' => $this->jobVacancyIdFor(self::firstValue($legacyRow, ['job_applied', 'job_vacancy_id'])),
            'brand_key' => $brandKey,
            'slug' => self::normalizeText(self::firstValue($legacyRow, ['slug'])),
            'applicant_status_id' => $this->applicantStatusIdFor(self::firstValue($legacyRow, ['nb', 'applicant_status', 'status'])),
            'full_name' => self::normalizeText(self::firstValue($legacyRow, ['full_name'])) ?? '-',
            'nickname' => self::normalizeText(self::firstValue($legacyRow, ['nickname'])),
            'place_of_birth' => self::normalizeText(self::firstValue($legacyRow, ['pob', 'place_of_birth'])),
            'date_of_birth' => self::normalizeText(self::firstValue($legacyRow, ['dob', 'date_of_birth'])),
            'email' => self::normalizeText(self::firstValue($legacyRow, ['email'])),
            'phone' => self::normalizeText(self::firstValue($legacyRow, ['phone'])),
            'gender_id' => $this->genderIdFor(self::firstValue($legacyRow, ['gender', 'gender_id'])),
            'marital_status_id' => $this->maritalStatusIdFor(self::firstValue($legacyRow, ['marital_status', 'marital_status_id'])),
            'address' => self::normalizeText(self::firstValue($legacyRow, ['address'])),
            'expected_salary' => self::normalizeText(self::firstValue($legacyRow, ['expected_salary'])),
            'self_resume' => self::normalizeText(self::firstValue($legacyRow, ['self_resume'])),
            'portfolio_web_address' => self::normalizeText(self::firstValue($legacyRow, ['portfolio_web_address'])),
            'cv' => self::normalizeText(self::firstValue($legacyRow, ['cv'])),
            'photo' => self::normalizeText(self::firstValue($legacyRow, ['photo'])),
            'agreement' => self::normalizeText(self::firstValue($legacyRow, ['agreement'])),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $legacyRow
     * @return list<array<string, mixed>>
     */
    public function mapEducationRows(array $legacyRow): array
    {
        $rows = self::combineRows([
            'educational_level' => self::firstValue($legacyRow, ['educational_level']),
            'institution' => self::firstValue($legacyRow, ['educational_institution']),
            'gpa' => self::firstValue($legacyRow, ['gpa']),
            'department' => self::firstValue($legacyRow, ['department']),
            'start_period' => self::firstValue($legacyRow, ['start_education']),
            'graduate_period' => self::firstValue($legacyRow, ['graduate_education']),
        ]);

        return array_map(function (array $row): array {
            $row['education_level_id'] = $this->educationLevelIdFor($row['educational_level'] ?? null);
            unset($row['educational_level']);

            return $row;
        }, $rows);
    }

    /**
     * @param  array<string, mixed>  $legacyRow
     * @return list<array<string, ?string>>
     */
    public function mapWorkExperienceRows(array $legacyRow): array
    {
        return self::combineRows([
            'company_name' => self::firstValue($legacyRow, ['company_name']),
            'role' => self::firstValue($legacyRow, ['role']),
            'company_location' => self::firstValue($legacyRow, ['company_location']),
            'start_period' => self::firstValue($legacyRow, ['start_date']),
            'end_period' => self::firstValue($legacyRow, ['end_date']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $legacyColumns
     * @return list<array<string, ?string>>
     */
    public static function combineRows(array $legacyColumns): array
    {
        $splitColumns = collect($legacyColumns)
            ->map(fn (mixed $value): array => self::splitPipedValues($value === null ? null : (string) $value));

        $maxRows = $splitColumns->map(fn (array $values): int => count($values))->max() ?? 0;

        $rows = [];

        for ($index = 0; $index < $maxRows; $index++) {
            $row = $splitColumns->map(fn (array $values): ?string => $values[$index] ?? null)->all();

            if (self::rowHasMeaningfulValue($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return list<string|null>
     */
    public static function splitPipedValues(?string $value): array
    {
        $text = self::normalizeText($value);

        if ($text === null) {
            return [];
        }

        return collect(preg_split('/\|\|/', $text) ?: [])
            ->map(fn (string $item): ?string => self::normalizeText($item))
            ->values()
            ->all();
    }

    private static function firstValue(array $row, array $candidateKeys): mixed
    {
        foreach ($candidateKeys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }

    /**
     * @param  array<string, ?string>  $row
     */
    private static function rowHasMeaningfulValue(array $row): bool
    {
        foreach ($row as $value) {
            if (self::hasMeaningfulValue($value)) {
                return true;
            }
        }

        return false;
    }

    private static function hasMeaningfulValue(?string $value): bool
    {
        return $value !== null && ! in_array(Str::lower(trim($value)), ['', '-'], true);
    }

    private static function normalizeText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $text === '' ? null : $text;
    }

    private static function integerOrNull(mixed $value): ?int
    {
        $text = self::normalizeText($value);

        return ($text !== null && ctype_digit($text)) ? (int) $text : null;
    }

    private function genderIdFor(mixed $legacyGender): ?string
    {
        $name = self::GENDER_NAMES_BY_LEGACY_VALUE[self::integerOrNull($legacyGender)] ?? null;

        return $name === null ? null : $this->genderIdsByName->get(Str::lower($name));
    }

    private function maritalStatusIdFor(mixed $legacyMaritalStatus): ?string
    {
        $name = self::MARITAL_STATUS_NAMES_BY_LEGACY_VALUE[self::integerOrNull($legacyMaritalStatus)] ?? null;

        return $name === null ? null : $this->maritalStatusIdsByName->get(Str::lower($name));
    }

    private function educationLevelIdFor(mixed $legacyEducationLevel): ?string
    {
        $name = self::EDUCATION_LEVEL_NAMES_BY_LEGACY_VALUE[self::integerOrNull($legacyEducationLevel)] ?? null;

        return $name === null ? null : $this->educationLevelIdsByName->get(Str::lower($name));
    }

    private function applicantStatusIdFor(mixed $legacyStatus): ?string
    {
        $value = self::integerOrNull($legacyStatus);
        $value = ($value !== null && $this->applicantStatusIdsByValue->has($value)) ? $value : 0;

        return $this->applicantStatusIdsByValue->get($value);
    }

    private function jobVacancyIdFor(mixed $legacyJobApplied): ?string
    {
        $value = self::integerOrNull($legacyJobApplied);

        if ($value === null) {
            return null;
        }

        $vacancyName = $this->legacyVacancyNamesByValue->get($value);

        return $vacancyName === null ? null : $this->jobVacancyIdsByName->get(Str::lower(trim((string) $vacancyName)));
    }

    private function parseLegacyTimestamp(mixed $value): ?Carbon
    {
        $text = self::normalizeText($value);

        if ($text === null) {
            return null;
        }

        try {
            return Carbon::parse($text);
        } catch (Throwable) {
            return null;
        }
    }
}
