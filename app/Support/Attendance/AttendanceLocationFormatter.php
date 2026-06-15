<?php

namespace App\Support\Attendance;

use App\Models\AttendanceLog;

class AttendanceLocationFormatter
{
    public function name(?AttendanceLog $attendanceLog): string
    {
        $locationParts = $this->parts($attendanceLog);
        if ($locationParts !== []) {
            return $locationParts[0];
        }

        if ($this->coordinatesLabel($attendanceLog) !== null) {
            return 'Koordinat Absensi';
        }

        return 'Location not available';
    }

    public function address(?AttendanceLog $attendanceLog): string
    {
        $locationParts = $this->parts($attendanceLog);
        if ($locationParts !== []) {
            return implode(', ', $locationParts);
        }

        $coordinatesLabel = $this->coordinatesLabel($attendanceLog);
        if ($coordinatesLabel !== null) {
            return $coordinatesLabel;
        }

        return $this->name($attendanceLog);
    }

    private function coordinatesLabel(?AttendanceLog $attendanceLog): ?string
    {
        if (! isset($attendanceLog?->latitude, $attendanceLog?->longitude)) {
            return null;
        }

        $latitude = (float) $attendanceLog->latitude;
        $longitude = (float) $attendanceLog->longitude;

        if (! $this->isValidCoordinate($latitude, $longitude)) {
            return null;
        }

        return number_format($latitude, 7, '.', '').', '.number_format($longitude, 7, '.', '');
    }

    /**
     * @return list<string>
     */
    private function parts(?AttendanceLog $attendanceLog): array
    {
        $location = is_string($attendanceLog?->location) ? trim($attendanceLog->location) : '';
        if ($location === '' || str_starts_with(strtolower($location), 'https://www.google.com/maps')) {
            return [];
        }

        return collect(explode(',', $location))
            ->map(static function (string $locationPart): string {
                $cleanLocationPart = preg_replace('/\b\d{5}(?:-\d{4})?\b/', '', $locationPart) ?? $locationPart;
                $cleanLocationPart = preg_replace('/\bindonesia\b/i', '', $cleanLocationPart) ?? $cleanLocationPart;

                return trim($cleanLocationPart, " \t\n\r\0\x0B,.-");
            })
            ->filter(static fn (string $locationPart): bool => $locationPart !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function isValidCoordinate(float $latitude, float $longitude): bool
    {
        return $latitude >= -90
            && $latitude <= 90
            && $longitude >= -180
            && $longitude <= 180
            && ! (abs($latitude) < 0.000001 && abs($longitude) < 0.000001);
    }
}
