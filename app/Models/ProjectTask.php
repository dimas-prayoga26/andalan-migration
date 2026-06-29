<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use GeneratesCustomSequenceUuid, SoftDeletes;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $projectTask): void {
            if (! is_string($projectTask->id) || trim($projectTask->id) === '') {
                $projectTask->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by', 'id');
    }

    public function overtime(): BelongsTo
    {
        return $this->belongsTo(AttendanceOvertime::class, 'overtime_id', 'id');
    }
}
