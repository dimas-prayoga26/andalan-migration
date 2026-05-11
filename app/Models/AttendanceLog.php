<?php

namespace App\Models;

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
    public $timestamps = false;

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
