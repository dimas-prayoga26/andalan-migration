<?php

namespace Tests\Unit;

use App\Http\Controllers\TalentAcquisitionController;
use App\Mail\ApplicantStatusMail;
use App\Models\Applicant;
use App\Support\CareerBrand;
use ReflectionMethod;
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
            $this->assertSame('hr@rnb.co.id', $brand['email']);
            $this->assertSame('rnb', $brand['mailer']);
        }
    }

    public function test_known_brand_key_uses_matching_brand(): void
    {
        $brand = CareerBrand::brand('tms');

        $this->assertSame('tms', $brand['key']);
        $this->assertSame('TMS', $brand['name']);
        $this->assertSame('tms', $brand['mailer']);
    }

    public function test_null_brand_config_falls_back_to_rnb_brand(): void
    {
        config(['career_brands.brands.tms' => null]);

        $brand = CareerBrand::brand('tms');

        $this->assertSame('rnb', $brand['key']);
        $this->assertSame('RNB Management', $brand['name']);
        $this->assertSame('rnb', $brand['mailer']);
    }

    public function test_partial_rnb_brand_config_is_completed_with_required_mail_fields(): void
    {
        config(['career_brands.brands.rnb' => [
            'name' => 'RNB Management',
        ]]);

        $brand = CareerBrand::brand(null);

        $this->assertSame('rnb', $brand['key']);
        $this->assertSame('RNB Management', $brand['name']);
        $this->assertSame('hr@rnb.co.id', $brand['email']);
        $this->assertSame('rnb', $brand['mailer']);
        $this->assertSame('https://rnb.co.id/', $brand['website']);
    }

    public function test_applicant_status_mail_handles_brand_without_email(): void
    {
        $mail = new ApplicantStatusMail(new Applicant([
            'full_name' => 'John Doe',
        ]), [
            'key' => 'rnb',
            'name' => 'RNB Management',
        ]);

        $envelope = $mail->envelope();

        $this->assertSame('Status Lamaran Anda: Submitted - RNB Management', $envelope->subject);
        $this->assertSame('hr@rnb.co.id', $envelope->from->address);
        $this->assertSame('RNB Management', $envelope->from->name);
    }

    public function test_missing_brand_mailer_config_falls_back_to_default_mailer(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.rnb' => null,
            'mail.mailers.smtp' => [
                'transport' => 'log',
            ],
        ]);

        $method = new ReflectionMethod(TalentAcquisitionController::class, 'mailerForBrand');

        $this->assertSame('smtp', $method->invoke(
            new TalentAcquisitionController,
            ['mailer' => 'rnb'],
        ));
    }
}
