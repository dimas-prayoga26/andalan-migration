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
    'current' => 'Report',
    'homeRoute' => 'dashboard',
])

@include('absensi.layouts_absensi.profileIndex')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Report</h5>
    </div>
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
            var attendanceDetailModalElement = document.getElementById('attendanceDetailModal');
            var attendanceDetailModalLabel = document.getElementById('attendanceDetailModalLabel');
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
            var attendanceDatatableUrl = null;
            var isStaffTableView = @json($showStaffPeriodFilter ?? false);
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

            $('.absensi-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
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
