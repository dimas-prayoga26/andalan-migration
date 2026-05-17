<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $company): void {
            if (! is_string($company->id) || trim($company->id) === '') {
                $company->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function attendanceRules(): HasMany
    {
        return $this->hasMany(RulesOfAttendace::class, 'companies_id', 'id');
    }

    public function activeAttendanceRule(): HasOne
    {
        return $this->hasOne(RulesOfAttendace::class, 'companies_id', 'id')
            ->where('rules_of_attendaces.is_active', true)
            ->latestOfMany('created_at');
    }
}
