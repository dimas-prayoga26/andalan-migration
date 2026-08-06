<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RulesOfAttendace extends Model
{
    public const TYPE_FIXED = 'fixed';

    public const TYPE_FLEXIBLE = 'flexible';

    use GeneratesCustomSequenceUuid;

    protected $table = 'rules_of_attendaces';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $attributes = [
        'attendance_type' => self::TYPE_FIXED,
        'office_reset_time' => '00:00:00',
    ];

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

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'attendance_rule_positions', 'attendance_rule_id', 'position_id', 'id', 'id')
            ->withTimestamps();
    }

    public function isFlexible(): bool
    {
        return $this->attendance_type === self::TYPE_FLEXIBLE;
    }
}
