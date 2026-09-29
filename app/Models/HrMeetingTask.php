<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrMeetingTask extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [];
    }

    protected static function booted(): void
    {
        static::creating(function (self $task): void {
            if (! is_string($task->id) || trim($task->id) === '') {
                $task->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(HrMeeting::class, 'hr_meeting_id', 'id');
    }

    public function projectTask(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id', 'id');
    }

}
