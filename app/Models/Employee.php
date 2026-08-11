<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Employee extends Model
{
    use GeneratesCustomSequenceUuid;
    use SoftDeletes;

    private const ATTENDANCE_REST_DEDUCTION_EXEMPT_POSITION_NAMES = [
        'Driver',
        'Executive Assistant',
    ];

    protected $table = 'employees';

    protected $guarded = [];

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

    public function profile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class, 'employee_id', 'id');
    }

    public function identity(): HasOne
    {
        return $this->hasOne(EmployeeIdentity::class, 'employee_id', 'id');
    }

    public function picAssignment(): HasOne
    {
        return $this->hasOne(EmployeePicAssignment::class, 'staff_employee_id', 'id')
            ->where('is_active', true)
            ->latest('created_at');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(EmployeeAddress::class, 'employee_id', 'id');
    }

    public function latestAddress(): HasOne
    {
        return $this->hasOne(EmployeeAddress::class, 'employee_id', 'id')->latestOfMany('created_at');
    }

    public function businessTrips(): HasMany
    {
        return $this->hasMany(BusinessTrip::class, 'employee_id', 'id');
    }

    public function projectMemberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'employee_id', 'id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members', 'employee_id', 'project_id')
            ->withPivot(['id', 'joined_at', 'left_at', 'status'])
            ->withTimestamps();
    }

    public function projectTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'employee_id', 'id');
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(AttendanceOvertime::class, 'employee_id', 'id');
    }

    public function telegramUser(): HasOne
    {
        return $this->hasOne(TelegramUser::class, 'employee_id', 'id');
    }

    public function hasPositionName(string $positionName): bool
    {
        return $this->hasAnyPositionName([$positionName]);
    }

    /**
     * @param  iterable<string>  $positionNames
     */
    public function hasAnyPositionName(iterable $positionNames): bool
    {
        $normalizedPositionNames = (new Collection($positionNames))
            ->map(fn (mixed $positionName): string => strtolower(trim((string) $positionName)))
            ->filter()
            ->values();

        if ($normalizedPositionNames->isEmpty()) {
            return false;
        }

        if (! $this->exists && ! $this->relationLoaded('deployment')) {
            return false;
        }

        if (! $this->relationLoaded('deployment')) {
            $this->loadMissing('deployment.position', 'deployment.positions');
        } elseif ($this->deployment !== null) {
            $this->deployment->loadMissing('position', 'positions');
        }

        $positionNames = new Collection;

        if ($this->deployment?->position !== null) {
            $positionNames->push((string) $this->deployment->position->name);
        }

        if ($this->deployment?->positions !== null) {
            $positionNames = $positionNames->merge(
                $this->deployment->positions->pluck('name')
            );
        }

        return $positionNames
            ->filter()
            ->contains(fn (mixed $name): bool => $normalizedPositionNames->contains(strtolower(trim((string) $name))));
    }

    public function isExemptFromAttendanceRestDeduction(): bool
    {
        return $this->hasAnyPositionName(self::ATTENDANCE_REST_DEDUCTION_EXEMPT_POSITION_NAMES);
    }
}
