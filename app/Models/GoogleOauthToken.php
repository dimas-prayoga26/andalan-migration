<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleOauthToken extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at' => 'datetime',
            'refresh_token_expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $googleOauthToken): void {
            if (! is_string($googleOauthToken->id) || trim($googleOauthToken->id) === '') {
                $googleOauthToken->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
