<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEducation extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'employee_educations';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $employeeEducation): void {
            if (! is_string($employeeEducation->id) || trim($employeeEducation->id) === '') {
                $employeeEducation->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'gpa' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class, 'education_level_id', 'id');
    }
}
