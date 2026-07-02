<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveSubType extends Model
{
    use SoftDeletes;

    protected $table = 'leave_sub_types';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'id');
    }
}
