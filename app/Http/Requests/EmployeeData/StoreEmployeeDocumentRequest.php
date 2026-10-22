<?php

namespace App\Http\Requests\EmployeeData;

use App\Support\Employee\EmployeeDocumentCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A file uploaded to the E-Filing & Document Vault. Format and size limits follow EmployeeDocumentCatalog.
 * Which document types the uploader may use (own documents vs HR documents) is checked by the controller.
 */
class StoreEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $catalog = app(EmployeeDocumentCatalog::class);
        $documentDefinition = $catalog->find((string) $this->input('document_type'));
        $fileRules = ['required', 'file'];

        if ($documentDefinition !== null) {
            $fileRules[] = 'mimes:'.implode(',', $documentDefinition['accepted_extensions']);
            $fileRules[] = 'max:'.($documentDefinition['max_size_mb'] * 1024);
        }

        return [
            'document_type' => ['required', 'string', Rule::in($catalog->documentKeys())],
            'file' => $fileRules,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $documentDefinition = app(EmployeeDocumentCatalog::class)->find((string) $this->input('document_type'));

        return [
            'document_type.required' => 'Jenis dokumen wajib dipilih.',
            'document_type.in' => 'Jenis dokumen tidak valid.',
            'file.required' => 'File wajib diunggah.',
            'file.file' => 'File gagal diunggah.',
            'file.uploaded' => 'File gagal diunggah.',
            'file.mimes' => 'Format file tidak didukung. Gunakan '.strtoupper(implode(', ', $documentDefinition['accepted_extensions'] ?? ['pdf'])).'.',
            'file.max' => 'Ukuran file maksimal '.($documentDefinition['max_size_mb'] ?? 2).' MB.',
        ];
    }
}
