<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AttendanceOvertime extends Model
{
    use GeneratesCustomSequenceUuid;

    protected $table = 'overtimes';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $overtime): void {
            if (! is_string($overtime->id) || trim($overtime->id) === '') {
                $overtime->id = static::generateCustomSequenceUuid('id');
            }

            if (! is_string($overtime->record_number) || trim($overtime->record_number) === '') {
                $overtime->record_number = static::generateRecordNumber($overtime->overtime_date);
            }
        });
    }

    protected static function generateRecordNumber(mixed $overtimeDate): string
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $date = $overtimeDate instanceof Carbon
            ? $overtimeDate->copy()
            : Carbon::parse($overtimeDate ?: now($timezone), $timezone);
        $prefix = 'OVT-'.$date->format('ym').'-';
        $sequencePartLength = 4;
        $maxSequence = (10 ** $sequencePartLength) - 1;

        return Cache::lock(static::class.':record_number:'.$prefix, 5)->block(5, function () use ($prefix, $sequencePartLength, $maxSequence): string {
            $latestRecordNumber = (string) static::query()
                ->where('record_number', 'like', $prefix.'%')
                ->orderByDesc('record_number')
                ->value('record_number');
            $nextSequence = 1;

            if (preg_match('/^'.preg_quote($prefix, '/').'(\d{'.$sequencePartLength.'})$/', $latestRecordNumber, $matches) === 1) {
                $nextSequence = (int) $matches[1] + 1;
            }

            while ($nextSequence <= $maxSequence) {
                $recordNumber = $prefix.str_pad((string) $nextSequence, $sequencePartLength, '0', STR_PAD_LEFT);

                if (! static::query()->where('record_number', $recordNumber)->exists()) {
                    return $recordNumber;
                }

                $nextSequence++;
            }

            throw new \RuntimeException("Overtime record number sequence limit reached for {$prefix}.");
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function projectTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'overtime_id', 'id');
    }

    public function lifecycleLogs(): HasMany
    {
        return $this->hasMany(OvertimeLifecycleLog::class, 'overtime_id', 'id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by', 'id');
    }
}
