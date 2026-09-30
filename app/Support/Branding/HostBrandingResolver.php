<?php

namespace App\Support\Branding;

class HostBrandingResolver
{
    /**
     * @return array{name: string, logo_path: string, logo_url: string, pwa_icon_192_path: string, pwa_icon_192_url: string, pwa_icon_512_path: string, pwa_icon_512_url: string}
     */
    public function resolve(?string $host = null): array
    {
        $normalizedHost = $this->normalizeHost($host ?? $this->currentHost());
        $hosts = config('branding.hosts', []);
        $brand = is_array($hosts) && array_key_exists($normalizedHost, $hosts)
            ? $hosts[$normalizedHost]
            : config('branding.default', []);
        $logoPath = (string) ($brand['logo'] ?? 'images/images.png');
        $pwaIcon192Path = (string) ($brand['pwa_icon_192'] ?? $logoPath);
        $pwaIcon512Path = (string) ($brand['pwa_icon_512'] ?? $pwaIcon192Path);

        return [
            'name' => (string) ($brand['name'] ?? 'Andalan Bersama Group'),
            'logo_path' => $logoPath,
            'logo_url' => asset($logoPath),
            'pwa_icon_192_path' => $pwaIcon192Path,
            'pwa_icon_192_url' => asset($pwaIcon192Path),
            'pwa_icon_512_path' => $pwaIcon512Path,
            'pwa_icon_512_url' => asset($pwaIcon512Path),
        ];
    }

    private function currentHost(): string
    {
        if (app()->runningInConsole()) {
            return (string) parse_url((string) config('app.url'), PHP_URL_HOST);
        }

        return request()->getHost();
    }

    private function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));

        if (str_contains($host, ':')) {
            $host = explode(':', $host, 2)[0];
        }

        return $host;
    }
}
