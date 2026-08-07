<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationLevel extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'education_levels';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'legacy_created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $educationLevel): void {
            if (! is_string($educationLevel->id) || trim($educationLevel->id) === '') {
                $educationLevel->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function applicantEducations(): HasMany
    {
        return $this->hasMany(ApplicantEducation::class, 'education_level_id', 'id');
    }
}
