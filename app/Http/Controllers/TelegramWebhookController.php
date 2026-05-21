<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->isValidWebhookSecret($request)) {
            return response()->json(['ok' => false, 'message' => 'Invalid telegram webhook secret.'], 403);
        }

        $payload = $request->all();
        $updates = $this->extractUpdates($payload);
        $processedCount = 0;
        $linkedCount = 0;

        foreach ($updates as $update) {
            if (! is_array($update)) {
                continue;
            }

            $processedCount++;
            if (! $this->isStartCommandUpdate($update)) {
                continue;
            }

            $telegramFrom = $this->resolveTelegramFrom($update);
            if (! is_array($telegramFrom)) {
                continue;
            }
            $telegramChat = $this->resolveTelegramChat($update);

            $telegramUsername = isset($telegramFrom['username']) && is_string($telegramFrom['username'])
                ? trim($telegramFrom['username'])
                : '';

            if ($telegramUsername === '') {
                continue;
            }

            $applicationUser = User::query()
                ->with('employee')
                ->whereRaw('LOWER(username) = ?', [mb_strtolower($telegramUsername)])
                ->first();

            $employeeId = $applicationUser?->employee?->id;
            if (! is_string($employeeId) || trim($employeeId) === '') {
                Log::info('Telegram username tidak ditemukan di users.username.', [
                    'telegram_username' => $telegramUsername,
                ]);

                continue;
            }

            TelegramUser::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'chat_id' => isset($telegramChat['id']) ? (string) $telegramChat['id'] : null,
                    'first_name' => isset($telegramFrom['first_name']) && is_string($telegramFrom['first_name']) ? $telegramFrom['first_name'] : null,
                    'last_name' => isset($telegramFrom['last_name']) && is_string($telegramFrom['last_name']) ? $telegramFrom['last_name'] : null,
                    'username' => $telegramUsername,
                    'language_code' => isset($telegramFrom['language_code']) && is_string($telegramFrom['language_code']) ? $telegramFrom['language_code'] : null,
                ]
            );

            $linkedCount++;
        }

        return response()->json([
            'ok' => true,
            'processed' => $processedCount,
            'linked' => $linkedCount,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    private function extractUpdates(array $payload): array
    {
        if (isset($payload['result']) && is_array($payload['result'])) {
            return array_values(array_filter($payload['result'], static fn (mixed $item): bool => is_array($item)));
        }

        return [$payload];
    }

    /**
     * @param  array<string, mixed>  $update
     */
    private function isStartCommandUpdate(array $update): bool
    {
        $message = $this->resolveTelegramMessage($update);
        if (! is_array($message)) {
            return false;
        }

        $messageText = isset($message['text']) && is_string($message['text']) ? trim($message['text']) : '';

        return str_starts_with($messageText, '/start');
    }

    /**
     * @param  array<string, mixed>  $update
     * @return array<string, mixed>|null
     */
    private function resolveTelegramMessage(array $update): ?array
    {
        $message = $update['message'] ?? $update['edited_message'] ?? null;

        return is_array($message) ? $message : null;
    }

    /**
     * @param  array<string, mixed>  $update
     * @return array<string, mixed>|null
     */
    private function resolveTelegramFrom(array $update): ?array
    {
        $message = $this->resolveTelegramMessage($update);
        if (! is_array($message)) {
            return null;
        }

        $from = $message['from'] ?? null;

        return is_array($from) ? $from : null;
    }

    /**
     * @param  array<string, mixed>  $update
     * @return array<string, mixed>|null
     */
    private function resolveTelegramChat(array $update): ?array
    {
        $message = $this->resolveTelegramMessage($update);
        if (! is_array($message)) {
            return null;
        }

        $chat = $message['chat'] ?? null;

        return is_array($chat) ? $chat : null;
    }

    private function isValidWebhookSecret(Request $request): bool
    {
        $expectedSecret = config('services.telegram.webhook_secret');

        if (! is_string($expectedSecret) || trim($expectedSecret) === '') {
            return true;
        }

        $incomingSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');

        return is_string($incomingSecret) && hash_equals($expectedSecret, $incomingSecret);
    }
}
