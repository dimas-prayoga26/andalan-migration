<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailAccountTakeover extends Model
{
    protected $table = 'mail_business_account_takeovers';

    protected $fillable = [
        'mail_business_account_id',
        'source_employee_id',
        'target_employee_id',
        'assigned_by_employee_id',
        'revoked_by_employee_id',
        'can_read',
        'starts_at',
        'ends_at',
        'revoked_at',
        'reason',
    ];

    protected $attributes = [
        'can_read' => true,
    ];

    protected function casts(): array
    {
        return [
            'can_read' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function mailBusinessAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccessAccount::class, 'mail_business_account_id', 'id');
    }

    public function mailAccessAccount(): BelongsTo
    {
        return $this->mailBusinessAccount();
    }

    public function sourceEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'source_employee_id', 'id');
    }

    public function targetEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'target_employee_id', 'id');
    }

    public function assignedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_by_employee_id', 'id');
    }

    public function revokedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'revoked_by_employee_id', 'id');
    }

    public function scopeReadable(Builder $query): Builder
    {
        return $query
            ->where('can_read', true)
            ->whereNull('revoked_at')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }
}
