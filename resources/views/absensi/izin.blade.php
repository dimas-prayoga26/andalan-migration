@extends('layouts.main')

@section('title', 'Dashboard Andalan')

@section('css')
    @php
        $dashboardCssPath = public_path('assets/css/dashboard.css');
        $dashboardCssVersion = file_exists($dashboardCssPath) ? filemtime($dashboardCssPath) : time();
        $absensiCssPath = public_path('assets/css/absensi.css');
        $absensiCssVersion = file_exists($absensiCssPath) ? filemtime($absensiCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ $dashboardCssVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/css/absensi.css') }}?v={{ $absensiCssVersion }}">
@endsection

@section('navbarTitle', 'Attendances')

@section('content')
@include('layouts.breadcrumb', [
    'title' => 'Attendances',
    'current' => 'Leaves & Sick',
    'homeRoute' => 'dashboard',
])

@include('absensi.layouts_absensi.profileIndex')

<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Leaves & Sick</h5>
    </div>
</div>

<div class="row">
    <div class="col-xxl-6 col-xl-6 col-sm-6">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="card-title">Leave Balance & Eligibility</h4>
                    <p class="fs-13 mb-0">Please ensure you have met the 1-year service requirement and your request does not exceed the maximum monthly limit.</p>
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
                        <span>Join Date</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">15 March 2025</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Current Tenure</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">1 Year, 2 Months</span> <br>
                        <span class="text-success">Eligible</span> / <span class="text-danger">Not Eligible</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Available Balance</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">3 Days</span> <br>
                        <span class="text-gray">Rolled over from previous months</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Leave Used in 2026</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">4 Days</span> <br>
                        <span class="text-gray">Jan (1 days), Mar (2 days)</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Leave Taken This Month (May)</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">0 Days</span> <br>
                        <span class="text-gray">Maximum limit is 2 days per month</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Next Accrual</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray fw-semibold">+1 Day</span> <br>
                        <span class="text-gray">Will be automatically added next month</span>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Active / Pending Requests</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">1 Request</span> <br>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Total Approved Leaves</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">2 Requests</span> <br>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-md-6 col-12">
                        <span>Total Rejected Leaves</span>
                    </div>
                    <div class="col-md-6 col-12">
                        <span class="text-gray">1 Requests</span> <br>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-6 col-xl-6 col-sm-6">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="card-title">Confirm Leave Request</h4>
                    <p class="fs-13 mb-0">Ensure your leave dates are accurate and required attachments are uploaded. This will be sent to HR and your manager for approval.</p>
                </div>
            </div>
            <div class="card-body">
                <div class=" mb-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" placeholder="Add Date">
                </div>
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select class="selectpicker form-select" required>
                            <option value="AL">Annual Leave (3 days left)</option>
                            <option value="WY">Special Leave</option>
                            <option value="WY">Sick Leave</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Special Leave Type</label>
                        <select class="selectpicker form-select">
                            <option>Choose Special Leave First</option>
                            <option>Employee's marriage (3 days)</option>
                            <option>Employee's wife giving birth (3 days)</option>
                            <option>Death of spouse, child, child-in-law, parent, or parent-in-law (3 days)</option>
                            <option>Death of sibling or sibling-in-law (1 days)</option>
                            <option>Marriage of employee's child (2 days)</option>
                            <option>Marriage of employee's sibling or sibling-in-law (1 days)</option>
                            <option>Circumcision of employee's child (2 days)</option>
                            <option>Baptism of employee's child (2 days)</option>
                            <option>Attending the university graduation of employee's child or spouse (1 days)</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="mb-3">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="2" placeholder="Misal: Istri melahirkan" required></textarea>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="mb-3">
                            <label class="form-label">Handover Notes (optional)</label>
                            <textarea class="form-control" rows="2" placeholder="Misal: Deployment akan di-backup oleh Budi"></textarea>
                        </div>
                    </div>
                </div>
                <label class="form-label">Attachment (max 1 MB)</label>
                <div class="">
                    <div class="avatar avatar-xl avatar-preview">
                        <img class="imagePreview w-100 h-100" src="assets/images/avatar/placeholder.avif" alt="Avatar">
                        <input type="file" class="imageUpload d-none" accept=".png, .jpg, .avif, .webp, .jpeg">
                        <a class="avatar avatar-xs position-absolute bottom-0 end-0 bg-white shadow-sm upload-trigger">
                            <i class="fa-solid fa-upload"></i>
                        </a>
                    </div>
                </div>
            </div>
            <a class="btn light btn-success m-3 mb-2 btn-lg" data-bs-toggle="modal" data-bs-target="#clockIn">Request Time Off</a>
            <div class="mb-3"></div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="clearfix d-flex">
                    <div class="avatar avatar-sm rounded me-3 p-2">
                        <img src="assets/images/logo/figma.avif" alt="">
                    </div>
                    <div class="clearfix">
                        <h6 class="mb-0 fw-semibold"><a data-bs-toggle="modal" data-bs-target="#annualLeave" class="stretched-link">Cuti Tahunan</a></h6>
                        <span class="small">20 May 2026 - 21 May 2026 (2 days)</span>	
                    </div>	
                </div>
                <p class="my-3">Izin acara menghadiri pernikahan saudara di Manado Sulawesi Utara</p>
                <div class="widget-timeline1">
                    <ul class="timeline">
                        <li>
                            <span class="timeline-status">15 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>Request Submitted</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">15 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>Supervisor Review</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">16 May</span>
                            <div class="timeline-badge border-dark"></div>
                            <div class="timeline-panel">
                                <span>HR Verification (Pending)</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status"></span>
                            <div class="timeline-badge border-dark"></div>
                            <div class="timeline-panel">
                                <span>Final Decision</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between flex-wrap">
                <p class="mb-0 fw-medium">Due <span class="text-purple">: 20 May 2026</span></p>
                <span class="badge badge-sm badge-primary light">Pending</span>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="clearfix d-flex">
                    <div class="avatar avatar-sm rounded me-3 p-2">
                        <img src="assets/images/logo/figma.avif" alt="">
                    </div>
                    <div class="clearfix">
                        <h6 class="mb-0 fw-semibold"><a data-bs-toggle="modal" data-bs-target="#annualLeave" class="stretched-link">Cuti Tahunan</a></h6>
                        <span class="small">20 May 2026 - 21 May 2026 (2 days)</span>	
                    </div>	
                </div>
                <p class="my-3">Izin acara liburan di Bali</p>
                <div class="widget-timeline1">
                    <ul class="timeline">
                        <li>
                            <span class="timeline-status">15 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>Request Submitted</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">15 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>Supervisor Review</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">16 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>HR Verification (Pending)</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">16 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>Approved</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between flex-wrap">
                <p class="mb-0 fw-medium">Due <span class="text-purple">: 20 May 2026</span></p>
                <span class="badge badge-sm badge-success light">Approved</span>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="clearfix d-flex">
                    <div class="avatar avatar-sm rounded me-3 p-2">
                        <img src="assets/images/logo/figma.avif" alt="">
                    </div>
                    <div class="clearfix">
                        <h6 class="mb-0 fw-semibold"><a data-bs-toggle="modal" data-bs-target="#annualLeave" class="stretched-link">Cuti Tahunan</a></h6>
                        <span class="small">20 May 2026 - 21 May 2026 (2 days)</span>	
                    </div>	
                </div>
                <p class="my-3">Izin acara liburan di Bali</p>
                <div class="widget-timeline1">
                    <ul class="timeline">
                        <li>
                            <span class="timeline-status">15 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>Request Submitted</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">15 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>Supervisor Review</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">16 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>HR Verification (Pending)</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">16 May</span>
                            <div class="timeline-badge border-danger"></div>
                            <div class="timeline-panel">
                                <span>Rejected</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between flex-wrap">
                <p class="mb-0 fw-medium">Due <span class="text-purple">: 20 May 2026</span></p>
                <span class="badge badge-sm badge-danger light">Rejected</span>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="clearfix d-flex">
                    <div class="avatar avatar-sm rounded me-3 p-2">
                        <img src="assets/images/logo/figma.avif" alt="">
                    </div>
                    <div class="clearfix">
                        <h6 class="mb-0 fw-semibold"><a data-bs-toggle="modal" data-bs-target="#sick" class="stretched-link">Cuti Sakit</a></h6>
                        <span class="small">20 May 2026 (1 day)</span>	
                    </div>	
                </div>
                <p class="my-3">Izin periksa ke dokter karena sakit diare dan muntah muntah</p>
                <div class="widget-timeline1">
                    <ul class="timeline">
                        <li>
                            <span class="timeline-status">15 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>Request Submitted</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">15 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>Supervisor Review</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">16 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>HR Verification (Pending)</span>
                            </div>
                        </li>
                        <li>
                            <span class="timeline-status">16 May</span>
                            <div class="timeline-badge border-success"></div>
                            <div class="timeline-panel">
                                <span>Approved</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between flex-wrap">
                <p class="mb-0 fw-medium">Due <span class="text-purple">: 20 May 2026</span></p>
                <span class="badge badge-sm badge-primary light">Pending</span>
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
            $('.absensi-tab-btn').on('click', function (event) {
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
