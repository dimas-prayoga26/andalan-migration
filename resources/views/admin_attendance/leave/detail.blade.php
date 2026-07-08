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
        <div class="card leave-summary-card mb-4">
            <div class="card-header d-sm-flex d-block pb-0 border-0">
                <div>
                    <h4 class="card-title">Leave Summary</h4>
                    <p class="fs-13 mb-0">Monitor your time-off and your eligibility status</p>
                </div>
                <div class="card-action stat-card-tabs">
                    <ul class="nav nav-underline" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link text-black fs-16 fw-medium active" data-bs-toggle="tab" href="#Eligibility" role="tab">
                                <svg class="me-2 leave-summary-icon leave-summary-icon--eligibility" width="30" height="30" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="4" y="3.5" width="16" height="18" rx="3" fill="#A639FA" opacity="0.16"/>
                                    <path d="M8.5 4H15.5C15.5 2.89543 14.6046 2 13.5 2H10.5C9.39543 2 8.5 2.89543 8.5 4Z" fill="#A639FA"/>
                                    <path d="M7.75 8H16.25M7.75 17H16.25M8.5 12.35L10.85 14.65L16.25 9.25" stroke="#A639FA" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Eligibility
                                <span class="bg-secondary"></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-black fs-16 fw-medium" data-bs-toggle="tab" href="#Tracker" role="tab">
                                <svg class="me-2 leave-summary-icon leave-summary-icon--tracker" width="30" height="30" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.75 19V4.75" stroke="#FF3282" stroke-width="2.2" stroke-linecap="round"/>
                                    <path d="M3.75 19H20.25" stroke="#FF3282" stroke-width="2.2" stroke-linecap="round"/>
                                    <rect x="6.6" y="12.8" width="3.2" height="4.4" rx="1.3" fill="#FF3282" opacity="0.35"/>
                                    <rect x="11" y="9.6" width="3.2" height="7.6" rx="1.3" fill="#FF3282" opacity="0.55"/>
                                    <rect x="15.4" y="6.4" width="3.2" height="10.8" rx="1.3" fill="#FF3282"/>
                                    <path d="M7 11.35L10.2 8.15L13.35 10.2L18.35 5.2" stroke="#FF3282" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
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
                    <div class="tab-pane fade show active" id="Eligibility" role="tabpanel">
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
                    <div class="tab-pane fade mb-3" id="Tracker" role="tabpanel">
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
                    <div class="px-3 pb-3">
                        <button type="submit" class="btn light btn-success w-100 btn-lg">Update Leave Request</button>
                    </div>
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
