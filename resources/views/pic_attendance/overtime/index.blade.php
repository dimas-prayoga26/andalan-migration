@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .pic-overtime-table-footer.dataTables_wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 8px 30px;
            border-top: 1px solid var(--bs-border-color);
        }

        .pic-overtime-table-footer.dataTables_wrapper .dataTables_info,
        .pic-overtime-table-footer.dataTables_wrapper .dataTables_paginate {
            float: none;
            margin-bottom: 0;
            padding: 0;
        }

        .pic-overtime-table-footer .paginate_button.disabled {
            pointer-events: none;
            opacity: .35;
        }

        .overtime-summary-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 18px 32px;
        }

        .overtime-summary-card {
            min-height: 82px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
        }

        .overtime-summary-label {
            min-height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.15;
        }

        .overtime-summary-value {
            max-width: 100%;
            margin: 4px 0 0;
            font-size: 18px;
            font-weight: 600;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .pic-overtime-review-table {
            height: 286px;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-color: #c6ccd6 #f1f3f7;
            scrollbar-width: thin;
        }

        .pic-overtime-review-table::-webkit-scrollbar {
            height: 7px;
        }

        .pic-overtime-review-table::-webkit-scrollbar-track {
            background: #f1f3f7;
            border-radius: 999px;
        }

        .pic-overtime-review-table::-webkit-scrollbar-thumb {
            background: #c6ccd6;
            border-radius: 999px;
        }

        .pic-overtime-review-table::-webkit-scrollbar-thumb:hover {
            background: #aab2c0;
        }

        .overtime-review-table .table {
            min-width: 900px;
            margin-bottom: 0;
        }

        .overtime-review-table th,
        .overtime-review-table td {
            vertical-align: middle;
        }

        @media (min-width: 1200px) {
            .overtime-summary-metrics {
                grid-template-columns: repeat(9, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .pic-overtime-table-footer.dataTables_wrapper {
                flex-direction: column;
                align-items: stretch;
                padding: 18px 20px 24px;
            }

            .pic-overtime-table-footer.dataTables_wrapper .dataTables_info,
            .pic-overtime-table-footer.dataTables_wrapper .dataTables_paginate {
                justify-content: center;
                text-align: center;
            }
        }
    </style>

@endsection

@section('navbarTitle', 'Overview')

@section('content')

@include('pic_attendance.layout.navbar')

@php
    $picOvertimeTablePageSize = 5;
    $pendingTableRows = $pendingRows ?? collect();
    $approvedTableRows = $approvedRows ?? collect();
    $selectedCardMonth = $selectedCardMonth ?? ($selectedMonth ?? now('Asia/Jakarta')->month);
    $selectedCardYear = $selectedCardYear ?? ($selectedYear ?? now('Asia/Jakarta')->year);
    $selectedPendingMonth = $selectedPendingMonth ?? ($selectedMonth ?? now('Asia/Jakarta')->month);
    $selectedPendingYear = $selectedPendingYear ?? ($selectedYear ?? now('Asia/Jakarta')->year);
    $selectedApprovedMonth = $selectedApprovedMonth ?? ($selectedMonth ?? now('Asia/Jakarta')->month);
    $selectedApprovedYear = $selectedApprovedYear ?? ($selectedYear ?? now('Asia/Jakarta')->year);
    $pendingTablePageCount = max(1, (int) ceil($pendingTableRows->count() / $picOvertimeTablePageSize));
    $approvedTablePageCount = max(1, (int) ceil($approvedTableRows->count() / $picOvertimeTablePageSize));
@endphp

<!-- Start - Attendance -->
<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Overtime</h5>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-primary btn-sm me-3" data-bs-toggle="modal" data-bs-target="#picAddOvertimeModal">
                + Add Overtime
            </button>
            <ul class="nav nav-pills nav-pills-square-sm gap-2" id="myTabView" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="project-list-tab" data-bs-toggle="tab" data-bs-target="#project-list-pane" type="button" role="tab" aria-controls="project-list-pane" aria-selected="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="project-grid-tab" data-bs-toggle="tab" data-bs-target="#project-grid-pane" type="button" role="tab" aria-controls="project-grid-pane" aria-selected="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="overtime-summary-metrics mb-3">
    @foreach (($overtimeMetricCards ?? []) as $summaryCard)
        <div class="overtime-summary-card {{ $summaryCard['background_class'] ?? 'bg-light-subtle' }}">
            <span class="overtime-summary-label {{ $summaryCard['text_class'] ?? 'text-black' }}">{{ $summaryCard['label'] ?? '-' }}</span>
            <h5 class="overtime-summary-value text-black">{{ $summaryCard['value'] ?? '-' }}</h5>
        </div>
    @endforeach
</div>

<!-- Start - Billing Statement -->
<div class="tab-content mt-3" id="myTabContentView">
    <div class="tab-pane fade show active" id="project-list-pane" role="tabpanel" aria-labelledby="project-list-tab" tabindex="0">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header border-0 align-items-center gap-2 flex-wrap">
                        <h4 class="card-title m-0">Pending</h4>
                        <form class="clearfix d-flex align-items-center" method="GET" action="{{ url()->current() }}">
                            <input type="hidden" name="card_month" value="{{ $selectedCardMonth }}">
                            <input type="hidden" name="card_year" value="{{ $selectedCardYear }}">
                            <input type="hidden" name="approved_month" value="{{ $selectedApprovedMonth }}">
                            <input type="hidden" name="approved_year" value="{{ $selectedApprovedYear }}">
                            <div class="clearfix me-1">
                                <select name="pending_month" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (($monthOptions ?? []) as $monthOption)
                                        <option value="{{ $monthOption['value'] }}" @selected($selectedPendingMonth === $monthOption['value'])>{{ $monthOption['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix">
                                <select name="pending_year" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (($yearOptions ?? []) as $yearOption)
                                        <option value="{{ $yearOption }}" @selected($selectedPendingYear === $yearOption)>{{ $yearOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ms-2">
                                <div id="licenseUsageExcelBTN"></div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body table-card-body px-0 pt-0 pb-0" data-pic-overtime-table data-pic-overtime-page-size="{{ $picOvertimeTablePageSize }}">
                        <div class="table-responsive pic-overtime-review-table overtime-review-table">
                            <table class="table table-sm table-sm-responsive text-nowrap overtime-pending-table" id="tableLicenseUsage">
                                <thead>
                                    <tr>
                                        <th class="mw-10">No</th>
                                        <th class="mw-50">Datetime</th>
                                        <th class="mw-50">Name</th>
                                        <th class="mw-80">SPV</th>
                                        <th class="mw-80">Status</th>
                                        <th class="mw-10">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pendingTableRows as $row)
                                        <tr @class(['js-pic-overtime-table-row', 'd-none' => $loop->iteration > $picOvertimeTablePageSize])>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $row['datetime'] }}</td>
                                            <td>{{ $row['name'] }}</td>
                                            <td>{{ $row['supervisor'] }}</td>
                                            <td class="overtime-status-cell">
                                                <span class="{{ $row['status_class'] ?? 'text-muted' }}">
                                                    {{ $row['status'] ?? '-' }}
                                                    <i class="fa fa-info-circle ms-1"
                                                        title="Status: {{ $row['status_info'] ?? '-' }}"
                                                        aria-label="Status: {{ $row['status_info'] ?? '-' }}"></i>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown dropdown-xs">
                                                    <button class="btn btn-xs btn-light btn-square" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="{{ $row['detail_url'] }}">Edit</a></li>
                                                        <li><a class="dropdown-item" >Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No pending overtime data available for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="pic-overtime-table-footer dataTables_wrapper no-footer">
                            <div class="dataTables_info js-pic-overtime-page-summary">
                                Showing {{ $pendingTableRows->isEmpty() ? 0 : 1 }} to {{ min($picOvertimeTablePageSize, $pendingTableRows->count()) }} of {{ $pendingTableRows->count() }} entries
                            </div>
                            <div class="dataTables_paginate paging_simple_numbers js-pic-overtime-pagination">
                                <a href="javascript:void(0)" class="paginate_button previous disabled js-pic-overtime-page-button" data-pic-overtime-page-action="previous" data-pic-overtime-page-item="previous" aria-label="Previous page"><i class="fa-solid fa-angle-left"></i></a>
                                <span>
                                    @for ($page = 1; $page <= $pendingTablePageCount; $page++)
                                        <a href="javascript:void(0)" class="paginate_button {{ $page === 1 ? 'current' : '' }} js-pic-overtime-page-button" data-pic-overtime-page="{{ $page }}" data-pic-overtime-page-item="{{ $page }}" @if ($page === 1) aria-current="page" @endif>{{ $page }}</a>
                                    @endfor
                                </span>
                                <a href="javascript:void(0)" class="paginate_button next {{ $pendingTablePageCount <= 1 ? 'disabled' : '' }} js-pic-overtime-page-button" data-pic-overtime-page-action="next" data-pic-overtime-page-item="next" aria-label="Next page"><i class="fa-solid fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header border-0 align-items-center">
                        <h4 class="card-title">Status : <span class="text-success">Approved</span> </h4>
                        <form class="clearfix d-flex align-items-center" method="GET" action="{{ url()->current() }}">
                            <input type="hidden" name="card_month" value="{{ $selectedCardMonth }}">
                            <input type="hidden" name="card_year" value="{{ $selectedCardYear }}">
                            <input type="hidden" name="pending_month" value="{{ $selectedPendingMonth }}">
                            <input type="hidden" name="pending_year" value="{{ $selectedPendingYear }}">
                            <div class="clearfix me-1">
                                <select name="approved_month" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (($monthOptions ?? []) as $monthOption)
                                        <option value="{{ $monthOption['value'] }}" @selected($selectedApprovedMonth === $monthOption['value'])>{{ $monthOption['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix">
                                <select name="approved_year" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (($yearOptions ?? []) as $yearOption)
                                        <option value="{{ $yearOption }}" @selected($selectedApprovedYear === $yearOption)>{{ $yearOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ms-2">
                                <div id="licenseUsageExcelBTN"></div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body table-card-body px-0 pt-0 pb-0" data-pic-overtime-table data-pic-overtime-page-size="{{ $picOvertimeTablePageSize }}">
                        <div class="table-responsive pic-overtime-review-table overtime-review-table">
                            <table id="tableLogs" class="table table-sm table-sm-responsive text-nowrap overtime-approved-table">
                                <thead>
                                    <tr>
                                        <th class="mw-10">No</th>
                                        <th class="mw-50">Datetime</th>
                                        <th class="mw-50">Name</th>
                                        <th class="mw-80">Status</th>
                                        <th class="mw-80">Task</th>
                                        <th class="mw-80">Payout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($approvedTableRows as $row)
                                        <tr @class(['js-pic-overtime-table-row', 'd-none' => $loop->iteration > $picOvertimeTablePageSize])>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $row['datetime'] }}</td>
                                            <td>{{ $row['name'] }}</td>
                                            <td class="overtime-status-cell">
                                                <span class="{{ $row['status_class'] ?? 'text-muted' }}">
                                                    {{ $row['status'] ?? '-' }}
                                                    <i class="fa fa-info-circle ms-1"
                                                        title="Status: {{ $row['status_info'] ?? '-' }}"
                                                        aria-label="Status: {{ $row['status_info'] ?? '-' }}"></i>
                                                </span>
                                            </td>
                                            <td>{{ $row['task'] }}</td>
                                            <td>{{ $row['payout'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No approved overtime data available for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="pic-overtime-table-footer dataTables_wrapper no-footer">
                            <div class="dataTables_info js-pic-overtime-page-summary">
                                Showing {{ $approvedTableRows->isEmpty() ? 0 : 1 }} to {{ min($picOvertimeTablePageSize, $approvedTableRows->count()) }} of {{ $approvedTableRows->count() }} entries
                            </div>
                            <div class="dataTables_paginate paging_simple_numbers js-pic-overtime-pagination">
                                <a href="javascript:void(0)" class="paginate_button previous disabled js-pic-overtime-page-button" data-pic-overtime-page-action="previous" data-pic-overtime-page-item="previous" aria-label="Previous page"><i class="fa-solid fa-angle-left"></i></a>
                                <span>
                                    @for ($page = 1; $page <= $approvedTablePageCount; $page++)
                                        <a href="javascript:void(0)" class="paginate_button {{ $page === 1 ? 'current' : '' }} js-pic-overtime-page-button" data-pic-overtime-page="{{ $page }}" data-pic-overtime-page-item="{{ $page }}" @if ($page === 1) aria-current="page" @endif>{{ $page }}</a>
                                    @endfor
                                </span>
                                <a href="javascript:void(0)" class="paginate_button next {{ $approvedTablePageCount <= 1 ? 'disabled' : '' }} js-pic-overtime-page-button" data-pic-overtime-page-action="next" data-pic-overtime-page-item="next" aria-label="Next page"><i class="fa-solid fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-end mb-3 pic-overtime-card-filter">
                    <form class="clearfix d-flex align-items-center" method="GET" action="{{ url()->current() }}">
                        <input type="hidden" name="pending_month" value="{{ $selectedPendingMonth }}">
                        <input type="hidden" name="pending_year" value="{{ $selectedPendingYear }}">
                        <input type="hidden" name="approved_month" value="{{ $selectedApprovedMonth }}">
                        <input type="hidden" name="approved_year" value="{{ $selectedApprovedYear }}">
                        <div class="clearfix me-1">
                            <select name="card_month" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                @foreach (($monthOptions ?? []) as $monthOption)
                                    <option value="{{ $monthOption['value'] }}" @selected($selectedCardMonth === $monthOption['value'])>{{ $monthOption['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="clearfix">
                            <select name="card_year" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                @foreach (($yearOptions ?? []) as $yearOption)
                                    <option value="{{ $yearOption }}" @selected($selectedCardYear === $yearOption)>{{ $yearOption }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            @forelse (($overtimeCards ?? collect()) as $overtimeCard)
                <div class="col-xxl-3 col-xl-4 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="clearfix d-flex">
                                <div class="avatar avatar-sm rounded me-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-semibold">
                                    {{ strtoupper(substr($overtimeCard['employee_name'], 0, 1)) }}
                                </div>
                                <div class="clearfix">
                                    <h6 class="mb-0 fw-semibold">
                                        <a href="{{ $overtimeCard['detail_url'] }}" class="stretched-link">{{ $overtimeCard['record_number'] }}</a>
                                    </h6>
                                    <span class="small">{{ $overtimeCard['employee_name'] }}</span>
                                </div>
                            </div>
                            <p class="my-3">{{ $overtimeCard['instruction'] }}</p>
                            <div class="row py-1">
                                <div class="col-12">
                                    <span>Date :</span>
                                </div>
                                <div class="col-12">
                                    <span>{{ $overtimeCard['date_label'] }}</span> <br>
                                    @if ($overtimeCard['is_overnight'])
                                        <span class="badge badge-sm badge-primary light fw-semibold mt-1">Overnight</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row py-1">
                                <div class="col-12">
                                    <span>Time :</span>
                                </div>
                                <div class="col-12">
                                    @foreach ($overtimeCard['time_lines'] as $timeLine)
                                        <span @class(['text-decoration-line-through' => $timeLine['strike']])>{{ $timeLine['label'] }}</span> <br>
                                    @endforeach
                                </div>
                            </div>
                            <div class="row py-1">
                                <div class="col-12">
                                    <span>PIC / Supervisor :</span>
                                </div>
                                <div class="col-12">
                                    <span>{{ $overtimeCard['supervisor_name'] }}</span> <br>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center gap-2 py-1">
                                <span class="small fw-semibold">{{ $overtimeCard['current_log']['title'] }}</span>
                                <span class="badge badge-sm {{ $overtimeCard['current_log']['badge_class'] }} light fw-semibold">{{ ucfirst($overtimeCard['current_log']['status']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-muted mb-0">No overtime card data available for this period.</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <div class="tab-pane fade" id="project-grid-pane" role="tabpanel" aria-labelledby="project-grid-tab" tabindex="0">
        <div class="accordion accordion-left-indicator" id="accordion-five">
            @forelse (($picOvertimeStaffGroups ?? collect()) as $staffGroup)
                @php
                    $accordionHeadingId = 'pic-overtime-staff-heading-'.$loop->iteration;
                    $accordionCollapseId = 'pic-overtime-staff-collapse-'.$loop->iteration;
                    $isFirstStaffGroup = $loop->first;
                @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header accordion-header-primary" id="{{ $accordionHeadingId }}">
                        <button class="accordion-button {{ $isFirstStaffGroup ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $accordionCollapseId }}" aria-expanded="{{ $isFirstStaffGroup ? 'true' : 'false' }}" aria-controls="{{ $accordionCollapseId }}">
                            <span class="accordion-header-text">{{ $staffGroup['group_label'] }}</span>
                        </button>
                    </h2>
                    <div id="{{ $accordionCollapseId }}" class="accordion-collapse collapse {{ $isFirstStaffGroup ? 'show' : '' }}" aria-labelledby="{{ $accordionHeadingId }}" data-bs-parent="#accordion-five">
                        <div class="accordion-body">
                            <div class="row">
                                @foreach ($staffGroup['staff'] as $staffCard)
                                    <div class="col-xxl-3 col-xl-4 col-sm-6">
                                        <div class="card" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;">
                                            <div class="card-body text-center p-md-4">
                                                <div class="mx-auto d-inline-flex align-items-center justify-content-center position-relative mb-3 rounded-circle bg-primary-subtle text-primary fw-semibold border" style="width: 72px; height: 72px;">
                                                    {{ $staffCard['initial'] }}
                                                    <span class="fa fa-circle bored border-light text-success position-absolute bottom-0 end-0 mb-0 me-1 fs-12"></span>
                                                </div>
                                                <div class="mb-4">
                                                    <h4 class="mb-0"><a>{{ $staffCard['name'] }}</a></h4>
                                                    <p class="mb-0">{{ $staffCard['position'] }}</p>
                                                </div>
                                                <div class="text-start">
                                                    <div class="row py-2">
                                                        <div class="col-md-6 col-12">
                                                            <span>OVT This Week</span>
                                                        </div>
                                                        <div class="col-md-6 col-12">
                                                            <span class="text-gray fw-semibold">{{ $staffCard['this_week_label'] }}</span> <br>
                                                        </div>
                                                    </div>
                                                    <div class="row py-2">
                                                        <div class="col-md-6 col-12">
                                                            <span>This Month</span>
                                                        </div>
                                                        <div class="col-md-6 col-12">
                                                            <span class="text-gray fw-semibold">{{ $staffCard['this_month_label'] }}</span> <br>
                                                        </div>
                                                    </div>
                                                    <div class="row py-2">
                                                        <div class="col-md-6 col-12">
                                                            <span>This Year</span>
                                                        </div>
                                                        <div class="col-md-6 col-12">
                                                            <span class="text-gray fw-semibold">{{ $staffCard['this_year_label'] }}</span> <br>
                                                        </div>
                                                    </div>
                                                    <div class="row py-2">
                                                        <div class="col-md-6 col-12">
                                                            <span>Paid Out!</span>
                                                        </div>
                                                        <div class="col-md-6 col-12">
                                                            <span class="text-gray fw-semibold">{{ $staffCard['paid_out_label'] }}</span> <br>
                                                            <span class="text-gray">{{ $staffCard['paid_amount_label'] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="row py-2">
                                                        <div class="col-md-6 col-12">
                                                            <span>Compensatory Time</span>
                                                        </div>
                                                        <div class="col-md-6 col-12">
                                                            <span class="text-gray fw-semibold">{{ $staffCard['compensatory_time_label'] }}</span> <br>
                                                        </div>
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
                <div class="bg-white rounded p-4 text-center text-muted">
                    No staff data available under this PIC.
                </div>
            @endforelse
        </div>
    </div>
</div>
<!-- End - Billing Statement -->

@php
    $picOvertimeErrors = $errors->getBag('picOvertimeStore');
@endphp

<div class="modal fade" id="picAddOvertimeModal" tabindex="-1" aria-labelledby="picAddOvertimeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('pic-attendance.overtime.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="picAddOvertimeModalLabel">Add Overtime</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($picOvertimeErrors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($picOvertimeErrors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="pic-overtime-instruction" class="form-label">Instruction</label>
                        <textarea class="form-control @error('instruction', 'picOvertimeStore') is-invalid @enderror" id="pic-overtime-instruction" name="instruction" rows="4" required>{{ old('instruction') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="pic-overtime-date" class="form-label">Overtime Date</label>
                        <input type="hidden" id="pic-overtime-date-value" name="overtime_date" value="{{ old('overtime_date') }}" required>
                        <input type="text" class="form-control pic-overtime-date-picker @error('overtime_date', 'picOvertimeStore') is-invalid @enderror" id="pic-overtime-date" data-date-target="#pic-overtime-date-value" placeholder="Select date" autocomplete="off" readonly required>
                    </div>

                    <div class="mb-3">
                        <label for="pic-overtime-assign-staff" class="form-label">Assign Staff</label>
                        <select class="form-select @error('employee_id', 'picOvertimeStore') is-invalid @enderror" id="pic-overtime-assign-staff" name="employee_id" required>
                            <option value="">Select staff</option>
                            @foreach (($assignableStaffOptions ?? collect()) as $staffOption)
                                <option value="{{ $staffOption['id'] }}" @selected(old('employee_id') === $staffOption['id'])>{{ $staffOption['name'] }}</option>
                            @endforeach
                        </select>
                        @if (($assignableStaffOptions ?? collect())->isEmpty())
                            <div class="form-text text-danger">Tidak ada staff aktif yang berada di bawah supervisor ini.</div>
                        @endif
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" @disabled(($assignableStaffOptions ?? collect())->isEmpty())>Save Overtime</button>
                </div>
            </form>
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
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.jQuery) {
                return;
            }

            var $modal = jQuery('#picAddOvertimeModal');
            var $overtimeDate = jQuery('#pic-overtime-date');
            var $overtimeDateValue = jQuery('#pic-overtime-date-value');

            function picOvertimeTablePageSize($tableContainer) {
                return parseInt($tableContainer.attr('data-pic-overtime-page-size'), 10) || 5;
            }

            function updatePicOvertimeTablePagination($tableContainer, currentPage, totalPages, totalRows) {
                var pageSize = picOvertimeTablePageSize($tableContainer);
                var start = totalRows === 0 ? 0 : ((currentPage - 1) * pageSize) + 1;
                var end = Math.min(currentPage * pageSize, totalRows);

                $tableContainer.find('.js-pic-overtime-page-summary').text('Showing ' + start + ' to ' + end + ' of ' + totalRows + ' entries');
                $tableContainer.find('[data-pic-overtime-page-item]').removeClass('current disabled');
                $tableContainer.find('[data-pic-overtime-page]').removeAttr('aria-current');
                $tableContainer.find('[data-pic-overtime-page-item="' + currentPage + '"]')
                    .addClass('current')
                    .attr('aria-current', 'page');

                if (currentPage <= 1) {
                    $tableContainer.find('[data-pic-overtime-page-item="previous"]').addClass('disabled');
                }

                if (currentPage >= totalPages) {
                    $tableContainer.find('[data-pic-overtime-page-item="next"]').addClass('disabled');
                }
            }

            function showPicOvertimeTablePage(tableContainer, pageNumber) {
                var $tableContainer = jQuery(tableContainer);
                var $rows = $tableContainer.find('.js-pic-overtime-table-row');
                var pageSize = picOvertimeTablePageSize($tableContainer);
                var totalRows = $rows.length;
                var totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
                var currentPage = Math.min(Math.max(parseInt(pageNumber, 10) || 1, 1), totalPages);
                var startIndex = (currentPage - 1) * pageSize;
                var endIndex = startIndex + pageSize;

                $rows.each(function (index) {
                    jQuery(this).toggleClass('d-none', index < startIndex || index >= endIndex);
                });

                $tableContainer.attr('data-pic-overtime-current-page', currentPage);
                updatePicOvertimeTablePagination($tableContainer, currentPage, totalPages, totalRows);
            }

            function initPicOvertimeTablePagination() {
                jQuery('[data-pic-overtime-table]').each(function () {
                    showPicOvertimeTablePage(this, jQuery(this).attr('data-pic-overtime-current-page') || 1);
                });
            }

            function formatOvertimeDateDisplay(dateValue) {
                if (!dateValue || typeof moment === 'undefined') {
                    return '';
                }

                var date = moment(dateValue, 'YYYY-MM-DD');

                return date.isValid() ? date.format('DD/MM/YYYY') : '';
            }

            function setOvertimeDateValue(dateValue) {
                var normalizedDate = dateValue || '';

                $overtimeDateValue.val(normalizedDate);
                $overtimeDate.val(formatOvertimeDateDisplay(normalizedDate));

                if (jQuery.fn.daterangepicker && $overtimeDate.data('daterangepicker') && typeof moment !== 'undefined' && normalizedDate) {
                    var date = moment(normalizedDate, 'YYYY-MM-DD');
                    if (date.isValid()) {
                        $overtimeDate.data('daterangepicker').setStartDate(date);
                        $overtimeDate.data('daterangepicker').setEndDate(date);
                    }
                }

                $overtimeDateValue.trigger('change');
            }

            function initOvertimeDatePicker() {
                $overtimeDate.val(formatOvertimeDateDisplay($overtimeDateValue.val()));

                if (!jQuery.fn.daterangepicker) {
                    return;
                }

                $overtimeDate.daterangepicker({
                    autoApply: true,
                    autoUpdateInput: false,
                    singleDatePicker: true,
                    parentEl: '#picAddOvertimeModal',
                    locale: {
                        format: 'DD/MM/YYYY',
                        cancelLabel: 'Clear'
                    }
                });

                $overtimeDate.on('apply.daterangepicker', function (event, picker) {
                    setOvertimeDateValue(picker.startDate.format('YYYY-MM-DD'));
                });

                $overtimeDate.on('cancel.daterangepicker', function () {
                    setOvertimeDateValue('');
                });
            }

            jQuery(document).on('click', '.js-pic-overtime-page-button', function () {
                var $button = jQuery(this);

                if ($button.hasClass('disabled')) {
                    return;
                }

                var $tableContainer = $button.closest('[data-pic-overtime-table]');
                var currentPage = parseInt($tableContainer.attr('data-pic-overtime-current-page'), 10) || 1;
                var pageAction = $button.attr('data-pic-overtime-page-action');
                var targetPage = parseInt($button.attr('data-pic-overtime-page'), 10) || currentPage;

                if (pageAction === 'previous') {
                    targetPage = currentPage - 1;
                }

                if (pageAction === 'next') {
                    targetPage = currentPage + 1;
                }

                showPicOvertimeTablePage($tableContainer, targetPage);
            });

            initPicOvertimeTablePagination();

            if ($modal.length && $overtimeDate.length) {
                initOvertimeDatePicker();
            }
        });
    </script>
    @if ($picOvertimeErrors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var addOvertimeModal = document.getElementById('picAddOvertimeModal');
                if (addOvertimeModal && window.bootstrap) {
                    new bootstrap.Modal(addOvertimeModal).show();
                }
            });
        </script>
    @endif
@endsection
