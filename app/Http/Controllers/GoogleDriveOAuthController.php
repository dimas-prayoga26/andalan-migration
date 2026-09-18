<?php

namespace App\Http\Controllers;

use App\Models\GoogleOauthToken;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GoogleDriveOAuthController extends Controller
{
    private const Provider = 'google_drive';

    private const TokenEndpoint = 'https://oauth2.googleapis.com/token';

    public function accessToken(Request $request): JsonResponse
    {
        $googleOauthToken = $this->tokenForAuthenticatedUser();

        if (! $googleOauthToken instanceof GoogleOauthToken) {
            return $this->authorizationRequiredResponse();
        }

        if (
            is_string($googleOauthToken->access_token)
            && trim($googleOauthToken->access_token) !== ''
            && $googleOauthToken->expires_at instanceof Carbon
            && $googleOauthToken->expires_at->greaterThan(now()->addMinute())
        ) {
            return response()->json([
                'success' => true,
                'access_token' => $googleOauthToken->access_token,
                'expires_at' => $googleOauthToken->expires_at->toIso8601String(),
            ]);
        }

        if (! is_string($googleOauthToken->refresh_token) || trim($googleOauthToken->refresh_token) === '') {
            return $this->authorizationRequiredResponse();
        }

        $response = $this->refreshGoogleToken($googleOauthToken);

        if (! $response->successful()) {
            return $this->authorizationRequiredResponse('Token Google perlu dihubungkan ulang.');
        }

        $payload = $response->json();
        $googleOauthToken->forceFill([
            'access_token' => $payload['access_token'] ?? null,
            'scopes' => $payload['scope'] ?? $googleOauthToken->scopes,
            'token_type' => $payload['token_type'] ?? $googleOauthToken->token_type,
            'expires_at' => $this->expiresAt($payload['expires_in'] ?? null),
            'revoked_at' => null,
        ])->save();

        return response()->json([
            'success' => true,
            'access_token' => $googleOauthToken->access_token,
            'expires_at' => $googleOauthToken->expires_at?->toIso8601String(),
        ]);
    }

    public function exchangeCode(Request $request): JsonResponse
    {
        abort_unless($request->header('X-Requested-With') === 'XMLHttpRequest', 403);

        $validated = $request->validate([
            'code' => ['required', 'string'],
            'redirect_uri' => ['required', 'url'],
        ]);

        if (! $this->googleClientId() || ! $this->googleClientSecret()) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi GOOGLE_CLIENT_ID atau GOOGLE_CLIENT_SECRET belum lengkap.',
            ], 422);
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post(self::TokenEndpoint, [
                'client_id' => $this->googleClientId(),
                'client_secret' => $this->googleClientSecret(),
                'code' => $validated['code'],
                'grant_type' => 'authorization_code',
                'redirect_uri' => $validated['redirect_uri'],
            ]);

        if (! $response->successful()) {
            return response()->json([
                'success' => false,
                'message' => $response->json('error_description') ?: $response->json('error') ?: 'Gagal menghubungkan Google OAuth.',
            ], 422);
        }

        $payload = $response->json();
        $existingGoogleOauthToken = $this->tokenForAuthenticatedUser(includeRevoked: true);

        GoogleOauthToken::query()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'provider' => self::Provider,
            ],
            [
                'scopes' => $payload['scope'] ?? null,
                'access_token' => $payload['access_token'] ?? null,
                'refresh_token' => $payload['refresh_token'] ?? $existingGoogleOauthToken?->refresh_token,
                'token_type' => $payload['token_type'] ?? null,
                'expires_at' => $this->expiresAt($payload['expires_in'] ?? null),
                'refresh_token_expires_at' => $this->expiresAt($payload['refresh_token_expires_in'] ?? null),
                'revoked_at' => null,
            ],
        );

        return response()->json([
            'success' => true,
            'access_token' => $payload['access_token'] ?? '',
            'expires_at' => $this->expiresAt($payload['expires_in'] ?? null)?->toIso8601String(),
        ]);
    }

    private function tokenForAuthenticatedUser(bool $includeRevoked = false): ?GoogleOauthToken
    {
        $query = GoogleOauthToken::query()
            ->where('user_id', Auth::id())
            ->where('provider', self::Provider);

        if (! $includeRevoked) {
            $query->whereNull('revoked_at');
        }

        return $query->first();
    }

    private function refreshGoogleToken(GoogleOauthToken $googleOauthToken): Response
    {
        return Http::asForm()
            ->timeout(10)
            ->post(self::TokenEndpoint, [
                'client_id' => $this->googleClientId(),
                'client_secret' => $this->googleClientSecret(),
                'refresh_token' => $googleOauthToken->refresh_token,
                'grant_type' => 'refresh_token',
            ]);
    }

    private function authorizationRequiredResponse(string $message = 'Google Drive belum terhubung.'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'requires_authorization' => true,
            'message' => $message,
        ], 409);
    }

    private function expiresAt(mixed $expiresIn): ?CarbonImmutable
    {
        if (! is_numeric($expiresIn)) {
            return null;
        }

        return CarbonImmutable::now()->addSeconds(max((int) $expiresIn - 60, 0));
    }

    private function googleClientId(): ?string
    {
        return config('services.google.client_id');
    }

    private function googleClientSecret(): ?string
    {
        return config('services.google.client_secret');
    }
}
