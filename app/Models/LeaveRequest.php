<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'leave_requests';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $leaveRequest): void {
            if (! is_string($leaveRequest->id) || trim($leaveRequest->id) === '') {
                $leaveRequest->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LeaveRequestAttachment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(LeaveRequestHistory::class, 'leave_request_id', 'id');
    }

    public function hasApprovedSupervisorReview(): bool
    {
        $isApprovedSupervisorReview = static function (LeaveRequestHistory $history): bool {
            $normalizedEventType = is_string($history->event_type) ? strtolower(trim($history->event_type)) : '';
            $normalizedStatus = is_string($history->to_status) ? strtolower(trim($history->to_status)) : '';

            return $normalizedEventType === 'supervisor_review'
                && $normalizedStatus === 'approved';
        };

        if ($this->relationLoaded('histories')) {
            return $this->histories->contains($isApprovedSupervisorReview);
        }

        return $this->histories()
            ->where('event_type', 'supervisor_review')
            ->where('to_status', 'approved')
            ->exists();
    }
}
