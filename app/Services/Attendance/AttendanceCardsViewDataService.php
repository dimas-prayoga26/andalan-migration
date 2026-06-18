<?php

namespace App\Services\Attendance;

use App\Models\AttendanceException;
use App\Models\User;
use Illuminate\Support\Carbon;

class AttendanceCardsViewDataService
{
    public function __construct(
        private AttendanceTodayStateService $attendanceTodayStateService,
        private AttendanceContextService $attendanceContextService
    ) {}

    /**
     * @return array{
     *   employeeId:?string,
     *   todayJakartaDate:string,
     *   todayAttendance:mixed,
     *   todayAttendanceId:?string,
     *   todayAttendanceDistanceKm:?float,
     *   todayAttendanceDistanceOutKm:?float,
     *   todayAttendanceExceptionTimeRange:string,
     *   todayAttendanceExceptionVariance:string,
     *   hasAttendanceExceptionToday:bool,
     *   hasEarlyDepartureExceptionToday:bool,
     *   hasCheckedInToday:bool,
     *   hasCheckedOutToday:bool,
     *   officeLocation:?array<string,mixed>,
     *   publicIp:string,
     *   publicIpPrefix:?string,
     *   allowedIpPrefix:?string,
     *   isIpPrefixMatch:bool
     * }
     */
    public function build(
        ?User $authenticatedUser,
        int|string|null $authenticatedUserId,
        mixed $preferredIpAddress = null,
        mixed $requestIpAddress = null
    ): array {
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $todayAttendanceState = $this->attendanceTodayStateService->getTodayStateForUser($authenticatedUser);
        $employeeId = $todayAttendanceState['employeeId'];
        $todayJakartaDate = $todayAttendanceState['todayJakartaDate'];

        $todayAttendanceExceptionTimeRange = '--:-- - --:--';
        $todayAttendanceExceptionVariance = '--.--';
        if (is_string($employeeId) && trim($employeeId) !== '') {
            $todayAttendanceException = AttendanceException::query()
                ->where('employee_id', $employeeId)
                ->whereDate('exception_date', $todayJakartaDate)
                ->latest('created_at')
                ->first(['exception_date', 'from_time', 'to_time']);

            if ($todayAttendanceException instanceof AttendanceException) {
                $fromTimeRaw = $todayAttendanceException->getRawOriginal('from_time');
                $toTimeRaw = $todayAttendanceException->getRawOriginal('to_time');
                $formattedFromTime = '--:--';
                $formattedToTime = '--:--';

                if (is_string($fromTimeRaw) && trim($fromTimeRaw) !== '') {
                    try {
                        $formattedFromTime = Carbon::createFromFormat('H:i:s', $fromTimeRaw, 'Asia/Jakarta')->format('H:i');
                    } catch (\Throwable) {
                        $formattedFromTime = '--:--';
                    }
                }

                if (is_string($toTimeRaw) && trim($toTimeRaw) !== '') {
                    try {
                        $formattedToTime = Carbon::createFromFormat('H:i:s', $toTimeRaw, 'Asia/Jakarta')->format('H:i');
                    } catch (\Throwable) {
                        $formattedToTime = '--:--';
                    }
                }

                $todayAttendanceExceptionTimeRange = $formattedFromTime.' - '.$formattedToTime;

                if (
                    is_string($fromTimeRaw)
                    && trim($fromTimeRaw) !== ''
                    && is_string($toTimeRaw)
                    && trim($toTimeRaw) !== ''
                ) {
                    try {
                        $exceptionDate = $todayAttendanceException->exception_date?->format('Y-m-d') ?? $todayJakartaDate;
                        $fromDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $exceptionDate.' '.$fromTimeRaw, 'Asia/Jakarta');
                        $toDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $exceptionDate.' '.$toTimeRaw, 'Asia/Jakarta');
                        $varianceHours = round(abs((int) $fromDateTime->diffInMinutes($toDateTime, false)) / 60, 2);
                        $todayAttendanceExceptionVariance = number_format($varianceHours, 2, '.', '');
                    } catch (\Throwable) {
                        $todayAttendanceExceptionVariance = '--.--';
                    }
                }
            }
        }

        $officeLocation = $this->attendanceContextService->resolveOfficeContext($authenticatedUserId);
        $publicIp = '-';
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
            'employeeId' => $employeeId,
            'todayJakartaDate' => $todayJakartaDate,
            'todayAttendance' => $todayAttendanceState['todayAttendance'],
            'todayAttendanceId' => $todayAttendanceState['todayAttendanceId'],
            'todayAttendanceDistanceKm' => $todayAttendanceState['todayAttendanceDistanceKm'],
            'todayAttendanceDistanceOutKm' => $todayAttendanceState['todayAttendanceDistanceOutKm'],
            'todayAttendanceExceptionTimeRange' => $todayAttendanceExceptionTimeRange,
            'todayAttendanceExceptionVariance' => $todayAttendanceExceptionVariance,
            'hasAttendanceExceptionToday' => $todayAttendanceState['hasAttendanceExceptionToday'],
            'hasEarlyDepartureExceptionToday' => $todayAttendanceState['hasEarlyDepartureExceptionToday'],
            'hasCheckedInToday' => $todayAttendanceState['hasCheckedInToday'],
            'hasCheckedOutToday' => $todayAttendanceState['hasCheckedOutToday'],
            'officeLocation' => $officeLocation,
            'publicIp' => $publicIp,
            'publicIpPrefix' => $publicIpPrefix,
            'allowedIpPrefix' => $allowedIpPrefix,
            'isIpPrefixMatch' => $isIpPrefixMatch,
        ];
    }
}
