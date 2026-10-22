<?php

namespace App\Support\Employee;

/**
 * The digital documents (E-Filing & Document Vault) an employee file is expected to hold, grouped by block.
 * Documents marked "hr" can only be uploaded by HR; employees may only preview and download them.
 *
 * @phpstan-type DocumentDefinition array{key: string, label: string, note: ?string, is_required: bool, uploaded_by: 'employee'|'hr', max_size_mb: int, accepted_extensions: list<string>}
 * @phpstan-type DocumentBlock array{key: string, title: string, is_hr_only: bool, documents: list<DocumentDefinition>}
 */
class EmployeeDocumentCatalog
{
    private const DEFAULT_MAX_SIZE_MB = 2;

    private const MCU_MAX_SIZE_MB = 5;

    private const DOCUMENT_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    private const PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * @return list<DocumentBlock>
     */
    public function blocks(): array
    {
        return [
            $this->block('identity', 'Dokumen Identitas & Legalitas Pribadi', [
                $this->document('ktp', 'Scan KTP', true),
                $this->document('family_card', 'Scan Kartu Keluarga (KK)', true, 'Untuk validasi BPJS & Pajak'),
                $this->document('npwp', 'Scan Kartu NPWP', true),
                $this->document('formal_photo', 'Foto Formal Terbaru', true, 'Untuk ID Card perusahaan', acceptedExtensions: self::PHOTO_EXTENSIONS),
                $this->document('bank_book', 'Scan Halaman Depan Buku Tabungan', true),
            ]),
            $this->block('academic', 'Dokumen Akademik & Kompetensi', [
                $this->document('last_diploma', 'Scan Ijazah Terakhir', true),
                $this->document('transcript', 'Scan Transkrip Nilai', true),
                $this->document('certificates', 'Scan Sertifikat Profesional/Pelatihan', false, 'Misal: K3 Umum, POP, POM'),
                $this->document('licenses', 'Scan Lisensi / SIO / SIM', false),
            ]),
            $this->block('work_medical', 'Dokumen Riwayat Pekerjaan & Medis', [
                $this->document('work_reference', 'Scan Paklaring / Surat Keterangan Kerja', false),
                $this->document('mcu', 'Hasil Medical Check-Up (MCU) Terakhir', false, maxSizeMb: self::MCU_MAX_SIZE_MB),
            ]),
            $this->block('company', 'Dokumen Perusahaan', [
                $this->document('employment_agreement', 'Scan Perjanjian Kerja (PKWT/PKWTT)', false, 'Yang sudah ditandatangani', uploadedBy: 'hr'),
                $this->document('nda', 'Scan Non-Disclosure Agreement (NDA)', false, uploadedBy: 'hr'),
                $this->document('asset_handover', 'Form Tanda Terima Aset (BAST Aset)', false, uploadedBy: 'hr'),
            ], isHrOnly: true),
        ];
    }

    /**
     * @return list<string>
     */
    public function requiredEmployeeDocumentKeys(): array
    {
        return collect($this->blocks())
            ->flatMap(fn (array $block): array => $block['documents'])
            ->filter(fn (array $document): bool => $document['is_required'] && $document['uploaded_by'] === 'employee')
            ->pluck('key')
            ->values()
            ->all();
    }

    /**
     * @param  list<DocumentDefinition>  $documents
     * @return DocumentBlock
     */
    private function block(string $key, string $title, array $documents, bool $isHrOnly = false): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'is_hr_only' => $isHrOnly,
            'documents' => $documents,
        ];
    }

    /**
     * @param  'employee'|'hr'  $uploadedBy
     * @param  list<string>  $acceptedExtensions
     * @return DocumentDefinition
     */
    private function document(
        string $key,
        string $label,
        bool $isRequired,
        ?string $note = null,
        string $uploadedBy = 'employee',
        int $maxSizeMb = self::DEFAULT_MAX_SIZE_MB,
        array $acceptedExtensions = self::DOCUMENT_EXTENSIONS,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'note' => $note,
            'is_required' => $isRequired,
            'uploaded_by' => $uploadedBy,
            'max_size_mb' => $maxSizeMb,
            'accepted_extensions' => $acceptedExtensions,
        ];
    }
}
