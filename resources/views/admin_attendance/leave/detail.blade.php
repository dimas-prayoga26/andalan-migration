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

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Leave</h5>
        <div class="d-flex align-items-center">
            <!-- <a href="javascript:void(0)" class="btn btn-primary btn-sm ms-2">+ New Project</a> -->
        </div>
    </div>
</div>
<!-- End - My Projects -->

<div class="row">
    <div class="col-md-2 col-sm-6">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Leave Balance ({{ $leaveSummaryYear }})</p>
                        <span class="title text-success fs-28 fw-semibold">{{ $leaveEligibility['available_balance_label'] }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-info" style="width: 0%; height:5px;" aria-label="Progess-info" role="progressbar">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-success"></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Joint Holiday ({{ $leaveSummaryYear }})</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $leaveEligibility['joint_holiday_label'] }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-info" style="width: 0%; height:5px;" aria-label="Progess-info" role="progressbar">
                            <span class="sr-only">100% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-secondary"></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Annual Leave ({{ $leaveSummaryYear }})</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $leaveTracker['annual_leave_taken_label'] }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar bg-success position-absolute rounded bootom-0" style="width: 0%; height:5px;" aria-label="Progess-success" role="progressbar">
                            <span class="sr-only">95% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-primary"></div>
        </div>
    </div>
    <!-- End - Daily Cycling -->
    <div class="col-md-2 col-sm-6">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Sick Leave ({{ $leaveSummaryYear }})</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $leaveTracker['sick_leave_taken_label'] }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-secondary" style="width: 0%; height:5px;" aria-label="Progess-secondary" role="progressbar">
                            <span class="sr-only">10%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-warning"></div>
        </div>
    </div>
    <!-- Start - Daily Cycling -->
    <div class="col-md-2 col-sm-6">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Special Leave ({{ $leaveSummaryYear }})</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $leaveTracker['special_leave_taken_label'] }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-danger" style="width: 0%; height:5px;" aria-label="Progess-danger"  role="progressbar">
                            <span class="sr-only">0% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-info"></div>
        </div>
    </div>
    <!-- Start - Daily Cycling -->
    <div class="col-md-2 col-sm-6">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <div>
                        <p class="fs-14 mb-2">Unpaid Leave ({{ $leaveSummaryYear }})</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $leaveTracker['unpaid_leave_taken_label'] }}</span>
                    </div>
                </div>
                <div>
                    <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                        <div class="progress-bar rounded bg-danger" style="width: 0%; height:5px;" aria-label="Progess-danger"  role="progressbar">
                            <span class="sr-only">0% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="effect bg-danger"></div>
        </div>
    </div>
</div>
    
<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header d-sm-flex d-block pb-0 border-0">
                <div>
                    <h4 class="card-title">Leave Summary</h4>
                    <p class="fs-13 mb-0">Monitor your time-off and your eligibility status</p>
                </div>
                <div class="card-action stat-card-tabs">
                    <ul class="nav nav-underline" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link text-black fs-16 fw-medium active" data-bs-toggle="tab" href="#Running" role="tab">
                                <svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0)">
                                    <path d="M0.988957 17.074C0.328275 17.2006 -0.104585 17.8385 0.0219823 18.4992C0.133362 19.0814 0.644694 19.4864 1.21678 19.4864C1.29272 19.4864 1.37119 19.4788 1.44713 19.4636L6.4592 18.5017C6.74524 18.446 7.00091 18.2916 7.18316 18.0638L9.33481 15.3502L8.61593 14.9832C8.08435 14.7148 7.71475 14.2288 7.58818 13.639L5.55804 16.1982L0.988957 17.074Z" fill="#A639FA"/>
                                    <path d="M18.84 6.493C20.3135 6.493 21.508 5.29848 21.508 3.82496C21.508 2.35144 20.3135 1.15692 18.84 1.15692C17.3665 1.15692 16.1719 2.35144 16.1719 3.82496C16.1719 5.29848 17.3665 6.493 18.84 6.493Z" fill="#A639FA"/>
                                    <path d="M13.0179 3.15671C12.7369 2.86813 12.4762 2.75422 12.1902 2.75422C12.0864 2.75422 11.9826 2.76941 11.8712 2.79472L7.29203 3.88067C6.6592 4.03002 6.26937 4.66539 6.41872 5.29569C6.54782 5.8374 7.02877 6.20192 7.56289 6.20192C7.65404 6.20192 7.74514 6.19179 7.8363 6.16901L11.7371 5.24507C11.9902 5.52605 13.2584 6.90057 13.4888 7.14358C11.8763 8.86996 10.2639 10.5938 8.65137 12.3202C8.62605 12.3481 8.60329 12.3759 8.58049 12.4037C8.10966 13.0036 8.25397 13.9453 8.96275 14.3022L13.9064 16.826L11.3397 20.985C10.9878 21.5571 11.165 22.3063 11.7371 22.6607C11.9371 22.7848 12.1573 22.843 12.375 22.843C12.7825 22.843 13.1824 22.638 13.4128 22.2658L16.6732 16.9829C16.8529 16.6918 16.901 16.34 16.8074 16.0134C16.7137 15.6843 16.4884 15.411 16.1821 15.2565L12.8331 13.5529L16.3543 9.7863L19.0122 12.0392C19.2324 12.2265 19.5032 12.3176 19.7716 12.3176C20.0601 12.3176 20.3487 12.2113 20.574 12.0038L23.6243 9.16106C24.1002 8.71808 24.128 7.97386 23.685 7.49797C23.4521 7.24989 23.1383 7.12333 22.8244 7.12333C22.5383 7.12333 22.2497 7.22711 22.0245 7.43721L19.7412 9.56101C19.7386 9.56354 14.0178 4.1819 13.0179 3.15671Z" fill="#A639FA"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0">
                                    <rect width="24" height="24" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                                Eligibility
                                <span class="bg-secondary"></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-black fs-16 fw-medium" data-bs-toggle="tab" href="#Cycling" role="tab">
                                <svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10.8586 5.22599L5.87121 10.5543C5.50758 11.0846 5.64394 11.8068 6.17172 12.1679L11.1945 15.6098V18.9558C11.1945 19.5921 11.6995 20.125 12.3359 20.1376C12.9874 20.1477 13.5177 19.625 13.5177 18.976V15.0013C13.5177 14.6174 13.3283 14.2588 13.0126 14.0442L9.79041 11.8346L12.5025 8.95836L13.8914 12.1225C14.0758 12.5442 14.4949 12.817 14.9546 12.817H19.1844C19.8207 12.817 20.3536 12.3119 20.3662 11.6755C20.3763 11.024 19.8536 10.4937 19.2046 10.4937H15.7172C15.2576 9.44824 14.7677 8.41288 14.3409 7.35228C14.1237 6.81693 14.0025 6.5846 13.6036 6.21592C13.5227 6.14016 12.9596 5.62501 12.4571 5.16541C11.995 4.74619 11.2828 4.77397 10.8586 5.22599Z" fill="#FF3282"/>
                                    <path d="M15.6162 5.80681C17.0861 5.80681 18.2778 4.61517 18.2778 3.1452C18.2778 1.67523 17.0861 0.483582 15.6162 0.483582C14.1462 0.483582 12.9545 1.67523 12.9545 3.1452C12.9545 4.61517 14.1462 5.80681 15.6162 5.80681Z" fill="#FF3282"/>
                                    <path d="M4.89899 23.5164C7.60463 23.5164 9.79798 21.3231 9.79798 18.6174C9.79798 15.9118 7.60463 13.7184 4.89899 13.7184C2.19335 13.7184 0 15.9118 0 18.6174C0 21.3231 2.19335 23.5164 4.89899 23.5164Z" fill="#FF3282"/>
                                    <path d="M19.101 23.5164C21.8066 23.5164 24 21.3231 24 18.6174C24 15.9118 21.8066 13.7184 19.101 13.7184C16.3954 13.7184 14.202 15.9118 14.202 18.6174C14.202 21.3231 16.3954 23.5164 19.101 23.5164Z" fill="#FF3282"/>
                                </svg>
                                Tracker
                                <span class="bg-danger"></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body pb-0">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="Running" role="tabpanel">
                        <div>
                            <h4 class="card-title">Leave Balance & Eligibility</h4>
                            <p class="fs-13 mb-0">Please ensure you have met the 1-year service requirement and your request does not exceed the maximum monthly limit.</p>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Full Name</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveEligibility['full_name'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Supervisor</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveEligibility['supervisor_name'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Join Date</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $leaveEligibility['join_date_label'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Current Tenure</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $leaveEligibility['tenure_label'] }}</span> <br>
                                @if ($leaveEligibility['is_eligible'])
                                    <span class="text-success">Eligible</span>
                                @else
                                    <span class="text-danger">Not Eligible</span>
                                @endif
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Available Balance</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveEligibility['available_balance_label'] }}</span> <br>
                                @if ($leaveEligibility['is_eligible'])
                                    <span class="text-success">Eligible</span>
                                @else
                                    <span class="text-danger">Not Eligible</span>
                                @endif
                                <br>
                                <span class="text-gray">{{ $leaveEligibility['available_balance_note'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Next Accrual</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveEligibility['next_accrual_label'] }}</span> <br>
                                <span class="text-gray">{{ $leaveEligibility['next_accrual_note'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Joint Holiday</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveEligibility['joint_holiday_label'] }}</span> <br>
                                <span class="text-gray">
                                    @forelse ($leaveEligibility['joint_holiday_items'] as $item)
                                        {{ $item }}@if (! $loop->last), <br>@endif
                                    @empty
                                        No joint holiday scheduled.
                                    @endforelse
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade mb-3" id="Cycling" role="tabpanel">
                        <div>
                            <h4 class="card-title">Time Off Tracker</h4>
                            <p class="fs-13 mb-0">Review your year-to-date usage and check the status of your recent requests.</p>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Annual Leave Taken</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['annual_leave_taken_label'] }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['annual_leave_taken_breakdown'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Annual Leave Taken This Month ({{ $leaveTracker['month_label'] }})</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['annual_leave_taken_month_label'] }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['annual_leave_taken_month_breakdown'] }}</span> <br>
                                <span class="text-gray">Maximum limit is 2 days per month</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Sick Leave Taken</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['sick_leave_taken_label'] }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['sick_leave_taken_breakdown'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Sick Leave Taken This Month ({{ $leaveTracker['month_label'] }})</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['sick_leave_taken_month_label'] }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['sick_leave_taken_month_breakdown'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Special Leave Taken</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['special_leave_taken_label'] }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['special_leave_taken_breakdown'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Special Leave Taken This Month ({{ $leaveTracker['month_label'] }})</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['special_leave_taken_month_label'] }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['special_leave_taken_month_breakdown'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Unpaid Leave Taken</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['unpaid_leave_taken_label'] }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['unpaid_leave_taken_breakdown'] }}</span>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Unpaid Leave Taken This Month ({{ $leaveTracker['month_label'] }})</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray fw-semibold">{{ $leaveTracker['unpaid_leave_taken_month_label'] }}</span> <br>
                                <span class="text-gray">{{ $leaveTracker['unpaid_leave_taken_month_breakdown'] }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Active / Pending Requests</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $leaveTracker['pending_requests_label'] }}</span> <br>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Total Approved Leaves</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $leaveTracker['approved_requests_label'] }}</span> <br>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-md-6 col-12">
                                <span>Total Rejected Leaves</span>
                            </div>
                            <div class="col-md-6 col-12">
                                <span class="text-gray">{{ $leaveTracker['rejected_requests_label'] }}</span> <br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="card-title">Confirm Leave Approval</h4>
                    <p class="fs-13 mb-0">Ensure leave dates are accurate and required attachments are uploaded.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin-attendance.leave.approval.update', ['uid' => request()->route('uid')]) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @error('status')
                        <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror
                <div class="row">
                    <div class="col-12 col-md-12 mb-3">
                        <label class="form-label">Approval Status <span class="text-danger">*</span></label>
                        <div class="form-group mb-0">
                            <div class="form-check d-inline-block me-3">
                                <input class="form-check-input" type="radio" name="status" value="pending" id="leaveApprovalPending" @checked($leaveApproval['status'] === 'pending') disabled>
                                <label class="form-check-label fw-semibold opacity-100 {{ $leaveApproval['status_text_class'] }}" for="leaveApprovalPending">
                                Pending
                                </label>
                            </div>
                            <div class="form-check d-inline-block mx-2 me-3">
                                <input class="form-check-input" type="radio" name="status" value="approved" id="leaveApprovalApproved" @checked($leaveApproval['status'] === 'approved') @disabled(! $leaveApproval['can_finalize'])>
                                <label class="form-check-label fw-semibold text-success" for="leaveApprovalApproved">
                                Approve
                                </label>
                            </div>
                            <div class="form-check d-inline-block">
                                <input class="form-check-input" type="radio" name="status" value="rejected" id="leaveApprovalRejected" @checked($leaveApproval['status'] === 'rejected') @disabled(! $leaveApproval['can_finalize'])>
                                <label class="form-check-label fw-semibold text-danger" for="leaveApprovalRejected">
                                Reject
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Leave Type</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">{{ $leaveApproval['leave_type_label'] }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Special Leave Type</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">{{ $leaveApproval['special_leave_type_label'] }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Date</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">{{ $leaveApproval['period_label'] }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Reason</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">{{ $leaveApproval['reason'] }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Handover Note</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">{{ $leaveApproval['handover_notes'] }}</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Attachment</span>
                    </div>
                    <div class="col-md-6 col-12">
                        @if ($leaveApproval['attachment_url'])
                            <a href="{{ $leaveApproval['attachment_url'] }}" class="text-primary" target="_blank" rel="noopener noreferrer">{{ $leaveApproval['attachment_label'] }}</a>
                        @else
                            <span class="text-gray">-</span>
                        @endif
                    </div>
                </div>
                </div>
                @if ($leaveApproval['can_finalize'])
                    <button type="submit" class="btn light btn-success m-3 mb-2 btn-lg">Update Leave Request</button>
                @endif
            </form>
            <div class="mb-3"></div>
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
@endsection
