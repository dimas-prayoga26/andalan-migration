<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use GeneratesCustomSequenceUuid;
    use HasFactory;

    protected $primaryKey = 'uuid';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $permission): void {
            if (! is_string($permission->uuid) || trim($permission->uuid) === '') {
                $permission->uuid = static::generateCustomSequenceUuid('uuid');
            }
        });
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'position_has_permissions', 'permission_id', 'position_id', 'uuid', 'id')
            ->withTimestamps();
    }
}
