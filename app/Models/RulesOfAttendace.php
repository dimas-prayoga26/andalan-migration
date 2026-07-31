<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RulesOfAttendace extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'rules_of_attendaces';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'radius' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $rule): void {
            if (! is_string($rule->id) || trim($rule->id) === '') {
                $rule->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class, 'office_location_id', 'id');
    }
}
