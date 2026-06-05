<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessTrip extends Model
{
    use GeneratesCustomSequenceUuid;
    use SoftDeletes;

    protected $table = 'business_trips';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_rate' => 'decimal:2',
        'total_allowance' => 'decimal:2',
        'departure_date' => 'date',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

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

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_employee_id', 'id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'business_trip_id', 'id');
    }

    public function cashAdvances(): HasMany
    {
        return $this->hasMany(BusinessTripCashAdvance::class, 'business_trip_id', 'id');
    }

    public function reimbursements(): HasMany
    {
        return $this->hasMany(BusinessTripReimbursement::class, 'business_trip_id', 'id');
    }

    public function lifecycleLogs(): HasMany
    {
        return $this->hasMany(BusinessTripLifecycleLog::class, 'business_trip_id', 'id');
    }
}
