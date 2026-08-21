<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDivisionEvent extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'project_division_event';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $attributes = [
        'status' => 'active',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $projectDivisionEvent): void {
            if (! is_string($projectDivisionEvent->id) || trim($projectDivisionEvent->id) === '') {
                $projectDivisionEvent->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function eventDivision(): BelongsTo
    {
        return $this->belongsTo(EventDivision::class, 'event_division_id', 'id');
    }
}
