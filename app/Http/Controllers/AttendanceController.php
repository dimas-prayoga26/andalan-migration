<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceHoliday;
use App\Models\TelegramUser;
use App\Models\User;
use App\Services\Attendance\AttendanceCardsViewDataService;
use App\Services\Attendance\AttendanceMutationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceCardsViewDataService $attendanceCardsViewDataService,
        private AttendanceMutationService $attendanceMutationService
    ) {}

    public function index(Request $request): View
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('employee.deployment');
        }

        $attendanceCardsData = $this->attendanceCardsViewDataService->build(
            $authenticatedUser instanceof User ? $authenticatedUser : null,
            Auth::id(),
            $request
        );
        $employeeId = $attendanceCardsData['employeeId'];
        $attendanceHistoryEvents = [];
        $holidayEvents = AttendanceHoliday::query()
            ->orderBy('date')
            ->get(['date', 'name', 'type'])
            ->map(static function (AttendanceHoliday $attendanceHoliday): ?array {
                $holidayDateLabel = $attendanceHoliday->date?->format('Y-m-d');
                $holidayNameLabel = is_string($attendanceHoliday->name) ? trim($attendanceHoliday->name) : '';
                if (! is_string($holidayDateLabel) || trim($holidayDateLabel) === '' || $holidayNameLabel === '') {
                    return null;
                }

                $holidayType = (int) ($attendanceHoliday->type ?? 1);
                $isNationalHoliday = $holidayType === 1;
                $eventTypeLabel = $isNationalHoliday ? 'Hari Libur Nasional' : 'Cuti Bersama';
                $eventBackgroundColor = $isNationalHoliday ? '#dc3545' : '#cd5c5c';

                return [
                    'title' => $holidayNameLabel,
                    'start' => $holidayDateLabel,
                    'allDay' => true,
                    'classNames' => [$isNationalHoliday ? 'fc-national-holiday-card' : 'fc-joint-leave-card'],
                    'backgroundColor' => $eventBackgroundColor,
                    'borderColor' => $eventBackgroundColor,
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'dayOffEventType' => $eventTypeLabel,
                        'dayOffHolidayName' => $holidayNameLabel,
                        'dayOffDate' => $holidayDateLabel,
                    ],
                ];
            })
            ->filter(static fn (mixed $eventItem): bool => is_array($eventItem))
            ->values()
            ->all();

        if (is_string($employeeId) && trim($employeeId) !== '') {
            $attendanceHistoryEvents = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereNotNull('clock_in')
                ->with([
                    'attendanceException:id,attendance_id,type,status',
                ])
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
                    $exceptionType = is_string($attendanceItem->attendanceException?->type)
                        ? strtolower(trim($attendanceItem->attendanceException->type))
                        : '';
                    $hasAttendanceExceptionColorOverride = in_array($exceptionType, ['early_departure', 'late_arrival'], true);
                    $isLate = (int) ($attendanceItem->late_minutes ?? 0) > 0 || $normalizedStatus === 'terlambat';
                    $eventBackgroundColor = '#20c997';
                    $eventBorderColor = '#1aa179';
                    if ($hasAttendanceExceptionColorOverride) {
                        $eventBackgroundColor = '#17a2b8';
                        $eventBorderColor = '#117a8b';
                    } elseif ($isLate) {
                        $eventBackgroundColor = '#fd7e14';
                        $eventBorderColor = '#d3680d';
                    }

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

        return view('attendance.attendance.index', array_merge(
            $attendanceCardsData,
            [
                'attendanceHistoryEvents' => $attendanceHistoryEvents,
                'holidayEvents' => $holidayEvents,
            ]
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

    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        try {
            $updateResult = $this->attendanceMutationService->update(
                $request,
                $attendance,
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
