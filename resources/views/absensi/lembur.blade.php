@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css">
    <!-- Start - All Required Plugins -->
    <style>
        .absensi-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
            white-space: nowrap;
            gap: 0.25rem;
        }

        .absensi-tabs .nav-item {
            flex: 0 0 auto;
        }

        .absensi-tabs .nav-link {
            white-space: nowrap;
        }

        .absensi-tabs .absensi-tab-btn {
            border: 0;
            background: transparent;
        }

        .absensi-tabs .absensi-tab-btn:focus {
            box-shadow: none;
        }

        #myTable thead th {
            font-size: 1.1rem;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }

        #myTable tbody td {
            font-size: 1rem;
        }

        #myTable thead th:first-child,
        #myTable tbody td:first-child {
            text-align: center !important;
        }

        #myTable.dataTable tbody td.dataTables_empty {
            text-align: center !important;
        }

        .badge-attendance-empty {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.6rem;
            border-radius: 999px;
            display: inline-block;
        }

        .attendance-datetime {
            font-size: 1rem;
            font-weight: 600;
            color: #25314c;
            margin-bottom: 1rem;
            text-align: center;
        }

        .lembur-table-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #25314c;
            margin-bottom: 0.6rem;
            text-align: left;
        }

        #myTable tbody td {
            vertical-align: middle;
        }

        .lembur-status-badge {
            display: inline-block;
            border-radius: 0.4rem;
            padding: 0.25rem 0.6rem;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.2;
            border: 1px solid transparent;
        }

        .lembur-status-badge.warning {
            background: #fff7e6;
            border-color: #ffd591;
            color: #ad6800;
        }

        .lembur-status-badge.success {
            background: #f6ffed;
            border-color: #b7eb8f;
            color: #237804;
        }

        .lembur-status-badge.danger {
            background: #fff1f0;
            border-color: #ffa39e;
            color: #a8071a;
        }

        .lembur-action-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }

        .lembur-action-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .lembur-action-btn.info {
            background: #d9f2f4;
            color: #4aa3ad;
        }

        .lembur-action-btn.edit {
            background: #f8e8b7;
            color: #f2ad00;
        }

        .lembur-action-btn.delete {
            background: #f8d6e2;
            color: #ff4f7b;
        }

        #myTable_wrapper .dt-length label,
        #myTable_wrapper .dt-search label,
        #myTable_wrapper .dt-info,
        #myTable_wrapper .dt-paging {
            font-size: 1rem;
        }

        #myTable_wrapper .dt-layout-row:first-child {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.85rem;
            background: #fff;
            border: 1px solid #e6eaf2;
            border-radius: 0.75rem;
        }

        #myTable_wrapper .dt-length,
        #myTable_wrapper .dt-search {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        #myTable_wrapper .dt-search label,
        #myTable_wrapper .dt-length label {
            margin: 0;
            color: #5f6b7a;
            font-weight: 500;
        }

        #myTable_wrapper .dt-search input,
        #myTable_wrapper .dt-length select {
            min-height: 40px;
            font-size: 1rem;
            border-radius: 0.6rem;
            border: 1px solid #d9dce5;
            padding: 0.35rem 0.75rem;
            background: #fff;
            color: #27334a;
        }

        #myTable_wrapper .dt-search input {
            min-width: 220px;
        }

        #myTable_wrapper .dt-search input:focus,
        #myTable_wrapper .dt-length select:focus {
            border-color: #cfd5df;
            box-shadow: 0 0 0 0.15rem rgba(15, 23, 42, 0.08);
            outline: 0;
        }

        #myTable_wrapper .dt-scroll,
        #myTable_wrapper .dataTables_scroll {
            overflow: hidden;
        }

        #myTable_wrapper .dt-scroll-head table,
        #myTable_wrapper .dt-scroll-body table,
        #myTable_wrapper .dataTables_scrollHead table,
        #myTable_wrapper .dataTables_scrollBody table {
            min-width: 920px;
        }

        #myTable_wrapper .dt-scroll-body,
        #myTable_wrapper .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
        }

        #myTable_wrapper .dt-scroll-body thead,
        #myTable_wrapper .dataTables_scrollBody thead {
            visibility: hidden !important;
        }

        #myTable_wrapper .dt-scroll-body thead tr,
        #myTable_wrapper .dt-scroll-body thead th,
        #myTable_wrapper .dataTables_scrollBody thead tr,
        #myTable_wrapper .dataTables_scrollBody thead th {
            height: 0 !important;
            line-height: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-top: 0 !important;
            border-bottom: 0 !important;
            margin: 0 !important;
        }

        #myTable_wrapper table.dataTable thead > tr > th span.dt-column-order {
            display: none !important;
        }

        @media only screen and (max-width: 767.98px) {
            #myTable_wrapper .dt-layout-row:first-child {
                flex-direction: column;
                align-items: stretch;
            }

            #myTable_wrapper .dt-search,
            #myTable_wrapper .dt-length {
                justify-content: space-between;
                width: 100%;
            }

            #myTable_wrapper .dt-search input {
                min-width: 0;
                width: 100%;
            }
        }

        #myTable_wrapper .dt-paging .dt-paging-button {
            background: #fff !important;
            border: 1px solid #d9dce5 !important;
            color: #5f6b7a !important;
        }

        #myTable_wrapper .dt-paging .dt-paging-button:hover {
            background: #f3f6ff !important;
            color: var(--bs-primary) !important;
        }

        #myTable_wrapper .dt-paging .dt-paging-button.current {
            background: var(--bs-danger) !important;
            border-color: var(--bs-danger) !important;
            color: #fff !important;
        }

        .overtime-forbidden-modal {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .overtime-forbidden-modal .error-page {
            min-height: 250px !important;
            height: auto !important;
            margin-bottom: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: transparent !important;
        }

        .overtime-forbidden-modal .error-page::before {
            display: none !important;
        }

        .overtime-forbidden-modal .error-page .error-inner {
            position: relative !important;
            left: auto !important;
            top: auto !important;
            transform: none !important;
            max-width: 100% !important;
            width: 100% !important;
            padding: 0.25rem 0.5rem !important;
        }

        .overtime-forbidden-modal .error-page .dz-error {
            font-size: clamp(4.5rem, 15vw, 6.8rem) !important;
            line-height: 1 !important;
            margin: 0 auto 0.35rem !important;
            animation: none !important;
        }

        .overtime-forbidden-modal .error-page .dz-error::before,
        .overtime-forbidden-modal .error-page .dz-error::after {
            display: none !important;
        }

        .overtime-forbidden-modal .error-page .error-head {
            font-size: clamp(1.55rem, 4.8vw, 2rem) !important;
            margin-bottom: 0.3rem !important;
        }

        .overtime-forbidden-modal .error-page .fs-16 {
            font-size: 1rem !important;
            margin-bottom: 0 !important;
        }

    </style>

@endsection

@section('navbarTitle', 'Attendances-data')

@section('content')

@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Izin',
    'homeRoute' => 'dashboard',
])

@include('absensi.layouts_absensi.profileIndex')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Lembur</h5>
        <div class="d-flex align-items-center">
            @if($canSubmitOvertime ?? false)
            <button
                type="button"
                id="openSubmitLemburModalButton"
                class="me-2 btn btn-success light btn-sm"
            >Submit Lembur</button>
            @endif
        </div>
    </div>
</div>
<!-- End - Page Title & Breadcrumb -->
<div class="row">
    <div class="col-xl-12 col-xxl-12">
        <div class="card h-auto">
            <div class="row">
                <div class="col-xxl-12 col-xl-12">
                    <div class="card-body">
                        <div class="lembur-table-title">Data Lembur</div>
                        <div class="table-responsive">
                            <table id="myTable" class="display table">
                                <thead>
                                <tr>
                                    <th class="mw-80">No</th>
                                    @if($canManageOvertimeActions ?? false)
                                        <th class="mw-150">Staff</th>
                                        <th class="mw-150">PIC</th>
                                        <th class="mw-180">Tanggal</th>
                                        <th class="mw-180">Actual Start</th>
                                        <th class="mw-180">Actual End</th>
                                        <th class="mw-200">Calculated Hours</th>
                                        <th class="mw-200">Instruction</th>
                                        <th class="mw-130 text-center">Action</th>
                                    @else
                                        <th class="mw-150">PIC</th>
                                        <th class="mw-180">Tanggal</th>
                                        <th class="mw-180">Planned Start</th>
                                        <th class="mw-180">Planned End</th>
                                        <th class="mw-200">Instruction</th>
                                        <th class="mw-130 text-center">Action</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(($canSubmitOvertime ?? false) || ($canManageOvertimeActions ?? false))
    <div class="modal fade" id="submitLemburModal" tabindex="-1" aria-labelledby="submitLemburModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="submitLemburModalLabel">Submit Lembur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body {{ (($isStaffOvertimeUser ?? false) && !($hasStaffOvertimeAssignment ?? false)) ? 'overtime-forbidden-modal' : '' }}">
                    @if(($isStaffOvertimeUser ?? false) && !($hasStaffOvertimeAssignment ?? false))
                        <div class="clearfix">
                            <div class="container px-0">
                                <div class="row justify-content-center h-100 align-items-center">
                                    <div class="col-12 error-page">
                                        <div class="error-inner text-center">
                                            <div class="dz-error" data-text="403">403</div>
                                            <h2 class="error-head"><i class="fa fa-thumbs-down text-danger"></i> Forbidden Error!</h2>
                                            <p class="fs-16">You do not have permission to view this resource.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <form id="overtimeSubmissionForm" action="{{ route('absensi.lembur.store') }}" method="POST">
                            @csrf
                            <input type="hidden" id="overtimeIdInput" value="">
                            <div class="mb-3" id="overtimeStaffGroup">
                                <label class="form-label" for="overtimeStaffInput">Staff</label>
                                <select class="form-select" id="overtimeStaffInput" name="employee_id" @if($isStaffOvertimeUser ?? false) disabled @endif>
                                    @foreach(($staffOptions ?? collect()) as $staffOption)
                                        <option value="{{ $staffOption['id'] }}" @selected(($defaultStaffEmployeeId ?? '') === ($staffOption['id'] ?? ''))>
                                            {{ $staffOption['name'] ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($isStaffOvertimeUser ?? false)
                                    <input type="hidden" name="employee_id" id="overtimeStaffInputHidden" value="{{ $defaultStaffEmployeeId ?? '' }}">
                                @endif
                            </div>
                            <div class="mb-3" id="overtimePicGroup">
                                <label class="form-label" for="overtimePicInput">PIC</label>
                                <select class="form-select" id="overtimePicInput" name="pic_user_id" @if($isStaffOvertimeUser ?? false) disabled @endif>
                                    @foreach(($picOptions ?? collect()) as $picOption)
                                        <option value="{{ $picOption['id'] }}" @selected(($defaultPicUserId ?? '') === ($picOption['id'] ?? ''))>
                                            {{ $picOption['name'] ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($isStaffOvertimeUser ?? false)
                                    <input type="hidden" name="pic_user_id" id="overtimePicInputHidden" value="{{ $defaultPicUserId ?? '' }}">
                                @endif
                            </div>
                            <div class="mb-3 d-none" id="overtimePicReadonlyGroup">
                                <label class="form-label" for="overtimePicReadonlyInput">PIC</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="overtimePicReadonlyInput"
                                    value="-"
                                    readonly
                                >
                            </div>
                            <div class="mb-3" id="overtimeDateGroup">
                                <label class="form-label" for="overtimeDateInput">Tanggal Lembur</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="overtimeDateInput"
                                    name="overtime_date"
                                    placeholder="DD/MM/YYYY"
                                    autocomplete="off"
                                >
                            </div>
                            <div class="mb-3" id="overtimePlannedTimeGroup">
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small mb-1" for="overtimeStartTimeInput">Planned Start Time</label>
                                        <input
                                            type="time"
                                            step="1"
                                            class="form-control"
                                            id="overtimeStartTimeInput"
                                            name="planned_start_time"
                                            @if($isStaffOvertimeUser ?? false) readonly @endif
                                        >
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small mb-1" for="overtimeEndTimeInput">Planned End Time</label>
                                        <input
                                            type="time"
                                            step="1"
                                            class="form-control"
                                            id="overtimeEndTimeInput"
                                            name="planned_end_time"
                                            @if($isStaffOvertimeUser ?? false) readonly @endif
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 d-none" id="overtimeActualTimeGroup">
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small mb-1" for="overtimeActualStartTimeInput">Actual Start Time</label>
                                        <input
                                            type="time"
                                            step="1"
                                            class="form-control"
                                            id="overtimeActualStartTimeInput"
                                            name="actual_start_time"
                                        >
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small mb-1" for="overtimeActualEndTimeInput">Actual End Time</label>
                                        <input
                                            type="time"
                                            step="1"
                                            class="form-control"
                                            id="overtimeActualEndTimeInput"
                                            name="actual_end_time"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="mb-0" id="overtimeInstructionGroup">
                                <label class="form-label" for="overtimeDescriptionInput">Instruction</label>
                                <textarea
                                    class="form-control"
                                    id="overtimeDescriptionInput"
                                    name="instruction"
                                    rows="4"
                                    placeholder="Tulis deskripsi lembur"
                                    @if($isStaffOvertimeUser ?? false) readonly @endif
                                ></textarea>
                            </div>
                            <div class="mb-0 mt-3 d-none" id="overtimeApprovalStatusGroup">
                                <label class="form-label" for="overtimeApprovalStatusInput">Status</label>
                                <select class="form-select" id="overtimeApprovalStatusInput" name="status">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                </select>
                            </div>
                        </form>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    @if(!(($isStaffOvertimeUser ?? false) && !($hasStaffOvertimeAssignment ?? false)))
                        <button type="button" class="btn btn-primary btn-sm" id="submitOvertimeButton">Submit Lembur</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
<!-- End - Content Body -->

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var overtimeShowUrlTemplate = '{{ route('absensi.lembur.show', ['attendanceOvertime' => '__ID__']) }}';
        var overtimeUpdateUrlTemplate = '{{ route('absensi.lembur.update', ['attendanceOvertime' => '__ID__']) }}';
        var overtimeDestroyUrlTemplate = '{{ route('absensi.lembur.destroy', ['attendanceOvertime' => '__ID__']) }}';
        var canManageOvertimeActions = @json($canManageOvertimeActions ?? false);
        var isStaffOvertimeUser = @json($isStaffOvertimeUser ?? false);
        var assignedOvertimeEmployeeIds = @json(($assignedOvertimeEmployeeIds ?? collect())->values());
        var defaultStaffEmployeeId = @json($defaultStaffEmployeeId ?? '');
        var defaultPicUserId = @json($defaultPicUserId ?? '');
        var hasStaffOvertimeAssignment = @json($hasStaffOvertimeAssignment ?? false);
        var staffEditableOvertimeId = @json($staffEditableOvertimeId ?? null);
        var overtimeTableInstance = null;
        var overtimeModalInstance = null;
        var overtimeFormMode = 'create';
        var shouldReloadOvertimeTable = false;

        $(function () {
            var attendanceDateElement = document.getElementById('attendanceDateTime');
            var overtimeModalElement = document.getElementById('submitLemburModal');
            overtimeModalInstance = window.bootstrap && overtimeModalElement
                ? bootstrap.Modal.getOrCreateInstance(overtimeModalElement)
                : null;

            function renderAttendanceDateTime() {
                if (!attendanceDateElement) {
                    return;
                }

                var now = new Date();

                var dateParts = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    weekday: 'long',
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }).formatToParts(now);

                var timeParts = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hourCycle: 'h23'
                }).formatToParts(now);

                var dateMap = {};
                dateParts.forEach(function (part) {
                    dateMap[part.type] = part.value;
                });

                var timeMap = {};
                timeParts.forEach(function (part) {
                    timeMap[part.type] = part.value;
                });

                var hourNumber = parseInt(timeMap.hour, 10);
                var meridiem = hourNumber < 12 ? 'AM' : 'PM';
                var formattedDateTime = dateMap.weekday + ', ' + dateMap.day + ' ' + dateMap.month + ' ' + dateMap.year
                    + ' | ' + timeMap.hour + ':' + timeMap.minute + ':' + timeMap.second + ' ' + meridiem;

                attendanceDateElement.textContent = formattedDateTime;
            }

            renderAttendanceDateTime();
            setInterval(renderAttendanceDateTime, 1000);

            $('.absensi-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            function ensureEmptyPageOne(tableApi) {
                var tableWrapper = $(tableApi.table().container());
                var pageInfo = tableApi.page.info();

                tableWrapper.find('.absensi-empty-page-btn').remove();

                if (!pageInfo || pageInfo.recordsTotal !== 0) {
                    return;
                }

                var modernNextButton = tableWrapper.find('.dt-paging .dt-paging-button.next');
                if (modernNextButton.length > 0) {
                    $('<button type="button" class="dt-paging-button current absensi-empty-page-btn" disabled>1</button>')
                        .insertBefore(modernNextButton.first());
                    return;
                }

                var legacyNextButton = tableWrapper.find('.dataTables_paginate .paginate_button.next');
                if (legacyNextButton.length > 0) {
                    $('<span class="paginate_button current absensi-empty-page-btn">1</span>')
                        .insertBefore(legacyNextButton.first());
                }
            }

            var overtimeTableColumns = canManageOvertimeActions
                ? [
                    { data: null, defaultContent: '' },
                    { data: 'staff_name', defaultContent: '-' },
                    { data: 'pic_name', defaultContent: '-' },
                    { data: 'overtime_date', defaultContent: '-' },
                    { data: 'actual_start_time', defaultContent: '-' },
                    { data: 'actual_end_time', defaultContent: '-' },
                    { data: 'calculated_hours_display', defaultContent: '-' },
                    { data: 'instruction', defaultContent: '-' },
                    { data: null, defaultContent: '' }
                ]
                : [
                    { data: null, defaultContent: '' },
                    { data: 'pic_name', defaultContent: '-' },
                    { data: 'overtime_date', defaultContent: '-' },
                    { data: 'planned_start_time', defaultContent: '-' },
                    { data: 'planned_end_time', defaultContent: '-' },
                    { data: 'instruction', defaultContent: '-' },
                    { data: null, defaultContent: '' }
                ];
            var managerActualStartColumnIndex = canManageOvertimeActions ? 4 : -1;
            var managerActualEndColumnIndex = canManageOvertimeActions ? 5 : -1;
            var managerCalculatedColumnIndex = canManageOvertimeActions ? 6 : -1;
            var actionColumnIndex = overtimeTableColumns.length - 1;
            var overtimeColumnDefinitions = [
                {
                    targets: 0,
                    searchable: false,
                    orderable: false
                },
                {
                    targets: actionColumnIndex,
                    searchable: false,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        var overtimeId = row && row.id ? row.id : '';
                        var actionButtons = '<button type="button" class="lembur-action-btn info" onclick="infoDataOvertime(' + overtimeId + ')"><i class="bi bi-info-circle"></i></button>';

                        if (canManageOvertimeActions || isStaffOvertimeUser) {
                            actionButtons += '<button type="button" class="lembur-action-btn edit" onclick="editDataOvertime(' + overtimeId + ')"><i class="bi bi-pencil"></i></button>';
                        }

                        if (canManageOvertimeActions) {
                            actionButtons += '<button type="button" class="lembur-action-btn delete" onclick="deleteDataOvertime(' + overtimeId + ')"><i class="bi bi-trash"></i></button>';
                        }

                        return '<div class="lembur-action-group">'
                            + actionButtons
                            + '</div>';
                    }
                }
            ];

            if (canManageOvertimeActions) {
                overtimeColumnDefinitions.push(
                    {
                        targets: [managerActualStartColumnIndex, managerActualEndColumnIndex],
                        render: function (data, type, row) {
                            if (!row || row.has_actual_time !== true) {
                                return buildPendingBadgeHtml();
                            }

                            return data && data !== '-' ? data : buildPendingBadgeHtml();
                        }
                    },
                    {
                        targets: managerCalculatedColumnIndex,
                        render: function (data, type, row) {
                            if (!row || row.has_actual_time !== true) {
                                return buildPendingBadgeHtml();
                            }

                            return data && data !== '-' ? data : buildPendingBadgeHtml();
                        }
                    }
                );
            }

            overtimeTableInstance = $('#myTable').DataTable({
                ajax: {
                    url: '{{ route('absensi.lembur.datatable') }}',
                    dataSrc: 'data'
                },
                processing: true,
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                searching: false,
                lengthChange: false,
                columns: overtimeTableColumns,
                columnDefs: overtimeColumnDefinitions,
                initComplete: function () {
                    var tableApi = this.api();
                    var tableContainer = $(tableApi.table().container());
                    var scrollBody = tableContainer.find('.dt-scroll-body');

                    scrollBody.css({
                        overflowX: 'auto',
                        overflowY: 'hidden',
                        WebkitOverflowScrolling: 'touch'
                    });

                    scrollBody.scrollLeft(0);
                    tableApi.columns.adjust();
                },
                drawCallback: function () {
                    ensureEmptyPageOne(this.api());
                }
            });

            overtimeTableInstance.on('order.dt search.dt draw.dt', function () {
                var pageInfo = overtimeTableInstance.page.info();
                overtimeTableInstance.column(0, { page: 'current' }).nodes().each(function (cell, index) {
                    cell.innerHTML = pageInfo.start + index + 1;
                });
            });

            overtimeTableInstance.on('draw', function () {
                overtimeTableInstance.columns.adjust();
            });

            $(window).on('resize', function () {
                overtimeTableInstance.columns.adjust();
            });

            function initOvertimeDatePicker() {
                var $overtimeDateInput = $('#overtimeDateInput');

                if (!$.fn.daterangepicker || !$overtimeDateInput.length) {
                    return;
                }

                $overtimeDateInput.daterangepicker({
                    parentEl: '#submitLemburModal .modal-body',
                    singleDatePicker: true,
                    autoApply: true,
                    autoUpdateInput: false,
                    locale: {
                        format: 'DD/MM/YYYY',
                        cancelLabel: 'Clear'
                    }
                });

                $overtimeDateInput.on('apply.daterangepicker', function (event, picker) {
                    $(this).val(picker.startDate.format('DD/MM/YYYY'));
                });

                $overtimeDateInput.on('cancel.daterangepicker', function () {
                    $(this).val('');
                });
            }

            $('#submitLemburModal').on('hidden.bs.modal', function () {
                resetOvertimeFormFields();
                setOvertimeModalCreateMode();

                if (shouldReloadOvertimeTable) {
                    refreshOvertimeTable();
                    shouldReloadOvertimeTable = false;
                }
            });

            $('#overtimeStaffInput').on('change', function () {
                toggleActualOvertimeFields();
            });

            function handleOvertimeFormSubmit() {
                $('#submitOvertimeButton').on('click', function (event) {
                    event.preventDefault();

                    var $submitButton = $(this);
                    var $overtimeForm = $('#overtimeSubmissionForm');
                    var formData = new FormData($overtimeForm[0]);
                    var overtimeId = $('#overtimeIdInput').val();
                    var requestUrl = '{{ route('absensi.lembur.store') }}';

                    if (overtimeFormMode === 'edit' && overtimeId) {
                        requestUrl = buildOvertimeUrl(overtimeUpdateUrlTemplate, overtimeId);
                        formData.append('_method', 'PUT');
                    }

                    $.ajax({
                        url: requestUrl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': formData.get('_token'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        beforeSend: function () {
                            $submitButton.prop('disabled', true).text('Menyimpan...');
                        },
                        success: function (response) {
                            if (response && response.success === true) {
                                shouldReloadOvertimeTable = true;

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                if (overtimeModalInstance) {
                                    overtimeModalInstance.hide();
                                } else {
                                    resetOvertimeFormFields();
                                    setOvertimeModalCreateMode();
                                    refreshOvertimeTable();
                                    shouldReloadOvertimeTable = false;
                                }

                                return;
                            }

                            Swal.fire({
                                icon: 'warning',
                                title: 'Gagal',
                                text: response && response.message ? response.message : 'Gagal menyimpan data lembur.'
                            });
                        },
                        error: function (xhr) {
                            var responseJson = xhr.responseJSON || {};
                            var errorMessage = responseJson.message || 'Gagal memproses permintaan.';

                            if (responseJson.errors) {
                                var firstErrorKey = Object.keys(responseJson.errors)[0];
                                if (firstErrorKey && Array.isArray(responseJson.errors[firstErrorKey])) {
                                    errorMessage = responseJson.errors[firstErrorKey][0];
                                }
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: errorMessage
                            });
                        },
                        complete: function () {
                            if (isStaffOvertimeUser) {
                                $submitButton.prop('disabled', false).text('Simpan Jam Aktual');
                                return;
                            }

                            $submitButton.prop('disabled', false).text(overtimeFormMode === 'edit' ? 'Update Lembur' : 'Submit Lembur');
                        }
                    });
                });
            }

            initOvertimeDatePicker();
            setOvertimeModalCreateMode();
            handleOvertimeFormSubmit();
        });

        function buildOvertimeUrl(urlTemplate, overtimeId) {
            return String(urlTemplate).replace('__ID__', String(overtimeId || ''));
        }

        function escapeHtml(value) {
            return $('<div>').text(value || '-').html();
        }

        function normalizeOvertimeStatusLabel(statusValue) {
            var normalizedStatus = String(statusValue || 'pending').toLowerCase();

            if (normalizedStatus === 'approved') {
                return 'Approved';
            }

            if (normalizedStatus === 'completed') {
                return 'Completed';
            }

            if (normalizedStatus === 'rejected' || normalizedStatus === 'refused') {
                return 'Rejected';
            }

            return 'Pending';
        }

        function buildPendingBadgeHtml() {
            return '<span class="badge light border-warning text-warning">Pending</span>';
        }

        function refreshOvertimeTable() {
            if (overtimeTableInstance && overtimeTableInstance.ajax) {
                overtimeTableInstance.ajax.reload(null, false);
            }
        }

        function showOvertimeAjaxError(xhr) {
            var responseJson = xhr.responseJSON || {};
            var errorMessage = responseJson.message || 'Gagal memproses permintaan.';

            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: errorMessage
            });
        }

        function hasAssignedOvertimeForSelectedStaff(employeeId) {
            if (!employeeId) {
                return false;
            }

            return (assignedOvertimeEmployeeIds || []).map(function (item) {
                return String(item);
            }).indexOf(String(employeeId)) !== -1;
        }

        function toggleActualOvertimeFields() {
            if (isStaffOvertimeUser) {
                $('#overtimeStaffGroup, #overtimePicGroup, #overtimeDateGroup, #overtimePlannedTimeGroup, #overtimeApprovalStatusGroup').addClass('d-none');
                $('#overtimePicReadonlyGroup').removeClass('d-none');
                $('#overtimeActualTimeGroup').removeClass('d-none');
                $('#overtimeActualStartTimeInput, #overtimeActualEndTimeInput').prop('disabled', false);
                $('#overtimeApprovalStatusInput').prop('disabled', true);
                $('#overtimeDescriptionInput').prop('readonly', true);
                return;
            }

            $('#overtimeStaffGroup, #overtimePicGroup, #overtimeDateGroup, #overtimePlannedTimeGroup').removeClass('d-none');
            $('#overtimePicReadonlyGroup').addClass('d-none');
            $('#overtimeActualTimeGroup').addClass('d-none');
            $('#overtimeApprovalStatusGroup').addClass('d-none');
            $('#overtimeActualStartTimeInput, #overtimeActualEndTimeInput').prop('disabled', true);
            $('#overtimeApprovalStatusInput').prop('disabled', true);
            $('#overtimeDescriptionInput').prop('readonly', false);
        }

        function resetOvertimeFormFields() {
            var overtimeFormElement = $('#overtimeSubmissionForm')[0];
            if (!overtimeFormElement) {
                return;
            }

            overtimeFormElement.reset();
            $('#overtimeIdInput').val('');
            $('#overtimeStaffInput').val(defaultStaffEmployeeId);
            $('#overtimeStaffInputHidden').val(defaultStaffEmployeeId);
            $('#overtimePicInput').val(defaultPicUserId);
            $('#overtimePicInputHidden').val(defaultPicUserId);
            $('#overtimePicReadonlyInput').val('-');
            $('#overtimeDateInput').val('');
            $('#overtimeStartTimeInput').val('');
            $('#overtimeEndTimeInput').val('');
            $('#overtimeActualStartTimeInput').val('');
            $('#overtimeActualEndTimeInput').val('');
            $('#overtimeDescriptionInput').val('');
        }

        function setOvertimeModalCreateMode() {
            overtimeFormMode = 'create';
            if (isStaffOvertimeUser) {
                $('#submitLemburModalLabel').text('Isi Jam Lembur');
                $('#submitOvertimeButton').text('Simpan Jam Aktual');
            } else {
                $('#submitLemburModalLabel').text('Submit Lembur');
                $('#submitOvertimeButton').text('Submit Lembur');
            }
            $('#overtimeIdInput').val('');
            $('#overtimeApprovalStatusInput').val('pending');
            toggleActualOvertimeFields();
        }

        function setOvertimeModalEditMode(detailData) {
            overtimeFormMode = 'edit';
            if (isStaffOvertimeUser) {
                $('#submitLemburModalLabel').text('Isi Jam Lembur');
                $('#submitOvertimeButton').text('Simpan Jam Aktual');
            } else {
                $('#submitLemburModalLabel').text('Edit Lembur');
                $('#submitOvertimeButton').text('Update Lembur');
            }

            $('#overtimeIdInput').val(detailData.id || '');
            $('#overtimeStaffInput').val(detailData.employee_id || defaultStaffEmployeeId);
            $('#overtimeStaffInputHidden').val(detailData.employee_id || defaultStaffEmployeeId);
            $('#overtimePicInput').val(detailData.pic_user_id || defaultPicUserId);
            $('#overtimePicInputHidden').val(detailData.pic_user_id || defaultPicUserId);
            $('#overtimePicReadonlyInput').val(detailData.pic_name || '-');
            $('#overtimeDateInput').val(detailData.overtime_date_input || '');
            $('#overtimeStartTimeInput').val(detailData.planned_start_time || '');
            $('#overtimeEndTimeInput').val(detailData.planned_end_time || '');
            $('#overtimeActualStartTimeInput').val(detailData.actual_start_time || '');
            $('#overtimeActualEndTimeInput').val(detailData.actual_end_time || '');
            $('#overtimeDescriptionInput').val(detailData.instruction && detailData.instruction !== '-' ? detailData.instruction : '');
            $('#overtimeApprovalStatusInput').val(String(detailData.status || 'pending').toLowerCase());
            toggleActualOvertimeFields();

            var datePickerInstance = $('#overtimeDateInput').data('daterangepicker');
            if (datePickerInstance && detailData.overtime_date_input && window.moment) {
                var selectedDate = moment(detailData.overtime_date_input, 'DD/MM/YYYY');
                if (selectedDate.isValid()) {
                    datePickerInstance.setStartDate(selectedDate);
                    datePickerInstance.setEndDate(selectedDate);
                }
            }
        }

        function infoDataOvertime(overtimeId) {
            if (!overtimeId) {
                return;
            }

            $.ajax({
                url: buildOvertimeUrl(overtimeShowUrlTemplate, overtimeId),
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (response) {
                    if (!response || response.success !== true || !response.data) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response && response.message ? response.message : 'Data lembur tidak ditemukan.'
                        });
                        return;
                    }

                    var detailData = response.data;
                    var detailHtml = '<div class="text-start px-2">'
                        + '<div class="table-responsive">'
                        + '<table class="table table-sm align-middle mb-0">'
                        + '<tbody>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="width: 38%; color: #334155;">Nama Staff</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.staff_name) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">PIC</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.pic_name || '-') + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Tanggal</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.overtime_date) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Planned Time</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.planned_start_time) + ' - ' + escapeHtml(detailData.planned_end_time) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Actual Time</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.actual_start_time || '-') + ' - ' + escapeHtml(detailData.actual_end_time || '-') + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Durasi</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.duration) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Status</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(normalizeOvertimeStatusLabel(detailData.status)) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Instruction</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.instruction) + '</td></tr>'
                        + '</tbody>'
                        + '</table>'
                        + '</div>'
                        + '</div>';

                    Swal.fire({
                        title: 'Detail Lembur',
                        html: detailHtml,
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#475569',
                        customClass: {
                            popup: 'p-3',
                            title: 'mb-2'
                        }
                    });
                },
                error: function (xhr) {
                    showOvertimeAjaxError(xhr);
                }
            });
        }

        function editDataOvertime(overtimeId) {
            if (!canManageOvertimeActions && !isStaffOvertimeUser) {
                return;
            }

            if (!overtimeId) {
                return;
            }

            $.ajax({
                url: buildOvertimeUrl(overtimeShowUrlTemplate, overtimeId),
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (response) {
                    if (!response || response.success !== true || !response.data) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response && response.message ? response.message : 'Data lembur tidak ditemukan.'
                        });
                        return;
                    }

                    setOvertimeModalEditMode(response.data);

                    if (overtimeModalInstance) {
                        overtimeModalInstance.show();
                    }
                },
                error: function (xhr) {
                    showOvertimeAjaxError(xhr);
                }
            });
        }

        function openOvertimeModalForStaff() {
            if (!overtimeModalInstance) {
                return;
            }

            if (!hasStaffOvertimeAssignment) {
                resetOvertimeFormFields();
                setOvertimeModalCreateMode();
                overtimeModalInstance.show();
                return;
            }

            if (!staffEditableOvertimeId) {
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Tidak ada assignment lembur yang perlu diisi saat ini.'
                });
                return;
            }

            $.ajax({
                url: buildOvertimeUrl(overtimeShowUrlTemplate, staffEditableOvertimeId),
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (response) {
                    if (!response || response.success !== true || !response.data) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response && response.message ? response.message : 'Data lembur tidak ditemukan.'
                        });
                        return;
                    }

                    setOvertimeModalEditMode(response.data);
                    overtimeModalInstance.show();
                },
                error: function (xhr) {
                    showOvertimeAjaxError(xhr);
                }
            });
        }

        $('#openSubmitLemburModalButton').on('click', function () {
            if (isStaffOvertimeUser) {
                openOvertimeModalForStaff();
                return;
            }

            resetOvertimeFormFields();
            setOvertimeModalCreateMode();
            if (overtimeModalInstance) {
                overtimeModalInstance.show();
            }
        });

        function deleteDataOvertime(overtimeId) {
            if (!canManageOvertimeActions) {
                return;
            }

            if (!overtimeId) {
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Hapus Data Lembur?',
                text: 'Data yang dihapus tidak dapat dikembalikan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: buildOvertimeUrl(overtimeDestroyUrlTemplate, overtimeId),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Menghapus...',
                            allowOutsideClick: false,
                            didOpen: function () {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: response && response.success === true ? 'success' : 'warning',
                            title: response && response.success === true ? 'Berhasil' : 'Gagal',
                            text: response && response.message ? response.message : 'Proses hapus selesai.'
                        }).then(function () {
                            refreshOvertimeTable();
                        });
                    },
                    error: function (xhr) {
                        showOvertimeAjaxError(xhr);
                    }
                });
            });
        }
    </script>
@endsection
