<?php

namespace App\Services;

use App\Models\DeviceSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class WebPushNotificationService
{
    /**
     * @param  Collection<int, DeviceSubscription>  $subscriptions
     * @param  array{title:string,body?:string,url?:string,icon?:string,badge?:string,tag?:string}  $payload
     * @return array{total:int,success:int,rejected:int,failed:int,deleted:int,skipped:?string}
     */
    public function sendToSubscriptions(Collection $subscriptions, array $payload): array
    {
        $result = [
            'total' => $subscriptions->count(),
            'success' => 0,
            'rejected' => 0,
            'failed' => 0,
            'deleted' => 0,
            'skipped' => null,
        ];
        $publicKey = trim((string) config('services.webpush.vapid_public_key'));
        $privateKey = trim((string) config('services.webpush.vapid_private_key'));

        if ($subscriptions->isEmpty()) {
            $result['skipped'] = 'no_subscriptions';

            return $result;
        }

        if ($publicKey === '' || $privateKey === '') {
            $result['skipped'] = 'missing_vapid_keys';
            Log::warning('Web push notification skipped because VAPID keys are missing.', [
                'subscription_count' => $subscriptions->count(),
                'has_public_key' => $publicKey !== '',
                'has_private_key' => $privateKey !== '',
            ]);

            return $result;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('app.url') ?: url('/'),
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);

            $encodedPayload = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $exception) {
            $result['skipped'] = 'prepare_failed';
            Log::warning('Failed to prepare web push notification.', [
                'message' => $exception->getMessage(),
            ]);

            return $result;
        }

        $subscriptions->each(function (DeviceSubscription $deviceSubscription) use ($webPush, $encodedPayload, &$result): void {
            try {
                $report = $webPush->sendOneNotification(
                    Subscription::create([
                        'endpoint' => $deviceSubscription->endpoint,
                        'publicKey' => $deviceSubscription->public_key,
                        'authToken' => $deviceSubscription->auth_token,
                        'contentEncoding' => $deviceSubscription->content_encoding ?: 'aes128gcm',
                    ]),
                    $encodedPayload
                );

                if ($report->isSuccess()) {
                    $result['success']++;
                    $deviceSubscription->forceFill(['last_used_at' => now()])->save();
                    Log::info('Web push notification sent successfully.', [
                        'device_subscription_id' => $deviceSubscription->id,
                        'user_id' => $deviceSubscription->user_id,
                        'employee_id' => $deviceSubscription->employee_id,
                    ]);

                    return;
                }

                $result['rejected']++;
                $statusCode = $report->getResponse()?->getStatusCode();
                if (in_array($statusCode, [404, 410], true)) {
                    $result['deleted']++;
                    $deviceSubscription->delete();

                    return;
                }

                Log::warning('Web push notification was rejected.', [
                    'device_subscription_id' => $deviceSubscription->id,
                    'status_code' => $statusCode,
                    'reason' => $report->getReason(),
                ]);
            } catch (Throwable $exception) {
                $result['failed']++;
                Log::warning('Failed to send web push notification.', [
                    'device_subscription_id' => $deviceSubscription->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        });

        return $result;
    }
}
