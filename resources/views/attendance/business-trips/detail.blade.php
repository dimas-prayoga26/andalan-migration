@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
@endsection

@section('navbarTitle', 'Attendances')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Business Trip Details',
    'homeRoute' => 'dashboard',
])

@include('attendance.layouts.profile-index')

<!-- Start - My Projects -->
<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Business Trip Details</h5>
        <div class="d-flex align-items-center">

        </div>
    </div>
</div>
<!-- End - My Projects -->
<div class="row">

    <div class="col-xxl-5 col-xl-5 col-md-5">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="card-title">Business Trip Request Details</h4>
                    <p class="fs-13 mb-0">Please ensure your travel dates and cash advance requests align with the company's travel policy.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Full Name</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">Thomas Jefferson</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Supervisor</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">Michael Scott</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Business Trip Purpose</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">Presentasi dan survei lokasi serta pengukuran cafe & finalisasi kontrak dengan klien.</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Business Trip Type</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">Intercity (Luar Kota)</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Trip Destination</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">Surabaya, Jawa Timur</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Trip Dates</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">10 June 2026 - 12 June 2026</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Trip Duration</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">3 Days</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Transportation</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">Rp. 3.000.000</span> <br>
                        <a href=""><span class="text-blue fw-semibold">Ticket</span></a> <br>
                        <span class="text-gray">Kereta Api Taksaka <br> 01 June 2026 - 20:00 WIB</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Local Transportation</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">Rp. 500.000</span> <br>
                        <span class="text-gray">Taxi to Airport etc</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Accommodation</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">Rp. 1.000.000</span> <br>
                        <a href=""><span class="text-blue fw-semibold">Receipt</span></a> <br>
                        <span class="text-gray">POP Hotel <br> 01 June 2026 - 02 June 2026</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Meals & Entertainment</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">Rp. 1.000.000</span> <br>
                        <span class="text-gray">Client dinner & daily meals</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Others</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">Rp. 500.000</span> <br>
                        <span class="text-gray">Others</span>
                    </div>
                </div>
                <hr>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Requested Cash Advance</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">Rp. 2.500.000</span> <br>
                        <span class="text-gray">For local transport, meals, and client entertainment</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Business Trip Incentive</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">Rp. 300.000</span> <br>
                        <span class="text-gray">Rp. 100.000 x 3 days</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Status Cash Advance</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="badge badge-sm badge-success light">Paid</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Status Reimbursement</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="badge badge-sm badge-success light">Paid</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Status Incentive</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="badge badge-sm badge-success light">Paid</span>
                    </div>
                </div>
            </div>
            <a class="btn light btn-warning m-3 mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#details">Update Details</a>
            <div class="mb-3"></div>
        </div>
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-4">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="card-title">Trip Expense & Reimbursement</h4>
                    <p class="fs-13 mb-0">Please ensure all declared expenses match the attached receipts and comply with the finance policy.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-start align-items-center mb-3">
                    <ul class="nav nav-pills nav-pills-sm nav-pills-bg gap-2" id="myTab3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="expense-tab3" data-bs-toggle="tab" data-bs-target="#tabExpense3" 
                            type="button" role="tab" aria-controls="tabExpense3" aria-selected="true">Expense</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cash-advance-tab3" data-bs-toggle="tab" data-bs-target="#tabCashAdvance3" 
                            type="button" role="tab" aria-controls="tabCashAdvance3" aria-selected="false">Cash Advance</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reimbursement-tab3" data-bs-toggle="tab" data-bs-target="#tabReimbursement3" 
                            type="button" role="tab" aria-controls="tabReimbursement3" aria-selected="false">Reimbursement</button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="tabExpense3" role="tabpanel" aria-labelledby="expense-tab3" tabindex="0">
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>10 Jun 2026</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Transportation</span> <br>
                                <span class="text-gray">Flight ticket (Round trip - Economy)</span> <br>
                                <span class="text-gray">Rp. 3.000.000</span> <br>
                                <a href=""><span class="text-blue fw-semibold">Attachment</span></a>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>12 Jun 2026</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Accommodation</span> <br>
                                <span class="text-gray">Hotel (2 Nights @ Rp 500.000)</span> <br>
                                <span class="text-gray">Rp. 1.000.000</span> <br>
                                <a href=""><span class="text-blue fw-semibold">Attachment</span></a>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>10-12 Jun 2026</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Meals & Entertaintment</span> <br>
                                <span class="text-gray">Client dinner & daily meals</span> <br>
                                <span class="text-gray">Rp. 800.000</span> <br>
                                <a href=""><span class="text-blue fw-semibold">Attachment</span></a>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>10-12 Jun 2026</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Local Transport</span> <br>
                                <span class="text-gray">Taxi to/from airport</span> <br>
                                <span class="text-gray">Rp. 200.000</span> <br>
                                <a href=""><span class="text-blue fw-semibold">Attachment</span></a>
                            </div>
                        </div>
                        <hr>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>Total Expenses</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Rp. 5.000.000</span> <br>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>Cash Advance</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-danger fw-semibold">Rp. 2.500.000</span> <br>
                                <hr>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>Balance Due</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Rp. 2.500.000</span> <br>
                                <span class="text-gray">Reimbursement to Employee</span> <br>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>Trip Incentive</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-success fw-semibold">Rp. 300.000</span> <br>
                                <span class="text-gray">Rp. 100.000 x 3 days</span> <br>
                            </div>
                        </div>
                        <hr>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>Total Payment</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-success fw-semibold">Rp. 2.800.000</span> <br>
                                <span class="text-gray">Company to Employee</span> <br>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tabCashAdvance3" role="tabpanel" aria-labelledby="cash-advance-tab3" tabindex="0">
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>10 Jun 2026</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Local Transport</span> <br>
                                <span class="text-gray">Taxi from Airport etc</span> <br>
                                <span class="text-decoration-line-through">Rp. 1.000.000</span>
                                <span class="text-gray">Rp. 500.000</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>10 Jun 2026</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Meals & Entertaintment</span> <br>
                                <span class="text-gray">Meals, and Client Entertainment</span> <br>
                                <span class="text-gray">Rp. 2.000.000</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>Total Payment</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Rp. 2.800.000</span> <br>
                                <span class="text-success">Approved cash advance</span> <br>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tabReimbursement3" role="tabpanel" aria-labelledby="reimbursement-tab3" tabindex="0">
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>10 Jun 2026</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Transportation</span> <br>
                                <span class="text-gray">Flight ticket</span> <br>
                                <span class="text-gray">Rp. 1.500.000</span> <br>
                                <a href=""><span class="text-blue fw-semibold">Attachment</span></a>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>12 Jun 2026</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Accommodation</span> <br>
                                <span class="text-gray">Hotel (2 Nights @ Rp 500.000)</span> <br>
                                <span class="text-gray">Rp. 1.000.000</span> <br>
                                <a href=""><span class="text-blue fw-semibold">Attachment</span></a>
                            </div>
                        </div>
                        <hr>
                        <div class="row py-2">
                            <div class="col-md-4 col-12">
                                <span>Total</span>
                            </div>
                            <div class="col-md-8 col-12">
                                <span class="text-gray fw-semibold">Rp. 2.500.000</span> <br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a class="btn light btn-secondary ms-3 mb-2 btn-lg" href="{{ isset($businessTrip) ? route('attendance.business-trips.cash-advances.create', $businessTrip) : '#' }}">Cash Advance</a>
                <a class="btn light btn-success me-3 mb-2 btn-lg" href="{{ isset($businessTrip) ? route('attendance.business-trips.reimbursements.create', $businessTrip) : '#' }}">Reimbursement</a>
            </div>
            <div class="mb-3"></div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-6 col-lg-6">
        <div class="card">
            <div class="card-body dz-scroll height380 p-3 pb-1">
                <div class="clearfix text-center">
                    <h4>Trip Lifecycle Tracker</h4>
                    <p>Track the real-time status of your business trip from the initial request to the final financial settlement.</p>
                </div>
                <div class="widget-timeline1 mb-3">
                    <ul class="timeline">
                        <li>
                            <span class="timeline-status">25 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span class="text-black fs-14 fw-semibold">Phase 1: Trip Approval</span> <br>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">26 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>
                                    1. Trip Request Submitted <br>
                                    Datetime : 22 May 2026, 17:00 <br>
                                    Actor : Thomas (As a Employee) <br>
                                    Status : <span class="badge badge-xs badge-success light fw-semibold">Complete</span>
                                </span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">26 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>
                                    2. Supervisor Review <br>
                                    Datetime : 22 May 2026, 17:00 <br>
                                    Actor : Thomas (Supervisor) <br>
                                    Status : <span class="badge badge-xs badge-success light fw-semibold">Approved</span>
                                </span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">27 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span class="text-black fs-14 fw-semibold">Phase 2: Pre-Trip Preparation</span> <br>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">27 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>
                                    3. Cash Advance Submitted <br>
                                    Datetime : 22 May 2026, 17:00 <br>
                                    Actor : Thomas (Employee) <br>
                                    Status : <span class="badge badge-xs badge-success light fw-semibold">Approved</span>
                                </span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">27 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>
                                    4. Finance Disbursement <br>
                                    Datetime : 22 May 2026, 17:00 <br>
                                    Actor : Ana (Finance) <br>
                                    Status : <span class="badge badge-xs badge-success light fw-semibold">Paid</span>
                                </span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">27 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span class="text-black fs-14 fw-semibold">Phase 3: Trip Execution</span> <br>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">15 Jun</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>
                                    5. Business Trip in Progress <br>
                                    Dates: 10 June 2026 - 12 June 2026 <br>
                                    Actor : Thomas (Employee) <br>
                                    Status : <span class="badge badge-xs badge-success light fw-semibold">Completed</span>
                                </span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">27 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span class="text-black fs-14 fw-semibold">Phase 4: Post-Trip Reporting & Settlement</span> <br>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">15 Jun</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>
                                    6. Trip Report & Task Submitted <br>
                                    Datetime : 22 May 2026, 17:00 <br>
                                    Actor : Thomas (Employee) <br>
                                    Status : <span class="badge badge-xs badge-success light fw-semibold">Completed</span>
                                </span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">15 Jun</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>
                                    7. Reimbursement Submitted <br>
                                    Datetime : 22 May 2026, 17:00 <br>
                                    Actor : Thomas (Employee) <br>
                                    Status : <span class="badge badge-xs badge-success light fw-semibold">Completed</span>
                                </span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">15 Jun</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>
                                    8. Final Finance Verification <br>
                                    Datetime : 22 May 2026, 17:00 <br>
                                    Actor : Ana (Finance) <br>
                                    Status : <span class="badge badge-xs badge-success light fw-semibold">Completed</span>
                                </span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">15 Jun</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>
                                    9. Reimbursement & Incentive Distributed <br>
                                    Datetime : 22 May 2026, 17:00 <br>
                                    Actor : Ana (Finance) <br>
                                    Status : <span class="badge badge-xs badge-success light fw-semibold">Paid</span>
                                </span>
                            </div>
                        </li>
                    </ul>
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
        $(function () {
            $('.attendance-tab-btn').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var targetUrl = $(this).data('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });
        });
    </script>
@endsection
