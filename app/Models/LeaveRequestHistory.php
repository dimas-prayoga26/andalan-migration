<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestHistory extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'leave_request_histories';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'metadata' => 'array',
        'happened_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $history): void {
            if (! is_string($history->id) || trim($history->id) === '') {
                $history->id = static::generateCustomSequenceUuid('id');
            }
        });

        static::saved(function (self $history): void {
            if (! $history->isApprovedSupervisorReview()) {
                return;
            }

            static::query()->firstOrCreate(
                [
                    'leave_request_id' => $history->leave_request_id,
                    'event_type' => 'hr_verification',
                ],
                [
                    'actor_user_id' => null,
                    'title' => 'HR Verification (Pending)',
                    'from_status' => 'pending',
                    'to_status' => 'pending',
                    'notes' => null,
                    'metadata' => null,
                    'happened_at' => now('Asia/Jakarta'),
                ]
            );
        });
    }

    private function isApprovedSupervisorReview(): bool
    {
        return strtolower(trim((string) $this->event_type)) === 'supervisor_review'
            && strtolower(trim((string) $this->to_status)) === 'approved';
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id', 'id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id', 'id');
    }
}
