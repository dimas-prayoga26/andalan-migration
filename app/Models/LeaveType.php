<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'leave_types';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (self $leaveType): void {
            if (! is_string($leaveType->id) || trim($leaveType->id) === '') {
                $leaveType->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id', 'id');
    }
}
