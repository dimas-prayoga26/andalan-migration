@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    @include('employee_data.wizard.styles')
    @include('employee_data.documents.styles')
    <style>
        .profile-tab-alert-badge {
            background-color: #dc3545;
            color: #fff;
        }

        .profile-locked-badge {
            font-size: 0.7rem;
            color: #6b7280;
            background: #f4f6fb;
            border: 1px solid #e7eaf3;
            border-radius: 0.375rem;
            padding: 0.2rem 0.5rem;
            white-space: nowrap;
        }

        .profile-account-tabs {
            display: inline-flex;
            gap: 0.25rem;
            padding: 0.25rem;
            background: #f4f6fb;
            border: 1px solid #e7eaf3;
            border-radius: 0.5rem;
        }

        .profile-account-tabs .nav-link {
            min-width: 140px;
            padding: 0.7rem 1rem;
            color: #6b7280;
            font-weight: 600;
            background: transparent;
            border-radius: 0.375rem;
        }

        .profile-account-tabs .nav-link:hover {
            color: var(--bs-primary);
            background: #ffffff;
        }

        .profile-account-tabs .nav-link.active {
            color: #ffffff;
            background: var(--bs-primary);
            box-shadow: 0 0.25rem 0.75rem rgba(var(--bs-primary-rgb), 0.18);
        }

        .profile-summary-list .list-group-item {
            display: grid;
            grid-template-columns: minmax(7.5rem, 42%) minmax(0, 1fr);
            gap: 1rem;
            align-items: start;
        }

        .profile-summary-label {
            color: #6b7280;
            white-space: nowrap;
        }

        .profile-summary-value {
            min-width: 0;
            color: #4b5563;
            text-align: right;
            overflow-wrap: anywhere;
            line-height: 1.35;
        }

        @media (max-width: 575.98px) {
            .profile-account-tabs {
                display: flex;
                width: 100%;
            }

            .profile-account-tabs .nav-item,
            .profile-account-tabs .nav-link {
                flex: 1 1 0;
                min-width: 0;
            }

            .profile-summary-list .list-group-item {
                grid-template-columns: 1fr;
                gap: 0.25rem;
            }

            .profile-summary-value {
                text-align: left;
            }
        }
    </style>
@endsection

@section('navbarTitle', 'Edit Profile')


@section('content')

@include('layouts.breadcrumb', [
    'title' => 'Profile',
    'current' => 'Profile',
    'homeRoute' => 'dashboard',
])

@php
    $isPasswordTabActive = session()->has('password_status') || $errors->passwordUpdate->any();
    $employeeDataCompletionPercent = (int) ($employeeDataCompletionPercent ?? 0);
@endphp

@if ($employeeDataCompletionPercent < 100)
    <div class="alert alert-warning js-employee-data-incomplete-alert" role="alert">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="alert-heading mb-1"><i class="fa fa-exclamation-circle me-2"></i>Data karyawan belum lengkap</h5>
                <p class="mb-0">Silakan lengkapi data diri, identitas, pendidikan, dan keluarga pada tab <strong>Data Karyawan</strong>.</p>
            </div>
            <button type="button" class="btn btn-warning btn-sm js-open-employee-data-tab">Lengkapi Sekarang</button>
        </div>
    </div>
@endif

<div class="row">
					
    <!-- Start - Portfolio -->
    <div class="col-xl-3">
        <div class="card h-auto">
            <div class="card-body py-sm-5">
                <div class="text-center">
                    <div class="avatar avatar-xl avatar-preview rounded-circle">
                        <img class="imagePreview w-100 h-100" src="{{ $profilePageAvatarUrl }}" alt="{{ $profilePageDisplayName }}">
                        <input type="file" id="profilePictureInput" class="imageUpload d-none js-profile-picture-input" accept=".jpg,.jpeg,.png" data-upload-url="{{ route('profile.photo.update') }}" data-csrf-token="{{ csrf_token() }}">
                        <a class="btn btn-square btn-primary btn-sm position-absolute bottom-0 end-0 shadow-sm upload-trigger rounded-circle border-2 border-white">
                            <i class="fa fa-camera "></i>
                        </a>
                    </div>
                    <div class="clearfix mt-3">
                        <h6 class="mb-0">{{ $profilePageDisplayName }}</h6>
                        <span>{{ $profilePagePositionName }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-2 px-4 pt-3 pb-2 border-top">
                <h6 class="mb-0">Data Kepegawaian</h6>
                <span class="profile-locked-badge"><i class="fa fa-lock me-1"></i>Dikelola HR</span>
            </div>
            <ul class="list-group list-group-flush profile-summary-list">
                <li class="list-group-item py-3"><span class="profile-summary-label">Employee ID</span><span class="profile-summary-value">{{ $profilePageEmployeeCode }}</span></li>
                <li class="list-group-item py-3"><span class="profile-summary-label">Company</span><span class="profile-summary-value">{{ $profileFormCompany }}</span></li>
                <li class="list-group-item py-3"><span class="profile-summary-label">Position</span><span class="profile-summary-value">{{ $profilePagePositionName }}</span></li>
                <li class="list-group-item py-3"><span class="profile-summary-label">Department</span><span class="profile-summary-value">{{ $profilePageDepartmentName }}</span></li>
                <li class="list-group-item py-3"><span class="profile-summary-label">PIC</span><span class="profile-summary-value">{{ $employeeEmploymentDetails['supervisor'] ?? '-' }}</span></li>
                <li class="list-group-item py-3"><span class="profile-summary-label">Join Date</span><span class="profile-summary-value">{{ $profilePageJoinDateLabel }}</span></li>
                <li class="list-group-item py-3"><span class="profile-summary-label">Work Location</span><span class="profile-summary-value">{{ $profileFormBaseOffice }}</span></li>
                <li class="list-group-item py-3"><span class="profile-summary-label">Work Email</span><span class="profile-summary-value">{{ $profileFormEmail }}</span></li>
            </ul>
        </div>
    </div>
    <!-- End - Portfolio -->

    <!-- Start - Account Setup -->
    <div class="col-xl-9">
        <div class="card h-auto m-b30">
            <div class="card-header">
                <h6 class="card-title">Account Setup</h6>
            </div>
            <div class="card-body pb-0">
                <ul class="nav nav-pills profile-account-tabs" id="profileAccountSetupTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $isPasswordTabActive ? '' : 'active' }}" id="profile-update-tab" data-bs-toggle="tab" data-bs-target="#profile-update-pane" type="button" role="tab" aria-controls="profile-update-pane" aria-selected="{{ $isPasswordTabActive ? 'false' : 'true' }}">Update Profile</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $isPasswordTabActive ? 'active' : '' }}" id="password-update-tab" data-bs-toggle="tab" data-bs-target="#password-update-pane" type="button" role="tab" aria-controls="password-update-pane" aria-selected="{{ $isPasswordTabActive ? 'true' : 'false' }}">Update Password</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="employee-data-tab" data-bs-toggle="tab" data-bs-target="#employee-data-pane" type="button" role="tab" aria-controls="employee-data-pane" aria-selected="false">
                            Data Karyawan
                            @if ($employeeDataCompletionPercent < 100)
                                <span class="badge profile-tab-alert-badge ms-1">!</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="employee-documents-tab" data-bs-toggle="tab" data-bs-target="#employee-documents-pane" type="button" role="tab" aria-controls="employee-documents-pane" aria-selected="false">
                            Dokumen
                            @if ($hasMissingRequiredEmployeeDocuments ?? false)
                                <span class="badge profile-tab-alert-badge ms-1">!</span>
                            @endif
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content" id="profileAccountSetupTabContent">
                <div class="tab-pane fade {{ $isPasswordTabActive ? '' : 'show active' }}" id="profile-update-pane" role="tabpanel" aria-labelledby="profile-update-tab" tabindex="0">
                    <form id="profileInformationForm" action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="card-body">
                    @include('partials.swal-alert', ['type' => 'success', 'message' => session('profile_status')])
                    @if ($errors->any())
                        @include('partials.swal-alert', ['type' => 'error', 'message' => implode("\n", $errors->all())])
                    @endif
                    @php
                        $profileNameValue = old('name', $profileFormName === '-' ? '' : $profileFormName);
                        $profileGenderValue = old('gender', $profileFormGender === '-' ? '' : $profileFormGender);
                        $profileMaritalStatusValue = old('marital_status', $profileFormMaritalStatus === '-' ? '' : $profileFormMaritalStatus);
                        $profileBirthValue = old('birth', $profileFormBirthValue);
                        $profileBirthDisplayValue = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $profileBirthValue)
                            ? substr((string) $profileBirthValue, 8, 2) . '/' . substr((string) $profileBirthValue, 5, 2) . '/' . substr((string) $profileBirthValue, 0, 4)
                            : '';
                        $profilePhoneValue = old('phone', $profileFormPhone === '-' ? '' : $profileFormPhone);
                        $profileEmailValue = old('email', $profileFormEmail === '-' ? '' : $profileFormEmail);
                        $profileAddressValue = old('address', $profileFormAddress === '-' ? '' : $profileFormAddress);
                    @endphp
                    <div class="row">
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $profileNameValue }}">
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="default-select form-control">
                                <option value="">Open this select menu</option>
                                @foreach (['Male', 'Female'] as $gender)
                                    <option value="{{ $gender }}" @selected($profileGenderValue === $gender)>{{ $gender }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Marital Status</label>
                            <select name="marital_status" class="default-select form-control">
                                <option value="">Open this select menu</option>
                                @foreach (array_keys(\App\Support\Employee\EmployeeDataOptions::MARITAL_STATUSES) as $status)
                                    <option value="{{ $status }}" @selected($profileMaritalStatusValue === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Birth</label>
                            <input type="hidden" name="birth" id="profileBirthValue" value="{{ $profileBirthValue }}">
                            <input type="text" id="profileBirthDisplay" class="form-control js-profile-birth-picker" value="{{ $profileBirthDisplayValue }}" placeholder="DD/MM/YYYY" autocomplete="off" readonly data-date-target="#profileBirthValue">
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ $profilePhoneValue }}">
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $profileEmailValue }}">
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Current Position</label>
                            <input type="text" name="current_position" class="form-control" value="{{ $profileFormCurrentPosition }}" disabled aria-disabled="true" data-disabled-field="true">
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" class="form-control" value="{{ $profileFormCompany }}" disabled aria-disabled="true" data-disabled-field="true">
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label class="form-label">Base Office</label>
                            <input type="text" name="base_office" class="form-control" value="{{ $profileFormBaseOffice }}" disabled aria-disabled="true" data-disabled-field="true">
                        </div>
                        <div class="col-12 mb-4">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3">{{ $profileAddressValue }}</textarea>
                        </div>
                    </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary me-1">UPDATE PROFILE</button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade {{ $isPasswordTabActive ? 'show active' : '' }}" id="password-update-pane" role="tabpanel" aria-labelledby="password-update-tab" tabindex="0">
                    <form id="profilePasswordForm" action="{{ route('profile.password.update') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="card-body">
                    @include('partials.swal-alert', ['type' => 'success', 'message' => session('password_status')])
                    @if ($errors->passwordUpdate->any())
                        @include('partials.swal-alert', ['type' => 'error', 'message' => implode("\n", $errors->passwordUpdate->all())])
                    @endif
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Confirmation Password</label>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                    </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary me-1">UPDATE PASSWORD</button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="employee-data-pane" role="tabpanel" aria-labelledby="employee-data-tab" tabindex="0">
                    <div class="card-body">
                        @include('employee_data.wizard.wizard', ['isEmploymentStepReadOnly' => true])
                    </div>
                </div>
                <div class="tab-pane fade" id="employee-documents-pane" role="tabpanel" aria-labelledby="employee-documents-tab" tabindex="0">
                    <div class="card-body">
                        @include('employee_data.documents.vault', ['documentVaultMode' => 'employee', 'employeeDocumentFiles' => $employeeDocumentFiles ?? []])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Start - Account Setup -->

    </div>

@endsection

@section('script')
    @include('employee_data.wizard.script')
    @include('employee_data.documents.script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var employeeDataTabButton = document.getElementById('employee-data-tab');

            if (!employeeDataTabButton) {
                return;
            }

            function openEmployeeDataTab() {
                bootstrap.Tab.getOrCreateInstance(employeeDataTabButton).show();
                employeeDataTabButton.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            document.querySelectorAll('.js-open-employee-data-tab').forEach(function (button) {
                button.addEventListener('click', openEmployeeDataTab);
            });

            if (window.location.hash === '#employee-data') {
                openEmployeeDataTab();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            function toggleTabAlertBadge(tabButtonId, isIncomplete) {
                var tabButton = document.getElementById(tabButtonId);
                var alertBadge = tabButton ? tabButton.querySelector('.profile-tab-alert-badge') : null;

                if (!tabButton) {
                    return;
                }

                if (isIncomplete && !alertBadge) {
                    alertBadge = document.createElement('span');
                    alertBadge.className = 'badge profile-tab-alert-badge ms-1';
                    alertBadge.textContent = '!';
                    tabButton.appendChild(alertBadge);
                }

                if (!isIncomplete && alertBadge) {
                    alertBadge.remove();
                }
            }

            document.addEventListener('employee-data:saved', function (event) {
                var isComplete = !!(event.detail && event.detail.is_employee_data_complete);
                var incompleteAlert = document.querySelector('.js-employee-data-incomplete-alert');

                toggleTabAlertBadge('employee-data-tab', !isComplete);

                if (incompleteAlert) {
                    incompleteAlert.classList.toggle('d-none', isComplete);
                }
            });

            document.addEventListener('employee-documents:changed', function (event) {
                toggleTabAlertBadge('employee-documents-tab', !!(event.detail && event.detail.has_missing_required_documents));
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (!window.jQuery || !jQuery.fn.daterangepicker || typeof moment === 'undefined') {
                return;
            }

            var $birthDisplayInput = jQuery('#profileBirthDisplay');

            if (!$birthDisplayInput.length || $birthDisplayInput.data('daterangepicker-initialized')) {
                return;
            }

            var $birthValueInput = jQuery($birthDisplayInput.data('date-target'));
            var currentBirthValue = $birthValueInput.val();
            var pickerOptions = {
                autoApply: true,
                autoUpdateInput: false,
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'DD/MM/YYYY',
                    cancelLabel: 'Clear'
                }
            };

            if (currentBirthValue && moment(currentBirthValue, 'YYYY-MM-DD', true).isValid()) {
                pickerOptions.startDate = moment(currentBirthValue, 'YYYY-MM-DD');
                $birthDisplayInput.val(pickerOptions.startDate.format('DD/MM/YYYY'));
            }

            $birthDisplayInput.daterangepicker(pickerOptions);

            $birthDisplayInput.on('apply.daterangepicker', function (event, picker) {
                $birthDisplayInput.val(picker.startDate.format('DD/MM/YYYY'));
                $birthValueInput.val(picker.startDate.format('YYYY-MM-DD'));
            });

            $birthDisplayInput.on('cancel.daterangepicker', function () {
                $birthDisplayInput.val('');
                $birthValueInput.val('');
            });

            $birthDisplayInput.data('daterangepicker-initialized', true);
        });

        document.addEventListener('DOMContentLoaded', function () {
            var profilePictureInput = document.getElementById('profilePictureInput');

            if (!profilePictureInput) {
                return;
            }

            profilePictureInput.addEventListener('change', function () {
                var file = profilePictureInput.files && profilePictureInput.files[0] ? profilePictureInput.files[0] : null;
                var uploadUrl = profilePictureInput.getAttribute('data-upload-url');
                var csrfToken = profilePictureInput.getAttribute('data-csrf-token');
                var previewImage = profilePictureInput.parentElement
                    ? profilePictureInput.parentElement.querySelector('.imagePreview')
                    : null;
                var previousPreviewUrl = previewImage ? previewImage.getAttribute('src') : '';

                if (!file || !uploadUrl || !csrfToken) {
                    return;
                }

                var allowedExtensions = ['jpg', 'jpeg', 'png'];
                var fileExtension = file.name && file.name.indexOf('.') !== -1
                    ? file.name.split('.').pop().toLowerCase()
                    : '';

                if (allowedExtensions.indexOf(fileExtension) === -1) {
                    window.alert('Foto profile hanya boleh menggunakan file JPG, JPEG, atau PNG.');
                    profilePictureInput.value = '';

                    return;
                }

                if (file.size > 1024 * 1024) {
                    window.alert('Ukuran foto profile maksimal 1MB.');
                    profilePictureInput.value = '';

                    return;
                }

                var formData = new FormData();
                formData.append('profile_picture', file);

                fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            if (!response.ok) {
                                throw payload;
                            }

                            return payload;
                        });
                    })
                    .then(function (payload) {
                        if (!payload.avatar_url) {
                            return;
                        }

                        if (previewImage) {
                            previewImage.setAttribute('src', payload.avatar_url);
                        }

                        document.querySelectorAll('.header-profile img').forEach(function (image) {
                            image.setAttribute('src', payload.avatar_url);
                        });
                    })
                    .catch(function (error) {
                        if (previewImage && previousPreviewUrl) {
                            previewImage.setAttribute('src', previousPreviewUrl);
                        }

                        var message = error && error.message
                            ? error.message
                            : 'Gagal memperbarui foto profile.';

                        if (error && error.errors && error.errors.profile_picture && error.errors.profile_picture[0]) {
                            message = error.errors.profile_picture[0];
                        }

                        window.alert(message);
                    })
                    .finally(function () {
                        profilePictureInput.value = '';
                    });
            });
        });
    </script>
@endsection
