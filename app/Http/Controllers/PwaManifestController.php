<?php

namespace App\Http\Controllers;

use App\Support\Branding\HostBrandingResolver;
use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function __invoke(HostBrandingResolver $brandingResolver): JsonResponse
    {
        $brand = $brandingResolver->resolve();
        $appName = trim((string) $brand['name']) !== '' ? (string) $brand['name'] : 'SIAP';
        $iconUrl = (string) ($brand['logo_url'] ?? asset('images/favicon.png'));

        return response()
            ->json([
                'name' => "{$appName} - SIAP",
                'short_name' => $appName,
                'description' => "SIAP {$appName}",
                'start_url' => url('/?source=pwa'),
                'scope' => url('/'),
                'display' => 'standalone',
                'orientation' => 'portrait-primary',
                'background_color' => '#ffffff',
                'theme_color' => '#2846c7',
                'icons' => [
                    [
                        'src' => $iconUrl,
                        'sizes' => '192x192',
                        'type' => 'image/png',
                        'purpose' => 'any',
                    ],
                    [
                        'src' => $iconUrl,
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'purpose' => 'any',
                    ],
                ],
            ], 200, [], JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/manifest+json');
    }
}
