<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

class Attendance extends Model
{
    use GeneratesCustomSequenceUuid;
    use HasFactory, HasRoles;

    protected $table = 'attendances';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime:H:i:s',
        'clock_out' => 'datetime:H:i:s',
        'is_overtime' => 'boolean',
        'work_hours' => 'decimal:2',
    ];

    protected $fillable = [
        'employee_id',
        'leave_request_id',
        'date',
        'clock_in',
        'clock_out',
        'late_minutes',
        'work_hours',
        'overtime_id',
        'is_overtime',
        'status',
        'location_in',
        'location_out',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attendance): void {
            if (! is_string($attendance->id) || trim($attendance->id) === '') {
                $attendance->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
