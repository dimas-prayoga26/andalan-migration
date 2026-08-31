@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
        $sweetAlertCssPath = public_path('assets/vendor/sweetalert2/sweetalert2.min.css');
        $sweetAlertCssVersion = file_exists($sweetAlertCssPath) ? filemtime($sweetAlertCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}?v={{ $sweetAlertCssVersion }}">
    <style>
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

        .attendance-clock-out-too-early.disabled {
            cursor: not-allowed;
            pointer-events: auto;
        }
    </style>
@endsection

@section('navbarTitle', 'Dashboard')

@section('content')

    @php
        $showDashboardMenuCarousel = false;
        $showDashboardInactiveShortcuts = false;
        $dashboardUser = auth()->user();
        $canViewDashboardEmployeeShortcut = false;
        $isClockOutBlocked = ($hasCheckedOutToday ?? false)
            || ($hasEarlyDepartureExceptionToday ?? false)
            || !($canClockOutNow ?? true);
        $isClockOutTooEarly = !($canClockOutNow ?? true);

        if ($dashboardUser instanceof \App\Models\User) {
            $canViewDashboardEmployeeShortcut = $dashboardUser->hasAnyPositionPermission([
                'view-authorization',
                'view-employee-database',
            ]);
        }
    @endphp

    <div class="row">

        <div class="col-xl-12 col-xxl-12">
            <div class="row">

                @if ($showDashboardMenuCarousel)
                <div class="dashboard-menu-carousel owl-carousel">
                    <!-- Start - Laporan Pekerjaan -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-success rounded-circle border-0">
                                        <i class="fa-solid fa-clipboard-list fs-1 text-success"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Laporan Pekerjaan</p>
                                        <span class="title text-black fs-20 fw-semibold">128 Laporan</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar bg-success position-absolute rounded bootom-0" style="width: 100%; height:5px;" aria-label="Progess-success" role="progressbar">
                                            <span class="sr-only">76% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-success"></div>
                        </div>
                    </div>
                    <!-- End - Laporan Pekerjaan -->

                    <!-- Start - Agenda Kegiatan -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-secondary rounded-circle border-0">
                                        <i class="fa-solid fa-calendar-days fs-1 text-secondary"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Agenda Kegiatan</p>
                                        <span class="title text-black fs-20 fw-semibold">24 Agenda</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-secondary" style="width: 100%; height:5px;" aria-label="Progess-secondary" role="progressbar">
                                            <span class="sr-only">58% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-secondary"></div>
                        </div>
                    </div>
                    <!-- End - Agenda Kegiatan -->

                    <!-- Start - Attendance Data -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-danger rounded-circle border-0">
                                        <i class="fa-solid fa-fingerprint fs-1 text-danger"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Attendance Data</p>
                                        <span class="title text-black fs-20 fw-semibold">97% Kehadiran</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-danger" style="width: 100%; height:5px;" aria-label="Progess-danger"  role="progressbar">
                                            <span class="sr-only">97% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-danger"></div>
                        </div>
                    </div>
                    <!-- End - Attendance Data -->

                    <!-- Start - Data Pelamar -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-warning rounded-circle border-0">
                                        <i class="fa-solid fa-id-card fs-1 text-warning"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Data Pelamar</p>
                                        <span class="title text-black fs-20 fw-semibold">36 Pelamar</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-warning" style="width: 100%; height:5px;" role="progressbar">
                                            <span class="sr-only">64% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-warning"></div>
                        </div>
                    </div>
                    <!-- End - Data Pelamar -->

                    <!-- Start - Data Karyawan -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-primary rounded-circle border-0">
                                        <i class="fa-solid fa-users fs-1 text-primary"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Data Karyawan</p>
                                        <span class="title text-black fs-20 fw-semibold">245 Karyawan</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-primary" style="width: 100%; height:5px;" role="progressbar">
                                            <span class="sr-only">70% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-primary"></div>
                        </div>
                    </div>
                    <!-- End - Data Karyawan -->

                    <!-- Start - Blog Management -->
                    <div class="dashboard-activity-item">
                        <div class="card overflow-hidden avtivity-card">
                            <div class="card-body">
                                <div class="d-flex gap-md-4 gap-3 align-items-center">
                                    <span class="avatar avatar-lg avatar-info rounded-circle border-0">
                                        <i class="fa-solid fa-newspaper fs-1 text-info"></i>
                                    </span>
                                    <div>
                                        <p class="fs-12 mb-2">Blog Management</p>
                                        <span class="title text-black fs-20 fw-semibold">18 Artikel</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                                        <div class="progress-bar rounded bg-info" style="width: 100%; height:5px;" role="progressbar">
                                            <span class="sr-only">54% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="effect bg-info"></div>
                        </div>
                    </div>
                    <!-- End - Blog Management -->
                </div>
                @endif

            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <div class="row g-3">
                        <div class="col-xl-6 {{ ($hasCheckedInToday ?? false) ? 'd-none d-md-block' : '' }}">
                            <div class="card">
                                <div class="card-header border-0 pb-3">
                                    <div>
                                        <h4 class="card-title">Attendance Confirmation</h4>
                                        <p class="fs-13 mb-0">Ensure your device location is enabled and you are within the authorized work area.</p>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="d-flex gap-3 justify-content-between flex-wrap p-4 pb-2">
                                        <div class="text-center">
                                            <p class="fs-14 mb-2">Date &amp; Time</p>
                                            <span class="fs-20 text-success" id="dashboardAttendanceSummaryTimeValue">{{ now('Asia/Jakarta')->format('d M Y | H:i:s') }}</span>
                                        </div>
                                        <div class="text-center">
                                            <p class="fs-14 mb-2">Clock In</p>
                                            <span class="fs-20 text-black">{{ $todayAttendance?->clock_in?->format('H:i') ?? '--:--' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <a
                                    id="dashboardClockInCardButton"
                                    class="btn light btn-success m-3 mb-2 btn-lg {{ ($hasCheckedInToday ?? false) ? 'disabled' : '' }}"
                                    @if (!($hasCheckedInToday ?? false))
                                        data-bs-toggle="modal"
                                        data-bs-target="#dashboardClockIn"
                                    @endif
                                    @if (($hasCheckedInToday ?? false))
                                        aria-disabled="true"
                                        tabindex="-1"
                                    @endif
                                >Clock In</a>
                                <div class="mb-3"></div>
                            </div>
                        </div>
                        <div class="col-xl-6 {{ !($hasCheckedInToday ?? false) ? 'd-none d-md-block' : '' }}">
                            <div class="card">
                                <div class="card-header border-0 pb-3">
                                    <div>
                                        <h4 class="card-title">End of Shift</h4>
                                        <p class="fs-13 mb-0">Ensure all your daily tasks and status reports have been updated before clocking out.</p>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="d-flex gap-3 justify-content-between flex-wrap p-4 pb-2">
                                        <div class="text-center">
                                            <p class="fs-14 mb-2">Date &amp; Time</p>
                                            <span class="fs-20 text-black" id="dashboardAttendanceClockOutSummaryTimeValue">{{ now('Asia/Jakarta')->format('d M Y | H:i:s') }}</span>
                                        </div>
                                        <div class="text-center">
                                            <p class="fs-14 mb-2">Clock Out</p>
                                            <span class="fs-20 text-black">{{ $todayAttendance?->clock_out?->format('H:i') ?? '--:--' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <a
                                    id="dashboardClockOutCardButton"
                                    class="btn light btn-danger m-3 mb-2 btn-lg {{ $isClockOutBlocked ? 'disabled' : '' }} {{ $isClockOutTooEarly ? 'attendance-clock-out-too-early' : '' }}"
                                    @if (! $isClockOutBlocked)
                                        data-bs-toggle="modal"
                                        data-bs-target="#dashboardClockOut"
                                    @endif
                                    @if ($isClockOutTooEarly)
                                        data-clock-out-too-early="true"
                                        data-clock-out-unavailable-message="{{ $clockOutUnavailableMessage ?? 'Clock out belum tersedia.' }}"
                                    @endif
                                    @if ($isClockOutBlocked)
                                        aria-disabled="true"
                                        tabindex="-1"
                                        title="{{ $clockOutUnavailableMessage ?? 'Clock out belum tersedia.' }}"
                                    @endif
                                >Clock Out</a>
                                <div class="mb-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12 mb-3">
                    <h4 class="mb-3">Menu</h4>
                    <div class="dashboard-shortcut-menu">
                        <a href="{{ route('attendance.today') }}" class="dashboard-shortcut-item dashboard-shortcut-item--attendance shortcut-card" aria-label="Attendance">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bx-calendar-check.svg.svg') }}" alt="Attendance icon" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Attendance</span>
                        </a>
                        <a href="{{ route('activity-schadule') }}" class="dashboard-shortcut-item dashboard-shortcut-item--agenda shortcut-card" aria-label="Agenda">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bx-calendar.svg.svg') }}" alt="Icon Agenda" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Agenda</span>
                        </a>
                        @if ($showDashboardInactiveShortcuts)
                        <a href="javascript:void(0)" class="dashboard-shortcut-item dashboard-shortcut-item--blog shortcut-card" aria-label="Blog">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bxs-detail.svg.svg') }}" alt="Icon Blog" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Blog</span>
                        </a>
                        @endif
                        <a href="{{ route('project_management.projects') }}" class="dashboard-shortcut-item dashboard-shortcut-item--project shortcut-card" aria-label="Project">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bx-task.svg.svg') }}" alt="Icon Project" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Project</span>
                        </a>
                        @if ($showDashboardInactiveShortcuts)
                        <a href="javascript:void(0)" class="dashboard-shortcut-item dashboard-shortcut-item--meeting shortcut-card" aria-label="Meeting">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bxl-zoom.svg.svg') }}" alt="Icon Meeting" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Meeting</span>
                        </a>
                        <a href="javascript:void(0)" class="dashboard-shortcut-item dashboard-shortcut-item--finance shortcut-card" aria-label="Finance">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/wallet-filled-money-tool 1.svg') }}" alt="Icon Finance" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Finance</span>
                        </a>
                        @endif
                        @if ($showDashboardInactiveShortcuts)
                        <a href="javascript:void(0)" class="dashboard-shortcut-item dashboard-shortcut-item--profile shortcut-card" aria-label="Profile">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bx-user-pin.svg.svg') }}" alt="Icon Profile" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Profile</span>
                        </a>
                        @endif
                        <a href="{{ route('project_management.task_list') }}" class="dashboard-shortcut-item dashboard-shortcut-item--laporan shortcut-card" aria-label="Laporan">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bxs-report.svg.svg') }}" alt="Icon Lainnya" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Laporan</span>
                        </a>
                        @if ($showDashboardInactiveShortcuts)
                        <a href="javascript:void(0)" class="dashboard-shortcut-item dashboard-shortcut-item--pelamar shortcut-card" aria-label="Pelamar">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bx-user-plus.svg.svg') }}" alt="Icon Pelamar" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Pelamar</span>
                        </a>
                        @endif
                        @if ($canViewDashboardEmployeeShortcut)
                        <a href="{{ route('employee_data') }}" class="dashboard-shortcut-item dashboard-shortcut-item--karyawan shortcut-card" aria-label="Karyawan">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bxs-user-detail.svg.svg') }}" alt="Icon Karyawan" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Karyawan</span>
                        </a>
                        @endif
                        @if ($showDashboardInactiveShortcuts)
                        <a href="javascript:void(0)" class="dashboard-shortcut-item dashboard-shortcut-item--setting shortcut-card" aria-label="Setting">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bx-slider-alt.svg.svg') }}" alt="Icon Setting" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Setting</span>
                        </a>
                        <a href="javascript:void(0)" class="dashboard-shortcut-item dashboard-shortcut-item--administration shortcut-card" aria-label="Administration">
                            <span class="dashboard-shortcut-box">
                                <img src="{{ asset('assets/icon-menus/bxs-credit-card.svg.svg') }}" alt="Icon Administration" class="dashboard-shortcut-icon-image">
                            </span>
                            <span class="dashboard-shortcut-text">Administration</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dashboardClockIn" tabindex="-1" aria-labelledby="dashboardClockInLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="dashboardClockInLabel">Attendance Confirmation</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="form-label mb-3 text-center">
                        <span id="dashboardClockInCurrentDate" class="onsite-running-time text-success fw-semibold">--</span>
                        <span id="dashboardClockInRunningTime" class="d-none">--:--:--</span>
                    </p>
                    <p class="form-label text-muted mb-3">
                        Grab your coffee and let's get things done. Clock in when you're ready to kick off your shift!
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Current Location</label>
                        <div id="dashboardClockInMapCanvas" class="onsite-map-canvas rounded"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <p class="small text-muted mb-0" id="dashboardClockInStatusText">Please wait</p>
                        <p class="small d-none mt-2 mb-0" id="dashboardClockInVerifyMessage"></p>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">IP Address</label>
                        <p class="mb-0">
                            @if (! empty($publicIp))
                                <span id="dashboardClockInIpText" class="{{ ($isIpPrefixMatch ?? false) ? 'text-success' : 'text-danger' }}">{{ $publicIp }}</span>
                            @else
                                <span id="dashboardClockInIpText" class="text-muted">Please wait</span>
                            @endif
                            <span id="dashboardClockInIpBadge" class="ms-1 {{ ($isIpPrefixMatch ?? false) ? 'text-success' : 'text-danger' }}">{{ ($isIpPrefixMatch ?? false) ? 'Valid' : 'Tidak Valid' }}</span>
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn light btn-success btn-lg w-100" id="dashboardClockInSubmitBtn" disabled>Clock In</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dashboardClockOut" tabindex="-1" aria-labelledby="dashboardClockOutLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="dashboardClockOutLabel">End of Shift</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="form-label mb-3 text-center">
                        <span id="dashboardClockOutCurrentDate" class="onsite-running-time text-black fw-semibold">--</span>
                        <span id="dashboardClockOutRunningTime" class="d-none">--:--:--</span>
                    </p>
                    <p class="form-label text-muted mb-3">
                        Please make sure your daily tasks are wrapped up before clocking out. Thank you for your hard work, and enjoy the rest of your day!
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Current Location</label>
                        <div id="dashboardClockOutMapCanvas" class="onsite-map-canvas rounded"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <p class="small text-muted mb-0" id="dashboardClockOutStatusText">Please wait</p>
                        <p class="small d-none mt-2 mb-0" id="dashboardClockOutVerifyMessage"></p>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">IP Address</label>
                        <p class="mb-0">
                            @if (! empty($publicIp))
                                <span id="dashboardClockOutIpText" class="{{ ($isIpPrefixMatch ?? false) ? 'text-success' : 'text-danger' }}">{{ $publicIp }}</span>
                            @else
                                <span id="dashboardClockOutIpText" class="text-muted">Please wait</span>
                            @endif
                            <span id="dashboardClockOutIpBadge" class="ms-1 {{ ($isIpPrefixMatch ?? false) ? 'text-success' : 'text-danger' }}">{{ ($isIpPrefixMatch ?? false) ? 'Valid' : 'Tidak Valid' }}</span>
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn light btn-danger btn-lg w-100" id="dashboardClockOutSubmitBtn" disabled>Clock Out</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    @php
        $sweetAlertJsPath = public_path('assets/vendor/sweetalert2/sweetalert2.min.js');
        $sweetAlertJsVersion = file_exists($sweetAlertJsPath) ? filemtime($sweetAlertJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ file_exists(public_path('assets/js/dashboard.js')) ? filemtime(public_path('assets/js/dashboard.js')) : time() }}"></script>
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}?v={{ $sweetAlertJsVersion }}"></script>
    <script>
        (function () {
            if (typeof window.jQuery === 'undefined') {
                return;
            }

            var $ = window.jQuery;
            var attendanceSummaryTimeElement = document.getElementById('dashboardAttendanceSummaryTimeValue');
            var attendanceClockOutSummaryTimeElement = document.getElementById('dashboardAttendanceClockOutSummaryTimeValue');
            var clockInCardButtonElement = document.getElementById('dashboardClockInCardButton');
            var clockOutCardButtonElement = document.getElementById('dashboardClockOutCardButton');
            var googleMapsApiKey = @json(config('services.google.api_key'));
            var officeLocation = @json($officeLocation);
            var clockInModalElement = document.getElementById('dashboardClockIn');
            var clockOutModalElement = document.getElementById('dashboardClockOut');
            var clockInCurrentDateElement = document.getElementById('dashboardClockInCurrentDate');
            var clockOutCurrentDateElement = document.getElementById('dashboardClockOutCurrentDate');
            var clockInRunningTimeElement = document.getElementById('dashboardClockInRunningTime');
            var clockOutRunningTimeElement = document.getElementById('dashboardClockOutRunningTime');
            var clockInMapCanvasElement = document.getElementById('dashboardClockInMapCanvas');
            var clockOutMapCanvasElement = document.getElementById('dashboardClockOutMapCanvas');
            var clockInStatusTextElement = document.getElementById('dashboardClockInStatusText');
            var clockOutStatusTextElement = document.getElementById('dashboardClockOutStatusText');
            var clockInVerifyButton = document.getElementById('dashboardClockInVerifyBtn');
            var clockOutVerifyButton = document.getElementById('dashboardClockOutVerifyBtn');
            var clockInVerifyMessageElement = document.getElementById('dashboardClockInVerifyMessage');
            var clockOutVerifyMessageElement = document.getElementById('dashboardClockOutVerifyMessage');
            var clockInIpTextElement = document.getElementById('dashboardClockInIpText');
            var clockOutIpTextElement = document.getElementById('dashboardClockOutIpText');
            var clockInIpBadgeElement = document.getElementById('dashboardClockInIpBadge');
            var clockOutIpBadgeElement = document.getElementById('dashboardClockOutIpBadge');
            var clockInSubmitButton = document.getElementById('dashboardClockInSubmitBtn');
            var clockOutSubmitButton = document.getElementById('dashboardClockOutSubmitBtn');
            var storeAttendanceUrl = @json(route('attendance.store'));
            var updateAttendanceUrlTemplate = @json(url('/attendance/__ATTENDANCE_ID__'));
            var currentIpUrl = @json(route('attendance.current-ip'));
            var verifyTelegramUsernameUrl = @json(route('attendance.verify-telegram-username'));
            var userActivityLogUrl = @json(route('user-activity-log.store'));
            var csrfToken = @json(csrf_token());
            var browserPublicIp = null;
            var attendanceState = {
                todayAttendanceId: @json($todayAttendanceId ?? null),
                hasCheckedInToday: @json($hasCheckedInToday ?? false),
                hasCheckedOutToday: @json($hasCheckedOutToday ?? false),
                hasEarlyDepartureExceptionToday: @json($hasEarlyDepartureExceptionToday ?? false),
                isFlexibleAttendance: @json((bool) ($officeLocation['is_flexible'] ?? false)),
                canClockOutNow: @json($canClockOutNow ?? true),
                clockOutUnavailableMessage: @json($clockOutUnavailableMessage ?? 'Clock out belum tersedia.')
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

            function parseTimeStringToMinutes(timeString, fallbackMinutes) {
                if (typeof timeString !== 'string' || timeString.trim() === '') {
                    return fallbackMinutes;
                }

                var parts = timeString.trim().split(':');
                if (parts.length < 2) {
                    return fallbackMinutes;
                }

                var hourValue = parseInt(parts[0], 10);
                var minuteValue = parseInt(parts[1], 10);
                if (Number.isNaN(hourValue) || Number.isNaN(minuteValue)) {
                    return fallbackMinutes;
                }

                return (hourValue * 60) + minuteValue;
            }

            function eachModalContext(callback) {
                callback(modalContext.clockIn);
                callback(modalContext.clockOut);
            }

            function showSwalAlert(iconType, titleText, messageText) {
                if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
                    Swal.fire({
                        icon: iconType,
                        title: titleText,
                        text: messageText
                    });
                    return;
                }

                if (typeof window !== 'undefined' && typeof window.alert === 'function') {
                    window.alert(titleText + ': ' + messageText);
                }
            }

            function showClockOutUnavailableNotification() {
                var message = attendanceState.clockOutUnavailableMessage || 'Clock out belum tersedia.';

                showSwalAlert('warning', 'Clock Out Belum Tersedia', message);
            }

            function setOnsiteStatus(context, text, type) {
                if (!context || !context.statusTextElement) {
                    return;
                }

                context.statusTextElement.textContent = text;
                context.statusTextElement.classList.remove('text-success', 'text-danger', 'text-warning', 'text-muted');
                if (type === 'success') {
                    context.statusTextElement.classList.add('text-success');
                    return;
                }
                if (type === 'error') {
                    context.statusTextElement.classList.add('text-danger');
                    return;
                }
                if (type === 'warning') {
                    context.statusTextElement.classList.add('text-warning');
                    return;
                }
                context.statusTextElement.classList.add('text-muted');
            }

            function setVerificationMessage(context, text, type) {
                if (!context || !context.verifyMessageElement) {
                    return;
                }

                setOnsiteStatus(context, text, type);
                context.verifyMessageElement.classList.add('d-none');
                context.verifyMessageElement.textContent = '';
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
                setOnsiteStatus(context, 'Please wait');
            }

            function setOnsiteIpIndicator(context, ipAddress, isValidIpPrefix) {
                if (!context || !context.ipTextElement) {
                    return;
                }

                var normalizedIp = (!ipAddress || ipAddress === '-') ? 'Tidak tersedia' : ipAddress;
                context.ipTextElement.textContent = normalizedIp;
                context.ipTextElement.classList.remove('text-success', 'text-danger', 'text-muted');
                context.ipTextElement.classList.add(normalizedIp === 'Tidak tersedia' ? 'text-muted' : (isValidIpPrefix ? 'text-success' : 'text-danger'));

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

                context.ipTextElement.textContent = 'Please wait';
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
                        || attendanceState.hasCheckedOutToday
                        || attendanceState.hasEarlyDepartureExceptionToday
                        || !attendanceState.canClockOutNow;
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
                        clockInCardButtonElement.setAttribute('data-bs-target', '#dashboardClockIn');
                        clockInCardButtonElement.removeAttribute('aria-disabled');
                        clockInCardButtonElement.removeAttribute('tabindex');
                    }
                }

                if (clockOutCardButtonElement) {
                    var isClockOutDisabled = attendanceState.hasCheckedOutToday
                        || attendanceState.hasEarlyDepartureExceptionToday
                        || !attendanceState.canClockOutNow;
                    clockOutCardButtonElement.classList.toggle('disabled', isClockOutDisabled);
                    clockOutCardButtonElement.classList.toggle('attendance-clock-out-too-early', !attendanceState.canClockOutNow);
                    if (isClockOutDisabled) {
                        clockOutCardButtonElement.removeAttribute('data-bs-toggle');
                        clockOutCardButtonElement.removeAttribute('data-bs-target');
                        clockOutCardButtonElement.setAttribute('aria-disabled', 'true');
                        clockOutCardButtonElement.setAttribute('tabindex', '-1');
                        clockOutCardButtonElement.setAttribute('title', attendanceState.clockOutUnavailableMessage || 'Clock out belum tersedia.');
                        if (!attendanceState.canClockOutNow) {
                            clockOutCardButtonElement.setAttribute('data-clock-out-too-early', 'true');
                            clockOutCardButtonElement.setAttribute('data-clock-out-unavailable-message', attendanceState.clockOutUnavailableMessage || 'Clock out belum tersedia.');
                        } else {
                            clockOutCardButtonElement.removeAttribute('data-clock-out-too-early');
                            clockOutCardButtonElement.removeAttribute('data-clock-out-unavailable-message');
                        }
                    } else {
                        clockOutCardButtonElement.setAttribute('data-bs-toggle', 'modal');
                        clockOutCardButtonElement.setAttribute('data-bs-target', '#dashboardClockOut');
                        clockOutCardButtonElement.removeAttribute('aria-disabled');
                        clockOutCardButtonElement.removeAttribute('tabindex');
                        clockOutCardButtonElement.removeAttribute('title');
                        clockOutCardButtonElement.removeAttribute('data-clock-out-too-early');
                        clockOutCardButtonElement.removeAttribute('data-clock-out-unavailable-message');
                    }
                }
            }

            function toRadians(degrees) {
                return degrees * (Math.PI / 180);
            }

            function calculateDistanceInMeters(lat1, lon1, lat2, lon2) {
                var earthRadius = 6371000;
                var latitudeDifference = toRadians(lat2 - lat1);
                var longitudeDifference = toRadians(lon2 - lon1);
                var haversineA = Math.sin(latitudeDifference / 2) * Math.sin(latitudeDifference / 2)
                    + Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2))
                    * Math.sin(longitudeDifference / 2) * Math.sin(longitudeDifference / 2);
                var haversineC = 2 * Math.atan2(Math.sqrt(haversineA), Math.sqrt(1 - haversineA));

                return earthRadius * haversineC;
            }

            function loadGoogleMapsApi() {
                return new Promise(function (resolve, reject) {
                    if (window.google && window.google.maps) {
                        resolve();
                        return;
                    }

                    if (!googleMapsApiKey) {
                        reject(new Error('Google Maps API key belum diset.'));
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
                if (!context || !context.mapCanvasElement || !officeLocation) {
                    return;
                }

                if (officeLocation.latitude === null || officeLocation.longitude === null) {
                    setVerificationMessage(context, 'Koordinat kantor belum tersedia di database perusahaan.', 'error');
                    return;
                }

                if (context.mapInstance || !window.google || !window.google.maps) {
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

                new window.google.maps.Marker({
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

            function logUserActivity(eventName, metadata) {
                if (!userActivityLogUrl || !eventName) {
                    return;
                }

                $.ajax({
                    url: userActivityLogUrl,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        event: eventName,
                        metadata: metadata || {}
                    }
                });
            }

            function checkOnsiteLocation(context) {
                if (!context) {
                    return;
                }

                if (!navigator.geolocation) {
                    context.hasVerifiedOnsite = false;
                    context.hasVerifiedTelegram = false;
                    setVerificationMessage(context, 'Browser tidak mendukung geolocation.', 'error');
                    renderSubmitButtons();
                    return;
                }

                if (!window.isSecureContext) {
                    context.hasVerifiedOnsite = false;
                    context.hasVerifiedTelegram = false;
                    setVerificationMessage(context, 'Geolocation hanya jalan di HTTPS atau localhost.', 'error');
                    renderSubmitButtons();
                    return;
                }

                context.hasVerifiedOnsite = false;
                context.hasVerifiedTelegram = false;
                if (context.verifyButtonElement) {
                    context.verifyButtonElement.classList.add('d-none');
                }

                setVerificationMessage(context, 'Please wait', 'muted');
                renderSubmitButtons();

                verifyTelegramUsernameSync().then(function () {
                    context.hasVerifiedTelegram = true;
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            updateUserLocationOnMap(context, position);
                            setVerificationMessage(context, 'Verification successful', 'success');
                            renderSubmitButtons();
                            logUserActivity(context.type + '_verified', {
                                has_coordinates: true
                            });
                        },
                        function () {
                            context.hasVerifiedOnsite = false;
                            renderSubmitButtons();
                            setVerificationMessage(context, 'Gagal mendapatkan lokasi.', 'error');
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
                    setVerificationMessage(context, 'Kamu sudah absen masuk hari ini.', 'warning');
                    return;
                }

                if (actionType === 'clock_out' && !attendanceState.hasCheckedInToday) {
                    setVerificationMessage(context, 'Kamu belum absen masuk hari ini.', 'warning');
                    return;
                }

                if (actionType === 'clock_out' && attendanceState.hasCheckedOutToday) {
                    setVerificationMessage(context, 'Kamu sudah absen pulang hari ini.', 'warning');
                    return;
                }

                if (actionType === 'clock_out' && attendanceState.hasEarlyDepartureExceptionToday) {
                    setVerificationMessage(context, 'Clock out dinonaktifkan karena exception Early Departure sudah diajukan.', 'warning');
                    return;
                }

                if (actionType === 'clock_out' && !attendanceState.canClockOutNow) {
                    setVerificationMessage(context, attendanceState.clockOutUnavailableMessage || 'Clock out belum tersedia.', 'warning');
                    return;
                }

                if (!(context.hasVerifiedOnsite && context.hasVerifiedTelegram)) {
                    setVerificationMessage(context, 'Harap verifikasi terlebih dahulu sebelum absen.', 'warning');
                    renderSubmitButtons();
                    return;
                }

                context.submitButtonElement.disabled = true;
                setVerificationMessage(context, 'Memproses submit absen...', 'muted');

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
                        if (isCheckInAction) {
                            attendanceState.hasCheckedInToday = true;
                            if (response && response.attendance_id) {
                                attendanceState.todayAttendanceId = response.attendance_id;
                            }
                        } else {
                            if (attendanceState.isFlexibleAttendance) {
                                attendanceState.hasCheckedInToday = false;
                                attendanceState.hasCheckedOutToday = false;
                                attendanceState.todayAttendanceId = null;
                            } else {
                                attendanceState.hasCheckedOutToday = true;
                            }
                        }

                        renderSubmitButtons();
                        resolveBrowserPublicIpAndRefresh();
                        setVerificationMessage(context, response && response.message ? response.message : 'Absen berhasil disimpan.', 'success');
                        window.location.reload();
                    }).fail(function (xhr) {
                        var errorMessage = 'Gagal memproses absen.';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        setVerificationMessage(context, errorMessage, 'error');
                    }).always(function () {
                        renderSubmitButtons();
                    });
                });
            }

            function renderDashboardAttendanceTime() {
                var now = new Date();
                var dateParts = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    weekday: 'long',
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }).formatToParts(now);
                var cardDateParts = new Intl.DateTimeFormat('en-GB', {
                    timeZone: 'Asia/Jakarta',
                    day: '2-digit',
                    month: 'short',
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
                var cardDateMap = {};
                cardDateParts.forEach(function (part) {
                    cardDateMap[part.type] = part.value;
                });
                var timeMap = {};
                timeParts.forEach(function (part) {
                    timeMap[part.type] = part.value;
                });
                var formattedTime = timeMap.hour + ':' + timeMap.minute + ':' + timeMap.second;
                var formattedCardMonth = String(cardDateMap.month || '').replace('.', '');
                var formattedDateTime = cardDateMap.day + ' ' + formattedCardMonth + ' ' + cardDateMap.year + ' | ' + formattedTime;
                var modalDate = dateMap.weekday + ', ' + dateMap.day + ' ' + dateMap.month + ' ' + dateMap.year;
                var modalDateTime = modalDate + ' - ' + formattedTime;
                var hour = parseInt(timeMap.hour, 10);
                var minute = parseInt(timeMap.minute, 10);
                var totalMinutes = (hour * 60) + minute;
                var isWithinWorkRange = totalMinutes >= officeStartTotalMinutes && totalMinutes < officeEndTotalMinutes;
                if (!attendanceState.canClockOutNow && totalMinutes >= officeEndTotalMinutes) {
                    attendanceState.canClockOutNow = true;
                    renderSubmitButtons();
                }

                if (attendanceSummaryTimeElement) {
                    attendanceSummaryTimeElement.textContent = formattedDateTime;
                    attendanceSummaryTimeElement.classList.remove('text-success', 'text-danger');
                    attendanceSummaryTimeElement.classList.add(totalMinutes <= lateThresholdTotalMinutes ? 'text-success' : 'text-danger');
                }
                if (attendanceClockOutSummaryTimeElement) {
                    attendanceClockOutSummaryTimeElement.textContent = formattedDateTime;
                    attendanceClockOutSummaryTimeElement.classList.remove('text-warning', 'text-black');
                    attendanceClockOutSummaryTimeElement.classList.add(isWithinWorkRange ? 'text-warning' : 'text-black');
                }
                if (clockInCurrentDateElement) {
                    clockInCurrentDateElement.textContent = modalDateTime;
                    clockInCurrentDateElement.classList.remove('text-success', 'text-danger');
                    clockInCurrentDateElement.classList.add(totalMinutes <= lateThresholdTotalMinutes ? 'text-success' : 'text-danger');
                }
                if (clockOutCurrentDateElement) {
                    clockOutCurrentDateElement.textContent = modalDateTime;
                    clockOutCurrentDateElement.classList.remove('text-warning', 'text-black');
                    clockOutCurrentDateElement.classList.add(isWithinWorkRange ? 'text-warning' : 'text-black');
                }
                if (clockInRunningTimeElement) {
                    clockInRunningTimeElement.textContent = formattedTime;
                }
                if (clockOutRunningTimeElement) {
                    clockOutRunningTimeElement.textContent = formattedTime;
                }
            }

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

                            checkOnsiteLocation(context);
                        })
                        .catch(function (error) {
                            setVerificationMessage(context, error && error.message ? error.message : 'Gagal memuat peta.', 'error');
                        });
                    renderSubmitButtons();
                });
            });

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

            if (clockInCardButtonElement) {
                clockInCardButtonElement.addEventListener('click', function () {
                    logUserActivity('clock_in_clicked');
                });
            }

            if (clockOutCardButtonElement) {
                clockOutCardButtonElement.addEventListener('click', function (event) {
                    logUserActivity('clock_out_clicked', {
                        can_clock_out_now: attendanceState.canClockOutNow
                    });

                    if (attendanceState.canClockOutNow) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    showClockOutUnavailableNotification();
                });
            }

            renderDashboardAttendanceTime();
            setInterval(renderDashboardAttendanceTime, 1000);
            renderSubmitButtons();
        })();
    </script>
@endsection
