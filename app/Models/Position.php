<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'positions';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $position): void {
            if (! is_string($position->id) || trim($position->id) === '') {
                $position->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'position_has_permissions', 'position_id', 'permission_id', 'id', 'uuid')
            ->withTimestamps();
    }

    public function deployments(): BelongsToMany
    {
        return $this->belongsToMany(EmployeeDeployment::class, 'employee_deployment_positions', 'position_id', 'employee_deployment_id', 'id', 'id')
            ->withPivot(['is_primary', 'status', 'started_at', 'ended_at'])
            ->withTimestamps()
            ->wherePivot('status', 'active');
    }
}
