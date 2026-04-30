<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'legal_name',
    'address',
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
    'images',
    'is_active',
])]
class Company extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
