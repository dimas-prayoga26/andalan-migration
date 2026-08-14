@extends('layouts.main')

@section('title', $mode === 'edit' ? 'Update Employee' : 'Add Employee')
@section('navbarTitle', $mode === 'edit' ? 'Update Employee' : 'Add Employee')

@section('content')
@php
    $isEdit = $mode === 'edit';
    $formatDate = static function (mixed $value): string {
        if (blank($value)) {
            return '';
        }

        return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
    };
@endphp

@include('layouts.breadcrumb', [
    'title' => $isEdit ? 'Update Employee' : 'Add Employee',
    'current' => 'Employee Data',
    'homeRoute' => 'dashboard',
])

<form method="POST" action="{{ $isEdit ? route('authorization.update', ['employee' => $employee]) : route('authorization.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="card">
        <div class="card-header border-0">
            <div>
                <h4 class="card-title mb-1">{{ $isEdit ? 'Update Employee' : 'Add Employee' }}</h4>
                <p class="mb-0 text-muted fs-13">Complete the user, profile, identity, deployment, and PIC data.</p>
            </div>
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $employee?->user?->is_active ?? true))>
                    <label class="form-check-label fw-semibold" for="is_active">Employee Status</label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="is_event_project_admin" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_event_project_admin" name="is_event_project_admin" value="1" @checked(old('is_event_project_admin', $employee?->is_event_project_admin ?? false))>
                    <label class="form-check-label fw-semibold" for="is_event_project_admin">Event Project Admin</label>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>The data is not valid.</strong> Please check the required fields again.
                </div>
            @endif

            @if (! $isEdit)
                <div class="alert alert-info mb-4" role="alert">
                    Default password for the new employee account is <strong>password</strong>.
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee?->profile?->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nickname</label>
                    <input type="text" name="nickname" class="form-control" value="{{ old('nickname', $employee?->profile?->nickname) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee?->user?->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee?->user?->phone) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $employee?->user?->username) }}" required>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Place of Birth</label>
                    <input type="text" name="place_of_birth" class="form-control" value="{{ old('place_of_birth', $employee?->profile?->place_of_birth) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="text" name="date_of_birth" class="form-control js-data-employee-date" value="{{ old('date_of_birth', $formatDate($employee?->profile?->date_of_birth)) }}" placeholder="dd/mm/yyyy" autocomplete="off">
                </div>

                <div class="col-md-3">
                    <label class="form-label">ID Number / NIK</label>
                    <input type="text" name="nik" class="form-control" value="{{ old('nik', $employee?->identity?->nik) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">NPWP</label>
                    <input type="text" name="npwp" class="form-control" value="{{ old('npwp', $employee?->identity?->npwp) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Healthcare BPJS</label>
                    <input type="text" name="bpjs_kesehatan" class="form-control" value="{{ old('bpjs_kesehatan', $employee?->identity?->bpjs_kesehatan) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Employment BPJS</label>
                    <input type="text" name="bpjs_ketenagakerjaan" class="form-control" value="{{ old('bpjs_ketenagakerjaan', $employee?->identity?->bpjs_ketenagakerjaan) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="default-select form-control">
                        <option value="">Open this to select</option>
                        @foreach (['Male', 'Female'] as $gender)
                            <option value="{{ $gender }}" @selected(old('gender', $employee?->profile?->gender) === $gender)>{{ $gender }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Marital Status</label>
                    <select name="marital_status" class="default-select form-control">
                        <option value="">Open this select menu</option>
                        @foreach (['Single', 'Married', 'Divorced', 'Widowed'] as $status)
                            <option value="{{ $status }}" @selected(old('marital_status', $employee?->profile?->marital_status) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="employee_status" value="Active">

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
                        $selectedPositionIds = collect(old('current_position_ids', $employee?->deployment?->positions?->pluck('id')->all() ?? []))
                            ->when(
                                old('current_position_ids') === null && $employee?->deployment?->current_position_id,
                                fn ($collection) => $collection->prepend($employee->deployment->current_position_id)
                            )
                            ->map(fn ($positionId) => (string) $positionId)
                            ->unique()
                            ->values();
                    @endphp
                    <select name="current_position_ids[]" class="default-select form-control" multiple>
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}" @selected($selectedPositionIds->contains((string) $position->id))>{{ $position->name }}</option>
                        @endforeach
                    </select>
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

                <div class="col-md-3">
                    <label class="form-label">Start Contract</label>
                    <input type="text" name="join_date" class="form-control js-data-employee-date" value="{{ old('join_date', $formatDate($employee?->deployment?->join_date)) }}" placeholder="dd/mm/yyyy" autocomplete="off">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Contract</label>
                    <input type="text" name="resignation_date" class="form-control js-data-employee-date" value="{{ old('resignation_date', $formatDate($employee?->deployment?->resignation_date)) }}" placeholder="dd/mm/yyyy" autocomplete="off">
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('authorization') }}" class="btn btn-light">Close</a>
            <button type="submit" class="btn btn-success">
                <i class="fa-regular fa-floppy-disk me-1"></i>{{ $isEdit ? 'Update Employee' : 'Add Employee' }}
            </button>
        </div>
    </div>
</form>
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.jQuery || !jQuery.fn.daterangepicker || typeof moment === 'undefined') {
                return;
            }

            jQuery('.js-data-employee-date').each(function () {
                var input = jQuery(this);

                input.daterangepicker({
                    autoUpdateInput: false,
                    singleDatePicker: true,
                    showDropdowns: true,
                    locale: {
                        cancelLabel: 'Clear',
                        format: 'DD/MM/YYYY'
                    }
                });

                input.on('apply.daterangepicker', function (event, picker) {
                    jQuery(this).val(picker.startDate.format('DD/MM/YYYY'));
                });

                input.on('cancel.daterangepicker', function () {
                    jQuery(this).val('');
                });
            });
        });
    </script>
@endsection
