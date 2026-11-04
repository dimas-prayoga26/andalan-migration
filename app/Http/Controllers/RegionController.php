<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Indonesian administrative regions (province → regency → district → village) for address inputs, proxied from wilayah.id.
 * Successful lookups are cached for a day because region data rarely changes.
 */
class RegionController extends Controller
{
    private const WILAYAH_API_BASE_URL = 'https://wilayah.id/api';

    private const CACHE_TTL_HOURS = 24;

    public function provinces(): JsonResponse
    {
        return $this->regionResponse('/provinces.json');
    }

    public function regencies(string $provinceCode): JsonResponse
    {
        return $this->regionResponse('/regencies/'.$provinceCode.'.json');
    }

    public function districts(string $regencyCode): JsonResponse
    {
        return $this->regionResponse('/districts/'.$regencyCode.'.json');
    }

    public function villages(string $districtCode): JsonResponse
    {
        return $this->regionResponse('/villages/'.$districtCode.'.json');
    }

    private function regionResponse(string $path): JsonResponse
    {
        $cacheKey = 'wilayah-regions:'.$path;
        $regions = Cache::get($cacheKey);

        if (! is_array($regions)) {
            try {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->get(self::WILAYAH_API_BASE_URL.$path);
            } catch (Throwable $exception) {
                report($exception);

                return $this->failedRegionResponse();
            }

            if ($response->failed()) {
                return $this->failedRegionResponse();
            }

            $regions = collect((array) $response->json('data', []))
                ->map(fn (mixed $region): array => [
                    'code' => trim((string) (is_array($region) ? ($region['code'] ?? '') : '')),
                    'name' => trim((string) (is_array($region) ? ($region['name'] ?? '') : '')),
                ])
                ->filter(fn (array $region): bool => $region['code'] !== '' && $region['name'] !== '')
                ->values()
                ->all();

            Cache::put($cacheKey, $regions, now()->addHours(self::CACHE_TTL_HOURS));
        }

        return response()->json(['data' => $regions]);
    }

    private function failedRegionResponse(): JsonResponse
    {
        return response()->json([
            'data' => [],
            'message' => 'Gagal memuat data wilayah.',
        ], 502);
    }
}
