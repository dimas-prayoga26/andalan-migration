<?php

namespace App\Services\Attendance;

class AttendanceIpVerificationService
{
    public function __construct(private AttendanceContextService $attendanceContextService) {}

    /**
     * @return array{ip:string,public_ip_prefix:?string,allowed_ip_prefix:?string,is_ip_prefix_match:bool}
     */
    public function payload(
        mixed $preferredIpAddress,
        mixed $requestIpAddress,
        int|string|null $userId
    ): array {
        $publicIp = '-';
        $officeLocation = $this->attendanceContextService->resolveOfficeContext($userId);
        $clientIpAddress = $this->attendanceContextService->resolveClientIpAddress($preferredIpAddress, $requestIpAddress);
        $ipdataData = $this->attendanceContextService->fetchIpdata($clientIpAddress);

        if (! empty($ipdataData['ip'])) {
            $publicIp = (string) $ipdataData['ip'];
        }

        $allowedIpRange = is_array($officeLocation) ? ($officeLocation['ip_range'] ?? null) : null;
        $publicIpPrefix = $this->attendanceContextService->extractIpTwoOctets($publicIp);
        $allowedIpPrefix = $this->attendanceContextService->extractIpTwoOctets($allowedIpRange);
        $isIpPrefixMatch = $publicIpPrefix !== null
            && $allowedIpPrefix !== null
            && $publicIpPrefix === $allowedIpPrefix;

        return [
            'ip' => $publicIp,
            'public_ip_prefix' => $publicIpPrefix,
            'allowed_ip_prefix' => $allowedIpPrefix,
            'is_ip_prefix_match' => $isIpPrefixMatch,
        ];
    }
}
