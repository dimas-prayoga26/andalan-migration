@extends('layouts.main')

@section('title', $mode === 'edit' ? 'Update Employee' : 'Add Employee')
@section('navbarTitle', $mode === 'edit' ? 'Update Employee' : 'Add Employee')

@section('css')
    @include('employee_data.wizard.styles')
    @include('employee_data.documents.styles')
@endsection

@section('content')
@php
    $isEdit = $mode === 'edit';
@endphp

@include('layouts.breadcrumb', [
    'title' => $isEdit ? 'Update Employee' : 'Add Employee',
    'current' => 'Employee Data',
    'homeRoute' => 'dashboard',
])

    <div class="card">
        <div class="card-header border-0">
            <div>
                <h4 class="card-title mb-1">{{ $isEdit ? 'Update Employee' : 'Add Employee' }}</h4>
                <p class="mb-0 text-muted fs-13">Only the account and placement tab is required to create a login. Employee data can be completed later.</p>
            </div>
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="is_active" value="0" form="employeeForm">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" form="employeeForm" @checked(old('is_active', $employee?->user?->is_active ?? true))>
                    <label class="form-check-label fw-semibold" for="is_active">Employee Status</label>
                </div>
                <input type="hidden" name="is_event_project_admin" value="0" form="employeeForm">
                @if ($isEdit)
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_event_project_admin" name="is_event_project_admin" value="1" form="employeeForm" @checked(old('is_event_project_admin', $employee?->is_event_project_admin ?? false))>
                        <label class="form-check-label fw-semibold" for="is_event_project_admin">Event Project Admin</label>
                    </div>
                @endif
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="is_core_staff" value="0" form="employeeForm">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_core_staff" name="is_core_staff" value="1" form="employeeForm" @checked(old('is_core_staff', $employee?->is_core_staff ?? false))>
                    <label class="form-check-label fw-semibold" for="is_core_staff">Core Staff</label>
                </div>
            </div>
        </div>

        <div class="card-body">
            @include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])
            @if ($errors->any())
                @include('partials.swal-alert', [
                    'type' => 'error',
                    'title' => 'The data is not valid.',
                    'message' => 'Please check the required fields again.',
                ])
            @endif

            @if (! $isEdit)
                <div class="alert alert-info mb-4" role="alert">
                    Default password for the new employee account is <strong>password</strong>.
                </div>
            @endif

            <ul class="nav nav-tabs" id="employeeFormTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="employeeFormAccountTab" data-bs-toggle="tab" data-bs-target="#employeeFormAccount" type="button" role="tab" aria-controls="employeeFormAccount" aria-selected="true">
                        1. Account &amp; Placement
                        <span class="badge badge-xs light badge-primary ms-1">Required</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="employeeFormDataTab" data-bs-toggle="tab" data-bs-target="#employeeFormData" type="button" role="tab" aria-controls="employeeFormData" aria-selected="false">
                        2. Employee Data
                        <span class="badge badge-xs light badge-secondary ms-1">Optional</span>
                    </button>
                </li>
                @if ($isEdit)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="employeeFormDocumentsTab" data-bs-toggle="tab" data-bs-target="#employeeFormDocuments" type="button" role="tab" aria-controls="employeeFormDocuments" aria-selected="false">
                            3. Documents
                            <span class="badge badge-xs light badge-secondary ms-1">Optional</span>
                        </button>
                    </li>
                @endif
            </ul>

            <div class="tab-content pt-4">
                <div class="tab-pane fade show active js-employee-form-pane" id="employeeFormAccount" role="tabpanel" aria-labelledby="employeeFormAccountTab" tabindex="0">
                    <form method="POST" id="employeeForm" action="{{ $isEdit ? route('authorization.update', ['employee' => $employee]) : route('authorization.store') }}">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif
                    <h6 class="text-primary mb-3">Login Account</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Employee Code</label>
                            <input type="text" name="employee_code" class="form-control @error('employee_code') is-invalid @enderror" value="{{ old('employee_code', $employee?->employee_code) }}" placeholder="Enter employee code" required>
                            @error('employee_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nickname</label>
                            <input type="text" name="nickname" class="form-control @error('nickname') is-invalid @enderror" value="{{ old('nickname', $employee?->profile?->nickname) }}" placeholder="Enter nickname" required>
                            @error('nickname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee?->user?->email) }}" placeholder="name@example.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee?->user?->phone) }}" placeholder="Enter phone number">
                        </div>
                    </div>

                    <h6 class="text-primary mb-3">Placement</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Company</label>
                            <select id="dataEmployeeCompany" name="current_company_id" class="default-select form-control">
                                <option value="">Open this select menu</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}" @selected(old('current_company_id', $employee?->deployment?->current_company_id) === $company->id)>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Base</label>
                            <select id="dataEmployeeOfficeLocation" name="current_office_location_id" class="form-control @error('current_office_location_id') is-invalid @enderror">
                                <option value="">Select Base</option>
                                @foreach ($officeLocationOptions as $officeLocation)
                                    <option value="{{ $officeLocation['id'] }}" @selected((string) old('current_office_location_id', $employee?->deployment?->current_office_location_id ?? '') === $officeLocation['id'])>{{ $officeLocation['label'] }}</option>
                                @endforeach
                            </select>
                            @error('current_office_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Division</label>
                            <select name="current_department_id" class="default-select form-control">
                                <option value="">Open this select menu</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('current_department_id', $employee?->deployment?->current_department_id) === $department->id)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Position</label>
                            @php
                                $selectedPrimaryPositionId = (string) old('current_position_id', $employee?->deployment?->current_position_id ?? '');
                                $selectedPositionIds = collect(old('current_position_ids', $employee?->deployment?->positions?->pluck('id')->all() ?? []))
                                    ->when($selectedPrimaryPositionId !== '', fn ($collection) => $collection->prepend($selectedPrimaryPositionId))
                                    ->map(fn ($positionId) => (string) $positionId)
                                    ->unique()
                                    ->values();
                            @endphp
                            <input type="hidden" name="current_position_id" value="{{ $selectedPositionIds->first() ?? '' }}" data-position-primary-input>
                            <span data-position-order-fields></span>
                            <select name="current_position_ids[]" class="default-select form-control js-position-primary-selector" multiple>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" @selected($selectedPositionIds->contains((string) $position->id))>{{ $position->name }}</option>
                                @endforeach
                            </select>
                            @error('current_position_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('current_position_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('current_position_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('current_position_order')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('current_position_order.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PIC / Person in Charge</label>
                            <select name="pic_employee_id" class="default-select form-control">
                                <option value="">Open this select menu</option>
                                @foreach ($picEmployees as $picEmployee)
                                    <option value="{{ $picEmployee->id }}" @selected(old('pic_employee_id', $employee?->picAssignment?->supervisor_employee_id) === $picEmployee->id)>
                                        {{ $picEmployee->profile?->name ?? $picEmployee->employee_code ?? $picEmployee->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    </form>
                </div>

                <div class="tab-pane fade js-employee-form-pane" id="employeeFormData" role="tabpanel" aria-labelledby="employeeFormDataTab" tabindex="0">
                    @if ($isEdit)
                        <p class="text-muted mb-3">This data is optional and is saved automatically when you move to another step. Employees can also complete it themselves from their Profile page.</p>
                        @include('employee_data.wizard.wizard', ['isAccountManagedElsewhere' => true])
                    @else
                        <div class="alert alert-info mb-0" role="alert">
                            Employee data can be completed after the account is created. Fill in <strong>1. Account &amp; Placement</strong>, then click <strong>Add Employee</strong>; you will be taken straight to this tab.
                        </div>
                    @endif
                </div>

                @if ($isEdit)
                    <div class="tab-pane fade js-employee-form-pane" id="employeeFormDocuments" role="tabpanel" aria-labelledby="employeeFormDocumentsTab" tabindex="0">
                        @include('employee_data.documents.vault', ['documentVaultMode' => 'hr', 'employeeDocumentFiles' => $employeeDocumentFiles ?? []])
                    </div>
                @endif
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('authorization') }}" class="btn btn-light">Kembali</a>
            <button type="submit" id="employeeFormSubmitButton" class="btn btn-success" form="employeeForm">
                <i class="fa-regular fa-floppy-disk me-1"></i>{{ $isEdit ? 'Update Employee' : 'Add Employee' }}
            </button>
        </div>
    </div>
@endsection

@section('script')
    @include('employee_data.wizard.script')
    @include('employee_data.documents.script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var positionSelect = document.querySelector('.js-position-primary-selector');
            var primaryInput = document.querySelector('[data-position-primary-input]');
            var positionOrderFields = document.querySelector('[data-position-order-fields]');
            var selectedPositionOrder = @js($selectedPositionIds->all());

            if (!positionSelect || !primaryInput || !positionOrderFields) {
                return;
            }

            Array.prototype.slice.call(positionSelect.options).forEach(function (option) {
                option.dataset.positionBaseLabel = option.text.trim();
            });

            function selectedValues() {
                return Array.prototype.slice.call(positionSelect.options)
                    .filter(function (option) {
                        return option.selected && option.value !== '';
                    })
                    .map(function (option) {
                        return option.value;
                    });
            }

            function uniqueValues(values) {
                return values.filter(function (value, index, collection) {
                    return value !== '' && collection.indexOf(value) === index;
                });
            }

            function reconcileOrder() {
                var currentSelectedValues = selectedValues();

                selectedPositionOrder = uniqueValues(selectedPositionOrder)
                    .filter(function (value) {
                        return currentSelectedValues.indexOf(value) !== -1;
                    });

                currentSelectedValues.forEach(function (value) {
                    if (selectedPositionOrder.indexOf(value) === -1) {
                        selectedPositionOrder.push(value);
                    }
                });
            }

            function refreshSelectpicker() {
                if (window.jQuery && jQuery.fn.selectpicker && jQuery(positionSelect).data('selectpicker')) {
                    jQuery(positionSelect).selectpicker('refresh');
                }
            }

            function renderPositionRoles() {
                reconcileOrder();
                primaryInput.value = selectedPositionOrder[0] || '';
                positionOrderFields.replaceChildren();

                selectedPositionOrder.forEach(function (positionId) {
                    var input = document.createElement('input');

                    input.type = 'hidden';
                    input.name = 'current_position_order[]';
                    input.value = positionId;
                    positionOrderFields.appendChild(input);
                });

                Array.prototype.slice.call(positionSelect.options).forEach(function (option) {
                    var baseLabel = option.dataset.positionBaseLabel || option.text.trim();
                    var positionIndex = selectedPositionOrder.indexOf(option.value);

                    option.text = positionIndex === -1
                        ? baseLabel
                        : baseLabel + (positionIndex === 0 ? ' (Primary Position)' : ' (Secondary Position)');
                });

                refreshSelectpicker();
            }

            renderPositionRoles();

            positionSelect.addEventListener('change', renderPositionRoles);

            if (window.jQuery) {
                jQuery(positionSelect).on('changed.bs.select', function (event, clickedIndex, isSelected) {
                    var option = typeof clickedIndex === 'number' ? positionSelect.options[clickedIndex] : null;

                    if (option && option.value !== '') {
                        if (isSelected) {
                            selectedPositionOrder.push(option.value);
                        } else {
                            selectedPositionOrder = selectedPositionOrder.filter(function (value) {
                                return value !== option.value;
                            });
                        }
                    }

                    renderPositionRoles();
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            var employeeForm = document.getElementById('employeeForm');

            if (!employeeForm || !window.bootstrap) {
                return;
            }

            employeeForm.addEventListener('invalid', function (event) {
                var tabPane = event.target.closest('.js-employee-form-pane');

                if (!tabPane || tabPane.classList.contains('active')) {
                    return;
                }

                var tabTrigger = document.querySelector('[data-bs-target="#' + tabPane.id + '"]');

                if (tabTrigger) {
                    bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
                }
            }, true);

            var submitButton = document.getElementById('employeeFormSubmitButton');
            var accountTab = document.getElementById('employeeFormAccountTab');

            if (!submitButton || !accountTab) {
                return;
            }

            document.querySelectorAll('#employeeFormTabs [data-bs-toggle="tab"]').forEach(function (tabTrigger) {
                tabTrigger.addEventListener('shown.bs.tab', function (event) {
                    submitButton.classList.toggle('d-none', event.target !== accountTab);
                });
            });

            var tabTriggerFromHash = {
                '#employee-data': document.getElementById('employeeFormDataTab'),
                '#employee-documents': document.getElementById('employeeFormDocumentsTab')
            }[window.location.hash];

            if (tabTriggerFromHash) {
                bootstrap.Tab.getOrCreateInstance(tabTriggerFromHash).show();
            }
        });
    </script>
@endsection
