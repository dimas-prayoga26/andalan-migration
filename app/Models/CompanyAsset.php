<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Database\Factories\CompanyAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An asset owned by a company; employee_id is the employee currently holding it, handed over on handover_date.
 */
class CompanyAsset extends Model
{
    public const STATUS_AVAILABLE = 'available';

    public const STATUS_ASSIGNED = 'assigned';

    use GeneratesCustomSequenceUuid;

    /** @use HasFactory<CompanyAssetFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'company_assets';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $attributes = [
        'status' => self::STATUS_AVAILABLE,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $companyAsset): void {
            if (! is_string($companyAsset->id) || trim($companyAsset->id) === '') {
                $companyAsset->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'handover_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function holder(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
