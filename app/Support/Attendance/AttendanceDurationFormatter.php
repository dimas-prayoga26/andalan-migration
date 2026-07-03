<?php

namespace App\Support\Attendance;

final class AttendanceDurationFormatter
{
    public function lateLabel(int $totalMinutes): string
    {
        if ($totalMinutes <= 0) {
            return 'Late Arrival';
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $segments = [];

        if ($hours > 0) {
            $segments[] = $hours.' '.($hours === 1 ? 'Hour' : 'Hours');
        }

        if ($minutes > 0) {
            $segments[] = $minutes.' '.($minutes === 1 ? 'Minute' : 'Minutes');
        }

        return 'Late '.implode(' ', $segments);
    }
}
