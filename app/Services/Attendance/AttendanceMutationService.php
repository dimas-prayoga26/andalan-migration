<?php

namespace App\Services\Attendance;

use App\Http\Controllers\Support\TelegramAttendanceNotifier;
use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceLog;
use App\Models\TelegramUser;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AttendanceMutationService
{
    public function __construct(private TelegramAttendanceNotifier $telegramAttendanceNotifier) {}

    /**
     * @return array{status:int,payload:array<string,mixed>}
     */
    public function store(Request $request, ?User $authenticatedUser, int|string|null $authenticatedUserId): array
    {
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.profile', 'employee.telegramUser');
        }

        $employeeId = $authenticatedUser?->employee?->id;
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return [
                'status' => 422,
                'payload' => [
                    'success' => false,
                    'message' => 'Data employee belum tersedia untuk user ini',
                ],
            ];
        }

        $nowJakarta = now('Asia/Jakarta');
        $todayDate = $nowJakarta->toDateString();
        $currentTime = $nowJakarta->format('H:i:s');
        $officeContext = $this->resolveOfficeContext($authenticatedUserId);
        $attendanceStatus = $this->resolveAttendanceStatus($nowJakarta, $officeContext);
        $lateMinutes = $this->calculateLateMinutes($nowJakarta, $officeContext);
        $trackingContext = $this->buildTrackingContext($request, $officeContext);

        try {
            $attendance = DB::transaction(function () use ($employeeId, $todayDate, $currentTime, $lateMinutes, $attendanceStatus, $trackingContext): Attendance {
                $attendanceAlreadyExists = Attendance::query()
                    ->where('employee_id', $employeeId)
                    ->whereDate('date', $todayDate)
                    ->exists();
                if ($attendanceAlreadyExists) {
                    throw new \RuntimeException('already_checked_in');
                }

                $attendance = Attendance::query()->create([
                    'employee_id' => $employeeId,
                    'date' => $todayDate,
                    'clock_in' => $currentTime,
                    'clock_out' => null,
                    'late_minutes' => $lateMinutes,
                    'work_hours' => null,
                    'status' => $attendanceStatus,
                ]);

                $this->createAttendanceLog($attendance->id, true, $trackingContext);

                return $attendance;
            });
        } catch (\RuntimeException $runtimeException) {
            if ($runtimeException->getMessage() === 'already_checked_in') {
                return [
                    'status' => 422,
                    'payload' => [
                        'success' => false,
                        'message' => 'Kamu sudah absen hari ini',
                    ],
                ];
            }

            throw $runtimeException;
        } catch (QueryException $queryException) {
            if ($this->isDuplicateKeyException($queryException)) {
                return [
                    'status' => 422,
                    'payload' => [
                        'success' => false,
                        'message' => 'Kamu sudah absen hari ini',
                    ],
                ];
            }

            throw $queryException;
        }

        if (
            $authenticatedUser instanceof User
            && $this->isStaffUser($authenticatedUser)
            && $authenticatedUser->is_telegram_verified
            && $authenticatedUser->employee?->telegramUser instanceof TelegramUser
        ) {
            $this->telegramAttendanceNotifier->notifyCheckIn($authenticatedUser, $attendance);
        }

        return [
            'status' => 200,
            'payload' => [
                'success' => true,
                'message' => 'Absen berhasil disimpan',
                'attendance_id' => $attendance->id,
            ],
        ];
    }

    /**
     * @return array{status:int,payload:array<string,mixed>}
     */
    public function update(Request $request, Attendance $attendance, ?User $authenticatedUser, int|string|null $authenticatedUserId): array
    {
        $attendance->loadMissing('employee');
        if ($attendance->employee?->user_id !== $authenticatedUserId) {
            return [
                'status' => 403,
                'payload' => [
                    'success' => false,
                    'message' => 'Tidak memiliki akses ke data absen ini',
                ],
            ];
        }

        $todayDate = now('Asia/Jakarta')->toDateString();
        if ($attendance->date?->format('Y-m-d') !== $todayDate) {
            return [
                'status' => 422,
                'payload' => [
                    'success' => false,
                    'message' => 'Data absen tidak sesuai tanggal hari ini',
                ],
            ];
        }

        if (empty($attendance->clock_in)) {
            return [
                'status' => 422,
                'payload' => [
                    'success' => false,
                    'message' => 'Data absen masuk tidak ditemukan',
                ],
            ];
        }

        if (! empty($attendance->clock_out)) {
            return [
                'status' => 422,
                'payload' => [
                    'success' => false,
                    'message' => 'Kamu sudah absen pulang hari ini',
                ],
            ];
        }

        $clockOutTime = now('Asia/Jakarta');
        $clockOutTimeString = $clockOutTime->format('H:i:s');
        $attendanceDate = $attendance->date?->format('Y-m-d') ?? $todayDate;
        $clockInRaw = $attendance->getRawOriginal('clock_in');
        $clockInTimeString = is_string($clockInRaw) && trim($clockInRaw) !== ''
            ? $clockInRaw
            : (string) $clockOutTimeString;
        try {
            $clockInTime = Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate.' '.$clockInTimeString, 'Asia/Jakarta');
        } catch (\Throwable) {
            $clockInTime = Carbon::parse($attendanceDate.' '.$clockInTimeString, 'Asia/Jakarta');
        }
        $workHours = $this->calculateWorkHours($clockInTime, $clockOutTime);
        $officeContext = $this->resolveOfficeContext($authenticatedUserId);
        $trackingContext = $this->buildTrackingContext($request, $officeContext);

        try {
            $updatedAttendance = DB::transaction(function () use ($attendance, $todayDate, $clockOutTimeString, $workHours, $trackingContext): Attendance {
                $lockedAttendance = Attendance::query()
                    ->whereKey($attendance->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedAttendance instanceof Attendance) {
                    throw new \RuntimeException('attendance_not_found');
                }

                if ($lockedAttendance->date?->format('Y-m-d') !== $todayDate) {
                    throw new \RuntimeException('invalid_attendance_date');
                }

                if (empty($lockedAttendance->clock_in)) {
                    throw new \RuntimeException('missing_clock_in');
                }

                if (! empty($lockedAttendance->clock_out)) {
                    throw new \RuntimeException('already_checked_out');
                }

                $lockedAttendance->update([
                    'clock_out' => $clockOutTimeString,
                    'work_hours' => $workHours,
                ]);

                $this->createAttendanceLog($lockedAttendance->id, false, $trackingContext);

                return $lockedAttendance;
            });
        } catch (\RuntimeException $runtimeException) {
            return match ($runtimeException->getMessage()) {
                'attendance_not_found' => [
                    'status' => 404,
                    'payload' => [
                        'success' => false,
                        'message' => 'Data absen tidak ditemukan',
                    ],
                ],
                'invalid_attendance_date' => [
                    'status' => 422,
                    'payload' => [
                        'success' => false,
                        'message' => 'Data absen tidak sesuai tanggal hari ini',
                    ],
                ],
                'missing_clock_in' => [
                    'status' => 422,
                    'payload' => [
                        'success' => false,
                        'message' => 'Data absen masuk tidak ditemukan',
                    ],
                ],
                'already_checked_out' => [
                    'status' => 422,
                    'payload' => [
                        'success' => false,
                        'message' => 'Kamu sudah absen pulang hari ini',
                    ],
                ],
                default => throw $runtimeException,
            };
        }

        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.profile', 'employee.telegramUser');
        }

        if (
            $authenticatedUser instanceof User
            && $this->isStaffUser($authenticatedUser)
            && $authenticatedUser->is_telegram_verified
            && $authenticatedUser->employee?->telegramUser instanceof TelegramUser
        ) {
            $updatedAttendance->refresh();
            $this->telegramAttendanceNotifier->notifyCheckOut($authenticatedUser, $updatedAttendance);
        }

        return [
            'status' => 200,
            'payload' => [
                'success' => true,
                'message' => 'Absen pulang berhasil disimpan',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $validatedData
     */
    public function storeException(array $validatedData, string $employeeId, int|string|null $authenticatedUserId): JsonResponse
    {
        return DB::transaction(function () use ($validatedData, $employeeId, $authenticatedUserId): JsonResponse {
            $exceptionDate = isset($validatedData['exception_date']) && is_string($validatedData['exception_date']) && trim($validatedData['exception_date']) !== ''
                ? Carbon::parse($validatedData['exception_date'], 'Asia/Jakarta')->toDateString()
                : now('Asia/Jakarta')->toDateString();
            $officeContext = $this->resolveOfficeContext($authenticatedUserId);
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
                $todayAttendance = Attendance::query()->create([
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

            $attendanceException = AttendanceException::query()->create([
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
        });
    }

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

    public function resolveClientIpAddress(Request $request, mixed $preferredIpAddress = null): ?string
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

    public function calculateWorkHours(Carbon $clockInTime, Carbon $clockOutTime): float
    {
        $workedMinutes = (int) $clockInTime->diffInMinutes($clockOutTime, false);
        if ($workedMinutes < 0) {
            return 0.0;
        }

        return round($workedMinutes / 60, 2);
    }

    public function normalizeTimeToSeconds(string $timeValue): string
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

    public function calculateMinutesBetweenTimes(string $dateValue, string $startTimeValue, string $endTimeValue): int
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

    /**
     * @param  array<string, mixed>|null  $officeContext
     * @return array{
     *     latitude:float,
     *     longitude:float,
     *     radius_result:string,
     *     distance:float,
     *     ip_address:string|null,
     *     user_agent:string|null,
     *     location:string|null,
     *     address_village:string|null,
     *     address_district:string|null,
     *     address_regency:string|null,
     *     address_city:string|null,
     *     address_province:string|null,
     *     address_postal_code:string|null,
     *     geocoded_at:?Carbon
     * }
     */
    private function buildTrackingContext(Request $request, ?array $officeContext): array
    {
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
        $ipAddress = isset($ipdataData['ip']) && is_string($ipdataData['ip']) ? $ipdataData['ip'] : $clientIpAddress;
        $hasCoordinates = $hasRequestCoordinates || $hasIpCoordinates;

        $distance = 0.0;
        $radiusResult = 'outside';
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
        $geocodedAt = isset($locationMetadata['geocoded_at']) && is_string($locationMetadata['geocoded_at'])
            ? Carbon::parse($locationMetadata['geocoded_at'])
            : null;

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_result' => $radiusResult,
            'distance' => round($distance, 2),
            'ip_address' => $ipAddress ?? $request->ip(),
            'user_agent' => $request->userAgent(),
            'location' => isset($locationMetadata['formatted_address']) && is_string($locationMetadata['formatted_address'])
                ? $locationMetadata['formatted_address']
                : null,
            'address_village' => $locationMetadata['address_village'] ?? null,
            'address_district' => $locationMetadata['address_district'] ?? null,
            'address_regency' => $locationMetadata['address_regency'] ?? null,
            'address_city' => $locationMetadata['address_city'] ?? null,
            'address_province' => $locationMetadata['address_province'] ?? null,
            'address_postal_code' => $locationMetadata['address_postal_code'] ?? null,
            'geocoded_at' => $geocodedAt,
        ];
    }

    /**
     * @param  array{
     *     latitude:float,
     *     longitude:float,
     *     radius_result:string,
     *     distance:float,
     *     ip_address:string|null,
     *     user_agent:string|null,
     *     location:string|null,
     *     address_village:string|null,
     *     address_district:string|null,
     *     address_regency:string|null,
     *     address_city:string|null,
     *     address_province:string|null,
     *     address_postal_code:string|null,
     *     geocoded_at:?Carbon
     * }  $trackingContext
     */
    private function createAttendanceLog(string $attendanceId, bool $type, array $trackingContext): void
    {
        AttendanceLog::query()->create([
            'attendance_id' => $attendanceId,
            'type' => $type,
            'location' => $trackingContext['location'],
            'latitude' => $trackingContext['latitude'],
            'longitude' => $trackingContext['longitude'],
            'radius_result' => $trackingContext['radius_result'],
            'distance' => $trackingContext['distance'],
            'ip_address' => $trackingContext['ip_address'],
            'user_agent' => $trackingContext['user_agent'],
            'device_hash' => hash('sha256', ($trackingContext['user_agent'] ?? 'unknown').'|'.($trackingContext['ip_address'] ?? 'unknown')),
            'address_village' => $trackingContext['address_village'],
            'address_district' => $trackingContext['address_district'],
            'address_regency' => $trackingContext['address_regency'],
            'address_city' => $trackingContext['address_city'],
            'address_province' => $trackingContext['address_province'],
            'address_postal_code' => $trackingContext['address_postal_code'],
            'geocoded_at' => $trackingContext['geocoded_at'],
        ]);
    }

    private function isDuplicateKeyException(QueryException $queryException): bool
    {
        $sqlState = $queryException->errorInfo[0] ?? null;
        if ($sqlState === '23000') {
            return true;
        }

        return str_contains(strtolower($queryException->getMessage()), 'duplicate');
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
}
