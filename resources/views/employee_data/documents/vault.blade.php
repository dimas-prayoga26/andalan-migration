{{--
    E-Filing & Document Vault. Modes: "employee" (staff uploads own documents; HR-only blocks are hidden), "hr" (HR uploads
    every document) and "view" (read-only). Files are uploaded as soon as they are chosen when an upload URL is given.
    Params: documentVaultMode, employeeDocumentFiles (document key => files), employeeDocumentUploadUrl.
--}}
@inject('employeeDocumentCatalog', \App\Support\Employee\EmployeeDocumentCatalog::class)
@php
    $documentVaultMode = $documentVaultMode ?? 'employee';
    $isHrDocumentVault = $documentVaultMode === 'hr';
    $isViewOnlyDocumentVault = $documentVaultMode === 'view';
    $employeeDocumentFiles = $employeeDocumentFiles ?? [];
    $documentUploadUrl = $isViewOnlyDocumentVault ? null : ($employeeDocumentUploadUrl ?? null);
    $requiredDocumentKeys = $employeeDocumentCatalog->requiredEmployeeDocumentKeys();
    $uploadedRequiredDocumentCount = collect($requiredDocumentKeys)
        ->filter(fn (string $documentKey): bool => ! empty($employeeDocumentFiles[$documentKey]))
        ->count();
    $hasAllRequiredDocuments = $uploadedRequiredDocumentCount === count($requiredDocumentKeys);
    $documentBlocks = collect($employeeDocumentCatalog->blocks())
        ->reject(fn (array $documentBlock): bool => $documentVaultMode === 'employee' && $documentBlock['is_hr_only'])
        ->values();
@endphp
<div class="employee-document-vault js-employee-document-vault" data-upload-url="{{ $documentUploadUrl }}" data-required-total="{{ count($requiredDocumentKeys) }}">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Dokumen Digital</h4>
            <p class="mb-0 text-muted small">Format PDF, JPG, atau PNG. Maksimal 2 MB per file, khusus MCU maksimal 5 MB.</p>
        </div>
        <span @class(['badge light js-document-required-counter', 'badge-success' => $hasAllRequiredDocuments, 'badge-warning' => ! $hasAllRequiredDocuments])>{{ $uploadedRequiredDocumentCount }}/{{ count($requiredDocumentKeys) }} Dokumen Wajib Terunggah</span>
    </div>

    @foreach ($documentBlocks as $documentBlock)
        <div class="mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <h6 class="mb-0 fw-semibold">{{ $loop->iteration }}. {{ $documentBlock['title'] }}</h6>
                @if ($documentBlock['is_hr_only'])
                    <span class="badge badge-sm light badge-secondary"><i class="fa fa-lock me-1"></i>Khusus HR</span>
                @endif
            </div>
            <div class="row g-3">
                @foreach ($documentBlock['documents'] as $documentDefinition)
                    @php
                        $documentFiles = $employeeDocumentFiles[$documentDefinition['key']] ?? [];
                        $canUploadDocument = ! $isViewOnlyDocumentVault
                            && ($isHrDocumentVault || $documentDefinition['uploaded_by'] === 'employee');
                        $documentFormatLabel = collect($documentDefinition['accepted_extensions'])
                            ->reject(fn (string $extension): bool => $extension === 'jpeg')
                            ->map(fn (string $extension): string => strtoupper($extension))
                            ->implode(', ');
                        $firstDocumentFile = $documentFiles[0] ?? null;
                        $emptyDocumentLabel = $canUploadDocument || $isViewOnlyDocumentVault ? 'Belum diunggah' : 'Belum tersedia';
                    @endphp
                    <div class="col-xl-4 col-md-6">
                        <div class="employee-document-card h-100 js-document-card" data-document-key="{{ $documentDefinition['key'] }}" data-is-required="{{ $documentDefinition['is_required'] ? '1' : '0' }}" data-can-manage="{{ $canUploadDocument ? '1' : '0' }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <h6 class="mb-0">{{ $documentDefinition['label'] }}</h6>
                                @if ($documentDefinition['uploaded_by'] === 'hr')
                                    <span class="badge badge-sm light badge-secondary">Dari HR</span>
                                @elseif ($documentDefinition['is_required'])
                                    <span class="badge badge-sm employee-document-required-badge">Wajib</span>
                                @else
                                    <span class="badge badge-sm light badge-primary">Opsional</span>
                                @endif
                            </div>
                            <p class="small text-muted mb-3">{{ $documentDefinition['note'] ?? "\u{00A0}" }}</p>

                            @if ($canUploadDocument)
                                <label class="employee-document-dropzone js-document-dropzone mb-2" data-max-size-mb="{{ $documentDefinition['max_size_mb'] }}" data-accepted-extensions="{{ implode(',', $documentDefinition['accepted_extensions']) }}" data-format-label="{{ $documentFormatLabel }}" data-allows-multiple="{{ $documentDefinition['allows_multiple'] ? '1' : '0' }}">
                                    <input type="file" class="d-none" accept=".{{ implode(',.', $documentDefinition['accepted_extensions']) }}">
                                    <i class="fa fa-cloud-upload-alt"></i>
                                    <span class="d-block fw-semibold">{{ $documentDefinition['allows_multiple'] ? 'Klik atau seret file untuk menambah' : 'Klik atau seret file ke sini' }}</span>
                                    <span class="d-block small text-muted">{{ $documentFormatLabel }} · Maks {{ $documentDefinition['max_size_mb'] }} MB</span>
                                </label>
                            @endif

                            <div class="small js-document-message"></div>
                            <div class="small js-document-status">
                                @forelse ($documentFiles as $documentFile)
                                    <div class="d-flex align-items-center gap-2 employee-document-file">
                                        @if ($documentFile['url'] !== null)
                                            <a href="{{ $documentFile['url'] }}" target="_blank" rel="noopener noreferrer" class="text-truncate"><i class="fa fa-file me-1"></i>{{ $documentFile['name'] }}</a>
                                            <span class="text-muted text-nowrap">{{ $documentFile['size'] ?? '' }}</span>
                                            @if ($canUploadDocument)
                                                <a href="{{ $documentFile['download_url'] ?? $documentFile['url'] }}" class="ms-auto text-muted" title="Download" aria-label="Download"><i class="fa fa-download"></i></a>
                                            @endif
                                        @else
                                            <span class="text-truncate"><i class="fa fa-file me-1"></i>{{ $documentFile['name'] }}</span>
                                            <span class="employee-document-error text-nowrap">File not found</span>
                                        @endif
                                        @if ($canUploadDocument && filled($documentFile['delete_url'] ?? null))
                                            <button type="button" class="btn btn-link btn-sm p-0 employee-document-error js-document-delete" data-delete-url="{{ $documentFile['delete_url'] }}" data-file-name="{{ $documentFile['name'] }}" title="Hapus" aria-label="Hapus"><i class="fa fa-trash"></i></button>
                                        @endif
                                    </div>
                                @empty
                                    <span class="text-muted"><i @class(['fa fa-circle me-1', 'employee-document-required-dot' => $documentDefinition['is_required'], 'employee-document-optional-dot' => ! $documentDefinition['is_required']])></i>{{ $emptyDocumentLabel }}</span>
                                @endforelse
                            </div>

                            @unless ($canUploadDocument)
                                <div class="d-flex gap-2 mt-3">
                                    @if ($firstDocumentFile !== null && $firstDocumentFile['url'] !== null)
                                        <a href="{{ $firstDocumentFile['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye me-1"></i>Preview</a>
                                        <a href="{{ $firstDocumentFile['download_url'] ?? $firstDocumentFile['url'] }}" download class="btn btn-sm btn-outline-secondary"><i class="fa fa-download me-1"></i>Download</a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary" disabled><i class="fa fa-eye me-1"></i>Preview</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled><i class="fa fa-download me-1"></i>Download</button>
                                    @endif
                                </div>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
