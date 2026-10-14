<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLicense extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'employee_licenses';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $employeeLicense): void {
            if (! is_string($employeeLicense->id) || trim($employeeLicense->id) === '') {
                $employeeLicense->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expired_at' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
