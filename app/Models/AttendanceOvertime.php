<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'assigned_by',
    'overtime_date',
    'planned_start_time',
    'planned_end_time',
    'instruction',
    'actual_start_time',
    'actual_end_time',
    'calculated_hours',
    'status',
])]
class AttendanceOvertime extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'overtimes';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'overtime_date' => 'date',
            'calculated_hours' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $overtime): void {
            if (! is_string($overtime->id) || trim($overtime->id) === '') {
                $overtime->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by', 'id');
    }
}
