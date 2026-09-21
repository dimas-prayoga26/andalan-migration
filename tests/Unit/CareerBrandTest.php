<?php

namespace Tests\Unit;

use App\Support\CareerBrand;
use Tests\TestCase;

class CareerBrandTest extends TestCase
{
    public function test_null_blank_and_unknown_brand_key_use_rnb_brand(): void
    {
        config(['career_brands.default_brand' => 'tms']);

        foreach ([null, '', 'unknown-brand'] as $brandKey) {
            $brand = CareerBrand::brand($brandKey);

            $this->assertSame('rnb', $brand['key']);
            $this->assertSame('RNB Management', $brand['name']);
        }
    }

    public function test_known_brand_key_uses_matching_brand(): void
    {
        $brand = CareerBrand::brand('tms');

        $this->assertSame('tms', $brand['key']);
        $this->assertSame('TMS', $brand['name']);
    }

    public function test_null_brand_config_falls_back_to_rnb_brand(): void
    {
        config(['career_brands.brands.tms' => null]);

        $brand = CareerBrand::brand('tms');

        $this->assertSame('rnb', $brand['key']);
        $this->assertSame('RNB Management', $brand['name']);
    }
}
