<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Attendance\AttendanceMutationService;
use App\Services\Attendance\AttendanceTodayStateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private AttendanceTodayStateService $attendanceTodayStateService,
        private AttendanceMutationService $attendanceMutationService
    ) {}

    public function index(Request $request): View
    {
        $authenticatedUser = Auth::user();
        $attendanceTodayState = $this->attendanceTodayStateService->getTodayStateForUser(
            $authenticatedUser instanceof User ? $authenticatedUser : null
        );

        $userId = Auth::id();
        $officeLocation = $this->attendanceMutationService->resolveOfficeContext($userId);
        $clientIpAddress = $this->attendanceMutationService->resolveClientIpAddress($request);
        $ipdataData = $this->attendanceMutationService->fetchIpdata($clientIpAddress);
        $publicIp = '-';
        if (! empty($ipdataData['ip'])) {
            $publicIp = (string) $ipdataData['ip'];
        }

        $allowedIpRange = is_array($officeLocation) ? ($officeLocation['ip_range'] ?? null) : null;
        $publicIpPrefix = $this->attendanceMutationService->extractIpTwoOctets($publicIp);
        $allowedIpPrefix = $this->attendanceMutationService->extractIpTwoOctets($allowedIpRange);
        $isIpPrefixMatch = $publicIpPrefix !== null
            && $allowedIpPrefix !== null
            && $publicIpPrefix === $allowedIpPrefix;

        return view('dashboard', [
            'absensiHariIni' => $attendanceTodayState['absensiHariIni'],
            'todayAttendanceId' => $attendanceTodayState['todayAttendanceId'],
            'todayAttendanceDistanceKm' => $attendanceTodayState['todayAttendanceDistanceKm'],
            'todayAttendanceDistanceOutKm' => $attendanceTodayState['todayAttendanceDistanceOutKm'],
            'hasEarlyDepartureExceptionToday' => $attendanceTodayState['hasEarlyDepartureExceptionToday'],
            'hasCheckedInToday' => $attendanceTodayState['hasCheckedInToday'],
            'hasCheckedOutToday' => $attendanceTodayState['hasCheckedOutToday'],
            'officeLocation' => $officeLocation,
            'publicIp' => $publicIp,
            'publicIpPrefix' => $publicIpPrefix,
            'allowedIpPrefix' => $allowedIpPrefix,
            'isIpPrefixMatch' => $isIpPrefixMatch,
        ]);
    }
}
