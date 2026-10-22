@php
    $employmentDetails = $employeeEmploymentDetails ?? [];
    $employmentValue = static fn (string $key): ?string => $employmentDetails[$key] ?? null;
    $employmentInputValues = $wizardValues['employment'];
    $employmentSaveUrl = $wizardStepUrls['employment'] ?? null;
    $placementFields = [
        'company' => 'Entitas Perusahaan',
        'employee_code' => 'Employee Code',
        'department' => 'Departemen',
        'position' => 'Jabatan',
        'supervisor' => 'PIC',
        'work_location' => 'Lokasi Penempatan (HO/Site)',
    ];
    $contractFields = [
        'employment_status' => 'Status Kepegawaian',
        'join_date' => 'Tanggal Bergabung',
        'contract_end_date' => 'Tanggal Berakhir Kontrak',
        'work_schedule' => 'Skema Waktu Kerja',
    ];
    $compensationFields = [
        'base_salary' => 'Gaji Pokok',
        'allowance' => 'Tunjangan',
    ];
    $mcuRecords = $employmentDetails['mcu_records'] ?? [];
    $companyAssets = $employmentDetails['assets'] ?? [];
@endphp
<div id="wizard_Finish" class="tab-pane" role="tabpanel">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-3">
        <h4 class="mb-0 fw-bold">Employment &amp; Contract Details</h4>
    </div>
    <div class="row">
        <h6 class="mb-1 fw-semibold">1. Penempatan:</h6>
        @unless ($isEmploymentStepReadOnly)
            <p class="text-muted small mb-3">Penempatan diatur pada tab <strong>1. Account &amp; Placement</strong>.</p>
        @endunless
        @foreach ($placementFields as $fieldKey => $fieldLabel)
            <div class="col-lg-4 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">{{ $fieldLabel }}</label>
                    <input type="text" class="form-control" value="{{ $employmentValue($fieldKey) ?? '-' }}" readonly disabled>
                </div>
            </div>
        @endforeach
    </div>
    <hr>

    @if ($isEmploymentStepReadOnly)
        <div class="row">
            <h6 class="mb-3 fw-semibold">2. Kontrak &amp; Operasional:</h6>
            @foreach ($contractFields as $fieldKey => $fieldLabel)
                <div class="col-lg-3 mb-2">
                    <div class="mb-3">
                        <label class="text-label form-label">{{ $fieldLabel }}</label>
                        <input type="text" class="form-control" value="{{ $employmentValue($fieldKey) ?? '-' }}" readonly disabled>
                    </div>
                </div>
            @endforeach
        </div>
        <hr>

        <div class="row">
            <h6 class="mb-3 fw-semibold">3. Kompensasi:</h6>
            @foreach ($compensationFields as $fieldKey => $fieldLabel)
                <div class="col-lg-6 mb-2">
                    <div class="mb-3">
                        <label class="text-label form-label">{{ $fieldLabel }}</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" value="{{ $employmentValue($fieldKey) ?? '-' }}" readonly disabled>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <hr>

        <div class="row">
            <h6 class="mb-3 fw-semibold">4. Riwayat MCU:</h6>
            <div class="col-12 mb-3">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal MCU</th>
                                <th>Rumah Sakit / Klinik</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mcuRecords as $mcuRecord)
                                <tr>
                                    <td>{{ $mcuRecord['date'] }}</td>
                                    <td>{{ $mcuRecord['clinic'] }}</td>
                                    <td>{{ $mcuRecord['status'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <hr>

        <div class="row">
            <h6 class="mb-3 fw-semibold">5. Aset Perusahaan yang Dipegang:</h6>
            <div class="col-12 mb-3">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Nama Aset</th>
                                <th>Merk / Spesifikasi</th>
                                <th>Nomor Seri</th>
                                <th>Tanggal Serah Terima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($companyAssets as $companyAsset)
                                <tr>
                                    <td>{{ $companyAsset['name'] }}</td>
                                    <td>{{ $companyAsset['specification'] }}</td>
                                    <td>{{ $companyAsset['serial_number'] }}</td>
                                    <td>{{ $companyAsset['handover_date'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <form class="js-employee-data-step-form" data-employee-data-step="employment" data-save-url="{{ $employmentSaveUrl }}" novalidate autocomplete="off">
            <fieldset @disabled(blank($employmentSaveUrl))>
                <div class="row">
                    <h6 class="mb-3 fw-semibold">2. Kontrak &amp; Operasional:</h6>
                    @include('employee_data.wizard.field', ['name' => 'employment_status', 'label' => 'Status Kepegawaian', 'type' => 'select', 'placeholder' => 'Pilih status', 'options' => array_combine(\App\Support\Employee\EmployeeEmploymentDetailResolver::EMPLOYMENT_STATUS_OPTIONS, \App\Support\Employee\EmployeeEmploymentDetailResolver::EMPLOYMENT_STATUS_OPTIONS), 'value' => $employmentInputValues['employment_status'] ?? null, 'attributes' => 'data-employment-status-select'])
                    @include('employee_data.wizard.field', ['name' => 'join_date', 'label' => 'Tanggal Bergabung', 'type' => 'date', 'value' => $employmentInputValues['join_date'] ?? null])
                    @include('employee_data.wizard.field', ['name' => 'end_date', 'label' => 'Tanggal Berakhir Kontrak / Probation', 'type' => 'date', 'value' => $employmentInputValues['end_date'] ?? null, 'help' => 'Wajib untuk PKWT dan Probation.', 'attributes' => 'data-employment-end-date'])
                    <div class="col-lg-3 mb-2">
                        <div class="mb-3">
                            <label class="text-label form-label">Skema Waktu Kerja</label>
                            <input type="text" class="form-control" value="{{ $employmentValue('work_schedule') ?? '-' }}" readonly disabled>
                            <small class="text-muted">Mengikuti Rules of Attendance lokasi penempatan.</small>
                        </div>
                    </div>
                </div>
                <hr>

                <div class="row">
                    <h6 class="mb-3 fw-semibold">3. Kompensasi:</h6>
                    @foreach ($compensationFields as $fieldKey => $fieldLabel)
                        @include('employee_data.wizard.field', ['name' => $fieldKey, 'label' => $fieldLabel, 'column' => 'col-lg-6', 'value' => $employmentInputValues[$fieldKey] ?? null, 'inputGroupText' => 'Rp', 'placeholder' => $fieldLabel, 'attributes' => 'inputmode="numeric" data-currency-input'])
                    @endforeach
                </div>
                <hr>

                <div class="row">
                    <h6 class="mb-3 fw-semibold">4. Riwayat MCU:</h6>
                    <div class="col-12">
                        @include('employee_data.wizard.repeatable', [
                            'group' => 'medical_checkups',
                            'rows' => $employmentInputValues['medical_checkups'] ?? [],
                            'fields' => [
                                ['name' => 'checkup_date', 'label' => 'Tanggal MCU', 'type' => 'date'],
                                ['name' => 'clinic_name', 'label' => 'Rumah Sakit / Klinik', 'column' => 'col-lg-4'],
                                ['name' => 'result_status', 'label' => 'Status', 'type' => 'select', 'options' => \App\Models\EmployeeMedicalCheckup::resultOptions()],
                            ],
                        ])
                    </div>
                </div>
                <hr>

                <div class="row">
                    <h6 class="mb-3 fw-semibold">5. Aset Perusahaan yang Dipegang:</h6>
                    <div class="col-12">
                        @include('employee_data.wizard.repeatable', [
                            'group' => 'assets',
                            'rows' => $employmentInputValues['assets'] ?? [],
                            'fields' => [
                                ['name' => 'name', 'label' => 'Nama Aset', 'placeholder' => 'Contoh: Laptop'],
                                ['name' => 'specification', 'label' => 'Merk / Spesifikasi'],
                                ['name' => 'serial_number', 'label' => 'Nomor Seri', 'column' => 'col-lg-2'],
                                ['name' => 'handover_date', 'label' => 'Tanggal Serah Terima', 'type' => 'date', 'column' => 'col-lg-2'],
                            ],
                        ])
                        <p class="text-muted small mb-0">Aset yang dihapus dari daftar ini dikembalikan ke inventaris perusahaan.</p>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-success js-employee-data-step-submit">
                        <i class="fa-regular fa-floppy-disk me-1"></i>Simpan Step 5
                    </button>
                </div>
            </fieldset>
        </form>
    @endif
</div>
