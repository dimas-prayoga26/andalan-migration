<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessTripExpenseItem extends Model
{
    use GeneratesCustomSequenceUuid;
    use SoftDeletes;

    protected $table = 'business_trip_expense_items';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $expenseItem): void {
            if (! is_string($expenseItem->id) || trim($expenseItem->id) === '') {
                $expenseItem->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function businessTrip(): BelongsTo
    {
        return $this->belongsTo(BusinessTrip::class, 'business_trip_id', 'id');
    }
}
