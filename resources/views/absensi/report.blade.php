@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
        $absensiCssPath = public_path('assets/css/absensi.css');
        $absensiCssVersion = file_exists($absensiCssPath) ? filemtime($absensiCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/css/absensi.css') }}?v={{ $absensiCssVersion }}">
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

        .attendance-action-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .attendance-action-btn {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .attendance-action-btn:hover {
            background: #dbeafe;
            color: #1e40af;
            border-color: #93c5fd;
        }

        .attendance-datetime {
            font-size: 1rem;
            font-weight: 600;
            color: #25314c;
            margin-bottom: 0;
            text-align: center;
        }

        .attendance-tag {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.25rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .attendance-tag--holiday {
            background: #f3e8ff;
            color: #7e22ce;
        }

        .attendance-tag--joint {
            background: #fee2e2;
            color: #b91c1c;
        }

        .attendance-tag--weekend {
            background: #fee2e2;
            color: #7f1d1d;
        }

        .attendance-tag--empty-note {
            background: #f1f5f9;
            color: #475569;
        }

        #tableLogs.dataTable tbody td.dataTables_empty {
            text-align: center !important;
        }

        .attendance-table-scroll-area {
            overflow: hidden;
        }

        .attendance-table-scroll-area #tableLogs {
            width: 100% !important;
        }

        #tableLogs_wrapper .dt-scroll,
        #tableLogs_wrapper .dataTables_scroll {
            overflow: hidden;
        }

        #tableLogs_wrapper .dt-scroll-head table,
        #tableLogs_wrapper .dt-scroll-body table,
        #tableLogs_wrapper .dataTables_scrollHead table,
        #tableLogs_wrapper .dataTables_scrollBody table {
            min-width: 820px;
        }

        #tableLogs_wrapper .dt-scroll-body,
        #tableLogs_wrapper .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
        }

        #tableLogs_wrapper .dt-scroll-body thead,
        #tableLogs_wrapper .dataTables_scrollBody thead {
            visibility: hidden !important;
        }

        #tableLogs_wrapper .dt-scroll-body thead tr,
        #tableLogs_wrapper .dt-scroll-body thead th,
        #tableLogs_wrapper .dataTables_scrollBody thead tr,
        #tableLogs_wrapper .dataTables_scrollBody thead th {
            height: 0 !important;
            line-height: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-top: 0 !important;
            border-bottom: 0 !important;
            margin: 0 !important;
        }

        #tableLogs_wrapper table.dataTable thead > tr > th span.dt-column-order {
            display: none !important;
        }

        .logs-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .logs-toolbar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: auto;
        }

        .logs-toolbar > .clearfix {
            flex: 0 0 auto;
        }

        @media only screen and (max-width: 767.98px) {
            .logs-card-header {
                flex-direction: column;
                align-items: center;
            }

            .logs-card-header .card-title {
                width: 100%;
                margin-bottom: 0.5rem;
                text-align: center;
            }

            .logs-toolbar {
                width: 100%;
                margin-left: 0;
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                align-items: center;
            }

            .logs-toolbar .bootstrap-select,
            .logs-toolbar .selectpicker,
            .logs-toolbar .bootstrap-select > .dropdown-toggle {
                width: auto !important;
                min-width: 110px;
            }

            .logs-toolbar .btn {
                white-space: nowrap;
            }
        }
    </style>

@endsection

@section('navbarTitle', 'Attendances')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Report',
    'homeRoute' => 'dashboard',
])

@include('absensi.layouts_absensi.profileIndex')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Report</h5>
    </div>
</div>

@include('absensi.components.attendance-cards')


<div class="tab-content" id="tabContentMyProfileBottom">
    <div class="row">

        <!-- Start - logs -->
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 align-items-center logs-card-header">
                    <h4 class="card-title">Attandance Report</h4>
                    <div class="d-flex align-items-center gap-2 logs-toolbar">
                        @if ($showCompanyFilter ?? false)
                            <div class="clearfix">
                                <select id="attendanceCompanyFilter" class="selectpicker" data-live-search="true" title="Pilih PT">
                                    <option value="">Semua PT</option>
                                    @foreach (($companies ?? collect()) as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if ($showStaffPeriodFilter ?? false)
                            <div class="clearfix">
                                <select id="attendanceMonthFilter" class="selectpicker" title="Pilih Bulan">
                                    @foreach (($staffMonthOptions ?? collect()) as $monthValue)
                                        <option value="{{ $monthValue }}" @selected((int) ($defaultStaffMonth ?? 0) === (int) $monthValue)>
                                            {{ \Illuminate\Support\Carbon::create(null, (int) $monthValue, 1)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix">
                                <select id="attendanceYearFilter" class="selectpicker" title="Pilih Tahun">
                                    @foreach (($staffYearOptions ?? collect()) as $yearValue)
                                        <option value="{{ $yearValue }}" @selected((int) ($defaultStaffYear ?? 0) === (int) $yearValue)>
                                            {{ $yearValue }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="clearfix">
                            <button type="button" id="attendanceExportButton" class="btn btn-sm btn-primary">
                                Export report
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-card-body px-0 pt-0 pb-2">
                    <div class="table-responsive attendance-table-scroll-area">
                        <table id="tableLogs" class="table table-sm table-sm-responsive text-nowrap">
                            <thead>
                                <tr>
                                    <th class="mw-120">Date</th>
                                    <th class="mw-100">Clock In</th>
                                    <th class="mw-150">Clock Out</th>
                                    <th class="mw-100">Variance</th>
                                    <th class="mw-100">Work Hours</th>
                                    <th class="mw-150">Notes</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- End - logs -->
    </div>
</div>
<!-- Login Sessions table temporarily removed -->
<!-- End - Content Body -->

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
	
    <script>
        $(function () {
            var attendanceDateElement = document.getElementById('attendanceDateTime');
            var attendanceCompanyFilter = document.getElementById('attendanceCompanyFilter');
            var attendanceMonthFilter = document.getElementById('attendanceMonthFilter');
            var attendanceYearFilter = document.getElementById('attendanceYearFilter');
            var attendanceExportButton = document.getElementById('attendanceExportButton');
            var officeLocation = @json($officeLocation);
            var attendanceDatatableUrl = @json(route('absensi.reports.datatable'));
            var attendanceExportUrl = @json(route('absensi.reports.export'));
            var officeStartTotalMinutes = parseTimeStringToMinutes(officeLocation && officeLocation.office_start_time, 8 * 60);

            var attendanceTable = null;

            function ensureEmptyPageOne(datatableApi) {
                if (!datatableApi) {
                    return;
                }

                var pageInfo = datatableApi.page.info();
                var tableWrapper = $(datatableApi.table().container());

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

            var tableLogs = function(){
                if ($('#tableLogs').length === 0) {
                    return;
                }

                var attendanceColumns = [
                    { data: 'attendance_date', defaultContent: '-' },
                    { data: 'check_in', defaultContent: '' },
                    { data: 'check_out', defaultContent: '' },
                    { data: 'variance', defaultContent: '-' },
                    { data: 'work_hours', defaultContent: '-' },
                    { data: 'notes', defaultContent: '-' }
                ];

                var checkInColumnIndex = 1;
                var checkOutColumnIndex = 2;
                var attendanceColumnDefs = [
                    {
                        targets: 0,
                        render: function (data, type, rowData) {
                            if (type === 'sort' || type === 'type') {
                                return rowData && rowData.attendance_date_iso ? rowData.attendance_date_iso : (data || '');
                            }

                            return data || '-';
                        }
                    },
                    {
                        targets: checkInColumnIndex,
                        render: function (data, type, rowData) {
                            var rowType = rowData && rowData.row_type ? String(rowData.row_type) : '';
                            if (rowType === 'national_holiday') {
                                return '<span class="attendance-tag attendance-tag--holiday">' + (data || 'Libur Nasional') + '</span>';
                            }

                            if (rowType === 'joint_leave') {
                                return '<span class="attendance-tag attendance-tag--joint">' + (data || 'Cuti Bersama') + '</span>';
                            }

                            if (rowType === 'weekend') {
                                return '<span class="attendance-tag attendance-tag--weekend">' + (data || 'Weekend / Day Off') + '</span>';
                            }

                            if (data) {
                                var timeParts = String(data).split(':');
                                var checkInHour = parseInt(timeParts[0], 10);
                                var checkInMinute = parseInt(timeParts[1], 10);
                                var checkInTotalMinutes = (checkInHour * 60) + checkInMinute;

                                if (!Number.isNaN(checkInTotalMinutes) && checkInTotalMinutes > officeStartTotalMinutes) {
                                    return '<span class="text-danger fw-semibold">' + data + '</span>';
                                }

                                return '<span class="text-success fw-semibold">' + data + '</span>';
                            }

                            return '<span class="badge-attendance-empty">Belum Absen Masuk</span>';
                        }
                    },
                    {
                        targets: checkOutColumnIndex,
                        render: function (data, type, rowData) {
                            var rowType = rowData && rowData.row_type ? String(rowData.row_type) : '';
                            if (rowType === 'national_holiday' || rowType === 'joint_leave' || rowType === 'weekend') {
                                return '-';
                            }

                            if (data) {
                                return data;
                            }

                            return '<span class="badge-attendance-empty">Belum Absen Pulang</span>';
                        }
                    },
                    {
                        targets: 3,
                        render: function (data) {
                            return data || '-';
                        }
                    },
                    {
                        targets: 4,
                        render: function (data) {
                            return data || '-';
                        }
                    },
                    {
                        targets: 5,
                        render: function (data) {
                            if (data && String(data).trim() !== '') {
                                return data;
                            }

                            return '<span class="attendance-tag attendance-tag--empty-note">No Notes</span>';
                        }
                    }
                ];

                attendanceTable = $('#tableLogs').DataTable({
                    ajax: {
                        url: attendanceDatatableUrl,
                        data: function (requestData) {
                            requestData.company_id = attendanceCompanyFilter ? attendanceCompanyFilter.value : '';
                            requestData.month = attendanceMonthFilter ? attendanceMonthFilter.value : 0;
                            requestData.year = attendanceYearFilter ? attendanceYearFilter.value : 0;
                        },
                        dataSrc: 'data'
                    },
                    autoWidth: false,
                    scrollX: true,
                    searching: false,
                    pageLength: 10,
                    select: false,            
                    lengthChange: false,
                    paging: true,
                    bInfo: true,
                    columns: attendanceColumns,
                    columnDefs: attendanceColumnDefs,
                    language: {
                        emptyTable: 'No data available in table',
                        paginate: {
                            next: '<i class="fa-solid fa-angle-right"></i>',
                            previous: '<i class="fa-solid fa-angle-left"></i>' 
                        }
                    },
                    drawCallback: function () {
                        ensureEmptyPageOne(this.api());
                    }
                });

                attendanceTable.on('order.dt search.dt draw.dt', function () {
                    // keep draw event to retain empty-page handler behavior
                });

                ensureEmptyPageOne(attendanceTable);
            };

            function parseTimeStringToMinutes(timeString, fallbackMinutes) {
                if (typeof timeString !== 'string' || timeString.trim() === '') {
                    return fallbackMinutes;
                }

                var normalizedTime = timeString.trim();
                var timeParts = normalizedTime.split(':');
                if (timeParts.length < 2) {
                    return fallbackMinutes;
                }

                var hourValue = parseInt(timeParts[0], 10);
                var minuteValue = parseInt(timeParts[1], 10);
                if (Number.isNaN(hourValue) || Number.isNaN(minuteValue)) {
                    return fallbackMinutes;
                }

                return (hourValue * 60) + minuteValue;
            }

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

            if (attendanceCompanyFilter) {
                attendanceCompanyFilter.addEventListener('change', function () {
                    if (attendanceTable) {
                        attendanceTable.ajax.reload();
                    }
                });
            }

            if (attendanceMonthFilter) {
                attendanceMonthFilter.addEventListener('change', function () {
                    if (attendanceTable) {
                        attendanceTable.ajax.reload();
                    }
                });
            }

            if (attendanceYearFilter) {
                attendanceYearFilter.addEventListener('change', function () {
                    var currentUrl = new URL(window.location.href);
                    var selectedYear = attendanceYearFilter.value || '';
                    var selectedMonth = attendanceMonthFilter ? (attendanceMonthFilter.value || '') : '';

                    if (selectedYear !== '') {
                        currentUrl.searchParams.set('year', selectedYear);
                    }

                    if (selectedMonth !== '') {
                        currentUrl.searchParams.set('month', selectedMonth);
                    }

                    window.location.href = currentUrl.toString();
                });
            }

            if (attendanceExportButton) {
                attendanceExportButton.addEventListener('click', function () {
                    var exportUrl = new URL(attendanceExportUrl, window.location.origin);
                    var selectedMonth = attendanceMonthFilter ? (attendanceMonthFilter.value || '') : '';
                    var selectedYear = attendanceYearFilter ? (attendanceYearFilter.value || '') : '';
                    var selectedCompanyId = attendanceCompanyFilter ? (attendanceCompanyFilter.value || '') : '';

                    if (selectedMonth !== '') {
                        exportUrl.searchParams.set('month', selectedMonth);
                    }

                    if (selectedYear !== '') {
                        exportUrl.searchParams.set('year', selectedYear);
                    }

                    if (selectedCompanyId !== '') {
                        exportUrl.searchParams.set('company_id', selectedCompanyId);
                    }

                    window.location.href = exportUrl.toString();
                });
            }

            tableLogs();

        });
    </script>
@endsection
