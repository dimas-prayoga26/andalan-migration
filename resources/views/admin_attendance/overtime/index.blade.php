@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">

@endsection

@section('navbarTitle', 'Overview')

@section('content')

@include('admin_attendance.layout.navbar')

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

<div class="row">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-4 col-4 mb-3">
                <div class="bg-dark-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-black fw-semibold fw-semibold">Pending</span>
                    <h5 class="mb-0 fw-semibold">5 request</h5>
                </div>
            </div>
            <div class="col-md-4 col-4 mb-3">
                <div class="bg-success-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-success fw-semibold">SPV ACC</span>
                    <h5 class="mb-0 fw-semibold">0 request</h5>
                </div>
            </div>
            <div class="col-md-4 col-4 mb-3">
                <div class="bg-success-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-success fw-semibold">Director ACC</span>
                    <h5 class="mb-0 fw-semibold">0 request</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-2 col-4 mb-3">
                <div class="bg-danger-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-danger fw-semibold">Total Hours</span>
                    <h5 class="mb-0 fw-semibold">20 hours</h5>
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
                    <h5 class="mb-0 fw-semibold">2 hours</h5>
                </div>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <div class="bg-warning-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-warning fw-semibold fw-semibold">Avg. Hours</span>
                    <h5 class="mb-0 fw-semibold">5 hours</h5>
                </div>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <div class="bg-secondary-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-secondary fw-semibold">Top Overtime</span>
                    <h5 class="mb-0 fw-semibold">Rico</h5>
                </div>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <div class="bg-light-subtle rounded px-3 py-2 text-center">
                    <span class="fs-14 text-black fw-semibold">W-end|W-day</span>
                    <h5 class="mb-0 fw-semibold">16h | 20h</h5>
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
                        <div class="clearfix d-flex align-items-center">
                            <div class="clearfix me-1">
                                <select class="selectpicker form-select form-select-sm" >
                                    <option value="May">May</option>
                                    <option value="June">June</option>
                                    <option value="July">July</option>
                                    <option value="August">August</option>
                                </select>
                            </div>
                            <div class="clearfix">
                                <select class="selectpicker form-select form-select-sm" >
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                </select>
                            </div>
                            <div class="ms-2">
                                <div id="licenseUsageExcelBTN"></div>
                            </div>
                        </div>
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
                                    <tr>
                                        <td>1</td>
                                        <td>01 Jun 2026, 18:00 - 20:00 (2 hours)</td>
                                        <td>Dimas</td>
                                        <td>Syafiq</td>
                                        <td>
                                            <div class="dropdown dropdown-xs">
                                                <button class="btn btn-xs btn-light btn-square" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" >Edit</a></li>
                                                    <li><a class="dropdown-item" >Delete</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>02 Jun 2026, 20:00 - 23:00 (3 hours)</td>
                                        <td>Rexy</td>
                                        <td>Rexy</td>
                                        <td>
                                            <div class="dropdown dropdown-xs">
                                                <button class="btn btn-xs btn-light btn-square" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" >Edit</a></li>
                                                    <li><a class="dropdown-item" >Delete</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
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
                        <div class="clearfix d-flex align-items-center">
                            <div class="clearfix me-1">
                                <select class="selectpicker form-select form-select-sm" >
                                    <option value="May">May</option>
                                    <option value="June">June</option>
                                    <option value="July">July</option>
                                    <option value="August">August</option>
                                </select>
                            </div>
                            <div class="clearfix">
                                <select class="selectpicker form-select form-select-sm" >
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                </select>
                            </div>
                            <div class="ms-2">
                                <div id="licenseUsageExcelBTN"></div>
                            </div>
                        </div>
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
                                    <tr>
                                        <td>1</td>
                                        <td>01 Jun 2026, 18:00 - 20:00 (2 hours)</td>
                                        <td>Dimas</td>
                                        <td>5 task</td>
                                        <td>Rp. 50.000</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>02 Jun 2026, 20:00 - 23:00 (3 hours)</td>
                                        <td>Rexy</td>
                                        <td>5 task</td>
                                        <td>Rp. 75.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="clearfix d-flex">
                            <div class="avatar avatar-sm rounded me-3">
                                <img src="files/employees/bussiness-man.png" alt="">
                            </div>
                            <div class="clearfix">
                                <h6 class="mb-0 fw-semibold">
                                    <a href="{{ route('admin-attendance.overtime.detail') }}" class="stretched-link">#OVT-2605-0101</a>
                                </h6>
                                <span class="small">Muhammad Syafiq</span>	
                            </div>	
                        </div>
                        <p class="my-3">Halaman backend overtime dan backend business trip.</p>
                        <div class="row py-1">
                            <div class="col-12">
                                <span>Date :</span>
                            </div>
                            <div class="col-12">
                                <span>10 Jun 2026</span> <br>
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-12">
                                <span>Time :</span>
                            </div>
                            <div class="col-12">
                                <span class="text-decoration-line-through">18:00 - 20:30 (2,5 hours)</span> <br>
                                <span>18:00 - 20:00 (2 hours)</span>
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-12">
                                <span>PIC / Supervisor :</span>
                            </div>
                            <div class="col-12">
                                <span>Muhammad Syafiq</span> <br>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between flex-wrap">
                        <p class="mb-0 fw-semibold"><span class="text-success">SPV : Approved</span></p>
                        <span class="badge badge-sm badge-success light fw-semibold">Approved</span>
                    </div>
                </div>
            </div>
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
@endsection
