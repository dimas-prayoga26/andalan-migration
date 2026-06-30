<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDeployment extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'employee_deployments';

    protected $guarded = [];

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

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class, 'current_office_location_id', 'id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'current_position_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'current_department_id', 'id');
    }
}
