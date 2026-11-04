<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicantStatus extends Model
{
    use GeneratesCustomSequenceUuid;

    public const VALUE_SUBMITTED = 0;

    public const VALUE_HR_INTERVIEW = 1;

    public const VALUE_TECHNICAL_TEST = 2;

    public const VALUE_USER_INTERVIEW = 3;

    public const VALUE_OFFERING = 4;

    public const VALUE_NOT_SUITABLE = 5;

    protected $table = 'applicant_statuses';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'value' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $applicantStatus): void {
            if (! is_string($applicantStatus->id) || trim($applicantStatus->id) === '') {
                $applicantStatus->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class, 'applicant_status_id', 'id');
    }

    /**
     * @return array<int, string>
     */
    public static function defaultStatuses(): array
    {
        return [
            self::VALUE_SUBMITTED => 'Submitted',
            self::VALUE_HR_INTERVIEW => 'HR Interview',
            self::VALUE_TECHNICAL_TEST => 'Technical test',
            self::VALUE_USER_INTERVIEW => 'User Interview',
            self::VALUE_OFFERING => 'Offering',
            self::VALUE_NOT_SUITABLE => 'Not Suitable',
        ];
    }
}
