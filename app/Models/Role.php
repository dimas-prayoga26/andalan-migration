<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use GeneratesCustomSequenceUuid;
    use HasFactory;

    protected $primaryKey = 'uuid';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $role): void {
            if (! is_string($role->uuid) || trim($role->uuid) === '') {
                $role->uuid = static::generateCustomSequenceUuid('uuid');
            }
        });
    }
}
