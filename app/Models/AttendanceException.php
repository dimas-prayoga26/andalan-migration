<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceException extends Model
{
    use GeneratesCustomSequenceUuid;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'attendance_exceptions';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'exception_date' => 'date',
        'from_time' => 'datetime:H:i:s',
        'to_time' => 'datetime:H:i:s',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attendanceException): void {
            if (! is_string($attendanceException->id) || trim($attendanceException->id) === '') {
                $attendanceException->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
