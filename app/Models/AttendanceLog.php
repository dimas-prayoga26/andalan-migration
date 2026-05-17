<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $guarded = [];

    protected $keyType = 'string';

    protected $table = 'attendance_logs';

    public $incrementing = false;

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (self $attendance): void {
            if (! is_string($attendance->id) || trim($attendance->id) === '') {
                $attendance->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
