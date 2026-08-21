@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')

@php
    $dashboardCssPath = public_path('assets/css/dashboard.css');
    $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    $attendanceCssPath = public_path('assets/css/attendance.css');
    $attendanceCssVersion = file_exists($attendanceCssPath) ? filemtime($attendanceCssPath) : time();
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
<link rel="stylesheet" href="{{ asset('assets/css/attendance.css') }}?v={{ $attendanceCssVersion }}">
@if ($canManageProjects ?? false)
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}">
@endif
<style>
    .project-card {
        border: 0;
        border-radius: var(--bs-border-radius-xl, 1rem);
        box-shadow: 0 5px 18px rgba(20, 24, 40, .05);
        overflow: hidden;
    }

    .project-card-description {
        min-height: 62px;
    }

    .project-card-title-wrapper {
        flex: 1 1 auto;
        min-width: 0;
    }

    .project-card-title {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .project-card-folder {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .project-card-avatar {
        width: 44px;
        height: 44px;
        border: 1px solid #e8ecf4;
        background: #fff;
    }

    .project-card-actions {
        position: relative;
        z-index: 3;
    }

    .project-card-action-button {
        align-items: center;
        border-radius: 8px;
        display: inline-flex;
        height: 34px;
        justify-content: center;
        padding: 0;
        width: 34px;
    }

    .project-team-stack {
        display: flex;
        align-items: center;
        min-height: 30px;
        padding-left: 2px;
    }

    .project-team-avatar {
        width: 30px;
        height: 30px;
        border: 2px solid #fff;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(20, 24, 40, .12);
        margin-left: -8px;
        overflow: hidden;
        background: #eef3ff;
        color: #2445c7;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
    }

    .project-team-avatar:first-child {
        margin-left: 0;
    }

    .project-team-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .project-team-more {
        background: #2445c7;
        color: #fff;
    }

    .project-division-add-task {
        appearance: none;
        background: transparent;
        border: 0;
        color: #2bc155;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
        padding: 0;
        white-space: nowrap;
    }

    .project-division-add-task:hover,
    .project-division-add-task:focus {
        color: #22a447;
        text-decoration: none;
    }

    .project-add-button {
        align-items: center;
        border-radius: 8px;
        display: inline-flex;
        gap: 6px;
        min-height: 34px;
        padding: 8px 14px;
        white-space: nowrap;
    }

    .project-create-form .select2-container {
        width: 100% !important;
    }

    .project-create-form .select2-container--default .select2-selection--multiple {
        min-height: 46px;
        border-color: #e5e7eb;
        border-radius: 8px;
        padding: 6px 10px;
    }

    .project-create-form .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #2445c7;
        box-shadow: 0 0 0 .15rem rgba(36, 69, 199, .12);
    }

    .project-create-form .select2-container--default .select2-selection--multiple .select2-selection__choice {
        align-items: center;
        background: #f4f6fb;
        border: 1px solid #d9deea;
        border-radius: 8px;
        color: #111827;
        display: inline-flex;
        font-size: 12px;
        font-weight: 600;
        gap: 6px;
        margin: 3px 6px 3px 0;
        padding: 4px 8px;
    }

    .project-create-form .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        border: 0;
        color: #64748b;
        font-size: 16px;
        line-height: 1;
        margin: 0;
        order: 2;
        padding: 0;
    }

    .project-create-form .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background: transparent;
        color: #ef4444;
    }

    .project-create-form .select2-search--inline .select2-search__field {
        margin-top: 5px;
    }

    .project-create-form .select2-container--default .select2-selection--single {
        align-items: center;
        border-color: #e5e7eb;
        border-radius: 8px;
        display: flex;
        min-height: 46px;
    }

    .project-create-form .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #374151;
        line-height: 44px;
        padding-left: 16px;
        padding-right: 32px;
    }

    .project-create-form .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px;
        right: 10px;
    }

    .project-create-select2-dropdown {
        z-index: 1065;
    }

    .project-create-select2-dropdown .select2-results__option {
        align-items: center;
        display: flex;
        gap: 10px;
        justify-content: space-between;
    }

    .project-create-select2-dropdown .select2-results__option[aria-selected="true"] {
        background: #eef3ff;
        color: #111827;
        font-weight: 700;
    }

    .project-create-select2-dropdown .select2-results__option[aria-selected="true"]::after {
        color: #2445c7;
        content: "\f00c";
        flex: 0 0 auto;
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free";
        font-size: 12px;
        font-weight: 900;
    }

    .project-create-select2-dropdown .select2-results__option--highlighted[aria-selected="true"]::after {
        color: #fff;
    }

    .project-create-date-range-input {
        background-color: #fff;
        cursor: pointer;
    }

    #projectCreateModal .daterangepicker {
        z-index: 1066;
    }
</style>

@endsection

@section('content')

@include('layouts.breadcrumb', [
    'title' => 'Project Management',
    'current' => 'Projects',
    'homeRoute' => 'dashboard',
])

@include('project_management.layouts.profile-index')

@php
    $projectCards = collect($projectCards ?? []);
    $projectCompanyOptions = collect($projectCompanyOptions ?? []);
    $projectCityOptions = collect($projectCityOptions ?? []);
    $projectProvinceOptions = collect($projectProvinceOptions ?? []);
    $projectStaffOptions = collect($projectStaffOptions ?? []);
@endphp

<div class="tab-content" id="tabContentMyProfileBottom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Projects</h5>
        @if ($canManageProjects ?? false)
            <button type="button" class="btn btn-primary btn-sm project-add-button" data-bs-toggle="modal" data-bs-target="#projectCreateModal">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Project</span>
            </button>
        @endif
    </div>

    <div class="row">
        @forelse ($projectCards as $projectCard)
            <div class="col-xxl-3 col-xl-4 col-sm-6">
                <div class="card project-card h-100">
                    <div class="card-body">
                        <div class="clearfix d-flex">
                            <div class="avatar avatar-sm rounded me-3 p-2 project-card-avatar">
                                <img src="{{ $projectCard['image_url'] ?? asset('assets/images/files/folder.avif') }}" class="project-card-folder" alt="">
                            </div>
                            <div class="clearfix pe-2 project-card-title-wrapper">
                                <h6 class="mb-0 fw-semibold">
                                    <a href="{{ $projectCard['detail_url'] }}" class="stretched-link project-card-title" title="{{ $projectCard['name'] }}">{{ $projectCard['name'] }}</a>
                                </h6>
                                <span class="small">{{ $projectCard['client_name'] }}</span>
                            </div>
                            @if ($canManageProjects ?? false)
                                <div class="dropdown project-card-actions ms-auto">
                                    <button type="button" class="btn btn-light project-card-action-button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Project actions">
                                        <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button type="button" class="dropdown-item js-project-edit" data-project-id="{{ $projectCard['id'] }}">
                                                <i class="fa-solid fa-pen-to-square me-2" aria-hidden="true"></i>Edit
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger js-project-delete" data-project-id="{{ $projectCard['id'] }}" data-delete-url="{{ $projectCard['delete_url'] }}">
                                                <i class="fa-solid fa-trash-can me-2" aria-hidden="true"></i>Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <p class="my-3 project-card-description">{{ $projectCard['description'] }}</p>
                        <div class="clearfix">
                            <h6 class="mb-1 fw-medium">Team</h6>
                            <div class="project-team-stack">
                                @forelse (collect($projectCard['team_members'] ?? [])->take(4) as $teamMember)
                                    <span class="project-team-avatar" title="{{ $teamMember['name'] ?? 'Staff' }}">
                                        @if (! empty($teamMember['avatar_url']))
                                            <img src="{{ $teamMember['avatar_url'] }}" alt="{{ $teamMember['name'] ?? 'Staff' }}">
                                        @else
                                            {{ $teamMember['fallback_label'] ?? 'S' }}
                                        @endif
                                    </span>
                                @empty
                                    <span class="fs-14 text-muted">{{ $projectCard['team_count'] }} Staff</span>
                                @endforelse

                                @if ((int) ($projectCard['team_count'] ?? 0) > 4)
                                    <span class="project-team-avatar project-team-more">+{{ (int) $projectCard['team_count'] - 4 }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <span>Project Complete</span>
                                <span>{{ $projectCard['completion_rate'] }}%</span>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-purple" style="width: {{ $projectCard['completion_rate'] }}%;" role="progressbar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between flex-wrap">
                        <p class="mb-0 fw-medium">Due <span class="text-purple">: {{ $projectCard['due_label'] }}</span></p>
                        <span class="badge badge-sm badge-{{ $projectCard['status_class'] }} light">{{ $projectCard['status_label'] }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-1">No active project found</h5>
                        <p class="mb-0 fs-14">Project cards appear after your employee is added as an active project member.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@if ($canManageProjects ?? false)
    <div class="modal fade" id="projectCreateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="projectCreateForm" class="project-create-form" method="POST" action="{{ $projectStoreUrl ?? route('project_management.projects.store') }}" data-store-url="{{ $projectStoreUrl ?? route('project_management.projects.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="projectCreateModalTitle">Add Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="projectImageFile" class="form-label">Project Image</label>
                                <input type="file" class="form-control" id="projectImageFile" name="project_image" accept="image/jpeg,image/png,image/webp">
                            </div>
                            <div class="col-md-6">
                                <label for="projectCompanyId" class="form-label">Company <span class="required text-danger">*</span></label>
                                <select class="selectpicker form-select" id="projectCompanyId" name="company_id" data-live-search="true" title="Select company" required>
                                    @foreach ($projectCompanyOptions as $companyOption)
                                        <option value="{{ $companyOption['id'] }}">{{ $companyOption['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="projectName" class="form-label">Project Name <span class="required text-danger">*</span></label>
                                <input type="text" class="form-control" id="projectName" name="name" maxlength="255" placeholder="RNB Multi Position Launch 2026" required>
                            </div>
                            <div class="col-12">
                                <label for="projectStaffEmployeeIds" class="form-label">Staff <span class="required text-danger">*</span></label>
                                <select class="form-control project-staff-select2 js-skip-selectpicker" id="projectStaffEmployeeIds" name="staff_employee_ids[]" multiple data-placeholder="Select staff">
                                    @foreach ($projectStaffOptions as $staffOption)
                                        <option value="{{ $staffOption['id'] }}">{{ $staffOption['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="projectClientName" class="form-label">Client Name</label>
                                <input type="text" class="form-control" id="projectClientName" name="client_name" maxlength="255" placeholder="RNB">
                            </div>
                            <div class="col-md-6">
                                <label for="projectProvinceCode" class="form-label">Provinsi</label>
                                <select class="selectpicker form-select js-project-location-selectpicker" id="projectProvinceCode" name="province_code" data-live-search="true" data-width="100%" data-size="5" title="Select province">
                                    @foreach ($projectProvinceOptions as $provinceOption)
                                        <option value="{{ $provinceOption['code'] }}">{{ $provinceOption['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="projectCityCode" class="form-label">Kabupaten/Kota</label>
                                <select class="selectpicker form-select js-project-location-selectpicker" id="projectCityCode" name="city_code" data-live-search="true" data-width="100%" data-size="5" title="Select city">
                                    @foreach ($projectCityOptions as $cityOption)
                                        <option value="{{ $cityOption['code'] }}" data-province-code="{{ $cityOption['province_code'] }}">{{ $cityOption['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="projectLiveEventDateRange" class="form-label">Live Event Date</label>
                                <input type="hidden" id="projectLiveEventStartDate" name="live_event_start_date">
                                <input type="hidden" id="projectLiveEventEndDate" name="live_event_end_date">
                                <input type="text" class="form-control project-create-date-range-input js-project-create-date-range-input" id="projectLiveEventDateRange" placeholder="Select date range" autocomplete="off" readonly data-start-target="#projectLiveEventStartDate" data-end-target="#projectLiveEventEndDate">
                            </div>
                            <div class="col-md-6">
                                <label for="projectDateRange" class="form-label">Date <span class="required text-danger">*</span></label>
                                <input type="hidden" id="projectStartDate" name="start_date" required>
                                <input type="hidden" id="projectEndDate" name="end_date" required>
                                <input type="text" class="form-control project-create-date-range-input js-project-create-date-range-input" id="projectDateRange" placeholder="Select date range" autocomplete="off" readonly required data-start-target="#projectStartDate" data-end-target="#projectEndDate">
                            </div>
                            <div class="col-12">
                                <label for="projectAddress" class="form-label">Alamat</label>
                                <textarea class="form-control" id="projectAddress" name="address" rows="2" maxlength="2000" placeholder="Tambahkan alamat project"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="projectDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="projectDescription" name="description" rows="3" maxlength="5000" placeholder="Seed project for checking timesheet and reporting..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="projectCreateSubmit">Save Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@endsection

@section('script')

@php
    $dashboardJsPath = public_path('assets/js/dashboard.js');
    $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
@endphp
@if ($canManageProjects ?? false)
    <script src="{{ asset('assets/vendor/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
@endif
<script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
@if ($canManageProjects ?? false)
    <script>
        $(function () {
            var projectEditPayloads = @json($projectCards->mapWithKeys(fn ($projectCard) => [$projectCard['id'] => $projectCard['form_value'] ?? []])->all());
            var showProjectCreateAlert = function (options) {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    return window.Swal.fire(options);
                }

                if (options.showCancelButton) {
                    return Promise.resolve({
                        isConfirmed: window.confirm([options.title, options.text].filter(Boolean).join('\n')),
                    });
                }

                if (options.icon !== 'success') {
                    window.alert([options.title, options.text].filter(Boolean).join('\n'));
                }

                return Promise.resolve();
            };
            var hideProjectCreateModal = function () {
                var modalElement = document.querySelector('#projectCreateModal');

                if (window.bootstrap && modalElement) {
                    var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                    modal.hide();

                    return;
                }

                $('#projectCreateModal').modal('hide');
            };
            var showProjectCreateModal = function () {
                var modalElement = document.querySelector('#projectCreateModal');

                if (window.bootstrap && modalElement) {
                    var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                    modal.show();

                    return;
                }

                $('#projectCreateModal').modal('show');
            };
            var formatProjectCreateDateRangeDisplay = function (startDateValue, endDateValue) {
                if (! startDateValue || ! endDateValue || typeof moment === 'undefined') {
                    return '';
                }

                var startDate = moment(startDateValue, 'YYYY-MM-DD');
                var endDate = moment(endDateValue, 'YYYY-MM-DD');

                if (! startDate.isValid() || ! endDate.isValid()) {
                    return '';
                }

                return startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY');
            };
            var setProjectCreateDateRangeValue = function (rangeInput, startDateValue, endDateValue, shouldTriggerChange) {
                var startDate = startDateValue || '';
                var endDate = endDateValue || startDate;
                var startTarget = $(rangeInput.data('startTarget'));
                var endTarget = $(rangeInput.data('endTarget'));

                startTarget.val(startDate);
                endTarget.val(endDate);
                rangeInput.val(formatProjectCreateDateRangeDisplay(startDate, endDate));

                if ($.fn.daterangepicker && rangeInput.data('daterangepicker') && typeof moment !== 'undefined' && startDate && endDate) {
                    var startMoment = moment(startDate, 'YYYY-MM-DD');
                    var endMoment = moment(endDate, 'YYYY-MM-DD');

                    if (startMoment.isValid() && endMoment.isValid()) {
                        rangeInput.data('daterangepicker').setStartDate(startMoment);
                        rangeInput.data('daterangepicker').setEndDate(endMoment);
                    }
                }

                if (shouldTriggerChange !== false) {
                    rangeInput.trigger('change.projectCreateRangeValue');
                }
            };
            var clearProjectCreateDateRangeValue = function (rangeInput) {
                $(rangeInput.data('startTarget')).val('');
                $(rangeInput.data('endTarget')).val('');
                rangeInput.val('').trigger('change.projectCreateRangeValue');
            };
            var initializeProjectCreateDateRangePickers = function () {
                if (! $.fn.daterangepicker || typeof moment === 'undefined') {
                    return;
                }

                $('.js-project-create-date-range-input').each(function () {
                    var input = $(this);
                    var startTarget = $(input.data('startTarget'));
                    var endTarget = $(input.data('endTarget'));

                    input.val(formatProjectCreateDateRangeDisplay(startTarget.val(), endTarget.val()));

                    if (input.data('daterangepicker-initialized')) {
                        return;
                    }

                    input.daterangepicker({
                        autoApply: true,
                        autoUpdateInput: false,
                        parentEl: '#projectCreateModal',
                        locale: {
                            format: 'DD/MM/YYYY',
                            cancelLabel: 'Clear',
                        },
                    });

                    input.on('apply.daterangepicker', function (event, picker) {
                        setProjectCreateDateRangeValue(input, picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
                    });

                    input.on('cancel.daterangepicker', function () {
                        clearProjectCreateDateRangeValue(input);
                    });

                    input.data('daterangepicker-initialized', true);
                });
            };
            var hideProjectCreateDateRangePickers = function () {
                $('.js-project-create-date-range-input').each(function () {
                    var dateRangePicker = $(this).data('daterangepicker');

                    if (dateRangePicker && typeof dateRangePicker.hide === 'function') {
                        dateRangePicker.hide();
                    }
                });
            };
            var syncProjectLifecycleDateRange = function () {
                var liveStartDate = $('#projectLiveEventStartDate').val();
                var liveEndDate = $('#projectLiveEventEndDate').val();

                if (! liveStartDate || ! liveEndDate || $('#projectStartDate').val() || $('#projectEndDate').val()) {
                    return;
                }

                setProjectCreateDateRangeValue($('#projectDateRange'), liveStartDate, liveEndDate, false);
            };
            var bindProjectLifecycleDateDefaults = function () {
                $('#projectLiveEventDateRange')
                    .off('apply.daterangepicker.projectCreateSync change.projectCreateSync')
                    .on('apply.daterangepicker.projectCreateSync change.projectCreateSync', function () {
                        syncProjectLifecycleDateRange();
                    });
            };
            var syncProjectCityOptions = function (shouldResetCity) {
                var provinceCode = $('#projectProvinceCode').val();
                var citySelect = $('#projectCityCode');
                var selectedCityOption = citySelect.find('option:selected');

                citySelect.find('option[data-province-code]').each(function () {
                    var option = $(this);
                    var isVisible = ! provinceCode || String(option.attr('data-province-code')) === String(provinceCode);

                    option.prop('disabled', ! isVisible).prop('hidden', ! isVisible);
                });

                if (shouldResetCity || (selectedCityOption.length && selectedCityOption.prop('disabled'))) {
                    citySelect.val('');
                }

                citySelect.trigger('change');

                if ($.fn.selectpicker) {
                    citySelect.selectpicker('refresh');
                }
            };
            var bindProjectLocationDefaults = function () {
                $('#projectProvinceCode')
                    .off('change.projectLocation')
                    .on('change.projectLocation', function () {
                        syncProjectCityOptions(true);
                    });
            };
            var refreshProjectLocationSelectpickers = function () {
                if (! $.fn.selectpicker) {
                    return;
                }

                $('.js-project-location-selectpicker').selectpicker('refresh');
            };
            var initializeProjectStaffSelect2 = function () {
                var selectElement = $('#projectStaffEmployeeIds');

                if (! selectElement.length || ! $.fn.select2) {
                    return;
                }

                if ($.fn.selectpicker && selectElement.data('selectpicker')) {
                    selectElement.selectpicker('destroy');
                }

                selectElement.siblings('.bootstrap-select').remove();

                if (selectElement.hasClass('select2-hidden-accessible')) {
                    selectElement.select2('destroy');
                }

                selectElement.select2({
                    closeOnSelect: false,
                    dropdownCssClass: 'project-create-select2-dropdown',
                    dropdownParent: $('#projectCreateModal'),
                    placeholder: selectElement.data('placeholder'),
                    width: '100%',
                });

                selectElement.off('select2:opening.projectCreate').on('select2:opening.projectCreate', function () {
                    if (document.activeElement instanceof HTMLElement) {
                        document.activeElement.blur();
                    }
                });
            };
            var refreshProjectCreateSelects = function () {
                if ($.fn.selectpicker) {
                    $('#projectCompanyId').selectpicker('refresh');
                }

                initializeProjectStaffSelect2();
                refreshProjectLocationSelectpickers();
            };
            var setProjectCreateMode = function (mode, payload) {
                var form = $('#projectCreateForm');
                var isEditMode = mode === 'edit';

                form.attr('action', isEditMode ? payload.update_url : form.data('storeUrl'));
                form.data('formMethod', isEditMode ? 'PUT' : 'POST');
                $('#projectCreateModalTitle').text(isEditMode ? 'Update Project' : 'Add Project');
                $('#projectCreateSubmit').text(isEditMode ? 'Update Project' : 'Save Project');
            };
            var resetProjectCreateForm = function () {
                var form = $('#projectCreateForm');

                form[0]?.reset();
                setProjectCreateMode('create', {});
                clearProjectCreateDateRangeValue($('#projectLiveEventDateRange'));
                clearProjectCreateDateRangeValue($('#projectDateRange'));
                $('#projectStaffEmployeeIds').val(null).trigger('change');
                $('#projectCompanyId').val('');
                $('#projectProvinceCode').val('').trigger('change');
                syncProjectCityOptions(true);

                if ($.fn.selectpicker) {
                    $('#projectCompanyId').selectpicker('refresh');
                }

                refreshProjectLocationSelectpickers();
            };
            var ensureProjectStaffOptionsPresent = function (staffMembers) {
                var selectElement = $('#projectStaffEmployeeIds');

                (staffMembers || []).forEach(function (staffMember) {
                    if (! staffMember || ! staffMember.id || selectElement.find('option[value="' + staffMember.id + '"]').length) {
                        return;
                    }

                    selectElement.append($('<option></option>').attr('value', staffMember.id).text(staffMember.label || ''));
                });
            };
            var fillProjectCreateForm = function (payload) {
                resetProjectCreateForm();
                setProjectCreateMode('edit', payload);
                $('#projectCompanyId').val(payload.company_id || '');
                $('#projectName').val(payload.name || '');
                $('#projectClientName').val(payload.client_name || '');
                $('#projectProvinceCode').val(payload.province_code || '').trigger('change');
                syncProjectCityOptions(false);
                $('#projectCityCode').val(payload.city_code || '').trigger('change');
                syncProjectCityOptions(false);
                $('#projectAddress').val(payload.address || '');
                $('#projectDescription').val(payload.description || '');

                if ($.fn.selectpicker) {
                    $('#projectCompanyId').selectpicker('refresh');
                }

                refreshProjectLocationSelectpickers();

                setProjectCreateDateRangeValue($('#projectLiveEventDateRange'), payload.live_event_start_date || '', payload.live_event_end_date || '');
                setProjectCreateDateRangeValue($('#projectDateRange'), payload.start_date || '', payload.end_date || '');
                ensureProjectStaffOptionsPresent(payload.staff_members || []);
                $('#projectStaffEmployeeIds').val(payload.staff_employee_ids || []).trigger('change');
            };
            var handleProjectCreateAjaxError = function (xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var firstError = Object.values(errors)[0]?.[0];

                showProjectCreateAlert({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: firstError || xhr.responseJSON?.message || 'Gagal menambahkan project.',
                });
            };

            $('#projectCreateModal').on('shown.bs.modal', function () {
                refreshProjectCreateSelects();
                window.setTimeout(function () {
                    refreshProjectLocationSelectpickers();
                    syncProjectCityOptions(false);
                    initializeProjectCreateDateRangePickers();
                }, 0);
            });
            $('#projectCreateModal').on('hidden.bs.modal', function () {
                hideProjectCreateDateRangePickers();
                resetProjectCreateForm();
            });
            $('#projectCreateModal').on('mousedown', function (event) {
                if ($(event.target).closest('.daterangepicker, .js-project-create-date-range-input').length) {
                    return;
                }

                hideProjectCreateDateRangePickers();
            });
            $(document).on('focus', '#projectCreateModal input:not(.js-project-create-date-range-input), #projectCreateModal textarea, #projectCreateModal select', hideProjectCreateDateRangePickers);
            $(document).on('select2:opening', '#projectStaffEmployeeIds', hideProjectCreateDateRangePickers);

            initializeProjectCreateDateRangePickers();
            bindProjectLifecycleDateDefaults();
            bindProjectLocationDefaults();
            refreshProjectLocationSelectpickers();
            syncProjectCityOptions(false);
            initializeProjectStaffSelect2();

            $(window).on('load.projectLocationSelectpicker', function () {
                window.setTimeout(function () {
                    refreshProjectLocationSelectpickers();
                    syncProjectCityOptions(false);
                }, 0);
            });

            $(document).on('click', '.js-project-edit', function (event) {
                event.preventDefault();
                event.stopPropagation();

                var payload = projectEditPayloads[$(this).data('projectId')] || null;

                if (! payload) {
                    return;
                }

                refreshProjectCreateSelects();
                initializeProjectCreateDateRangePickers();
                fillProjectCreateForm(payload);
                showProjectCreateModal();
            });

            $(document).on('click', '.js-project-delete', function (event) {
                event.preventDefault();
                event.stopPropagation();

                var deleteUrl = $(this).data('deleteUrl');

                if (! deleteUrl) {
                    return;
                }

                showProjectCreateAlert({
                    icon: 'warning',
                    title: 'Delete Project?',
                    text: 'Project ini akan dihapus dari daftar project.',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                }).then(function (result) {
                    if (result.isConfirmed !== true) {
                        return;
                    }

                    $.ajax({
                        url: deleteUrl,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: @json(csrf_token()),
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        success: function (response) {
                            showProjectCreateAlert({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1100,
                                showConfirmButton: false,
                            }).then(function () {
                                window.location.href = response.redirect_url || @json(route('project_management.projects'));
                            });
                        },
                        error: handleProjectCreateAjaxError,
                    });
                });
            });

            $('#projectCreateForm').on('submit', function (event) {
                event.preventDefault();

                var form = $(this);
                var formData = new FormData(this);
                var submitButton = $('#projectCreateSubmit');
                var formMethod = form.data('formMethod') || 'POST';

                if (! formData.get('start_date') || ! formData.get('end_date')) {
                    showProjectCreateAlert({
                        icon: 'warning',
                        title: 'Tanggal belum dipilih',
                        text: 'Pilih date range project terlebih dahulu.',
                    });

                    return;
                }

                if (formMethod !== 'POST') {
                    formData.append('_method', formMethod);
                }

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    beforeSend: function () {
                        submitButton.prop('disabled', true).text('Menyimpan...');
                    },
                    success: function (response) {
                        if (response.success === true || response.status === true) {
                            showProjectCreateAlert({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1100,
                                showConfirmButton: false,
                            }).then(function () {
                                hideProjectCreateModal();
                                window.location.href = response.redirect_url || @json(route('project_management.projects'));
                            });
                        } else {
                            showProjectCreateAlert({
                                icon: 'warning',
                                title: 'Gagal',
                                text: response.message,
                            });
                        }
                    },
                    error: handleProjectCreateAjaxError,
                    complete: function () {
                        submitButton.prop('disabled', false).text((form.data('formMethod') || 'POST') === 'PUT' ? 'Update Project' : 'Save Project');
                    },
                });
            });
        });
    </script>
@endif

@endsection
