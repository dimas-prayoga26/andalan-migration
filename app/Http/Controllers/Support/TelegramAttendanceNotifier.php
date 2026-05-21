<?php

namespace App\Http\Controllers\Support;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramAttendanceNotifier
{
    public function notifyCheckIn(User $user, Attendance $attendance): void
    {
        $this->sendAttendanceMessage($user, $attendance, 'Check In');
    }

    public function notifyCheckOut(User $user, Attendance $attendance): void
    {
        $this->sendAttendanceMessage($user, $attendance, 'Check Out');
    }

    private function sendAttendanceMessage(User $user, Attendance $attendance, string $attendanceAction): void
    {
        $botToken = config('services.telegram.bot_token');

        if (! is_string($botToken) || trim($botToken) === '') {
            return;
        }

        $user->loadMissing('employee.profile', 'employee.telegramUser');
        $employee = $user->employee;
        $employeeProfile = $employee?->profile;
        $chatId = $employee?->telegramUser?->chat_id;

        if (! is_string($chatId) || trim($chatId) === '') {
            return;
        }

        $displayName = $employeeProfile?->name;
        if (! is_string($displayName) || trim($displayName) === '') {
            $displayName = is_string($user->username) && trim($user->username) !== ''
                ? trim($user->username)
                : trim((string) $user->email);
        }

        $attendanceDate = $attendance->date?->format('d-m-Y') ?? '-';
        $clockIn = $attendance->clock_in?->format('H:i:s') ?? '-';
        $clockOut = $attendance->clock_out?->format('H:i:s') ?? '-';
        $attendanceStatus = is_string($attendance->status) && trim($attendance->status) !== '' ? $attendance->status : '-';

        $messageLines = [
            'Notifikasi Absensi Staff',
            'Nama: '.$displayName,
            'Aksi: '.$attendanceAction,
            'Tanggal: '.$attendanceDate,
            'Status: '.$attendanceStatus,
            'Jam Masuk: '.$clockIn,
            'Jam Pulang: '.$clockOut,
        ];

        try {
            Http::timeout(10)
                ->asForm()
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => implode(PHP_EOL, $messageLines),
                ]);
        } catch (\Throwable $throwable) {
            Log::warning('Gagal mengirim notifikasi telegram absensi.', [
                'error' => $throwable->getMessage(),
                'attendance_id' => $attendance->id,
                'employee_id' => $attendance->employee_id,
            ]);
        }
    }
}
