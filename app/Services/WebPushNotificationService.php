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
     * @param  array{title:string,body?:string,url?:string,icon?:string,badge?:string}  $payload
     */
    public function sendToSubscriptions(Collection $subscriptions, array $payload): void
    {
        $publicKey = trim((string) config('services.webpush.vapid_public_key'));
        $privateKey = trim((string) config('services.webpush.vapid_private_key'));

        if ($publicKey === '' || $privateKey === '' || $subscriptions->isEmpty()) {
            return;
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
            Log::warning('Failed to prepare web push notification.', [
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        $subscriptions->each(function (DeviceSubscription $deviceSubscription) use ($webPush, $encodedPayload): void {
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
                    $deviceSubscription->forceFill(['last_used_at' => now()])->save();

                    return;
                }

                $statusCode = $report->getResponse()?->getStatusCode();
                if (in_array($statusCode, [404, 410], true)) {
                    $deviceSubscription->delete();
                }
            } catch (Throwable $exception) {
                Log::warning('Failed to send web push notification.', [
                    'device_subscription_id' => $deviceSubscription->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        });
    }
}
