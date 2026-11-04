<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Database\Factories\EmployeeMedicalCheckupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeMedicalCheckup extends Model
{
    public const RESULT_FIT = 'fit';

    public const RESULT_FIT_WITH_NOTE = 'fit_with_note';

    public const RESULT_UNFIT = 'unfit';

    use GeneratesCustomSequenceUuid;

    /** @use HasFactory<EmployeeMedicalCheckupFactory> */
    use HasFactory;

    protected $table = 'employee_medical_checkups';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $employeeMedicalCheckup): void {
            if (! is_string($employeeMedicalCheckup->id) || trim($employeeMedicalCheckup->id) === '') {
                $employeeMedicalCheckup->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public static function resultOptions(): array
    {
        return [
            self::RESULT_FIT => 'Fit',
            self::RESULT_FIT_WITH_NOTE => 'Fit with Note',
            self::RESULT_UNFIT => 'Unfit',
        ];
    }

    protected function casts(): array
    {
        return [
            'checkup_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
