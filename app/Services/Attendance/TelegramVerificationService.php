<?php

namespace App\Services\Attendance;

use App\Models\TelegramUser;
use App\Models\User;

class TelegramVerificationService
{
    /**
     * @return array{status:int,payload:array<string,mixed>}
     */
    public function verify(?User $authenticatedUser): array
    {
        if (! $authenticatedUser instanceof User) {
            return [
                'status' => 401,
                'payload' => [
                    'success' => false,
                    'message' => 'User tidak terautentikasi.',
                ],
            ];
        }

        $authenticatedUser->loadMissing('employee.telegramUser');
        $employeeId = $authenticatedUser?->employee?->id;
        if (! is_string($employeeId) || trim($employeeId) === '') {
            return [
                'status' => 422,
                'payload' => [
                    'success' => false,
                    'message' => 'Employee untuk user ini belum tersedia.',
                ],
            ];
        }

        if ($authenticatedUser->is_telegram_verified) {
            if ($authenticatedUser->employee?->telegramUser instanceof TelegramUser) {
                return [
                    'status' => 200,
                    'payload' => [
                        'success' => true,
                        'message' => 'Verifikasi Telegram berhasil.',
                    ],
                ];
            }

            $authenticatedUser->forceFill([
                'is_telegram_verified' => false,
            ])->save();

            return [
                'status' => 422,
                'payload' => [
                    'success' => false,
                    'message' => 'Data Telegram user tidak ditemukan. Silakan verifikasi ulang.',
                ],
            ];
        }

        if ($authenticatedUser->employee?->telegramUser instanceof TelegramUser) {
            $authenticatedUser->forceFill([
                'is_telegram_verified' => true,
            ])->save();

            return [
                'status' => 200,
                'payload' => [
                    'success' => true,
                    'message' => 'Verifikasi Telegram berhasil.',
                ],
            ];
        }

        return [
            'status' => 200,
            'payload' => [
                'success' => true,
                'message' => 'Verifikasi Telegram sementara dinonaktifkan. Lanjut verifikasi geofencing.',
            ],
        ];
    }
}
