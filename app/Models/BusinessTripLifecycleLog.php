<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessTripLifecycleLog extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'business_trip_lifecycle_logs';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $attributes = [
        'status' => 'waiting',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $lifecycleLog): void {
            if (! is_string($lifecycleLog->id) || trim($lifecycleLog->id) === '') {
                $lifecycleLog->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function businessTrip(): BelongsTo
    {
        return $this->belongsTo(BusinessTrip::class, 'business_trip_id', 'id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }
}
