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
            padding: 0.9rem 0.75rem;
        }

        #myTable thead th:first-child,
        #myTable tbody td:first-child {
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
    </style>

@endsection

@section('navbarTitle', 'Attendances-data')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
<div class="page-title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><h1>Attendances-data</h1></li>
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">
                    <svg width="18" height="18" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="var(--bs-body-color)" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Home
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Attendances-data</li>
        </ol>
    </nav>
</div>
<!-- End - Page Title & Breadcrumb -->
<div class="row">
    <div class="col-xl-12 col-xxl-12">
        <div class="card h-auto">
            <div class="card-header">
                <ul class="nav nav-underline card-header-tabs absensi-tabs" id="nav-tab" role="tablist">
                    <li class="nav-item">
                        <button type="button" data-href="{{ route('absensi') }}" class="nav-link absensi-tab-btn {{ request()->routeIs('absensi') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('absensi') ? 'true' : 'false' }}">Absensi Hari Ini</button>
                    </li>
                    <li class="nav-item">
                    </li>
                    <li class="nav-item">
                        <button type="button" data-href="{{ route('absensi.reports') }}" class="nav-link absensi-tab-btn {{ request()->routeIs('absensi.reports') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('absensi.reports') ? 'true' : 'false' }}">Reports</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" data-href="{{ route('absensi.izin') }}" class="nav-link absensi-tab-btn {{ request()->routeIs('absensi.izin') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('absensi.izin') ? 'true' : 'false' }}">Izin</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" data-href="{{ route('absensi.lembur') }}" class="nav-link absensi-tab-btn {{ request()->routeIs('absensi.lembur') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('absensi.lembur') ? 'true' : 'false' }}">Lembur</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" data-href="{{ route('absensi.cuti') }}" class="nav-link absensi-tab-btn {{ request()->routeIs('absensi.cuti') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('absensi.cuti') ? 'true' : 'false' }}">Libur Nasional dan Cuti Bersama</button>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-xxl-12 col-xl-12">
                    <div class="card-body">
                        <div class="row g-2 align-items-center mb-3">
                            <div class="col-12 col-md-3"></div>
                            <div class="col-12 col-md-6">
                                <div class="attendance-datetime mb-0" id="attendanceDateTime"></div>
                            </div>
                            <div class="col-12 col-md-3 text-md-end">
                                @if($canSubmitOvertime ?? false)
                                    <button
                                        type="button"
                                        id="openSubmitLemburModalButton"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#submitLemburModal"
                                    >
                                        Submit Lembur
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="lembur-table-title">Data Lembur</div>
                        <div class="table-responsive">
                            <table id="myTable" class="display table">
                                <thead>
                                <tr>
                                    <th class="mw-80">No</th>
                                    <th class="mw-180">Tanggal</th>
                                    <th class="mw-220">Nama</th>
                                    <th class="mw-150">Jam</th>
                                    <th class="mw-160">Durasi</th>
                                    <th class="mw-140">Status</th>
                                    <th class="mw-300">Deskripsi</th>
                                    <th class="mw-130 text-center">Action</th>
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
                <div class="modal-body">
                    <form id="overtimeSubmissionForm" action="{{ route('absensi.lembur.store') }}" method="POST">
                        @csrf
                        <input type="hidden" id="overtimeIdInput" value="">
                        <div class="mb-3">
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
                        <div class="mb-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small mb-1" for="overtimeStartTimeInput">Jam Mulai</label>
                                    <input
                                        type="time"
                                        step="1"
                                        class="form-control"
                                        id="overtimeStartTimeInput"
                                        name="start_time"
                                    >
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small mb-1" for="overtimeEndTimeInput">Jam Selesai</label>
                                    <input
                                        type="time"
                                        step="1"
                                        class="form-control"
                                        id="overtimeEndTimeInput"
                                        name="end_time"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="overtimeDescriptionInput">Deskripsi</label>
                            <textarea
                                class="form-control"
                                id="overtimeDescriptionInput"
                                name="description"
                                rows="4"
                                placeholder="Tulis deskripsi lembur"
                            ></textarea>
                        </div>
                        <div class="mb-0 mt-3 d-none" id="overtimeApprovalStatusGroup">
                            <label class="form-label" for="overtimeApprovalStatusInput">Approval Status</label>
                            <select class="form-select" id="overtimeApprovalStatusInput" name="approval_status">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" id="submitOvertimeButton">Submit Lembur</button>
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

            overtimeTableInstance = $('#myTable').DataTable({
                ajax: {
                    url: '{{ route('absensi.lembur.datatable') }}',
                    dataSrc: 'data'
                },
                processing: true,
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                columns: [
                    { data: null, defaultContent: '' },
                    { data: 'overtime_date', defaultContent: '-' },
                    { data: 'staff_name', defaultContent: '-' },
                    { data: 'time_range', defaultContent: '-' },
                    { data: 'duration', defaultContent: '-' },
                    { data: 'status', defaultContent: 'pending' },
                    { data: 'description', defaultContent: '-' },
                    { data: null, defaultContent: '' }
                ],
                columnDefs: [
                    {
                        targets: 0,
                        searchable: false,
                        orderable: false
                    },
                    {
                        targets: 5,
                        render: function (data) {
                            var normalizedStatus = String(data || 'pending').toLowerCase();
                            var statusLabel = 'Pending';
                            var statusClass = 'warning';

                            if (normalizedStatus === 'approved') {
                                statusLabel = 'Approved';
                                statusClass = 'success';
                            } else if (normalizedStatus === 'rejected') {
                                statusLabel = 'Rejected';
                                statusClass = 'danger';
                            }

                            return '<span class="lembur-status-badge ' + statusClass + '">' + statusLabel + '</span>';
                        }
                    },
                    {
                        targets: 7,
                        searchable: false,
                        orderable: false,
                        className: 'text-center',
                        render: function (data, type, row) {
                            var overtimeId = row && row.id ? row.id : '';
                            var actionButtons = '<button type="button" class="lembur-action-btn info" onclick="infoDataOvertime(' + overtimeId + ')"><i class="bi bi-info-circle"></i></button>';

                            if (canManageOvertimeActions) {
                                actionButtons += '<button type="button" class="lembur-action-btn edit" onclick="editDataOvertime(' + overtimeId + ')"><i class="bi bi-pencil"></i></button>'
                                    + '<button type="button" class="lembur-action-btn delete" onclick="deleteDataOvertime(' + overtimeId + ')"><i class="bi bi-trash"></i></button>';
                            }

                            return '<div class="lembur-action-group">'
                                + actionButtons
                                + '</div>';
                        }
                    }
                ],
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
                            $submitButton.prop('disabled', false).text(overtimeFormMode === 'edit' ? 'Update Lembur' : 'Submit Lembur');
                        }
                    });
                });
            }

            $('#openSubmitLemburModalButton').on('click', function () {
                resetOvertimeFormFields();
                setOvertimeModalCreateMode();
            });

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

            if (normalizedStatus === 'rejected' || normalizedStatus === 'refused') {
                return 'Rejected';
            }

            return 'Pending';
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

        function resetOvertimeFormFields() {
            $('#overtimeSubmissionForm')[0].reset();
            $('#overtimeIdInput').val('');
            $('#overtimeDateInput').val('');
            $('#overtimeStartTimeInput').val('');
            $('#overtimeEndTimeInput').val('');
            $('#overtimeDescriptionInput').val('');
        }

        function setOvertimeModalCreateMode() {
            overtimeFormMode = 'create';
            $('#submitLemburModalLabel').text('Submit Lembur');
            $('#submitOvertimeButton').text('Submit Lembur');
            $('#overtimeIdInput').val('');
            $('#overtimeApprovalStatusInput').val('pending');
            $('#overtimeApprovalStatusGroup').addClass('d-none');
        }

        function setOvertimeModalEditMode(detailData) {
            overtimeFormMode = 'edit';
            $('#submitLemburModalLabel').text('Edit Lembur');
            $('#submitOvertimeButton').text('Update Lembur');

            $('#overtimeIdInput').val(detailData.id || '');
            $('#overtimeDateInput').val(detailData.overtime_date_input || '');
            $('#overtimeStartTimeInput').val(detailData.start_time || '');
            $('#overtimeEndTimeInput').val(detailData.end_time || '');
            $('#overtimeDescriptionInput').val(detailData.description && detailData.description !== '-' ? detailData.description : '');
            $('#overtimeApprovalStatusInput').val(String(detailData.status || 'pending').toLowerCase());
            $('#overtimeApprovalStatusGroup').removeClass('d-none');

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
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Tanggal</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.overtime_date) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Jam</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.start_time) + ' - ' + escapeHtml(detailData.end_time) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Durasi</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.duration) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Status</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(normalizeOvertimeStatusLabel(detailData.status)) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Deskripsi</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.description) + '</td></tr>'
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
            if (!canManageOvertimeActions) {
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
