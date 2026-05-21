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

        .attendance-card-icon {
            width: 51px;
            height: 51px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            flex: 0 0 51px;
        }

        .attendance-card-icon--success {
            background: #2BC155;
        }

        .attendance-card-icon--danger {
            background: #F94687;
        }

        .attendance-datetime {
            font-size: 1rem;
            font-weight: 600;
            color: #25314c;
            margin-bottom: 0;
            text-align: center;
        }

        .onsite-running-time {
            font-size: 1.05rem;
            letter-spacing: 0.02em;
        }

        .onsite-map-canvas {
            width: 100%;
            height: 250px;
            border: 1px solid #e6eaf2;
            background: #f8fafc;
        }

        .attendance-detail-label {
            width: 150px;
            color: #64748b;
            font-weight: 600;
        }

        .attendance-detail-map-canvas {
            width: 100%;
            height: 260px;
            border-radius: 0.65rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            overflow: hidden;
        }

        .attendance-detail-map-empty {
            display: none;
            margin-top: 0.65rem;
            font-size: 0.9rem;
            color: #64748b;
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
    'current' => 'Presensi',
    'homeRoute' => 'dashboard',
])

@include('absensi.layouts_absensi.profileIndex')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Presensi</h5>
        <div class="d-flex align-items-center">
            <button
                type="button"
                class="me-2 btn btn-success light btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#clockIn"
            >Presensi Check in</button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Start - Workout Details -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header border-0 pb-3">
                <div>
                    <h4 class="card-title">Attendance Confirmation</h4>
                    <p class="fs-13 mb-0">Ensure your device location is enabled and you are within the authorized work area.</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="d-flex gap-3 align-items-center avatar-success p-4">
                    <div class="attendance-card-icon attendance-card-icon--success">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h6 class="fs-16 text-black mb-0">Ready to Start Your Day?</h6>
                        <span class="fs-12">Good morning. Don't forget to clock in to keep your attendance records up to date.</span>
                    </div>
                </div>
                <div class="d-flex gap-3 justify-content-between flex-wrap p-4 pb-2">
                    <div class="text-center">
                        <p class="fs-14 mb-2">Distance</p>
                        <span class="fs-20 text-black">{{ $todayAttendanceDistanceKm !== null ? number_format($todayAttendanceDistanceKm, 2).' KM' : '- KM' }}</span>
                    </div>
                    <div class="text-center">
                        <p class="fs-14 mb-2">Time</p>
                        <span class="fs-20 text-success" id="attendanceSummaryTimeValue">--:--:--</span>
                    </div>
                    <div class="text-center">
                        <p class="fs-14 mb-2">Clock In</p>
                        <span class="fs-20 text-black">{{ $absensiHariIni?->clock_in?->format('H:i') ?? '-' }}</span>
                    </div>
                </div>
            </div>
            <a
                id="clockInCardButton"
                class="btn light btn-success m-3 mb-2 btn-lg {{ ($hasCheckedInToday ?? false) ? 'disabled' : '' }}"
                @if (!($hasCheckedInToday ?? false))
                    data-bs-toggle="modal"
                    data-bs-target="#clockIn"
                @endif
                @if (($hasCheckedInToday ?? false))
                    aria-disabled="true"
                    tabindex="-1"
                @endif
            >Clock In</a>
            <div class="mb-3"></div>
        </div>
    </div>
    <!-- End - Maps Route -->
    <!-- Start - Workout Details -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header border-0 pb-3">
                <div>
                    <h4 class="card-title">End of Shift</h4>
                    <p class="fs-13 mb-0">Ensure all your daily tasks and status reports have been updated before clocking out.</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="d-flex gap-3 align-items-center avatar-danger p-4">
                    <div class="attendance-card-icon attendance-card-icon--danger">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </div>
                    <div>
                        <h6 class="fs-16 text-black mb-0"><i class="fa-solid fa-moon me-1 text-secondary"></i>Time to Recharge!</h6>
                        <span class="fs-12">Thank you for your hard work today. Please clock out and enjoy the rest of your day.</span>
                    </div>
                </div>
                <div class="d-flex gap-3 justify-content-between flex-wrap p-4 pb-2">
                    <div class="text-center">
                        <p class="fs-14 mb-2">Distance</p>
                        <span class="fs-20 text-black">- KM</span>
                    </div>
                    <div class="text-center">
                        <p class="fs-14 mb-2">Time</p>
                        <span class="fs-20 text-black">08:34:53</span>
                    </div>
                    <div class="text-center">
                        <p class="fs-14 mb-2">Clock Out</p>
                        <span class="fs-20 text-black">17:00</span>
                    </div>
                </div>
            </div>
            <a
                id="clockOutCardButton"
                class="btn light btn-danger m-3 mb-2 btn-lg {{ ($hasCheckedOutToday ?? false) ? 'disabled' : '' }}"
                @if (!($hasCheckedOutToday ?? false))
                    data-bs-toggle="modal"
                    data-bs-target="#clockOut"
                @endif
                @if (($hasCheckedOutToday ?? false))
                    aria-disabled="true"
                    tabindex="-1"
                @endif
            >Clock Out</a>
            <div class="mb-3"></div>
        </div>
    </div>
    <!-- End - Maps Route -->
        <!-- Start - Workout Details -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header border-0 pb-3">
                <div>
                    <h4 class="card-title">Attendance Exception</h4>
                    <p class="fs-13 mb-0">Your logged time is outside the standard. Please ensure your supervisor is aware of this adjustment.</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="d-flex gap-3 align-items-center avatar-secondary p-4">
                    <svg width="51" height="51" viewBox="0 0 51 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="51" height="51" rx="25.5" fill="#A02CFA"></rect>
                        <g clip-path="url()">
                        <path d="M23.8586 19.226L18.8712 24.5542C18.5076 25.0845 18.6439 25.8068 19.1717 26.1679L24.1945 29.6098L24.1945 32.9558C24.1945 33.5921 24.6995 34.125 25.3359 34.1376C25.9874 34.1477 26.5177 33.6249 26.5177 32.976L26.5177 29.0012C26.5177 28.6174 26.3283 28.2588 26.0126 28.0442L22.7904 25.8346L25.5025 22.9583L26.8914 26.1225C27.0758 26.5442 27.4949 26.8169 27.9546 26.8169L32.1844 26.8169C32.8207 26.8169 33.3536 26.3119 33.3662 25.6755C33.3763 25.024 32.8536 24.4937 32.2046 24.4937L28.7172 24.4937C28.2576 23.4482 27.7677 22.4129 27.3409 21.3522C27.1237 20.8169 27.0025 20.5846 26.6036 20.2159C26.5227 20.1401 25.9596 19.625 25.4571 19.1654C24.995 18.7462 24.2828 18.7739 23.8586 19.226Z" fill="white"></path>
                        <path d="M28.6162 19.8068C30.0861 19.8068 31.2778 18.6151 31.2778 17.1452C31.2778 15.6752 30.0861 14.4836 28.6162 14.4836C27.1462 14.4836 25.9545 15.6752 25.9545 17.1452C25.9545 18.6151 27.1462 19.8068 28.6162 19.8068Z" fill="white"></path>
                        <path d="M17.899 37.5164C20.6046 37.5164 22.798 35.323 22.798 32.6174C22.798 29.9117 20.6046 27.7184 17.899 27.7184C15.1934 27.7184 13 29.9117 13 32.6174C13 35.323 15.1934 37.5164 17.899 37.5164Z" fill="white"></path>
                        <path d="M32.101 37.5164C34.8066 37.5164 37 35.323 37 32.6174C37 29.9118 34.8066 27.7184 32.101 27.7184C29.3954 27.7184 27.202 29.9118 27.202 32.6174C27.202 35.323 29.3954 37.5164 32.101 37.5164Z" fill="white"></path>
                        </g>
                        <defs>
                        <clipPath id="clip8">
                        <rect width="24" height="24" fill="white" transform="translate(13 14)"></rect>
                        </clipPath>
                        </defs>
                    </svg>
                    <div>
                        <h6 class="fs-16 text-black mb-0">Schedule Deviation?</h6>
                        <span class="fs-12">Adjusting your schedule? Please leave a brief note for your records.</span>
                    </div>
                </div>
                <div class="d-flex gap-3 justify-content-between flex-wrap p-4 pb-2">
                    <div class="text-center">
                        <p class="fs-14 mb-2">Time</p>
                        <span class="fs-20 text-black">13:00-17:00</span>
                    </div>
                    <div class="text-center">
                        <p class="fs-14 mb-2">Variance</p>
                        <span class="fs-20 text-black">04.00</span>
                    </div>
                </div>
            </div>
            <a class="btn light btn-secondary m-3 mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#exception">Exception</a>
            <div class="mb-3"></div>
        </div>
    </div>
    <!-- End - Maps Route -->
</div>

<div class="tab-content" id="tabContentMyProfileBottom">
    <div class="row">

        <!-- Start - logs -->
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 align-items-center logs-card-header">
                    <h4 class="card-title">Logs</h4>
                    <div class="d-flex align-items-center gap-2 logs-toolbar">
                        <div class="clearfix">
                            <select class="selectpicker">
                                <option value="All Time">All Time</option>
                                <option value="Weekly">Week</option>
                                <option value="Monthly">Month</option>
                            </select>
                        </div>
                        <div class="clearfix">
                            <select class="selectpicker">
                                <option value="View All">View All</option>
                                <option value="Top 10">Top 10</option>
                                <option value="Top 20">Top 20</option>
                            </select>
                        </div>
                        <div class="clearfix">
                            <button class="btn btn-sm btn-primary">Reset</button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-card-body px-0 pt-0 pb-2">
                    <div class="table-responsive attendance-table-scroll-area">
                        <table id="tableLogs" class="table table-sm table-sm-responsive text-nowrap">
                            <thead>
                                <tr>
                                    <th class="mw-50">No</th>
                                    @if ($showStaffPeriodFilter ?? false)
                                    <th class="mw-120">Date</th>
                                    @endif
                                    @if (! ($showStaffPeriodFilter ?? false))
                                    <th class="mw-150">Nama Staff</th>
                                    @endif
                                    <th class="mw-100">Masuk</th>
                                    <th class="mw-150">Pulang</th>
                                    @if (! ($showStaffPeriodFilter ?? false))
                                    <th class="mw-100">Nama PT</th>
                                    <th class="text-end mw-100">Action</th>
                                    @endif
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
<div class="modal fade" id="attendanceDetailModal" tabindex="-1" aria-labelledby="attendanceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceDetailModalLabel">Detail Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                        <tr>
                            <td class="attendance-detail-label">Alamat</td>
                            <td id="attendanceDetailFormattedAddress">-</td>
                        </tr>
                        <tr>
                            <td class="attendance-detail-label">Desa/Kelurahan</td>
                            <td id="attendanceDetailVillage">-</td>
                        </tr>
                        <tr>
                            <td class="attendance-detail-label">Kecamatan</td>
                            <td id="attendanceDetailDistrict">-</td>
                        </tr>
                        <tr>
                            <td class="attendance-detail-label">Kabupaten/Kota</td>
                            <td id="attendanceDetailRegency">-</td>
                        </tr>
                        <tr>
                            <td class="attendance-detail-label">Provinsi</td>
                            <td id="attendanceDetailProvince">-</td>
                        </tr>
                        <tr>
                            <td class="attendance-detail-label">Kode Pos</td>
                            <td id="attendanceDetailPostalCode">-</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <div id="attendanceDetailLocationInfo" class="small text-muted mb-2">Lokasi absen: -</div>
                    <div id="attendanceDetailMapCanvas" class="attendance-detail-map-canvas"></div>
                    <div id="attendanceDetailMapEmpty" class="attendance-detail-map-empty">Lokasi absen tidak tersedia.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<!-- End - Content Body -->

<!-- Modal Box Start -->
<div class="modal fade" id="clockIn" tabindex="-1" aria-labelledby="clockInLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="clockInLabel">Attendance Confirmation</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="form-label mb-3 text-center">
                    <span id="clockInCurrentDate">--</span> -
                    <span id="clockInRunningTime" class="onsite-running-time text-success fw-semibold">--:--:--</span>
                </p>
                <p class="form-label text-muted mb-3">
                    Grab your coffee and let's get things done. Clock in when you're ready to kick off your shift!
                </p>
                <div class="mb-3">
                    <label class="form-label">Current Location</label>
                    <div id="clockInMapCanvas" class="onsite-map-canvas rounded"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="clockInVerifyBtn">Mulai Verifikasi</button>
                    </div>
                    <p class="small d-none mt-2 mb-0" id="clockInVerifyMessage"></p>
                </div>
                <div class="mb-0">
                    <label class="form-label">IP Address</label>
                    <p class="mb-0">
                        @if (! empty($publicIp))
                            <span id="clockInIpText" class="{{ ($isIpPrefixMatch ?? false) ? 'text-success' : 'text-danger' }}">{{ $publicIp }}</span>
                        @else
                            <span id="clockInIpText" class="text-muted">Memuat...</span>
                        @endif
                        <span id="clockInIpBadge" class="ms-1 {{ ($isIpPrefixMatch ?? false) ? 'text-success' : 'text-danger' }}">{{ ($isIpPrefixMatch ?? false) ? 'Valid' : 'Tidak Valid' }}</span>
                    </p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn light btn-success btn-lg w-100" id="clockInSubmitBtn">Clock In</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

<!-- Modal Box Start -->
<div class="modal fade" id="clockOut" tabindex="-1" aria-labelledby="clockOutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="clockOutLabel">End of Shift</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="form-label mb-3 text-center">
                    <span id="clockOutCurrentDate">--</span> -
                    <span id="clockOutRunningTime" class="onsite-running-time text-success fw-semibold">--:--:--</span>
                </p>
                <p class="form-label text-muted mb-3">
                    Please make sure your daily tasks are wrapped up before clocking out. Thank you for your hard work, and enjoy the rest of your day!
                </p>
                <div class="mb-3">
                    <label class="form-label">Current Location</label>
                    <div id="clockOutMapCanvas" class="onsite-map-canvas rounded"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="clockOutVerifyBtn">Mulai Verifikasi</button>
                    </div>
                    <p class="small d-none mt-2 mb-0" id="clockOutVerifyMessage"></p>
                </div>
                <div class="mb-0">
                    <label class="form-label">IP Address</label>
                    <p class="mb-0">
                        @if (! empty($publicIp))
                            <span id="clockOutIpText" class="{{ ($isIpPrefixMatch ?? false) ? 'text-success' : 'text-danger' }}">{{ $publicIp }}</span>
                        @else
                            <span id="clockOutIpText" class="text-muted">Memuat...</span>
                        @endif
                        <span id="clockOutIpBadge" class="ms-1 {{ ($isIpPrefixMatch ?? false) ? 'text-success' : 'text-danger' }}">{{ ($isIpPrefixMatch ?? false) ? 'Valid' : 'Tidak Valid' }}</span>
                    </p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn light btn-danger btn-lg w-100" id="clockOutSubmitBtn">Clock Out</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

<!-- Modal Box Start -->
<div class="modal fade" id="exception" tabindex="-1" aria-labelledby="exceptionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exceptionLabel">Attendance Exception</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <p class="form-label mb-3 text-center">Wed, 20 May 2026 - 
                                <span class="fw-semibold">17:00:10</span>
                            </p>
                            <p class="form-label text-muted mb-3">
                                Clocking in late or heading out early? Just make sure your <span class="fw-bold">supervisor</span> is in the loop 
                                and there are <span class="fw-bold">no urgent tasks</span> left behind. <br> Oh, and don't forget to leave a quick note!
                            </p>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label text-secondary">Quick Note</label>
                                <input type="email" class="form-control" id="exampleFormControlInput1" placeholder="Contoh: Izin ke dokter, macet, atau ada urusan keluarga">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label text-secondary">Request Type</label>
                                <div class="form-group mb-0">
                                    <div class="form-check d-inline-block">
                                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault4">
                                        <label class="form-check-label" for="flexRadioDefault4">Late Arrival</label>
                                    </div>
                                    <div class="form-check d-inline-block mx-2">
                                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault5">
                                        <label class="form-check-label" for="flexRadioDefault5">Early Departure</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="exampleFormControlInput1" class="form-label text-secondary">From</label>
                                        <input type="time" class="form-control" id="exampleFormControlInput1">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="exampleFormControlInput1" class="form-label text-secondary">To</label>
                                        <input type="time" class="form-control" id="exampleFormControlInput1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <a class="btn light btn-secondary mt-2 mb-2 btn-lg w-100" data-bs-toggle="modal" data-bs-target="#clockOut">Got it!</a>
            </div>
        </div>
    </div>
</div>
<!-- Modal-Box-End -->

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
            var attendanceSummaryTimeElement = document.getElementById('attendanceSummaryTimeValue');
            var clockInCardButtonElement = document.getElementById('clockInCardButton');
            var clockOutCardButtonElement = document.getElementById('clockOutCardButton');
            var attendanceCompanyFilter = document.getElementById('attendanceCompanyFilter');
            var attendanceMonthFilter = document.getElementById('attendanceMonthFilter');
            var attendanceYearFilter = document.getElementById('attendanceYearFilter');
            var googleMapsApiKey = @json(config('services.google_maps.api_key'));
            var officeLocation = @json($officeLocation);
            var clockInModalElement = document.getElementById('clockIn');
            var clockOutModalElement = document.getElementById('clockOut');
            var attendanceDetailModalElement = document.getElementById('attendanceDetailModal');
            var attendanceDetailModalLabel = document.getElementById('attendanceDetailModalLabel');
            var clockInCurrentDateElement = document.getElementById('clockInCurrentDate');
            var clockOutCurrentDateElement = document.getElementById('clockOutCurrentDate');
            var clockInRunningTimeElement = document.getElementById('clockInRunningTime');
            var clockOutRunningTimeElement = document.getElementById('clockOutRunningTime');
            var clockInMapCanvasElement = document.getElementById('clockInMapCanvas');
            var clockOutMapCanvasElement = document.getElementById('clockOutMapCanvas');
            var clockInStatusTextElement = document.getElementById('clockInStatusText');
            var clockOutStatusTextElement = document.getElementById('clockOutStatusText');
            var clockInVerifyButton = document.getElementById('clockInVerifyBtn');
            var clockOutVerifyButton = document.getElementById('clockOutVerifyBtn');
            var clockInVerifyMessageElement = document.getElementById('clockInVerifyMessage');
            var clockOutVerifyMessageElement = document.getElementById('clockOutVerifyMessage');
            var clockInIpTextElement = document.getElementById('clockInIpText');
            var clockOutIpTextElement = document.getElementById('clockOutIpText');
            var clockInIpBadgeElement = document.getElementById('clockInIpBadge');
            var clockOutIpBadgeElement = document.getElementById('clockOutIpBadge');
            var clockInSubmitButton = document.getElementById('clockInSubmitBtn');
            var clockOutSubmitButton = document.getElementById('clockOutSubmitBtn');
            var attendanceDetailFormattedAddressElement = document.getElementById('attendanceDetailFormattedAddress');
            var attendanceDetailVillageElement = document.getElementById('attendanceDetailVillage');
            var attendanceDetailDistrictElement = document.getElementById('attendanceDetailDistrict');
            var attendanceDetailRegencyElement = document.getElementById('attendanceDetailRegency');
            var attendanceDetailProvinceElement = document.getElementById('attendanceDetailProvince');
            var attendanceDetailPostalCodeElement = document.getElementById('attendanceDetailPostalCode');
            var attendanceDetailLocationInfoElement = document.getElementById('attendanceDetailLocationInfo');
            var attendanceDetailMapCanvasElement = document.getElementById('attendanceDetailMapCanvas');
            var attendanceDetailMapEmptyElement = document.getElementById('attendanceDetailMapEmpty');
            var attendanceDetailMapInstance = null;
            var attendanceDetailUserMarker = null;
            var attendanceDetailOfficeMarker = null;
            var attendanceDetailLine = null;
            var storeAttendanceUrl = @json(route('absensi.store'));
            var updateAttendanceUrlTemplate = @json(url('/absensi/__ATTENDANCE_ID__'));
            var attendanceDatatableUrl = @json(route('absensi.datatable'));
            var projectManagementIndexUrl = @json(route('project_management'));
            var currentIpUrl = @json(route('absensi.current-ip'));
            var verifyTelegramUsernameUrl = @json(route('absensi.verify-telegram-username'));
            var isStaffTableView = @json($showStaffPeriodFilter ?? false);
            var csrfToken = @json(csrf_token());
            var browserPublicIp = null;
            var attendanceState = {
                todayAttendanceId: @json($todayAttendanceId ?? null),
                hasCheckedInToday: @json($hasCheckedInToday ?? false),
                hasCheckedOutToday: @json($hasCheckedOutToday ?? false)
            };
            var modalContext = {
                clockIn: {
                    type: 'clock_in',
                    modalElement: clockInModalElement,
                    currentDateElement: clockInCurrentDateElement,
                    runningTimeElement: clockInRunningTimeElement,
                    mapCanvasElement: clockInMapCanvasElement,
                    statusTextElement: clockInStatusTextElement,
                    verifyButtonElement: clockInVerifyButton,
                    verifyMessageElement: clockInVerifyMessageElement,
                    ipTextElement: clockInIpTextElement,
                    ipBadgeElement: clockInIpBadgeElement,
                    submitButtonElement: clockInSubmitButton,
                    hasVerifiedOnsite: false,
                    hasVerifiedTelegram: false,
                    latestUserCoordinates: null,
                    mapInstance: null,
                    officeMarker: null,
                    officeRadiusCircle: null,
                    userMarker: null,
                    userToOfficeLine: null
                },
                clockOut: {
                    type: 'clock_out',
                    modalElement: clockOutModalElement,
                    currentDateElement: clockOutCurrentDateElement,
                    runningTimeElement: clockOutRunningTimeElement,
                    mapCanvasElement: clockOutMapCanvasElement,
                    statusTextElement: clockOutStatusTextElement,
                    verifyButtonElement: clockOutVerifyButton,
                    verifyMessageElement: clockOutVerifyMessageElement,
                    ipTextElement: clockOutIpTextElement,
                    ipBadgeElement: clockOutIpBadgeElement,
                    submitButtonElement: clockOutSubmitButton,
                    hasVerifiedOnsite: false,
                    hasVerifiedTelegram: false,
                    latestUserCoordinates: null,
                    mapInstance: null,
                    officeMarker: null,
                    officeRadiusCircle: null,
                    userMarker: null,
                    userToOfficeLine: null
                }
            };
            var officeStartTotalMinutes = parseTimeStringToMinutes(officeLocation && officeLocation.office_start_time, 8 * 60);
            var officeEndTotalMinutes = parseTimeStringToMinutes(officeLocation && officeLocation.office_end_time, 17 * 60);
            var lateGraceMinutes = Number(officeLocation && officeLocation.late_grace_minutes);
            lateGraceMinutes = Number.isNaN(lateGraceMinutes) ? 0 : Math.max(lateGraceMinutes, 0);
            var lateThresholdTotalMinutes = officeStartTotalMinutes + lateGraceMinutes;

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
                    { data: null, defaultContent: '' }
                ];
                if (isStaffTableView) {
                    attendanceColumns.push({ data: 'attendance_created_at', defaultContent: '-' });
                }
                if (!isStaffTableView) {
                    attendanceColumns.push({ data: 'staff_name', defaultContent: '-' });
                }
                attendanceColumns.push(
                    { data: 'check_in', defaultContent: '' },
                    { data: 'check_out', defaultContent: '' }
                );
                if (!isStaffTableView) {
                    attendanceColumns.push(
                        { data: 'company_name', defaultContent: '-' },
                        { data: null, defaultContent: '' }
                    );
                }

                var checkInColumnIndex = isStaffTableView ? 1 : 2;
                var checkOutColumnIndex = isStaffTableView ? 2 : 3;
                if (isStaffTableView) {
                    checkInColumnIndex = 2;
                    checkOutColumnIndex = 3;
                }
                var actionColumnIndex = attendanceColumns.length - 1;
                var attendanceColumnDefs = [
                    {
                        targets: 0,
                        searchable: false,
                        orderable: false
                    },
                    {
                        targets: checkInColumnIndex,
                        render: function (data) {
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
                        render: function (data) {
                            if (data) {
                                return data;
                            }

                            return '<span class="badge-attendance-empty">Belum Absen Pulang</span>';
                        }
                    }
                ];
                if (isStaffTableView) {
                    attendanceColumnDefs.push({
                        targets: 1,
                        render: function (data) {
                            if (!data) {
                                return '-';
                            }

                            return data;
                        }
                    });
                }
                if (!isStaffTableView) {
                    attendanceColumnDefs.push({
                        targets: actionColumnIndex,
                        searchable: false,
                        orderable: false,
                        className: 'text-end',
                        render: function () {
                            return '<div class="dropdown dropdown-sm">'
                                + '<button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Options</button>'
                                + '<ul class="dropdown-menu dropdown-menu-end">'
                                + '<li><a class="dropdown-item" href="javascript:void(0)" onclick="onClickAttendanceDetail(this)">Detail</a></li>'
                                + '</ul>'
                                + '</div>';
                        }
                    });
                }

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
                    pageLength: 6,
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
                    var pageInfo = attendanceTable.page.info();
                    attendanceTable.column(0, { page: 'current' }).nodes().each(function (cell, index) {
                        cell.innerHTML = pageInfo.start + index + 1;
                    });
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

            function eachModalContext(callback) {
                callback(modalContext.clockIn);
                callback(modalContext.clockOut);
            }

            function setOnsiteStatus(context, text) {
                if (!context || !context.statusTextElement) {
                    return;
                }

                context.statusTextElement.textContent = text;
            }

            function setVerificationMessage(context, text, type) {
                if (!context || !context.verifyMessageElement) {
                    return;
                }

                context.verifyMessageElement.classList.remove('d-none', 'text-success', 'text-danger', 'text-warning', 'text-muted');
                context.verifyMessageElement.textContent = text;
                if (type === 'success') {
                    context.verifyMessageElement.classList.add('text-success');
                    return;
                }
                if (type === 'error') {
                    context.verifyMessageElement.classList.add('text-danger');
                    return;
                }
                if (type === 'warning') {
                    context.verifyMessageElement.classList.add('text-warning');
                    return;
                }
                context.verifyMessageElement.classList.add('text-muted');
            }

            function resetVerificationUi(context) {
                if (!context) {
                    return;
                }

                context.hasVerifiedOnsite = false;
                context.hasVerifiedTelegram = false;
                context.latestUserCoordinates = null;
                if (context.verifyButtonElement) {
                    context.verifyButtonElement.classList.remove('d-none');
                    context.verifyButtonElement.disabled = false;
                }
                if (context.verifyMessageElement) {
                    context.verifyMessageElement.classList.add('d-none');
                    context.verifyMessageElement.textContent = '';
                }
                setOnsiteStatus(context, 'Harap verifikasi terlebih dahulu sebelum absen');
            }

            function setOnsiteIpIndicator(context, ipAddress, isValidIpPrefix) {
                if (!context || !context.ipTextElement) {
                    return;
                }

                var normalizedIp = (!ipAddress || ipAddress === '-') ? 'Tidak tersedia' : ipAddress;
                context.ipTextElement.textContent = normalizedIp;
                context.ipTextElement.classList.remove('text-success', 'text-danger', 'text-muted');
                if (normalizedIp === 'Tidak tersedia') {
                    context.ipTextElement.classList.add('text-muted');
                } else {
                    context.ipTextElement.classList.add(isValidIpPrefix ? 'text-success' : 'text-danger');
                }

                if (context.ipBadgeElement) {
                    context.ipBadgeElement.textContent = normalizedIp === 'Tidak tersedia'
                        ? 'Tidak tersedia'
                        : (isValidIpPrefix ? 'Valid' : 'Tidak Valid');
                    context.ipBadgeElement.classList.remove('text-success', 'text-danger', 'text-muted');
                    context.ipBadgeElement.classList.add(normalizedIp === 'Tidak tersedia'
                        ? 'text-muted'
                        : (isValidIpPrefix ? 'text-success' : 'text-danger'));
                }
            }

            function setOnsiteIpLoadingState(context) {
                if (!context || !context.ipTextElement) {
                    return;
                }

                context.ipTextElement.textContent = 'Memuat...';
                context.ipTextElement.classList.remove('text-success', 'text-danger');
                context.ipTextElement.classList.add('text-muted');
                if (context.ipBadgeElement) {
                    context.ipBadgeElement.textContent = '';
                }
            }

            function refreshOnsiteIpIndicator() {
                if (!currentIpUrl) {
                    return;
                }

                $.ajax({
                    url: currentIpUrl,
                    method: 'GET',
                    data: browserPublicIp ? { client_ip: browserPublicIp } : {},
                    timeout: 10000
                }).done(function (response) {
                    var ipAddress = response && response.ip ? response.ip : '-';
                    var isValidIpPrefix = !!(response && response.is_ip_prefix_match);
                    eachModalContext(function (context) {
                        setOnsiteIpIndicator(context, ipAddress, isValidIpPrefix);
                    });
                }).fail(function () {
                    eachModalContext(function (context) {
                        setOnsiteIpIndicator(context, '-', false);
                    });
                });
            }

            function resolveBrowserPublicIpAndRefresh() {
                $.ajax({
                    url: 'https://api.ipify.org?format=json',
                    method: 'GET',
                    timeout: 7000
                }).done(function (response) {
                    if (response && response.ip) {
                        browserPublicIp = response.ip;
                    }
                }).always(function () {
                    refreshOnsiteIpIndicator();
                });
            }

            function getUpdateAttendanceUrl(attendanceId) {
                return updateAttendanceUrlTemplate.replace('__ATTENDANCE_ID__', String(attendanceId));
            }

            function renderSubmitButtons() {
                if (clockInSubmitButton) {
                    clockInSubmitButton.disabled = !modalContext.clockIn.hasVerifiedOnsite
                        || !modalContext.clockIn.hasVerifiedTelegram
                        || attendanceState.hasCheckedInToday;
                }
                if (clockOutSubmitButton) {
                    clockOutSubmitButton.disabled = !modalContext.clockOut.hasVerifiedOnsite
                        || !modalContext.clockOut.hasVerifiedTelegram
                        || !attendanceState.hasCheckedInToday
                        || attendanceState.hasCheckedOutToday;
                }

                if (clockInCardButtonElement) {
                    clockInCardButtonElement.classList.toggle('disabled', attendanceState.hasCheckedInToday);
                    if (attendanceState.hasCheckedInToday) {
                        clockInCardButtonElement.removeAttribute('data-bs-toggle');
                        clockInCardButtonElement.removeAttribute('data-bs-target');
                        clockInCardButtonElement.setAttribute('aria-disabled', 'true');
                        clockInCardButtonElement.setAttribute('tabindex', '-1');
                    } else {
                        clockInCardButtonElement.setAttribute('data-bs-toggle', 'modal');
                        clockInCardButtonElement.setAttribute('data-bs-target', '#clockIn');
                        clockInCardButtonElement.removeAttribute('aria-disabled');
                        clockInCardButtonElement.removeAttribute('tabindex');
                    }
                }

                if (clockOutCardButtonElement) {
                    clockOutCardButtonElement.classList.toggle('disabled', attendanceState.hasCheckedOutToday);
                    if (attendanceState.hasCheckedOutToday) {
                        clockOutCardButtonElement.removeAttribute('data-bs-toggle');
                        clockOutCardButtonElement.removeAttribute('data-bs-target');
                        clockOutCardButtonElement.setAttribute('aria-disabled', 'true');
                        clockOutCardButtonElement.setAttribute('tabindex', '-1');
                    } else {
                        clockOutCardButtonElement.setAttribute('data-bs-toggle', 'modal');
                        clockOutCardButtonElement.setAttribute('data-bs-target', '#clockOut');
                        clockOutCardButtonElement.removeAttribute('aria-disabled');
                        clockOutCardButtonElement.removeAttribute('tabindex');
                    }
                }
            }

            function toRadians(value) {
                return value * (Math.PI / 180);
            }

            function calculateDistanceInMeters(startLat, startLng, endLat, endLng) {
                var earthRadius = 6371000;
                var latitudeDelta = toRadians(endLat - startLat);
                var longitudeDelta = toRadians(endLng - startLng);
                var a = Math.sin(latitudeDelta / 2) * Math.sin(latitudeDelta / 2)
                    + Math.cos(toRadians(startLat)) * Math.cos(toRadians(endLat))
                    * Math.sin(longitudeDelta / 2) * Math.sin(longitudeDelta / 2);
                var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                return earthRadius * c;
            }

            function loadGoogleMapsApi() {
                return new Promise(function (resolve, reject) {
                    if (window.google && window.google.maps) {
                        resolve();
                        return;
                    }

                    if (!googleMapsApiKey) {
                        reject(new Error('GOOGLE_MAPS_API_KEY belum diset'));
                        return;
                    }

                    var existingScript = document.getElementById('googleMapsScript');
                    if (existingScript) {
                        existingScript.addEventListener('load', function () {
                            resolve();
                        });
                        existingScript.addEventListener('error', function () {
                            reject(new Error('Gagal memuat Google Maps API'));
                        });
                        return;
                    }

                    var script = document.createElement('script');
                    script.id = 'googleMapsScript';
                    script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(googleMapsApiKey);
                    script.async = true;
                    script.defer = true;
                    script.onload = function () {
                        resolve();
                    };
                    script.onerror = function () {
                        reject(new Error('Gagal memuat Google Maps API'));
                    };
                    document.head.appendChild(script);
                });
            }

            function initializeOnsiteMap(context) {
                if (!context || !context.mapCanvasElement) {
                    return;
                }

                if (!officeLocation || officeLocation.latitude === null || officeLocation.longitude === null) {
                    setOnsiteStatus(context, 'Koordinat kantor belum tersedia di database perusahaan');
                    return;
                }

                if (context.mapInstance) {
                    return;
                }

                if (!window.google || !window.google.maps) {
                    return;
                }

                var officePosition = {
                    lat: Number(officeLocation.latitude),
                    lng: Number(officeLocation.longitude)
                };
                var officeRadius = Number(officeLocation.radius_meters || 100);

                context.mapInstance = new window.google.maps.Map(context.mapCanvasElement, {
                    center: officePosition,
                    zoom: 17,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: false
                });

                context.officeMarker = new window.google.maps.Marker({
                    position: officePosition,
                    map: context.mapInstance,
                    title: officeLocation.name || 'Office'
                });

                context.officeRadiusCircle = new window.google.maps.Circle({
                    map: context.mapInstance,
                    center: officePosition,
                    radius: officeRadius,
                    strokeColor: '#2563eb',
                    strokeOpacity: 0.85,
                    strokeWeight: 2,
                    fillColor: '#60a5fa',
                    fillOpacity: 0.14
                });
            }

            function updateUserLocationOnMap(context, position) {
                if (!context || !context.mapInstance || !officeLocation) {
                    return;
                }

                context.latestUserCoordinates = {
                    latitude: Number(position.coords.latitude),
                    longitude: Number(position.coords.longitude)
                };

                var officePosition = {
                    lat: Number(officeLocation.latitude),
                    lng: Number(officeLocation.longitude)
                };
                var userPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                var distance = calculateDistanceInMeters(
                    userPosition.lat,
                    userPosition.lng,
                    officePosition.lat,
                    officePosition.lng
                );
                var allowedRadius = Number(officeLocation.radius_meters || 100);
                var inRadius = distance <= allowedRadius;
                context.hasVerifiedOnsite = true;

                if (!context.userMarker) {
                    context.userMarker = new window.google.maps.Marker({
                        position: userPosition,
                        map: context.mapInstance,
                        title: 'Lokasi Saya',
                        icon: {
                            path: window.google.maps.SymbolPath.CIRCLE,
                            scale: 8,
                            fillColor: '#dc2626',
                            fillOpacity: 1,
                            strokeColor: '#ffffff',
                            strokeWeight: 2
                        }
                    });
                } else {
                    context.userMarker.setPosition(userPosition);
                }

                if (context.userToOfficeLine) {
                    context.userToOfficeLine.setMap(null);
                }

                context.userToOfficeLine = new window.google.maps.Polyline({
                    path: [officePosition, userPosition],
                    geodesic: true,
                    strokeColor: '#dc2626',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    map: context.mapInstance
                });

                context.mapInstance.panTo(userPosition);
                setOnsiteStatus(context, inRadius ? 'Di dalam radius kantor' : 'Di luar radius kantor');
                renderSubmitButtons();
            }

            function verifyTelegramUsernameSync() {
                return new Promise(function (resolve, reject) {
                    if (!verifyTelegramUsernameUrl) {
                        reject(new Error('Endpoint verifikasi Telegram belum tersedia.'));
                        return;
                    }

                    $.ajax({
                        url: verifyTelegramUsernameUrl,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    }).done(function (response) {
                        if (response && response.success) {
                            resolve(response);
                            return;
                        }

                        reject(new Error(response && response.message ? response.message : 'Verifikasi Telegram gagal.'));
                    }).fail(function (xhr) {
                        var errorMessage = 'Verifikasi Telegram gagal.';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        reject(new Error(errorMessage));
                    });
                });
            }

            function checkOnsiteLocation(context) {
                if (!context) {
                    return;
                }

                if (!navigator.geolocation) {
                    context.hasVerifiedOnsite = false;
                    context.hasVerifiedTelegram = false;
                    setOnsiteStatus(context, 'Browser tidak mendukung geolocation');
                    setVerificationMessage(context, 'Browser tidak mendukung geolocation', 'error');
                    renderSubmitButtons();
                    return;
                }

                if (!window.isSecureContext) {
                    context.hasVerifiedOnsite = false;
                    context.hasVerifiedTelegram = false;
                    setOnsiteStatus(context, 'Geolocation hanya jalan di HTTPS atau localhost');
                    setVerificationMessage(context, 'Geolocation hanya jalan di HTTPS atau localhost', 'error');
                    renderSubmitButtons();
                    return;
                }

                context.hasVerifiedOnsite = false;
                context.hasVerifiedTelegram = false;
                if (context.verifyButtonElement) {
                    context.verifyButtonElement.classList.add('d-none');
                }
                setOnsiteStatus(context, 'Memverifikasi Telegram dan lokasi...');
                setVerificationMessage(context, 'Memverifikasi Telegram dan lokasi...', 'muted');
                renderSubmitButtons();

                verifyTelegramUsernameSync().then(function () {
                    context.hasVerifiedTelegram = true;
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            updateUserLocationOnMap(context, position);
                            setOnsiteStatus(context, 'Verifikasi lokasi dan Telegram berhasil.');
                            setVerificationMessage(context, 'Verifikasi berhasil. Kamu bisa lanjut submit.', 'success');
                        },
                        function (error) {
                            context.hasVerifiedOnsite = false;
                            renderSubmitButtons();

                            if (!error || typeof error.code === 'undefined') {
                                setOnsiteStatus(context, 'Gagal mendapatkan lokasi');
                                setVerificationMessage(context, 'Gagal mendapatkan lokasi', 'error');
                                return;
                            }

                            if (error.code === 1) {
                                setOnsiteStatus(context, 'Izin lokasi ditolak. Izinkan lokasi di browser.');
                                setVerificationMessage(context, 'Izin lokasi ditolak. Izinkan lokasi di browser.', 'error');
                                return;
                            }

                            if (error.code === 2) {
                                setOnsiteStatus(context, 'Lokasi tidak tersedia. Aktifkan GPS/lokasi perangkat.');
                                setVerificationMessage(context, 'Lokasi tidak tersedia. Aktifkan GPS/lokasi perangkat.', 'error');
                                return;
                            }

                            if (error.code === 3) {
                                setOnsiteStatus(context, 'Timeout saat mengambil lokasi. Coba lagi.');
                                setVerificationMessage(context, 'Timeout saat mengambil lokasi. Coba lagi.', 'error');
                                return;
                            }

                            setOnsiteStatus(context, 'Gagal mendapatkan lokasi');
                            setVerificationMessage(context, 'Gagal mendapatkan lokasi', 'error');
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0
                        }
                    );
                }).catch(function (error) {
                    context.hasVerifiedTelegram = false;
                    context.hasVerifiedOnsite = false;
                    renderSubmitButtons();
                    setOnsiteStatus(context, error && error.message ? error.message : 'Verifikasi Telegram gagal.');
                    setVerificationMessage(context, error && error.message ? error.message : 'Verifikasi Telegram gagal.', 'error');
                });
            }

            function resolveCurrentCoordinatesForAttendance(context) {
                return new Promise(function (resolve) {
                    if (!navigator.geolocation || !window.isSecureContext) {
                        resolve(context.latestUserCoordinates);
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            context.latestUserCoordinates = {
                                latitude: Number(position.coords.latitude),
                                longitude: Number(position.coords.longitude)
                            };
                            resolve(context.latestUserCoordinates);
                        },
                        function () {
                            resolve(context.latestUserCoordinates);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 7000,
                            maximumAge: 0
                        }
                    );
                });
            }

            function submitOnsiteAttendance(actionType, context) {
                if (!context || !context.submitButtonElement) {
                    return;
                }

                if (actionType === 'clock_in' && attendanceState.hasCheckedInToday) {
                    setOnsiteStatus(context, 'Kamu sudah absen masuk hari ini');
                    return;
                }

                if (actionType === 'clock_out' && !attendanceState.hasCheckedInToday) {
                    setOnsiteStatus(context, 'Kamu belum absen masuk hari ini');
                    return;
                }

                if (actionType === 'clock_out' && attendanceState.hasCheckedOutToday) {
                    setOnsiteStatus(context, 'Kamu sudah absen pulang hari ini');
                    return;
                }

                if (!(context.hasVerifiedOnsite && context.hasVerifiedTelegram)) {
                    setOnsiteStatus(context, 'Harap verifikasi terlebih dahulu sebelum absen');
                    renderSubmitButtons();
                    return;
                }

                context.submitButtonElement.disabled = true;
                setOnsiteStatus(context, 'Memproses submit absen...');

                var isCheckInAction = actionType === 'clock_in';
                var requestMethod = isCheckInAction ? 'POST' : 'PATCH';
                var requestUrl = isCheckInAction
                    ? storeAttendanceUrl
                    : getUpdateAttendanceUrl(attendanceState.todayAttendanceId);

                resolveCurrentCoordinatesForAttendance(context).then(function (coordinates) {
                    var payload = {
                        client_ip: browserPublicIp || (context.ipTextElement ? context.ipTextElement.textContent.trim() : null)
                    };

                    if (coordinates && Number.isFinite(coordinates.latitude) && Number.isFinite(coordinates.longitude)) {
                        payload.latitude = coordinates.latitude;
                        payload.longitude = coordinates.longitude;
                    }

                    $.ajax({
                        url: requestUrl,
                        method: requestMethod,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: payload
                    }).done(function (response) {
                        setOnsiteStatus(context, response && response.message ? response.message : 'Absen berhasil disimpan');
                        if (isCheckInAction) {
                            attendanceState.hasCheckedInToday = true;
                            if (response && response.attendance_id) {
                                attendanceState.todayAttendanceId = response.attendance_id;
                            }
                        } else {
                            attendanceState.hasCheckedOutToday = true;
                        }
                        renderSubmitButtons();
                        resolveBrowserPublicIpAndRefresh();
                        if (projectManagementIndexUrl) {
                            window.location.href = projectManagementIndexUrl;
                            return;
                        }
                    }).fail(function (xhr) {
                        var errorMessage = 'Gagal memproses absen';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        setOnsiteStatus(context, errorMessage);
                    }).always(function () {
                        renderSubmitButtons();
                    });
                });
            }

            function renderAttendanceDateTime() {
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

                if (attendanceDateElement) {
                    attendanceDateElement.textContent = formattedDateTime;
                }

                var modalDate = dateMap.weekday + ', ' + dateMap.day + ' ' + dateMap.month + ' ' + dateMap.year;
                if (clockInCurrentDateElement) {
                    clockInCurrentDateElement.textContent = modalDate;
                }
                if (clockOutCurrentDateElement) {
                    clockOutCurrentDateElement.textContent = modalDate;
                }
            }

            function renderOnsiteRunningTime() {
                var now = new Date();

                var timeParts = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hourCycle: 'h23'
                }).formatToParts(now);

                var timeMap = {};
                timeParts.forEach(function (part) {
                    timeMap[part.type] = part.value;
                });

                var hour = parseInt(timeMap.hour, 10);
                var minute = parseInt(timeMap.minute, 10);
                var totalMinutes = (hour * 60) + minute;
                var formattedTime = timeMap.hour + ':' + timeMap.minute + ':' + timeMap.second;

                if (attendanceSummaryTimeElement) {
                    attendanceSummaryTimeElement.textContent = formattedTime;
                    attendanceSummaryTimeElement.classList.remove('text-success', 'text-danger');
                    attendanceSummaryTimeElement.classList.add(totalMinutes <= lateThresholdTotalMinutes ? 'text-success' : 'text-danger');
                }

                [clockInRunningTimeElement, clockOutRunningTimeElement].forEach(function (runningTimeElement) {
                    if (!runningTimeElement) {
                        return;
                    }

                    runningTimeElement.textContent = formattedTime;
                    runningTimeElement.classList.remove('text-success', 'text-warning', 'text-danger', 'text-secondary');

                    if (totalMinutes < officeStartTotalMinutes) {
                        runningTimeElement.classList.add('text-success');
                    } else if (totalMinutes <= lateThresholdTotalMinutes) {
                        runningTimeElement.classList.add('text-warning');
                    } else if (totalMinutes > officeEndTotalMinutes) {
                        runningTimeElement.classList.add('text-secondary');
                    } else {
                        runningTimeElement.classList.add('text-danger');
                    }
                });
            }

            function showAttendanceDetail(rowData) {
                var staffName = rowData && rowData.staff_name ? rowData.staff_name : '-';
                var formattedAddress = rowData && rowData.formatted_address ? rowData.formatted_address : '-';
                var villageName = rowData && rowData.address_village ? rowData.address_village : '-';
                var districtName = rowData && rowData.address_district ? rowData.address_district : '-';
                var regencyName = rowData && rowData.address_regency ? rowData.address_regency : (rowData && rowData.address_city ? rowData.address_city : '-');
                var provinceName = rowData && rowData.address_province ? rowData.address_province : '-';
                var postalCode = rowData && rowData.address_postal_code ? rowData.address_postal_code : '-';

                if (!attendanceDetailModalElement || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                    return;
                }

                if (attendanceDetailModalLabel) {
                    attendanceDetailModalLabel.textContent = 'Detail Absensi - ' + staffName;
                }

                if (attendanceDetailFormattedAddressElement) {
                    attendanceDetailFormattedAddressElement.textContent = formattedAddress;
                }
                if (attendanceDetailVillageElement) {
                    attendanceDetailVillageElement.textContent = villageName;
                }
                if (attendanceDetailDistrictElement) {
                    attendanceDetailDistrictElement.textContent = districtName;
                }
                if (attendanceDetailRegencyElement) {
                    attendanceDetailRegencyElement.textContent = regencyName;
                }
                if (attendanceDetailProvinceElement) {
                    attendanceDetailProvinceElement.textContent = provinceName;
                }
                if (attendanceDetailPostalCodeElement) {
                    attendanceDetailPostalCodeElement.textContent = postalCode;
                }
                bootstrap.Modal.getOrCreateInstance(attendanceDetailModalElement).show();
                renderAttendanceDetailMap(rowData || {});
            }

            window.onClickAttendanceDetail = function (buttonElement) {
                if (!buttonElement || !attendanceTable) {
                    return;
                }

                var rowData = attendanceTable.row($(buttonElement).closest('tr')).data();
                showAttendanceDetail(rowData || {});
            };

            function isValidCoordinate(latitudeValue, longitudeValue) {
                if (!Number.isFinite(latitudeValue) || !Number.isFinite(longitudeValue)) {
                    return false;
                }

                if (latitudeValue < -90 || latitudeValue > 90 || longitudeValue < -180 || longitudeValue > 180) {
                    return false;
                }

                return !(Math.abs(latitudeValue) < 0.000001 && Math.abs(longitudeValue) < 0.000001);
            }

            function setAttendanceDetailMapState(showMap, emptyText) {
                if (attendanceDetailMapCanvasElement) {
                    attendanceDetailMapCanvasElement.style.display = showMap ? 'block' : 'none';
                }

                if (attendanceDetailMapEmptyElement) {
                    attendanceDetailMapEmptyElement.style.display = showMap ? 'none' : 'block';
                    attendanceDetailMapEmptyElement.textContent = emptyText || 'Lokasi absen tidak tersedia.';
                }
            }

            function clearAttendanceDetailMapElements() {
                if (attendanceDetailUserMarker) {
                    attendanceDetailUserMarker.setMap(null);
                    attendanceDetailUserMarker = null;
                }

                if (attendanceDetailOfficeMarker) {
                    attendanceDetailOfficeMarker.setMap(null);
                    attendanceDetailOfficeMarker = null;
                }

                if (attendanceDetailLine) {
                    attendanceDetailLine.setMap(null);
                    attendanceDetailLine = null;
                }
            }

            function renderAttendanceDetailMap(rowData) {
                var latitudeValue = Number(rowData && rowData.check_in_latitude);
                var longitudeValue = Number(rowData && rowData.check_in_longitude);
                var hasValidAttendanceCoordinate = isValidCoordinate(latitudeValue, longitudeValue);
                var officeLatitudeValue = Number(officeLocation && officeLocation.latitude);
                var officeLongitudeValue = Number(officeLocation && officeLocation.longitude);
                var hasValidOfficeCoordinate = isValidCoordinate(officeLatitudeValue, officeLongitudeValue);
                var distanceMetersValue = Number(rowData && rowData.distance_meters);
                var distanceLabel = Number.isFinite(distanceMetersValue) ? distanceMetersValue.toFixed(2) + ' m' : '-';
                var radiusResultValue = rowData && rowData.radius_result ? String(rowData.radius_result) : '-';

                if (attendanceDetailLocationInfoElement) {
                    if (hasValidAttendanceCoordinate) {
                        attendanceDetailLocationInfoElement.textContent = 'Lokasi absen: '
                            + latitudeValue.toFixed(6) + ', ' + longitudeValue.toFixed(6)
                            + ' | Jarak: ' + distanceLabel
                            + ' | Radius: ' + radiusResultValue;
                    } else {
                        attendanceDetailLocationInfoElement.textContent = 'Lokasi absen: -';
                    }
                }

                if (!hasValidAttendanceCoordinate) {
                    setAttendanceDetailMapState(false, 'Lokasi absen tidak tersedia.');
                    clearAttendanceDetailMapElements();
                    return;
                }

                setAttendanceDetailMapState(true, '');

                if (!attendanceDetailMapCanvasElement) {
                    return;
                }

                loadGoogleMapsApi()
                    .then(function () {
                        var attendancePosition = {
                            lat: latitudeValue,
                            lng: longitudeValue
                        };

                        if (!attendanceDetailMapInstance) {
                            attendanceDetailMapInstance = new window.google.maps.Map(attendanceDetailMapCanvasElement, {
                                center: attendancePosition,
                                zoom: 16,
                                mapTypeControl: false,
                                streetViewControl: false,
                                fullscreenControl: false
                            });
                        }

                        clearAttendanceDetailMapElements();

                        attendanceDetailUserMarker = new window.google.maps.Marker({
                            position: attendancePosition,
                            map: attendanceDetailMapInstance,
                            title: 'Titik Absen',
                            icon: {
                                path: window.google.maps.SymbolPath.CIRCLE,
                                scale: 7,
                                fillColor: '#dc2626',
                                fillOpacity: 1,
                                strokeColor: '#ffffff',
                                strokeWeight: 2
                            }
                        });

                        if (hasValidOfficeCoordinate) {
                            var officePosition = {
                                lat: officeLatitudeValue,
                                lng: officeLongitudeValue
                            };

                            attendanceDetailOfficeMarker = new window.google.maps.Marker({
                                position: officePosition,
                                map: attendanceDetailMapInstance,
                                title: 'Titik Kantor'
                            });

                            attendanceDetailLine = new window.google.maps.Polyline({
                                path: [officePosition, attendancePosition],
                                geodesic: true,
                                strokeColor: '#2563eb',
                                strokeOpacity: 0.85,
                                strokeWeight: 2,
                                map: attendanceDetailMapInstance
                            });

                            var bounds = new window.google.maps.LatLngBounds();
                            bounds.extend(attendancePosition);
                            bounds.extend(officePosition);
                            attendanceDetailMapInstance.fitBounds(bounds);
                        } else {
                            attendanceDetailMapInstance.setCenter(attendancePosition);
                            attendanceDetailMapInstance.setZoom(16);
                        }

                        window.google.maps.event.trigger(attendanceDetailMapInstance, 'resize');
                    })
                    .catch(function () {
                        setAttendanceDetailMapState(false, 'Gagal memuat peta lokasi absen.');
                    });
            }

            setAttendanceDetailMapState(false, 'Lokasi absen tidak tersedia.');

            renderAttendanceDateTime();
            setInterval(renderAttendanceDateTime, 1000);
            renderOnsiteRunningTime();
            setInterval(renderOnsiteRunningTime, 1000);
            renderSubmitButtons();
            tableLogs();

            $('.absensi-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            eachModalContext(function (context) {
                if (!context.modalElement) {
                    return;
                }

                context.modalElement.addEventListener('shown.bs.modal', function () {
                    resetVerificationUi(context);
                    if (!browserPublicIp) {
                        eachModalContext(function (item) {
                            setOnsiteIpLoadingState(item);
                        });
                    }

                    resolveBrowserPublicIpAndRefresh();
                    loadGoogleMapsApi()
                        .then(function () {
                            initializeOnsiteMap(context);
                            if (context.mapInstance && context.officeRadiusCircle) {
                                window.google.maps.event.trigger(context.mapInstance, 'resize');
                                context.mapInstance.fitBounds(context.officeRadiusCircle.getBounds());
                            }
                        })
                        .catch(function (error) {
                            setOnsiteStatus(context, error.message);
                            setVerificationMessage(context, error.message, 'error');
                        });

                    renderSubmitButtons();
                });
            });

            if (attendanceDetailModalElement) {
                attendanceDetailModalElement.addEventListener('hidden.bs.modal', function () {
                    clearAttendanceDetailMapElements();
                    if (attendanceDetailModalLabel) {
                        attendanceDetailModalLabel.textContent = 'Detail Absensi';
                    }
                    if (attendanceDetailLocationInfoElement) {
                        attendanceDetailLocationInfoElement.textContent = 'Lokasi absen: -';
                    }
                    setAttendanceDetailMapState(false, 'Lokasi absen tidak tersedia.');
                });
            }

            if (clockInVerifyButton) {
                clockInVerifyButton.addEventListener('click', function () {
                    checkOnsiteLocation(modalContext.clockIn);
                });
            }

            if (clockOutVerifyButton) {
                clockOutVerifyButton.addEventListener('click', function () {
                    checkOnsiteLocation(modalContext.clockOut);
                });
            }

            if (clockInSubmitButton) {
                clockInSubmitButton.addEventListener('click', function () {
                    submitOnsiteAttendance('clock_in', modalContext.clockIn);
                });
            }

            if (clockOutSubmitButton) {
                clockOutSubmitButton.addEventListener('click', function () {
                    submitOnsiteAttendance('clock_out', modalContext.clockOut);
                });
            }

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
                    if (attendanceTable) {
                        attendanceTable.ajax.reload();
                    }
                });
            }

        });
    </script>
@endsection
