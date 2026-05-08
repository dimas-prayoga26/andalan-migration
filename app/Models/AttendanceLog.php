<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attendance_id',
    'check_in',
    'check_out',
    'latitude',
    'longitude',
    'radius_result',
    'distance',
    'ip_address',
    'user_agent',
    'device_hash',
    'location_source',
    'formatted_address',
    'address_village',
    'address_district',
    'address_regency',
    'address_city',
    'address_province',
    'address_postal_code',
    'geocoding_provider',
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
