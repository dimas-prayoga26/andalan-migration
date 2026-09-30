<?php

namespace App\Http\Controllers;

use App\Models\DeviceSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['nullable', 'string'],
            'keys.auth' => ['nullable', 'string'],
            'expirationTime' => ['nullable'],
            'browser' => ['nullable', 'string', 'max:100'],
            'platform' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $employee = $user?->employee;

        $subscription = DeviceSubscription::query()->updateOrCreate(
            ['endpoint_hash' => hash('sha256', $validated['endpoint'])],
            [
                'endpoint' => $validated['endpoint'],
                'user_id' => $user?->id,
                'employee_id' => $employee?->id,
                'brand_key' => config('app.brand_key'),
                'public_key' => $validated['keys']['p256dh'] ?? null,
                'auth_token' => $validated['keys']['auth'] ?? null,
                'content_encoding' => 'aes128gcm',
                'browser' => $validated['browser'] ?? null,
                'platform' => $validated['platform'] ?? null,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'last_subscribed_at' => now(),
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'ok',
            'id' => $subscription->id,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
        ]);

        DeviceSubscription::query()
            ->where('endpoint_hash', hash('sha256', $validated['endpoint']))
            ->where('user_id', $request->user()?->id)
            ->delete();

        return response()->json(['status' => 'ok']);
    }
}
