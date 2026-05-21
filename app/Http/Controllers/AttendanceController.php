<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Support\TelegramAttendanceNotifier;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\EmployeeProfile;
use App\Models\TelegramUser;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }
        $userId = Auth::id();
        $employeeId = $authenticatedUser?->employee?->id;
        $isSuperUser = $this->isSuperUser($authenticatedUser);
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $nowJakarta = now('Asia/Jakarta');
        $publicIp = '-';
        $attendance = collect();
        if (is_string($employeeId) && trim($employeeId) !== '') {
            $attendance = Attendance::query()
                ->where('employee_id', $employeeId)
                ->get();
        }
        $showCompanyFilter = $isSuperUser;
        $showStaffPeriodFilter = $isStaffUser;
        $companies = collect();
        $staffMonthOptions = collect(range(1, 12));
        $staffYearOptions = collect();
        $defaultStaffMonth = (int) $request->integer('month', (int) $nowJakarta->month);
        $defaultStaffYear = (int) $request->integer('year', (int) $nowJakarta->year);

        if ($defaultStaffMonth < 1 || $defaultStaffMonth > 12) {
            $defaultStaffMonth = (int) $nowJakarta->month;
        }

        if ($defaultStaffYear < 2000 || $defaultStaffYear > 2100) {
            $defaultStaffYear = (int) $nowJakarta->year;
        }

        if ($isStaffUser) {
            $staffYearOptions = Attendance::query()
                ->where('employee_id', $employeeId)
                ->selectRaw('DISTINCT YEAR(`date`) as year_value')
                ->orderByDesc('year_value')
                ->pluck('year_value')
                ->filter(static fn (mixed $yearValue): bool => is_numeric($yearValue))
                ->map(static fn (mixed $yearValue): int => (int) $yearValue)
                ->values();

            if ($staffYearOptions->isEmpty()) {
                $staffYearOptions = collect([(int) $nowJakarta->year]);
            }

            if (! $staffYearOptions->contains($defaultStaffYear)) {
                $staffYearOptions = $staffYearOptions
                    ->push($defaultStaffYear)
                    ->unique()
                    ->sortDesc()
                    ->values();
            }
        }

        if ($showCompanyFilter) {
            $companies = User::query()
                ->with([
                    'employee.deployment.company:id,name',
                ])
                ->get()
                ->pluck('employee.deployment.company')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();
        }
        $izin = collect();
        $lembur = collect();
        $absensiHariIni = null;
        if (is_string($employeeId) && trim($employeeId) !== '') {
            $absensiHariIni = Attendance::query()
                ->where('date', now()->format('Y-m-d'))
                ->where('employee_id', $employeeId)
                ->first();
        }
        $todayAttendanceId = $absensiHariIni?->id;
        $todayAttendanceDistanceKm = null;
        if (is_string($todayAttendanceId) && trim($todayAttendanceId) !== '') {
            $todayAttendanceDistanceMeters = AttendanceLog::query()
                ->where('attendance_id', $todayAttendanceId)
                ->value('distance_in');

            if (is_numeric($todayAttendanceDistanceMeters)) {
                $todayAttendanceDistanceKm = round(((float) $todayAttendanceDistanceMeters) / 1000, 2);
            }
        }
        $hasCheckedInToday = ! empty($absensiHariIni?->clock_in);
        $hasCheckedOutToday = ! empty($absensiHariIni?->clock_out);
        $totalLemburJam = 0;

        $agEvent = $attendance->groupBy(function ($data) {
            return Carbon::parse($data->date)->format('Y-m-d');
        })->map(function ($items) {
            return $items->map(function ($data) {
                return [
                    'check_in' => $data->clock_in?->format('H:i'),
                    'check_out' => $data->clock_out?->format('H:i'),
                    'status' => $data->status,
                ];
            })->values();
        });

        $officeLocation = $this->resolveOfficeContext($userId);

        $clientIpAddress = $this->resolveClientIpAddress($request);
        $ipdataData = $this->fetchIpdata($clientIpAddress);
        if (! empty($ipdataData['ip'])) {
            $publicIp = (string) $ipdataData['ip'];
        }

        $allowedIpRange = is_array($officeLocation) ? ($officeLocation['ip_range'] ?? null) : null;
        $publicIpPrefix = $this->extractIpTwoOctets($publicIp);
        $allowedIpPrefix = $this->extractIpTwoOctets($allowedIpRange);
        $isIpPrefixMatch = $publicIpPrefix !== null
            && $allowedIpPrefix !== null
            && $publicIpPrefix === $allowedIpPrefix;

        return view('absensi.index', compact(
            'attendance',
            'izin',
            'lembur',
            'agEvent',
            'totalLemburJam',
            'absensiHariIni',
            'officeLocation',
            'publicIp',
            'publicIpPrefix',
            'allowedIpPrefix',
            'isIpPrefixMatch',
            'companies',
            'showCompanyFilter',
            'showStaffPeriodFilter',
            'staffMonthOptions',
            'staffYearOptions',
            'defaultStaffMonth',
            'defaultStaffYear',
            'todayAttendanceId',
            'todayAttendanceDistanceKm',
            'hasCheckedInToday',
            'hasCheckedOutToday',
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.profile');
        }
        $employeeId = $authenticatedUser?->employee?->id;
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data employee belum tersedia untuk user ini',
            ], 422);
        }

        $nowJakarta = now('Asia/Jakarta');
        $todayDate = $nowJakarta->toDateString();
        $currentTime = $nowJakarta->format('H:i:s');
        $officeContext = $this->resolveOfficeContext($userId);
        $attendanceStatus = $this->resolveAttendanceStatus($nowJakarta, $officeContext);
        $lateMinutes = $this->calculateLateMinutes($nowJakarta, $officeContext);

        if (Attendance::where('employee_id', $employeeId)
            ->whereDate('date', $todayDate)
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah absen hari ini',
            ], 422);
        }

        $attendance = Attendance::create([
            'employee_id' => $employeeId,
            'date' => $todayDate,
            'clock_in' => $currentTime,
            'clock_out' => null,
            'late_minutes' => $lateMinutes,
            'work_hours' => null,
            'status' => $attendanceStatus,
        ]);

        $clientIpAddress = $this->resolveClientIpAddress($request, $request->input('client_ip'));
        $ipdataData = $this->fetchIpdata($clientIpAddress);
        $requestLatitude = $request->input('latitude');
        $requestLongitude = $request->input('longitude');
        $hasRequestCoordinates = is_numeric($requestLatitude)
            && is_numeric($requestLongitude)
            && $this->isValidCoordinate((float) $requestLatitude, (float) $requestLongitude);
        $hasIpCoordinates = isset($ipdataData['latitude'], $ipdataData['longitude'])
            && is_numeric($ipdataData['latitude'])
            && is_numeric($ipdataData['longitude'])
            && $this->isValidCoordinate((float) $ipdataData['latitude'], (float) $ipdataData['longitude']);
        $latitude = $hasRequestCoordinates ? (float) $requestLatitude : ($hasIpCoordinates ? (float) $ipdataData['latitude'] : 0.0);
        $longitude = $hasRequestCoordinates ? (float) $requestLongitude : ($hasIpCoordinates ? (float) $ipdataData['longitude'] : 0.0);
        $ipAddress = $ipdataData['ip'] ?? $clientIpAddress ?? $request->ip();

        $distance = 0.0;
        $radiusResult = 'outside';
        $hasCoordinates = $hasRequestCoordinates || $hasIpCoordinates;
        if ($officeContext !== null && $hasCoordinates) {
            $distance = $this->calculateDistanceInMeters(
                $latitude,
                $longitude,
                $officeContext['latitude'],
                $officeContext['longitude']
            );

            $radiusResult = $distance <= $officeContext['radius_meters'] ? 'inside' : 'outside';
        }

        $locationMetadata = $hasCoordinates ? $this->reverseGeocodeCoordinates($latitude, $longitude) : [];

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'location_in' => $locationMetadata['formatted_address'] ?? null,
            'location_out' => null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_result' => $radiusResult,
            'distance_in' => round($distance, 2),
            'distance_out' => null,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'device_hash' => hash('sha256', ($request->userAgent() ?? 'unknown').'|'.$ipAddress),
            'address_village' => $locationMetadata['address_village'] ?? null,
            'address_district' => $locationMetadata['address_district'] ?? null,
            'address_regency' => $locationMetadata['address_regency'] ?? null,
            'address_city' => $locationMetadata['address_city'] ?? null,
            'address_province' => $locationMetadata['address_province'] ?? null,
            'address_postal_code' => $locationMetadata['address_postal_code'] ?? null,
            'geocoded_at' => isset($locationMetadata['geocoded_at']) ? Carbon::parse($locationMetadata['geocoded_at']) : null,
        ]);

        if ($authenticatedUser instanceof User && $this->isStaffUser($authenticatedUser)) {
            app(TelegramAttendanceNotifier::class)->notifyCheckIn($authenticatedUser, $attendance);
        }

        return response()->json([
            'success' => true,
            'message' => 'Absen berhasil disimpan',
            'attendance_id' => $attendance->id,
        ]);
    }

    public function update(Request $request, Attendance $absensi): JsonResponse
    {
        $absensi->loadMissing('employee');
        if ($absensi->employee?->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke data absen ini',
            ], 403);
        }

        $todayDate = now('Asia/Jakarta')->toDateString();

        if ($absensi->date?->format('Y-m-d') !== $todayDate) {
            return response()->json([
                'success' => false,
                'message' => 'Data absen tidak sesuai tanggal hari ini',
            ], 422);
        }

        if (empty($absensi->clock_in)) {
            return response()->json([
                'success' => false,
                'message' => 'Data absen masuk tidak ditemukan',
            ], 422);
        }

        if (! empty($absensi->clock_out)) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah absen pulang hari ini',
            ], 422);
        }

        $clockOutTime = now('Asia/Jakarta');
        $clockOutTimeString = $clockOutTime->format('H:i:s');
        $attendanceDate = $absensi->date?->format('Y-m-d') ?? $todayDate;
        $clockInRaw = $absensi->getRawOriginal('clock_in');
        $clockInTimeString = is_string($clockInRaw) && trim($clockInRaw) !== ''
            ? $clockInRaw
            : (string) $clockOutTimeString;
        try {
            $clockInTime = Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate.' '.$clockInTimeString, 'Asia/Jakarta');
        } catch (\Throwable) {
            $clockInTime = Carbon::parse($attendanceDate.' '.$clockInTimeString, 'Asia/Jakarta');
        }
        $workHours = $this->calculateWorkHours($clockInTime, $clockOutTime);

        $absensi->update([
            'clock_out' => $clockOutTimeString,
            'work_hours' => $workHours,
        ]);

        $officeContext = $this->resolveOfficeContext(Auth::id());
        $clientIpAddress = $this->resolveClientIpAddress($request, $request->input('client_ip'));
        $ipdataData = $this->fetchIpdata($clientIpAddress);
        $requestLatitude = $request->input('latitude');
        $requestLongitude = $request->input('longitude');
        $hasRequestCoordinates = is_numeric($requestLatitude)
            && is_numeric($requestLongitude)
            && $this->isValidCoordinate((float) $requestLatitude, (float) $requestLongitude);
        $hasIpCoordinates = isset($ipdataData['latitude'], $ipdataData['longitude'])
            && is_numeric($ipdataData['latitude'])
            && is_numeric($ipdataData['longitude'])
            && $this->isValidCoordinate((float) $ipdataData['latitude'], (float) $ipdataData['longitude']);
        $latitude = $hasRequestCoordinates ? (float) $requestLatitude : ($hasIpCoordinates ? (float) $ipdataData['latitude'] : 0.0);
        $longitude = $hasRequestCoordinates ? (float) $requestLongitude : ($hasIpCoordinates ? (float) $ipdataData['longitude'] : 0.0);
        $hasCoordinates = $hasRequestCoordinates || $hasIpCoordinates;

        $distanceOut = 0.0;
        if ($officeContext !== null && $hasCoordinates) {
            $distanceOut = $this->calculateDistanceInMeters(
                $latitude,
                $longitude,
                $officeContext['latitude'],
                $officeContext['longitude']
            );
        }

        $locationMetadata = $hasCoordinates ? $this->reverseGeocodeCoordinates($latitude, $longitude) : [];

        AttendanceLog::query()
            ->where('attendance_id', $absensi->id)
            ->update([
                'location_out' => $locationMetadata['formatted_address'] ?? null,
                'distance_out' => round($distanceOut, 2),
            ]);

        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.profile');
        }

        if ($authenticatedUser instanceof User && $this->isStaffUser($authenticatedUser)) {
            $absensi->refresh();
            app(TelegramAttendanceNotifier::class)->notifyCheckOut($authenticatedUser, $absensi);
        }

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil disimpan',
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $isSuperUser = $this->isSuperUser($authenticatedUser);
        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $userCompanyId = $authenticatedUser?->employee?->deployment?->current_company_id;
        $nowJakarta = now('Asia/Jakarta');
        $todayDate = $nowJakarta->toDateString();
        $selectedMonth = (int) $request->integer('month', (int) $nowJakarta->month);
        $selectedYear = (int) $request->integer('year', (int) $nowJakarta->year);
        $selectedCompanyId = trim((string) $request->input('company_id', ''));

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int) $nowJakarta->month;
        }

        if ($selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = (int) $nowJakarta->year;
        }

        if ($isStaffUser) {
            $staffUser = User::query()
                ->with(['employee.deployment.company:id,name'])
                ->find(Auth::id());

            if (! $staffUser) {
                return response()->json(['data' => []]);
            }
            $staffEmployeeId = $staffUser->employee?->id;
            if (! is_string($staffEmployeeId) || trim($staffEmployeeId) === '') {
                return response()->json(['data' => []]);
            }

            $staffAttendances = Attendance::query()
                ->where('employee_id', $staffEmployeeId)
                ->whereMonth('date', $selectedMonth)
                ->whereYear('date', $selectedYear)
                ->orderByDesc('date')
                ->orderByDesc('created_at')
                ->get(['id', 'date', 'status', 'clock_in', 'clock_out', 'created_at']);
            $staffProfileName = EmployeeProfile::query()
                ->where('employee_id', $staffEmployeeId)
                ->value('name');
            $staffDisplayName = is_string($staffProfileName) && trim($staffProfileName) !== ''
                ? trim($staffProfileName)
                : ((is_string($staffUser->username) && trim($staffUser->username) !== '')
                    ? trim($staffUser->username)
                    : ((is_string($staffUser->email) && trim($staffUser->email) !== '') ? trim($staffUser->email) : '-'));

            $attendanceLogsByAttendanceId = AttendanceLog::query()
                ->whereIn('attendance_id', $staffAttendances->pluck('id'))
                ->orderByDesc('created_at')
                ->get([
                    'attendance_id',
                    'location_in',
                    'location_out',
                    'latitude',
                    'longitude',
                    'distance_in',
                    'distance_out',
                    'radius_result',
                    'address_village',
                    'address_district',
                    'address_regency',
                    'address_city',
                    'address_province',
                    'address_postal_code',
                ])
                ->groupBy('attendance_id')
                ->map(fn ($attendanceLogs) => $attendanceLogs->first());
            $tableRows = $staffAttendances->map(function (Attendance $attendanceItem) use ($attendanceLogsByAttendanceId, $staffDisplayName, $staffUser): array {
                $attendanceLog = $attendanceLogsByAttendanceId->get($attendanceItem->id);

                return [
                    'attendance_id' => $attendanceItem->id,
                    'attendance_date' => $attendanceItem->date?->format('Y-m-d'),
                    'attendance_created_at' => $attendanceItem->created_at?->format('Y-m-d'),
                    'staff_name' => $staffDisplayName,
                    'company_name' => $staffUser->employee?->deployment?->company?->name,
                    'check_in' => $attendanceItem->clock_in?->format('H:i'),
                    'check_out' => $attendanceItem->clock_out?->format('H:i'),
                    'status' => $attendanceItem->status,
                    'check_in_latitude' => isset($attendanceLog?->latitude) ? (float) $attendanceLog->latitude : null,
                    'check_in_longitude' => isset($attendanceLog?->longitude) ? (float) $attendanceLog->longitude : null,
                    'distance_meters' => isset($attendanceLog?->distance_in) ? (float) $attendanceLog->distance_in : null,
                    'distance_out_meters' => isset($attendanceLog?->distance_out) ? (float) $attendanceLog->distance_out : null,
                    'radius_result' => isset($attendanceLog?->radius_result) ? (string) $attendanceLog->radius_result : null,
                    'location_in' => isset($attendanceLog?->location_in) ? (string) $attendanceLog->location_in : null,
                    'location_out' => isset($attendanceLog?->location_out) ? (string) $attendanceLog->location_out : null,
                    'formatted_address' => isset($attendanceLog?->location_in) ? (string) $attendanceLog->location_in : null,
                    'address_village' => isset($attendanceLog?->address_village) ? (string) $attendanceLog->address_village : null,
                    'address_district' => isset($attendanceLog?->address_district) ? (string) $attendanceLog->address_district : null,
                    'address_regency' => isset($attendanceLog?->address_regency) ? (string) $attendanceLog->address_regency : null,
                    'address_city' => isset($attendanceLog?->address_city) ? (string) $attendanceLog->address_city : null,
                    'address_province' => isset($attendanceLog?->address_province) ? (string) $attendanceLog->address_province : null,
                    'address_postal_code' => isset($attendanceLog?->address_postal_code) ? (string) $attendanceLog->address_postal_code : null,
                ];
            })->values();

            return response()->json([
                'data' => $tableRows,
            ]);
        }

        $tableUsersQuery = User::query()
            ->with([
                'employee.deployment.company:id,name',
                'employee:id,user_id',
            ]);

        if ($isBoardOfDirectur) {
            if ($userCompanyId) {
                $tableUsersQuery->whereHas('employee.deployment', function ($query) use ($userCompanyId): void {
                    $query->where('current_company_id', $userCompanyId);
                });
            } else {
                $tableUsersQuery->whereRaw('1 = 0');
            }
        } elseif ($isSuperUser && $selectedCompanyId !== '') {
            $tableUsersQuery->whereHas('employee.deployment', function ($query) use ($selectedCompanyId): void {
                $query->where('current_company_id', $selectedCompanyId);
            });
        }

        $tableUsers = $tableUsersQuery->get();
        $employeeIds = $tableUsers
            ->map(fn (User $user): mixed => $user->employee?->id)
            ->filter()
            ->values();
        $employeeProfileNamesByEmployeeId = EmployeeProfile::query()
            ->whereIn('employee_id', $employeeIds)
            ->orderBy('created_at')
            ->pluck('name', 'employee_id');
        $attendancesTodayByEmployeeId = Attendance::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('date', $todayDate)
            ->orderByDesc('created_at')
            ->get(['id', 'employee_id', 'date', 'clock_in', 'clock_out', 'status'])
            ->groupBy('employee_id')
            ->map(fn ($attendanceItems) => $attendanceItems->first());
        $attendanceIds = $tableUsers
            ->map(fn (User $user): mixed => $attendancesTodayByEmployeeId->get($user->employee?->id)?->id)
            ->filter()
            ->values();
        $attendanceLogsByAttendanceId = AttendanceLog::query()
            ->whereIn('attendance_id', $attendanceIds)
            ->orderByDesc('created_at')
            ->get([
                'attendance_id',
                'location_in',
                'location_out',
                'latitude',
                'longitude',
                'distance_in',
                'distance_out',
                'radius_result',
                'address_village',
                'address_district',
                'address_regency',
                'address_city',
                'address_province',
                'address_postal_code',
            ])
            ->groupBy('attendance_id')
            ->map(fn ($attendanceLogs) => $attendanceLogs->first());

        $tableRows = $tableUsers->map(function (User $user) use ($attendanceLogsByAttendanceId, $attendancesTodayByEmployeeId, $todayDate, $employeeProfileNamesByEmployeeId): array {
            $attendanceToday = $attendancesTodayByEmployeeId->get($user->employee?->id);
            $attendanceLog = $attendanceToday ? $attendanceLogsByAttendanceId->get($attendanceToday->id) : null;
            $employeeId = $user->employee?->id;
            $profileName = is_string($employeeId) ? $employeeProfileNamesByEmployeeId->get($employeeId) : null;
            $staffDisplayName = is_string($profileName) && trim($profileName) !== ''
                ? trim($profileName)
                : ((is_string($user->username) && trim($user->username) !== '')
                    ? trim($user->username)
                    : ((is_string($user->email) && trim($user->email) !== '') ? trim($user->email) : '-'));

            return [
                'attendance_id' => $attendanceToday?->id,
                'attendance_date' => $attendanceToday?->date?->format('Y-m-d') ?? $todayDate,
                'staff_name' => $staffDisplayName,
                'company_name' => $user->employee?->deployment?->company?->name,
                'check_in' => $attendanceToday?->clock_in?->format('H:i'),
                'check_out' => $attendanceToday?->clock_out?->format('H:i'),
                'status' => $attendanceToday?->status,
                'check_in_latitude' => isset($attendanceLog?->latitude) ? (float) $attendanceLog->latitude : null,
                'check_in_longitude' => isset($attendanceLog?->longitude) ? (float) $attendanceLog->longitude : null,
                'distance_meters' => isset($attendanceLog?->distance_in) ? (float) $attendanceLog->distance_in : null,
                'distance_out_meters' => isset($attendanceLog?->distance_out) ? (float) $attendanceLog->distance_out : null,
                'radius_result' => isset($attendanceLog?->radius_result) ? (string) $attendanceLog->radius_result : null,
                'location_in' => isset($attendanceLog?->location_in) ? (string) $attendanceLog->location_in : null,
                'location_out' => isset($attendanceLog?->location_out) ? (string) $attendanceLog->location_out : null,
                'formatted_address' => isset($attendanceLog?->location_in) ? (string) $attendanceLog->location_in : null,
                'address_village' => isset($attendanceLog?->address_village) ? (string) $attendanceLog->address_village : null,
                'address_district' => isset($attendanceLog?->address_district) ? (string) $attendanceLog->address_district : null,
                'address_regency' => isset($attendanceLog?->address_regency) ? (string) $attendanceLog->address_regency : null,
                'address_city' => isset($attendanceLog?->address_city) ? (string) $attendanceLog->address_city : null,
                'address_province' => isset($attendanceLog?->address_province) ? (string) $attendanceLog->address_province : null,
                'address_postal_code' => isset($attendanceLog?->address_postal_code) ? (string) $attendanceLog->address_postal_code : null,
            ];
        })->values();

        return response()->json([
            'data' => $tableRows,
        ]);
    }

    public function currentIp(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $publicIp = '-';
        $officeLocation = $this->resolveOfficeContext($userId);
        $clientIpAddress = $this->resolveClientIpAddress($request, $request->query('client_ip'));
        $ipdataData = $this->fetchIpdata($clientIpAddress);

        if (! empty($ipdataData['ip'])) {
            $publicIp = (string) $ipdataData['ip'];
        }

        $allowedIpRange = is_array($officeLocation) ? ($officeLocation['ip_range'] ?? null) : null;
        $publicIpPrefix = $this->extractIpTwoOctets($publicIp);
        $allowedIpPrefix = $this->extractIpTwoOctets($allowedIpRange);
        $isIpPrefixMatch = $publicIpPrefix !== null
            && $allowedIpPrefix !== null
            && $publicIpPrefix === $allowedIpPrefix;

        return response()->json([
            'ip' => $publicIp,
            'public_ip_prefix' => $publicIpPrefix,
            'allowed_ip_prefix' => $allowedIpPrefix,
            'is_ip_prefix_match' => $isIpPrefixMatch,
        ]);
    }

    public function verifyTelegramUsername(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if (! $authenticatedUser instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi.',
            ], 401);
        }

        $authenticatedUser->loadMissing('employee.telegramUser');
        $employeeId = $authenticatedUser?->employee?->id;
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Employee untuk user ini belum tersedia.',
            ], 422);
        }

        if ($authenticatedUser->employee?->telegramUser instanceof TelegramUser) {
            return response()->json([
                'success' => true,
                'message' => 'Verifikasi Telegram berhasil.',
            ]);
        }

        $applicationUsername = is_string($authenticatedUser->username) ? trim($authenticatedUser->username) : '';
        if ($applicationUsername === '') {
            return response()->json([
                'success' => false,
                'message' => 'Username akun aplikasi belum tersedia.',
            ], 422);
        }

        $botToken = config('services.telegram.bot_token');
        if (! is_string($botToken) || trim($botToken) === '') {
            return response()->json([
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN belum diset.',
            ], 422);
        }

        try {
            $telegramResponse = Http::connectTimeout(8)
                ->timeout(20)
                ->retry(3, 500)
                ->withOptions([
                    'version' => 1.1,
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    ],
                ])
                ->acceptJson()
                ->get("https://api.telegram.org/bot{$botToken}/getUpdates");

            if (! $telegramResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data update Telegram.',
                ], 422);
            }

            $payload = $telegramResponse->json();
            if (! is_array($payload)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payload Telegram tidak valid.',
                ], 422);
            }

            $updates = isset($payload['result']) && is_array($payload['result']) ? $payload['result'] : [];
            $matchedFrom = null;
            $matchedChat = null;

            foreach ($updates as $update) {
                if (! is_array($update)) {
                    continue;
                }

                $message = $update['message'] ?? $update['edited_message'] ?? null;
                if (! is_array($message)) {
                    continue;
                }

                $from = $message['from'] ?? null;
                if (! is_array($from)) {
                    continue;
                }

                $telegramUsername = isset($from['username']) && is_string($from['username']) ? trim($from['username']) : '';
                if ($telegramUsername === '') {
                    continue;
                }

                if (mb_strtolower($telegramUsername) !== mb_strtolower($applicationUsername)) {
                    continue;
                }

                $matchedFrom = $from;
                $matchedChat = isset($message['chat']) && is_array($message['chat']) ? $message['chat'] : null;
            }

            if (! is_array($matchedFrom)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username Telegram belum ditemukan. Silakan kirim /start ke bot terlebih dahulu.',
                ], 422);
            }

            TelegramUser::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'chat_id' => is_array($matchedChat) && isset($matchedChat['id']) ? (string) $matchedChat['id'] : null,
                    'first_name' => isset($matchedFrom['first_name']) && is_string($matchedFrom['first_name']) ? $matchedFrom['first_name'] : null,
                    'last_name' => isset($matchedFrom['last_name']) && is_string($matchedFrom['last_name']) ? $matchedFrom['last_name'] : null,
                    'username' => isset($matchedFrom['username']) && is_string($matchedFrom['username']) ? $matchedFrom['username'] : null,
                    'language_code' => isset($matchedFrom['language_code']) && is_string($matchedFrom['language_code']) ? $matchedFrom['language_code'] : null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi Telegram berhasil.',
            ]);
        } catch (\Throwable $throwable) {
            $errorMessage = 'Terjadi kesalahan saat verifikasi Telegram.';
            if (str_contains($throwable->getMessage(), 'cURL error 35')) {
                $errorMessage = 'Koneksi ke Telegram terputus. Silakan coba lagi.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchIpdata(?string $ipAddress = null): array
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
     *     formatted_address:string|null,
     *     address_village:string|null,
     *     address_district:string|null,
     *     address_regency:string|null,
     *     address_city:string|null,
     *     address_province:string|null,
     *     address_postal_code:string|null,
     *     geocoded_at:string|null
     * }
     */
    private function reverseGeocodeCoordinates(float $latitude, float $longitude): array
    {
        $googleMapsApiKey = config('services.google_maps.api_key');

        if (empty($googleMapsApiKey) || ! $this->isValidCoordinate($latitude, $longitude)) {
            return [
                'formatted_address' => null,
                'address_village' => null,
                'address_district' => null,
                'address_regency' => null,
                'address_city' => null,
                'address_province' => null,
                'address_postal_code' => null,
                'geocoded_at' => null,
            ];
        }

        try {
            $response = Http::timeout(7)
                ->acceptJson()
                ->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'latlng' => $latitude.','.$longitude,
                    'language' => 'id',
                    'key' => $googleMapsApiKey,
                ]);

            if (! $response->successful()) {
                return [];
            }

            $payload = $response->json();
            if (! is_array($payload)) {
                return [];
            }

            $results = $payload['results'] ?? null;
            if (! is_array($results) || ! isset($results[0]) || ! is_array($results[0])) {
                return [];
            }

            $primaryResult = $results[0];
            $components = isset($primaryResult['address_components']) && is_array($primaryResult['address_components'])
                ? $primaryResult['address_components']
                : [];
            $addressComponents = $this->parseAddressComponents($components);

            return [
                'formatted_address' => isset($primaryResult['formatted_address']) && is_string($primaryResult['formatted_address'])
                    ? $primaryResult['formatted_address']
                    : null,
                'address_village' => $addressComponents['address_village'],
                'address_district' => $addressComponents['address_district'],
                'address_regency' => $addressComponents['address_regency'],
                'address_city' => $addressComponents['address_city'],
                'address_province' => $addressComponents['address_province'],
                'address_postal_code' => $addressComponents['address_postal_code'],
                'geocoded_at' => now()->toDateTimeString(),
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  array<int, mixed>  $components
     * @return array{
     *     address_village:string|null,
     *     address_district:string|null,
     *     address_regency:string|null,
     *     address_city:string|null,
     *     address_province:string|null,
     *     address_postal_code:string|null
     * }
     */
    private function parseAddressComponents(array $components): array
    {
        $resolvedComponents = [
            'address_village' => null,
            'address_district' => null,
            'address_regency' => null,
            'address_city' => null,
            'address_province' => null,
            'address_postal_code' => null,
        ];

        foreach ($components as $component) {
            if (! is_array($component)) {
                continue;
            }

            $longName = isset($component['long_name']) && is_string($component['long_name'])
                ? trim($component['long_name'])
                : null;
            $types = isset($component['types']) && is_array($component['types'])
                ? $component['types']
                : [];

            if ($longName === null || $longName === '') {
                continue;
            }

            if (in_array('administrative_area_level_4', $types, true) || in_array('sublocality_level_1', $types, true)) {
                $resolvedComponents['address_village'] ??= $longName;
            }

            if (in_array('administrative_area_level_3', $types, true) || in_array('sublocality', $types, true)) {
                $resolvedComponents['address_district'] ??= $longName;
            }

            if (in_array('administrative_area_level_2', $types, true)) {
                $resolvedComponents['address_regency'] ??= $longName;
            }

            if (in_array('administrative_area_level_1', $types, true)) {
                $resolvedComponents['address_province'] ??= $longName;
            }

            if (in_array('locality', $types, true)) {
                $resolvedComponents['address_city'] ??= $longName;
            }

            if (in_array('postal_code', $types, true)) {
                $resolvedComponents['address_postal_code'] ??= $longName;
            }
        }

        if ($resolvedComponents['address_city'] === null) {
            $resolvedComponents['address_city'] = $resolvedComponents['address_regency'];
        }

        return $resolvedComponents;
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
    private function resolveOfficeContext(int|string|null $userId): ?array
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

    /**
     * @param  array<string, mixed>|null  $officeContext
     */
    private function resolveAttendanceStatus(Carbon $attendanceTime, ?array $officeContext): string
    {
        $officeStartTime = is_array($officeContext) && isset($officeContext['office_start_time']) && is_string($officeContext['office_start_time'])
            ? $officeContext['office_start_time']
            : '08:00:00';

        $officeStartDateTime = $attendanceTime->copy();
        try {
            $officeStartDateTime->setTimeFromTimeString($officeStartTime);
        } catch (\Throwable) {
            $officeStartDateTime->setTime(8, 0, 0);
        }

        if ($attendanceTime->greaterThan($officeStartDateTime)) {
            return 'Terlambat';
        }

        return 'Masuk';
    }

    /**
     * @param  array<string, mixed>|null  $officeContext
     */
    private function calculateLateMinutes(Carbon $attendanceTime, ?array $officeContext): int
    {
        $officeStartTime = is_array($officeContext) && isset($officeContext['office_start_time']) && is_string($officeContext['office_start_time'])
            ? $officeContext['office_start_time']
            : '08:00:00';

        $officeStartDateTime = $attendanceTime->copy();
        try {
            $officeStartDateTime->setTimeFromTimeString($officeStartTime);
        } catch (\Throwable) {
            $officeStartDateTime->setTime(8, 0, 0);
        }

        if ($attendanceTime->lessThanOrEqualTo($officeStartDateTime)) {
            return 0;
        }

        return max(0, (int) $officeStartDateTime->diffInMinutes($attendanceTime, true));
    }

    private function calculateWorkHours(Carbon $clockInTime, Carbon $clockOutTime): float
    {
        $workedMinutes = (int) $clockInTime->diffInMinutes($clockOutTime, false);

        if ($workedMinutes < 0) {
            return 0.0;
        }

        return round($workedMinutes / 60, 2);
    }

    private function extractIpTwoOctets(?string $ipValue): ?string
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

    private function calculateDistanceInMeters(float $startLat, float $startLng, float $endLat, float $endLng): float
    {
        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($endLat - $startLat);
        $longitudeDelta = deg2rad($endLng - $startLng);

        $a = sin($latitudeDelta / 2) * sin($latitudeDelta / 2)
            + cos(deg2rad($startLat)) * cos(deg2rad($endLat))
            * sin($longitudeDelta / 2) * sin($longitudeDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function isValidCoordinate(float $latitude, float $longitude): bool
    {
        return $latitude >= -90
            && $latitude <= 90
            && $longitude >= -180
            && $longitude <= 180
            && ! (abs($latitude) < 0.000001 && abs($longitude) < 0.000001);
    }

    private function isSuperUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('superuser');
    }

    private function isBoardOfDirectur(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('board of directur')
            || $normalizedRoleNames->contains('board of directors');
    }

    private function isStaffUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('staff');
    }

    private function resolveClientIpAddress(Request $request, mixed $preferredIpAddress = null): ?string
    {
        if (is_string($preferredIpAddress) && filter_var($preferredIpAddress, FILTER_VALIDATE_IP)) {
            return $preferredIpAddress;
        }

        $requestIpAddress = $request->ip();
        if (is_string($requestIpAddress) && filter_var($requestIpAddress, FILTER_VALIDATE_IP)) {
            return $requestIpAddress;
        }

        return null;
    }
}
