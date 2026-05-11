<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'employee_id',
    'leave_type_id',
    'start_date',
    'end_date',
    'total_days',
    'reason',
    'is_active',
    'attachment_path',
    'permission_types',
    'status',
    'approval_status',
    'approved_by',
    'approved_at',
])]
class LeaveRequest extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'leave_requests';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'approved_at' => 'datetime',
            'is_active' => 'boolean',
            'total_days' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $leaveRequest): void {
            if (! is_string($leaveRequest->id) || trim($leaveRequest->id) === '') {
                $leaveRequest->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LeaveRequestAttachment::class);
    }
}
