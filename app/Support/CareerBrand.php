<?php

namespace App\Support;

use Illuminate\Support\Arr;

class CareerBrand
{
    private const FALLBACK_BRAND_KEY = 'rnb';

    /**
     * Resolve the branding data for an applicant's brand key, falling back
     * to RNB when the key is null, blank, or unknown.
     *
     * @return array<string, mixed>
     */
    public static function brand(?string $brandKey): array
    {
        $fallbackBrandKey = self::FALLBACK_BRAND_KEY;
        $brandKey = filled($brandKey) && Arr::has(config('career_brands.brands'), $brandKey)
            ? $brandKey
            : $fallbackBrandKey;

        $brand = config("career_brands.brands.{$brandKey}", config("career_brands.brands.{$fallbackBrandKey}"));

        if (! is_array($brand)) {
            $brandKey = $fallbackBrandKey;
            $brand = (array) config("career_brands.brands.{$fallbackBrandKey}", []);
        }

        return [
            ...$brand,
            'key' => $brandKey,
        ];
    }
}
