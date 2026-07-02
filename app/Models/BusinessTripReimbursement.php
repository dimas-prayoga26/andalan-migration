<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessTripReimbursement extends Model
{
    use GeneratesCustomSequenceUuid;
    use SoftDeletes;

    protected $table = 'business_trip_reimbursements';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'amount_approved' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $reimbursement): void {
            if (! is_string($reimbursement->id) || trim($reimbursement->id) === '') {
                $reimbursement->id = static::generateCustomSequenceUuid('id');
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
