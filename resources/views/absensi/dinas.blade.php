@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        #myTable thead th {
            font-size: 1.1rem;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }

        #myTable tbody td {
            font-size: 1rem;
            vertical-align: middle;
        }

        #myTable thead th:first-child,
        #myTable tbody td:first-child {
            text-align: center !important;
        }

        #myTable thead th[class*="mw-"] {
            text-align: center !important;
        }

        #myTable.dataTable tbody td.dataTables_empty {
            text-align: center !important;
        }

        .dinas-table-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #25314c;
            margin-bottom: 2rem;
            text-align: left;
        }

        .dinas-status-badge {
            display: inline-block;
            border-radius: 0.4rem;
            padding: 0.25rem 0.6rem;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.2;
            border: 1px solid transparent;
        }

        .dinas-status-badge.warning {
            background: #fff7e6;
            border-color: #ffd591;
            color: #ad6800;
        }

        .dinas-status-badge.success {
            background: #f6ffed;
            border-color: #b7eb8f;
            color: #237804;
        }

        .dinas-status-badge.danger {
            background: #fff1f0;
            border-color: #ffa39e;
            color: #a8071a;
        }

        .dinas-action-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }

        .dinas-action-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .dinas-action-btn.info {
            background: #d9f2f4;
            color: #4aa3ad;
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

        #myTable_wrapper .dt-scroll,
        #myTable_wrapper .dataTables_scroll {
            overflow: hidden;
        }

        #myTable_wrapper .dt-scroll-head table,
        #myTable_wrapper .dt-scroll-body table,
        #myTable_wrapper .dataTables_scrollHead table,
        #myTable_wrapper .dataTables_scrollBody table {
            min-width: 1240px;
        }

        #myTable_wrapper .dt-scroll-body,
        #myTable_wrapper .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
        }

        #myTable_wrapper .dt-scroll-body thead,
        #myTable_wrapper .dataTables_scrollBody thead {
            visibility: hidden !important;
        }

        #myTable_wrapper .dt-scroll-body thead tr,
        #myTable_wrapper .dt-scroll-body thead th,
        #myTable_wrapper .dataTables_scrollBody thead tr,
        #myTable_wrapper .dataTables_scrollBody thead th {
            height: 0 !important;
            line-height: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-top: 0 !important;
            border-bottom: 0 !important;
            margin: 0 !important;
        }

        #myTable_wrapper table.dataTable thead > tr > th span.dt-column-order {
            display: none !important;
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

@section('navbarTitle', 'Attendances')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Perjalanan Dinas',
    'homeRoute' => 'dashboard',
])

@include('absensi.layouts_absensi.profileIndex')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Perjalanan Dinas</h5>
        <div class="d-flex align-items-center">
            <button
                type="button"
                id="openSubmitLemburModalButton"
                class="me-2 btn btn-success light btn-sm"
            >Submit Dinas</button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12 col-xxl-12">
        <div class="card h-auto">
            <div class="row">
                <div class="col-xxl-12 col-xl-12">
                    <div class="card-body">
                        <div class="dinas-table-title">Data Perjalanan Dinas</div>
                        <div class="table-responsive">
                            <table id="myTable" class="display table">
                                <thead>
                                    <tr>
                                        <th class="mw-80">No</th>
                                        <th class="mw-150">Staff</th>
                                        <th class="mw-150">PIC</th>
                                        <th class="mw-210">Tanggal Dinas</th>
                                        <th class="mw-150">Total Hari</th>
                                        <th class="mw-200">Tujuan</th>
                                        <th class="mw-200">Keperluan</th>
                                        <th class="mw-170">Approval</th>
                                        <th class="mw-170">Pembayaran</th>
                                        <th class="mw-250">Payment Reference</th>
                                        <th class="mw-120 text-center">Action</th>
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
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
    @endphp
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        var businessTripTableInstance;

        function renderStatusBadge(statusValue) {
            var normalized = String(statusValue || 'pending').toLowerCase();
            var badgeClass = 'warning';
            var badgeLabel = 'Pending';

            if (normalized === 'approved' || normalized === 'paid') {
                badgeClass = 'success';
                badgeLabel = normalized === 'paid' ? 'Paid' : 'Approved';
            }

            if (normalized === 'rejected' || normalized === 'failed' || normalized === 'cancelled') {
                badgeClass = 'danger';
                badgeLabel = normalized === 'rejected' ? 'Rejected' : 'Failed';
            }

            return '<span class="dinas-status-badge ' + badgeClass + '">' + badgeLabel + '</span>';
        }

        function escapeHtml(value) {
            return $('<div>').text(value || '-').html();
        }

        function infoDataBusinessTrip(rowData) {
            if (!rowData) {
                return;
            }

            var detailHtml = '<div class="text-start px-2">'
                + '<div class="table-responsive">'
                + '<table class="table table-sm align-middle mb-0">'
                + '<tbody>'
                + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="width: 38%; color: #334155;">Staff</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(rowData.staff_name) + '</td></tr>'
                + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">PIC</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(rowData.pic_name) + '</td></tr>'
                + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Tanggal Dinas</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(rowData.trip_date) + '</td></tr>'
                + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Total Hari</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(rowData.total_days) + '</td></tr>'
                + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Tujuan</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(rowData.destination) + '</td></tr>'
                + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Keperluan</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(rowData.purpose) + '</td></tr>'
                + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Approval</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(rowData.approval_status) + '</td></tr>'
                + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Pembayaran</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(rowData.payment_status) + '</td></tr>'
                + '<tr><th class="fw-semibold border-0 ps-0 pe-3" style="color: #334155;">Payment Reference</th><td class="border-0 px-0" style="color: #1f2937;">' + escapeHtml(rowData.payment_reference) + '</td></tr>'
                + '</tbody>'
                + '</table>'
                + '</div>'
                + '</div>';

            Swal.fire({
                title: 'Detail Perjalanan Dinas',
                html: detailHtml,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#475569',
                customClass: {
                    popup: 'p-3',
                    title: 'mb-2'
                }
            });
        }

        $(function () {
            var businessTripDatatableUrl = '{{ route('absensi.dinas.datatable') }}';

            $('.absensi-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });

            function ensureEmptyPageOne(tableApi) {
                var tableWrapper = $(tableApi.table().container());
                var pageInfo = tableApi.page.info();

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

            businessTripTableInstance = $('#myTable').DataTable({
                ajax: {
                    url: businessTripDatatableUrl,
                    dataSrc: 'data',
                    error: function (xhr) {
                        var responseJson = xhr.responseJSON || {};
                        var errorMessage = responseJson.message || 'Gagal memproses permintaan.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: errorMessage
                        });
                    }
                },
                processing: true,
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                searching: false,
                lengthChange: false,
                columns: [
                    { data: null, defaultContent: '' },
                    { data: 'staff_name' },
                    { data: 'pic_name' },
                    { data: 'trip_date' },
                    { data: 'total_days' },
                    { data: 'destination' },
                    { data: 'purpose' },
                    {
                        data: 'approval_status',
                        render: function (data) {
                            return renderStatusBadge(data);
                        }
                    },
                    {
                        data: 'payment_status',
                        render: function (data) {
                            return renderStatusBadge(data);
                        }
                    },
                    { data: 'payment_reference' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function () {
                            return '<div class="dinas-action-group">'
                                + '<button type="button" class="dinas-action-btn info js-dinas-info"><i class="bi bi-info-circle"></i></button>'
                                + '</div>';
                        }
                    }
                ],
                columnDefs: [
                    {
                        targets: '_all',
                        className: 'text-center'
                    },
                    {
                        targets: 0,
                        searchable: false,
                        orderable: false
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
                },
                drawCallback: function () {
                    ensureEmptyPageOne(this.api());
                }
            });

            businessTripTableInstance.on('order.dt search.dt draw.dt', function () {
                var pageInfo = businessTripTableInstance.page.info();
                businessTripTableInstance.column(0, { page: 'current' }).nodes().each(function (cell, index) {
                    cell.innerHTML = pageInfo.start + index + 1;
                });
            });

            businessTripTableInstance.on('draw', function () {
                businessTripTableInstance.columns.adjust();
            });

            $(window).on('resize', function () {
                businessTripTableInstance.columns.adjust();
            });

            $('#myTable').on('click', '.js-dinas-info', function () {
                var rowData = businessTripTableInstance.row($(this).closest('tr')).data();
                infoDataBusinessTrip(rowData);
            });
        });
    </script>
@endsection
