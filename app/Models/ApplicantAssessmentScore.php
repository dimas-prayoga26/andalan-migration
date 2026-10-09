<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantAssessmentScore extends Model
{
    use GeneratesCustomSequenceUuid;

    public const SOURCE_FIXED_HR = 'fixed_hr';

    public const SOURCE_FIXED_USER = 'fixed_user';

    public const SOURCE_JOB_VACANCY_TECHNICAL_CRITERION = 'job_vacancy_technical_criterion';

    protected $fillable = [
        'applicant_assessment_id',
        'criterion_key',
        'criterion_label',
        'parent_key',
        'source_type',
        'source_id',
        'weight',
        'rating',
        'raw_score',
        'weighted_score',
        'notes',
        'sort_order',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'rating' => 'integer',
            'raw_score' => 'decimal:2',
            'weighted_score' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $score): void {
            if (! is_string($score->id) || trim($score->id) === '') {
                $score->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(ApplicantAssessment::class, 'applicant_assessment_id', 'id');
    }

    public function technicalCriterion(): BelongsTo
    {
        return $this->belongsTo(JobVacancyTechnicalCriterion::class, 'source_id', 'id');
    }
}
