<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Models\EmployeeCertification;
use App\Models\EmployeeDocument;
use App\Models\EmployeeLicense;
use App\Models\Position;
use App\Models\User;
use App\Support\Employee\EmployeeDocumentCatalog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Stores and lists the files of the E-Filing & Document Vault.
 * Files live on the private disk and are only served through routes that check access (own profile or HR).
 * Re-uploading a single-file document type replaces the previous file; the old record is soft deleted as history.
 * Certificate and license files uploaded before the vault existed are still listed, read-only, from their own tables.
 *
 * @phpstan-type VaultFile array{id: ?string, name: string, url: ?string, download_url: ?string, delete_url: ?string, size: ?string, icon: string, is_image: bool, uploaded_at: ?string}
 */
class EmployeeDocumentVaultService
{
    public const DISK = 'local';

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'];

    public function __construct(private readonly EmployeeDocumentCatalog $employeeDocumentCatalog) {}

    /**
     * HR-only documents (the "Khusus HR" blocks) are restricted to the Administrator and Super Administrator positions.
     */
    public function canAccessHrOnlyDocuments(?User $user): bool
    {
        return $user instanceof User
            && ($user->isSuperAdministrator() || ($user->employee?->hasAnyPositionSystemKey([Position::KEY_ADMINISTRATOR]) ?? false));
    }

    public function canAccessDocumentType(string $documentType, ?User $user): bool
    {
        return ! $this->employeeDocumentCatalog->isHrOnlyDocument($documentType) || $this->canAccessHrOnlyDocuments($user);
    }

    /**
     * @return array<string, list<VaultFile>>
     */
    public function filesFor(Employee $employee, bool $isSelfService): array
    {
        $vaultFiles = EmployeeDocument::query()
            ->where('employee_id', $employee->id)
            ->orderBy('uploaded_at')
            ->orderBy('created_at')
            ->get()
            ->groupBy('document_type')
            ->map(fn (Collection $documents): array => $documents
                ->map(fn (EmployeeDocument $document): array => $this->presentDocument($employee, $document, $isSelfService))
                ->values()
                ->all());

        return collect($this->legacyFilesFor($employee))
            ->mergeRecursive($vaultFiles)
            ->filter()
            ->all();
    }

    /**
     * The files of one document type plus the required-document summary, returned after an upload or delete.
     *
     * @return array{document_type: string, files: list<VaultFile>, required_uploaded_count: int, required_total: int, has_missing_required_documents: bool}
     */
    public function documentTypeState(Employee $employee, string $documentType, bool $isSelfService): array
    {
        $files = $this->filesFor($employee, $isSelfService);

        return [
            'document_type' => $documentType,
            'files' => $files[$documentType] ?? [],
        ] + $this->requiredDocumentSummary($files);
    }

    /**
     * @param  array<string, list<array<string, mixed>>>  $files
     * @return array{required_uploaded_count: int, required_total: int, has_missing_required_documents: bool}
     */
    public function requiredDocumentSummary(array $files): array
    {
        $requiredDocumentKeys = $this->employeeDocumentCatalog->requiredEmployeeDocumentKeys();
        $uploadedRequiredCount = collect($requiredDocumentKeys)
            ->filter(fn (string $documentKey): bool => ! empty($files[$documentKey]))
            ->count();

        return [
            'required_uploaded_count' => $uploadedRequiredCount,
            'required_total' => count($requiredDocumentKeys),
            'has_missing_required_documents' => $uploadedRequiredCount < count($requiredDocumentKeys),
        ];
    }

    public function store(Employee $employee, string $documentType, UploadedFile $file, ?User $uploader): EmployeeDocument
    {
        $documentDefinition = $this->employeeDocumentCatalog->find($documentType);
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension()));
        $storedPath = $file->storeAs(
            'employee-documents/'.$employee->id.'/'.$documentType,
            now()->format('YmdHis').'_'.Str::random(8).'.'.$extension,
            self::DISK,
        );

        if ($storedPath === false) {
            throw ValidationException::withMessages(['file' => 'Gagal menyimpan file.']);
        }

        return DB::transaction(function () use ($employee, $documentType, $documentDefinition, $file, $storedPath, $uploader): EmployeeDocument {
            if (! ($documentDefinition['allows_multiple'] ?? false)) {
                EmployeeDocument::query()
                    ->where('employee_id', $employee->id)
                    ->where('document_type', $documentType)
                    ->get()
                    ->each(fn (EmployeeDocument $replacedDocument): ?bool => $replacedDocument->delete());
            }

            return EmployeeDocument::query()->create([
                'employee_id' => $employee->id,
                'document_type' => $documentType,
                'file_path' => $storedPath,
                'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => $uploader?->id,
                'uploaded_at' => now(),
            ]);
        });
    }

    public function delete(EmployeeDocument $employeeDocument): void
    {
        $employeeDocument->delete();
    }

    public function fileResponse(EmployeeDocument $employeeDocument, bool $asDownload): StreamedResponse
    {
        $disk = Storage::disk(self::DISK);

        abort_unless($disk->exists($employeeDocument->file_path), 404);

        $fileName = $employeeDocument->original_name ?: basename($employeeDocument->file_path);

        if ($asDownload) {
            return $disk->download($employeeDocument->file_path, $fileName);
        }

        return $disk->response($employeeDocument->file_path, $fileName, array_filter([
            'Content-Type' => $employeeDocument->mime_type,
        ]));
    }

    /**
     * @return VaultFile
     */
    private function presentDocument(Employee $employee, EmployeeDocument $employeeDocument, bool $isSelfService): array
    {
        $extension = strtolower(pathinfo((string) ($employeeDocument->original_name ?: $employeeDocument->file_path), PATHINFO_EXTENSION));
        $routeParameters = $isSelfService
            ? ['employeeDocument' => $employeeDocument]
            : ['employee' => $employee, 'employeeDocument' => $employeeDocument];
        $routePrefix = $isSelfService ? 'profile.employee-documents.' : 'authorization.employee-documents.';
        $canDelete = ! $isSelfService
            || ($this->employeeDocumentCatalog->find($employeeDocument->document_type)['uploaded_by'] ?? 'hr') === 'employee';

        return [
            'id' => (string) $employeeDocument->id,
            'name' => $employeeDocument->original_name ?: basename($employeeDocument->file_path),
            'url' => route($routePrefix.'show', $routeParameters),
            'download_url' => route($routePrefix.'show', $routeParameters + ['download' => 1]),
            'delete_url' => $canDelete ? route($routePrefix.'destroy', $routeParameters) : null,
            'size' => $employeeDocument->file_size !== null ? Number::fileSize($employeeDocument->file_size, precision: 1) : null,
            'icon' => $this->iconFor($extension),
            'is_image' => in_array($extension, self::IMAGE_EXTENSIONS, true),
            'uploaded_at' => $employeeDocument->uploaded_at?->timezone('Asia/Jakarta')->format('d M Y H:i'),
        ];
    }

    /**
     * Certificate and license files stored on the public disk by the older certification/license records.
     *
     * @return array<string, list<VaultFile>>
     */
    private function legacyFilesFor(Employee $employee): array
    {
        return [
            'certificates' => EmployeeCertification::query()
                ->where('employee_id', $employee->id)
                ->whereNotNull('document_path')
                ->where('document_path', '<>', '')
                ->orderBy('sequence')
                ->get()
                ->map(fn (EmployeeCertification $certification): array => $this->presentLegacyFile($certification->certificate_name ?: 'Certificate', (string) $certification->document_path))
                ->all(),
            'licenses' => EmployeeLicense::query()
                ->where('employee_id', $employee->id)
                ->whereNotNull('document_path')
                ->where('document_path', '<>', '')
                ->orderBy('created_at')
                ->get()
                ->map(fn (EmployeeLicense $license): array => $this->presentLegacyFile($license->license_type ?: 'License', (string) $license->document_path))
                ->all(),
        ];
    }

    /**
     * @return VaultFile
     */
    private function presentLegacyFile(string $name, string $path): array
    {
        $path = trim($path);
        $extension = strtolower(pathinfo((string) (parse_url($path, PHP_URL_PATH) ?: $path), PATHINFO_EXTENSION));
        $url = null;
        $size = null;

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $url = $path;
        } else {
            $publicPath = ltrim($path, '/');
            $storagePath = Str::startsWith($publicPath, 'storage/') ? Str::after($publicPath, 'storage/') : $publicPath;

            if (Storage::disk('public')->exists($storagePath)) {
                $url = asset('storage/'.$storagePath);
                $size = Number::fileSize(Storage::disk('public')->size($storagePath), precision: 1);
            }
        }

        return [
            'id' => null,
            'name' => $name,
            'url' => $url,
            'download_url' => $url,
            'delete_url' => null,
            'size' => $size,
            'icon' => $this->iconFor($extension),
            'is_image' => $url !== null && in_array($extension, self::IMAGE_EXTENSIONS, true),
            'uploaded_at' => null,
        ];
    }

    private function iconFor(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'assets/images/files/pdf.avif',
            'csv', 'xls', 'xlsx' => 'assets/images/files/csv.avif',
            'ppt', 'pptx' => 'assets/images/files/ppt.avif',
            default => 'assets/images/files/folder.avif',
        };
    }
}
