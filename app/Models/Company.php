<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'legal_name',
    'address',
    'latitude',
    'longitude',
    'city',
    'province',
    'postal_code',
    'country',
    'industry',
    'primary_color',
    'secondary_color',
    'vision',
    'mission',
    'description',
    'phone',
    'email',
    'website',
    'is_active',
])]
class Company extends Model
{
    use GeneratesCustomSequenceUuid;

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
}
