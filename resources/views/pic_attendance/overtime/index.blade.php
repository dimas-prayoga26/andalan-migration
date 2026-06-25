@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}">

@endsection

@section('navbarTitle', 'Overview')

@section('content')

@include('pic_attendance.layout.navbar')

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

<div class="row">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-4 col-4 mb-3">
                <div class="bg-dark-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-black fw-semibold fw-semibold">Pending</span>
                    <h5 class="mb-0 fw-semibold">{{ $overtimeSummary['pending_label'] ?? '0 request' }}</h5>
                </div>
            </div>
            <div class="col-md-4 col-4 mb-3">
                <div class="bg-success-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-success fw-semibold">SPV ACC</span>
                    <h5 class="mb-0 fw-semibold">{{ $overtimeSummary['supervisor_approved_label'] ?? '0 request' }}</h5>
                </div>
            </div>
            <div class="col-md-4 col-4 mb-3">
                <div class="bg-success-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-success fw-semibold">Director ACC</span>
                    <h5 class="mb-0 fw-semibold">{{ $overtimeSummary['director_approved_label'] ?? '0 request' }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-2 col-4 mb-3">
                <div class="bg-danger-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-danger fw-semibold">Total Hours</span>
                    <h5 class="mb-0 fw-semibold">{{ $overtimeSummary['total_hours_label'] ?? '0 hours' }}</h5>
                </div>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <div class="bg-info-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-info fw-semibold fw-semibold">Est. Cost</span>
                    <h5 class="mb-0 fw-semibold">Rp. 12 Jt</h5>
                </div>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <div class="bg-primary-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-primary fw-semibold">Median Hours</span>
                    <h5 class="mb-0 fw-semibold">{{ $overtimeSummary['median_hours_label'] ?? '0 hours' }}</h5>
                </div>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <div class="bg-warning-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-warning fw-semibold fw-semibold">Avg. Hours</span>
                    <h5 class="mb-0 fw-semibold">{{ $overtimeSummary['average_hours_label'] ?? '0 hours' }}</h5>
                </div>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <div class="bg-secondary-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-secondary fw-semibold">Top Overtime</span>
                    <h5 class="mb-0 fw-semibold">{{ $overtimeSummary['top_overtime_label'] ?? '-' }}</h5>
                </div>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <div class="bg-light-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-black fw-semibold">W-end|W-day</span>
                    <h5 class="mb-0 fw-semibold">{{ $overtimeSummary['weekend_weekday_label'] ?? '0h | 0h' }}</h5>
                </div>
            </div>
        </div>
    </div>
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
                            <div class="clearfix me-1">
                                <select name="month" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (($monthOptions ?? []) as $monthOption)
                                        <option value="{{ $monthOption['value'] }}" @selected(($selectedMonth ?? now('Asia/Jakarta')->month) === $monthOption['value'])>{{ $monthOption['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix">
                                <select name="year" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (($yearOptions ?? []) as $yearOption)
                                        <option value="{{ $yearOption }}" @selected(($selectedYear ?? now('Asia/Jakarta')->year) === $yearOption)>{{ $yearOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ms-2">
                                <div id="licenseUsageExcelBTN"></div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body table-card-body px-0 pt-0 pb-2">
                        <div class="table-responsive">
                            <table class="table table-sm table-sm-responsive text-nowrap" id="tableLicenseUsage">
                                <thead>
                                    <tr>
                                        <th class="mw-10">No</th>
                                        <th class="mw-50">Datetime</th>
                                        <th class="mw-50">Name</th>
                                        <th class="mw-80">SPV</th>
                                        <th class="mw-10">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse (($pendingRows ?? collect()) as $row)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $row['datetime'] }}</td>
                                            <td>{{ $row['name'] }}</td>
                                            <td>{{ $row['supervisor'] }}</td>
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
                                            <td colspan="5" class="text-center text-muted">No pending overtime data available for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header border-0 align-items-center">
                        <h4 class="card-title">Status : <span class="text-success">Approved</span> </h4>
                        <form class="clearfix d-flex align-items-center" method="GET" action="{{ url()->current() }}">
                            <div class="clearfix me-1">
                                <select name="month" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (($monthOptions ?? []) as $monthOption)
                                        <option value="{{ $monthOption['value'] }}" @selected(($selectedMonth ?? now('Asia/Jakarta')->month) === $monthOption['value'])>{{ $monthOption['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix">
                                <select name="year" class="selectpicker form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (($yearOptions ?? []) as $yearOption)
                                        <option value="{{ $yearOption }}" @selected(($selectedYear ?? now('Asia/Jakarta')->year) === $yearOption)>{{ $yearOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ms-2">
                                <div id="licenseUsageExcelBTN"></div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body table-card-body px-0 pt-0 pb-2">
                        <div class="table-responsive">
                            <table id="tableLogs" class="table table-sm table-sm-responsive text-nowrap">
                                <thead>
                                    <tr>
                                        <th class="mw-10">No</th>
                                        <th class="mw-50">Datetime</th>
                                        <th class="mw-50">Name</th>
                                        <th class="mw-80">Task</th>
                                        <th class="mw-80">Payout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse (($approvedRows ?? collect()) as $row)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $row['datetime'] }}</td>
                                            <td>{{ $row['name'] }}</td>
                                            <td>{{ $row['task'] }}</td>
                                            <td>{{ $row['payout'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No approved overtime data available for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pic-overtime-start-date" class="form-label">Start Date</label>
                            <input type="text" class="form-control pic-overtime-date-picker @error('start_date', 'picOvertimeStore') is-invalid @enderror" id="pic-overtime-start-date" name="start_date" value="{{ old('start_date') }}" placeholder="yyyy-mm-dd" autocomplete="off" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pic-overtime-end-date" class="form-label">End Date</label>
                            <input type="text" class="form-control pic-overtime-date-picker @error('end_date', 'picOvertimeStore') is-invalid @enderror" id="pic-overtime-end-date" name="end_date" value="{{ old('end_date') }}" placeholder="yyyy-mm-dd" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pic-overtime-start-time" class="form-label">Start Time</label>
                            <input type="time" class="form-control @error('start_time', 'picOvertimeStore') is-invalid @enderror" id="pic-overtime-start-time" name="start_time" value="{{ old('start_time') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pic-overtime-end-time" class="form-label">End Time</label>
                            <input type="time" class="form-control @error('end_time', 'picOvertimeStore') is-invalid @enderror" id="pic-overtime-end-time" name="end_time" value="{{ old('end_time') }}" required>
                        </div>
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
    <script src="{{ asset('assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ $dashboardJsVersion }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.jQuery || !jQuery.fn.datepicker) {
                return;
            }

            var $modal = jQuery('#picAddOvertimeModal');
            var $startDate = jQuery('#pic-overtime-start-date');
            var $endDate = jQuery('#pic-overtime-end-date');

            if (!$modal.length || !$startDate.length || !$endDate.length) {
                return;
            }

            var datepickerOptions = {
                autoclose: true,
                clearBtn: true,
                container: '#picAddOvertimeModal',
                format: 'yyyy-mm-dd',
                orientation: 'bottom auto',
                todayHighlight: true,
                zIndexOffset: 1060,
            };

            $endDate.datepicker(datepickerOptions);

            $startDate.datepicker(datepickerOptions).on('changeDate', function (event) {
                var endDate = $endDate.datepicker('getDate');

                $endDate.datepicker('setStartDate', event.date);

                if (endDate && event.date && endDate < event.date) {
                    $endDate.datepicker('setDate', event.date);
                }
            });

            $startDate.on('clearDate', function () {
                $endDate.datepicker('setStartDate', null);
            });

            $modal.on('hidden.bs.modal', function () {
                $startDate.datepicker('hide');
                $endDate.datepicker('hide');
            });
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
