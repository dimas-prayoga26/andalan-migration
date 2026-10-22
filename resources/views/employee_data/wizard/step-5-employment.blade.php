@php
    $employmentDetails = $employeeEmploymentDetails ?? [];
    $isEmploymentStepReadOnly = $isEmploymentStepReadOnly ?? true;
    $employmentValue = static fn (string $key): ?string => $employmentDetails[$key] ?? null;
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
        <span class="badge badge-sm light badge-secondary"><i class="fa fa-lock me-1"></i>Dikelola HR</span>
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

    <div class="row">
        <h6 class="mb-3 fw-semibold">2. Kontrak &amp; Operasional:</h6>
        @if ($isEmploymentStepReadOnly)
            @foreach ($contractFields as $fieldKey => $fieldLabel)
                <div class="col-lg-3 mb-2">
                    <div class="mb-3">
                        <label class="text-label form-label">{{ $fieldLabel }}</label>
                        <input type="text" class="form-control" value="{{ $employmentValue($fieldKey) ?? '-' }}" readonly disabled>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-lg-3 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Status Kepegawaian</label>
                    <select class="selectpicker form-select">
                        <option value="">Pilih status</option>
                        @foreach (\App\Support\Employee\EmployeeEmploymentDetailResolver::EMPLOYMENT_STATUS_OPTIONS as $employmentStatusOption)
                            <option value="{{ $employmentStatusOption }}" @selected($employmentValue('employment_status') === $employmentStatusOption)>{{ $employmentStatusOption }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Tanggal Bergabung</label>
                    <input type="text" class="form-control" value="{{ $employmentValue('join_date') }}" placeholder="dd/mm/yyyy">
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Tanggal Berakhir Kontrak</label>
                    <input type="text" class="form-control" value="{{ $employmentValue('contract_end_date') }}" placeholder="dd/mm/yyyy">
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Skema Waktu Kerja</label>
                    <select class="selectpicker form-select">
                        <option value="">Pilih skema</option>
                        @foreach (\App\Support\Employee\EmployeeEmploymentDetailResolver::WORK_SCHEDULE_OPTIONS as $workScheduleOption)
                            <option value="{{ $workScheduleOption }}" @selected($employmentValue('work_schedule') === $workScheduleOption)>{{ $workScheduleOption }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
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
                        @if ($isEmploymentStepReadOnly)
                            <input type="text" class="form-control" value="{{ $employmentValue($fieldKey) ?? '-' }}" readonly disabled>
                        @else
                            <input type="text" class="form-control" value="{{ $employmentValue($fieldKey) }}" placeholder="{{ $fieldLabel }}" inputmode="numeric">
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <hr>

    <div class="row">
        <h6 class="mb-3 fw-semibold">4. Riwayat MCU:</h6>
        @if ($isEmploymentStepReadOnly)
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
        @else
            <div class="col-lg-3 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Tanggal MCU</label>
                    <input type="text" class="form-control" placeholder="dd/mm/yyyy">
                </div>
            </div>
            <div class="col-lg-4 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Rumah Sakit / Klinik</label>
                    <input type="text" class="form-control" placeholder="Rumah Sakit / Klinik">
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Status</label>
                    <select class="selectpicker form-select">
                        @foreach (\App\Support\Employee\EmployeeEmploymentDetailResolver::MCU_STATUS_OPTIONS as $mcuStatusOption)
                            <option value="{{ $mcuStatusOption }}">{{ $mcuStatusOption }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Action</label>
                    <div class="employee-data-row-actions">
                        <button type="button" class="btn btn-success light" title="Tambah baris" aria-label="Tambah baris"><i class="fa fa-plus"></i></button>
                        <button type="button" class="btn btn-danger light" title="Hapus baris" aria-label="Hapus baris"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <hr>

    <div class="row">
        <h6 class="mb-3 fw-semibold">5. Aset Perusahaan yang Dipegang:</h6>
        @if ($isEmploymentStepReadOnly)
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
        @else
            <div class="col-lg-3 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Nama Aset</label>
                    <input type="text" class="form-control" placeholder="Contoh: Laptop">
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Merk / Spesifikasi</label>
                    <input type="text" class="form-control" placeholder="Merk / Spesifikasi">
                </div>
            </div>
            <div class="col-lg-2 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Nomor Seri</label>
                    <input type="text" class="form-control" placeholder="Nomor Seri">
                </div>
            </div>
            <div class="col-lg-2 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Tanggal Serah Terima</label>
                    <input type="text" class="form-control" placeholder="dd/mm/yyyy">
                </div>
            </div>
            <div class="col-lg-2 mb-2">
                <div class="mb-3">
                    <label class="text-label form-label">Action</label>
                    <div class="employee-data-row-actions">
                        <button type="button" class="btn btn-success light" title="Tambah baris" aria-label="Tambah baris"><i class="fa fa-plus"></i></button>
                        <button type="button" class="btn btn-danger light" title="Hapus baris" aria-label="Hapus baris"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
