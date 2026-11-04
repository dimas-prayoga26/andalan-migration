@php
    $legalValues = $wizardValues['legal'];
    $legalSaveUrl = $wizardStepUrls['legal'] ?? null;
@endphp
<div id="wizard_Time" class="tab-pane" role="tabpanel">
    <form class="js-employee-data-step-form" data-employee-data-step="legal" data-save-url="{{ $legalSaveUrl }}" novalidate autocomplete="off">
        <fieldset @disabled(blank($legalSaveUrl))>
            <div class="row">
                <h4 class="mt-3 mb-3 fw-bold">Legal, Tax & Compliance</h4>
                <h6 class="mb-3 fw-semibold">1. Identitas & Pajak:</h6>
                @include('employee_data.wizard.field', ['name' => 'nik', 'label' => 'NIK (No KTP)', 'value' => $legalValues['nik'] ?? null, 'required' => true, 'placeholder' => '16 digit NIK', 'attributes' => 'inputmode="numeric" maxlength="16"'])
                @include('employee_data.wizard.field', ['name' => 'kk', 'label' => 'Nomor Kartu Keluarga', 'value' => $legalValues['kk'] ?? null, 'required' => true, 'placeholder' => '16 digit Nomor KK', 'attributes' => 'inputmode="numeric" maxlength="16"'])
                @include('employee_data.wizard.field', ['name' => 'mother_name', 'label' => 'Nama Ibu Kandung', 'value' => $legalValues['mother_name'] ?? null, 'required' => true])
                @include('employee_data.wizard.field', ['name' => 'npwp', 'label' => 'Nomor NPWP', 'value' => $legalValues['npwp'] ?? null, 'attributes' => 'inputmode="numeric" maxlength="25"'])
                @include('employee_data.wizard.field', ['name' => 'bpjs_ketenagakerjaan', 'label' => 'Nomor BPJS Ketenagakerjaan', 'column' => 'col-lg-6', 'value' => $legalValues['bpjs_ketenagakerjaan'] ?? null, 'attributes' => 'inputmode="numeric" maxlength="20"'])
                @include('employee_data.wizard.field', ['name' => 'bpjs_kesehatan', 'label' => 'Nomor BPJS Kesehatan', 'column' => 'col-lg-6', 'value' => $legalValues['bpjs_kesehatan'] ?? null, 'attributes' => 'inputmode="numeric" maxlength="20"'])
            </div>
            <hr>
            <div class="row">
                <h6 class="mb-3 fw-semibold">2. Data Perbankan:</h6>
                @include('employee_data.wizard.field', ['name' => 'bank[bank_name]', 'label' => 'Nama Bank', 'type' => 'select', 'options' => \App\Support\Employee\EmployeeDataOptions::BANKS, 'value' => $legalValues['bank']['bank_name'] ?? null])
                @include('employee_data.wizard.field', ['name' => 'bank[account_number]', 'label' => 'Nomor Rekening', 'value' => $legalValues['bank']['account_number'] ?? null, 'required' => true, 'attributes' => 'inputmode="numeric" maxlength="30"'])
                @include('employee_data.wizard.field', ['name' => 'bank[account_holder_name]', 'label' => 'Nama Pemilik Rekening', 'column' => 'col-lg-6', 'value' => $legalValues['bank']['account_holder_name'] ?? null])
            </div>
            <hr>
            <div class="row">
                <h6 class="mb-3 fw-semibold">3. Lisensi Operasional:</h6>
                <div class="col-12">
                    @include('employee_data.wizard.repeatable', [
                        'group' => 'licenses',
                        'rows' => $legalValues['licenses'] ?? [],
                        'actionColumn' => 'col-lg-3',
                        'fields' => [
                            ['name' => 'license_type', 'label' => 'Jenis Lisensi', 'type' => 'select', 'options' => \App\Support\Employee\EmployeeDataOptions::LICENSE_TYPES],
                            ['name' => 'license_number', 'label' => 'Nomor Lisensi'],
                            ['name' => 'expired_at', 'label' => 'Masa Berlaku', 'type' => 'date'],
                        ],
                    ])
                </div>
            </div>
        </fieldset>
    </form>
</div>
