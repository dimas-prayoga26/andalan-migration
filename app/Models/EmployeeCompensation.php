<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Database\Factories\EmployeeCompensationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Base salary and allowance of an employee; amounts are encrypted at rest.
 */
class EmployeeCompensation extends Model
{
    use GeneratesCustomSequenceUuid;

    /** @use HasFactory<EmployeeCompensationFactory> */
    use HasFactory;

    protected $table = 'employee_compensations';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $employeeCompensation): void {
            if (! is_string($employeeCompensation->id) || trim($employeeCompensation->id) === '') {
                $employeeCompensation->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'base_salary' => 'encrypted',
            'allowance' => 'encrypted',
            'effective_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
