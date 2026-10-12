<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantFinalAssessment extends Model
{
    use GeneratesCustomSequenceUuid;

    public const DECISION_HIRED = 'hired';

    public const DECISION_KEEP_IN_VIEW = 'keep_in_view';

    public const DECISION_REJECTED = 'rejected';

    protected $fillable = [
        'applicant_id',
        'skill_note',
        'communication_note',
        'experience_note',
        'tools_note',
        'final_note',
        'final_decision',
        'assessed_by',
        'submitted_at',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $finalAssessment): void {
            if (! is_string($finalAssessment->id) || trim($finalAssessment->id) === '') {
                $finalAssessment->id = static::generateCustomSequenceUuid('id');
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
}
