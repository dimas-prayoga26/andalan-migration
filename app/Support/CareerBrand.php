<?php

namespace App\Support;

use Illuminate\Support\Arr;

class CareerBrand
{
    private const FALLBACK_BRAND_KEY = 'rnb';

    /**
     * @return array<string, string>
     */
    public static function fallbackBrand(): array
    {
        return [
            'name' => 'RNB Management',
            'mailer' => 'rnb',
            'logo_url' => 'https://raw.githubusercontent.com/dimas-prayoga26/web_careers_company/main/public/images/Logo%20RNB.png',
            'website' => 'https://rnb.co.id/',
            'email' => 'hr@rnb.co.id',
            'primary_color' => '#b01020',
            'accent_color' => '#8c6d00',
            'header_background_color' => '#fff8e6',
            'header_text_color' => '#172033',
        ];
    }

    /**
     * Resolve the branding data for an applicant's brand key, falling back
     * to RNB when the key is null, blank, or unknown.
     *
     * @return array<string, mixed>
     */
    public static function brand(?string $brandKey): array
    {
        $fallbackBrandKey = self::FALLBACK_BRAND_KEY;
        $fallbackBrand = self::completeBrand((array) config("career_brands.brands.{$fallbackBrandKey}", []));
        $brandKey = filled($brandKey) && Arr::has(config('career_brands.brands'), $brandKey)
            ? $brandKey
            : $fallbackBrandKey;

        $brand = config("career_brands.brands.{$brandKey}", $fallbackBrand);

        if (! is_array($brand)) {
            $brandKey = $fallbackBrandKey;
            $brand = $fallbackBrand;
        }

        return [
            ...self::completeBrand($brand),
            'key' => $brandKey,
        ];
    }

    /**
     * @param  array<string, mixed>  $brand
     * @return array<string, mixed>
     */
    private static function completeBrand(array $brand): array
    {
        $filledBrand = array_filter($brand, filled(...));

        return [
            ...self::fallbackBrand(),
            ...$filledBrand,
        ];
    }
}
