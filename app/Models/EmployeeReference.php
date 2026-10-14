<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeReference extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'employee_references';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $employeeReference): void {
            if (! is_string($employeeReference->id) || trim($employeeReference->id) === '') {
                $employeeReference->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
