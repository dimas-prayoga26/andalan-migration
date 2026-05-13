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

        #myTable tbody td {
            vertical-align: middle;
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
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .submission-list-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #25314c;
            margin-bottom: 0.6rem;
            text-align: center;
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

        .izin-filepond .filepond--root {
            margin-bottom: 0;
            font-family: inherit;
        }

        .izin-filepond .filepond--panel-root {
            border: 1px dashed #cbd5e1;
            background: #fbfdff;
        }

        .izin-filepond .filepond--drop-label {
            color: #72829f;
            font-weight: 500;
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
@include('layouts.breadcrumb', [
    'title' => 'Attendances-data',
    'current' => 'Izin / Cuti',
    'homeRoute' => 'dashboard',
])
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
                                @if($canSubmitPermission ?? false)
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#submitIzinModal"
                                >
                                    Submit Izin
                                </button>
                                @endif
                            </div>
                        </div>
                        <div id="izinSummaryCarousel" class="izin-summary-row owl-carousel">
                            @foreach(($summaryCards ?? []) as $summaryCard)
                                <div class="izin-summary-item">
                                    <div class="izin-summary-card">
                                        <span class="izin-summary-label">{{ $summaryCard['label'] ?? '-' }}</span>
                                        <span class="izin-summary-value">{{ $summaryCard['value'] ?? 0 }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="submission-list-title">List Pengajuan</div>
                        @if($canFilterEmployees ?? false)
                            <div class="row mb-3">
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
                        <div>
                            <table id="myTable" class="display table">
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
        </div>
    </div>
</div>

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
                    <div class="mb-3 izin-filepond">
                        <label class="izin-form-label" for="izinAttachments">Lampiran (Opsional)</label>
                        <input
                            class="form-control"
                            type="file"
                            id="izinAttachments"
                            name="attachment_files[]"
                            accept=".jpg,.jpeg,.png,.pdf"
                            multiple
                        >
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
    <script>
        var leaveRequestShowUrlTemplate = '{{ route('absensi.izin.show', ['leaveRequest' => '__ID__']) }}';
        var leaveRequestDestroyUrlTemplate = '{{ route('absensi.izin.destroy', ['leaveRequest' => '__ID__']) }}';
        var leaveRequestUpdateStatusUrlTemplate = '{{ route('absensi.izin.update-status', ['leaveRequest' => '__ID__']) }}';
        var canUpdatePermissionStatus = @json($canUpdatePermissionStatus ?? false);

        $(function () {
            var attendanceDateElement = document.getElementById('attendanceDateTime');
            var izinAttachmentPond = null;
            var attendanceStaffFilter = document.getElementById('attendanceStaffFilter');

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

            var leaveRequestTable = $('#myTable').DataTable({
                ajax: {
                    url: '{{ route('absensi.izin.datatable') }}',
                    data: function (requestData) {
                        requestData.staff_user_id = attendanceStaffFilter ? attendanceStaffFilter.value : '';
                    },
                    dataSrc: 'data'
                },
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
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
                            var permissionId = row && row.id ? row.id : '';

                            return '<div class="izin-action-group">'
                                + '<button type="button" class="izin-action-btn info" onclick="infoData(' + permissionId + ')"><i class="bi bi-info-circle"></i></button>'
                                + '<button type="button" class="izin-action-btn delete" onclick="deleteData(' + permissionId + ')"><i class="bi bi-trash"></i></button>'
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

            leaveRequestTable.on('draw', function () {
                var tableContainer = $(leaveRequestTable.table().container());
                tableContainer.find('.dt-scroll-body').css({
                    overflowX: 'auto',
                    overflowY: 'hidden',
                    WebkitOverflowScrolling: 'touch'
                });
                leaveRequestTable.columns.adjust();
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

            if (attendanceStaffFilter) {
                attendanceStaffFilter.addEventListener('change', function () {
                    leaveRequestTable.ajax.reload();
                });
            }

            $('#myTable').on('change', '.permission-status-select', function () {
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
                        approval_status: selectedStatus
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

            if ($('#izinSummaryCarousel').length && typeof $.fn.owlCarousel === 'function') {
                $('#izinSummaryCarousel').owlCarousel({
                    items: 1,
                    slideBy: 1,
                    autoWidth: false,
                    nav: false,
                    dots: false,
                    smartSpeed: 450,
                    touchDrag: true,
                    mouseDrag: true,
                    checkVisible: false,
                    responsiveRefreshRate: 100,
                    responsive: {
                        0: {
                            items: 1,
                            slideBy: 1,
                            margin: 8,
                            nav: false
                        },
                        768: {
                            items: 2,
                            slideBy: 1,
                            margin: 10,
                            nav: false
                        },
                        1200: {
                            items: 4,
                            slideBy: 1,
                            margin: 12,
                            nav: false
                        }
                    }
                });
            }

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

            function initIzinAttachmentFilePond() {
                var attachmentInput = document.getElementById('izinAttachments');

                if (!attachmentInput || typeof FilePond === 'undefined') {
                    return;
                }

                izinAttachmentPond = FilePond.create(attachmentInput, {
                    allowMultiple: true,
                    instantUpload: false,
                    credits: false,
                    labelIdle: 'Drop file di sini atau <span class="filepond--label-action">Browse</span>',
                    labelFileProcessingError: 'Terjadi kesalahan saat memproses file',
                    labelTapToUndo: 'Tap untuk batal',
                    labelTapToCancel: 'Tap untuk batal',
                    labelTapToRetry: 'Tap untuk coba lagi'
                });
            }

            function handleIzinFormSubmit() {
                $('#submitIzinButton').on('click', function (event) {
                    event.preventDefault();

                    var $submitButton = $(this);
                    var $izinForm = $('#izinSubmissionForm');

                    var formData = new FormData($izinForm[0]);
                    formData.delete('attachment_files');
                    formData.delete('attachment_files[]');

                    if (izinAttachmentPond) {
                        var selectedAttachmentFiles = izinAttachmentPond.getFiles();

                        selectedAttachmentFiles.forEach(function (pondFileItem) {
                            if (pondFileItem && pondFileItem.file) {
                                formData.append('attachment_files[]', pondFileItem.file, pondFileItem.file.name);
                            }
                        });
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
                                    if (izinAttachmentPond) {
                                        izinAttachmentPond.removeFiles();
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
                if (izinAttachmentPond) {
                    izinAttachmentPond.removeFiles();
                }
            });

            initIzinAttachmentFilePond();
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

                            return '<div class="mb-1">'
                                + '<a href="' + safeFileUrl + '" target="_blank" rel="noopener noreferrer">Gambar ' + (index + 1) + '</a>'
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
            if (!permissionId) {
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
                            $('#myTable').DataTable().ajax.reload(null, false);
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
