@extends('layouts.main')

@section('title', 'Setup Meeting')

@section('navbarTitle', 'Setup Meeting')

@section('css')
@php
    $sweetAlertCssPath = public_path('assets/vendor/sweetalert2/sweetalert2.min.css');
    $sweetAlertCssVersion = file_exists($sweetAlertCssPath) ? filemtime($sweetAlertCssPath) : time();
@endphp
<link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}?v={{ $sweetAlertCssVersion }}">
<style>
    .meeting-table-card {
        overflow: hidden;
    }

    .meeting-table-card .card-header {
        padding: 1.75rem 1.875rem 1.1rem;
    }

    .meeting-table-card .table-responsive {
        padding: 0 1.875rem 1rem;
    }

    #datatable_wrapper .dt-layout-row:first-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin: 0 0 1rem;
    }

    #datatable_wrapper .dt-layout-row:first-child .dt-layout-cell {
        display: flex;
        align-items: center;
        width: auto;
    }

    #datatable_wrapper .dt-layout-row:first-child .dt-layout-cell:last-child {
        margin-left: auto;
    }

    #datatable_wrapper .dt-length,
    #datatable_wrapper .dt-search,
    #datatable_wrapper .dataTables_length label,
    #datatable_wrapper .dataTables_filter label {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        margin: 0;
    }

    #datatable_wrapper .dt-length label,
    #datatable_wrapper .dt-search label,
    #datatable_wrapper .dataTables_length label,
    #datatable_wrapper .dataTables_filter label,
    #datatable_wrapper .dt-info {
        color: #5f6b7a;
        font-weight: 600;
    }

    #datatable_wrapper .dt-length select,
    #datatable_wrapper .dt-search input,
    #datatable_wrapper .dataTables_length select,
    #datatable_wrapper .dataTables_filter input {
        min-height: 38px;
        border: 1px solid #d9dce5;
        border-radius: 0.5rem;
        background: #fff;
        color: #27334a;
        box-shadow: none;
    }

    #datatable_wrapper .dt-search input,
    #datatable_wrapper .dataTables_filter input {
        width: 240px;
        max-width: 100%;
        padding: 0.35rem 0.75rem;
    }

    #datatable_wrapper .dt-length select,
    #datatable_wrapper .dataTables_length select {
        min-width: 78px;
        padding: 0.35rem 2rem 0.35rem 0.75rem;
    }

    #datatable {
        margin-bottom: 0 !important;
    }

    #datatable thead th {
        color: #071739;
        font-size: 1rem;
        font-weight: 700;
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    #datatable tbody td {
        color: #5f6b7a;
        font-size: 0.95rem;
        padding: 0.9rem 0.75rem;
        vertical-align: middle;
    }

    #datatable thead th:first-child,
    #datatable tbody td:first-child {
        text-align: center !important;
        width: 70px;
    }

    #datatable_wrapper .dt-layout-row:last-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-top: 1px solid #edf0f5;
        padding-top: 1rem;
        margin-top: 0.25rem;
    }

    #datatable_wrapper .dt-paging,
    #datatable_wrapper .dataTables_paginate {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    #datatable_wrapper .dt-paging button,
    #datatable_wrapper .dataTables_paginate .paginate_button {
        border: 0 !important;
        border-radius: 0.5rem !important;
        background: #f3f5fb !important;
        color: #071739 !important;
        min-width: 34px;
        min-height: 34px;
        padding: 0.35rem 0.75rem !important;
        box-shadow: none !important;
    }

    #datatable_wrapper .dt-paging button.current,
    #datatable_wrapper .dataTables_paginate .paginate_button.current {
        background: #2444c3 !important;
        color: #fff !important;
    }

    #datatable_wrapper .dt-paging button.disabled,
    #datatable_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.55;
    }

    @media only screen and (max-width: 767.98px) {
        .meeting-table-card .card-header {
            align-items: stretch !important;
            flex-direction: column;
            padding: 1.25rem;
        }

        .meeting-table-card .card-header > div {
            width: 100%;
            justify-content: space-between;
        }

        .meeting-table-card .table-responsive {
            padding: 0 1.25rem 1rem;
        }

        #datatable_wrapper .dt-layout-row:first-child,
        #datatable_wrapper .dt-layout-row:last-child {
            align-items: stretch;
            flex-direction: column;
        }

        #datatable_wrapper .dt-layout-row:first-child .dt-layout-cell,
        #datatable_wrapper .dt-length,
        #datatable_wrapper .dt-search,
        #datatable_wrapper .dataTables_length label,
        #datatable_wrapper .dataTables_filter label {
            justify-content: space-between;
            width: 100%;
        }

        #datatable_wrapper .dt-search input,
        #datatable_wrapper .dataTables_filter input {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Setup Meeting</h1></li>
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">
                    <svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Home
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Setup Meeting</li>
        </ol>
    </nav>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Meeting</h5>
    <a href="{{ route('hr-meetings.create') }}" class="btn light btn-success btn-sm mb-1">+ New Meeting</a>
</div>

@if (session('status'))
    @include('partials.swal-alert', ['type' => 'success', 'message' => session('status')])
@endif

@if (session('error'))
    @include('partials.swal-alert', ['type' => 'error', 'message' => session('error')])
@endif

<div class="card meeting-table-card">
    <div class="card-header border-0 align-items-center gap-2 flex-wrap">
        <h4 class="card-title m-0">Meeting</h4>
        <div class="clearfix d-flex align-items-center">
            <div class="clearfix me-1">
                <select class="selectpicker form-select form-select-sm" id="meetingMonthFilter" name="month">
                    @foreach ($monthOptions as $monthValue => $monthLabel)
                        <option value="{{ $monthValue }}" @selected((int) $month === (int) $monthValue)>{{ $monthLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="clearfix">
                <select class="selectpicker form-select form-select-sm" id="meetingYearFilter" name="year">
                    @foreach ($yearOptions as $yearOption)
                        <option value="{{ $yearOption }}" @selected((int) $year === (int) $yearOption)>{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="card-body table-card-body px-0 pt-0 pb-2">
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered text-nowrap w-100"></table>
        </div>
    </div>
</div>
@endsection

@section('script')
    @php
        $dataTablesJsPath = public_path('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js');
        $dataTablesJsVersion = file_exists($dataTablesJsPath) ? filemtime($dataTablesJsPath) : time();
        $sweetAlertJsPath = public_path('assets/vendor/sweetalert2/sweetalert2.min.js');
        $sweetAlertJsVersion = file_exists($sweetAlertJsPath) ? filemtime($sweetAlertJsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js') }}?v={{ $dataTablesJsVersion }}"></script>
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}?v={{ $sweetAlertJsVersion }}"></script>
    <script>
        var csrfToken = @json(csrf_token());

        function escapeHtml(value) {
            return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
        }

        function renderMeetingStatus(meeting) {
            return '<span class="badge badge-sm ' + escapeHtml(meeting.status_badge_class) + ' light fw-semibold">'
                + escapeHtml(meeting.status)
                + '</span>';
        }

        function renderMeetingActions(meeting) {
            return '<div class="d-inline-flex align-items-center gap-1">'
                + '<a href="' + escapeHtml(meeting.details_url) + '" class="btn btn-square btn-secondary light btn-xs" title="Details">'
                + '<i class="fa-regular fa-file-lines"></i>'
                + '</a>'
                + '<a href="' + escapeHtml(meeting.update_url) + '" class="btn btn-square btn-warning light btn-xs" title="Update">'
                + '<i class="fa-solid fa-pencil"></i>'
                + '</a>'
                + '<form method="POST" action="' + escapeHtml(meeting.destroy_url) + '" class="d-inline js-meeting-delete-form" data-delete-title="Delete Meeting" data-delete-message="Delete this meeting?">'
                + '<input type="hidden" name="_token" value="' + escapeHtml(csrfToken) + '">'
                + '<input type="hidden" name="_method" value="DELETE">'
                + '<button type="submit" class="btn btn-square btn-danger light btn-xs" title="Delete">'
                + '<i class="fa-solid fa-trash"></i>'
                + '</button>'
                + '</form>'
                + '</div>';
        }

        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!form.matches('.js-meeting-delete-form')) {
                return;
            }

            if (form.dataset.deleteConfirmed === 'true') {
                return;
            }

            event.preventDefault();

            var title = form.dataset.deleteTitle || 'Delete Meeting';
            var message = form.dataset.deleteMessage || 'Delete this meeting?';

            if (typeof Swal === 'undefined' || !Swal || typeof Swal.fire !== 'function') {
                if (window.confirm(message)) {
                    form.dataset.deleteConfirmed = 'true';
                    form.submit();
                }

                return;
            }

            Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                reverseButtons: true,
                focusCancel: true,
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                form.dataset.deleteConfirmed = 'true';
                form.submit();
            });
        });

        $(function () {
            if (!window.jQuery || !window.jQuery.fn.DataTable) {
                return;
            }

            var meetingTable = $('#datatable').DataTable({
                autoWidth: false,
                ajax: {
                    url: @json(route('hr-meetings.datatable')),
                    dataSrc: 'data',
                    data: function (params) {
                        params.month = $('#meetingMonthFilter').val();
                        params.year = $('#meetingYearFilter').val();
                    }
                },
                processing: true,
                serverSide: true,
                columns: [
                    {
                        title: 'No',
                        className: 'mw-10',
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row, meta) {
                            return (meta.row + meta.settings._iDisplayStart + 1) + '.';
                        }
                    },
                    { title: 'Date', className: 'mw-80', data: 'date' },
                    { title: 'Meeting Type', className: 'mw-80', data: 'type' },
                    { title: 'Time', className: 'mw-80', data: 'time' },
                    { title: 'Joined', className: 'mw-80', data: 'joined' },
                    { title: 'Tasks', className: 'mw-80', data: 'tasks' },
                    {
                        title: 'Status',
                        className: 'mw-80',
                        data: null,
                        render: function (data, type, row) {
                            return renderMeetingStatus(row);
                        }
                    },
                    {
                        title: 'Actions',
                        className: 'mw-100',
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row) {
                            return renderMeetingActions(row);
                        }
                    }
                ],
                columnDefs: [
                    { targets: [0, 7], orderable: false }
                ],
                language: {
                    emptyTable: 'No meeting data available.'
                }
            });

            $('#meetingMonthFilter, #meetingYearFilter').on('change', function () {
                meetingTable.ajax.reload();
            });
        });
    </script>
@endsection
