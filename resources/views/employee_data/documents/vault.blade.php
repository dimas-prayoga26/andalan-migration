@inject('employeeDocumentCatalog', \App\Support\Employee\EmployeeDocumentCatalog::class)
@php
    $isHrDocumentVault = ($documentVaultMode ?? 'employee') === 'hr';
    $employeeDocumentFiles = $employeeDocumentFiles ?? [];
    $requiredDocumentKeys = $employeeDocumentCatalog->requiredEmployeeDocumentKeys();
    $uploadedRequiredDocumentCount = collect($requiredDocumentKeys)
        ->filter(fn (string $documentKey): bool => ! empty($employeeDocumentFiles[$documentKey]))
        ->count();
    $hasAllRequiredDocuments = $uploadedRequiredDocumentCount === count($requiredDocumentKeys);
@endphp
<div class="employee-document-vault">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Dokumen Digital</h4>
            <p class="mb-0 text-muted small">Format PDF, JPG, atau PNG. Maksimal 2 MB per file, khusus MCU maksimal 5 MB.</p>
        </div>
        <span @class(['badge light', 'badge-success' => $hasAllRequiredDocuments, 'badge-warning' => ! $hasAllRequiredDocuments])>{{ $uploadedRequiredDocumentCount }}/{{ count($requiredDocumentKeys) }} Dokumen Wajib Terunggah</span>
    </div>

    @foreach ($employeeDocumentCatalog->blocks() as $documentBlock)
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
                        $canUploadDocument = $isHrDocumentVault || $documentDefinition['uploaded_by'] === 'employee';
                        $documentFormatLabel = collect($documentDefinition['accepted_extensions'])
                            ->reject(fn (string $extension): bool => $extension === 'jpeg')
                            ->map(fn (string $extension): string => strtoupper($extension))
                            ->implode(', ');
                        $firstDocumentFile = $documentFiles[0] ?? null;
                    @endphp
                    <div class="col-xl-4 col-md-6">
                        <div class="employee-document-card h-100" data-document-key="{{ $documentDefinition['key'] }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <h6 class="mb-0">{{ $documentDefinition['label'] }}</h6>
                                @if ($documentDefinition['uploaded_by'] === 'hr')
                                    <span class="badge badge-sm light badge-secondary">Dari HR</span>
                                @elseif ($documentDefinition['is_required'])
                                    <span class="badge badge-sm light badge-danger">Wajib</span>
                                @else
                                    <span class="badge badge-sm light badge-primary">Opsional</span>
                                @endif
                            </div>
                            <p class="small text-muted mb-3">{{ $documentDefinition['note'] ?? "\u{00A0}" }}</p>

                            @if ($canUploadDocument)
                                <label class="employee-document-dropzone js-document-dropzone mb-2" data-max-size-mb="{{ $documentDefinition['max_size_mb'] }}" data-accepted-extensions="{{ implode(',', $documentDefinition['accepted_extensions']) }}" data-format-label="{{ $documentFormatLabel }}">
                                    <input type="file" class="d-none" accept=".{{ implode(',.', $documentDefinition['accepted_extensions']) }}">
                                    <i class="fa fa-cloud-upload-alt"></i>
                                    <span class="d-block fw-semibold">Klik atau seret file ke sini</span>
                                    <span class="d-block small text-muted">{{ $documentFormatLabel }} · Maks {{ $documentDefinition['max_size_mb'] }} MB</span>
                                </label>
                            @endif

                            <div class="small js-document-status">
                                @forelse ($documentFiles as $documentFile)
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($documentFile['url'] !== null)
                                            <a href="{{ $documentFile['url'] }}" target="_blank" rel="noopener noreferrer" class="text-truncate"><i class="fa fa-file me-1"></i>{{ $documentFile['name'] }}</a>
                                            <span class="text-muted text-nowrap">{{ $documentFile['size'] ?? '' }}</span>
                                        @else
                                            <span class="text-truncate"><i class="fa fa-file me-1"></i>{{ $documentFile['name'] }}</span>
                                            <span class="text-danger text-nowrap">File not found</span>
                                        @endif
                                    </div>
                                @empty
                                    <span class="text-muted"><i @class(['fa fa-circle me-1', 'text-danger' => $documentDefinition['is_required'], 'text-secondary' => ! $documentDefinition['is_required']])></i>{{ $canUploadDocument ? 'Belum diunggah' : 'Belum tersedia' }}</span>
                                @endforelse
                            </div>

                            @unless ($canUploadDocument)
                                <div class="d-flex gap-2 mt-3">
                                    @if ($firstDocumentFile !== null && $firstDocumentFile['url'] !== null)
                                        <a href="{{ $firstDocumentFile['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye me-1"></i>Preview</a>
                                        <a href="{{ $firstDocumentFile['url'] }}" download class="btn btn-sm btn-outline-secondary"><i class="fa fa-download me-1"></i>Download</a>
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
