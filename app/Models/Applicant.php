<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Applicant extends Model
{
    use GeneratesCustomSequenceUuid;
    use SoftDeletes;

    private const LEGACY_CV_BASE_URL = 'https://rnbmanagement.com/domain-rnbmanagementcom/subdomain/careers/files/cv/';

    protected $table = 'applicants';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'legacy_created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $applicant): void {
            if (! is_string($applicant->id) || trim($applicant->id) === '') {
                $applicant->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id', 'id');
    }

    public function applicantStatus(): BelongsTo
    {
        return $this->belongsTo(ApplicantStatus::class, 'applicant_status_id', 'id');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(MetaDataGender::class, 'gender_id', 'id');
    }

    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(MetaDataMaritalStatus::class, 'marital_status_id', 'id');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(ApplicantEducation::class, 'applicant_id', 'id')->orderBy('sequence');
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(ApplicantWorkExperience::class, 'applicant_id', 'id')->orderBy('sequence');
    }

    public function statusLabel(): string
    {
        return (string) ($this->applicantStatus?->name ?? 'Submitted');
    }

    public function whatsAppUrl(): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $this->phone);

        if ($phone === '') {
            return null;
        }

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62'.$phone;
        }

        return 'https://wa.me/'.$phone;
    }

    public function cvDownloadUrl(): ?string
    {
        $cvFile = trim((string) $this->cv);

        if ($cvFile === '') {
            return null;
        }

        if (Str::startsWith($cvFile, ['http://', 'https://'])) {
            return $cvFile;
        }

        return self::LEGACY_CV_BASE_URL.rawurlencode($cvFile);
    }

    /**
     * @return array<int, string>
     */
    public function portfolioLinks(): array
    {
        $portfolio = trim((string) $this->portfolio_web_address);

        if ($portfolio === '') {
            return [];
        }

        preg_match_all('/https?:\/\/[^\s,]+|www\.[^\s,]+/i', $portfolio, $matches);

        return collect($matches[0] ?? [])
            ->map(static function (string $url): string {
                return str_starts_with(strtolower($url), 'http') ? $url : 'https://'.$url;
            })
            ->unique()
            ->values()
            ->all();
    }
}
