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
                        @php
                            $filterColumnClass = 'col-12 col-md-3 order-2 order-md-1';
                            $dateTimeColumnClass = 'col-12 col-md-6 order-1 order-md-2';

                            if ($showStaffPeriodFilter ?? false) {
                                $filterColumnClass = 'col-12 col-md-4 order-2 order-md-1';
                                $dateTimeColumnClass = 'col-12 col-md-5 order-1 order-md-2';
                            }
                        @endphp
                        <div class="row g-2 align-items-center mb-3">
                            @if($showCompanyFilter ?? false)
                                <div class="{{ $filterColumnClass }}">
                                    <div class="small text-muted mb-1">Filter Perusahaan</div>
                                    <select class="form-select form-select-sm" id="attendanceCompanyFilter">
                                        <option value="0">Semua Perusahaan</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif($showStaffPeriodFilter ?? false)
                                <div class="{{ $filterColumnClass }}">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="small text-muted mb-1">Bulan</div>
                                            <select class="form-select form-select-sm" id="attendanceMonthFilter">
                                                @foreach(($staffMonthOptions ?? collect()) as $monthOption)
                                                    <option value="{{ $monthOption }}" {{ (int) ($defaultStaffMonth ?? 0) === (int) $monthOption ? 'selected' : '' }}>
                                                        {{ \Illuminate\Support\Carbon::create(null, (int) $monthOption, 1)->translatedFormat('F') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted mb-1">Tahun</div>
                                            <select class="form-select form-select-sm" id="attendanceYearFilter">
                                                @foreach(($staffYearOptions ?? collect()) as $yearOption)
                                                    <option value="{{ $yearOption }}" {{ (int) ($defaultStaffYear ?? 0) === (int) $yearOption ? 'selected' : '' }}>
                                                        {{ $yearOption }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col-12 col-md-3 order-2 order-md-1"></div>
                            @endif
                            <div class="{{ $dateTimeColumnClass }}">
                                <div class="attendance-datetime mb-0" id="attendanceDateTime"></div>
                            </div>
                            <div class="col-12 col-md-3 order-3 text-md-end">
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#attendanceActionModal">Absen</button>
                            </div>
                        </div>
                        <div>
                            <table id="myTable" class="display table">
                                <thead>
                                <tr>
                                    <th class="mw-100">No</th>
                                    <th class="mw-200">Nama Staff</th>
                                    <th class="mw-150">Masuk</th>
                                    <th class="mw-150">Pulang</th>
                                    <th class="mw-200">Nama PT</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="attendanceActionModal" tabindex="-1" aria-labelledby="attendanceActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceActionModalLabel">Pilih Jenis Absen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills mb-3" id="attendanceTypeTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="onsite-tab" data-bs-toggle="tab" data-bs-target="#onsite-pane" type="button" role="tab" aria-controls="onsite-pane" aria-selected="true">Absen Onsite</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="business-trip-tab" data-bs-toggle="tab" data-bs-target="#business-trip-pane" type="button" role="tab" aria-controls="business-trip-pane" aria-selected="false">Absen Business Trip</button>
                    </li>
                </ul>
                <div class="tab-content" id="attendanceTypeTabContent">
                    <div class="tab-pane fade show active" id="onsite-pane" role="tabpanel" aria-labelledby="onsite-tab" tabindex="0">
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
                                    <span id="onsiteIpText" class="{{ ($isIpPrefixMatch ?? false) ? 'text-success fw-semibold' : 'text-danger fw-semibold' }}">
                                        {{ $publicIp ?? '-' }}
                                    </span>
                                    <span id="onsiteIpBadge" class="badge ms-2 {{ ($isIpPrefixMatch ?? false) ? 'bg-success' : 'bg-danger' }}">
                                        {{ ($isIpPrefixMatch ?? false) ? 'Valid' : 'Tidak Valid' }}
                                    </span>
                                </div>
                                <div class="d-flex gap-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="checkOnsiteLocationBtn">Cek Lokasi Saya</button>
                                    <button type="button" class="btn btn-primary btn-sm" id="submitOnsiteAttendanceBtn">Masuk</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="business-trip-pane" role="tabpanel" aria-labelledby="business-trip-tab" tabindex="0">
                        <div class="border rounded p-3">
                            <h6 class="mb-2">Business Trip</h6>
                            <p class="mb-0 text-muted">Konten khusus business trip ditampilkan di sini.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
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
            var attendanceCompanyFilter = document.getElementById('attendanceCompanyFilter');
            var attendanceMonthFilter = document.getElementById('attendanceMonthFilter');
            var attendanceYearFilter = document.getElementById('attendanceYearFilter');
            var googleMapsApiKey = @json(config('services.google_maps.api_key'));
            var officeLocation = @json($officeLocation);
            var attendanceModalElement = document.getElementById('attendanceActionModal');
            var checkOnsiteLocationButton = document.getElementById('checkOnsiteLocationBtn');
            var submitOnsiteAttendanceButton = document.getElementById('submitOnsiteAttendanceBtn');
            var onsiteStatusText = document.getElementById('onsiteStatusText');
            var onsiteIpText = document.getElementById('onsiteIpText');
            var onsiteIpBadge = document.getElementById('onsiteIpBadge');
            var onsiteRunningTimeElement = document.getElementById('onsiteRunningTime');
            var onsiteMapInstance = null;
            var officeMarker = null;
            var officeRadiusCircle = null;
            var userMarker = null;
            var userToOfficeLine = null;
            var storeAttendanceUrl = @json(route('absensi.store'));
            var updateAttendanceUrlTemplate = @json(url('/absensi/__ATTENDANCE_ID__'));
            var attendanceDatatableUrl = @json(route('absensi.datatable'));
            var currentIpUrl = @json(route('absensi.current-ip'));
            var csrfToken = @json(csrf_token());
            var browserPublicIp = null;
            var attendanceState = {
                todayAttendanceId: @json($todayAttendanceId ?? null),
                hasCheckedInToday: @json($hasCheckedInToday ?? false),
                hasCheckedOutToday: @json($hasCheckedOutToday ?? false)
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
                if (!onsiteIpText || !onsiteIpBadge) {
                    return;
                }

                onsiteIpText.textContent = ipAddress || '-';
                onsiteIpText.classList.remove('text-success', 'text-danger', 'fw-semibold');
                onsiteIpBadge.classList.remove('bg-success', 'bg-danger');

                if (isValidIpPrefix) {
                    onsiteIpText.classList.add('text-success', 'fw-semibold');
                    onsiteIpBadge.classList.add('bg-success');
                    onsiteIpBadge.textContent = 'Valid';
                    return;
                }

                onsiteIpText.classList.add('text-danger', 'fw-semibold');
                onsiteIpBadge.classList.add('bg-danger');
                onsiteIpBadge.textContent = 'Tidak Valid';
            }

            function refreshOnsiteIpIndicator() {
                if (!currentIpUrl) {
                    return;
                }

                var refreshIpRequestData = {};
                if (browserPublicIp) {
                    refreshIpRequestData.client_ip = browserPublicIp;
                }

                $.ajax({
                    url: currentIpUrl,
                    method: 'GET',
                    data: refreshIpRequestData,
                    timeout: 10000
                }).done(function (response) {
                    var ipAddress = response && response.ip ? response.ip : '-';
                    var isValidIpPrefix = !!(response && response.is_ip_prefix_match);
                    setOnsiteIpIndicator(ipAddress, isValidIpPrefix);
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
                    return;
                }

                submitOnsiteAttendanceButton.classList.add('btn-success');
                submitOnsiteAttendanceButton.textContent = 'Masuk';
            }

            function setOnsiteStatus(text) {
                if (onsiteStatusText) {
                    onsiteStatusText.textContent = text;
                    onsiteStatusText.classList.remove('text-success', 'text-danger', 'text-muted');

                    if (text === 'Di dalam radius kantor') {
                        onsiteStatusText.classList.add('text-success');
                        return;
                    }

                    if (text === 'Di luar radius kantor') {
                        onsiteStatusText.classList.add('text-danger');
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

                setOnsiteStatus('Peta siap. Klik "Cek Lokasi Saya" untuk validasi.');
            }

            function updateUserLocationOnMap(position) {
                if (!onsiteMapInstance || !officeLocation) {
                    return;
                }

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
            }

            function checkOnsiteLocation() {
                if (!navigator.geolocation) {
                    setOnsiteStatus('Browser tidak mendukung geolocation');
                    return;
                }

                if (!window.isSecureContext) {
                    setOnsiteStatus('Geolocation hanya jalan di HTTPS atau localhost');
                    return;
                }

                setOnsiteStatus('Mengambil lokasi saat ini...');

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        updateUserLocationOnMap(position);
                    },
                    function (error) {
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
            }

            function submitOnsiteAttendance() {
                if (!submitOnsiteAttendanceButton) {
                    return;
                }

                if (attendanceState.hasCheckedOutToday) {
                    setOnsiteStatus('Kamu sudah absen pulang hari ini');
                    return;
                }

                submitOnsiteAttendanceButton.disabled = true;
                setOnsiteStatus('Memproses submit absen...');

                var requestMethod = attendanceState.hasCheckedInToday ? 'PATCH' : 'POST';
                var requestUrl = attendanceState.hasCheckedInToday && attendanceState.todayAttendanceId
                    ? getUpdateAttendanceUrl(attendanceState.todayAttendanceId)
                    : storeAttendanceUrl;

                $.ajax({
                    url: requestUrl,
                    method: requestMethod,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        client_ip: browserPublicIp || (onsiteIpText ? onsiteIpText.textContent.trim() : null)
                    }
                }).done(function (response) {
                    setOnsiteStatus(response && response.message ? response.message : 'Absen berhasil disimpan');
                    if (!attendanceState.hasCheckedInToday) {
                        attendanceState.hasCheckedInToday = true;
                        if (response && response.attendance_id) {
                            attendanceState.todayAttendanceId = response.attendance_id;
                        }
                    } else {
                        attendanceState.hasCheckedOutToday = true;
                    }
                    renderSubmitAttendanceButton();
                    resolveBrowserPublicIpAndRefresh();
                    if (attendanceTable) {
                        attendanceTable.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    var errorMessage = 'Gagal memproses absen';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    setOnsiteStatus(errorMessage);
                }).always(function () {
                    if (!attendanceState.hasCheckedOutToday) {
                        submitOnsiteAttendanceButton.disabled = false;
                    }
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

            renderAttendanceDateTime();
            setInterval(renderAttendanceDateTime, 1000);
            renderOnsiteRunningTime();
            setInterval(renderOnsiteRunningTime, 1000);
            renderSubmitAttendanceButton();

            $('.absensi-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            var attendanceTable = $('#myTable').DataTable({
                ajax: {
                    url: attendanceDatatableUrl,
                    data: function (requestData) {
                        requestData.company_id = attendanceCompanyFilter ? attendanceCompanyFilter.value : 0;
                        requestData.month = attendanceMonthFilter ? attendanceMonthFilter.value : 0;
                        requestData.year = attendanceYearFilter ? attendanceYearFilter.value : 0;
                    },
                    dataSrc: 'data'
                },
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                columns: [
                    {
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'staff_name'
                    },
                    {
                        data: 'check_in'
                    },
                    {
                        data: 'check_out'
                    },
                    {
                        data: 'company_name'
                    }
                ],
                columnDefs: [
                    {
                        targets: 0,
                        searchable: false,
                        orderable: false
                    },
                    {
                        targets: 2,
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
                        targets: 3,
                        render: function (data) {
                            if (data) {
                                return data;
                            }

                            return '<span class="badge-attendance-empty">Belum Absen Pulang</span>';
                        }
                    },
                    {
                        targets: 4,
                        render: function (data) {
                            return data || '-';
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

            attendanceTable.on('order.dt search.dt draw.dt', function () {
                var pageInfo = attendanceTable.page.info();
                attendanceTable.column(0, { page: 'current' }).nodes().each(function (cell, index) {
                    cell.innerHTML = pageInfo.start + index + 1;
                });
            });

            attendanceTable.on('draw', function () {
                attendanceTable.columns.adjust();
            });

            $(window).on('resize', function () {
                attendanceTable.columns.adjust();
            });

            if (attendanceModalElement) {
                attendanceModalElement.addEventListener('shown.bs.modal', function () {
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
                    attendanceTable.ajax.reload();
                });
            }

            if (attendanceMonthFilter) {
                attendanceMonthFilter.addEventListener('change', function () {
                    attendanceTable.ajax.reload();
                });
            }

            if (attendanceYearFilter) {
                attendanceYearFilter.addEventListener('change', function () {
                    attendanceTable.ajax.reload();
                });
            }

        });
    </script>
@endsection
