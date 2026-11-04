<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Database\Factories\EmployeeBankAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBankAccount extends Model
{
    use GeneratesCustomSequenceUuid;

    /** @use HasFactory<EmployeeBankAccountFactory> */
    use HasFactory;

    protected $table = 'employee_bank_accounts';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $employeeBankAccount): void {
            if (! is_string($employeeBankAccount->id) || trim($employeeBankAccount->id) === '') {
                $employeeBankAccount->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
