<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrMeeting extends Model
{
    use GeneratesCustomSequenceUuid, SoftDeletes;

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $meeting): void {
            if (! is_string($meeting->id) || trim($meeting->id) === '') {
                $meeting->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    /**
     * The meeting's scheduled start moment in Asia/Jakarta time.
     */
    public function startsAt(): CarbonImmutable
    {
        $meetingDate = $this->meeting_date instanceof \DateTimeInterface
            ? $this->meeting_date->format('Y-m-d')
            : (string) $this->meeting_date;
        $meetingTime = trim((string) $this->meeting_time);

        if (preg_match('/^\d{2}:\d{2}$/', $meetingTime)) {
            $meetingTime .= ':00';
        }

        return CarbonImmutable::parse(trim($meetingDate.' '.$meetingTime), 'Asia/Jakarta');
    }

    /**
     * Staff may only join once the scheduled start time has been reached.
     */
    public function isJoinable(?CarbonImmutable $now = null): bool
    {
        return ($now ?? CarbonImmutable::now('Asia/Jakarta'))->greaterThanOrEqualTo($this->startsAt());
    }

    /**
     * Whole seconds remaining until the meeting can be joined (0 once it is joinable).
     */
    public function secondsUntilJoinable(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now('Asia/Jakarta');

        return max(0, (int) ceil($now->diffInSeconds($this->startsAt(), false)));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(HrMeetingParticipant::class, 'hr_meeting_id', 'id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(HrMeetingTask::class, 'hr_meeting_id', 'id');
    }

    public function momExports(): HasMany
    {
        return $this->hasMany(HrMeetingMomExport::class, 'hr_meeting_id', 'id');
    }
}
