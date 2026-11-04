@php
    $familyValues = $wizardValues['family'];
    $familySaveUrl = $wizardStepUrls['family'] ?? null;
@endphp
<div id="wizard_Payment" class="tab-pane" role="tabpanel">
    <form class="js-employee-data-step-form" data-employee-data-step="family" data-save-url="{{ $familySaveUrl }}" novalidate autocomplete="off">
        <fieldset @disabled(blank($familySaveUrl))>
            <div class="row">
                <h4 class="mt-3 mb-3 fw-bold">Family & Emergency Contacts</h4>
                <h6 class="mb-3 fw-semibold">1. Susunan Keluarga:</h6>
                <div class="col-12">
                    @include('employee_data.wizard.repeatable', [
                        'group' => 'families',
                        'rows' => $familyValues['families'] ?? [],
                        'actionColumn' => 'col-lg-3',
                        'fields' => [
                            ['name' => 'name', 'label' => 'Nama Lengkap (Sesuai KTP)'],
                            ['name' => 'date_of_birth', 'label' => 'Tanggal Lahir', 'type' => 'date'],
                            ['name' => 'occupation', 'label' => 'Pekerjaan'],
                            ['name' => 'phone_number', 'label' => 'No Handphone', 'type' => 'tel', 'attributes' => 'inputmode="tel"'],
                            ['name' => 'relationship', 'label' => 'Status', 'type' => 'select', 'options' => \App\Support\Employee\EmployeeDataOptions::FAMILY_RELATIONSHIPS],
                        ],
                    ])
                </div>
            </div>
            <hr>
            <div class="row">
                <h6 class="mb-3 fw-semibold">2. Kontak Darurat:<span class="employee-data-required-mark">*</span></h6>
                <div class="col-12">
                    @include('employee_data.wizard.repeatable', [
                        'group' => 'emergency_contacts',
                        'rows' => $familyValues['emergency_contacts'] ?? [],
                        'actionColumn' => 'col-lg-3',
                        'fields' => [
                            ['name' => 'name', 'label' => 'Nama Lengkap (Sesuai KTP)'],
                            ['name' => 'phone_number', 'label' => 'No Handphone', 'type' => 'tel', 'attributes' => 'inputmode="tel"'],
                            ['name' => 'relationship', 'label' => 'Hubungan', 'type' => 'select', 'options' => \App\Support\Employee\EmployeeDataOptions::EMERGENCY_CONTACT_RELATIONSHIPS],
                        ],
                    ])
                </div>
            </div>
        </fieldset>
    </form>
</div>
