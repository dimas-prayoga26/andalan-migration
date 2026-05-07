<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attendance_permission_id',
    'file_path',
    'file_name',
    'attachment_type',
])]
class AttendancePermissionAttachment extends Model
{
    public function attendancePermission(): BelongsTo
    {
        return $this->belongsTo(AttendancePermission::class);
    }
}
