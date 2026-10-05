<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class MailAccessAccount extends Model
{
    public const TYPE_PERSONAL = 'personal';

    public const TYPE_DEPARTMENT = 'department';

    protected $guarded = [];

    protected $attributes = [
        'type' => self::TYPE_PERSONAL,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDepartment(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_DEPARTMENT);
    }

    /**
     * @return Attribute<string, string>
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: static fn (string $value): string => mb_strtolower(trim($value)),
        );
    }

    public function pinMatches(string $pin): bool
    {
        $storedPin = (string) $this->pin;

        if (hash_equals($storedPin, $pin)) {
            return true;
        }

        return Hash::isHashed($storedPin) && Hash::check($pin, $storedPin);
    }

    public function ensurePinIsHashed(string $pin): void
    {
        if (Hash::isHashed((string) $this->pin) && Hash::check($pin, (string) $this->pin)) {
            return;
        }

        $this->forceFill([
            'pin' => Hash::make($pin),
        ])->save();
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_PERSONAL => 'Personal',
            self::TYPE_DEPARTMENT => 'Department',
        ];
    }
}
