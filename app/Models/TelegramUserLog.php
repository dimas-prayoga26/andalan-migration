<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramUserLog extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'telegram_users_logs';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'is_success' => 'boolean',
            'http_status_code' => 'integer',
            'response_payload' => 'array',
            'notified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $telegramUserLog): void {
            if (! is_string($telegramUserLog->id) || trim($telegramUserLog->id) === '') {
                $telegramUserLog->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function telegramUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id', 'id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'id');
    }
}
