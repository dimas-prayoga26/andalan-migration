<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attendance_id',
    'location_in',
    'location_out',
    'latitude',
    'longitude',
    'radius_result',
    'distance',
    'ip_address',
    'user_agent',
    'device_hash',
    'address_village',
    'address_district',
    'address_regency',
    'address_city',
    'address_province',
    'address_postal_code',
    'geocoded_at',
])]
class AttendanceLog extends Model
{
    use GeneratesCustomSequenceUuid;

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
