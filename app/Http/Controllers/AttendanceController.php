<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\TelegramUser;
use App\Models\User;
use App\Services\Attendance\AttendanceMutationService;
use App\Services\Attendance\AttendanceTodayStateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceTodayStateService $attendanceTodayStateService,
        private AttendanceMutationService $attendanceMutationService
    ) {}

    public function index(Request $request): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $todayAttendanceState = $this->attendanceTodayStateService->getTodayStateForUser(
            $authenticatedUser instanceof User ? $authenticatedUser : null
        );

        $userId = Auth::id();
        $employeeId = $todayAttendanceState['employeeId'];
        $todayJakartaDate = $todayAttendanceState['todayJakartaDate'];
        $publicIp = '-';
        $absensiHariIni = $todayAttendanceState['absensiHariIni'];
        $todayAttendanceId = $todayAttendanceState['todayAttendanceId'];
        $todayAttendanceDistanceKm = $todayAttendanceState['todayAttendanceDistanceKm'];
        $todayAttendanceDistanceOutKm = $todayAttendanceState['todayAttendanceDistanceOutKm'];
        $hasEarlyDepartureExceptionToday = $todayAttendanceState['hasEarlyDepartureExceptionToday'];
        $hasCheckedInToday = $todayAttendanceState['hasCheckedInToday'];
        $hasCheckedOutToday = $todayAttendanceState['hasCheckedOutToday'];
        $todayAttendanceExceptionTimeRange = '--:-- - --:--';
        $todayAttendanceExceptionVariance = '--.--';
        $attendanceHistoryEvents = [];

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
                ->whereDate('exception_date', $todayJakartaDate)
                ->latest('created_at')
                ->first(['exception_date', 'from_time', 'to_time', 'type']);

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
        $officeLocation = $this->attendanceMutationService->resolveOfficeContext($userId);

        $clientIpAddress = $this->attendanceMutationService->resolveClientIpAddress($request);
        $ipdataData = $this->attendanceMutationService->fetchIpdata($clientIpAddress);
        if (! empty($ipdataData['ip'])) {
            $publicIp = (string) $ipdataData['ip'];
        }

        $allowedIpRange = is_array($officeLocation) ? ($officeLocation['ip_range'] ?? null) : null;
        $publicIpPrefix = $this->attendanceMutationService->extractIpTwoOctets($publicIp);
        $allowedIpPrefix = $this->attendanceMutationService->extractIpTwoOctets($allowedIpRange);
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
        try {
            $storeResult = $this->attendanceMutationService->store(
                $request,
                Auth::user(),
                Auth::id(),
            );

            return response()->json($storeResult['payload'], $storeResult['status']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absen masuk.',
            ], 500);
        }
    }

    public function update(Request $request, Attendance $absensi): JsonResponse
    {
        try {
            $updateResult = $this->attendanceMutationService->update(
                $request,
                $absensi,
                Auth::user(),
                Auth::id(),
            );

            return response()->json($updateResult['payload'], $updateResult['status']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absen pulang.',
            ], 500);
        }
    }

    public function currentIp(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $publicIp = '-';
        $officeLocation = $this->attendanceMutationService->resolveOfficeContext($userId);
        $clientIpAddress = $this->attendanceMutationService->resolveClientIpAddress($request, $request->query('client_ip'));
        $ipdataData = $this->attendanceMutationService->fetchIpdata($clientIpAddress);

        if (! empty($ipdataData['ip'])) {
            $publicIp = (string) $ipdataData['ip'];
        }

        $allowedIpRange = is_array($officeLocation) ? ($officeLocation['ip_range'] ?? null) : null;
        $publicIpPrefix = $this->attendanceMutationService->extractIpTwoOctets($publicIp);
        $allowedIpPrefix = $this->attendanceMutationService->extractIpTwoOctets($allowedIpRange);
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

        // Temporary disabled:
        // Skip Telegram API verification + persistence flow for now.
        // Remove this return to re-enable verification flow below.
        return response()->json([
            'success' => true,
            'message' => 'Verifikasi Telegram sementara dinonaktifkan. Lanjut verifikasi geofencing.',
        ]);

        /*
        // Temporary disabled:
        // Enforce app username requirement before matching Telegram account.
        // Uncomment this block when strict username binding is needed again.
        // $applicationUsername = is_string($authenticatedUser->username) ? trim($authenticatedUser->username) : '';
        // if ($applicationUsername === '') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Username akun aplikasi belum tersedia.',
        //     ], 422);
        // }

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

                // Temporary disabled:
                // Strict Telegram username matching with app username.
                // Uncomment this block when strict username binding is needed again.
                // if (mb_strtolower($telegramUsername) !== mb_strtolower($applicationUsername)) {
                //     continue;
                // }

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
        */
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

        try {
            return $this->attendanceMutationService->storeException(
                $validatedData,
                $employeeId,
                Auth::id(),
            );
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses attendance exception.',
            ], 500);
        }
    }
}
