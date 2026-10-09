<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\ApplicantAssessment;
use App\Models\ApplicantAssessmentScore;
use App\Models\JobVacancyTechnicalCriterion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicantAssessmentScoreService
{
    /**
     * @return array<int, array{key: string, label: string, weight: int|float, default_rating: int, sort_order: int}>
     */
    public function hrCriteria(): array
    {
        return array_values((array) config('applicant_assessments.hr_interview.criteria', []));
    }

    /**
     * @return array<int, array{key: string, label: string, weight: int|float, default_rating: int, sort_order: int}>
     */
    public function userCriteria(): array
    {
        return array_values((array) config('applicant_assessments.user_interview.criteria', []));
    }

    /**
     * @return array<int, array{key: string, label: string, weight: int|float, weight_label: string, selected: int}>
     */
    public function hrCriteriaForView(Applicant $applicant): array
    {
        $assessment = $this->assessmentForSection($applicant, ApplicantAssessment::SECTION_HR_INTERVIEW);
        $scoresByCriterion = $assessment instanceof ApplicantAssessment
            ? $assessment->scores->keyBy('criterion_key')
            : collect();

        return array_map(
            fn (array $criterion): array => $this->criterionForView($criterion, $scoresByCriterion),
            $this->hrCriteria()
        );
    }

    /**
     * @return array<int, array{key: string, label: string, weight: int|float, weight_label: string, selected: int}>
     */
    public function technicalCriteriaForView(Applicant $applicant): array
    {
        $applicant->loadMissing('jobVacancy.technicalCriteria');

        $assessment = $this->assessmentForSection($applicant, ApplicantAssessment::SECTION_TECHNICAL_TEST);
        $scoresBySourceId = $assessment instanceof ApplicantAssessment
            ? $assessment->scores->keyBy('source_id')
            : collect();

        return $applicant->jobVacancy?->technicalCriteria
            ->map(fn (JobVacancyTechnicalCriterion $criterion): array => $this->technicalCriterionForView($criterion, $scoresBySourceId))
            ->values()
            ->all() ?? [];
    }

    /**
     * @return array<int, array{key: string, label: string, weight: int|float, weight_label: string, selected: int}>
     */
    public function userCriteriaForView(Applicant $applicant): array
    {
        $assessment = $this->assessmentForSection($applicant, ApplicantAssessment::SECTION_INTERVIEW_USER);
        $scoresByCriterion = $assessment instanceof ApplicantAssessment
            ? $assessment->scores->keyBy('criterion_key')
            : collect();

        return array_map(
            fn (array $criterion): array => $this->criterionForView($criterion, $scoresByCriterion),
            $this->userCriteria()
        );
    }

    /**
     * @param  array<int, array{weight: int|float, selected?: int, default_rating?: int}>  $criteria
     */
    public function calculateTotalScore(array $criteria): float
    {
        return round(array_reduce($criteria, function (float $total, array $criterion): float {
            $rating = (int) ($criterion['selected'] ?? $criterion['default_rating'] ?? 0);
            $weight = (float) $criterion['weight'];

            return $total + $this->weightedScore($rating, $weight);
        }, 0.0), 2);
    }

    public function scoreForSection(Applicant $applicant, string $section, ?float $fallback = null): ?float
    {
        $assessment = $this->assessmentForSection($applicant, $section);

        if (! $assessment instanceof ApplicantAssessment) {
            return $fallback;
        }

        return (float) $assessment->total_score;
    }

    public function notesForSection(Applicant $applicant, string $section): string
    {
        $assessment = $this->assessmentForSection($applicant, $section);

        return $assessment instanceof ApplicantAssessment ? (string) ($assessment->notes ?? '') : '';
    }

    public function storeSectionNotes(
        Applicant $applicant,
        string $section,
        ?string $notes,
        ?User $assessor
    ): ApplicantAssessment {
        if (! in_array($section, [ApplicantAssessment::SECTION_TECHNICAL_TEST, ApplicantAssessment::SECTION_INTERVIEW_USER], true)) {
            throw ValidationException::withMessages([
                'section' => 'Section assessment tidak valid.',
            ]);
        }

        $cleanNotes = trim((string) $notes);

        return ApplicantAssessment::query()->updateOrCreate(
            [
                'applicant_id' => $applicant->id,
                'section' => $section,
            ],
            [
                'notes' => $cleanNotes !== '' ? $cleanNotes : null,
                'status' => ApplicantAssessment::STATUS_SUBMITTED,
                'assessed_by' => $assessor?->id,
                'assessed_at' => now(),
                'submitted_at' => now(),
            ]
        );
    }

    public function storeHrInterviewRating(
        Applicant $applicant,
        string $criterionKey,
        int $rating,
        ?User $assessor
    ): ApplicantAssessment {
        $criterion = $this->hrCriterion($criterionKey);

        if ($criterion === null) {
            throw ValidationException::withMessages([
                'criterion_key' => 'Kriteria interview HR tidak valid.',
            ]);
        }

        return DB::transaction(function () use ($applicant, $criterion, $rating, $assessor): ApplicantAssessment {
            $assessment = ApplicantAssessment::query()->updateOrCreate(
                [
                    'applicant_id' => $applicant->id,
                    'section' => ApplicantAssessment::SECTION_HR_INTERVIEW,
                ],
                [
                    'status' => ApplicantAssessment::STATUS_SUBMITTED,
                    'assessed_by' => $assessor?->id,
                    'assessed_at' => now(),
                    'submitted_at' => now(),
                ]
            );

            $this->ensureHrScores($assessment);

            $assessment->scores()->updateOrCreate(
                ['criterion_key' => $criterion['key']],
                $this->scorePayload($criterion, $rating, ApplicantAssessmentScore::SOURCE_FIXED_HR)
            );

            $assessment->forceFill([
                'total_score' => (float) $assessment->scores()->sum('weighted_score'),
                'status' => ApplicantAssessment::STATUS_SUBMITTED,
                'assessed_by' => $assessor?->id,
                'assessed_at' => now(),
                'submitted_at' => $assessment->submitted_at ?? now(),
            ])->save();

            return $assessment->refresh()->load('scores');
        });
    }

    public function storeTechnicalTestRating(
        Applicant $applicant,
        string $criterionId,
        int $rating,
        ?User $assessor
    ): ApplicantAssessment {
        $criterion = $this->technicalCriterion($applicant, $criterionId);

        if (! $criterion instanceof JobVacancyTechnicalCriterion) {
            throw ValidationException::withMessages([
                'criterion_key' => 'Kriteria technical test tidak valid untuk lowongan kandidat ini.',
            ]);
        }

        return DB::transaction(function () use ($applicant, $criterion, $rating, $assessor): ApplicantAssessment {
            $assessment = ApplicantAssessment::query()->updateOrCreate(
                [
                    'applicant_id' => $applicant->id,
                    'section' => ApplicantAssessment::SECTION_TECHNICAL_TEST,
                ],
                [
                    'status' => ApplicantAssessment::STATUS_SUBMITTED,
                    'assessed_by' => $assessor?->id,
                    'assessed_at' => now(),
                    'submitted_at' => now(),
                ]
            );

            $this->ensureTechnicalScores($assessment, $applicant);

            $assessment->scores()->updateOrCreate(
                ['source_id' => $criterion->id],
                $this->technicalScorePayload($criterion, $rating)
            );

            $assessment->forceFill([
                'total_score' => (float) $assessment->scores()->sum('weighted_score'),
                'status' => ApplicantAssessment::STATUS_SUBMITTED,
                'assessed_by' => $assessor?->id,
                'assessed_at' => now(),
                'submitted_at' => $assessment->submitted_at ?? now(),
            ])->save();

            return $assessment->refresh()->load('scores');
        });
    }

    public function storeUserInterviewRating(
        Applicant $applicant,
        string $criterionKey,
        int $rating,
        ?User $assessor
    ): ApplicantAssessment {
        $criterion = $this->userCriterion($criterionKey);

        if ($criterion === null) {
            throw ValidationException::withMessages([
                'criterion_key' => 'Kriteria interview user tidak valid.',
            ]);
        }

        return DB::transaction(function () use ($applicant, $criterion, $rating, $assessor): ApplicantAssessment {
            $assessment = ApplicantAssessment::query()->updateOrCreate(
                [
                    'applicant_id' => $applicant->id,
                    'section' => ApplicantAssessment::SECTION_INTERVIEW_USER,
                ],
                [
                    'status' => ApplicantAssessment::STATUS_SUBMITTED,
                    'assessed_by' => $assessor?->id,
                    'assessed_at' => now(),
                    'submitted_at' => now(),
                ]
            );

            $this->ensureUserScores($assessment);

            $assessment->scores()->updateOrCreate(
                ['criterion_key' => $criterion['key']],
                $this->scorePayload($criterion, $rating, ApplicantAssessmentScore::SOURCE_FIXED_USER)
            );

            $assessment->forceFill([
                'total_score' => (float) $assessment->scores()->sum('weighted_score'),
                'status' => ApplicantAssessment::STATUS_SUBMITTED,
                'assessed_by' => $assessor?->id,
                'assessed_at' => now(),
                'submitted_at' => $assessment->submitted_at ?? now(),
            ])->save();

            return $assessment->refresh()->load('scores');
        });
    }

    public function weightedScore(int $rating, float $weight): float
    {
        return round(($rating / 5) * $weight, 2);
    }

    /**
     * @param  Collection<string, ApplicantAssessmentScore>  $scoresBySourceId
     * @return array{key: string, label: string, weight: int|float, weight_label: string, selected: int}
     */
    private function technicalCriterionForView(JobVacancyTechnicalCriterion $criterion, Collection $scoresBySourceId): array
    {
        $score = $scoresBySourceId->get((string) $criterion->id);

        return [
            'key' => (string) $criterion->id,
            'label' => (string) $criterion->name,
            'weight' => (float) $criterion->weight,
            'weight_label' => $this->weightLabel((float) $criterion->weight),
            'selected' => $score instanceof ApplicantAssessmentScore ? (int) $score->rating : 0,
        ];
    }

    private function assessmentForSection(Applicant $applicant, string $section): ?ApplicantAssessment
    {
        return ApplicantAssessment::query()
            ->with('scores')
            ->whereBelongsTo($applicant)
            ->where('section', $section)
            ->first();
    }

    /**
     * @param  array{key: string, label: string, weight: int|float, default_rating: int}  $criterion
     * @param  Collection<string, ApplicantAssessmentScore>  $scoresByCriterion
     * @return array{key: string, label: string, weight: int|float, weight_label: string, selected: int}
     */
    private function criterionForView(array $criterion, Collection $scoresByCriterion): array
    {
        $score = $scoresByCriterion->get($criterion['key']);
        $selected = $score instanceof ApplicantAssessmentScore
            ? (int) $score->rating
            : (int) $criterion['default_rating'];

        return [
            'key' => $criterion['key'],
            'label' => $criterion['label'],
            'weight' => $criterion['weight'],
            'weight_label' => $this->weightLabel((float) $criterion['weight']),
            'selected' => $selected,
        ];
    }

    /**
     * @return array{key: string, label: string, weight: int|float, default_rating: int, sort_order: int}|null
     */
    private function hrCriterion(string $criterionKey): ?array
    {
        foreach ($this->hrCriteria() as $criterion) {
            if ($criterion['key'] === $criterionKey) {
                return $criterion;
            }
        }

        return null;
    }

    /**
     * @return array{key: string, label: string, weight: int|float, default_rating: int, sort_order: int}|null
     */
    private function userCriterion(string $criterionKey): ?array
    {
        foreach ($this->userCriteria() as $criterion) {
            if ($criterion['key'] === $criterionKey) {
                return $criterion;
            }
        }

        return null;
    }

    private function ensureHrScores(ApplicantAssessment $assessment): void
    {
        foreach ($this->hrCriteria() as $criterion) {
            $assessment->scores()->firstOrCreate(
                ['criterion_key' => $criterion['key']],
                $this->scorePayload($criterion, (int) $criterion['default_rating'], ApplicantAssessmentScore::SOURCE_FIXED_HR)
            );
        }
    }

    private function ensureUserScores(ApplicantAssessment $assessment): void
    {
        foreach ($this->userCriteria() as $criterion) {
            $assessment->scores()->firstOrCreate(
                ['criterion_key' => $criterion['key']],
                $this->scorePayload($criterion, (int) $criterion['default_rating'], ApplicantAssessmentScore::SOURCE_FIXED_USER)
            );
        }
    }

    private function ensureTechnicalScores(ApplicantAssessment $assessment, Applicant $applicant): void
    {
        $applicant->loadMissing('jobVacancy.technicalCriteria');

        foreach ($applicant->jobVacancy?->technicalCriteria ?? [] as $criterion) {
            $assessment->scores()->firstOrCreate(
                ['source_id' => $criterion->id],
                $this->technicalScorePayload($criterion, 0)
            );
        }
    }

    /**
     * @param  array{key: string, label: string, weight: int|float, sort_order: int}  $criterion
     * @return array<string, mixed>
     */
    private function scorePayload(array $criterion, int $rating, string $sourceType): array
    {
        $weight = (float) $criterion['weight'];

        return [
            'criterion_label' => $criterion['label'],
            'source_type' => $sourceType,
            'weight' => $weight,
            'rating' => $rating,
            'raw_score' => $rating,
            'weighted_score' => $this->weightedScore($rating, $weight),
            'sort_order' => (int) $criterion['sort_order'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function technicalScorePayload(JobVacancyTechnicalCriterion $criterion, int $rating): array
    {
        $weight = (float) $criterion->weight;

        return [
            'criterion_key' => (string) $criterion->id,
            'criterion_label' => (string) $criterion->name,
            'source_type' => ApplicantAssessmentScore::SOURCE_JOB_VACANCY_TECHNICAL_CRITERION,
            'source_id' => $criterion->id,
            'weight' => $weight,
            'rating' => $rating,
            'raw_score' => $rating,
            'weighted_score' => $this->weightedScore($rating, $weight),
            'sort_order' => (int) $criterion->sort_order,
        ];
    }

    private function technicalCriterion(Applicant $applicant, string $criterionId): ?JobVacancyTechnicalCriterion
    {
        $applicant->loadMissing('jobVacancy');

        if ($applicant->jobVacancy === null) {
            return null;
        }

        return $applicant->jobVacancy
            ->technicalCriteria()
            ->whereKey($criterionId)
            ->first();
    }

    private function weightLabel(float $weight): string
    {
        return rtrim(rtrim(number_format($weight, 2), '0'), '.').'%';
    }
}
