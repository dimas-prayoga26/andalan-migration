<?php

namespace App\Http\Controllers\Support;

use App\Models\Attendance;
use App\Models\TelegramUserLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramAttendanceNotifier
{
    public function notifyCheckIn(User $user, Attendance $attendance): void
    {
        $clockInTime = $attendance->clock_in?->format('H:i:s') ?? '-';
        $attendanceStatus = is_string($attendance->status) && trim($attendance->status) !== '' ? $attendance->status : '-';
        $this->sendAttendanceMessage($user, $attendance, 'clock_in', 'Clock in '.$clockInTime.' '.$attendanceStatus);
    }

    public function notifyCheckOut(User $user, Attendance $attendance): void
    {
        $clockOutTime = $attendance->clock_out?->format('H:i:s') ?? '-';
        $attendanceStatus = is_string($attendance->status) && trim($attendance->status) !== '' ? $attendance->status : '-';
        $this->sendAttendanceMessage($user, $attendance, 'clock_out', 'Clock out '.$clockOutTime.' '.$attendanceStatus);
    }

    private function sendAttendanceMessage(User $user, Attendance $attendance, string $notificationType, string $message): void
    {
        $botToken = config('services.telegram.bot_token');
        $user->loadMissing('employee.profile', 'employee.telegramUser');
        $employee = $user->employee;
        $employeeProfile = $employee?->profile;
        $telegramUser = $employee?->telegramUser;
        $chatId = $telegramUser?->chat_id;

        if (! is_string($botToken) || trim($botToken) === '') {
            $this->createTelegramLog(
                $telegramUser?->id,
                $attendance->id,
                $notificationType,
                $message,
                false,
                null,
                null,
                'Telegram bot token belum diatur'
            );

            return;
        }

        if (! is_string($chatId) || trim($chatId) === '') {
            $this->createTelegramLog(
                $telegramUser?->id,
                $attendance->id,
                $notificationType,
                $message,
                false,
                null,
                null,
                'Chat ID Telegram belum terhubung untuk staff ini'
            );

            return;
        }

        $displayName = $employeeProfile?->name;
        if (! is_string($displayName) || trim($displayName) === '') {
            $displayName = is_string($user->username) && trim($user->username) !== ''
                ? trim($user->username)
                : trim((string) $user->email);
        }

        try {
            $response = Http::connectTimeout(8)
                ->timeout(20)
                ->retry(3, 500)
                ->withOptions([
                    'version' => 1.1,
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    ],
                ])
                ->asForm()
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                ]);

            $responsePayload = $response->json();
            if (! is_array($responsePayload)) {
                $responsePayload = [
                    'raw_body' => $response->body(),
                ];
            }

            $isSuccess = $response->successful()
                && (($responsePayload['ok'] ?? false) === true);

            $this->createTelegramLog(
                $telegramUser?->id,
                $attendance->id,
                $notificationType,
                $message,
                $isSuccess,
                $response->status(),
                $responsePayload,
                $isSuccess ? null : ('Telegram API error: '.$response->body())
            );
        } catch (\Throwable $throwable) {
            $this->createTelegramLog(
                $telegramUser?->id,
                $attendance->id,
                $notificationType,
                $message,
                false,
                null,
                null,
                $throwable->getMessage()
            );

            Log::warning('Failed to send Telegram attendance notification.', [
                'error' => $throwable->getMessage(),
                'attendance_id' => $attendance->id,
                'employee_id' => $attendance->employee_id,
                'staff_name' => $displayName,
            ]);
        }
    }

    private function createTelegramLog(
        ?string $telegramUserId,
        ?string $attendanceId,
        string $notificationType,
        string $message,
        bool $isSuccess,
        ?int $httpStatusCode,
        mixed $responsePayload,
        ?string $errorMessage
    ): void {
        TelegramUserLog::create([
            'telegram_user_id' => $telegramUserId,
            'attendance_id' => $attendanceId,
            'notification_type' => $notificationType,
            'message' => $message,
            'is_success' => $isSuccess,
            'http_status_code' => $httpStatusCode,
            'response_payload' => is_array($responsePayload) ? $responsePayload : null,
            'error_message' => $errorMessage,
            'notified_at' => now(),
        ]);
    }
}
