<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobVacancy extends Model
{
    use GeneratesCustomSequenceUuid;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const LEGACY_STATUS_ACTIVE = 1;

    public const LEGACY_STATUS_INACTIVE = 2;

    protected $table = 'job_vacancies';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $attributes = [
        'status' => self::STATUS_INACTIVE,
    ];

    protected function casts(): array
    {
        return [
            'legacy_created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $jobVacancy): void {
            if (! is_string($jobVacancy->id) || trim($jobVacancy->id) === '') {
                $jobVacancy->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class, 'job_vacancy_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Non Active',
        ];
    }

    public static function legacyStatusValueFor(string $status): int
    {
        return $status === self::STATUS_ACTIVE
            ? self::LEGACY_STATUS_ACTIVE
            : self::LEGACY_STATUS_INACTIVE;
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? 'Non Active';
    }
}
