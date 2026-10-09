<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\ApplicantUploadRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicantAssessmentUploadLinkService
{
    /**
     * @return array{uploadRequest: ApplicantUploadRequest, url: string}
     */
    public function create(Applicant $applicant, ?User $creator): array
    {
        $uploadRequest = DB::transaction(function () use ($applicant, $creator): ApplicantUploadRequest {
            $applicant->uploadRequests()
                ->whereNull('used_at')
                ->whereNull('revoked_at')
                ->update([
                    'revoked_at' => now(),
                    'updated_at' => now(),
                ]);

            return $applicant->uploadRequests()->create([
                'token_hash' => $this->uniqueTokenHash(),
                'expires_at' => now()->addDays((int) config('assessment_upload.expires_in_days', 7)),
                'created_by' => $creator?->id,
            ]);
        });

        return [
            'uploadRequest' => $uploadRequest,
            'url' => $this->urlFor($applicant),
        ];
    }

    public function latestUsableRequest(Applicant $applicant): ?ApplicantUploadRequest
    {
        return $applicant->uploadRequests()
            ->whereNull('used_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function urlFor(Applicant $applicant): string
    {
        return rtrim($this->baseUrlFor($applicant), '/')
            .'/'.$applicant->getKey()
            .'/upload-file/verify-applicant';
    }

    private function baseUrlFor(Applicant $applicant): string
    {
        $brandKey = $this->brandKeyFor($applicant);

        return (string) config(
            "assessment_upload.brand_base_urls.{$brandKey}",
            config('assessment_upload.brand_base_urls.rnb'),
        );
    }

    private function brandKeyFor(Applicant $applicant): string
    {
        $brandKey = Str::lower(trim((string) $applicant->brand_key));
        $baseUrls = (array) config('assessment_upload.brand_base_urls', []);

        if ($brandKey !== '' && array_key_exists($brandKey, $baseUrls)) {
            return $brandKey;
        }

        return (string) config('assessment_upload.default_brand', 'rnb');
    }

    private function uniqueTokenHash(): string
    {
        do {
            $tokenHash = hash('sha256', Str::random(64));
        } while (ApplicantUploadRequest::query()->where('token_hash', $tokenHash)->exists());

        return $tokenHash;
    }
}
