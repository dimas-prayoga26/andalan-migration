<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    public function projectTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'department_id', 'id');
    }
}
