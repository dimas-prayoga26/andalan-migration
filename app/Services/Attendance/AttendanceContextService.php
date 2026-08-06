<?php

namespace App\Services\Attendance;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class AttendanceContextService
{
    /**
     * @return array<string, mixed>
     */
    public function fetchIpdata(?string $ipAddress = null): array
    {
        $ipdataApiKey = config('services.ipdata.api_key');
        if (empty($ipdataApiKey)) {
            return [];
        }

        try {
            $endpoint = 'https://api.ipdata.co';
            if ($ipAddress) {
                $endpoint .= '/'.rawurlencode($ipAddress);
            }

            $ipdataResponse = Http::timeout(7)
                ->acceptJson()
                ->get($endpoint, [
                    'api-key' => $ipdataApiKey,
                ]);

            if (! $ipdataResponse->successful()) {
                return [];
            }

            $ipdataData = $ipdataResponse->json();

            return is_array($ipdataData) ? $ipdataData : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array{
     *     id:string|null,
     *     name:string|null,
     *     address:string|null,
     *     latitude:float,
     *     longitude:float,
     *     radius_meters:int,
     *     ip_range:string|null,
     *     office_start_time:string,
     *     office_end_time:string,
     *     office_reset_time:string
     * }|null
     */
    public function resolveOfficeContext(int|string|null $userId): ?array
    {
        if (! is_string($userId) && ! is_int($userId)) {
            return null;
        }

        $currentUser = User::query()
            ->with([
                'employee.deployment.officeLocation:id,name,address,latitude,longitude,is_active',
                'employee.deployment.officeLocation.activeAttendanceRule' => static function ($query): void {
                    $query->select([
                        'rules_of_attendaces.id',
                        'rules_of_attendaces.office_location_id',
                        'rules_of_attendaces.radius',
                        'rules_of_attendaces.ip_range',
                        'rules_of_attendaces.office_start_time',
                        'rules_of_attendaces.office_end_time',
                        'rules_of_attendaces.office_reset_time',
                    ]);
                },
            ])
            ->find($userId);

        $deployment = $currentUser?->employee?->deployment;
        $officeLocation = $deployment?->officeLocation;
        $hasOfficeLocationCoordinates = $officeLocation
            && $officeLocation->is_active !== false
            && $officeLocation->latitude !== null
            && $officeLocation->longitude !== null;

        if (! $hasOfficeLocationCoordinates) {
            return null;
        }

        $attendanceRule = $officeLocation->activeAttendanceRule;

        return [
            'id' => $officeLocation->id,
            'name' => $officeLocation->name,
            'address' => $officeLocation->address,
            'latitude' => (float) $officeLocation->latitude,
            'longitude' => (float) $officeLocation->longitude,
            'radius_meters' => (int) ($attendanceRule->radius ?? 10),
            'ip_range' => isset($attendanceRule?->ip_range) ? (string) $attendanceRule->ip_range : null,
            'office_start_time' => isset($attendanceRule?->office_start_time) && is_string($attendanceRule->office_start_time)
                ? $attendanceRule->office_start_time
                : '08:00:00',
            'office_end_time' => isset($attendanceRule?->office_end_time) && is_string($attendanceRule->office_end_time)
                ? $attendanceRule->office_end_time
                : '17:00:00',
            'office_reset_time' => isset($attendanceRule?->office_reset_time) && is_string($attendanceRule->office_reset_time)
                ? $attendanceRule->office_reset_time
                : '00:00:00',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $officeContext
     */
    public function attendanceDateFor(Carbon $dateTime, ?array $officeContext): string
    {
        $officeResetTime = $this->officeContextTime($officeContext, 'office_reset_time', '00:00:00');
        $resetDateTime = $dateTime->copy();

        try {
            $resetDateTime->setTimeFromTimeString($officeResetTime);
        } catch (\Throwable) {
            $resetDateTime->setTime(0, 0, 0);
        }

        if ($dateTime->lessThan($resetDateTime)) {
            return $dateTime->copy()->subDay()->toDateString();
        }

        return $dateTime->toDateString();
    }

    /**
     * @param  array<string, mixed>|null  $officeContext
     */
    public function isClockOutAllowedAt(Carbon $dateTime, string $attendanceDate, ?array $officeContext): bool
    {
        return $dateTime->greaterThanOrEqualTo($this->clockOutAvailableAt($attendanceDate, $officeContext));
    }

    /**
     * @param  array<string, mixed>|null  $officeContext
     */
    public function clockOutAvailableAt(string $attendanceDate, ?array $officeContext): Carbon
    {
        $officeEndTime = $this->officeContextTime($officeContext, 'office_end_time', '17:00:00');

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate.' '.$officeEndTime, 'Asia/Jakarta');
        } catch (\Throwable) {
            return Carbon::parse($attendanceDate.' 17:00:00', 'Asia/Jakarta');
        }
    }

    /**
     * @param  array<string, mixed>|null  $officeContext
     */
    private function officeContextTime(?array $officeContext, string $key, string $fallback): string
    {
        if (is_array($officeContext) && isset($officeContext[$key]) && is_string($officeContext[$key])) {
            $timeValue = trim($officeContext[$key]);

            if (preg_match('/^\d{2}:\d{2}(?::\d{2})?$/', $timeValue) === 1) {
                return strlen($timeValue) === 5 ? $timeValue.':00' : $timeValue;
            }
        }

        return $fallback;
    }

    public function resolveClientIpAddress(mixed $preferredIpAddress = null, mixed $requestIpAddress = null): ?string
    {
        if (is_string($preferredIpAddress) && filter_var($preferredIpAddress, FILTER_VALIDATE_IP)) {
            return $preferredIpAddress;
        }

        if (is_string($requestIpAddress) && filter_var($requestIpAddress, FILTER_VALIDATE_IP)) {
            return $requestIpAddress;
        }

        return null;
    }

    public function extractIpTwoOctets(?string $ipValue): ?string
    {
        if ($ipValue === null) {
            return null;
        }

        $matches = [];
        if (preg_match('/(\d{1,3})\.(\d{1,3})/', $ipValue, $matches) !== 1) {
            return null;
        }

        $firstOctet = (int) $matches[1];
        $secondOctet = (int) $matches[2];

        if ($firstOctet > 255 || $secondOctet > 255) {
            return null;
        }

        return $firstOctet.'.'.$secondOctet;
    }
}
