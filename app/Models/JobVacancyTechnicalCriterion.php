<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobVacancyTechnicalCriterion extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'job_vacancy_technical_criteria';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $criterion): void {
            if (! is_string($criterion->id) || trim($criterion->id) === '') {
                $criterion->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id', 'id');
    }
}
