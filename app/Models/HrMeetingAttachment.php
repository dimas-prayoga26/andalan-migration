<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrMeetingAttachment extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $attachment): void {
            if (! is_string($attachment->id) || trim($attachment->id) === '') {
                $attachment->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(HrMeeting::class, 'hr_meeting_id', 'id');
    }
}
