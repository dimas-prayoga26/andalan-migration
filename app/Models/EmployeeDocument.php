<?php

namespace App\Models;

use App\Models\Concerns\GeneratesCustomSequenceUuid;
use Database\Factories\EmployeeDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A file in the employee's E-Filing & Document Vault; document_type is a key from EmployeeDocumentCatalog.
 */
class EmployeeDocument extends Model
{
    use GeneratesCustomSequenceUuid;

    /** @use HasFactory<EmployeeDocumentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'employee_documents';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $employeeDocument): void {
            if (! is_string($employeeDocument->id) || trim($employeeDocument->id) === '') {
                $employeeDocument->id = static::generateCustomSequenceUuid('id');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }
}
