<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'employee_code',
    'status',
])]
class Employee extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'employees';

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $employee): void {
            if (! is_string($employee->id) || trim($employee->id) === '') {
                $employee->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function deployment(): HasOne
    {
        return $this->hasOne(EmployeeDeployment::class, 'employee_id', 'id');
    }
}
