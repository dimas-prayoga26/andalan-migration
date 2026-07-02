<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeLifecycleLog extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $attributes = [
        'status' => 'waiting',
    ];

    protected function casts(): array
    {
        return [
            'happened_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $lifecycleLog): void {
            if (! is_string($lifecycleLog->id) || trim($lifecycleLog->id) === '') {
                $lifecycleLog->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function overtime(): BelongsTo
    {
        return $this->belongsTo(AttendanceOvertime::class, 'overtime_id', 'id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }
}
