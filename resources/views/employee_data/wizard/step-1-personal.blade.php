@php
    $personalValues = $wizardValues['personal'];
    $personalSaveUrl = $wizardStepUrls['personal'] ?? null;
    $accountFieldHelp = $isAccountManagedElsewhere ? 'Diatur pada tab 1. Account & Placement.' : null;
@endphp
<div id="wizard_Service" class="tab-pane" role="tabpanel">
    <form class="js-employee-data-step-form" data-employee-data-step="personal" data-save-url="{{ $personalSaveUrl }}" novalidate autocomplete="off">
        <fieldset @disabled(blank($personalSaveUrl))>
            <div class="row">
                <h4 class="mt-3 mb-3 fw-bold">Personal & Physical Profile</h4>
                <h6 class="mb-3 fw-semibold">1. Data Demografi:</h6>
                @include('employee_data.wizard.field', ['name' => 'name', 'label' => 'Nama Lengkap', 'value' => $personalValues['name'] ?? null, 'required' => true])
                @include('employee_data.wizard.field', ['name' => 'nickname', 'label' => 'Nama Panggilan', 'value' => $personalValues['nickname'] ?? null, 'disabled' => $isAccountManagedElsewhere, 'help' => $accountFieldHelp])
                @include('employee_data.wizard.field', ['name' => 'place_of_birth', 'label' => 'Tempat Lahir', 'value' => $personalValues['place_of_birth'] ?? null, 'placeholder' => 'Contoh: Sleman', 'required' => true])
                @include('employee_data.wizard.field', ['name' => 'date_of_birth', 'label' => 'Tanggal Lahir', 'type' => 'date', 'value' => $personalValues['date_of_birth'] ?? null, 'required' => true, 'attributes' => 'max="'.now()->subDay()->toDateString().'"'])
                @include('employee_data.wizard.field', ['name' => 'gender', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => \App\Support\Employee\EmployeeDataOptions::GENDERS, 'value' => $personalValues['gender'] ?? null, 'required' => true])
                @include('employee_data.wizard.field', ['name' => 'religion', 'label' => 'Agama', 'type' => 'select', 'options' => \App\Support\Employee\EmployeeDataOptions::RELIGIONS, 'value' => $personalValues['religion'] ?? null, 'required' => true])
                @include('employee_data.wizard.field', ['name' => 'marital_status', 'label' => 'Status Pernikahan', 'type' => 'select', 'options' => \App\Support\Employee\EmployeeDataOptions::MARITAL_STATUSES, 'value' => $personalValues['marital_status'] ?? null, 'required' => true])
                @include('employee_data.wizard.field', ['name' => 'nationality', 'label' => 'Kewarganegaraan', 'type' => 'select', 'options' => \App\Support\Employee\EmployeeDataOptions::NATIONALITIES, 'value' => $personalValues['nationality'] ?? null])
            </div>
            <hr>
            <div class="row">
                <h6 class="mb-3 fw-semibold">2. Data Fisik & Medis Dasar:</h6>
                @include('employee_data.wizard.field', ['name' => 'height', 'label' => 'Tinggi Badan', 'type' => 'number', 'column' => 'col-lg-2', 'value' => $personalValues['height'] ?? null, 'placeholder' => 'cm', 'attributes' => 'min="50" max="250" step="1"'])
                @include('employee_data.wizard.field', ['name' => 'weight', 'label' => 'Berat Badan', 'type' => 'number', 'column' => 'col-lg-2', 'value' => $personalValues['weight'] ?? null, 'placeholder' => 'kg', 'attributes' => 'min="20" max="300" step="0.1"'])
                @include('employee_data.wizard.field', ['name' => 'blood_type', 'label' => 'Golongan Darah', 'type' => 'select', 'column' => 'col-lg-2', 'options' => \App\Support\Employee\EmployeeDataOptions::BLOOD_TYPES, 'value' => $personalValues['blood_type'] ?? null, 'placeholder' => 'Pilih'])
                @include('employee_data.wizard.field', ['name' => 'shirt_size', 'label' => 'Ukuran Kemeja', 'column' => 'col-lg-2', 'value' => $personalValues['shirt_size'] ?? null, 'placeholder' => 'Contoh: L'])
                @include('employee_data.wizard.field', ['name' => 'pants_size', 'label' => 'Ukuran Celana', 'column' => 'col-lg-2', 'value' => $personalValues['pants_size'] ?? null, 'placeholder' => 'Contoh: 32'])
                @include('employee_data.wizard.field', ['name' => 'shoe_size', 'label' => 'Ukuran Sepatu', 'column' => 'col-lg-2', 'value' => $personalValues['shoe_size'] ?? null, 'placeholder' => 'Contoh: 42'])
                @include('employee_data.wizard.field', ['name' => 'medical_history', 'label' => 'Riwayat Penyakit Khusus/Alergi', 'column' => 'col-lg-12', 'value' => $personalValues['medical_history'] ?? null])
            </div>
            <hr>
            <div class="row">
                <h6 class="mb-3 fw-semibold">3. Kontak & Lokasi:</h6>
                @include('employee_data.wizard.field', ['name' => 'phone', 'label' => 'Nomor Handphone (Aktif WhatsApp)', 'type' => 'tel', 'value' => $personalValues['phone'] ?? null, 'required' => true, 'disabled' => $isAccountManagedElsewhere, 'help' => $accountFieldHelp, 'attributes' => 'inputmode="tel"'])
                @include('employee_data.wizard.field', ['name' => 'personal_email', 'label' => 'Email Pribadi', 'type' => 'email', 'value' => $personalValues['personal_email'] ?? null])
                @include('employee_data.wizard.field', ['name' => 'linkedin_url', 'label' => 'LinkedIn Profile', 'type' => 'url', 'value' => $personalValues['linkedin_url'] ?? null, 'placeholder' => 'https://www.linkedin.com/in/...'])
                @include('employee_data.wizard.field', ['name' => 'portfolio_url', 'label' => 'Website / Portfolio', 'type' => 'url', 'value' => $personalValues['portfolio_url'] ?? null, 'placeholder' => 'https://...'])
            </div>
            <div class="row js-region-address" data-address-type="ktp">
                <h6 class="mb-3 fw-semibold">Alamat Sesuai KTP:<span class="employee-data-required-mark">*</span></h6>
                @include('employee_data.wizard.address-fields', ['addressType' => 'ktp', 'addressValues' => $personalValues['addresses']['ktp'] ?? [], 'isAddressRequired' => true])
            </div>
            <div class="row">
                <div class="d-flex">
                    <h6 class="mb-3 fw-semibold">Alamat Domisili:<span class="employee-data-required-mark">*</span></h6>
                    <div class="form-check ms-3">
                        <input type="hidden" name="domicile_same_as_ktp" value="0">
                        <input class="form-check-input js-domicile-same-as-ktp" type="checkbox" name="domicile_same_as_ktp" value="1" id="employeeDataDomicileSameAsKtp" @checked($personalValues['domicile_same_as_ktp'] ?? false)>
                        <label class="form-check-label" for="employeeDataDomicileSameAsKtp">Sama dengan KTP</label>
                    </div>
                </div>
            </div>
            <div class="row js-region-address js-domicile-address" data-address-type="domicile">
                @include('employee_data.wizard.address-fields', ['addressType' => 'domicile', 'addressValues' => $personalValues['addresses']['domicile'] ?? [], 'isAddressRequired' => true])
            </div>
        </fieldset>
    </form>
</div>
