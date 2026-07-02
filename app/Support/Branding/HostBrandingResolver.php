<?php

namespace App\Support\Branding;

class HostBrandingResolver
{
    /**
     * @return array{name: string, logo_path: string, logo_url: string}
     */
    public function resolve(?string $host = null): array
    {
        $normalizedHost = $this->normalizeHost($host ?? request()->getHost());
        $hosts = config('branding.hosts', []);
        $brand = is_array($hosts) && array_key_exists($normalizedHost, $hosts)
            ? $hosts[$normalizedHost]
            : config('branding.default', []);
        $logoPath = (string) ($brand['logo'] ?? 'images/images.png');

        return [
            'name' => (string) ($brand['name'] ?? 'Andalan Bersama Group'),
            'logo_path' => $logoPath,
            'logo_url' => asset($logoPath),
        ];
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
