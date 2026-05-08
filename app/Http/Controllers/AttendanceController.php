<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $authenticatedUser = Auth::user();
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $nowJakarta = now('Asia/Jakarta');
        $publicIp = '-';
        $attendance = Attendance::with('user')->where('user_id', $userId)->get();
        $showCompanyFilter = $this->isSuperUser($authenticatedUser);
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
                ->where('user_id', $userId)
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
                    'userEmployee.company:id,name',
                ])
                ->get()
                ->pluck('userEmployee.company')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();
        }
        $izin = collect();
        $lembur = collect();
        $absensiHariIni = Attendance::where('date', now()->format('Y-m-d'))->where('user_id', $userId)->first();
        $todayAttendanceId = $absensiHariIni?->id;
        $todayAttendanceLog = null;
        if ($todayAttendanceId) {
            $todayAttendanceLog = AttendanceLog::query()
                ->where('attendance_id', $todayAttendanceId)
                ->orderByDesc('id')
                ->first(['check_in', 'check_out']);
        }
        $hasCheckedInToday = ! empty($todayAttendanceLog?->check_in);
        $hasCheckedOutToday = ! empty($todayAttendanceLog?->check_out);
        $totalLemburJam = 0;

        $agEvent = $attendance->groupBy(function ($data) {
            return Carbon::parse($data->date)->format('Y-m-d');
        })->map(function ($items) {
            return $items->map(function ($data) {
                return [
                    'check_in' => $data->check_in?->format('H:i'),
                    'check_out' => $data->check_out?->format('H:i'),
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
            'hasCheckedInToday',
            'hasCheckedOutToday',
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $nowJakarta = now('Asia/Jakarta');
        $todayDate = $nowJakarta->toDateString();
        $currentTime = $nowJakarta->format('H:i:s');
        $officeContext = $this->resolveOfficeContext($userId);
        $attendanceStatus = $this->resolveAttendanceStatus($nowJakarta, $officeContext);

        if (Attendance::where('user_id', $userId)
            ->whereDate('date', $todayDate)
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah absen hari ini',
            ], 422);
        }

        $attendance = Attendance::create([
            'user_id' => $userId,
            'date' => $todayDate,
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
        $locationSource = $hasRequestCoordinates ? 'gps' : ($hasIpCoordinates ? 'ip' : null);

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
            'check_in' => $currentTime,
            'check_out' => null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_result' => $radiusResult,
            'distance' => round($distance, 2),
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'device_hash' => hash('sha256', ($request->userAgent() ?? 'unknown').'|'.$ipAddress),
            'location_source' => $locationSource,
            'formatted_address' => $locationMetadata['formatted_address'] ?? null,
            'address_village' => $locationMetadata['address_village'] ?? null,
            'address_district' => $locationMetadata['address_district'] ?? null,
            'address_regency' => $locationMetadata['address_regency'] ?? null,
            'address_city' => $locationMetadata['address_city'] ?? null,
            'address_province' => $locationMetadata['address_province'] ?? null,
            'address_postal_code' => $locationMetadata['address_postal_code'] ?? null,
            'geocoding_provider' => $locationMetadata['geocoding_provider'] ?? null,
            'geocoded_at' => isset($locationMetadata['geocoded_at']) ? Carbon::parse($locationMetadata['geocoded_at']) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen berhasil disimpan',
            'attendance_id' => $attendance->id,
        ]);
    }

    public function update(Request $request, Attendance $absensi): JsonResponse
    {
        if ($absensi->user_id !== Auth::id()) {
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

        $attendanceLog = AttendanceLog::query()
            ->where('attendance_id', $absensi->id)
            ->orderByDesc('id')
            ->first();

        if (! $attendanceLog || empty($attendanceLog->check_in)) {
            return response()->json([
                'success' => false,
                'message' => 'Data absen masuk tidak ditemukan',
            ], 422);
        }

        if (! empty($attendanceLog->check_out)) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah absen pulang hari ini',
            ], 422);
        }

        $attendanceLog->update([
            'check_out' => now('Asia/Jakarta')->format('H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil disimpan',
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('userEmployee');
        }

        $isSuperUser = $this->isSuperUser($authenticatedUser);
        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $isStaffUser = $this->isStaffUser($authenticatedUser);
        $userCompanyId = $authenticatedUser?->userEmployee?->company_id;
        $nowJakarta = now('Asia/Jakarta');
        $todayDate = $nowJakarta->toDateString();
        $selectedMonth = (int) $request->integer('month', (int) $nowJakarta->month);
        $selectedYear = (int) $request->integer('year', (int) $nowJakarta->year);
        $selectedCompanyId = $request->integer('company_id', 0);

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int) $nowJakarta->month;
        }

        if ($selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = (int) $nowJakarta->year;
        }

        if ($isStaffUser) {
            $staffUser = User::query()
                ->with(['userEmployee.company:id,name'])
                ->find(Auth::id());

            if (! $staffUser) {
                return response()->json(['data' => []]);
            }

            $staffAttendances = Attendance::query()
                ->where('user_id', $staffUser->id)
                ->whereMonth('date', $selectedMonth)
                ->whereYear('date', $selectedYear)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get(['id', 'date', 'status']);

            $attendanceLogsByAttendanceId = AttendanceLog::query()
                ->whereIn('attendance_id', $staffAttendances->pluck('id'))
                ->orderByDesc('id')
                ->get([
                    'attendance_id',
                    'check_in',
                    'check_out',
                    'latitude',
                    'longitude',
                    'distance',
                    'radius_result',
                    'location_source',
                    'formatted_address',
                    'address_village',
                    'address_district',
                    'address_regency',
                    'address_city',
                    'address_province',
                    'address_postal_code',
                ])
                ->groupBy('attendance_id')
                ->map(fn ($attendanceLogs) => $attendanceLogs->first());

            $tableRows = $staffAttendances->map(function (Attendance $attendanceItem) use ($attendanceLogsByAttendanceId, $staffUser): array {
                $attendanceLog = $attendanceLogsByAttendanceId->get($attendanceItem->id);

                return [
                    'attendance_id' => $attendanceItem->id,
                    'attendance_date' => $attendanceItem->date?->format('Y-m-d'),
                    'staff_name' => $staffUser->name,
                    'company_name' => $staffUser->userEmployee?->company?->name,
                    'check_in' => $this->formatAttendanceLogTime($attendanceLog?->check_in),
                    'check_out' => $this->formatAttendanceLogTime($attendanceLog?->check_out),
                    'status' => $attendanceItem->status,
                    'check_in_latitude' => isset($attendanceLog?->latitude) ? (float) $attendanceLog->latitude : null,
                    'check_in_longitude' => isset($attendanceLog?->longitude) ? (float) $attendanceLog->longitude : null,
                    'distance_meters' => isset($attendanceLog?->distance) ? (float) $attendanceLog->distance : null,
                    'radius_result' => isset($attendanceLog?->radius_result) ? (string) $attendanceLog->radius_result : null,
                    'location_source' => isset($attendanceLog?->location_source) ? (string) $attendanceLog->location_source : null,
                    'formatted_address' => isset($attendanceLog?->formatted_address) ? (string) $attendanceLog->formatted_address : null,
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
                'userEmployee.company:id,name',
                'attendances' => function ($query) use ($todayDate): void {
                    $query->whereDate('date', $todayDate)->orderByDesc('id');
                },
            ]);

        if ($isBoardOfDirectur) {
            if ($userCompanyId) {
                $tableUsersQuery->whereHas('userEmployee', function ($query) use ($userCompanyId): void {
                    $query->where('company_id', $userCompanyId);
                });
            } else {
                $tableUsersQuery->whereRaw('1 = 0');
            }
        } elseif ($isSuperUser && $selectedCompanyId > 0) {
            $tableUsersQuery->whereHas('userEmployee', function ($query) use ($selectedCompanyId): void {
                $query->where('company_id', $selectedCompanyId);
            });
        }

        $tableUsers = $tableUsersQuery->get();
        $attendanceIds = $tableUsers
            ->map(fn (User $user): mixed => $user->attendances->first()?->id)
            ->filter()
            ->values();
        $attendanceLogsByAttendanceId = AttendanceLog::query()
            ->whereIn('attendance_id', $attendanceIds)
            ->orderByDesc('id')
            ->get([
                'attendance_id',
                'check_in',
                'check_out',
                'latitude',
                'longitude',
                'distance',
                'radius_result',
                'location_source',
                'formatted_address',
                'address_village',
                'address_district',
                'address_regency',
                'address_city',
                'address_province',
                'address_postal_code',
            ])
            ->groupBy('attendance_id')
            ->map(fn ($attendanceLogs) => $attendanceLogs->first());

        $tableRows = $tableUsers->map(function (User $user) use ($attendanceLogsByAttendanceId, $todayDate): array {
            $attendanceToday = $user->attendances->first();
            $attendanceLog = $attendanceToday ? $attendanceLogsByAttendanceId->get($attendanceToday->id) : null;

            return [
                'attendance_id' => $attendanceToday?->id,
                'attendance_date' => $attendanceToday?->date?->format('Y-m-d') ?? $todayDate,
                'staff_name' => $user->name,
                'company_name' => $user->userEmployee?->company?->name,
                'check_in' => $this->formatAttendanceLogTime($attendanceLog?->check_in),
                'check_out' => $this->formatAttendanceLogTime($attendanceLog?->check_out),
                'status' => $attendanceToday?->status,
                'check_in_latitude' => isset($attendanceLog?->latitude) ? (float) $attendanceLog->latitude : null,
                'check_in_longitude' => isset($attendanceLog?->longitude) ? (float) $attendanceLog->longitude : null,
                'distance_meters' => isset($attendanceLog?->distance) ? (float) $attendanceLog->distance : null,
                'radius_result' => isset($attendanceLog?->radius_result) ? (string) $attendanceLog->radius_result : null,
                'location_source' => isset($attendanceLog?->location_source) ? (string) $attendanceLog->location_source : null,
                'formatted_address' => isset($attendanceLog?->formatted_address) ? (string) $attendanceLog->formatted_address : null,
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
     *     geocoding_provider:string|null,
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
                'geocoding_provider' => null,
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
                'geocoding_provider' => 'google_maps',
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
     *     office_end_time:string,
     *     late_grace_minutes:int|null
     * }|null
     */
    private function resolveOfficeContext(int $userId): ?array
    {
        $currentUser = User::query()
            ->with([
                'userEmployee.company:id,name,address,latitude,longitude',
            ])
            ->find($userId);

        $officeCompany = $currentUser?->userEmployee?->company;
        $companyId = $currentUser?->userEmployee?->company_id;

        if (! $officeCompany || $officeCompany->latitude === null || $officeCompany->longitude === null) {
            return null;
        }

        $attendanceRule = null;
        if ($companyId) {
            $ruleSelectColumns = ['radius', 'ip_range'];
            if (Schema::hasColumn('rules_of_attendaces', 'office_start_time')) {
                $ruleSelectColumns[] = 'office_start_time';
            }
            if (Schema::hasColumn('rules_of_attendaces', 'office_end_time')) {
                $ruleSelectColumns[] = 'office_end_time';
            }
            if (Schema::hasColumn('rules_of_attendaces', 'late_grace_minutes')) {
                $ruleSelectColumns[] = 'late_grace_minutes';
            }

            $attendanceRule = DB::table('rules_of_attendaces')
                ->where('companies_id', $companyId)
                ->where('is_active', true)
                ->orderByDesc('id')
                ->first($ruleSelectColumns);
        }

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
            'late_grace_minutes' => isset($attendanceRule?->late_grace_minutes)
                ? max((int) $attendanceRule->late_grace_minutes, 0)
                : null,
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

        $lateGraceMinutes = is_array($officeContext) && array_key_exists('late_grace_minutes', $officeContext)
            && $officeContext['late_grace_minutes'] !== null
            ? max((int) $officeContext['late_grace_minutes'], 0)
            : 0;

        $officeStartDateTime = $attendanceTime->copy();
        try {
            $officeStartDateTime->setTimeFromTimeString($officeStartTime);
        } catch (\Throwable) {
            $officeStartDateTime->setTime(8, 0, 0);
        }

        $lateThresholdDateTime = $officeStartDateTime->copy()->addMinutes($lateGraceMinutes);

        if ($attendanceTime->greaterThan($lateThresholdDateTime)) {
            return 'Terlambat';
        }

        return 'Masuk';
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

    private function formatAttendanceLogTime(mixed $timeValue): ?string
    {
        if (! is_string($timeValue) || trim($timeValue) === '') {
            return null;
        }

        try {
            return Carbon::parse($timeValue)->format('H:i');
        } catch (\Throwable) {
            return substr($timeValue, 0, 5);
        }
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
