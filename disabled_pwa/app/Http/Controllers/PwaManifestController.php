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
        $icon192Url = (string) ($brand['pwa_icon_192_url'] ?? $brand['logo_url'] ?? asset('images/favicon.png'));
        $icon512Url = (string) ($brand['pwa_icon_512_url'] ?? $icon192Url);

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
                        'src' => $icon192Url,
                        'sizes' => '192x192',
                        'type' => 'image/png',
                        'purpose' => 'any maskable',
                    ],
                    [
                        'src' => $icon512Url,
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'purpose' => 'any maskable',
                    ],
                ],
            ], 200, [], JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/manifest+json');
    }
}
