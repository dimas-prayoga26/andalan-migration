<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Carbon\CarbonInterface;
use Database\Factories\EmployeeDeploymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmployeeDeployment extends Model
{
    use GeneratesCustomSequenceUuid;

    /** @use HasFactory<EmployeeDeploymentFactory> */
    use HasFactory;

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
        'is_contract' => 'boolean',
        'contract_end_date' => 'date',
        'probation_end_date' => 'date',
        'resignation_date' => 'date',
    ];

    /**
     * Probation only applies to permanent (PKWTT) employees and ends after its end date.
     */
    public function isOnProbation(?CarbonInterface $date = null): bool
    {
        if ($this->is_contract || $this->probation_end_date === null) {
            return false;
        }

        $date ??= now();

        return $date->toDateString() <= $this->probation_end_date->toDateString();
    }

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

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'employee_deployment_positions', 'employee_deployment_id', 'position_id', 'id', 'id')
            ->withPivot(['is_primary', 'sort_order', 'status', 'started_at', 'ended_at'])
            ->withTimestamps()
            ->wherePivot('status', 'active')
            ->orderBy('employee_deployment_positions.sort_order')
            ->orderBy('positions.name');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'current_department_id', 'id');
    }

    public function eventDivision(): BelongsTo
    {
        return $this->belongsTo(EventDivision::class, 'current_event_division_id', 'id');
    }
}
