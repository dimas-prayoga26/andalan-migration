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

        #myTable thead th,
        #myTableBottom thead th {
            font-size: 1.1rem;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }

        #myTable tbody td,
        #myTableBottom tbody td {
            font-size: 1rem;
            padding: 0.9rem 0.75rem;
        }

        #myTable thead th:first-child,
        #myTable tbody td:first-child,
        #myTableBottom thead th:first-child,
        #myTableBottom tbody td:first-child {
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

        #myTable_wrapper .dt-length label,
        #myTable_wrapper .dt-search label,
        #myTable_wrapper .dt-info,
        #myTable_wrapper .dt-paging,
        #myTableBottom_wrapper .dt-length label,
        #myTableBottom_wrapper .dt-search label,
        #myTableBottom_wrapper .dt-info,
        #myTableBottom_wrapper .dt-paging {
            font-size: 1rem;
        }

        #myTable_wrapper .dt-layout-row:first-child,
        #myTableBottom_wrapper .dt-layout-row:first-child {
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
        #myTable_wrapper .dt-search,
        #myTableBottom_wrapper .dt-length,
        #myTableBottom_wrapper .dt-search {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        #myTable_wrapper .dt-search label,
        #myTable_wrapper .dt-length label,
        #myTableBottom_wrapper .dt-search label,
        #myTableBottom_wrapper .dt-length label {
            margin: 0;
            color: #5f6b7a;
            font-weight: 500;
        }

        #myTable_wrapper .dt-search input,
        #myTable_wrapper .dt-length select,
        #myTableBottom_wrapper .dt-search input,
        #myTableBottom_wrapper .dt-length select {
            min-height: 40px;
            font-size: 1rem;
            border-radius: 0.6rem;
            border: 1px solid #d9dce5;
            padding: 0.35rem 0.75rem;
            background: #fff;
            color: #27334a;
        }

        #myTable_wrapper .dt-search input,
        #myTableBottom_wrapper .dt-search input {
            min-width: 220px;
        }

        #myTable_wrapper .dt-search input:focus,
        #myTable_wrapper .dt-length select:focus,
        #myTableBottom_wrapper .dt-search input:focus,
        #myTableBottom_wrapper .dt-length select:focus {
            border-color: #cfd5df;
            box-shadow: 0 0 0 0.15rem rgba(15, 23, 42, 0.08);
            outline: 0;
        }

        @media only screen and (max-width: 767.98px) {
            #myTable_wrapper .dt-layout-row:first-child,
            #myTableBottom_wrapper .dt-layout-row:first-child {
                flex-direction: column;
                align-items: stretch;
            }

            #myTable_wrapper .dt-search,
            #myTable_wrapper .dt-length,
            #myTableBottom_wrapper .dt-search,
            #myTableBottom_wrapper .dt-length {
                justify-content: space-between;
                width: 100%;
            }

            #myTable_wrapper .dt-search input,
            #myTableBottom_wrapper .dt-search input {
                min-width: 0;
                width: 100%;
            }
        }

        #myTable_wrapper .dt-paging .dt-paging-button,
        #myTableBottom_wrapper .dt-paging .dt-paging-button {
            background: #fff !important;
            border: 1px solid #d9dce5 !important;
            color: #5f6b7a !important;
        }

        #myTable_wrapper .dt-paging .dt-paging-button:hover,
        #myTableBottom_wrapper .dt-paging .dt-paging-button:hover {
            background: #f3f6ff !important;
            color: var(--bs-primary) !important;
        }

        #myTable_wrapper .dt-paging .dt-paging-button.current,
        #myTableBottom_wrapper .dt-paging .dt-paging-button.current {
            background: var(--bs-danger) !important;
            border-color: var(--bs-danger) !important;
            color: #fff !important;
        }

        .cuti-table-section + .cuti-table-section {
            margin-top: 1.5rem;
        }

        .cuti-table-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #25314c;
            margin-bottom: 0.6rem;
        }

        .cuti-action-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }

        .cuti-action-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .cuti-action-btn.info {
            background: #d9f2f4;
            color: #4aa3ad;
        }

        .cuti-action-btn.edit {
            background: #f8e8b7;
            color: #f2ad00;
        }

        .cuti-action-btn.delete {
            background: #f8d6e2;
            color: #ff4f7b;
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
                        <button type="button" data-href="{{ route('absensi.dinas') }}" class="nav-link absensi-tab-btn {{ request()->routeIs('absensi.dinas') ? 'active' : '' }}" aria-selected="{{ request()->routeIs('absensi.dinas') ? 'true' : 'false' }}">Absensi Dinas</button>
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
                        <div class="attendance-datetime" id="attendanceDateTime"></div>
                        <div class="cuti-table-section">
                            <div class="cuti-table-title">Data Libur Nasional</div>
                        <div class="table-responsive">
                            <table id="myTable" class="display table">
                                <thead>
                                <tr>
                                        <th class="mw-100">No</th>
                                        <th class="mw-220">Tanggal</th>
                                        <th class="mw-300">Libur Nasional</th>
                                        <th class="mw-130 text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                        <td>1</td>
                                        <td>01 May 2026</td>
                                        <td>Hari Buruh Internasional</td>
                                        <td>
                                            <div class="cuti-action-group">
                                                <button type="button" class="cuti-action-btn info"><i class="bi bi-info-circle"></i></button>
                                                <button type="button" class="cuti-action-btn edit"><i class="bi bi-pencil"></i></button>
                                                <button type="button" class="cuti-action-btn delete"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                </tr>
                                <tr>
                                        <td>2</td>
                                        <td>14 May 2026</td>
                                        <td>Kenaikan Isa Almasih</td>
                                        <td>
                                            <div class="cuti-action-group">
                                                <button type="button" class="cuti-action-btn info"><i class="bi bi-info-circle"></i></button>
                                                <button type="button" class="cuti-action-btn edit"><i class="bi bi-pencil"></i></button>
                                                <button type="button" class="cuti-action-btn delete"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>27 May 2026</td>
                                        <td>Idul Adha</td>
                                        <td>
                                            <div class="cuti-action-group">
                                                <button type="button" class="cuti-action-btn info"><i class="bi bi-info-circle"></i></button>
                                                <button type="button" class="cuti-action-btn edit"><i class="bi bi-pencil"></i></button>
                                                <button type="button" class="cuti-action-btn delete"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>

                        <div class="cuti-table-section">
                            <div class="cuti-table-title">Data Cuti Bersama</div>
                            <div class="table-responsive">
                                <table id="myTableBottom" class="display table">
                                    <thead>
                                    <tr>
                                        <th class="mw-100">No</th>
                                        <th class="mw-220">Tanggal</th>
                                        <th class="mw-300">Cuti Bersama</th>
                                        <th class="mw-130 text-center">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>15 May 2026</td>
                                        <td>Cuti Bersama Kenaikan Isa Almasih</td>
                                        <td>
                                            <div class="cuti-action-group">
                                                <button type="button" class="cuti-action-btn info"><i class="bi bi-info-circle"></i></button>
                                                <button type="button" class="cuti-action-btn edit"><i class="bi bi-pencil"></i></button>
                                                <button type="button" class="cuti-action-btn delete"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>28 May 2026</td>
                                        <td>Cuti Bersama Idul Adha</td>
                                        <td>
                                            <div class="cuti-action-group">
                                                <button type="button" class="cuti-action-btn info"><i class="bi bi-info-circle"></i></button>
                                                <button type="button" class="cuti-action-btn edit"><i class="bi bi-pencil"></i></button>
                                                <button type="button" class="cuti-action-btn delete"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>26 Dec 2026</td>
                                        <td>Cuti Bersama Natal</td>
                                        <td>
                                            <div class="cuti-action-group">
                                                <button type="button" class="cuti-action-btn info"><i class="bi bi-info-circle"></i></button>
                                                <button type="button" class="cuti-action-btn edit"><i class="bi bi-pencil"></i></button>
                                                <button type="button" class="cuti-action-btn delete"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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

            $('#myTable').DataTable({
                columnDefs: [
                    {
                        targets: 0,
                        type: 'string'
                    }
                ]
            });

            $('#myTableBottom').DataTable({
                columnDefs: [
                    {
                        targets: 0,
                        type: 'string'
                    }
                ]
            });
        });
    </script>
@endsection
