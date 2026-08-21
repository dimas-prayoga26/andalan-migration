<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;

class EventDivision extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $attributes = [
        'status' => 'active',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $eventDivision): void {
            if (! is_string($eventDivision->id) || trim($eventDivision->id) === '') {
                $eventDivision->id = static::generateCustomSequenceUuid('id');
            }
        });
    }
}
