<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicantAssessment extends Model
{
    use GeneratesCustomSequenceUuid;

    public const SECTION_HR_INTERVIEW = 'hr_interview';

    public const SECTION_TECHNICAL_TEST = 'technical_test';

    public const SECTION_INTERVIEW_USER = 'interview_user';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    protected $fillable = [
        'applicant_id',
        'section',
        'total_score',
        'status',
        'summary',
        'notes',
        'assessed_by',
        'assessed_at',
        'submitted_at',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'total_score' => 0,
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'total_score' => 'decimal:2',
            'assessed_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $assessment): void {
            if (! is_string($assessment->id) || trim($assessment->id) === '') {
                $assessment->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by', 'id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ApplicantAssessmentScore::class, 'applicant_assessment_id', 'id')
            ->orderBy('sort_order');
    }
}
