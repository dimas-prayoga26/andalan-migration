<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantDocument extends Model
{
    use GeneratesCustomSequenceUuid;

    public const TYPE_CV = 'cv';

    public const TYPE_PHOTO_PROFILE = 'photo_profile';

    public const TYPE_PHOTO = 'photo';

    public const TYPE_ASSESSMENT_TEST = 'assessment_test';

    protected $fillable = [
        'applicant_id',
        'applicant_upload_request_id',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_at',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $document): void {
            if (! is_string($document->id) || trim($document->id) === '') {
                $document->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'id');
    }

    public function uploadRequest(): BelongsTo
    {
        return $this->belongsTo(ApplicantUploadRequest::class, 'applicant_upload_request_id', 'id');
    }
}
