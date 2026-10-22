@php
    $educationValues = $wizardValues['education'];
    $educationSaveUrl = $wizardStepUrls['education'] ?? null;
    $educationLevelOptions = collect($wizardValues['education_levels'] ?? [])->pluck('name', 'id')->all();
@endphp
<div id="wizard_Details" class="tab-pane" role="tabpanel">
    <form class="js-employee-data-step-form" data-employee-data-step="education" data-save-url="{{ $educationSaveUrl }}" novalidate autocomplete="off">
        <fieldset @disabled(blank($educationSaveUrl))>
            <div class="row">
                <h4 class="mt-3 mb-3 fw-bold">Education, Experience & Skills</h4>
                <h6 class="mb-3 fw-semibold">1. Pendidikan Formal:<span class="employee-data-required-mark">*</span></h6>
                <div class="col-12">
                    @include('employee_data.wizard.repeatable', [
                        'group' => 'educations',
                        'rows' => $educationValues['educations'] ?? [],
                        'actionColumn' => 'col-lg-3',
                        'fields' => [
                            ['name' => 'education_level_id', 'label' => 'Tingkat Pendidikan', 'type' => 'select', 'options' => $educationLevelOptions],
                            ['name' => 'institution_name', 'label' => 'Nama Institusi'],
                            ['name' => 'major', 'label' => 'Jurusan'],
                            ['name' => 'city', 'label' => 'Kota'],
                            ['name' => 'start_year', 'label' => 'Tahun Masuk', 'type' => 'number', 'attributes' => 'min="1950" max="2100" step="1"'],
                            ['name' => 'graduation_year', 'label' => 'Tahun Lulus', 'type' => 'number', 'attributes' => 'min="1950" max="2100" step="1"'],
                            ['name' => 'gpa', 'label' => 'IPK', 'type' => 'number', 'placeholder' => '0.00 – 4.00', 'attributes' => 'min="0" max="4" step="0.01"'],
                        ],
                    ])
                </div>
            </div>
            <hr>
            <div class="row">
                <h6 class="mb-3 fw-semibold">2. Sertifikasi Profesional:</h6>
                <div class="col-12">
                    @include('employee_data.wizard.repeatable', [
                        'group' => 'certifications',
                        'rows' => $educationValues['certifications'] ?? [],
                        'fields' => [
                            ['name' => 'certificate_name', 'label' => 'Nama Sertifikat', 'column' => 'col-lg-2'],
                            ['name' => 'issuer', 'label' => 'Lembaga Penerbit', 'column' => 'col-lg-2'],
                            ['name' => 'effective_date', 'label' => 'Tanggal Berlaku', 'type' => 'date', 'column' => 'col-lg-2'],
                            ['name' => 'expired_at', 'label' => 'Berakhir', 'type' => 'date', 'column' => 'col-lg-2'],
                            ['name' => 'city', 'label' => 'Kota', 'column' => 'col-lg-2'],
                        ],
                    ])
                </div>
            </div>
            <hr>
            <div class="row">
                <h6 class="mb-3 fw-semibold">3. Riwayat Pekerjaan:</h6>
                <div class="col-12">
                    @include('employee_data.wizard.repeatable', [
                        'group' => 'work_experiences',
                        'rows' => $educationValues['work_experiences'] ?? [],
                        'fields' => [
                            ['name' => 'company_name', 'label' => 'Nama Perusahaan', 'column' => 'col-lg-2'],
                            ['name' => 'last_position', 'label' => 'Jabatan Terakhir', 'column' => 'col-lg-2'],
                            ['name' => 'duration_text', 'label' => 'Durasi Bekerja', 'column' => 'col-lg-2', 'placeholder' => 'Contoh: 2 tahun'],
                            ['name' => 'resignation_reason', 'label' => 'Alasan Resign', 'column' => 'col-lg-4'],
                        ],
                    ])
                </div>
            </div>
            <hr>
            <div class="row">
                <h6 class="mb-3 fw-semibold">4. Kontak Referensi:</h6>
                <div class="col-12">
                    @include('employee_data.wizard.repeatable', [
                        'group' => 'references',
                        'rows' => $educationValues['references'] ?? [],
                        'fields' => [
                            ['name' => 'name', 'label' => 'Nama Atasan Lama', 'column' => 'col-lg-2'],
                            ['name' => 'position', 'label' => 'Jabatan Atasan Lama', 'column' => 'col-lg-3'],
                            ['name' => 'company_name', 'label' => 'Nama Perusahaan', 'column' => 'col-lg-3'],
                            ['name' => 'phone_number', 'label' => 'Nomor Handphone', 'type' => 'tel', 'column' => 'col-lg-2', 'attributes' => 'inputmode="tel"'],
                        ],
                    ])
                </div>
            </div>
            <hr>
            <div class="row">
                <h6 class="mb-3 fw-semibold">5. Keterampilan:</h6>
                @include('employee_data.wizard.field', ['name' => 'languages', 'label' => 'Bahasa Yang Dikuasai', 'column' => 'col-lg-4', 'value' => $educationValues['languages'] ?? null, 'placeholder' => 'Contoh: Indonesia, Inggris', 'help' => 'Pisahkan dengan koma.'])
                @include('employee_data.wizard.field', ['name' => 'hard_skills', 'label' => 'Hard Skills', 'column' => 'col-lg-4', 'value' => $educationValues['hard_skills'] ?? null, 'placeholder' => 'Contoh: AutoCAD, Excel', 'help' => 'Pisahkan dengan koma.'])
                @include('employee_data.wizard.field', ['name' => 'soft_skills', 'label' => 'Soft Skills', 'type' => 'select', 'multiple' => true, 'column' => 'col-lg-4', 'options' => \App\Support\Employee\EmployeeDataOptions::SOFT_SKILLS, 'value' => $educationValues['soft_skills'] ?? [], 'attributes' => 'title="Pilih Soft Skills"'])
            </div>
        </fieldset>
    </form>
</div>
