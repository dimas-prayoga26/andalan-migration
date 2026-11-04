{{--
    "Data Karyawan" wizard shared by the Profile page (steps 1–4 only) and Update Employee (all steps).
    Each step is its own form that is saved automatically when the user moves to another step.
    Params: hasEmploymentStep (step 5, HR only; default true), employeeDataWizardValues,
    employeeDataStepUrls (step => save URL; empty = read-only), isAccountManagedElsewhere (nickname/phone are edited on the account tab).
--}}
@php
    $wizardValues = $employeeDataWizardValues ?? app(\App\Support\Employee\EmployeeDataWizardValues::class)->forEmployee(null);
    $wizardStepUrls = $employeeDataStepUrls ?? [];
    $isAccountManagedElsewhere = $isAccountManagedElsewhere ?? false;
    $hasEmploymentStep = $hasEmploymentStep ?? true;
@endphp
<div id="employeeDataWizard" class="form-wizard order-create border-0" data-region-urls="{{ json_encode([
    'province' => route('regions.provinces'),
    'regency' => url('regions/regencies'),
    'subdistrict' => url('regions/districts'),
    'village' => url('regions/villages'),
]) }}">
    <div class="alert alert-danger d-none js-employee-data-error" role="alert"></div>
    <ul class="nav nav-wizard">
        <li><a class="nav-link" href="#wizard_Service"><span>1</span></a></li>
        <li><a class="nav-link" href="#wizard_Time"><span>2</span></a></li>
        <li><a class="nav-link" href="#wizard_Details"><span>3</span></a></li>
        <li><a class="nav-link" href="#wizard_Payment"><span>4</span></a></li>
        @if ($hasEmploymentStep)
            <li><a class="nav-link" href="#wizard_Finish"><span>5</span></a></li>
        @endif
    </ul>
    <div class="tab-content">
        @include('employee_data.wizard.step-1-personal')
        @include('employee_data.wizard.step-2-legal-tax')
        @include('employee_data.wizard.step-3-education')
        @include('employee_data.wizard.step-4-family')
        @if ($hasEmploymentStep)
            @include('employee_data.wizard.step-5-employment')
        @endif
    </div>
    @if ($wizardStepUrls !== [])
        <p class="small text-muted mb-0 mt-2 js-employee-data-save-status" aria-live="polite">Data tersimpan otomatis saat pindah step.</p>
    @endif
</div>
