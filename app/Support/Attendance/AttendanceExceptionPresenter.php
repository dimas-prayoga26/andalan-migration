<?php

namespace App\Support\Attendance;

use App\Models\AttendanceException;
use Illuminate\Support\Carbon;

class AttendanceExceptionPresenter
{
    public function modalTitle(AttendanceException $attendanceException): string
    {
        return match ($this->type($attendanceException)) {
            'late_arrival' => 'Permitted Late Arrival',
            'early_departure' => 'Early Departure',
            default => 'Attendance Exception',
        };
    }

    public function introTitle(AttendanceException $attendanceException): string
    {
        return match ($this->type($attendanceException)) {
            'late_arrival' => 'Thanks for the heads-up!',
            'early_departure' => 'Wrapping up early today',
            default => '',
        };
    }

    public function introPrimary(AttendanceException $attendanceException): string
    {
        return match ($this->type($attendanceException)) {
            'late_arrival' => 'We know things happen. Travel safely, and just jump right into your tasks whenever you get settled at your desk.',
            'early_departure' => 'Before you head out, just make sure any urgent tasks are handed over or completed. Have a great rest of your day!',
            default => '',
        };
    }

    public function introSecondary(AttendanceException $attendanceException): string
    {
        return '';
    }

    public function requestTypeLabel(AttendanceException $attendanceException): string
    {
        return match ($this->type($attendanceException)) {
            'late_arrival' => 'Permitted Late Arrival',
            'early_departure' => 'Early Departure',
            default => '-',
        };
    }

    public function timeVarianceLabel(AttendanceException $attendanceException): string
    {
        $fromTime = $this->normalizeTime($attendanceException->getRawOriginal('from_time'))
            ?? $this->normalizeTime($attendanceException->from_time);
        $toTime = $this->normalizeTime($attendanceException->getRawOriginal('to_time'))
            ?? $this->normalizeTime($attendanceException->to_time);

        if ($fromTime === null || $toTime === null) {
            return '-';
        }

        $durationLabel = $this->durationLabel($attendanceException, $fromTime, $toTime);
        $fromTimeLabel = substr($fromTime, 0, 5);
        $toTimeLabel = substr($toTime, 0, 5);

        if ($durationLabel === '') {
            return $fromTimeLabel.' - '.$toTimeLabel;
        }

        if ($this->type($attendanceException) === 'late_arrival') {
            return trim($fromTimeLabel.' - '.$toTimeLabel.' (Terlambat '.$durationLabel.')');
        }

        if ($this->type($attendanceException) === 'early_departure') {
            return trim($fromTimeLabel.' - '.$toTimeLabel.' (Pulang '.$durationLabel.' Lebih Awal)');
        }

        return $fromTimeLabel.' - '.$toTimeLabel;
    }

    public function statusLabel(AttendanceException $attendanceException): string
    {
        $status = is_string($attendanceException->status) ? trim($attendanceException->status) : '';

        return $status !== '' ? str($status)->replace('_', ' ')->title()->toString() : '-';
    }

    public function statusDateLabel(AttendanceException $attendanceException): string
    {
        return $attendanceException->exception_date?->format('d M Y') ?? '-';
    }

    private function type(AttendanceException $attendanceException): string
    {
        return is_string($attendanceException->type) ? strtolower(trim($attendanceException->type)) : '';
    }

    private function normalizeTime(mixed $time): ?string
    {
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i:s');
        }

        if (! is_string($time) || trim($time) === '') {
            return null;
        }

        $normalizedTime = trim($time);
        if (preg_match('/^\d{2}:\d{2}$/', $normalizedTime) === 1) {
            return $normalizedTime.':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $normalizedTime) === 1) {
            return $normalizedTime;
        }

        return null;
    }

    private function durationLabel(AttendanceException $attendanceException, string $fromTime, string $toTime): string
    {
        $exceptionDate = $attendanceException->exception_date?->format('Y-m-d') ?? now('Asia/Jakarta')->toDateString();

        try {
            $fromDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $exceptionDate.' '.$fromTime, 'Asia/Jakarta');
            $toDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $exceptionDate.' '.$toTime, 'Asia/Jakarta');
        } catch (\Throwable) {
            return '';
        }

        $minutes = abs((int) $fromDateTime->diffInMinutes($toDateTime, false));
        if ($minutes <= 0) {
            return '';
        }

        $hoursPart = intdiv($minutes, 60);
        $minutesPart = $minutes % 60;
        $segments = [];

        if ($hoursPart > 0) {
            $segments[] = $hoursPart.' Jam';
        }

        if ($minutesPart > 0) {
            $segments[] = $minutesPart.' Menit';
        }

        return implode(' ', $segments);
    }
}
