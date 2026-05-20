<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessTrip extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'business_trips';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $businessTrip): void {
            if (! is_string($businessTrip->id) || trim($businessTrip->id) === '') {
                $businessTrip->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'business_trip_id', 'id');
    }
}
