<?php

namespace App\Support;

use Illuminate\Support\Arr;

class CareerBrand
{
    /**
     * Resolve the branding data for an applicant's brand key, falling back
     * to the default brand when the key is null or unknown.
     *
     * @return array<string, mixed>
     */
    public static function brand(?string $brandKey): array
    {
        $defaultBrandKey = (string) config('career_brands.default_brand');
        $brandKey = $brandKey && Arr::has(config('career_brands.brands'), $brandKey)
            ? $brandKey
            : $defaultBrandKey;

        $brand = config("career_brands.brands.{$brandKey}", config("career_brands.brands.{$defaultBrandKey}"));

        return [
            ...$brand,
            'key' => $brandKey,
        ];
    }
}
