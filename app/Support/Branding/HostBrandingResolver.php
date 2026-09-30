<?php

namespace App\Support\Branding;

class HostBrandingResolver
{
    /**
     * @return array{name: string, logo_path: string, logo_url: string}
     */
    public function resolve(?string $host = null): array
    {
        $resolvedHost = trim((string) $host) !== '' ? (string) $host : $this->currentHost();
        $normalizedHost = $this->normalizeHost($resolvedHost);
        $hosts = config('branding.hosts', []);
        $brand = is_array($hosts) && array_key_exists($normalizedHost, $hosts)
            ? $hosts[$normalizedHost]
            : config('branding.default', []);
        $logoPath = (string) ($brand['logo'] ?? 'images/images.png');

        return [
            'name' => (string) ($brand['name'] ?? 'Andalan Bersama Group'),
            'logo_path' => $logoPath,
            'logo_url' => $this->assetUrl($logoPath, $normalizedHost),
        ];
    }

    private function currentHost(): string
    {
        if (app()->runningInConsole()) {
            return (string) parse_url((string) config('app.url'), PHP_URL_HOST);
        }

        return request()->getHost();
    }

    private function assetUrl(string $path, string $host): string
    {
        $path = ltrim($path, '/');

        if (! app()->runningInConsole() && $this->normalizeHost(request()->getHost()) === $host) {
            return asset($path);
        }

        return rtrim($this->baseUrlForHost($host), '/').'/'.$path;
    }

    private function baseUrlForHost(string $host): string
    {
        $appUrl = (string) config('app.url');
        $appHost = $this->normalizeHost((string) parse_url($appUrl, PHP_URL_HOST));
        $scheme = (string) (parse_url($appUrl, PHP_URL_SCHEME) ?: 'https');

        if ($appHost !== $host && ! $this->isLocalHost($host)) {
            $scheme = 'https';
        }

        return $scheme.'://'.$host;
    }

    private function isLocalHost(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.test');
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
