<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceHoliday extends Model
{
    protected $table = 'attendances_holidays';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'date',
        'name',
        'type',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date:Y-m-d',
        'type' => 'integer',
    ];
}
