<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Support\TelegramAttendanceNotifier;
use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceLog;
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
        $todayJakartaDate = now('Asia/Jakarta')->toDateString();
        $publicIp = '-';
        $absensiHariIni = null;
        if (is_string($employeeId) && trim($employeeId) !== '') {
            $absensiHariIni = Attendance::query()
                ->where('date', $todayJakartaDate)
                ->where('employee_id', $employeeId)
                ->first();
        }
        $todayAttendanceId = $absensiHariIni?->id;
        $todayAttendanceDistanceKm = null;
        $todayAttendanceDistanceOutKm = null;
        $todayAttendanceExceptionTimeRange = '--:-- - --:--';
        $todayAttendanceExceptionVariance = '--.--';
        $hasEarlyDepartureExceptionToday = false;
        $attendanceHistoryEvents = [];
        if (is_string($todayAttendanceId) && trim($todayAttendanceId) !== '') {
            $latestDistanceIn = AttendanceLog::query()
                ->where('attendance_id', $todayAttendanceId)
                ->where('type', true)
                ->whereNotNull('distance')
                ->orderByDesc('created_at')
                ->value('distance');
            $latestDistanceOut = AttendanceLog::query()
                ->where('attendance_id', $todayAttendanceId)
                ->where('type', false)
                ->whereNotNull('distance')
                ->orderByDesc('created_at')
                ->value('distance');

            if (is_numeric($latestDistanceIn)) {
                $todayAttendanceDistanceKm = round(((float) $latestDistanceIn) / 1000, 2);
            }

            if (is_numeric($latestDistanceOut)) {
                $todayAttendanceDistanceOutKm = round(((float) $latestDistanceOut) / 1000, 2);
            }
        }

        if (is_string($employeeId) && trim($employeeId) !== '') {
            $attendanceHistoryEvents = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereNotNull('clock_in')
                ->orderBy('date')
                ->get(['date', 'clock_in', 'clock_out', 'late_minutes', 'status'])
                ->map(static function (Attendance $attendanceItem): ?array {
                    $attendanceDateLabel = $attendanceItem->date?->format('Y-m-d');
                    $clockInLabel = $attendanceItem->clock_in?->format('H:i');
                    if (! is_string($attendanceDateLabel) || trim($attendanceDateLabel) === '' || ! is_string($clockInLabel) || trim($clockInLabel) === '') {
                        return null;
                    }

                    $clockOutLabel = $attendanceItem->clock_out?->format('H:i') ?? '--:--';
                    $normalizedStatus = is_string($attendanceItem->status)
                        ? strtolower(trim($attendanceItem->status))
                        : '';
                    $isLate = (int) ($attendanceItem->late_minutes ?? 0) > 0 || $normalizedStatus === 'terlambat';
                    $eventBackgroundColor = $isLate ? '#fd7e14' : '#20c997';
                    $eventBorderColor = $isLate ? '#d3680d' : '#1aa179';

                    return [
                        'title' => 'In : '.$clockInLabel.' - Out : '.$clockOutLabel,
                        'start' => $attendanceDateLabel,
                        'allDay' => true,
                        'classNames' => ['fc-attendance-log-card'],
                        'backgroundColor' => $eventBackgroundColor,
                        'borderColor' => $eventBorderColor,
                        'textColor' => '#ffffff',
                    ];
                })
                ->filter(static fn (mixed $eventItem): bool => is_array($eventItem))
                ->values()
                ->all();
        }

        if (is_string($employeeId) && trim($employeeId) !== '') {
            $todayAttendanceException = AttendanceException::query()
                ->where('employee_id', $employeeId)
                ->whereDate('exception_date', now('Asia/Jakarta')->toDateString())
                ->latest('created_at')
                ->first(['exception_date', 'from_time', 'to_time', 'type']);

            if ($todayAttendanceException instanceof AttendanceException) {
                $hasEarlyDepartureExceptionToday = $todayAttendanceException->type === 'early_departure';
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
                        $exceptionDate = $todayAttendanceException->exception_date?->format('Y-m-d') ?? now('Asia/Jakarta')->toDateString();
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

        $hasCheckedInToday = ! empty($absensiHariIni?->clock_in);
        $hasCheckedOutToday = ! empty($absensiHariIni?->clock_out);
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
            'absensiHariIni',
            'officeLocation',
            'publicIp',
            'publicIpPrefix',
            'allowedIpPrefix',
            'isIpPrefixMatch',
            'todayAttendanceId',
            'todayAttendanceDistanceKm',
            'todayAttendanceDistanceOutKm',
            'todayAttendanceExceptionTimeRange',
            'todayAttendanceExceptionVariance',
            'hasEarlyDepartureExceptionToday',
            'hasCheckedInToday',
            'hasCheckedOutToday',
            'attendanceHistoryEvents',
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.profile', 'employee.telegramUser');
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
            'type' => true,
            'location' => $locationMetadata['formatted_address'] ?? null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_result' => $radiusResult,
            'distance' => round($distance, 2),
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

        if (
            $authenticatedUser instanceof User
            && $this->isStaffUser($authenticatedUser)
            && $authenticatedUser->is_telegram_verified
            && $authenticatedUser->employee?->telegramUser instanceof TelegramUser
        ) {
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
        $radiusResult = 'outside';
        if ($officeContext !== null && $hasCoordinates) {
            $distanceOut = $this->calculateDistanceInMeters(
                $latitude,
                $longitude,
                $officeContext['latitude'],
                $officeContext['longitude']
            );
            $radiusResult = $distanceOut <= $officeContext['radius_meters'] ? 'inside' : 'outside';
        }

        $locationMetadata = $hasCoordinates ? $this->reverseGeocodeCoordinates($latitude, $longitude) : [];
        $ipAddress = $ipdataData['ip'] ?? $clientIpAddress ?? $request->ip();

        AttendanceLog::create([
            'attendance_id' => $absensi->id,
            'type' => false,
            'location' => $locationMetadata['formatted_address'] ?? null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_result' => $radiusResult,
            'distance' => round($distanceOut, 2),
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

        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.profile', 'employee.telegramUser');
        }

        if (
            $authenticatedUser instanceof User
            && $this->isStaffUser($authenticatedUser)
            && $authenticatedUser->is_telegram_verified
            && $authenticatedUser->employee?->telegramUser instanceof TelegramUser
        ) {
            $absensi->refresh();
            app(TelegramAttendanceNotifier::class)->notifyCheckOut($authenticatedUser, $absensi);
        }

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil disimpan',
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

        if ($authenticatedUser->is_telegram_verified) {
            if ($authenticatedUser->employee?->telegramUser instanceof TelegramUser) {
                return response()->json([
                    'success' => true,
                    'message' => 'Verifikasi Telegram berhasil.',
                ]);
            }

            $authenticatedUser->forceFill([
                'is_telegram_verified' => false,
            ])->save();

            return response()->json([
                'success' => false,
                'message' => 'Data Telegram user tidak ditemukan. Silakan verifikasi ulang.',
            ], 422);
        }

        if ($authenticatedUser->employee?->telegramUser instanceof TelegramUser) {
            $authenticatedUser->forceFill([
                'is_telegram_verified' => true,
            ])->save();

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

            $authenticatedUser->forceFill([
                'is_telegram_verified' => true,
            ])->save();

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

    public function storeException(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee');
        }

        $employeeId = $authenticatedUser?->employee?->id;
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data employee belum tersedia untuk user ini.',
            ], 422);
        }

        $validatedData = $request->validate([
            'type' => ['required', 'in:late_arrival,early_departure'],
            'note' => ['nullable', 'string', 'max:1000'],
            'from_time' => ['nullable', 'date_format:H:i'],
            'to_time' => ['nullable', 'date_format:H:i'],
            'exception_date' => ['nullable', 'date'],
        ]);

        $exceptionDate = isset($validatedData['exception_date']) && is_string($validatedData['exception_date']) && trim($validatedData['exception_date']) !== ''
            ? Carbon::parse($validatedData['exception_date'], 'Asia/Jakarta')->toDateString()
            : now('Asia/Jakarta')->toDateString();
        $officeContext = $this->resolveOfficeContext(Auth::id());
        $officeStartTime = is_array($officeContext) && isset($officeContext['office_start_time']) && is_string($officeContext['office_start_time'])
            ? $officeContext['office_start_time']
            : '08:00:00';
        $currentJakartaTime = now('Asia/Jakarta')->format('H:i:s');
        $fromTimeInput = isset($validatedData['from_time']) && is_string($validatedData['from_time']) ? trim($validatedData['from_time']) : '';
        $toTimeInput = isset($validatedData['to_time']) && is_string($validatedData['to_time']) ? trim($validatedData['to_time']) : '';
        $fromTime = $this->normalizeTimeToSeconds($fromTimeInput === '' ? $currentJakartaTime : $fromTimeInput);
        $toTime = $this->normalizeTimeToSeconds($toTimeInput === '' ? $currentJakartaTime : $toTimeInput);

        $todayAttendance = Attendance::query()
            ->where('employee_id', $employeeId)
            ->whereDate('date', $exceptionDate)
            ->first();
        if (! $todayAttendance instanceof Attendance) {
            $todayAttendance = Attendance::create([
                'employee_id' => $employeeId,
                'date' => $exceptionDate,
                'clock_in' => null,
                'clock_out' => null,
                'late_minutes' => 0,
                'work_hours' => null,
                'status' => 'Masuk',
            ]);
        }

        $existingAttendanceException = AttendanceException::query()
            ->where('attendance_id', $todayAttendance->id)
            ->latest('created_at')
            ->first(['id']);
        if ($existingAttendanceException instanceof AttendanceException) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance exception untuk tanggal ini sudah pernah diajukan.',
            ], 422);
        }

        $lateMinutes = 0;
        $workHours = $todayAttendance->work_hours;
        $attendanceStatus = $todayAttendance->status ?: 'Masuk';
        if (($validatedData['type'] ?? null) === 'late_arrival') {
            $todayAttendance->clock_in = $toTime;
            $lateMinutes = $this->calculateMinutesBetweenTimes($exceptionDate, $officeStartTime, $toTime);
            $attendanceStatus = $lateMinutes > 0 ? 'Terlambat' : 'Masuk';
            if (! empty($todayAttendance->clock_out)) {
                try {
                    $clockInTime = Carbon::createFromFormat('Y-m-d H:i:s', $exceptionDate.' '.$toTime, 'Asia/Jakarta');
                    $clockOutTime = Carbon::createFromFormat('Y-m-d H:i:s', $exceptionDate.' '.(string) $todayAttendance->clock_out, 'Asia/Jakarta');
                    $workHours = $this->calculateWorkHours($clockInTime, $clockOutTime);
                } catch (\Throwable) {
                    $workHours = null;
                }
            } else {
                $workHours = null;
            }
        }

        if (($validatedData['type'] ?? null) === 'early_departure') {
            if (empty($todayAttendance->clock_in)) {
                $todayAttendance->clock_in = $officeStartTime;
            }
            $todayAttendance->clock_out = $fromTime;
            $lateMinutes = $this->calculateMinutesBetweenTimes($exceptionDate, $officeStartTime, $toTime);
            $workedMinutes = $this->calculateMinutesBetweenTimes($exceptionDate, $officeStartTime, $fromTime);
            $workHours = round($workedMinutes / 60, 2);
            $attendanceStatus = 'Masuk';
        }

        $todayAttendance->late_minutes = $lateMinutes;
        $todayAttendance->work_hours = $workHours;
        $todayAttendance->status = $attendanceStatus;
        $todayAttendance->save();

        $attendanceException = AttendanceException::create([
            'attendance_id' => $todayAttendance->id,
            'employee_id' => $employeeId,
            'exception_date' => $exceptionDate,
            'type' => $validatedData['type'],
            'note' => $validatedData['note'] ?? null,
            'from_time' => $fromTime,
            'to_time' => $toTime,
            'status' => 'approved',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance exception berhasil disimpan.',
            'data' => $attendanceException,
            'attendance_id' => $todayAttendance->id,
            'exception_type' => $validatedData['type'],
            'has_early_departure_exception' => $validatedData['type'] === 'early_departure',
            'has_checked_in_today' => ! empty($todayAttendance->clock_in),
            'has_checked_out_today' => ! empty($todayAttendance->clock_out),
            'summary_time_range' => substr($fromTime, 0, 5).' - '.substr($toTime, 0, 5),
            'summary_variance' => number_format(abs($this->calculateMinutesBetweenTimes($exceptionDate, $fromTime, $toTime)) / 60, 2, '.', ''),
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

    private function normalizeTimeToSeconds(string $timeValue): string
    {
        $normalizedTime = trim($timeValue);
        if ($normalizedTime === '') {
            return '00:00:00';
        }

        try {
            if (preg_match('/^\d{2}:\d{2}$/', $normalizedTime) === 1) {
                return Carbon::createFromFormat('H:i', $normalizedTime, 'Asia/Jakarta')->format('H:i:s');
            }

            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $normalizedTime) === 1) {
                return Carbon::createFromFormat('H:i:s', $normalizedTime, 'Asia/Jakarta')->format('H:i:s');
            }
        } catch (\Throwable) {
            return '00:00:00';
        }

        return '00:00:00';
    }

    private function calculateMinutesBetweenTimes(string $dateValue, string $startTimeValue, string $endTimeValue): int
    {
        try {
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateValue.' '.$this->normalizeTimeToSeconds($startTimeValue), 'Asia/Jakarta');
            $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateValue.' '.$this->normalizeTimeToSeconds($endTimeValue), 'Asia/Jakarta');
        } catch (\Throwable) {
            return 0;
        }

        $minutes = (int) $startTime->diffInMinutes($endTime, false);

        return max(0, $minutes);
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
