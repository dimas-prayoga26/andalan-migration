<?php

namespace App\Support\Attendance;

use Illuminate\Support\Carbon;

class AttendanceWorkDurationCalculator
{
    private const REST_DEDUCTION_MINUTES = 60;

    private const REST_DEDUCTION_THRESHOLD_MINUTES = 360;

    public function netHoursBetween(Carbon $clockInTime, Carbon $clockOutTime, bool $deductRestTime = true): float
    {
        return round($this->netMinutesBetween($clockInTime, $clockOutTime, $deductRestTime) / 60, 2);
    }

    public function netMinutesBetween(Carbon $clockInTime, Carbon $clockOutTime, bool $deductRestTime = true): int
    {
        $grossMinutes = (int) $clockInTime->diffInMinutes($clockOutTime, false);

        return $this->netMinutesFromGrossMinutes($grossMinutes, $deductRestTime);
    }

    public function netMinutesBetweenTimeLabels(string $clockInValue, string $clockOutValue, bool $deductRestTime = true): ?int
    {
        $clockInTime = $this->parseTimeLabel($clockInValue);
        $clockOutTime = $this->parseTimeLabel($clockOutValue);
        if (! $clockInTime instanceof Carbon || ! $clockOutTime instanceof Carbon) {
            return null;
        }

        if ($clockOutTime->lessThan($clockInTime)) {
            $clockOutTime->addDay();
        }

        return $this->netMinutesBetween($clockInTime, $clockOutTime, $deductRestTime);
    }

    public function netMinutesFromGrossMinutes(int $grossMinutes, bool $deductRestTime = true): int
    {
        if ($grossMinutes <= 0) {
            return 0;
        }

        if ($deductRestTime && $grossMinutes >= self::REST_DEDUCTION_THRESHOLD_MINUTES) {
            return max(0, $grossMinutes - self::REST_DEDUCTION_MINUTES);
        }

        return $grossMinutes;
    }

    private function parseTimeLabel(string $timeValue): ?Carbon
    {
        $normalizedTime = trim($timeValue);
        if ($normalizedTime === '' || $normalizedTime === '-') {
            return null;
        }

        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, $normalizedTime);
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
