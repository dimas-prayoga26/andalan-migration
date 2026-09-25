<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class MailAccessAccount extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
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
}
