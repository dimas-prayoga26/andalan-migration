@extends('layouts.main')

@section('title', 'Leave')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
@endsection

@section('navbarTitle', 'Overview')

@section('content')
    @include('pic_attendance.layout.navbar')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Leave</h5>
        <div class="btn-group" role="group" aria-label="Leave view">
            <button type="button" id="project-list-tab" class="btn btn-primary btn-sm" aria-label="List view" aria-pressed="true">
                <i class="bi bi-list-ul"></i>
            </button>
            <button type="button" id="project-grid-tab" class="btn btn-light btn-sm" aria-label="Grid view" aria-pressed="false">
                <i class="bi bi-grid"></i>
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-4 col-4 mb-3">
                    <div class="bg-dark-subtle rounded px-3 py-2 text-center">
                        <span class="fs-14 text-black fw-semibold fw-semibold">Pending</span>
                        <h5 class="mb-0 fw-semibold">{{ $leaveOverviewStats['pending'] }} request</h5>
                    </div>
                </div>
                <div class="col-md-4 col-4 mb-3">
                    <div class="bg-danger-subtle rounded px-3 py-2 text-center">
                        <span class="fs-14 text-danger fw-semibold">Rejected</span>
                        <h5 class="mb-0 fw-semibold">{{ $leaveOverviewStats['rejected'] }} request</h5>
                    </div>
                </div>
                <div class="col-md-4 col-4 mb-3">
                    <div class="bg-success-subtle rounded px-3 py-2 text-center">
                        <span class="fs-14 text-success fw-semibold">Approved</span>
                        <h5 class="mb-0 fw-semibold">{{ $leaveOverviewStats['approved'] }} request</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-2 col-4 mb-3">
                    <div class="bg-danger-subtle rounded px-3 py-2 text-center">
                        <span class="fs-14 text-danger fw-semibold">This Week</span>
                        <h5 class="mb-0 fw-semibold">{{ $leaveOverviewStats['this_week'] }} request</h5>
                    </div>
                </div>
                <div class="col-md-2 col-4 mb-3">
                    <div class="bg-info-subtle rounded px-3 py-2 text-center">
                        <span class="fs-14 text-info fw-semibold fw-semibold">Next Week</span>
                        <h5 class="mb-0 fw-semibold">{{ $leaveOverviewStats['next_week'] }} request</h5>
                    </div>
                </div>
                <div class="col-md-2 col-4 mb-3">
                    <div class="bg-primary-subtle rounded px-3 py-2 text-center">
                        <span class="fs-14 text-primary fw-semibold">Annual LV</span>
                        <h5 class="mb-0 fw-semibold">{{ $leaveOverviewStats['annual'] }} request</h5>
                    </div>
                </div>
                <div class="col-md-2 col-4 mb-3">
                    <div class="bg-warning-subtle rounded px-3 py-2 text-center">
                        <span class="fs-14 text-warning fw-semibold fw-semibold">Sick LV</span>
                        <h5 class="mb-0 fw-semibold">{{ $leaveOverviewStats['sick'] }} request</h5>
                    </div>
                </div>
                <div class="col-md-2 col-4 mb-3">
                    <div class="bg-secondary-subtle rounded px-3 py-2 text-center">
                        <span class="fs-14 text-secondary fw-semibold">Special LV</span>
                        <h5 class="mb-0 fw-semibold">{{ $leaveOverviewStats['special'] }} request</h5>
                    </div>
                </div>
                <div class="col-md-2 col-4 mb-3">
                    <div class="bg-light-subtle rounded px-3 py-2 text-center">
                        <span class="fs-14 text-black fw-semibold">Unpaid LV</span>
                        <h5 class="mb-0 fw-semibold">{{ $leaveOverviewStats['unpaid'] }} request</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="project-list-pane" class="row g-4">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header border-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h4 class="card-title m-0">Pending <span class="text-danger">(Supervisor Review)</span></h4>
                    <div class="d-flex gap-2">
                        <select id="adminLeavePendingMonth" class="form-select form-select-sm" aria-label="Pending month">
                            @foreach ($leaveMonthOptions as $month)
                                <option value="{{ $month['value'] }}" @selected($month['value'] === $leaveSelectedMonth)>{{ $month['label'] }}</option>
                            @endforeach
                        </select>
                        <select id="adminLeavePendingYear" class="form-select form-select-sm" aria-label="Pending year">
                            @foreach ($leaveYearOptions as $year)
                                <option value="{{ $year }}" @selected($year === $leaveSelectedYear)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body table-card-body px-0 pt-0 pb-2">
                    <div class="table-responsive">
                        <table id="adminLeavePendingTable" class="table table-sm table-sm-responsive text-nowrap mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header border-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h4 class="card-title m-0">Status : <span class="text-success">Supervisor Approved</span></h4>
                    <div class="d-flex gap-2">
                        <select id="adminLeaveApprovedMonth" class="form-select form-select-sm" aria-label="Approved month">
                            @foreach ($leaveMonthOptions as $month)
                                <option value="{{ $month['value'] }}" @selected($month['value'] === $leaveSelectedMonth)>{{ $month['label'] }}</option>
                            @endforeach
                        </select>
                        <select id="adminLeaveApprovedYear" class="form-select form-select-sm" aria-label="Approved year">
                            @foreach ($leaveYearOptions as $year)
                                <option value="{{ $year }}" @selected($year === $leaveSelectedYear)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body table-card-body px-0 pt-0 pb-2">
                    <div class="table-responsive">
                        <table id="adminLeaveApprovedTable" class="table table-sm table-sm-responsive text-nowrap mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="d-none" id="project-grid-pane" role="tabpanel" aria-labelledby="project-grid-tab" tabindex="0">
        <div class="accordion accordion-left-indicator" id="accordion-five">
            @forelse ($leaveGridPositionGroups as $positionIndex => $positionGroup)
                @php
                    $headingId = 'leavePositionHeading'.$positionIndex;
                    $collapseId = 'leavePositionCollapse'.$positionIndex;
                    $isFirstPosition = $positionIndex === 0;
                @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header accordion-header-primary" id="{{ $headingId }}">
                        <button class="accordion-button {{ $isFirstPosition ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $isFirstPosition ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                            <span class="accordion-header-text">{{ $positionGroup['position_name'] }}</span>
                        </button>
                    </h2>
                    <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $isFirstPosition ? 'show' : '' }}" aria-labelledby="{{ $headingId }}" data-bs-parent="#accordion-five">
                        <div class="accordion-body">
                            <div class="row">
                                @foreach ($positionGroup['employees'] as $gridEmployee)
                                    <div class="col-xxl-3 col-xl-4 col-sm-6">
                                        <div class="card" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;">
                                            <div class="card-body text-center p-md-4">
                                                <div class="mx-auto d-inline-flex align-items-center justify-content-center position-relative mb-3 avatar avatar-md rounded-circle bg-primary-subtle text-primary fw-semibold">
                                                    {{ $gridEmployee['initial'] }}
                                                    <span class="fa fa-circle bored border-light text-success position-absolute bottom-0 end-0 mb-0 me-1 fs-12"></span>
                                                </div>
                                                <div class="mb-4">
                                                    <h4 class="mb-0">{{ $gridEmployee['name'] }}</h4>
                                                    <p class="mb-0">{{ $gridEmployee['department_name'] }}</p>
                                                </div>
                                                <div class="text-start">
                                                    <div class="row py-2">
                                                        <div class="col-md-6 col-12"><span>Annual Leave</span></div>
                                                        <div class="col-md-6 col-12">
                                                            <span class="text-gray fw-semibold">{{ $gridEmployee['annual_days_label'] }}</span><br>
                                                            @if ($gridEmployee['annual_start_label'])
                                                                <span class="badge badge-xs badge-success light fw-semibold">{{ $gridEmployee['annual_start_label'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="row py-2">
                                                        <div class="col-md-6 col-12"><span>Sick Leave</span></div>
                                                        <div class="col-md-6 col-12"><span class="text-gray fw-semibold">{{ $gridEmployee['sick_days_label'] }}</span></div>
                                                    </div>
                                                    <div class="row py-2">
                                                        <div class="col-md-6 col-12"><span>Special Leave</span></div>
                                                        <div class="col-md-6 col-12"><span class="text-gray fw-semibold">{{ $gridEmployee['special_days_label'] }}</span></div>
                                                    </div>
                                                    <div class="row py-2">
                                                        <div class="col-md-6 col-12"><span>Unpaid Leave</span></div>
                                                        <div class="col-md-6 col-12"><span class="text-gray fw-semibold">{{ $gridEmployee['unpaid_days_label'] }}</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="accordion-item">
                    <div class="accordion-body text-center text-muted py-4">No active employee data available.</div>
                </div>
            @endforelse
        </div>
    </div>
    <div id="project-list-card-pane" class="row g-4 mt-1">
        @forelse ($leavePendingCards as $leavePendingCard)
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="clearfix d-flex align-items-center">
                            <div class="avatar avatar-sm rounded me-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-semibold">
                                {{ $leavePendingCard['initial'] }}
                            </div>
                            <div class="clearfix">
                                <h6 class="mb-0 fw-semibold">
                                    <a href="{{ $leavePendingCard['detail_url'] }}" class="stretched-link text-dark">{{ $leavePendingCard['name'] }}</a>
                                </h6>
                                <span class="small text-muted">{{ $leavePendingCard['leave_type'] }}</span>
                            </div>
                        </div>
                        <p class="my-3">{{ $leavePendingCard['reason'] }}</p>
                        <div class="row py-1">
                            <div class="col-12">
                                <span>Date :</span>
                            </div>
                            <div class="col-12">
                                <span>{{ $leavePendingCard['date_label'] }}</span>
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-12">
                                <span>PIC / Supervisor :</span>
                            </div>
                            <div class="col-12">
                                <span>{{ $leavePendingCard['supervisor_name'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-warning">Supervisor Review</span>
                        <span class="badge badge-sm badge-warning light fw-semibold">Pending</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted py-4">No pending leave request data available.</div>
                </div>
            </div>
        @endforelse
    </div>
@endsection

@section('script')
    @php
        $dashboardJsPath = public_path('assets/js/dashboard.js');
        $dashboardJsVersion = file_exists($dashboardJsPath) ? filemtime($dashboardJsPath) : time();
        $dataTablesJsPath = public_path('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js');
        $dataTablesJsVersion = file_exists($dataTablesJsPath) ? filemtime($dataTablesJsPath) : time();
    @endphp
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.bundle.min.js') }}?v={{ $dataTablesJsVersion }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var listViewButton = document.getElementById('project-list-tab');
            var gridViewButton = document.getElementById('project-grid-tab');
            var listPane = document.getElementById('project-list-pane');
            var listCardPane = document.getElementById('project-list-card-pane');
            var gridPane = document.getElementById('project-grid-pane');

            function setLeaveView(view) {
                var isGridView = view === 'grid';

                listPane.classList.toggle('d-none', isGridView);
                listCardPane.classList.toggle('d-none', isGridView);
                gridPane.classList.toggle('d-none', !isGridView);
                listViewButton.classList.toggle('btn-primary', !isGridView);
                listViewButton.classList.toggle('btn-light', isGridView);
                gridViewButton.classList.toggle('btn-primary', isGridView);
                gridViewButton.classList.toggle('btn-light', !isGridView);
                listViewButton.setAttribute('aria-pressed', String(!isGridView));
                gridViewButton.setAttribute('aria-pressed', String(isGridView));
            }

            listViewButton.addEventListener('click', function () {
                setLeaveView('list');
            });
            gridViewButton.addEventListener('click', function () {
                setLeaveView('grid');
            });

            if (!window.jQuery || !window.jQuery.fn.DataTable) {
                return;
            }

            var $ = window.jQuery;

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderTypeBadge(row) {
                var type = escapeHtml(row.type || 'Leave');
                var badge = escapeHtml(row.type_badge || 'primary');

                return '<span class="badge badge-xs badge-outline-' + badge + ' fw-semibold">' + type + '</span>';
            }

            function renderDetailAction(row) {
                if (!row.detail_url) {
                    return '-';
                }

                return '<a href="' + escapeHtml(row.detail_url) + '" class="btn btn-xs btn-light btn-square" aria-label="View leave request">'
                    + '<i class="bi bi-pencil"></i></a>';
            }

            function createLeaveTable(tableSelector, endpoint, monthSelector, yearSelector) {
                var table = $(tableSelector).DataTable({
                    ajax: {
                        url: endpoint,
                        data: function (requestData) {
                            requestData.month = $(monthSelector).val();
                            requestData.year = $(yearSelector).val();
                        },
                        dataSrc: 'data'
                    },
                    autoWidth: false,
                    searching: false,
                    pageLength: 5,
                    lengthChange: false,
                    paging: true,
                    info: true,
                    columns: [
                        { data: 'no', defaultContent: '' },
                        { data: 'date', defaultContent: '-' },
                        { data: 'name', defaultContent: '-' },
                        { data: 'type', defaultContent: '-' },
                        { data: 'detail_url', defaultContent: '' }
                    ],
                    columnDefs: [
                        {
                            targets: 3,
                            render: function (data, type, row) {
                                if (type === 'sort' || type === 'type') {
                                    return row.type || '';
                                }

                                return renderTypeBadge(row);
                            }
                        },
                        {
                            targets: 4,
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row) {
                                return type === 'sort' || type === 'type' ? '' : renderDetailAction(row);
                            }
                        }
                    ],
                    language: {
                        emptyTable: 'No leave request data available for this period.',
                        paginate: {
                            next: '<i class="fa-solid fa-angle-right"></i>',
                            previous: '<i class="fa-solid fa-angle-left"></i>'
                        }
                    }
                });

                $(monthSelector + ', ' + yearSelector).on('change', function () {
                    table.ajax.reload();
                });

                return table;
            }

            createLeaveTable(
                '#adminLeavePendingTable',
                '{{ route('pic-attendance.leave.pending-datatable') }}',
                '#adminLeavePendingMonth',
                '#adminLeavePendingYear'
            );
            createLeaveTable(
                '#adminLeaveApprovedTable',
                '{{ route('pic-attendance.leave.approved-datatable') }}',
                '#adminLeaveApprovedMonth',
                '#adminLeaveApprovedYear'
            );
        });
    </script>
@endsection
