<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Database\Factories\EmployeeFamilyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFamily extends Model
{
    use GeneratesCustomSequenceUuid;

    /** @use HasFactory<EmployeeFamilyFactory> */
    use HasFactory;

    protected $table = 'employee_families';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $employeeFamily): void {
            if (! is_string($employeeFamily->id) || trim($employeeFamily->id) === '') {
                $employeeFamily->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_dependents' => 'boolean',
            'is_emergency_contact' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function scopeEmergencyContact(Builder $query): Builder
    {
        return $query->where('is_emergency_contact', true);
    }
}
