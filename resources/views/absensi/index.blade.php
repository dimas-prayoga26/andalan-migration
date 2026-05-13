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

<div class="tab-content" id="tabContentMyProfileBottom">
    <div class="row">

        <!-- Start - logs -->
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 align-items-center">
                    <h4 class="card-title">Logs</h4>
                    <div class="d-flex align-items-center gap-2">
                        <div class="clearfix">
                            <select class="selectpicker form-select form-select-sm">
                                <option value="All Time">All Time</option>
                                <option value="Weekly">Week</option>
                                <option value="Monthly">Month</option>
                            </select>
                        </div>
                        <div class="clearfix">
                            <select class="selectpicker form-select form-select-sm">
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
                    <div class="table-responsive">
                        <table id="tableLogs" class="table table-sm table-sm-responsive text-nowrap">
                            <thead>
                                <tr>
                                    <th class="mw-50">No</th>
                                    <th class="mw-150">Nama Staff</th>
                                    <th class="mw-100">Masuk</th>
                                    <th class="mw-150">Pulang</th>
                                    <th class="mw-100">Nama PT</th>
                                    <th class="text-end mw-100">Action</th>
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
                            <button type="button" class="btn btn-outline-primary btn-sm" id="checkOnsiteLocationBtn">Cek Lokasi Saya</button>
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

@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
	
	<!-- Script For Datatables -->
	<script src="vendor/datatables/js/jquery.dataTables.bundle.min.js"></script>
	
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
            var csrfToken = @json(csrf_token());
            var browserPublicIp = null;
            var attendanceState = {
                todayAttendanceId: @json($todayAttendanceId ?? null),
                hasCheckedInToday: @json($hasCheckedInToday ?? false),
                hasCheckedOutToday: @json($hasCheckedOutToday ?? false)
            };
            var latestUserCoordinates = null;
            var officeStartTotalMinutes = parseTimeStringToMinutes(officeLocation && officeLocation.office_start_time, 8 * 60);
            var officeEndTotalMinutes = parseTimeStringToMinutes(officeLocation && officeLocation.office_end_time, 17 * 60);
            var lateGraceMinutes = Number(officeLocation && officeLocation.late_grace_minutes);
            lateGraceMinutes = Number.isNaN(lateGraceMinutes) ? 0 : Math.max(lateGraceMinutes, 0);
            var lateThresholdTotalMinutes = officeStartTotalMinutes + lateGraceMinutes;

            var attendanceTable = null;

            var tableLogs = function(){
                if ($('#tableLogs').length === 0) {
                    return;
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
                    searching: false,
                    pageLength: 6,
                    select: false,
                    lengthChange: false,
                    paging: true,
                    bInfo: true,
                    columns: [
                        { data: null, defaultContent: '' },
                        { data: 'staff_name', defaultContent: '-' },
                        { data: 'check_in', defaultContent: '' },
                        { data: 'check_out', defaultContent: '' },
                        { data: 'company_name', defaultContent: '-' },
                        { data: null, defaultContent: '' }
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
                            targets: 5,
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
                        }
                    ],
                    language: {
                        paginate: {
                            next: '<i class="fa-solid fa-angle-right"></i>',
                            previous: '<i class="fa-solid fa-angle-left"></i>'
                        }
                    }
                });

                attendanceTable.on('order.dt search.dt draw.dt', function () {
                    var pageInfo = attendanceTable.page.info();
                    attendanceTable.column(0, { page: 'current' }).nodes().each(function (cell, index) {
                        cell.innerHTML = pageInfo.start + index + 1;
                    });
                });
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
                        if (!attendanceState.hasCheckedOutToday) {
                            submitOnsiteAttendanceButton.disabled = false;
                        }
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
