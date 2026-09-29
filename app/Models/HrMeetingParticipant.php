<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrMeetingParticipant extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $participant): void {
            if (! is_string($participant->id) || trim($participant->id) === '') {
                $participant->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(HrMeeting::class, 'hr_meeting_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
