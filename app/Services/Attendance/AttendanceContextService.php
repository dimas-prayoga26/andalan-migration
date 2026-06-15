<?php

namespace App\Services\Attendance;

use App\Models\User;
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
     *     name:string|null,
     *     address:string|null,
     *     latitude:float,
     *     longitude:float,
     *     radius_meters:int,
     *     ip_range:string|null,
     *     office_start_time:string,
     *     office_end_time:string
     * }|null
     */
    public function resolveOfficeContext(int|string|null $userId): ?array
    {
        if (! is_string($userId) && ! is_int($userId)) {
            return null;
        }

        $currentUser = User::query()
            ->with([
                'employee.deployment.company:id,name,address,latitude,longitude',
                'employee.deployment.company.activeAttendanceRule' => static function ($query): void {
                    $query->select([
                        'rules_of_attendaces.id',
                        'rules_of_attendaces.companies_id',
                        'rules_of_attendaces.radius',
                        'rules_of_attendaces.ip_range',
                        'rules_of_attendaces.office_start_time',
                        'rules_of_attendaces.office_end_time',
                    ]);
                },
            ])
            ->find($userId);

        $officeCompany = $currentUser?->employee?->deployment?->company;
        if (! $officeCompany || $officeCompany->latitude === null || $officeCompany->longitude === null) {
            return null;
        }

        $attendanceRule = $officeCompany->activeAttendanceRule;

        return [
            'name' => $officeCompany->name,
            'address' => $officeCompany->address,
            'latitude' => (float) $officeCompany->latitude,
            'longitude' => (float) $officeCompany->longitude,
            'radius_meters' => (int) ($attendanceRule->radius ?? 10),
            'ip_range' => isset($attendanceRule?->ip_range) ? (string) $attendanceRule->ip_range : null,
            'office_start_time' => isset($attendanceRule?->office_start_time) && is_string($attendanceRule->office_start_time)
                ? $attendanceRule->office_start_time
                : '08:00:00',
            'office_end_time' => isset($attendanceRule?->office_end_time) && is_string($attendanceRule->office_end_time)
                ? $attendanceRule->office_end_time
                : '17:00:00',
        ];
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
