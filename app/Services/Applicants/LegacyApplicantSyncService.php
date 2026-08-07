<?php

namespace App\Services\Applicants;

use App\Models\Applicant;
use App\Models\ApplicantEducation;
use App\Models\ApplicantStatus;
use App\Models\ApplicantWorkExperience;
use App\Models\EducationLevel;
use App\Models\JobVacancy;
use App\Models\Legacy\LegacyApplicant;
use App\Models\Legacy\LegacyApplicantVacancy;
use App\Models\Legacy\LegacyEducationLevel;
use App\Models\MetaDataGender;
use App\Models\MetaDataMaritalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class LegacyApplicantSyncService
{
    private const LEGACY_ACTIVE_STATUS = 1;

    private const LEGACY_APPLICANT_INTERVIEW_STATUS = 1;

    private const LEGACY_APPLICANT_ACCEPTED_STATUS = 2;

    /**
     * @var Collection<int, int>|null
     */
    private ?Collection $genderIdsByLegacyValue = null;

    /**
     * @var Collection<int, int>|null
     */
    private ?Collection $maritalStatusIdsByLegacyValue = null;

    /**
     * @var Collection<int, string>|null
     */
    private ?Collection $educationLevelIdsByLegacyValue = null;

    /**
     * @return array{available: bool, applicants: int, vacancies: int, message: string|null}
     */
    public function sync(): array
    {
        try {
            return DB::transaction(function (): array {
                $this->syncApplicantStatuses();
                $this->syncEducationLevels();
                $vacancies = $this->syncVacancies();
                $applicants = $this->syncApplicants();
                $this->syncExistingApplicantStatuses();

                return [
                    'available' => true,
                    'applicants' => $applicants,
                    'vacancies' => $vacancies,
                    'message' => null,
                ];
            });
        } catch (Throwable $throwable) {
            report($throwable);

            return [
                'available' => false,
                'applicants' => 0,
                'vacancies' => 0,
                'message' => 'Koneksi database legacy belum tersedia.',
            ];
        }
    }

    private function syncVacancies(): int
    {
        $syncedCount = 0;

        LegacyApplicantVacancy::query()
            ->orderBy('id')
            ->cursor()
            ->each(function (LegacyApplicantVacancy $legacyVacancy) use (&$syncedCount): void {
                $statusValue = $this->integerOrNull($legacyVacancy->status);

                JobVacancy::query()->updateOrCreate(
                    ['legacy_vacancy_id' => (int) $legacyVacancy->id],
                    [
                        'legacy_value' => $this->integerOrNull($legacyVacancy->value),
                        'name' => $this->normalizeNullableText($legacyVacancy->name) ?? '-',
                        'status' => $statusValue === self::LEGACY_ACTIVE_STATUS ? 'active' : 'inactive',
                        'legacy_status_value' => $statusValue,
                        'legacy_created_at' => $this->parseLegacyTimestamp($legacyVacancy->created_at),
                    ]
                );

                $syncedCount++;
            });

        return $syncedCount;
    }

    private function syncEducationLevels(): void
    {
        LegacyEducationLevel::query()
            ->orderBy('id')
            ->cursor()
            ->each(function (LegacyEducationLevel $legacyEducationLevel): void {
                EducationLevel::query()->updateOrCreate(
                    ['legacy_education_level_id' => (int) $legacyEducationLevel->id],
                    [
                        'legacy_value' => $this->integerOrNull($legacyEducationLevel->value),
                        'name' => $this->normalizeNullableText($legacyEducationLevel->name) ?? '-',
                        'legacy_created_at' => $this->parseLegacyTimestamp($legacyEducationLevel->created_at),
                    ]
                );
            });

        $this->educationLevelIdsByLegacyValue = null;
    }

    private function syncApplicantStatuses(): void
    {
        foreach (ApplicantStatus::defaultStatuses() as $value => $name) {
            ApplicantStatus::query()->updateOrCreate(
                ['value' => $value],
                ['name' => $name]
            );
        }
    }

    private function syncApplicants(): int
    {
        $syncedCount = 0;
        $lastSyncedLegacyApplicantId = (int) Applicant::withTrashed()->max('legacy_applicant_id');

        LegacyApplicant::query()
            ->where('id', '>', $lastSyncedLegacyApplicantId)
            ->orderBy('id')
            ->cursor()
            ->each(function (LegacyApplicant $legacyApplicant) use (&$syncedCount): void {
                $jobAppliedValue = $this->integerOrNull($legacyApplicant->job_applied);
                $jobVacancy = $jobAppliedValue === null
                    ? null
                    : JobVacancy::query()->where('legacy_value', $jobAppliedValue)->first(['id']);

                $applicant = Applicant::query()->updateOrCreate(
                    ['legacy_applicant_id' => (int) $legacyApplicant->id],
                    [
                        'job_vacancy_id' => $jobVacancy?->id,
                        'slug' => $this->normalizeNullableText($legacyApplicant->slug),
                        'applicant_status_id' => $this->applicantStatusIdFor($legacyApplicant->nb),
                        'full_name' => $this->normalizeNullableText($legacyApplicant->full_name) ?? '-',
                        'nickname' => $this->normalizeNullableText($legacyApplicant->nickname),
                        'place_of_birth' => $this->normalizeNullableText($legacyApplicant->pob),
                        'date_of_birth' => $this->normalizeNullableText($legacyApplicant->dob),
                        'email' => $this->normalizeNullableText($legacyApplicant->email),
                        'phone' => $this->normalizeNullableText($legacyApplicant->phone),
                        'gender_id' => $this->genderIdFor($legacyApplicant->gender),
                        'marital_status_id' => $this->maritalStatusIdFor($legacyApplicant->marital_status),
                        'address' => $this->normalizeNullableText($legacyApplicant->address),
                        'job_applied_legacy_value' => $jobAppliedValue,
                        'expected_salary' => $this->normalizeNullableText($legacyApplicant->expected_salary),
                        'self_resume' => $this->normalizeNullableText($legacyApplicant->self_resume),
                        'portfolio_web_address' => $this->normalizeNullableText($legacyApplicant->portfolio_web_address),
                        'cv' => $this->normalizeNullableText($legacyApplicant->cv),
                        'photo' => $this->normalizeNullableText($legacyApplicant->photo),
                        'agreement' => $this->normalizeNullableText($legacyApplicant->agreement),
                        'legacy_created_at' => $this->parseLegacyTimestamp($legacyApplicant->created_at),
                    ]
                );

                $this->syncEducations($applicant, $legacyApplicant);
                $this->syncWorkExperiences($applicant, $legacyApplicant);

                $syncedCount++;
            });

        return $syncedCount;
    }

    private function syncExistingApplicantStatuses(): void
    {
        $statusIdsByValue = ApplicantStatus::query()->pluck('id', 'value');

        LegacyApplicant::query()
            ->select(['id', 'nb', 'gender', 'marital_status'])
            ->orderBy('id')
            ->cursor()
            ->each(function (LegacyApplicant $legacyApplicant) use ($statusIdsByValue): void {
                $statusValue = $this->applicantStatusValueFor($legacyApplicant->nb);

                Applicant::query()
                    ->where('legacy_applicant_id', (int) $legacyApplicant->id)
                    ->update([
                        'applicant_status_id' => $statusIdsByValue->get($statusValue),
                        'gender_id' => $this->genderIdFor($legacyApplicant->gender),
                        'marital_status_id' => $this->maritalStatusIdFor($legacyApplicant->marital_status),
                    ]);
            });
    }

    private function syncEducations(Applicant $applicant, LegacyApplicant $legacyApplicant): void
    {
        $rows = $this->combinedRows([
            'educational_level' => $legacyApplicant->educational_level,
            'institution' => $legacyApplicant->educational_institution,
            'gpa' => $legacyApplicant->gpa,
            'department' => $legacyApplicant->department,
            'start_period' => $legacyApplicant->start_education,
            'graduate_period' => $legacyApplicant->graduate_education,
        ]);

        $applicant->educations()->delete();

        $rows->each(function (array $row, int $index) use ($applicant): void {
            $educationLevelId = $this->educationLevelIdFor($row['educational_level'] ?? null);
            unset($row['educational_level']);

            ApplicantEducation::query()->create([
                'applicant_id' => $applicant->id,
                'education_level_id' => $educationLevelId,
                'sequence' => $index + 1,
            ] + $row);
        });
    }

    private function syncWorkExperiences(Applicant $applicant, LegacyApplicant $legacyApplicant): void
    {
        $rows = $this->combinedRows([
            'company_name' => $legacyApplicant->company_name,
            'role' => $legacyApplicant->role,
            'company_location' => $legacyApplicant->company_location,
            'start_period' => $legacyApplicant->start_date,
            'end_period' => $legacyApplicant->end_date,
        ]);

        $applicant->workExperiences()->delete();

        $rows->each(function (array $row, int $index) use ($applicant): void {
            ApplicantWorkExperience::query()->create([
                'applicant_id' => $applicant->id,
                'sequence' => $index + 1,
            ] + $row);
        });
    }

    /**
     * @param  array<string, mixed>  $legacyColumns
     * @return Collection<int, array<string, string|null>>
     */
    private function combinedRows(array $legacyColumns): Collection
    {
        $splitColumns = collect($legacyColumns)
            ->map(fn (mixed $value): Collection => $this->splitLegacyValue($value));

        $maxRows = $splitColumns
            ->map(fn (Collection $values): int => $values->count())
            ->max() ?? 0;

        return collect(range(0, max(0, $maxRows - 1)))
            ->map(function (int $index) use ($splitColumns): array {
                return $splitColumns
                    ->map(fn (Collection $values): ?string => $values->get($index))
                    ->all();
            })
            ->filter(function (array $row): bool {
                return collect($row)
                    ->filter(fn (?string $value): bool => $this->hasMeaningfulValue($value))
                    ->isNotEmpty();
            })
            ->values();
    }

    private function splitLegacyValue(mixed $value): Collection
    {
        $text = $this->normalizeNullableText($value);

        if ($text === null) {
            return collect();
        }

        return collect(preg_split('/\|\|/', $text) ?: [])
            ->map(fn (string $item): ?string => $this->normalizeNullableText($item))
            ->values();
    }

    private function normalizeNullableText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $text === '' ? null : $text;
    }

    private function hasMeaningfulValue(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        return ! in_array(Str::of($value)->trim()->lower()->toString(), ['', '-'], true);
    }

    private function integerOrNull(mixed $value): ?int
    {
        $text = $this->normalizeNullableText($value);

        if ($text === null || ! ctype_digit($text)) {
            return null;
        }

        return (int) $text;
    }

    private function applicantStatusValueFor(mixed $legacyStatus): int
    {
        return match ($this->integerOrNull($legacyStatus)) {
            self::LEGACY_APPLICANT_INTERVIEW_STATUS => ApplicantStatus::VALUE_INTERVIEW,
            self::LEGACY_APPLICANT_ACCEPTED_STATUS => ApplicantStatus::VALUE_DITERIMA,
            default => ApplicantStatus::VALUE_SUBMITTED,
        };
    }

    private function applicantStatusIdFor(mixed $legacyStatus): ?string
    {
        return ApplicantStatus::query()
            ->where('value', $this->applicantStatusValueFor($legacyStatus))
            ->value('id');
    }

    private function genderIdFor(mixed $legacyGender): ?int
    {
        $legacyValue = $this->integerOrNull($legacyGender);

        return $legacyValue === null ? null : $this->genderIdsByLegacyValue()->get($legacyValue);
    }

    private function maritalStatusIdFor(mixed $legacyMaritalStatus): ?int
    {
        $legacyValue = $this->integerOrNull($legacyMaritalStatus);

        return $legacyValue === null ? null : $this->maritalStatusIdsByLegacyValue()->get($legacyValue);
    }

    /**
     * @return Collection<int, int>
     */
    private function genderIdsByLegacyValue(): Collection
    {
        return $this->genderIdsByLegacyValue ??= $this->metadataIdsByLegacyValue(MetaDataGender::class, [
            1 => 'Male',
            2 => 'Female',
        ]);
    }

    /**
     * @return Collection<int, int>
     */
    private function maritalStatusIdsByLegacyValue(): Collection
    {
        return $this->maritalStatusIdsByLegacyValue ??= $this->metadataIdsByLegacyValue(MetaDataMaritalStatus::class, [
            1 => 'Single',
            2 => 'Married',
            3 => 'Divorced',
            4 => 'Widowed',
        ]);
    }

    private function educationLevelIdFor(mixed $legacyEducationLevel): ?string
    {
        $legacyValue = $this->integerOrNull($legacyEducationLevel);

        return $legacyValue === null ? null : $this->educationLevelIdsByLegacyValue()->get($legacyValue);
    }

    /**
     * @return Collection<int, string>
     */
    private function educationLevelIdsByLegacyValue(): Collection
    {
        return $this->educationLevelIdsByLegacyValue ??= EducationLevel::query()
            ->whereNotNull('legacy_value')
            ->pluck('id', 'legacy_value');
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string>  $namesByLegacyValue
     * @return Collection<int, int>
     */
    private function metadataIdsByLegacyValue(string $modelClass, array $namesByLegacyValue): Collection
    {
        return collect($namesByLegacyValue)
            ->mapWithKeys(function (string $name, int $legacyValue) use ($modelClass): array {
                $metadataId = $modelClass::query()->whereKey($legacyValue)->value('id')
                    ?? $modelClass::query()->where('name', $name)->value('id');

                return $metadataId === null ? [] : [$legacyValue => (int) $metadataId];
            });
    }

    private function parseLegacyTimestamp(mixed $value): ?Carbon
    {
        $text = $this->normalizeNullableText($value);

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
