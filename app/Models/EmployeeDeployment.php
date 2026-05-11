<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'current_department_id',
    'current_position_id',
    'current_company_id',
    'join_date',
    'resignation_date',
    'workplace',
    'status',
])]
class EmployeeDeployment extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'employee_deployments';

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $employeeDeployment): void {
            if (! is_string($employeeDeployment->id) || trim($employeeDeployment->id) === '') {
                $employeeDeployment->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    protected $casts = [
        'join_date' => 'date',
        'resignation_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }
}
