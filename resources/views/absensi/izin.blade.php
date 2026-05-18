@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="https://unpkg.com/antd@6.2.3/dist/antd.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
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

        #tableLogs.dataTable tbody td.dataTables_empty {
            text-align: center !important;
        }

        .izin-table-scroll-area {
            overflow: hidden;
        }

        .izin-table-scroll-area #tableLogs {
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
            min-width: 980px;
        }

        #tableLogs_wrapper .dt-scroll-body,
        #tableLogs_wrapper .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
        }

        /* Hilangkan baris header duplikat di body saat scrollX aktif. */
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

        #tableLogs_wrapper .dt-scroll-body thead .dt-column-title,
        #tableLogs_wrapper .dt-scroll-body thead .dt-column-order,
        #tableLogs_wrapper .dataTables_scrollBody thead .dt-column-title,
        #tableLogs_wrapper .dataTables_scrollBody thead .dt-column-order {
            display: none !important;
        }

        /* Gunakan icon sorting dari theme lama saja, matikan icon DataTables v2. */
        #tableLogs_wrapper table.dataTable thead > tr > th span.dt-column-order {
            display: none !important;
        }

        #tableLogs_wrapper .dt-layout-row:first-child {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
        }

        #tableLogs_wrapper .dt-layout-start,
        #tableLogs_wrapper .dt-layout-end {
            display: flex;
            align-items: center;
        }

        #tableLogs_wrapper .dt-layout-end {
            margin-left: auto;
        }

        @media only screen and (max-width: 767.98px) {
            #tableLogs_wrapper .dt-layout-row:first-child {
                flex-direction: column;
                align-items: stretch;
            }

            #tableLogs_wrapper .dt-layout-end {
                margin-left: 0;
            }
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

        .izin-summary-row {
            margin-bottom: 1rem;
        }

        .izin-summary-item {
            padding: 2px;
        }

        .izin-summary-carousel .owl-stage {
            display: flex;
            align-items: stretch;
        }

        .izin-summary-carousel .owl-item {
            display: flex;
        }

        .izin-summary-carousel .owl-item .izin-summary-item {
            width: 100%;
            padding: 0;
        }

        .izin-summary-card {
            background: #fff;
            border: 1px solid #e6eaf2;
            border-radius: 0.75rem;
            box-shadow: 0 1px 6px rgba(17, 24, 39, 0.05);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 78px;
        }

        .izin-summary-label {
            color: #72829f;
            font-size: 1.05rem;
            font-weight: 600;
        }

        .izin-summary-value {
            color: #111827;
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
        }

        #tableLogs thead th {
            color: #25314c;
            font-size: 1.05rem;
            font-weight: 600;
            padding-top: 0.95rem;
            padding-bottom: 0.95rem;
            border-bottom: 1px solid #e6eaf2;
        }

        #tableLogs tbody td {
            color: #5f6c87;
            font-size: 1.02rem;
            padding-top: 1rem;
            padding-bottom: 1rem;
            vertical-align: middle;
            border-top: 1px solid #eef2f7;
        }

        #tableLogs tbody td:nth-child(1),
        #tableLogs tbody td:nth-child(3),
        #tableLogs tbody td:nth-child(8) {
            text-align: center;
        }

        #tableLogs tbody td:nth-child(2),
        #tableLogs tbody td:nth-child(4),
        #tableLogs tbody td:nth-child(5),
        #tableLogs tbody td:nth-child(6),
        #tableLogs tbody td:nth-child(7) {
            white-space: nowrap;
        }

        .submission-list-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #25314c;
            margin-bottom: 0.6rem;
            text-align: center;
            white-space: nowrap;
        }

        .izin-card-header {
            display: block !important;
        }

        .izin-status-badge {
            display: inline-block;
            border-radius: 0.4rem;
            padding: 0.25rem 0.6rem;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.2;
            border: 1px solid;
        }

        .izin-status-badge.pending {
            background: #eef2ff;
            color: #6475a7;
            border-color: #8397cf;
        }

        .izin-status-badge.refused {
            background: #ffe8ed;
            color: #ff3355;
            border-color: #ff3355;
        }

        .izin-status-badge.approved {
            background: #eafbf1;
            color: #0f9d58;
            border-color: #9de0bc;
        }

        .permission-status-select {
            min-width: 150px;
            max-width: 170px;
            margin: 0 auto;
            border-radius: 0.65rem;
            border: 1px solid #d9e1ee;
            font-weight: 600;
            color: #4c5d7c;
            background-color: #f8fbff;
        }

        .izin-action-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }

        .izin-action-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .izin-action-btn.info {
            background: #d9f2f4;
            color: #4aa3ad;
        }

        .izin-action-btn.edit {
            background: #f8e8b7;
            color: #f2ad00;
        }

        .izin-action-btn.delete {
            background: #f8d6e2;
            color: #ff4f7b;
        }

        .izin-form-label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #25314c;
            margin-bottom: 0.45rem;
        }

        #izinAttachmentDropzone {
            border: 1.5px dashed #8da2ce;
            border-radius: 0.8rem;
            background: linear-gradient(180deg, #f8fbff 0%, #f1f6ff 100%);
            min-height: 150px;
            transition: all 0.2s ease-in-out;
        }

        #izinAttachmentDropzone.dz-drag-hover {
            border-color: #5b7ddb;
            background: #eaf1ff;
        }

        #izinAttachmentDropzone .dz-message {
            margin: 1.6rem 0;
            color: #5d6b84;
            font-weight: 500;
        }

        #izinAttachmentDropzone .dz-message .dropzone-icon {
            display: inline-flex;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            color: #3758b5;
            background: rgba(55, 88, 181, 0.12);
            font-size: 1.2rem;
        }

        #izinAttachmentDropzone .dz-message .dropzone-main {
            font-size: 0.98rem;
            font-weight: 600;
            color: #455677;
        }

        #izinAttachmentDropzone .dz-message .dropzone-sub {
            font-size: 0.84rem;
            color: #7b88a1;
        }

        #izinAttachmentDropzone .dz-preview {
            background: transparent !important;
            box-shadow: none !important;
            border: 0 !important;
            margin: 0.9rem auto 0.35rem;
            padding: 0 !important;
        }

        #izinAttachmentDropzone .dz-preview .dz-details {
            background: transparent !important;
            padding: 0 !important;
        }

        #izinAttachmentDropzone .dz-preview .dz-image {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            border-radius: 0.8rem;
            overflow: hidden;
        }
    </style>

@endsection

@section('navbarTitle', 'Attendances')

@section('content')
<!-- Start - Page Title & Breadcrumb -->
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Izin',
    'homeRoute' => 'dashboard',
])

@include('absensi.layouts_absensi.profileIndex')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Izin / Cuti</h5>
        <div class="d-flex align-items-center">
            @if($canSubmitPermission ?? false)
            <button
                type="button"
                class="me-2 btn btn-success light btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#submitIzinModal"
            >Izin</button>
            @endif
        </div>
    </div>
</div>

<div class="tab-content" id="tabContentMyProfileBottom">
    <div class="row">

        <!-- Start - logs -->
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 izin-card-header">
                    <h4 class="card-title mb-3">List Pengajuan</h4>
                    <div class="row g-2 align-items-stretch">
                        <div class="col-12">
                            <div class="izin-summary-carousel js-izin-summary-carousel owl-carousel">
                                @foreach(($summaryCards ?? []) as $summaryCard)
                                    <div class="izin-summary-item">
                                        <div class="izin-summary-card h-100">
                                            <span class="izin-summary-label">{{ $summaryCard['label'] ?? '-' }}</span>
                                            <span class="izin-summary-value">{{ $summaryCard['value'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @if($canFilterEmployees ?? false)
                        <div class="row mt-3">
                            <div class="col-12 col-md-5 col-lg-4">
                                <label for="attendanceStaffFilter" class="form-label mb-1">Filter Karyawan</label>
                                <select id="attendanceStaffFilter" class="form-select form-select-sm">
                                    <option value="">Semua Karyawan</option>
                                    @foreach(($staffUsers ?? collect()) as $staffUser)
                                        <option value="{{ $staffUser->id }}" {{ (string) ($defaultStaffUserId ?? '') === (string) $staffUser->id ? 'selected' : '' }}>
                                            {{ $staffUser->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body table-card-body px-0 pt-0 pb-2">
                    <div class="table-responsive izin-table-scroll-area">
                        <table id="tableLogs" class="table table-sm table-sm-responsive text-nowrap">
                            <thead>
                                <tr>
                                    <th class="mw-100">No</th>
                                    <th class="mw-220">Tanggal</th>
                                    <th class="mw-120">Durasi</th>
                                    <th class="mw-180">Nama Staff</th>
                                    <th class="mw-180">Tipe Izin</th>
                                    <th class="mw-140">Status</th>
                                    <th class="mw-250">Keterangan</th>
                                    <th class="mw-130 text-center">Action</th>
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
<!-- End - Page Title & Breadcrumb -->

<div class="modal fade" id="submitIzinModal" tabindex="-1" aria-labelledby="submitIzinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submitIzinModalLabel">Pengajuan Izin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="izinSubmissionForm" action="{{ route('absensi.izin.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="izinStartDate" name="start_date">
                    <input type="hidden" id="izinEndDate" name="end_date">
                    <input type="hidden" id="izinAttachmentPath" name="attachment_path">
                    <div class="mb-3">
                        <label class="izin-form-label" for="izinDateRangeRoot">Durasi Izin atau Cuti</label>
                        <input
                            class="form-control"
                            type="text"
                            id="izinDateRangeInput"
                            name="izin_daterange"
                            placeholder="DD/MM/YYYY - DD/MM/YYYY"
                            autocomplete="off"
                        />
                    </div>
                    <div class="mb-3">
                        <label class="izin-form-label" for="izinPermissionType">Tipe Izin atau Cuti</label>
                        <select class="form-select" id="izinPermissionType" name="permission_type_id">
                            <option value="">Pilih tipe izin atau cuti</option>
                            @forelse($permissionTypes ?? [] as $permissionType)
                                <option value="{{ $permissionType->id }}">{{ $permissionType->name }}</option>
                            @empty
                                <option value="" disabled>Data tipe izin belum tersedia</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="izin-form-label" for="izinAttachmentDropzone">Lampiran (Opsional)</label>
                        <div id="izinAttachmentDropzone" class="dropzone izin-dropzone-skin"></div>
                    </div>
                    <div class="mb-0">
                        <label class="izin-form-label" for="izinDescription">Keterangan</label>
                        <textarea
                            class="form-control"
                            id="izinDescription"
                            name="reason"
                            rows="4"
                            placeholder="Tulis keterangan izin atau cuti"
                        ></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="submitIzinButton" class="btn btn-primary btn-sm">Submit Izin</button>
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
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/dayjs@1/dayjs.min.js"></script>
    <script src="https://unpkg.com/antd@6.2.3/dist/antd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
    <script>
        if (typeof Dropzone !== 'undefined') {
            Dropzone.autoDiscover = false;
        }

        var leaveRequestShowUrlTemplate = '{{ route('absensi.izin.show', ['leaveRequest' => '__ID__']) }}';
        var leaveRequestDestroyUrlTemplate = '{{ route('absensi.izin.destroy', ['leaveRequest' => '__ID__']) }}';
        var leaveRequestUpdateStatusUrlTemplate = '{{ route('absensi.izin.update-status', ['leaveRequest' => '__ID__']) }}';
        var leaveRequestStoreUrl = '{{ route('absensi.izin.store') }}';
        var leaveRequestUploadImageUrl = '{{ route('absensi.izin.upload-image') }}';
        var leaveRequestDeleteUploadedImageUrl = '{{ route('absensi.izin.delete-uploaded-image') }}';
        var canUpdatePermissionStatus = @json($canUpdatePermissionStatus ?? false);
        var canDeletePermission = @json($canDeletePermission ?? false);

        $(function () {
            var attendanceDateElement = document.getElementById('attendanceDateTime');
            var attendanceStaffFilter = document.getElementById('attendanceStaffFilter');
            var izinAttachmentDropzoneInstance = null;
            var izinAttachmentPathInput = document.getElementById('izinAttachmentPath');

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

            function initIzinSummaryCarousel() {
                var $summaryCarousel = $('.js-izin-summary-carousel');
                if ($summaryCarousel.length === 0 || typeof $.fn.owlCarousel !== 'function') {
                    return;
                }

                if ($summaryCarousel.hasClass('owl-loaded')) {
                    $summaryCarousel.trigger('destroy.owl.carousel');
                    $summaryCarousel.removeClass('owl-loaded');
                    $summaryCarousel.find('.owl-stage-outer').children().unwrap();
                }

                $summaryCarousel.owlCarousel({
                    loop: false,
                    margin: 10,
                    nav: false,
                    dots: false,
                    mouseDrag: true,
                    touchDrag: true,
                    pullDrag: true,
                    responsive: {
                        0: {
                            items: 1
                        },
                        576: {
                            items: 2
                        },
                        992: {
                            items: 4
                        }
                    }
                });
            }

            var leaveRequestTable = null;

            // OLD script inisialisasi langsung DataTable dinonaktifkan,
            // sekarang dipakai pola tableLogs() agar konsisten seperti halaman index.
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

            var tableLogs = function () {
                if ($('#tableLogs').length === 0) {
                    return;
                }

                if ($.fn.DataTable.isDataTable('#tableLogs')) {
                    leaveRequestTable = $('#tableLogs').DataTable();
                    return;
                }

                leaveRequestTable = $('#tableLogs').DataTable({
                    ajax: {
                        url: '{{ route('absensi.izin.datatable') }}',
                        data: function (requestData) {
                            requestData.staff_user_id = attendanceStaffFilter ? attendanceStaffFilter.value : '';
                        },
                        dataSrc: 'data'
                    },
                    autoWidth: false,
                    scrollX: true,
                    scrollCollapse: false,
                    searching: false,
                    lengthChange: false,
                    columns: [
                        { data: null, defaultContent: '' },
                        { data: 'date_range', defaultContent: '-' },
                        { data: 'duration', defaultContent: '-' },
                        { data: 'staff_name', defaultContent: '-' },
                        { data: 'permission_type', defaultContent: '-' },
                        { data: 'status', defaultContent: 'pending' },
                        { data: 'reason', defaultContent: '-' },
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
                            render: function (data, type, row) {
                                var normalizedStatus = String(data || 'pending').toLowerCase();
                                var selectedPending = normalizedStatus === 'pending' ? ' selected' : '';
                                var selectedApproved = normalizedStatus === 'approved' ? ' selected' : '';
                                var selectedRejected = (normalizedStatus === 'refused' || normalizedStatus === 'rejected') ? ' selected' : '';
                                var isFinalStatus = normalizedStatus !== 'pending';
                                var selectDisabled = !canUpdatePermissionStatus || isFinalStatus ? ' disabled' : '';

                                return '<select class="form-select form-select-sm permission-status-select" data-id="' + (row && row.id ? row.id : '') + '" data-current-status="' + normalizedStatus + '"' + selectDisabled + '>'
                                    + '<option value="pending"' + selectedPending + '>Pending</option>'
                                    + '<option value="approved"' + selectedApproved + '>Approved</option>'
                                    + '<option value="rejected"' + selectedRejected + '>Rejected</option>'
                                    + '</select>';
                            }
                        },
                        {
                            targets: 7,
                            searchable: false,
                            orderable: false,
                            className: 'text-center',
                            render: function (data, type, row) {
                                var permissionId = row && row.id ? String(row.id) : '';
                                var safePermissionId = escapeHtml(permissionId);

                                if (safePermissionId === '') {
                                    return '-';
                                }

                                var deleteButtonHtml = '';
                                if (canDeletePermission) {
                                    deleteButtonHtml = '<button type="button" class="izin-action-btn delete js-izin-delete" data-id="' + safePermissionId + '"><i class="bi bi-trash"></i></button>';
                                }

                                return '<div class="izin-action-group">'
                                    + '<button type="button" class="izin-action-btn info js-izin-info" data-id="' + safePermissionId + '"><i class="bi bi-info-circle"></i></button>'
                                    + deleteButtonHtml
                                    + '</div>';
                            }
                        }
                    ],
                    drawCallback: function () {
                        ensureEmptyPageOne(this.api());
                    }
                });

                leaveRequestTable.on('order.dt search.dt draw.dt', function () {
                    var pageInfo = leaveRequestTable.page.info();
                    leaveRequestTable.column(0, { page: 'current' }).nodes().each(function (cell, index) {
                        cell.innerHTML = pageInfo.start + index + 1;
                    });
                });

                $(window).on('resize', function () {
                    leaveRequestTable.columns.adjust();
                });

                ensureEmptyPageOne(leaveRequestTable);
            };

            initIzinSummaryCarousel();
            tableLogs();

            if (attendanceStaffFilter) {
                attendanceStaffFilter.addEventListener('change', function () {
                    if (leaveRequestTable) {
                        leaveRequestTable.ajax.reload();
                    }
                });
            }

            $('#tableLogs').on('click', '.js-izin-info', function () {
                var permissionId = $(this).attr('data-id') || '';
                infoData(permissionId);
            });

            $('#tableLogs').on('click', '.js-izin-delete', function () {
                var permissionId = $(this).attr('data-id') || '';
                deleteData(permissionId);
            });

            $('#tableLogs').on('change', '.permission-status-select', function () {
                var $statusSelect = $(this);
                var permissionId = $statusSelect.data('id');
                var previousStatus = String($statusSelect.data('current-status') || 'pending').toLowerCase();
                var selectedStatus = String($statusSelect.val() || 'pending').toLowerCase();

                if (!permissionId || !canUpdatePermissionStatus) {
                    $statusSelect.val(previousStatus);
                    return;
                }

                if (selectedStatus === previousStatus) {
                    return;
                }

                $.ajax({
                    url: buildLeaveRequestUrl(leaveRequestUpdateStatusUrlTemplate, permissionId),
                    type: 'PUT',
                    data: {
                        status: selectedStatus
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    beforeSend: function () {
                        $statusSelect.prop('disabled', true);
                    },
                    success: function (response) {
                        if (!response || response.success !== true) {
                            $statusSelect.val(previousStatus);
                            $statusSelect.prop('disabled', false);

                            Swal.fire({
                                icon: 'warning',
                                title: 'Gagal',
                                text: response && response.message ? response.message : 'Gagal memperbarui status izin.'
                            });
                            return;
                        }

                        $statusSelect.data('current-status', selectedStatus);
                        $statusSelect.prop('disabled', true);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1200,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        $statusSelect.val(previousStatus);
                        $statusSelect.prop('disabled', false);

                        var responseJson = xhr.responseJSON || {};
                        var errorMessage = responseJson.message || 'Gagal memproses permintaan.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: errorMessage
                        });
                    }
                });
            });

            // Summary cards now use static Bootstrap grid (no carousel) to avoid layout collisions.

            function initIzinDateRangePicker() {
                var $izinDateRangeInput = $('#izinDateRangeInput');
                var $submitIzinModal = $('#submitIzinModal');
                var $izinStartDate = $('#izinStartDate');
                var $izinEndDate = $('#izinEndDate');

                if (!$.fn.daterangepicker || !$izinDateRangeInput.length) {
                    return;
                }

                var currentMonthStart = moment().startOf('month');
                var currentDate = moment();

                $izinDateRangeInput.daterangepicker({
                    parentEl: '#submitIzinModal .modal-body',
                    opens: 'center',
                    autoUpdateInput: false,
                    minDate: currentMonthStart,
                    startDate: currentMonthStart,
                    endDate: currentDate,
                    locale: {
                        format: 'DD/MM/YYYY',
                        cancelLabel: 'Clear'
                    }
                });

                $izinDateRangeInput.on('apply.daterangepicker', function (event, picker) {
                    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                    $izinStartDate.val(picker.startDate.format('YYYY-MM-DD'));
                    $izinEndDate.val(picker.endDate.format('YYYY-MM-DD'));
                });

                $izinDateRangeInput.on('cancel.daterangepicker', function () {
                    $(this).val('');
                    $izinStartDate.val('');
                    $izinEndDate.val('');
                });

                $submitIzinModal.on('shown.bs.modal', function () {
                    $izinDateRangeInput.attr('placeholder', 'DD/MM/YYYY - DD/MM/YYYY');
                });
            }

            function initIzinAttachmentDropzone() {
                if (typeof Dropzone === 'undefined') {
                    return;
                }

                if (izinAttachmentDropzoneInstance) {
                    return;
                }

                var dropzoneElement = document.getElementById('izinAttachmentDropzone');
                if (!dropzoneElement) {
                    return;
                }

                try {
                    var existingInstance = Dropzone.forElement(dropzoneElement);
                    if (existingInstance) {
                        existingInstance.destroy();
                        dropzoneElement.innerHTML = '';
                    }
                } catch (error) {
                    // No existing instance attached, continue initialization.
                }

                var dropzoneConfiguration = {
                    url: leaveRequestUploadImageUrl,
                    paramName: 'attachment_file',
                    autoProcessQueue: true,
                    uploadMultiple: false,
                    maxFiles: 1,
                    acceptedFiles: '.jpg,.jpeg,.png,.pdf',
                    maxFilesize: 1,
                    dictDefaultMessage: '<div class="text-center">'
                        + '<div class="dropzone-icon"><i class="bi bi-cloud-arrow-up"></i></div>'
                        + '<div class="dropzone-main">Drop file di sini atau klik untuk upload</div>'
                        + '<div class="dropzone-sub">Format: JPG, JPEG, PNG, PDF (Maks. 1 MB)</div>'
                        + '</div>',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    init: function () {
                        izinAttachmentDropzoneInstance = this;

                        this.on('maxfilesexceeded', function (file) {
                            this.removeAllFiles(true);
                            this.addFile(file);
                        });

                        this.on('success', function (file, response) {
                            var uploadedAttachmentPath = response && response.attachment_path ? String(response.attachment_path) : '';
                            if (izinAttachmentPathInput) {
                                izinAttachmentPathInput.value = uploadedAttachmentPath;
                            }
                            file._uploadedAttachmentPath = uploadedAttachmentPath;
                        });

                        this.on('removedfile', function (file) {
                            var fileAttachmentPath = file && file._uploadedAttachmentPath ? String(file._uploadedAttachmentPath) : '';
                            if (fileAttachmentPath === '' && izinAttachmentPathInput) {
                                fileAttachmentPath = String(izinAttachmentPathInput.value || '');
                            }

                            if (izinAttachmentPathInput) {
                                izinAttachmentPathInput.value = '';
                            }

                            if (fileAttachmentPath !== '') {
                                $.ajax({
                                    url: leaveRequestDeleteUploadedImageUrl,
                                    type: 'POST',
                                    data: {
                                        attachment_path: fileAttachmentPath
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                            }
                        });
                    }
                };

                izinAttachmentDropzoneInstance = new Dropzone(dropzoneElement, dropzoneConfiguration);
            }

            function handleIzinFormSubmit() {
                $('#submitIzinButton').on('click', function (event) {
                    event.preventDefault();

                    var $submitButton = $(this);
                    var $izinForm = $('#izinSubmissionForm');

                    var formData = new FormData($izinForm[0]);
                    formData.delete('attachment_file');

                    if (izinAttachmentDropzoneInstance) {
                        var selectedFiles = izinAttachmentDropzoneInstance.getAcceptedFiles();
                        var uploadedAttachmentPath = izinAttachmentPathInput ? String(izinAttachmentPathInput.value || '') : '';
                        if (uploadedAttachmentPath === '' && Array.isArray(selectedFiles) && selectedFiles.length > 0) {
                            var selectedFile = selectedFiles[0];
                            formData.append('attachment_file', selectedFile, selectedFile.name || 'lampiran');
                        }
                    }

                    $.ajax({
                        url: $izinForm.attr('action'),
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
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                setTimeout(function () {
                                    $izinForm[0].reset();
                                    $('#izinDateRangeInput').val('');
                                    $('#izinStartDate').val('');
                                    $('#izinEndDate').val('');
                                    if (izinAttachmentPathInput) {
                                        izinAttachmentPathInput.value = '';
                                    }
                                    if (izinAttachmentDropzoneInstance) {
                                        izinAttachmentDropzoneInstance.removeAllFiles(true);
                                    }

                                    $('#submitIzinModal').modal('hide');
                                    leaveRequestTable.ajax.reload(null, false);
                                }, 1000);

                                return;
                            }

                            Swal.fire({
                                icon: 'warning',
                                title: 'Gagal',
                                text: response && response.message ? response.message : 'Gagal menyimpan data.'
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

                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: errorMessage
                            });
                        },
                        complete: function () {
                            $submitButton.prop('disabled', false).text('Submit Izin');
                        }
                    });
                });
            }

            $('#submitIzinModal').on('hidden.bs.modal', function () {
                if (izinAttachmentPathInput) {
                    izinAttachmentPathInput.value = '';
                }
                if (izinAttachmentDropzoneInstance) {
                    izinAttachmentDropzoneInstance.removeAllFiles(true);
                }
            });

            $('#submitIzinModal').on('shown.bs.modal', function () {
                if (!izinAttachmentDropzoneInstance) {
                    setTimeout(initIzinAttachmentDropzone, 0);
                }
            });

            initIzinDateRangePicker();
            handleIzinFormSubmit();
        });

        function buildLeaveRequestUrl(urlTemplate, permissionId) {
            return String(urlTemplate).replace('__ID__', String(permissionId || ''));
        }

        function normalizeStatusLabel(statusValue) {
            var normalizedStatus = String(statusValue || 'pending').toLowerCase();

            if (normalizedStatus === 'approved') {
                return 'Approved';
            }

            if (normalizedStatus === 'refused' || normalizedStatus === 'rejected') {
                return 'Refused';
            }

            return 'Pending';
        }

        function resolveStatusBadgeClass(statusValue) {
            var normalizedStatus = String(statusValue || 'pending').toLowerCase();

            if (normalizedStatus === 'approved') {
                return 'text-bg-success';
            }

            if (normalizedStatus === 'refused' || normalizedStatus === 'rejected') {
                return 'text-bg-danger';
            }

            return 'text-bg-warning';
        }

        function escapeHtml(value) {
            return $('<div>').text(value || '-').html();
        }

        function infoData(permissionId) {
            if (!permissionId) {
                return;
            }

            $.ajax({
                url: buildLeaveRequestUrl(leaveRequestShowUrlTemplate, permissionId),
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (response) {
                    if (!response || response.success !== true || !response.data) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal',
                            text: response && response.message ? response.message : 'Data izin tidak ditemukan.'
                        });
                        return;
                    }

                    var detailData = response.data;
                    console.log(detailData);
                    
                    var statusLabel = normalizeStatusLabel(detailData.status);
                    var statusBadgeClass = resolveStatusBadgeClass(detailData.status);
                    var attachments = Array.isArray(detailData.attachments) ? detailData.attachments : [];
                    var attachmentListHtml = '<span class="text-muted small">Tidak ada lampiran</span>';

                    if (attachments.length > 0) {
                        var attachmentItems = attachments.map(function (attachment, index) {
                            var fileUrl = String(attachment.file_url || '');
                            var normalizedFileUrl = encodeURI(fileUrl);
                            var safeFileUrl = escapeHtml(normalizedFileUrl);
                            var attachmentFileName = String(attachment.file_name || ('Lampiran ' + (index + 1)));

                            return '<div class="mb-1">'
                                + '<a href="' + safeFileUrl + '" target="_blank" rel="noopener noreferrer">' + escapeHtml(attachmentFileName) + '</a>'
                                + '</div>';
                        }).join('');

                        attachmentListHtml = '<div class="mb-0">' + attachmentItems + '</div>';
                    }

                    var detailHtml = '<div class="text-start px-2">'
                        + '<div class="table-responsive">'
                        + '<table class="table table-sm align-middle mb-0">'
                        + '<tbody>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="width: 38%; color: #334155;">Nama Staff</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.staff_name) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Tanggal</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.date_range) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Durasi</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.duration) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Tipe Izin</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.permission_type) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Status</th><td class="border-0 px-0" style="color: #1f2937;"><span class="badge ' + statusBadgeClass + '">' + statusLabel + '</span></td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Keterangan</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(detailData.reason) + '</td></tr>'
                        + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Lampiran</th><td class="border-0 px-0">' + attachmentListHtml + '</td></tr>'
                        + '</tbody>'
                        + '</table>'
                        + '</div>'
                        + '</div>';

                    Swal.fire({
                        title: 'Detail Izin ' + (detailData.permission_type || '-'),
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
                    var responseJson = xhr.responseJSON || {};
                    var errorMessage = responseJson.message || 'Gagal memproses permintaan.';

                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: errorMessage
                    });
                }
            });
        }

        function deleteData(permissionId) {
            if (!permissionId || !canDeletePermission) {
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Hapus Data Izin?',
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
                    url: buildLeaveRequestUrl(leaveRequestDestroyUrlTemplate, permissionId),
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
                            leaveRequestTable.ajax.reload(null, false);
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        var responseJson = xhr.responseJSON || {};
                        var errorMessage = responseJson.message || 'Gagal memproses permintaan.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: errorMessage
                        });
                    }
                });
            });
        }
    </script>
@endsection
