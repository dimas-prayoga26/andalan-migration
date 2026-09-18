<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    use GeneratesCustomSequenceUuid;

    public const LoggedIn = 'logged_in';

    public const LoggedOut = 'logged_out';

    public const PageVisited = 'page_visited';

    public const ClockInClicked = 'clock_in_clicked';

    public const ClockInVerified = 'clock_in_verified';

    public const ClockInSubmitted = 'clock_in_submitted';

    public const ClockOutClicked = 'clock_out_clicked';

    public const ClockOutVerified = 'clock_out_verified';

    public const ClockOutSubmitted = 'clock_out_submitted';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $activityLog): void {
            if (! is_string($activityLog->id) || trim($activityLog->id) === '') {
                $activityLog->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'id');
    }
}
