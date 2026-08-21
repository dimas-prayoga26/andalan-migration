@extends('layouts.main')

@section('title', 'Authorization - Assign Event Division')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2.min.css') }}">
    <style>
        .authorization-nav-card {
            border-radius: 8px;
        }

        .authorization-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            white-space: nowrap;
        }

        .authorization-tabs .nav-link {
            border: 0;
            border-bottom: 3px solid transparent;
            color: #6b7280;
            background: transparent;
        }

        .authorization-tabs .nav-link.active {
            color: var(--bs-primary);
            border-bottom-color: var(--bs-primary);
            background: transparent;
        }

        .authorization-access-form .select2-container {
            width: 100% !important;
        }

        .authorization-access-form .bootstrap-select {
            display: none !important;
        }

        .authorization-access-form .select2-container--default .select2-selection--single,
        .authorization-access-form .select2-container--default .select2-selection--multiple {
            min-height: 44px;
            border-color: #e5e7eb;
            border-radius: 8px;
        }

        .authorization-access-form .select2-container--default .select2-selection--multiple {
            padding: 6px 10px;
        }

        .authorization-access-form .select2-container--default .select2-selection--multiple .select2-selection__choice {
            margin-top: 2px;
            margin-bottom: 2px;
            border-color: #d1d5db;
            background: #f3f4f6;
            border-radius: 6px;
        }

        .authorization-access-form .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            border-right-color: #d1d5db;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background: var(--bs-primary);
            color: #fff;
            font-weight: 600;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--bs-primary);
            color: #fff;
        }

        .select2-container--default .select2-results__option[aria-disabled=true] {
            color: #9ca3af;
            cursor: not-allowed;
            background: #f9fafb;
        }

        .authorization-event-division-actions {
            white-space: nowrap;
        }
    </style>
@endsection

@section('navbarTitle', 'Authorization')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Authorization',
    'current' => 'Assign Event Division',
    'homeRoute' => 'dashboard',
])

<div class="card authorization-nav-card">
    <div class="card-header py-0">
        <ul class="nav nav-underline authorization-tabs gap-3">
            <li class="nav-item">
                <a class="nav-link py-3 px-1" href="{{ route('authorization') }}">Employee List</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 px-1" href="{{ route('authorization.access-menus') }}">Assign Permission</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 px-1 active" href="{{ route('authorization.event-divisions') }}">Assign Event Division</a>
            </li>
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 flex-wrap gap-3">
        <div>
            <h4 class="card-title mb-1">Assign Event Division</h4>
            <p class="mb-0 text-muted fs-13">Assign staff into one event division each. Picking a staff into a division removes them from any other division.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary light btn-sm" data-bs-toggle="modal" data-bs-target="#addEventDivisionModal">
                <i class="fa-solid fa-plus me-2"></i>Add Event Division
            </button>
            <button type="submit" form="authorizationEventDivisionForm" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-floppy-disk me-2"></i>Save Access
            </button>
        </div>
    </div>
    <div class="card-body pt-0">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">Periksa kembali data yang dipilih atau diisi.</div>
        @endif

        <form id="authorizationEventDivisionForm" action="{{ route('authorization.event-divisions.update') }}" method="POST">
            @csrf
            <div class="authorization-access-form table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Event Division</th>
                            <th style="min-width: 420px;">Assign Staff</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($eventDivisionAssignments as $eventDivisionAssignment)
                            @php
                                $selectedEmployeeIds = old("division_employees.{$eventDivisionAssignment['id']}", $eventDivisionAssignment['employee_ids']);
                                $selectedEmployeeIds = is_array($selectedEmployeeIds) ? $selectedEmployeeIds : [];
                            @endphp
                            <tr>
                                <td>
                                    <h6 class="mb-1 text-black">{{ $eventDivisionAssignment['title'] }}</h6>
                                    @if ($eventDivisionAssignment['sub_title'] !== '')
                                        <span class="text-muted fs-13">{{ $eventDivisionAssignment['sub_title'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    <select
                                        class="form-control authorization-select2 js-skip-selectpicker js-event-division-select"
                                        id="eventDivisionEmployeeSelect-{{ $eventDivisionAssignment['id'] }}"
                                        name="division_employees[{{ $eventDivisionAssignment['id'] }}][]"
                                        multiple
                                        data-placeholder="Select staff"
                                    >
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee['id'] }}" @selected(in_array($employee['id'], $selectedEmployeeIds, true))>{{ $employee['label'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end authorization-event-division-actions">
                                    <button
                                        type="button"
                                        class="btn btn-primary light btn-sm js-edit-event-division"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editEventDivisionModal"
                                        data-update-url="{{ route('authorization.event-divisions.divisions.update', ['eventDivision' => $eventDivisionAssignment['id']]) }}"
                                        data-title="{{ $eventDivisionAssignment['title'] }}"
                                        data-sub-title="{{ $eventDivisionAssignment['sub_title'] }}"
                                    >
                                        Update
                                    </button>
                                    <button
                                        type="submit"
                                        form="deleteEventDivisionForm-{{ $eventDivisionAssignment['id'] }}"
                                        class="btn btn-danger light btn-sm"
                                        onclick="return confirm('Delete this event division?')"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No event division data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @foreach ($eventDivisionAssignments as $eventDivisionAssignment)
            <form
                id="deleteEventDivisionForm-{{ $eventDivisionAssignment['id'] }}"
                action="{{ route('authorization.event-divisions.divisions.destroy', ['eventDivision' => $eventDivisionAssignment['id']]) }}"
                method="POST"
                class="d-none"
            >
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>
</div>

<div class="modal fade" id="addEventDivisionModal" tabindex="-1" aria-labelledby="addEventDivisionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('authorization.event-divisions.divisions.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addEventDivisionModalLabel">Add Event Division</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Event Division</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="sub_title" class="form-control @error('sub_title') is-invalid @enderror" value="{{ old('sub_title') }}">
                    @error('sub_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-2"></i>Add Event Division
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editEventDivisionModal" tabindex="-1" aria-labelledby="editEventDivisionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" id="editEventDivisionForm">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title" id="editEventDivisionModalLabel">Update Event Division</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Event Division</label>
                    <input type="text" name="title" id="editEventDivisionTitle" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="sub_title" id="editEventDivisionSubTitle" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-regular fa-floppy-disk me-2"></i>Update Event Division
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        (function () {
            function initializeAuthorizationSelect2() {
                $('.authorization-select2').each(function () {
                    var selectElement = $(this);

                    if ($.fn.selectpicker && selectElement.data('selectpicker')) {
                        selectElement.selectpicker('destroy');
                    }

                    selectElement.siblings('.bootstrap-select').remove();

                    if ($.fn.select2) {
                        if (selectElement.hasClass('select2-hidden-accessible')) {
                            selectElement.select2('destroy');
                        }

                        selectElement.select2({
                            placeholder: selectElement.data('placeholder'),
                            closeOnSelect: false,
                            width: '100%'
                        });
                    }
                });
            }

            function enforceSingleDivisionPerStaff() {
                function selectedEmployeeIds(selectElement) {
                    return selectElement.val() || [];
                }

                function refreshSelectAvailability() {
                    $('.js-event-division-select').each(function () {
                        var currentSelect = $(this);
                        var currentValues = selectedEmployeeIds(currentSelect);
                        var selectedElsewhere = [];

                        $('.js-event-division-select').not(currentSelect).each(function () {
                            selectedElsewhere = selectedElsewhere.concat(selectedEmployeeIds($(this)));
                        });

                        currentSelect.find('option').each(function () {
                            var option = $(this);
                            var employeeId = option.val();
                            var isSelectedHere = currentValues.indexOf(employeeId) !== -1;
                            var isSelectedElsewhere = selectedElsewhere.indexOf(employeeId) !== -1;

                            option.prop('disabled', isSelectedElsewhere && ! isSelectedHere);
                        });

                        if (currentSelect.hasClass('select2-hidden-accessible')) {
                            currentSelect.trigger('change.select2');
                        }
                    });
                }

                $(document).on('select2:select', '.js-event-division-select', function (event) {
                    var currentSelect = $(this);
                    var selectedEmployeeId = event.params.data.id;

                    $('.js-event-division-select').not(currentSelect).each(function () {
                        var otherSelect = $(this);
                        var otherValues = otherSelect.val() || [];

                        if (otherValues.indexOf(selectedEmployeeId) !== -1) {
                            otherValues = otherValues.filter(function (value) {
                                return value !== selectedEmployeeId;
                            });
                            otherSelect.val(otherValues).trigger('change');
                        }
                    });

                    refreshSelectAvailability();
                });

                $(document).on('change', '.js-event-division-select', function () {
                    refreshSelectAvailability();
                });

                refreshSelectAvailability();
            }

            function initializeEventDivisionEditor() {
                $(document).on('click', '.js-edit-event-division', function () {
                    var button = $(this);

                    $('#editEventDivisionForm').attr('action', button.data('update-url'));
                    $('#editEventDivisionTitle').val(button.data('title') || '');
                    $('#editEventDivisionSubTitle').val(button.data('sub-title') || '');
                });
            }

            $(function () {
                initializeAuthorizationSelect2();
                enforceSingleDivisionPerStaff();
                initializeEventDivisionEditor();
            });
            $(window).on('load', initializeAuthorizationSelect2);
        })();
    </script>
@endsection
