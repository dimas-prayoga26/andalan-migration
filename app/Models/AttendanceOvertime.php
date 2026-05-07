<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'overtime_date',
    'start_time',
    'end_time',
    'description',
    'notes',
    'approval_status',
])]
class AttendanceOvertime extends Model
{
    protected $table = 'attendances_overtime';

    protected function casts(): array
    {
        return [
            'overtime_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
