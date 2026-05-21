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

        .onsite-map-container {
            border: 1px solid #e6eaf2;
            border-radius: 0.75rem;
            overflow: hidden;
            background: #fff;
        }

        #onsiteRunningTime {
            padding: 0.85rem 1rem;
            font-size: 1.05rem;
            letter-spacing: 0.02em;
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        #onsiteRunningTime.time-green {
            color: #166534;
            background: #dcfce7;
        }

        #onsiteRunningTime.time-yellow {
            color: #854d0e;
            background: #fef9c3;
        }

        #onsiteRunningTime.time-red {
            color: #b91c1c;
            background: #fee2e2;
        }

        #onsiteRunningTime.time-gray {
            color: #374151;
            background: #e5e7eb;
        }

        .onsite-map-canvas {
            width: 100%;
            height: 360px;
            background: #f8fafc;
        }

        .onsite-map-meta {
            border-top: 1px solid #e6eaf2;
            padding: 1.2rem 1.2rem;
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
                data-bs-target="#attendanceActionModal"
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
                        <span class="fs-20 text-black">1 KM</span>
                    </div>
                    <div class="text-center">
                        <p class="fs-14 mb-2">Time</p>
                        <span class="fs-20 text-black">08:34:53</span>
                    </div>
                    <div class="text-center">
                        <p class="fs-14 mb-2">Clock In</p>
                        <span class="fs-20 text-black">08:00</span>
                    </div>
                </div>
            </div>
            <a class="btn light btn-success m-3 mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#clockIn">Clock In</a>
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
            <a class="btn light btn-danger m-3 mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#clockOut">Clock Out</a>
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
<div class="modal fade" id="attendanceActionModal" tabindex="-1" aria-labelledby="attendanceActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceActionModalLabel">Absen {{ now('Asia/Jakarta')->format('d/m/Y') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onsite-map-container">
                    <div class="px-3 py-2 border-bottom text-center fw-semibold" id="onsiteRunningTime">--:--:--</div>
                    <div id="onsiteMapCanvas" class="onsite-map-canvas"></div>
                    <div class="onsite-map-meta">
                        <h6 class="mb-3">Validasi Lokasi Onsite</h6>
                        <div class="small text-muted mb-2">
                            <strong>Status:</strong> <span id="onsiteStatusText">Menunggu cek lokasi</span>
                        </div>
                        <div class="small mb-3">
                            <strong>IP:</strong>
                            @if (!empty($publicIp))
                                <span id="onsiteIpText" class="{{ ($isIpPrefixMatch ?? false) ? 'text-success fw-semibold' : 'text-danger fw-semibold' }}">
                                    {{ $publicIp }}
                                </span>
                                <span id="onsiteIpBadge" class="badge ms-2 {{ ($isIpPrefixMatch ?? false) ? 'bg-success' : 'bg-danger' }}">
                                    {{ ($isIpPrefixMatch ?? false) ? 'Valid' : 'Tidak Valid' }}
                                </span>
                            @else
                                <span id="onsiteIpText" class="text-muted">
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Memuat...
                                </span>
                                <span id="onsiteIpBadge" class="badge ms-2 d-none"></span>
                            @endif
                        </div>
                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="checkOnsiteLocationBtn">Mulai Verifikasi</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm w-100" id="submitOnsiteAttendanceBtn">Masuk</button>
            </div>
        </div>
    </div>
</div>

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
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <p class="form-label mb-3 text-center">Wed, 20 May 2026 - 
                                <span class="text-success fw-semibold">08:00:10</span>
                            </p>
                            <p class="form-label text-muted mb-3">
                                Grab your coffee and let's get things done. Clock in when you're ready to kick off your shift!
                            </p>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Current Location</label>
                                <iframe class="border-0 rounded" height="250"  width="100%" id="gmap_canvas" src="https://maps.google.com/maps?q=&t=&z=13&ie=UTF8&iwloc=&output=embed"></iframe>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Status</label>
                                <p class="fs-13 mb-0">Ensure your device location is enabled and you are within the authorized work area.</p>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">IP Address</label>
                                <p>
                                    <span class="fs-13 mb-0 text-success">182.8.226.88</span> | <span class="fs-13 mb-0 text-danger">182.8.226.88</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
                <a class="btn light btn-success mb-2 btn-lg w-100" data-bs-toggle="modal" data-bs-target="#clockIn">Clock In</a>
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
                <form>
                    <div class="row">
                        <div class="col-xl-12">
                            <p class="form-label mb-3 text-center">Wed, 20 May 2026 - 
                                <span class="fw-semibold">17:00:10</span>
                            </p>
                            <p class="form-label text-muted mb-3">
                                Please make sure your daily tasks are wrapped up before clocking out. Thank you for your hard work, and enjoy the rest of your day!
                            </p>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Current Location</label>
                                <iframe class="border-0 rounded" height="250"  width="100%" id="gmap_canvas" src="https://maps.google.com/maps?q=&t=&z=13&ie=UTF8&iwloc=&output=embed"></iframe>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Status</label>
                                <p class="fs-13 mb-0">Ensure your device location is enabled and you are within the authorized work area.</p>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">IP Address</label>
                                <p>
                                    <span class="fs-13 mb-0 text-success">182.8.226.88</span> | <span class="fs-13 mb-0 text-danger">182.8.226.88</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
                <a class="btn light btn-danger mb-2 btn-lg w-100" data-bs-toggle="modal" data-bs-target="#clockOut">See You Tomorrow</a>
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
            var attendanceCompanyFilter = document.getElementById('attendanceCompanyFilter');
            var attendanceMonthFilter = document.getElementById('attendanceMonthFilter');
            var attendanceYearFilter = document.getElementById('attendanceYearFilter');
            var googleMapsApiKey = @json(config('services.google_maps.api_key'));
            var officeLocation = @json($officeLocation);
            var attendanceModalElement = document.getElementById('attendanceActionModal');
            var attendanceDetailModalElement = document.getElementById('attendanceDetailModal');
            var attendanceDetailModalLabel = document.getElementById('attendanceDetailModalLabel');
            var checkOnsiteLocationButton = document.getElementById('checkOnsiteLocationBtn');
            var submitOnsiteAttendanceButton = document.getElementById('submitOnsiteAttendanceBtn');
            var onsiteStatusText = document.getElementById('onsiteStatusText');
            var onsiteIpText = document.getElementById('onsiteIpText');
            var onsiteIpBadge = document.getElementById('onsiteIpBadge');
            var onsiteRunningTimeElement = document.getElementById('onsiteRunningTime');
            var attendanceDetailFormattedAddressElement = document.getElementById('attendanceDetailFormattedAddress');
            var attendanceDetailVillageElement = document.getElementById('attendanceDetailVillage');
            var attendanceDetailDistrictElement = document.getElementById('attendanceDetailDistrict');
            var attendanceDetailRegencyElement = document.getElementById('attendanceDetailRegency');
            var attendanceDetailProvinceElement = document.getElementById('attendanceDetailProvince');
            var attendanceDetailPostalCodeElement = document.getElementById('attendanceDetailPostalCode');
            var attendanceDetailLocationInfoElement = document.getElementById('attendanceDetailLocationInfo');
            var attendanceDetailMapCanvasElement = document.getElementById('attendanceDetailMapCanvas');
            var attendanceDetailMapEmptyElement = document.getElementById('attendanceDetailMapEmpty');
            var onsiteMapInstance = null;
            var officeMarker = null;
            var officeRadiusCircle = null;
            var userMarker = null;
            var userToOfficeLine = null;
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
                hasCheckedOutToday: @json($hasCheckedOutToday ?? false),
                hasVerifiedOnsite: false,
                hasVerifiedTelegram: false
            };
            var latestUserCoordinates = null;
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

            function setOnsiteIpIndicator(ipAddress, isValidIpPrefix) {
                if (!onsiteIpText) {
                    return;
                }

                if (!ipAddress || ipAddress === '-') {
                    setOnsiteIpUnavailableState();
                    return;
                }

                onsiteIpText.textContent = ipAddress;
                onsiteIpText.classList.remove('text-success', 'text-danger', 'text-muted', 'fw-semibold');
                if (onsiteIpBadge) {
                    onsiteIpBadge.classList.remove('d-none');
                    onsiteIpBadge.classList.remove('bg-success', 'bg-danger', 'bg-secondary');
                }

                if (isValidIpPrefix) {
                    onsiteIpText.classList.add('text-success', 'fw-semibold');
                    if (onsiteIpBadge) {
                        onsiteIpBadge.classList.add('bg-success');
                        onsiteIpBadge.textContent = 'Valid';
                    }
                    return;
                }

                onsiteIpText.classList.add('text-danger', 'fw-semibold');
                if (onsiteIpBadge) {
                    onsiteIpBadge.classList.add('bg-danger');
                    onsiteIpBadge.textContent = 'Tidak Valid';
                }
            }

            function setOnsiteIpLoadingState() {
                if (!onsiteIpText) {
                    return;
                }

                onsiteIpText.classList.remove('text-success', 'text-danger', 'fw-semibold');
                onsiteIpText.classList.add('text-muted');
                onsiteIpText.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Memuat...';
                if (onsiteIpBadge) {
                    onsiteIpBadge.classList.add('d-none');
                    onsiteIpBadge.classList.remove('bg-success', 'bg-danger', 'bg-secondary');
                    onsiteIpBadge.textContent = '';
                }
            }

            function setOnsiteIpUnavailableState() {
                if (!onsiteIpText) {
                    return;
                }

                onsiteIpText.textContent = 'Tidak tersedia';
                onsiteIpText.classList.remove('text-success', 'text-danger', 'fw-semibold');
                onsiteIpText.classList.add('text-muted');
                if (onsiteIpBadge) {
                    onsiteIpBadge.classList.remove('d-none');
                    onsiteIpBadge.classList.remove('bg-success', 'bg-danger');
                    onsiteIpBadge.classList.add('bg-secondary');
                    onsiteIpBadge.textContent = 'Tidak tersedia';
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
                    setOnsiteIpIndicator(ipAddress, isValidIpPrefix);
                }).fail(function () {
                    setOnsiteIpUnavailableState();
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

            function renderSubmitAttendanceButton() {
                if (!submitOnsiteAttendanceButton) {
                    return;
                }

                submitOnsiteAttendanceButton.classList.remove('btn-primary', 'btn-success', 'btn-danger', 'btn-secondary');
                submitOnsiteAttendanceButton.disabled = false;

                if (attendanceState.hasCheckedOutToday) {
                    submitOnsiteAttendanceButton.classList.add('btn-secondary');
                    submitOnsiteAttendanceButton.textContent = 'Sudah Pulang';
                    submitOnsiteAttendanceButton.disabled = true;
                    return;
                }

                if (attendanceState.hasCheckedInToday) {
                    submitOnsiteAttendanceButton.classList.add('btn-danger');
                    submitOnsiteAttendanceButton.textContent = 'Keluar';
                    submitOnsiteAttendanceButton.disabled = !(attendanceState.hasVerifiedOnsite && attendanceState.hasVerifiedTelegram);
                    return;
                }

                submitOnsiteAttendanceButton.classList.add('btn-success');
                submitOnsiteAttendanceButton.textContent = 'Masuk';
                submitOnsiteAttendanceButton.disabled = !(attendanceState.hasVerifiedOnsite && attendanceState.hasVerifiedTelegram);
            }

            function setOnsiteStatus(text) {
                if (onsiteStatusText) {
                    onsiteStatusText.textContent = text;
                    onsiteStatusText.classList.remove('text-success', 'text-danger', 'text-warning', 'text-muted');

                    if (text === 'Di dalam radius kantor') {
                        onsiteStatusText.classList.add('text-success');
                        return;
                    }

                    if (text === 'Di luar radius kantor') {
                        onsiteStatusText.classList.add('text-danger');
                        return;
                    }

                    if (text === 'Harap verifikasi terlebih dahulu sebelum absen') {
                        onsiteStatusText.classList.add('text-warning');
                        return;
                    }

                    onsiteStatusText.classList.add('text-muted');
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

            function initializeOnsiteMap() {
                if (!officeLocation || officeLocation.latitude === null || officeLocation.longitude === null) {
                    setOnsiteStatus('Koordinat kantor belum tersedia di database perusahaan');
                    return;
                }

                if (onsiteMapInstance) {
                    return;
                }

                var mapElement = document.getElementById('onsiteMapCanvas');
                if (!mapElement || !window.google || !window.google.maps) {
                    return;
                }

                var officePosition = {
                    lat: Number(officeLocation.latitude),
                    lng: Number(officeLocation.longitude)
                };
                var officeRadius = Number(officeLocation.radius_meters || 100);

                onsiteMapInstance = new window.google.maps.Map(mapElement, {
                    center: officePosition,
                    zoom: 17,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: false
                });

                officeMarker = new window.google.maps.Marker({
                    position: officePosition,
                    map: onsiteMapInstance,
                    title: officeLocation.name || 'Office'
                });

                officeRadiusCircle = new window.google.maps.Circle({
                    map: onsiteMapInstance,
                    center: officePosition,
                    radius: officeRadius,
                    strokeColor: '#2563eb',
                    strokeOpacity: 0.85,
                    strokeWeight: 2,
                    fillColor: '#60a5fa',
                    fillOpacity: 0.14
                });

                attendanceState.hasVerifiedOnsite = false;
                attendanceState.hasVerifiedTelegram = false;
                renderSubmitAttendanceButton();
                setOnsiteStatus('Harap verifikasi terlebih dahulu sebelum absen');
            }

            function updateUserLocationOnMap(position) {
                if (!onsiteMapInstance || !officeLocation) {
                    return;
                }

                latestUserCoordinates = {
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
                attendanceState.hasVerifiedOnsite = true;

                if (!userMarker) {
                    userMarker = new window.google.maps.Marker({
                        position: userPosition,
                        map: onsiteMapInstance,
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
                    userMarker.setPosition(userPosition);
                }

                if (userToOfficeLine) {
                    userToOfficeLine.setMap(null);
                }

                userToOfficeLine = new window.google.maps.Polyline({
                    path: [officePosition, userPosition],
                    geodesic: true,
                    strokeColor: '#dc2626',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    map: onsiteMapInstance
                });

                onsiteMapInstance.panTo(userPosition);

                setOnsiteStatus(inRadius ? 'Di dalam radius kantor' : 'Di luar radius kantor');
                renderSubmitAttendanceButton();
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
                            attendanceState.hasVerifiedTelegram = true;
                            resolve(response);
                            return;
                        }

                        attendanceState.hasVerifiedTelegram = false;
                        reject(new Error(response && response.message ? response.message : 'Verifikasi Telegram gagal.'));
                    }).fail(function (xhr) {
                        attendanceState.hasVerifiedTelegram = false;
                        var errorMessage = 'Verifikasi Telegram gagal.';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        reject(new Error(errorMessage));
                    });
                });
            }

            function checkOnsiteLocation() {
                if (!navigator.geolocation) {
                    attendanceState.hasVerifiedOnsite = false;
                    attendanceState.hasVerifiedTelegram = false;
                    renderSubmitAttendanceButton();
                    setOnsiteStatus('Browser tidak mendukung geolocation');
                    return;
                }

                if (!window.isSecureContext) {
                    attendanceState.hasVerifiedOnsite = false;
                    attendanceState.hasVerifiedTelegram = false;
                    renderSubmitAttendanceButton();
                    setOnsiteStatus('Geolocation hanya jalan di HTTPS atau localhost');
                    return;
                }

                attendanceState.hasVerifiedOnsite = false;
                attendanceState.hasVerifiedTelegram = false;
                renderSubmitAttendanceButton();
                setOnsiteStatus('Memverifikasi Telegram dan lokasi...');

                verifyTelegramUsernameSync().then(function () {
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            updateUserLocationOnMap(position);
                            setOnsiteStatus('Verifikasi lokasi dan Telegram berhasil.');
                        },
                        function (error) {
                            attendanceState.hasVerifiedOnsite = false;
                            renderSubmitAttendanceButton();

                            if (!error || typeof error.code === 'undefined') {
                                setOnsiteStatus('Gagal mendapatkan lokasi');
                                return;
                            }

                            if (error.code === 1) {
                                setOnsiteStatus('Izin lokasi ditolak. Izinkan lokasi di browser.');
                                return;
                            }

                            if (error.code === 2) {
                                setOnsiteStatus('Lokasi tidak tersedia. Aktifkan GPS/lokasi perangkat.');
                                return;
                            }

                            if (error.code === 3) {
                                setOnsiteStatus('Timeout saat mengambil lokasi. Coba lagi.');
                                return;
                            }

                            setOnsiteStatus('Gagal mendapatkan lokasi');
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0
                        }
                    );
                }).catch(function (error) {
                    attendanceState.hasVerifiedTelegram = false;
                    attendanceState.hasVerifiedOnsite = false;
                    renderSubmitAttendanceButton();
                    setOnsiteStatus(error && error.message ? error.message : 'Verifikasi Telegram gagal.');
                });
            }

            function resolveCurrentCoordinatesForAttendance() {
                return new Promise(function (resolve) {
                    if (!navigator.geolocation || !window.isSecureContext) {
                        resolve(latestUserCoordinates);
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            latestUserCoordinates = {
                                latitude: Number(position.coords.latitude),
                                longitude: Number(position.coords.longitude)
                            };
                            resolve(latestUserCoordinates);
                        },
                        function () {
                            resolve(latestUserCoordinates);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 7000,
                            maximumAge: 0
                        }
                    );
                });
            }

            function submitOnsiteAttendance() {
                if (!submitOnsiteAttendanceButton) {
                    return;
                }

                if (attendanceState.hasCheckedOutToday) {
                    setOnsiteStatus('Kamu sudah absen pulang hari ini');
                    return;
                }

                if (!(attendanceState.hasVerifiedOnsite && attendanceState.hasVerifiedTelegram)) {
                    setOnsiteStatus('Harap verifikasi terlebih dahulu sebelum absen');
                    renderSubmitAttendanceButton();
                    return;
                }

                submitOnsiteAttendanceButton.disabled = true;
                setOnsiteStatus('Memproses submit absen...');

                var isCheckInAction = !attendanceState.hasCheckedInToday;
                var requestMethod = attendanceState.hasCheckedInToday ? 'PATCH' : 'POST';
                var requestUrl = attendanceState.hasCheckedInToday && attendanceState.todayAttendanceId
                    ? getUpdateAttendanceUrl(attendanceState.todayAttendanceId)
                    : storeAttendanceUrl;

                resolveCurrentCoordinatesForAttendance().then(function (coordinates) {
                    var payload = {
                        client_ip: browserPublicIp || (onsiteIpText ? onsiteIpText.textContent.trim() : null)
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
                        setOnsiteStatus(response && response.message ? response.message : 'Absen berhasil disimpan');
                        if (isCheckInAction) {
                            attendanceState.hasCheckedInToday = true;
                            if (response && response.attendance_id) {
                                attendanceState.todayAttendanceId = response.attendance_id;
                            }
                        } else {
                            attendanceState.hasCheckedOutToday = true;
                        }
                        renderSubmitAttendanceButton();
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

                        setOnsiteStatus(errorMessage);
                    }).always(function () {
                        renderSubmitAttendanceButton();
                    });
                });
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

            function renderOnsiteRunningTime() {
                if (!onsiteRunningTimeElement) {
                    return;
                }

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

                onsiteRunningTimeElement.textContent = formattedTime;
                onsiteRunningTimeElement.classList.remove('time-green', 'time-yellow', 'time-red', 'time-gray');

                if (totalMinutes < officeStartTotalMinutes) {
                    onsiteRunningTimeElement.classList.add('time-green');
                } else if (totalMinutes <= lateThresholdTotalMinutes) {
                    onsiteRunningTimeElement.classList.add('time-yellow');
                } else if (totalMinutes > officeEndTotalMinutes) {
                    onsiteRunningTimeElement.classList.add('time-gray');
                } else {
                    onsiteRunningTimeElement.classList.add('time-red');
                }
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
            renderSubmitAttendanceButton();
            tableLogs();

            $('.absensi-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            if (attendanceModalElement) {
                attendanceModalElement.addEventListener('shown.bs.modal', function () {
                    attendanceState.hasVerifiedOnsite = false;
                    attendanceState.hasVerifiedTelegram = false;
                    renderSubmitAttendanceButton();
                    setOnsiteStatus('Harap verifikasi terlebih dahulu sebelum absen');

                    if (!browserPublicIp) {
                        setOnsiteIpLoadingState();
                    }

                    resolveBrowserPublicIpAndRefresh();
                    loadGoogleMapsApi()
                        .then(function () {
                            initializeOnsiteMap();
                            if (onsiteMapInstance && officeRadiusCircle) {
                                window.google.maps.event.trigger(onsiteMapInstance, 'resize');
                                onsiteMapInstance.fitBounds(officeRadiusCircle.getBounds());
                            }
                        })
                        .catch(function (error) {
                            setOnsiteStatus(error.message);
                        });
                });
            }

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

            if (checkOnsiteLocationButton) {
                checkOnsiteLocationButton.addEventListener('click', function () {
                    checkOnsiteLocation();
                });
            }

            if (submitOnsiteAttendanceButton) {
                submitOnsiteAttendanceButton.addEventListener('click', function () {
                    submitOnsiteAttendance();
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
