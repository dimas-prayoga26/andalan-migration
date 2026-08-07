<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaDataGender extends Model
{
    protected $table = 'meta_data_gender';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class, 'gender_id', 'id');
    }
}
