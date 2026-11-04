<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrMeetingMomExport extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $momExport): void {
            if (! is_string($momExport->id) || trim($momExport->id) === '') {
                $momExport->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(HrMeeting::class, 'hr_meeting_id', 'id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'generated_by', 'id');
    }
}
