<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\ApplicantTechnicalTestUploadRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Upload links for technical test results, served by the Hasil-technical-test project
 * on technical-test.{brand}. Kept separate from the assessment test upload flow.
 */
class ApplicantTechnicalTestUploadLinkService
{
    /**
     * @return array{uploadRequest: ApplicantTechnicalTestUploadRequest, url: string}
     */
    public function create(Applicant $applicant, ?User $creator): array
    {
        $uploadRequest = DB::transaction(function () use ($applicant, $creator): ApplicantTechnicalTestUploadRequest {
            $applicant->technicalTestUploadRequests()
                ->whereNull('used_at')
                ->whereNull('revoked_at')
                ->update([
                    'revoked_at' => now(),
                    'updated_at' => now(),
                ]);

            return $applicant->technicalTestUploadRequests()->create([
                'token_hash' => $this->uniqueTokenHash(),
                'expires_at' => now()->addDays((int) config('technical_test_upload.expires_in_days', 7)),
                'created_by' => $creator?->id,
            ]);
        });

        return [
            'uploadRequest' => $uploadRequest,
            'url' => $this->urlFor($applicant),
        ];
    }

    public function latestUsableRequest(Applicant $applicant): ?ApplicantTechnicalTestUploadRequest
    {
        return $applicant->technicalTestUploadRequests()
            ->whereNull('used_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function revokeLatestUsableRequest(Applicant $applicant): bool
    {
        $uploadRequest = $this->latestUsableRequest($applicant);

        if (! $uploadRequest instanceof ApplicantTechnicalTestUploadRequest) {
            return false;
        }

        return $uploadRequest->forceFill([
            'revoked_at' => now(),
        ])->save();
    }

    public function urlFor(Applicant $applicant): string
    {
        return rtrim($this->baseUrlFor($applicant), '/')
            .'/'.$applicant->getKey()
            .'/upload-file/verify-applicant';
    }

    public function technicalTestFileUrlFor(Applicant $applicant): string
    {
        return rtrim($this->baseUrlFor($applicant), '/')
            .'/'.$applicant->getKey()
            .'/technical-test-file';
    }

    private function baseUrlFor(Applicant $applicant): string
    {
        $brandKey = $this->brandKeyFor($applicant);

        return (string) config(
            "technical_test_upload.brand_base_urls.{$brandKey}",
            config('technical_test_upload.brand_base_urls.rnb'),
        );
    }

    private function brandKeyFor(Applicant $applicant): string
    {
        $brandKey = Str::lower(trim((string) $applicant->brand_key));
        $baseUrls = (array) config('technical_test_upload.brand_base_urls', []);

        if ($brandKey !== '' && array_key_exists($brandKey, $baseUrls)) {
            return $brandKey;
        }

        return (string) config('technical_test_upload.default_brand', 'rnb');
    }

    private function uniqueTokenHash(): string
    {
        do {
            $tokenHash = hash('sha256', Str::random(64));
        } while (ApplicantTechnicalTestUploadRequest::query()->where('token_hash', $tokenHash)->exists());

        return $tokenHash;
    }
}
