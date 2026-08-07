<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantWorkExperience extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'applicant_work_experiences';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $applicantWorkExperience): void {
            if (! is_string($applicantWorkExperience->id) || trim($applicantWorkExperience->id) === '') {
                $applicantWorkExperience->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'id');
    }
}
