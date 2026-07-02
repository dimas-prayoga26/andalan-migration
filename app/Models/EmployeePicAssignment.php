<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePicAssignment extends Model
{
    use GeneratesCustomSequenceUuid;
    use SoftDeletes;

    protected $table = 'employee_pic_assignments';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $employeePicAssignment): void {
            if (! is_string($employeePicAssignment->id) || trim($employeePicAssignment->id) === '') {
                $employeePicAssignment->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_employee_id', 'id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'staff_employee_id', 'id');
    }
}
