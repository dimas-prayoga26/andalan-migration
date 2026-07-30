@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <style>
        .admin-overtime-table-footer.dataTables_wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 8px 30px;
            border-top: 1px solid var(--bs-border-color);
        }

        .admin-overtime-review-table {
            height: 286px;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .overtime-review-table .table {
            min-width: 760px;
            margin-bottom: 0;
        }

        .overtime-review-table th,
        .overtime-review-table td {
            vertical-align: middle;
        }

        .admin-overtime-table-footer.dataTables_wrapper .dataTables_info,
        .admin-overtime-table-footer.dataTables_wrapper .dataTables_paginate {
            float: none;
            margin-bottom: 0;
            padding: 0;
        }

        .admin-overtime-table-footer .paginate_button.disabled {
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

        @media (min-width: 1200px) {
            .overtime-summary-metrics {
                grid-template-columns: repeat(9, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .admin-overtime-table-footer.dataTables_wrapper {
                flex-direction: column;
                align-items: stretch;
                padding: 18px 20px 24px;
            }

            .admin-overtime-table-footer.dataTables_wrapper .dataTables_info,
            .admin-overtime-table-footer.dataTables_wrapper .dataTables_paginate {
                justify-content: center;
                text-align: center;
            }
        }
    </style>

@endsection

@section('navbarTitle', 'Overview')

@section('content')

@include('admin_attendance.layout.navbar')

@php
    $adminOvertimeTablePageSize = 5;
    $pendingTableRows = $pendingRows ?? collect();
    $completeTableRows = $approvedRows ?? collect();
    $selectedCardMonth = $selectedCardMonth ?? ($selectedMonth ?? now('Asia/Jakarta')->month);
    $selectedCardYear = $selectedCardYear ?? ($selectedYear ?? now('Asia/Jakarta')->year);
    $selectedPendingMonth = $selectedPendingMonth ?? ($selectedMonth ?? now('Asia/Jakarta')->month);
    $selectedPendingYear = $selectedPendingYear ?? ($selectedYear ?? now('Asia/Jakarta')->year);
    $selectedCompleteMonth = $selectedCompleteMonth ?? ($selectedMonth ?? now('Asia/Jakarta')->month);
    $selectedCompleteYear = $selectedCompleteYear ?? ($selectedYear ?? now('Asia/Jakarta')->year);
    $pendingTablePageCount = max(1, (int) ceil($pendingTableRows->count() / $adminOvertimeTablePageSize));
    $completeTablePageCount = max(1, (int) ceil($completeTableRows->count() / $adminOvertimeTablePageSize));
@endphp

<!-- Start - Attendance -->
<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Overtime</h5>
        <div class="d-flex align-items-center">
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
                            <input type="hidden" name="complete_month" value="{{ $selectedCompleteMonth }}">
                            <input type="hidden" name="complete_year" value="{{ $selectedCompleteYear }}">
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
                    <div class="card-body table-card-body px-0 pt-0 pb-0" data-admin-overtime-table data-admin-overtime-page-size="{{ $adminOvertimeTablePageSize }}">
                        <div class="table-responsive admin-overtime-review-table overtime-review-table">
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
                                        <tr @class(['js-admin-overtime-table-row', 'd-none' => $loop->iteration > $adminOvertimeTablePageSize])>
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
                        <div class="admin-overtime-table-footer dataTables_wrapper no-footer">
                            <div class="dataTables_info js-admin-overtime-page-summary">
                                Showing {{ $pendingTableRows->isEmpty() ? 0 : 1 }} to {{ min($adminOvertimeTablePageSize, $pendingTableRows->count()) }} of {{ $pendingTableRows->count() }} entries
                            </div>
                            <div class="dataTables_paginate paging_simple_numbers js-admin-overtime-pagination">
                                <a href="javascript:void(0)" class="paginate_button previous disabled js-admin-overtime-page-button" data-admin-overtime-page-action="previous" data-admin-overtime-page-item="previous" aria-label="Previous page"><i class="fa-solid fa-angle-left"></i></a>
                                <span>
                                    @for ($page = 1; $page <= $pendingTablePageCount; $page++)
                                        <a href="javascript:void(0)" class="paginate_button {{ $page === 1 ? 'current' : '' }} js-admin-overtime-page-button" data-admin-overtime-page="{{ $page }}" data-admin-overtime-page-item="{{ $page }}" @if ($page === 1) aria-current="page" @endif>{{ $page }}</a>
                                    @endfor
                                </span>
                                <a href="javascript:void(0)" class="paginate_button next {{ $pendingTablePageCount <= 1 ? 'disabled' : '' }} js-admin-overtime-page-button" data-admin-overtime-page-action="next" data-admin-overtime-page-item="next" aria-label="Next page"><i class="fa-solid fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header border-0 align-items-center">
                        <h4 class="card-title">Status : <span class="text-success">Complete</span> </h4>
                        <form class="clearfix d-flex align-items-center" method="GET" action="{{ url()->current() }}">
                            <input type="hidden" name="card_month" value="{{ $selectedCardMonth }}">
                            <input type="hidden" name="card_year" value="{{ $selectedCardYear }}">
                            <input type="hidden" name="pending_month" value="{{ $selectedPendingMonth }}">
                            <input type="hidden" name="pending_year" value="{{ $selectedPendingYear }}">
                            <div class="clearfix me-1">
                                <select name="complete_month" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (($monthOptions ?? []) as $monthOption)
                                        <option value="{{ $monthOption['value'] }}" @selected($selectedCompleteMonth === $monthOption['value'])>{{ $monthOption['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix">
                                <select name="complete_year" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (($yearOptions ?? []) as $yearOption)
                                        <option value="{{ $yearOption }}" @selected($selectedCompleteYear === $yearOption)>{{ $yearOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ms-2">
                                <div id="licenseUsageExcelBTN"></div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body table-card-body px-0 pt-0 pb-0" data-admin-overtime-table data-admin-overtime-page-size="{{ $adminOvertimeTablePageSize }}">
                        <div class="table-responsive admin-overtime-review-table overtime-review-table">
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
                                    @forelse ($completeTableRows as $row)
                                        <tr @class(['js-admin-overtime-table-row', 'd-none' => $loop->iteration > $adminOvertimeTablePageSize])>
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
                                            <td colspan="6" class="text-center text-muted">No complete overtime data available for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-overtime-table-footer dataTables_wrapper no-footer">
                            <div class="dataTables_info js-admin-overtime-page-summary">
                                Showing {{ $completeTableRows->isEmpty() ? 0 : 1 }} to {{ min($adminOvertimeTablePageSize, $completeTableRows->count()) }} of {{ $completeTableRows->count() }} entries
                            </div>
                            <div class="dataTables_paginate paging_simple_numbers js-admin-overtime-pagination">
                                <a href="javascript:void(0)" class="paginate_button previous disabled js-admin-overtime-page-button" data-admin-overtime-page-action="previous" data-admin-overtime-page-item="previous" aria-label="Previous page"><i class="fa-solid fa-angle-left"></i></a>
                                <span>
                                    @for ($page = 1; $page <= $completeTablePageCount; $page++)
                                        <a href="javascript:void(0)" class="paginate_button {{ $page === 1 ? 'current' : '' }} js-admin-overtime-page-button" data-admin-overtime-page="{{ $page }}" data-admin-overtime-page-item="{{ $page }}" @if ($page === 1) aria-current="page" @endif>{{ $page }}</a>
                                    @endfor
                                </span>
                                <a href="javascript:void(0)" class="paginate_button next {{ $completeTablePageCount <= 1 ? 'disabled' : '' }} js-admin-overtime-page-button" data-admin-overtime-page-action="next" data-admin-overtime-page-item="next" aria-label="Next page"><i class="fa-solid fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-end mb-3 admin-overtime-card-filter">
                    <form class="clearfix d-flex align-items-center" method="GET" action="{{ url()->current() }}">
                        <input type="hidden" name="pending_month" value="{{ $selectedPendingMonth }}">
                        <input type="hidden" name="pending_year" value="{{ $selectedPendingYear }}">
                        <input type="hidden" name="complete_month" value="{{ $selectedCompleteMonth }}">
                        <input type="hidden" name="complete_year" value="{{ $selectedCompleteYear }}">
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
                                <span class="badge badge-sm {{ $overtimeCard['current_log']['badge_class'] }} light fw-semibold">{{ $overtimeCard['current_log']['status'] }}</span>
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
            <div class="accordion-item">
                <h2 class="accordion-header accordion-header-primary" id="headingOne7">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne7">
                    <span class="accordion-header-text">Finance And Administration</span>
                </button>
                </h2>
                <div id="collapseOne7" class="accordion-collapse collapse show" aria-labelledby="headingOne7" data-bs-parent="#accordion-five">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <div class="card" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;">
                                    <div class="card-body text-center p-md-4">
                                        <div class="mx-auto d-inline-block position-relative mb-3">
                                            <img src="files/employees/bussiness-man.png" alt="" class="rounded-circle avatar">
                                            <span class="fa fa-circle bored border-light text-success position-absolute bottom-0 end-0 mb-0 me-1 fs-12"></span>
                                        </div>
                                        <div class="mb-4">
                                            <h4 class="mb-0"><a >Evelyn Hope</a></h4>
                                            <p class="mb-0">IT and Publication</p>
                                        </div>
                                        <div class="text-start">
                                            <div class="row py-2">
                                                <div class="col-md-6 col-12">
                                                    <span>OVT This Week</span>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <span class="text-gray fw-semibold">2 hours</span> <br>
                                                </div>
                                            </div>
                                            <div class="row py-2">
                                                <div class="col-md-6 col-12">
                                                    <span>This Month</span>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <span class="text-gray fw-semibold">10 hours</span> <br>
                                                </div>
                                            </div>
                                            <div class="row py-2">
                                                <div class="col-md-6 col-12">
                                                    <span>This Year</span>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <span class="text-gray fw-semibold">31 hours</span> <br>
                                                </div>
                                            </div>
                                            <div class="row py-2">
                                                <div class="col-md-6 col-12">
                                                    <span>Paid Out!</span>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <span class="text-gray fw-semibold">10 hours</span> <br>
                                                    <span class="text-gray">Rp. 300.000</span>
                                                </div>
                                            </div>
                                            <div class="row py-2">
                                                <div class="col-md-6 col-12">
                                                    <span>Compensatory Time</span>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <span class="text-gray fw-semibold">8 hours</span> <br>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header accordion-header-info" id="headingTwo7">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo7" >
                    <span class="accordion-header-text">Accordion Header Two</span>
                </button>
                </h2>
                <div id="collapseTwo7" class="accordion-collapse collapse" aria-labelledby="headingTwo7" data-bs-parent="#accordion-five">
                    <div class="accordion-body">
                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header accordion-header-primary" id="headingThree7">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree7">
                    <span class="accordion-header-text">Accordion Header Three</span>
                </button>
                </h2>
                <div id="collapseThree7" class="accordion-collapse collapse" aria-labelledby="headingThree7" data-bs-parent="#accordion-five">
                    <div class="accordion-body">
                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End - Billing Statement -->

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

            function adminOvertimeTablePageSize($tableContainer) {
                return parseInt($tableContainer.attr('data-admin-overtime-page-size'), 10) || 5;
            }

            function updateAdminOvertimeTablePagination($tableContainer, currentPage, totalPages, totalRows) {
                var pageSize = adminOvertimeTablePageSize($tableContainer);
                var start = totalRows === 0 ? 0 : ((currentPage - 1) * pageSize) + 1;
                var end = Math.min(currentPage * pageSize, totalRows);

                $tableContainer.find('.js-admin-overtime-page-summary').text('Showing ' + start + ' to ' + end + ' of ' + totalRows + ' entries');
                $tableContainer.find('[data-admin-overtime-page-item]').removeClass('current disabled');
                $tableContainer.find('[data-admin-overtime-page]').removeAttr('aria-current');
                $tableContainer.find('[data-admin-overtime-page-item="' + currentPage + '"]')
                    .addClass('current')
                    .attr('aria-current', 'page');

                if (currentPage <= 1) {
                    $tableContainer.find('[data-admin-overtime-page-item="previous"]').addClass('disabled');
                }

                if (currentPage >= totalPages) {
                    $tableContainer.find('[data-admin-overtime-page-item="next"]').addClass('disabled');
                }
            }

            function showAdminOvertimeTablePage(tableContainer, pageNumber) {
                var $tableContainer = jQuery(tableContainer);
                var $rows = $tableContainer.find('.js-admin-overtime-table-row');
                var pageSize = adminOvertimeTablePageSize($tableContainer);
                var totalRows = $rows.length;
                var totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
                var currentPage = Math.min(Math.max(parseInt(pageNumber, 10) || 1, 1), totalPages);
                var startIndex = (currentPage - 1) * pageSize;
                var endIndex = startIndex + pageSize;

                $rows.each(function (index) {
                    jQuery(this).toggleClass('d-none', index < startIndex || index >= endIndex);
                });

                $tableContainer.attr('data-admin-overtime-current-page', currentPage);
                updateAdminOvertimeTablePagination($tableContainer, currentPage, totalPages, totalRows);
            }

            function initAdminOvertimeTablePagination() {
                jQuery('[data-admin-overtime-table]').each(function () {
                    showAdminOvertimeTablePage(this, jQuery(this).attr('data-admin-overtime-current-page') || 1);
                });
            }

            jQuery(document).on('click', '.js-admin-overtime-page-button', function () {
                var $button = jQuery(this);

                if ($button.hasClass('disabled')) {
                    return;
                }

                var $tableContainer = $button.closest('[data-admin-overtime-table]');
                var currentPage = parseInt($tableContainer.attr('data-admin-overtime-current-page'), 10) || 1;
                var pageAction = $button.attr('data-admin-overtime-page-action');
                var targetPage = parseInt($button.attr('data-admin-overtime-page'), 10) || currentPage;

                if (pageAction === 'previous') {
                    targetPage = currentPage - 1;
                }

                if (pageAction === 'next') {
                    targetPage = currentPage + 1;
                }

                showAdminOvertimeTablePage($tableContainer, targetPage);
            });

            initAdminOvertimeTablePagination();
        });
    </script>
@endsection
