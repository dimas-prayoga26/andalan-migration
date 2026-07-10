<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OfficeLocation extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $officeLocation): void {
            if (! is_string($officeLocation->id) || trim($officeLocation->id) === '') {
                $officeLocation->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function attendanceRules(): HasMany
    {
        return $this->hasMany(RulesOfAttendace::class, 'office_location_id', 'id');
    }

    public function activeAttendanceRule(): HasOne
    {
        return $this->hasOne(RulesOfAttendace::class, 'office_location_id', 'id')
            ->where('rules_of_attendaces.is_active', true)
            ->latestOfMany('created_at');
    }
}
