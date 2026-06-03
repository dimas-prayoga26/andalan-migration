<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessTripCashAdvance extends Model
{
    use GeneratesCustomSequenceUuid;
    use SoftDeletes;

    protected $table = 'business_trip_cash_advances';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'date_needed' => 'date',
        'amount_requested' => 'decimal:2',
        'amount_realized' => 'decimal:2',
        'amount_approved' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $cashAdvance): void {
            if (! is_string($cashAdvance->id) || trim($cashAdvance->id) === '') {
                $cashAdvance->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function businessTrip(): BelongsTo
    {
        return $this->belongsTo(BusinessTrip::class, 'business_trip_id', 'id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requested_by', 'id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
