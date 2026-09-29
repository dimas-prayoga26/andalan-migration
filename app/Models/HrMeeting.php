<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrMeeting extends Model
{
    use GeneratesCustomSequenceUuid, SoftDeletes;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $meeting): void {
            if (! is_string($meeting->id) || trim($meeting->id) === '') {
                $meeting->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(HrMeetingParticipant::class, 'hr_meeting_id', 'id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(HrMeetingTask::class, 'hr_meeting_id', 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(HrMeetingAttachment::class, 'hr_meeting_id', 'id');
    }

    public function momExports(): HasMany
    {
        return $this->hasMany(HrMeetingMomExport::class, 'hr_meeting_id', 'id');
    }
}
